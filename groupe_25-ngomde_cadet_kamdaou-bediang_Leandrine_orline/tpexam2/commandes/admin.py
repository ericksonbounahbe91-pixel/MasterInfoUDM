from django.contrib import admin
from .models import *

for m in (Departement, Region, Client, Produit, Commande, LigneCommande):
    admin.site.register(m)
