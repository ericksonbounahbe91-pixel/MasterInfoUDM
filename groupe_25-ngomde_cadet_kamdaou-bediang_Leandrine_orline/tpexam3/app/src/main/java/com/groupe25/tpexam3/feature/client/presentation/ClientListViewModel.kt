package com.groupe25.tpexam3.feature.client.presentation

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.groupe25.tpexam3.core.data.local.dao.ClientDao
import com.groupe25.tpexam3.core.data.local.dao.ClientWithRegion
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.flow.stateIn

data class ClientListState(
    val clients: List<ClientWithRegion> = emptyList(),
    val isLoading: Boolean = true
)

class ClientListViewModel(clientDao: ClientDao) : ViewModel() {

    val state = clientDao.getAllClientsWithRegion()
        .map { ClientListState(clients = it, isLoading = false) }
        .stateIn(viewModelScope, SharingStarted.WhileSubscribed(5000), ClientListState())
}
