package com.groupe25.tpexam3.core.data.local

import android.content.Context
import androidx.room.Database
import androidx.room.Room
import androidx.room.RoomDatabase
import androidx.sqlite.db.SupportSQLiteDatabase
import com.groupe25.tpexam3.core.data.local.dao.ClientDao
import com.groupe25.tpexam3.core.data.local.dao.CommandeDao
import com.groupe25.tpexam3.core.data.local.dao.DashboardDao
import com.groupe25.tpexam3.core.data.local.dao.LigneCommandeDao
import com.groupe25.tpexam3.core.data.local.dao.ProductDao
import com.groupe25.tpexam3.core.data.local.dao.UserDao
import com.groupe25.tpexam3.core.data.local.entity.ClientEntity
import com.groupe25.tpexam3.core.data.local.entity.CommandeEntity
import com.groupe25.tpexam3.core.data.local.entity.DepartmentEntity
import com.groupe25.tpexam3.core.data.local.entity.LigneCommandeEntity
import com.groupe25.tpexam3.core.data.local.entity.ProductEntity
import com.groupe25.tpexam3.core.data.local.entity.RegionEntity
import com.groupe25.tpexam3.core.data.local.entity.UserEntity
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch

@Database(
    entities = [
        UserEntity::class,
        DepartmentEntity::class,
        RegionEntity::class,
        ClientEntity::class,
        ProductEntity::class,
        CommandeEntity::class,
        LigneCommandeEntity::class
    ],
    version = 1,
    exportSchema = false
)
abstract class AppDatabase : RoomDatabase() {

    abstract fun userDao(): UserDao
    abstract fun productDao(): ProductDao
    abstract fun clientDao(): ClientDao
    abstract fun commandeDao(): CommandeDao
    abstract fun ligneCommandeDao(): LigneCommandeDao
    abstract fun dashboardDao(): DashboardDao

    companion object {
        fun create(context: Context): AppDatabase {
            return Room.databaseBuilder(
                context.applicationContext,
                AppDatabase::class.java,
                "tpexam3.db"
            )
                .addCallback(SeedCallback())
                .build()
        }
    }
}

private class SeedCallback : RoomDatabase.Callback() {
    override fun onCreate(db: SupportSQLiteDatabase) {
        super.onCreate(db)
        CoroutineScope(Dispatchers.IO).launch {
            seedDatabase(db)
        }
    }
}

private fun seedDatabase(db: SupportSQLiteDatabase) {
    // User for fake auth
    db.execSQL("INSERT INTO users (login, password) VALUES ('MasTMound2', 'MoundMasT2@2026')")

    // Departments
    db.execSQL("INSERT INTO departement (idDep, nomDep) VALUES (1, 'Informatique')")
    db.execSQL("INSERT INTO departement (idDep, nomDep) VALUES (2, 'Mathématiques')")
    db.execSQL("INSERT INTO departement (idDep, nomDep) VALUES (3, 'Physique')")

    // Regions
    db.execSQL("INSERT INTO region (idRegion, nomRegion, idDep) VALUES (1, 'Nord', 1)")
    db.execSQL("INSERT INTO region (idRegion, nomRegion, idDep) VALUES (2, 'Sud', 1)")
    db.execSQL("INSERT INTO region (idRegion, nomRegion, idDep) VALUES (3, 'Est', 2)")
    db.execSQL("INSERT INTO region (idRegion, nomRegion, idDep) VALUES (4, 'Ouest', 2)")
    db.execSQL("INSERT INTO region (idRegion, nomRegion, idDep) VALUES (5, 'Centre', 3)")

    // Clients
    db.execSQL("INSERT INTO client (idCli, nomCli, prenomCli, email, idRegion) VALUES (1, 'Dupont', 'Jean', 'jean.dupont@email.com', 1)")
    db.execSQL("INSERT INTO client (idCli, nomCli, prenomCli, email, idRegion) VALUES (2, 'Martin', 'Marie', 'marie.martin@email.com', 2)")
    db.execSQL("INSERT INTO client (idCli, nomCli, prenomCli, email, idRegion) VALUES (3, 'Bernard', 'Pierre', 'pierre.bernard@email.com', 3)")
    db.execSQL("INSERT INTO client (idCli, nomCli, prenomCli, email, idRegion) VALUES (4, 'Petit', 'Sophie', 'sophie.petit@email.com', 4)")
    db.execSQL("INSERT INTO client (idCli, nomCli, prenomCli, email, idRegion) VALUES (5, 'Robert', 'Luc', 'luc.robert@email.com', 5)")
    db.execSQL("INSERT INTO client (idCli, nomCli, prenomCli, email, idRegion) VALUES (6, 'Moreau', 'Claire', 'claire.moreau@email.com', 1)")
    db.execSQL("INSERT INTO client (idCli, nomCli, prenomCli, email, idRegion) VALUES (7, 'Simon', 'Paul', 'paul.simon@email.com', 2)")
    db.execSQL("INSERT INTO client (idCli, nomCli, prenomCli, email, idRegion) VALUES (8, 'Laurent', 'Anne', 'anne.laurent@email.com', 3)")

    // Products
    db.execSQL("INSERT INTO produit (idProd, designProd, qteStock, prixUProd) VALUES (1, 'Laptop HP ProBook', 50, 899.99)")
    db.execSQL("INSERT INTO produit (idProd, designProd, qteStock, prixUProd) VALUES (2, 'Souris Logitech MX', 200, 79.99)")
    db.execSQL("INSERT INTO produit (idProd, designProd, qteStock, prixUProd) VALUES (3, 'Clavier Mécanique', 150, 129.99)")
    db.execSQL("INSERT INTO produit (idProd, designProd, qteStock, prixUProd) VALUES (4, 'Écran Dell 27\"', 80, 349.99)")
    db.execSQL("INSERT INTO produit (idProd, designProd, qteStock, prixUProd) VALUES (5, 'Casque Audio Sony', 120, 199.99)")
    db.execSQL("INSERT INTO produit (idProd, designProd, qteStock, prixUProd) VALUES (6, 'Webcam Logitech C920', 90, 69.99)")
    db.execSQL("INSERT INTO produit (idProd, designProd, qteStock, prixUProd) VALUES (7, 'Disque SSD 1To', 300, 89.99)")
    db.execSQL("INSERT INTO produit (idProd, designProd, qteStock, prixUProd) VALUES (8, 'Câble HDMI 2m', 500, 12.99)")
    db.execSQL("INSERT INTO produit (idProd, designProd, qteStock, prixUProd) VALUES (9, 'Hub USB-C', 180, 45.99)")
    db.execSQL("INSERT INTO produit (idProd, designProd, qteStock, prixUProd) VALUES (10, 'Tapis de Souris XL', 250, 24.99)")
    db.execSQL("INSERT INTO produit (idProd, designProd, qteStock, prixUProd) VALUES (11, 'Support Laptop', 60, 39.99)")
    db.execSQL("INSERT INTO produit (idProd, designProd, qteStock, prixUProd) VALUES (12, 'Imprimante Laser', 40, 249.99)")

    // Orders
    db.execSQL("INSERT INTO commande (idCmd, dateCmd, idCli) VALUES (1, 1696118400000, 1)")
    db.execSQL("INSERT INTO commande (idCmd, dateCmd, idCli) VALUES (2, 1696204800000, 2)")
    db.execSQL("INSERT INTO commande (idCmd, dateCmd, idCli) VALUES (3, 1696291200000, 3)")
    db.execSQL("INSERT INTO commande (idCmd, dateCmd, idCli) VALUES (4, 1696377600000, 1)")
    db.execSQL("INSERT INTO commande (idCmd, dateCmd, idCli) VALUES (5, 1696464000000, 4)")
    db.execSQL("INSERT INTO commande (idCmd, dateCmd, idCli) VALUES (6, 1696550400000, 5)")
    db.execSQL("INSERT INTO commande (idCmd, dateCmd, idCli) VALUES (7, 1696636800000, 6)")
    db.execSQL("INSERT INTO commande (idCmd, dateCmd, idCli) VALUES (8, 1696723200000, 7)")
    db.execSQL("INSERT INTO commande (idCmd, dateCmd, idCli) VALUES (9, 1696809600000, 2)")
    db.execSQL("INSERT INTO commande (idCmd, dateCmd, idCli) VALUES (10, 1696896000000, 8)")

    // Order lines
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (1, 1, 2)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (1, 2, 3)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (1, 8, 5)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (2, 3, 1)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (2, 5, 2)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (3, 4, 1)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (3, 7, 4)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (4, 1, 1)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (4, 6, 2)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (5, 9, 3)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (5, 10, 2)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (6, 2, 5)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (6, 11, 1)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (7, 12, 1)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (7, 3, 2)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (8, 5, 1)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (8, 7, 2)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (9, 1, 3)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (9, 4, 1)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (10, 6, 1)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (10, 8, 10)")
    db.execSQL("INSERT INTO ligne_commande (idCmd, idProd, qteCmd) VALUES (10, 9, 2)")
}
