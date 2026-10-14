package com.groupe25.tpexam3.core.data.local.dao;

import android.database.Cursor;
import android.os.CancellationSignal;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.room.CoroutinesRoom;
import androidx.room.EntityDeletionOrUpdateAdapter;
import androidx.room.EntityInsertionAdapter;
import androidx.room.RoomDatabase;
import androidx.room.RoomSQLiteQuery;
import androidx.room.util.CursorUtil;
import androidx.room.util.DBUtil;
import androidx.sqlite.db.SupportSQLiteStatement;
import com.groupe25.tpexam3.core.data.local.entity.ProductEntity;
import java.lang.Class;
import java.lang.Exception;
import java.lang.Integer;
import java.lang.Long;
import java.lang.Object;
import java.lang.Override;
import java.lang.String;
import java.lang.SuppressWarnings;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.concurrent.Callable;
import javax.annotation.processing.Generated;
import kotlin.Unit;
import kotlin.coroutines.Continuation;
import kotlinx.coroutines.flow.Flow;

@Generated("androidx.room.RoomProcessor")
@SuppressWarnings({"unchecked", "deprecation"})
public final class ProductDao_Impl implements ProductDao {
  private final RoomDatabase __db;

  private final EntityInsertionAdapter<ProductEntity> __insertionAdapterOfProductEntity;

  private final EntityDeletionOrUpdateAdapter<ProductEntity> __deletionAdapterOfProductEntity;

  private final EntityDeletionOrUpdateAdapter<ProductEntity> __updateAdapterOfProductEntity;

  public ProductDao_Impl(@NonNull final RoomDatabase __db) {
    this.__db = __db;
    this.__insertionAdapterOfProductEntity = new EntityInsertionAdapter<ProductEntity>(__db) {
      @Override
      @NonNull
      protected String createQuery() {
        return "INSERT OR REPLACE INTO `produit` (`idProd`,`designProd`,`qteStock`,`prixUProd`,`photoProd`) VALUES (nullif(?, 0),?,?,?,?)";
      }

      @Override
      protected void bind(@NonNull final SupportSQLiteStatement statement,
          @NonNull final ProductEntity entity) {
        statement.bindLong(1, entity.getIdProd());
        statement.bindString(2, entity.getDesignProd());
        statement.bindLong(3, entity.getQteStock());
        statement.bindDouble(4, entity.getPrixUProd());
        if (entity.getPhotoProd() == null) {
          statement.bindNull(5);
        } else {
          statement.bindString(5, entity.getPhotoProd());
        }
      }
    };
    this.__deletionAdapterOfProductEntity = new EntityDeletionOrUpdateAdapter<ProductEntity>(__db) {
      @Override
      @NonNull
      protected String createQuery() {
        return "DELETE FROM `produit` WHERE `idProd` = ?";
      }

      @Override
      protected void bind(@NonNull final SupportSQLiteStatement statement,
          @NonNull final ProductEntity entity) {
        statement.bindLong(1, entity.getIdProd());
      }
    };
    this.__updateAdapterOfProductEntity = new EntityDeletionOrUpdateAdapter<ProductEntity>(__db) {
      @Override
      @NonNull
      protected String createQuery() {
        return "UPDATE OR ABORT `produit` SET `idProd` = ?,`designProd` = ?,`qteStock` = ?,`prixUProd` = ?,`photoProd` = ? WHERE `idProd` = ?";
      }

      @Override
      protected void bind(@NonNull final SupportSQLiteStatement statement,
          @NonNull final ProductEntity entity) {
        statement.bindLong(1, entity.getIdProd());
        statement.bindString(2, entity.getDesignProd());
        statement.bindLong(3, entity.getQteStock());
        statement.bindDouble(4, entity.getPrixUProd());
        if (entity.getPhotoProd() == null) {
          statement.bindNull(5);
        } else {
          statement.bindString(5, entity.getPhotoProd());
        }
        statement.bindLong(6, entity.getIdProd());
      }
    };
  }

  @Override
  public Object insert(final ProductEntity product, final Continuation<? super Long> $completion) {
    return CoroutinesRoom.execute(__db, true, new Callable<Long>() {
      @Override
      @NonNull
      public Long call() throws Exception {
        __db.beginTransaction();
        try {
          final Long _result = __insertionAdapterOfProductEntity.insertAndReturnId(product);
          __db.setTransactionSuccessful();
          return _result;
        } finally {
          __db.endTransaction();
        }
      }
    }, $completion);
  }

  @Override
  public Object delete(final ProductEntity product, final Continuation<? super Unit> $completion) {
    return CoroutinesRoom.execute(__db, true, new Callable<Unit>() {
      @Override
      @NonNull
      public Unit call() throws Exception {
        __db.beginTransaction();
        try {
          __deletionAdapterOfProductEntity.handle(product);
          __db.setTransactionSuccessful();
          return Unit.INSTANCE;
        } finally {
          __db.endTransaction();
        }
      }
    }, $completion);
  }

  @Override
  public Object update(final ProductEntity product, final Continuation<? super Unit> $completion) {
    return CoroutinesRoom.execute(__db, true, new Callable<Unit>() {
      @Override
      @NonNull
      public Unit call() throws Exception {
        __db.beginTransaction();
        try {
          __updateAdapterOfProductEntity.handle(product);
          __db.setTransactionSuccessful();
          return Unit.INSTANCE;
        } finally {
          __db.endTransaction();
        }
      }
    }, $completion);
  }

  @Override
  public Flow<List<ProductEntity>> getAllProducts() {
    final String _sql = "SELECT * FROM produit ORDER BY designProd ASC";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 0);
    return CoroutinesRoom.createFlow(__db, false, new String[] {"produit"}, new Callable<List<ProductEntity>>() {
      @Override
      @NonNull
      public List<ProductEntity> call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfIdProd = CursorUtil.getColumnIndexOrThrow(_cursor, "idProd");
          final int _cursorIndexOfDesignProd = CursorUtil.getColumnIndexOrThrow(_cursor, "designProd");
          final int _cursorIndexOfQteStock = CursorUtil.getColumnIndexOrThrow(_cursor, "qteStock");
          final int _cursorIndexOfPrixUProd = CursorUtil.getColumnIndexOrThrow(_cursor, "prixUProd");
          final int _cursorIndexOfPhotoProd = CursorUtil.getColumnIndexOrThrow(_cursor, "photoProd");
          final List<ProductEntity> _result = new ArrayList<ProductEntity>(_cursor.getCount());
          while (_cursor.moveToNext()) {
            final ProductEntity _item;
            final int _tmpIdProd;
            _tmpIdProd = _cursor.getInt(_cursorIndexOfIdProd);
            final String _tmpDesignProd;
            _tmpDesignProd = _cursor.getString(_cursorIndexOfDesignProd);
            final int _tmpQteStock;
            _tmpQteStock = _cursor.getInt(_cursorIndexOfQteStock);
            final double _tmpPrixUProd;
            _tmpPrixUProd = _cursor.getDouble(_cursorIndexOfPrixUProd);
            final String _tmpPhotoProd;
            if (_cursor.isNull(_cursorIndexOfPhotoProd)) {
              _tmpPhotoProd = null;
            } else {
              _tmpPhotoProd = _cursor.getString(_cursorIndexOfPhotoProd);
            }
            _item = new ProductEntity(_tmpIdProd,_tmpDesignProd,_tmpQteStock,_tmpPrixUProd,_tmpPhotoProd);
            _result.add(_item);
          }
          return _result;
        } finally {
          _cursor.close();
        }
      }

      @Override
      protected void finalize() {
        _statement.release();
      }
    });
  }

  @Override
  public Object getProductById(final int id,
      final Continuation<? super ProductEntity> $completion) {
    final String _sql = "SELECT * FROM produit WHERE idProd = ?";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 1);
    int _argIndex = 1;
    _statement.bindLong(_argIndex, id);
    final CancellationSignal _cancellationSignal = DBUtil.createCancellationSignal();
    return CoroutinesRoom.execute(__db, false, _cancellationSignal, new Callable<ProductEntity>() {
      @Override
      @Nullable
      public ProductEntity call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfIdProd = CursorUtil.getColumnIndexOrThrow(_cursor, "idProd");
          final int _cursorIndexOfDesignProd = CursorUtil.getColumnIndexOrThrow(_cursor, "designProd");
          final int _cursorIndexOfQteStock = CursorUtil.getColumnIndexOrThrow(_cursor, "qteStock");
          final int _cursorIndexOfPrixUProd = CursorUtil.getColumnIndexOrThrow(_cursor, "prixUProd");
          final int _cursorIndexOfPhotoProd = CursorUtil.getColumnIndexOrThrow(_cursor, "photoProd");
          final ProductEntity _result;
          if (_cursor.moveToFirst()) {
            final int _tmpIdProd;
            _tmpIdProd = _cursor.getInt(_cursorIndexOfIdProd);
            final String _tmpDesignProd;
            _tmpDesignProd = _cursor.getString(_cursorIndexOfDesignProd);
            final int _tmpQteStock;
            _tmpQteStock = _cursor.getInt(_cursorIndexOfQteStock);
            final double _tmpPrixUProd;
            _tmpPrixUProd = _cursor.getDouble(_cursorIndexOfPrixUProd);
            final String _tmpPhotoProd;
            if (_cursor.isNull(_cursorIndexOfPhotoProd)) {
              _tmpPhotoProd = null;
            } else {
              _tmpPhotoProd = _cursor.getString(_cursorIndexOfPhotoProd);
            }
            _result = new ProductEntity(_tmpIdProd,_tmpDesignProd,_tmpQteStock,_tmpPrixUProd,_tmpPhotoProd);
          } else {
            _result = null;
          }
          return _result;
        } finally {
          _cursor.close();
          _statement.release();
        }
      }
    }, $completion);
  }

  @Override
  public Flow<List<ProductEntity>> searchProducts(final String query) {
    final String _sql = "SELECT * FROM produit WHERE designProd LIKE '%' || ? || '%'";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 1);
    int _argIndex = 1;
    _statement.bindString(_argIndex, query);
    return CoroutinesRoom.createFlow(__db, false, new String[] {"produit"}, new Callable<List<ProductEntity>>() {
      @Override
      @NonNull
      public List<ProductEntity> call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfIdProd = CursorUtil.getColumnIndexOrThrow(_cursor, "idProd");
          final int _cursorIndexOfDesignProd = CursorUtil.getColumnIndexOrThrow(_cursor, "designProd");
          final int _cursorIndexOfQteStock = CursorUtil.getColumnIndexOrThrow(_cursor, "qteStock");
          final int _cursorIndexOfPrixUProd = CursorUtil.getColumnIndexOrThrow(_cursor, "prixUProd");
          final int _cursorIndexOfPhotoProd = CursorUtil.getColumnIndexOrThrow(_cursor, "photoProd");
          final List<ProductEntity> _result = new ArrayList<ProductEntity>(_cursor.getCount());
          while (_cursor.moveToNext()) {
            final ProductEntity _item;
            final int _tmpIdProd;
            _tmpIdProd = _cursor.getInt(_cursorIndexOfIdProd);
            final String _tmpDesignProd;
            _tmpDesignProd = _cursor.getString(_cursorIndexOfDesignProd);
            final int _tmpQteStock;
            _tmpQteStock = _cursor.getInt(_cursorIndexOfQteStock);
            final double _tmpPrixUProd;
            _tmpPrixUProd = _cursor.getDouble(_cursorIndexOfPrixUProd);
            final String _tmpPhotoProd;
            if (_cursor.isNull(_cursorIndexOfPhotoProd)) {
              _tmpPhotoProd = null;
            } else {
              _tmpPhotoProd = _cursor.getString(_cursorIndexOfPhotoProd);
            }
            _item = new ProductEntity(_tmpIdProd,_tmpDesignProd,_tmpQteStock,_tmpPrixUProd,_tmpPhotoProd);
            _result.add(_item);
          }
          return _result;
        } finally {
          _cursor.close();
        }
      }

      @Override
      protected void finalize() {
        _statement.release();
      }
    });
  }

  @Override
  public Object getProductCount(final Continuation<? super Integer> $completion) {
    final String _sql = "SELECT COUNT(*) FROM produit";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 0);
    final CancellationSignal _cancellationSignal = DBUtil.createCancellationSignal();
    return CoroutinesRoom.execute(__db, false, _cancellationSignal, new Callable<Integer>() {
      @Override
      @NonNull
      public Integer call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final Integer _result;
          if (_cursor.moveToFirst()) {
            final int _tmp;
            _tmp = _cursor.getInt(0);
            _result = _tmp;
          } else {
            _result = 0;
          }
          return _result;
        } finally {
          _cursor.close();
          _statement.release();
        }
      }
    }, $completion);
  }

  @NonNull
  public static List<Class<?>> getRequiredConverters() {
    return Collections.emptyList();
  }
}
