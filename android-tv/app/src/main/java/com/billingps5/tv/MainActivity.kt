package com.billingps5.tv

import android.os.Bundle
import android.view.WindowManager
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.billingps5.tv.api.ApiClient
import com.billingps5.tv.api.StateResponse
import kotlinx.coroutines.delay
import java.time.OffsetDateTime

private val Bg = Color(0xFF0F172A)
private val Amber = Color(0xFFF59E0B)

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
        setContent { MaterialTheme { App() } }
    }
}

@Composable
fun App() {
    var state by remember { mutableStateOf<StateResponse?>(null) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(Unit) {
        while (true) {
            try {
                state = ApiClient.api.heartbeat(Config.STATION_CODE)
                error = null
            } catch (e: Exception) {
                error = e.message ?: "Tidak dapat terhubung"
            }
            delay(Config.POLL_INTERVAL_MS)
        }
    }

    val current = state
    when {
        current == null -> ConnectingScreen(error)
        current.mode == "session" && current.session != null -> SessionScreen(current)
        else -> IdleScreen(current)
    }
}

@Composable
private fun ConnectingScreen(error: String?) {
    Box(Modifier.fillMaxSize().background(Bg), contentAlignment = Alignment.Center) {
        Column(horizontalAlignment = Alignment.CenterHorizontally) {
            Text("Billing PS5", color = Amber, fontSize = 40.sp, fontWeight = FontWeight.Bold)
            Text(error ?: "Menghubungkan…", color = Color(0xFF94A3B8), fontSize = 18.sp, modifier = Modifier.padding(top = 12.dp))
        }
    }
}

@Composable
private fun IdleScreen(state: StateResponse) {
    val joinUrl = state.qr?.join_url ?: ""
    val bitmap = remember(joinUrl) { if (joinUrl.isNotEmpty()) QrGenerator.generate(joinUrl) else null }

    Box(Modifier.fillMaxSize().background(Bg), contentAlignment = Alignment.Center) {
        Column(horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.Center) {
            Text(state.station.name, color = Color.White, fontSize = 34.sp, fontWeight = FontWeight.Bold)
            Text("Scan untuk mulai bermain", color = Color(0xFF94A3B8), fontSize = 20.sp, modifier = Modifier.padding(top = 8.dp, bottom = 28.dp))
            if (bitmap != null) {
                Image(
                    bitmap = bitmap.asImageBitmap(),
                    contentDescription = "QR Join",
                    modifier = Modifier.size(320.dp).clip(RoundedCornerShape(16.dp)),
                )
            }
            Text(joinUrl, color = Color(0xFF64748B), fontSize = 14.sp, modifier = Modifier.padding(top = 20.dp))
        }
    }
}

@Composable
private fun SessionScreen(state: StateResponse) {
    val session = state.session!!
    val endMillis = remember(session.planned_end_at) {
        session.planned_end_at?.let { runCatching { OffsetDateTime.parse(it).toInstant().toEpochMilli() }.getOrNull() }
    }
    var secondsLeft by remember { mutableStateOf(session.remaining_minutes * 60L) }

    LaunchedEffect(endMillis) {
        while (true) {
            secondsLeft = if (endMillis != null) {
                ((endMillis - System.currentTimeMillis()) / 1000).coerceAtLeast(0)
            } else {
                (secondsLeft - 1).coerceAtLeast(0)
            }
            delay(1000)
        }
    }

    val mm = (secondsLeft / 60).toString().padStart(2, '0')
    val ss = (secondsLeft % 60).toString().padStart(2, '0')

    Box(Modifier.fillMaxSize().background(Bg), contentAlignment = Alignment.Center) {
        Column(horizontalAlignment = Alignment.CenterHorizontally) {
            Text("SEDANG BERMAIN", color = Amber, fontSize = 20.sp, fontWeight = FontWeight.Bold)
            Text(session.member, color = Color.White, fontSize = 30.sp, fontWeight = FontWeight.Bold, modifier = Modifier.padding(top = 8.dp))
            Text(state.station.name, color = Color(0xFF94A3B8), fontSize = 18.sp)
            Text(
                "$mm:$ss",
                color = Color.White,
                fontSize = 120.sp,
                fontWeight = FontWeight.Bold,
                textAlign = TextAlign.Center,
                modifier = Modifier.padding(top = 24.dp),
            )
            Text("sisa waktu", color = Color(0xFF64748B), fontSize = 16.sp)
        }
    }
}
