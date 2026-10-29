from django import forms
from .models import Produit, LigneCommande, Commande


class ProduitForm(forms.ModelForm):
    class Meta:
        model = Produit
        fields = ["designprod", "qtestock", "prixuprod", "photoprod"]
        labels = {"designprod": "Désignation", "qtestock": "Quantité en stock",
                  "prixuprod": "Prix unitaire", "photoprod": "Photo"}


class CommandeForm(forms.ModelForm):
    class Meta:
        model = Commande
        fields = ["datecmd", "idcli"]
        labels = {"datecmd": "Date de la commande", "idcli": "Client"}
        widgets = {"datecmd": forms.DateInput(attrs={"type": "date"})}


class LigneCommandeForm(forms.ModelForm):
    class Meta:
        model = LigneCommande
        fields = ["idprod", "qtecmd"]
        labels = {"idprod": "Produit", "qtecmd": "Quantité commandée"}

    def __init__(self, *args, commande=None, **kwargs):
        super().__init__(*args, **kwargs)
        self.commande = commande
        self.fields["qtecmd"].min_value = 1
        self.fields["qtecmd"].widget.attrs["min"] = 1

    def clean(self):
        data = super().clean()
        p, q = data.get("idprod"), data.get("qtecmd")
        if p and q:
            if q < 1:
                raise forms.ValidationError("La quantité doit être au moins 1.")
            if q > p.qtestock:
                raise forms.ValidationError(
                    f"Stock insuffisant : {p.qtestock} disponible(s) pour « {p} ».")
            if self.commande and LigneCommande.objects.filter(
                    idcmd=self.commande, idprod=p).exists():
                raise forms.ValidationError(
                    "Ce produit est déjà dans la commande (supprimez-le puis ré-ajoutez-le).")
        return data
