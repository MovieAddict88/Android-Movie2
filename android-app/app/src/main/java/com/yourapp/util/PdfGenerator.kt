package com.yourapp.util

import android.content.Context
import android.graphics.Canvas
import android.graphics.Paint
import android.graphics.pdf.PdfDocument
import com.yourapp.data.entity.JobLog
import java.io.File
import java.io.FileOutputStream
import java.time.Instant
import java.time.ZoneId
import java.time.format.DateTimeFormatter

class PdfGenerator(private val context: Context) {
    private val formatter = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss")
        .withZone(ZoneId.systemDefault())

    fun generateReport(jobLog: JobLog, locationName: String): File? {
        val fileName = "JobReport_${jobLog.id}_${System.currentTimeMillis()}.pdf"
        val file = File(context.getExternalFilesDir(null), fileName)

        val pdfDocument = PdfDocument()
        val pageInfo = PdfDocument.PageInfo.Builder(595, 842, 1).create()
        val page = pdfDocument.startPage(pageInfo)
        val canvas: Canvas = page.canvas
        val paint = Paint()

        var y = 50f
        paint.textSize = 18f
        paint.isFakeBoldText = true
        canvas.drawText("Job Completion Report", 50f, y, paint)

        y += 40f
        paint.textSize = 12f
        paint.isFakeBoldText = false
        canvas.drawText("Location: $locationName", 50f, y, paint)

        y += 20f
        canvas.drawText("Check-in: ${formatter.format(Instant.ofEpochMilli(jobLog.checkInAt))}", 50f, y, paint)

        y += 20f
        val checkOut = jobLog.checkOutAt?.let { formatter.format(Instant.ofEpochMilli(it)) } ?: "N/A"
        canvas.drawText("Check-out: $checkOut", 50f, y, paint)

        y += 20f
        canvas.drawText("Notes: ${jobLog.notes ?: "None"}", 50f, y, paint)

        pdfDocument.finishPage(page)

        try {
            pdfDocument.writeTo(FileOutputStream(file))
            pdfDocument.close()
            return file
        } catch (e: Exception) {
            e.printStackTrace()
            pdfDocument.close()
            return null
        }
    }
}
