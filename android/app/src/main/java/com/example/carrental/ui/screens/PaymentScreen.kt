package com.example.carrental.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.example.carrental.viewmodel.PaymentViewModel

@Composable
fun PaymentScreen(
    bookingId: Int,
    amount: Double,
    viewModel: PaymentViewModel,
    onPaymentSubmitted: () -> Unit
) {
    var paymentMethod by remember { mutableStateOf("GCash") }
    var referenceNumber by remember { mutableStateOf("") }
    val paymentMethods = listOf("GCash", "PayMaya", "Coins.ph", "Bank Transfer")
    
    var proofOfPaymentName by remember { mutableStateOf("") }

    val status by viewModel.submissionStatus.collectAsState()

    LaunchedEffect(status) {
        if (status == "success") {
            onPaymentSubmitted()
            viewModel.resetSubmissionStatus()
        }
    }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(16.dp)
            .verticalScroll(rememberScrollState()),
        verticalArrangement = Arrangement.spacedBy(16.dp)
    ) {
        Text(text = "Payment for Booking #$bookingId", style = MaterialTheme.typography.headlineMedium)
        Text(text = "Amount to Pay: $$amount", style = MaterialTheme.typography.titleLarge)

        Text(text = "Select Payment Method")
        paymentMethods.forEach { method ->
            Row(verticalAlignment = androidx.compose.ui.Alignment.CenterVertically) {
                RadioButton(
                    selected = (paymentMethod == method),
                    onClick = { paymentMethod = method }
                )
                Text(text = method)
            }
        }

        OutlinedTextField(
            value = referenceNumber,
            onValueChange = { referenceNumber = it },
            label = { Text("Reference Number") },
            modifier = Modifier.fillMaxWidth()
        )

        if (paymentMethod == "Bank Transfer") {
            Card(
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.secondaryContainer),
                modifier = Modifier.fillMaxWidth()
            ) {
                Column(modifier = Modifier.padding(16.dp)) {
                    Text(text = "Bank Transfer Details:", style = MaterialTheme.typography.titleSmall)
                    Text(text = "Bank: BDO")
                    Text(text = "Account Name: Car Rental Inc")
                    Text(text = "Account Number: 00123456789")
                }
            }
        } else {
            Card(
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.secondaryContainer),
                modifier = Modifier.fillMaxWidth()
            ) {
                Column(modifier = Modifier.padding(16.dp)) {
                    Text(text = "$paymentMethod Details:", style = MaterialTheme.typography.titleSmall)
                    Text(text = "Account Name: Car Rental Inc")
                    Text(text = "Number: 09123456789")
                }
            }
        }

        OutlinedTextField(
            value = proofOfPaymentName,
            onValueChange = { proofOfPaymentName = it },
            label = { Text("Proof of Payment (Filename)") },
            modifier = Modifier.fillMaxWidth(),
            placeholder = { Text("e.g. proof.jpg") }
        )

        Button(
            onClick = {
                viewModel.submitPayment(bookingId, paymentMethod, referenceNumber, amount, null)
            },
            modifier = Modifier.fillMaxWidth(),
            enabled = referenceNumber.isNotEmpty()
        ) {
            Text("Submit Payment")
        }
        
        if (status?.startsWith("error") == true) {
            Text(text = status!!, color = MaterialTheme.colorScheme.error)
        }
    }
}
