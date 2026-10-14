package com.groupe25.tpexam3.core.data.local;

import androidx.annotation.NonNull;
import androidx.room.DatabaseConfiguration;
import androidx.room.InvalidationTracker;
import androidx.room.RoomDatabase;
import androidx.room.RoomOpenHelper;
import androidx.room.migration.AutoMigrationSpec;
import androidx.room.migration.Migration;
import androidx.room.util.DBUtil;
import androidx.room.util.TableInfo;
import androidx.sqlite.db.SupportSQLiteDatabase;
import androidx.sqlite.db.SupportSQLiteOpenHelper;
import com.groupe25.tpexam3.core.data.local.dao.ProductDao;
import com.groupe25.tpexam3.core.data.local.dao.ProductDao_Impl;
import com.groupe25.tpexam3.core.data.local.dao.UserDao;
import com.groupe25.tpexam3.core.data.local.dao.UserDao_Impl;
import java.lang.Class;
import java.lang.Override;
import java.lang.String;
import java.lang.SuppressWarnings;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;
import javax.annotation.processing.Generated;

@Generated("androidx.room.RoomProcessor")
@SuppressWarnings({"unchecked", "deprecation"})
public final class AppDatabase_Impl extends AppDatabase {
  private volatile UserDao _userDao;

  private volatile ProductDao _productDao;

  @Override
  @NonNull
  protected SupportSQLiteOpenHelper createOpenHelper(@NonNull final DatabaseConfiguration config) {
    final SupportSQLiteOpenHelper.Callback _openCallback = new RoomOpenHelper(config, new RoomOpenHelper.Delegate(1) {
      @Override
      public void createAllTables(@NonNull final SupportSQLiteDatabase db) {
        db.execSQL("CREATE TABLE IF NOT EXISTS `users` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, `login` TEXT NOT NULL, `password` TEXT NOT NULL)");
        db.execSQL("CREATE TABLE IF NOT EXISTS `departement` (`idDep` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, `nomDep` TEXT NOT NULL)");
        db.execSQL("CREATE TABLE IF NOT EXISTS `region` (`idRegion` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, `nomRegion` TEXT NOT NULL, `idDep` INTEGER NOT NULL, FOREIGN KEY(`idDep`) REFERENCES `departement`(`idDep`) ON UPDATE NO ACTION ON DELETE CASCADE )");
        db.execSQL("CREATE INDEX IF NOT EXISTS `index_region_idDep` ON `region` (`idDep`)");
        db.execSQL("CREATE TABLE IF NOT EXISTS `client` (`idCli` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, `nomCli` TEXT NOT NULL, `prenomCli` TEXT NOT NULL, `email` TEXT NOT NULL, `photo` TEXT, `idRegion` INTEGER NOT NULL, FOREIGN KEY(`idRegion`) REFERENCES `region`(`idRegion`) ON UPDATE NO ACTION ON DELETE CASCADE )");
        db.execSQL("CREATE INDEX IF NOT EXISTS `index_client_idRegion` ON `client` (`idRegion`)");
        db.execSQL("CREATE TABLE IF NOT EXISTS `produit` (`idProd` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, `designProd` TEXT NOT NULL, `qteStock` INTEGER NOT NULL, `prixUProd` REAL NOT NULL, `photoProd` TEXT)");
        db.execSQL("CREATE TABLE IF NOT EXISTS `commande` (`idCmd` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, `dateCmd` INTEGER NOT NULL, `idCli` INTEGER NOT NULL, FOREIGN KEY(`idCli`) REFERENCES `client`(`idCli`) ON UPDATE NO ACTION ON DELETE CASCADE )");
        db.execSQL("CREATE INDEX IF NOT EXISTS `index_commande_idCli` ON `commande` (`idCli`)");
        db.execSQL("CREATE TABLE IF NOT EXISTS `ligne_commande` (`idCmd` INTEGER NOT NULL, `idProd` INTEGER NOT NULL, `qteCmd` INTEGER NOT NULL, PRIMARY KEY(`idCmd`, `idProd`), FOREIGN KEY(`idCmd`) REFERENCES `commande`(`idCmd`) ON UPDATE NO ACTION ON DELETE CASCADE , FOREIGN KEY(`idProd`) REFERENCES `produit`(`idProd`) ON UPDATE NO ACTION ON DELETE CASCADE )");
        db.execSQL("CREATE INDEX IF NOT EXISTS `index_ligne_commande_idCmd` ON `ligne_commande` (`idCmd`)");
        db.execSQL("CREATE INDEX IF NOT EXISTS `index_ligne_commande_idProd` ON `ligne_commande` (`idProd`)");
        db.execSQL("CREATE TABLE IF NOT EXISTS room_master_table (id INTEGER PRIMARY KEY,identity_hash TEXT)");
        db.execSQL("INSERT OR REPLACE INTO room_master_table (id,identity_hash) VALUES(42, '64e3cf6357836aaf910b8ae0417df867')");
      }

      @Override
      public void dropAllTables(@NonNull final SupportSQLiteDatabase db) {
        db.execSQL("DROP TABLE IF EXISTS `users`");
        db.execSQL("DROP TABLE IF EXISTS `departement`");
        db.execSQL("DROP TABLE IF EXISTS `region`");
        db.execSQL("DROP TABLE IF EXISTS `client`");
        db.execSQL("DROP TABLE IF EXISTS `produit`");
        db.execSQL("DROP TABLE IF EXISTS `commande`");
        db.execSQL("DROP TABLE IF EXISTS `ligne_commande`");
        final List<? extends RoomDatabase.Callback> _callbacks = mCallbacks;
        if (_callbacks != null) {
          for (RoomDatabase.Callback _callback : _callbacks) {
            _callback.onDestructiveMigration(db);
          }
        }
      }

      @Override
      public void onCreate(@NonNull final SupportSQLiteDatabase db) {
        final List<? extends RoomDatabase.Callback> _callbacks = mCallbacks;
        if (_callbacks != null) {
          for (RoomDatabase.Callback _callback : _callbacks) {
            _callback.onCreate(db);
          }
        }
      }

      @Override
      public void onOpen(@NonNull final SupportSQLiteDatabase db) {
        mDatabase = db;
        db.execSQL("PRAGMA foreign_keys = ON");
        internalInitInvalidationTracker(db);
        final List<? extends RoomDatabase.Callback> _callbacks = mCallbacks;
        if (_callbacks != null) {
          for (RoomDatabase.Callback _callback : _callbacks) {
            _callback.onOpen(db);
          }
        }
      }

      @Override
      public void onPreMigrate(@NonNull final SupportSQLiteDatabase db) {
        DBUtil.dropFtsSyncTriggers(db);
      }

      @Override
      public void onPostMigrate(@NonNull final SupportSQLiteDatabase db) {
      }

      @Override
      @NonNull
      public RoomOpenHelper.ValidationResult onValidateSchema(
          @NonNull final SupportSQLiteDatabase db) {
        final HashMap<String, TableInfo.Column> _columnsUsers = new HashMap<String, TableInfo.Column>(3);
        _columnsUsers.put("id", new TableInfo.Column("id", "INTEGER", true, 1, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsUsers.put("login", new TableInfo.Column("login", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsUsers.put("password", new TableInfo.Column("password", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        final HashSet<TableInfo.ForeignKey> _foreignKeysUsers = new HashSet<TableInfo.ForeignKey>(0);
        final HashSet<TableInfo.Index> _indicesUsers = new HashSet<TableInfo.Index>(0);
        final TableInfo _infoUsers = new TableInfo("users", _columnsUsers, _foreignKeysUsers, _indicesUsers);
        final TableInfo _existingUsers = TableInfo.read(db, "users");
        if (!_infoUsers.equals(_existingUsers)) {
          return new RoomOpenHelper.ValidationResult(false, "users(com.groupe25.tpexam3.core.data.local.entity.UserEntity).\n"
                  + " Expected:\n" + _infoUsers + "\n"
                  + " Found:\n" + _existingUsers);
        }
        final HashMap<String, TableInfo.Column> _columnsDepartement = new HashMap<String, TableInfo.Column>(2);
        _columnsDepartement.put("idDep", new TableInfo.Column("idDep", "INTEGER", true, 1, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsDepartement.put("nomDep", new TableInfo.Column("nomDep", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        final HashSet<TableInfo.ForeignKey> _foreignKeysDepartement = new HashSet<TableInfo.ForeignKey>(0);
        final HashSet<TableInfo.Index> _indicesDepartement = new HashSet<TableInfo.Index>(0);
        final TableInfo _infoDepartement = new TableInfo("departement", _columnsDepartement, _foreignKeysDepartement, _indicesDepartement);
        final TableInfo _existingDepartement = TableInfo.read(db, "departement");
        if (!_infoDepartement.equals(_existingDepartement)) {
          return new RoomOpenHelper.ValidationResult(false, "departement(com.groupe25.tpexam3.core.data.local.entity.DepartmentEntity).\n"
                  + " Expected:\n" + _infoDepartement + "\n"
                  + " Found:\n" + _existingDepartement);
        }
        final HashMap<String, TableInfo.Column> _columnsRegion = new HashMap<String, TableInfo.Column>(3);
        _columnsRegion.put("idRegion", new TableInfo.Column("idRegion", "INTEGER", true, 1, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsRegion.put("nomRegion", new TableInfo.Column("nomRegion", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsRegion.put("idDep", new TableInfo.Column("idDep", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        final HashSet<TableInfo.ForeignKey> _foreignKeysRegion = new HashSet<TableInfo.ForeignKey>(1);
        _foreignKeysRegion.add(new TableInfo.ForeignKey("departement", "CASCADE", "NO ACTION", Arrays.asList("idDep"), Arrays.asList("idDep")));
        final HashSet<TableInfo.Index> _indicesRegion = new HashSet<TableInfo.Index>(1);
        _indicesRegion.add(new TableInfo.Index("index_region_idDep", false, Arrays.asList("idDep"), Arrays.asList("ASC")));
        final TableInfo _infoRegion = new TableInfo("region", _columnsRegion, _foreignKeysRegion, _indicesRegion);
        final TableInfo _existingRegion = TableInfo.read(db, "region");
        if (!_infoRegion.equals(_existingRegion)) {
          return new RoomOpenHelper.ValidationResult(false, "region(com.groupe25.tpexam3.core.data.local.entity.RegionEntity).\n"
                  + " Expected:\n" + _infoRegion + "\n"
                  + " Found:\n" + _existingRegion);
        }
        final HashMap<String, TableInfo.Column> _columnsClient = new HashMap<String, TableInfo.Column>(6);
        _columnsClient.put("idCli", new TableInfo.Column("idCli", "INTEGER", true, 1, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsClient.put("nomCli", new TableInfo.Column("nomCli", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsClient.put("prenomCli", new TableInfo.Column("prenomCli", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsClient.put("email", new TableInfo.Column("email", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsClient.put("photo", new TableInfo.Column("photo", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsClient.put("idRegion", new TableInfo.Column("idRegion", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        final HashSet<TableInfo.ForeignKey> _foreignKeysClient = new HashSet<TableInfo.ForeignKey>(1);
        _foreignKeysClient.add(new TableInfo.ForeignKey("region", "CASCADE", "NO ACTION", Arrays.asList("idRegion"), Arrays.asList("idRegion")));
        final HashSet<TableInfo.Index> _indicesClient = new HashSet<TableInfo.Index>(1);
        _indicesClient.add(new TableInfo.Index("index_client_idRegion", false, Arrays.asList("idRegion"), Arrays.asList("ASC")));
        final TableInfo _infoClient = new TableInfo("client", _columnsClient, _foreignKeysClient, _indicesClient);
        final TableInfo _existingClient = TableInfo.read(db, "client");
        if (!_infoClient.equals(_existingClient)) {
          return new RoomOpenHelper.ValidationResult(false, "client(com.groupe25.tpexam3.core.data.local.entity.ClientEntity).\n"
                  + " Expected:\n" + _infoClient + "\n"
                  + " Found:\n" + _existingClient);
        }
        final HashMap<String, TableInfo.Column> _columnsProduit = new HashMap<String, TableInfo.Column>(5);
        _columnsProduit.put("idProd", new TableInfo.Column("idProd", "INTEGER", true, 1, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsProduit.put("designProd", new TableInfo.Column("designProd", "TEXT", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsProduit.put("qteStock", new TableInfo.Column("qteStock", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsProduit.put("prixUProd", new TableInfo.Column("prixUProd", "REAL", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsProduit.put("photoProd", new TableInfo.Column("photoProd", "TEXT", false, 0, null, TableInfo.CREATED_FROM_ENTITY));
        final HashSet<TableInfo.ForeignKey> _foreignKeysProduit = new HashSet<TableInfo.ForeignKey>(0);
        final HashSet<TableInfo.Index> _indicesProduit = new HashSet<TableInfo.Index>(0);
        final TableInfo _infoProduit = new TableInfo("produit", _columnsProduit, _foreignKeysProduit, _indicesProduit);
        final TableInfo _existingProduit = TableInfo.read(db, "produit");
        if (!_infoProduit.equals(_existingProduit)) {
          return new RoomOpenHelper.ValidationResult(false, "produit(com.groupe25.tpexam3.core.data.local.entity.ProductEntity).\n"
                  + " Expected:\n" + _infoProduit + "\n"
                  + " Found:\n" + _existingProduit);
        }
        final HashMap<String, TableInfo.Column> _columnsCommande = new HashMap<String, TableInfo.Column>(3);
        _columnsCommande.put("idCmd", new TableInfo.Column("idCmd", "INTEGER", true, 1, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsCommande.put("dateCmd", new TableInfo.Column("dateCmd", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsCommande.put("idCli", new TableInfo.Column("idCli", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        final HashSet<TableInfo.ForeignKey> _foreignKeysCommande = new HashSet<TableInfo.ForeignKey>(1);
        _foreignKeysCommande.add(new TableInfo.ForeignKey("client", "CASCADE", "NO ACTION", Arrays.asList("idCli"), Arrays.asList("idCli")));
        final HashSet<TableInfo.Index> _indicesCommande = new HashSet<TableInfo.Index>(1);
        _indicesCommande.add(new TableInfo.Index("index_commande_idCli", false, Arrays.asList("idCli"), Arrays.asList("ASC")));
        final TableInfo _infoCommande = new TableInfo("commande", _columnsCommande, _foreignKeysCommande, _indicesCommande);
        final TableInfo _existingCommande = TableInfo.read(db, "commande");
        if (!_infoCommande.equals(_existingCommande)) {
          return new RoomOpenHelper.ValidationResult(false, "commande(com.groupe25.tpexam3.core.data.local.entity.CommandeEntity).\n"
                  + " Expected:\n" + _infoCommande + "\n"
                  + " Found:\n" + _existingCommande);
        }
        final HashMap<String, TableInfo.Column> _columnsLigneCommande = new HashMap<String, TableInfo.Column>(3);
        _columnsLigneCommande.put("idCmd", new TableInfo.Column("idCmd", "INTEGER", true, 1, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsLigneCommande.put("idProd", new TableInfo.Column("idProd", "INTEGER", true, 2, null, TableInfo.CREATED_FROM_ENTITY));
        _columnsLigneCommande.put("qteCmd", new TableInfo.Column("qteCmd", "INTEGER", true, 0, null, TableInfo.CREATED_FROM_ENTITY));
        final HashSet<TableInfo.ForeignKey> _foreignKeysLigneCommande = new HashSet<TableInfo.ForeignKey>(2);
        _foreignKeysLigneCommande.add(new TableInfo.ForeignKey("commande", "CASCADE", "NO ACTION", Arrays.asList("idCmd"), Arrays.asList("idCmd")));
        _foreignKeysLigneCommande.add(new TableInfo.ForeignKey("produit", "CASCADE", "NO ACTION", Arrays.asList("idProd"), Arrays.asList("idProd")));
        final HashSet<TableInfo.Index> _indicesLigneCommande = new HashSet<TableInfo.Index>(2);
        _indicesLigneCommande.add(new TableInfo.Index("index_ligne_commande_idCmd", false, Arrays.asList("idCmd"), Arrays.asList("ASC")));
        _indicesLigneCommande.add(new TableInfo.Index("index_ligne_commande_idProd", false, Arrays.asList("idProd"), Arrays.asList("ASC")));
        final TableInfo _infoLigneCommande = new TableInfo("ligne_commande", _columnsLigneCommande, _foreignKeysLigneCommande, _indicesLigneCommande);
        final TableInfo _existingLigneCommande = TableInfo.read(db, "ligne_commande");
        if (!_infoLigneCommande.equals(_existingLigneCommande)) {
          return new RoomOpenHelper.ValidationResult(false, "ligne_commande(com.groupe25.tpexam3.core.data.local.entity.LigneCommandeEntity).\n"
                  + " Expected:\n" + _infoLigneCommande + "\n"
                  + " Found:\n" + _existingLigneCommande);
        }
        return new RoomOpenHelper.ValidationResult(true, null);
      }
    }, "64e3cf6357836aaf910b8ae0417df867", "7cbe2beb216534bc99f938ab9d985958");
    final SupportSQLiteOpenHelper.Configuration _sqliteConfig = SupportSQLiteOpenHelper.Configuration.builder(config.context).name(config.name).callback(_openCallback).build();
    final SupportSQLiteOpenHelper _helper = config.sqliteOpenHelperFactory.create(_sqliteConfig);
    return _helper;
  }

  @Override
  @NonNull
  protected InvalidationTracker createInvalidationTracker() {
    final HashMap<String, String> _shadowTablesMap = new HashMap<String, String>(0);
    final HashMap<String, Set<String>> _viewTables = new HashMap<String, Set<String>>(0);
    return new InvalidationTracker(this, _shadowTablesMap, _viewTables, "users","departement","region","client","produit","commande","ligne_commande");
  }

  @Override
  public void clearAllTables() {
    super.assertNotMainThread();
    final SupportSQLiteDatabase _db = super.getOpenHelper().getWritableDatabase();
    final boolean _supportsDeferForeignKeys = android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.LOLLIPOP;
    try {
      if (!_supportsDeferForeignKeys) {
        _db.execSQL("PRAGMA foreign_keys = FALSE");
      }
      super.beginTransaction();
      if (_supportsDeferForeignKeys) {
        _db.execSQL("PRAGMA defer_foreign_keys = TRUE");
      }
      _db.execSQL("DELETE FROM `users`");
      _db.execSQL("DELETE FROM `departement`");
      _db.execSQL("DELETE FROM `region`");
      _db.execSQL("DELETE FROM `client`");
      _db.execSQL("DELETE FROM `produit`");
      _db.execSQL("DELETE FROM `commande`");
      _db.execSQL("DELETE FROM `ligne_commande`");
      super.setTransactionSuccessful();
    } finally {
      super.endTransaction();
      if (!_supportsDeferForeignKeys) {
        _db.execSQL("PRAGMA foreign_keys = TRUE");
      }
      _db.query("PRAGMA wal_checkpoint(FULL)").close();
      if (!_db.inTransaction()) {
        _db.execSQL("VACUUM");
      }
    }
  }

  @Override
  @NonNull
  protected Map<Class<?>, List<Class<?>>> getRequiredTypeConverters() {
    final HashMap<Class<?>, List<Class<?>>> _typeConvertersMap = new HashMap<Class<?>, List<Class<?>>>();
    _typeConvertersMap.put(UserDao.class, UserDao_Impl.getRequiredConverters());
    _typeConvertersMap.put(ProductDao.class, ProductDao_Impl.getRequiredConverters());
    return _typeConvertersMap;
  }

  @Override
  @NonNull
  public Set<Class<? extends AutoMigrationSpec>> getRequiredAutoMigrationSpecs() {
    final HashSet<Class<? extends AutoMigrationSpec>> _autoMigrationSpecsSet = new HashSet<Class<? extends AutoMigrationSpec>>();
    return _autoMigrationSpecsSet;
  }

  @Override
  @NonNull
  public List<Migration> getAutoMigrations(
      @NonNull final Map<Class<? extends AutoMigrationSpec>, AutoMigrationSpec> autoMigrationSpecs) {
    final List<Migration> _autoMigrations = new ArrayList<Migration>();
    return _autoMigrations;
  }

  @Override
  public UserDao userDao() {
    if (_userDao != null) {
      return _userDao;
    } else {
      synchronized(this) {
        if(_userDao == null) {
          _userDao = new UserDao_Impl(this);
        }
        return _userDao;
      }
    }
  }

  @Override
  public ProductDao productDao() {
    if (_productDao != null) {
      return _productDao;
    } else {
      synchronized(this) {
        if(_productDao == null) {
          _productDao = new ProductDao_Impl(this);
        }
        return _productDao;
      }
    }
  }
}
