package com.groupe25.tpexam3.feature.order.data.repository

import com.groupe25.tpexam3.core.data.local.dao.CommandeDao
import com.groupe25.tpexam3.core.data.local.dao.LigneCommandeDao
import com.groupe25.tpexam3.core.data.local.entity.CommandeEntity
import com.groupe25.tpexam3.core.data.local.entity.LigneCommandeEntity
import com.groupe25.tpexam3.feature.order.domain.model.Order
import com.groupe25.tpexam3.feature.order.domain.model.OrderLine
import com.groupe25.tpexam3.feature.order.domain.repository.OrderRepository
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.map

class OrderRepositoryImpl(
    private val commandeDao: CommandeDao,
    private val ligneCommandeDao: LigneCommandeDao
) : OrderRepository {

    override fun getAllOrders(): Flow<List<Order>> {
        return commandeDao.getAllOrdersWithDetails().map { list ->
            list.map {
                Order(
                    id = it.idCmd,
                    date = it.dateCmd,
                    clientId = it.idCli,
                    clientName = "${it.prenomCli ?: ""} ${it.nomCli ?: ""}".trim(),
                    itemCount = it.itemCount,
                    totalAmount = it.totalAmount
                )
            }
        }
    }

    override fun getOrderLines(orderId: Int): Flow<List<OrderLine>> {
        return ligneCommandeDao.getOrderLinesByOrderId(orderId).map { list ->
            list.map {
                OrderLine(
                    orderId = it.idCmd,
                    productId = it.idProd,
                    quantity = it.qteCmd,
                    productName = it.designProd,
                    unitPrice = it.prixUProd,
                    photoPath = it.photoProd
                )
            }
        }
    }

    override suspend fun createOrder(date: Long, clientId: Int): Long {
        return commandeDao.insert(CommandeEntity(dateCmd = date, idCli = clientId))
    }

    override suspend fun addProductToOrder(orderId: Int, productId: Int, quantity: Int) {
        ligneCommandeDao.insert(LigneCommandeEntity(idCmd = orderId, idProd = productId, qteCmd = quantity))
    }

    override suspend fun removeProductFromOrder(orderId: Int, productId: Int) {
        ligneCommandeDao.delete(orderId, productId)
    }

    override suspend fun deleteOrder(orderId: Int) {
        commandeDao.deleteById(orderId)
    }
}
