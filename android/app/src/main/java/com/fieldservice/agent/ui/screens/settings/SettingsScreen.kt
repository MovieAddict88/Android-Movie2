package com.fieldservice.agent.ui.screens.settings

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.res.stringResource
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.fieldservice.agent.R
import com.fieldservice.agent.worker.SyncWorker

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun SettingsScreen(
    onBack: () -> Unit,
    onLogout: () -> Unit,
    viewModel: SettingsViewModel = hiltViewModel()
) {
    val uiState by viewModel.uiState.collectAsState()
    var showLogoutDialog by remember { mutableStateOf(false) }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text(stringResource(R.string.settings)) },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.Default.ArrowBack, contentDescription = "Back")
                    }
                }
            )
        }
    ) { paddingValues ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
        ) {
            ListItem(
                headlineContent = { Text(stringResource(R.string.sync_now)) },
                supportingContent = {
                    Text(
                        if (uiState.lastSyncTime != null) {
                            "Last sync: ${uiState.lastSyncTime}"
                        } else {
                            "Tap to sync now"
                        }
                    )
                },
                leadingContent = {
                    Icon(Icons.Default.Sync, contentDescription = null)
                },
                trailingContent = {
                    if (uiState.isSyncing) {
                        CircularProgressIndicator(modifier = Modifier.size(24.dp))
                    }
                },
                modifier = Modifier.clickable(enabled = !uiState.isSyncing) {
                    viewModel.triggerSync()
                }
            )

            HorizontalDivider()

            ListItem(
                headlineContent = { Text("App Version") },
                supportingContent = { Text(uiState.appVersion) },
                leadingContent = {
                    Icon(Icons.Default.Info, contentDescription = null)
                }
            )

            HorizontalDivider()

            ListItem(
                headlineContent = { Text("Sync Status") },
                supportingContent = {
                    Text(
                        if (uiState.pendingSyncs > 0) {
                            "${uiState.pendingSyncs} items pending upload"
                        } else {
                            "All synced"
                        }
                    )
                },
                leadingContent = {
                    Icon(
                        if (uiState.pendingSyncs > 0) Icons.Default.CloudOff else Icons.Default.CloudDone,
                        contentDescription = null,
                        tint = if (uiState.pendingSyncs > 0)
                            MaterialTheme.colorScheme.error
                        else
                            MaterialTheme.colorScheme.primary
                    )
                }
            )

            HorizontalDivider()

            Spacer(modifier = Modifier.weight(1f))

            HorizontalDivider()

            ListItem(
                headlineContent = {
                    Text(
                        text = stringResource(R.string.logout),
                        color = MaterialTheme.colorScheme.error
                    )
                },
                leadingContent = {
                    Icon(
                        Icons.Default.Logout,
                        contentDescription = null,
                        tint = MaterialTheme.colorScheme.error
                    )
                },
                modifier = Modifier.clickable { showLogoutDialog = true }
            )
        }

        if (showLogoutDialog) {
            AlertDialog(
                onDismissRequest = { showLogoutDialog = false },
                title = { Text("Logout") },
                text = { Text("Are you sure you want to logout? Any pending sync data will be uploaded first.") },
                confirmButton = {
                    TextButton(
                        onClick = {
                            showLogoutDialog = false
                            onLogout()
                        }
                    ) {
                        Text("Logout")
                    }
                },
                dismissButton = {
                    TextButton(onClick = { showLogoutDialog = false }) {
                        Text("Cancel")
                    }
                }
            )
        }
    }
}
