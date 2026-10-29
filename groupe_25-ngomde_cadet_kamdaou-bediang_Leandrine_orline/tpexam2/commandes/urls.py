from django.urls import path
from . import views

urlpatterns = [
    path("", views.dashboard, name="dashboard"),
    path("produits/", views.produits, name="produits"),
    path("produits/<int:pk>/modifier/", views.modifier_produit, name="modifier_produit"),
    path("clients/", views.clients, name="clients"),
    path("commandes/", views.commandes, name="commandes"),
    path("commandes/nouvelle/", views.nouvelle_commande, name="nouvelle_commande"),
    path("commandes/<int:idcmd>/", views.detail_commande, name="detail_commande"),
    path("commandes/<int:idcmd>/ajouter/", views.ajouter_ligne, name="ajouter_ligne"),
    path("commandes/<int:idcmd>/supprimer/<int:idprod>/", views.supprimer_ligne, name="supprimer_ligne"),
]
