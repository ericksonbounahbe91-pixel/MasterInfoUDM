from django.db import models


class Departement(models.Model):
    iddep = models.AutoField(primary_key=True)
    nomdep = models.CharField(max_length=100)

    def __str__(self):
        return self.nomdep


class Region(models.Model):
    idregion = models.AutoField(primary_key=True)
    nomregion = models.CharField(max_length=100)
    iddep = models.ForeignKey(Departement, on_delete=models.PROTECT, related_name="regions")

    def __str__(self):
        return self.nomregion


class Client(models.Model):
    idcli = models.AutoField(primary_key=True)
    nomcli = models.CharField(max_length=100)
    prenomcli = models.CharField(max_length=100)
    email = models.EmailField()
    photo = models.ImageField(upload_to="clients/", blank=True, null=True)
    idregion = models.ForeignKey(Region, on_delete=models.PROTECT, related_name="clients")

    def __str__(self):
        return f"{self.nomcli} {self.prenomcli}"


class Produit(models.Model):
    idprod = models.AutoField(primary_key=True)
    designprod = models.CharField(max_length=150)
    qtestock = models.PositiveIntegerField(default=0)
    prixuprod = models.DecimalField(max_digits=10, decimal_places=2)
    photoprod = models.ImageField(upload_to="produits/", blank=True, null=True)

    def __str__(self):
        return self.designprod


class Commande(models.Model):
    idcmd = models.AutoField(primary_key=True)
    datecmd = models.DateField()
    idcli = models.ForeignKey(Client, on_delete=models.CASCADE, related_name="commandes")

    def __str__(self):
        return f"Commande {self.idcmd}"


class LigneCommande(models.Model):
    idcmd = models.ForeignKey(Commande, on_delete=models.CASCADE, related_name="lignes")
    idprod = models.ForeignKey(Produit, on_delete=models.PROTECT)
    qtecmd = models.PositiveIntegerField()

    class Meta:
        constraints = [
            models.UniqueConstraint(fields=["idcmd", "idprod"], name="pk_lignecommande")
        ]

    def __str__(self):
        return f"{self.idcmd} / {self.idprod} x{self.qtecmd}"