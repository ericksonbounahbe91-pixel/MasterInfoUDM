import json
from django.contrib import messages
from django.db.models import Count, DecimalField, ExpressionWrapper, F, Sum
from django.db.models.functions import TruncMonth
from django.shortcuts import get_object_or_404, redirect, render

from .forms import CommandeForm, LigneCommandeForm, ProduitForm
from .models import Client, Commande, LigneCommande, Produit

# Chiffre d'affaires d'une ligne = quantité x prix unitaire
CA = ExpressionWrapper(F("qtecmd") * F("idprod__prixuprod"),
                       output_field=DecimalField(max_digits=14, decimal_places=2))
CA_VIA_CMD = ExpressionWrapper(F("lignes__qtecmd") * F("lignes__idprod__prixuprod"),
                               output_field=DecimalField(max_digits=14, decimal_places=2))


def produits(request):
    return render(request, "commandes/produits.html",
                  {"produits": Produit.objects.order_by("idprod")})


def clients(request):
    qs = Client.objects.select_related("idregion", "idregion__iddep") \
        .annotate(nb_cmd=Count("commandes")).order_by("idcli")
    return render(request, "commandes/clients.html", {"clients": qs})


def commandes(request):
    qs = (Commande.objects.select_related("idcli")
          .annotate(nb_lignes=Count("lignes"), total=Sum(CA_VIA_CMD))
          .order_by("-datecmd", "-idcmd"))
    return render(request, "commandes/commandes.html", {"commandes": qs})


def nouvelle_commande(request):
    form = CommandeForm(request.POST or None)
    if form.is_valid():
        cmd = form.save()
        messages.success(request, f"Commande {cmd.idcmd} créée : ajoutez maintenant ses produits.")
        return redirect("ajouter_ligne", idcmd=cmd.idcmd)
    return render(request, "commandes/form.html", {"form": form, "titre": "Nouvelle commande"})


def detail_commande(request, idcmd):
    cmd = get_object_or_404(Commande.objects.select_related("idcli"), pk=idcmd)
    lignes = cmd.lignes.select_related("idprod").annotate(sous_total=CA).order_by("idprod")
    total = sum(l.sous_total for l in lignes)
    return render(request, "commandes/detail_commande.html",
                  {"cmd": cmd, "lignes": lignes, "total": total})


def ajouter_ligne(request, idcmd):
    cmd = get_object_or_404(Commande, pk=idcmd)
    form = LigneCommandeForm(request.POST or None, commande=cmd)
    if form.is_valid():
        ligne = form.save(commit=False)
        ligne.idcmd = cmd
        ligne.save()
        messages.success(request, "Produit ajouté à la commande.")
        return redirect("detail_commande", idcmd=cmd.idcmd)
    return render(request, "commandes/form.html",
                  {"form": form, "titre": f"Ajouter un produit à la commande n°{cmd.idcmd}",
                   "retour": f"/commandes/{cmd.idcmd}/"})


def modifier_produit(request, pk):
    p = get_object_or_404(Produit, pk=pk)
    form = ProduitForm(request.POST or None, request.FILES or None, instance=p)
    if form.is_valid():
        form.save()
        messages.success(request, "Produit modifié.")
        return redirect("produits")
    return render(request, "commandes/form.html",
                  {"form": form, "titre": f"Modifier le produit « {p} »", "retour": "/produits/"})


def supprimer_ligne(request, idcmd, idprod):
    ligne = get_object_or_404(LigneCommande.objects.select_related("idprod"),
                              idcmd=idcmd, idprod=idprod)
    if request.method == "POST":
        ligne.delete()
        messages.success(request, "Produit retiré de la commande.")
        return redirect("detail_commande", idcmd=idcmd)
    return render(request, "commandes/confirmer.html",
                  {"ligne": ligne, "retour": f"/commandes/{idcmd}/"})


def dashboard(request):
    nb_cmd = Commande.objects.count()
    ca_total = LigneCommande.objects.aggregate(t=Sum(CA))["t"] or 0

    top_produits = list(Produit.objects.annotate(vendu=Sum("lignecommande__qtecmd"))
                        .filter(vendu__gt=0).order_by("-vendu")[:5])
    ca_region = list(LigneCommande.objects
                     .annotate(region=F("idcmd__idcli__idregion__nomregion"))
                     .values("region").annotate(ca=Sum(CA)).order_by("-ca"))
    ca_mois = list(LigneCommande.objects
                   .annotate(mois=TruncMonth("idcmd__datecmd"))
                   .values("mois").annotate(ca=Sum(CA)).order_by("mois"))
    top_clients = list(LigneCommande.objects
                       .annotate(nom=F("idcmd__idcli__nomcli"), prenom=F("idcmd__idcli__prenomcli"))
                       .values("nom", "prenom").annotate(ca=Sum(CA)).order_by("-ca")[:5])

    charts = {
        "produits": {"labels": [p.designprod for p in top_produits],
                     "data": [p.vendu for p in top_produits]},
        "regions": {"labels": [r["region"] for r in ca_region],
                    "data": [float(r["ca"]) for r in ca_region]},
        "mois": {"labels": [m["mois"].strftime("%m/%Y") for m in ca_mois],
                 "data": [float(m["ca"]) for m in ca_mois]},
    }
    ctx = {
        "nb_clients": Client.objects.count(),
        "nb_commandes": nb_cmd,
        "nb_produits": Produit.objects.count(),
        "ca_total": ca_total,
        "panier_moyen": (ca_total / nb_cmd) if nb_cmd else 0,
        "stock_bas": Produit.objects.filter(qtestock__lt=10).order_by("qtestock"),
        "top_clients": top_clients,
        "charts": json.dumps(charts),
    }
    return render(request, "commandes/dashboard.html", ctx)
