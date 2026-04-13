package com.example.carrental.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.example.carrental.model.Payment
import com.example.carrental.viewmodel.PaymentViewModel

@Composable
fun AdminPaymentScreen(viewModel: PaymentViewModel) {
    val payments by viewModel.payments.collectAsState()
    val loading by viewModel.loading.collectAsState()

    LaunchedEffect(Unit) {
        viewModel.fetchAllPayments()
    }

    Box(modifier = Modifier.fillMaxSize()) {
        if (loading && payments.isEmpty()) {
            CircularProgressIndicator(modifier = Modifier.align(androidx.compose.ui.Alignment.Center))
        } else {
            LazyColumn(
                contentPadding = PaddingValues(16.dp),
                verticalArrangement = Arrangement.spacedBy(16.dp)
            ) {
                items(payments) { payment ->
                    PaymentItem(payment = payment, onAction = { action, reason ->
                        viewModel.updatePaymentStatus(payment.id, action, reason)
                    })
                }
            }
        }
    }
}

@Composable
fun PaymentItem(payment: Payment, onAction: (String, String?) -> Unit) {
    var showRejectDialog by remember { mutableStateOf(false) }
    var rejectionReason by remember { mutableStateOf("") }

    Card(
        modifier = Modifier.fillMaxWidth(),
        elevation = CardDefaults.cardElevation(defaultElevation = 4.dp)
    ) {
        Column(modifier = Modifier.padding(16.dp)) {
            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                Text(text = "Payment ID: ${payment.id}", style = MaterialTheme.typography.titleMedium)
                StatusBadge(status = payment.status)
            }
            Spacer(modifier = Modifier.height(8.dp))
            Text(text = "User: ${payment.user_name}")
            Text(text = "Method: ${payment.payment_method}")
            Text(text = "Ref #: ${payment.reference_number}")
            Text(text = "Amount: $${payment.amount}", style = MaterialTheme.typography.titleSmall)
            
            if (payment.status == "pending") {
                Row(
                    modifier = Modifier.fillMaxWidth().padding(top = 16.dp),
                    horizontalArrangement = Arrangement.End
                ) {
                    TextButton(
                        onClick = { showRejectDialog = true },
                        colors = ButtonDefaults.textButtonColors(contentColor = MaterialTheme.colorScheme.error)
                    ) {
                        Text("Reject")
                    }
                    Spacer(modifier = Modifier.width(8.dp))
                    Button(
                        onClick = { onAction("approved", null) }
                    ) {
                        Text("Approve")
                    }
                }
            } else if (payment.status == "rejected" && !payment.rejection_reason.isNullOrEmpty()) {
                Text(
                    text = "Reason: ${payment.rejection_reason}",
                    color = MaterialTheme.colorScheme.error,
                    style = MaterialTheme.typography.bodySmall,
                    modifier = Modifier.padding(top = 8.dp)
                )
            }
        }
    }

    if (showRejectDialog) {
        AlertDialog(
            onDismissRequest = { showRejectDialog = false },
            title = { Text("Reject Payment") },
            text = {
                OutlinedTextField(
                    value = rejectionReason,
                    onValueChange = { rejectionReason = it },
                    label = { Text("Reason for rejection") },
                    modifier = Modifier.fillMaxWidth()
                )
            },
            confirmButton = {
                Button(onClick = {
                    onAction("rejected", rejectionReason)
                    showRejectDialog = false
                }) {
                    Text("Reject")
                }
            },
            dismissButton = {
                TextButton(onClick = { showRejectDialog = false }) {
                    Text("Cancel")
                }
            }
        )
    }
}

@Composable
fun StatusBadge(status: String) {
    val color = when (status) {
        "approved" -> MaterialTheme.colorScheme.primary
        "rejected" -> MaterialTheme.colorScheme.error
        else -> MaterialTheme.colorScheme.secondary
    }
    Surface(
        color = color.copy(alpha = 0.1f),
        shape = MaterialTheme.shapes.extraSmall
    ) {
        Text(
            text = status.uppercase(),
            modifier = Modifier.padding(horizontal = 8.dp, vertical = 4.dp),
            style = MaterialTheme.typography.labelSmall,
            color = color
        )
    }
}
