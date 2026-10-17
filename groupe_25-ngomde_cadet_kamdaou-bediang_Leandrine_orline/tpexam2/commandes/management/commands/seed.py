import random
from datetime import date, timedelta
from django.core.management.base import BaseCommand
from commandes.models import *

class Command(BaseCommand):
    def handle(self, *args, **kwargs):
        deps = [Departement.objects.create(nomdep=f"Département {i}") for i in range(1, 4)]
        regs = [Region.objects.create(nomregion=f"Région {i}", iddep=random.choice(deps)) for i in range(1, 7)]
        clis = [Client.objects.create(nomcli=f"Nom{i}", prenomcli=f"Prénom{i}",
                email=f"client{i}@mail.com", idregion=random.choice(regs)) for i in range(1, 16)]
        prods = [Produit.objects.create(designprod=f"Produit {i}", qtestock=random.randint(0, 100),
                 prixuprod=random.randint(5, 200)) for i in range(1, 13)]
        for _ in range(40):
            c = Commande.objects.create(datecmd=date.today() - timedelta(days=random.randint(0, 180)),
                                        idcli=random.choice(clis))
            for p in random.sample(prods, random.randint(1, 4)):
                LigneCommande.objects.create(idcmd=c, idprod=p, qtecmd=random.randint(1, 10))
        self.stdout.write("Données créées")