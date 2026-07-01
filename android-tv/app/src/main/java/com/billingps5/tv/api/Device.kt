package com.billingps5.tv.api

import com.billingps5.tv.Config
import okhttp3.OkHttpClient
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Path

// --- Response models (match Laravel StationDeviceService::state) ---

data class StateResponse(
    val mode: String,
    val station: StationInfo,
    val qr: QrInfo?,
    val session: SessionInfo?,
)

data class StationInfo(
    val code: String,
    val name: String,
    val status: String,
    val app_mode: String,
)

data class QrInfo(
    val join_url: String,
)

data class SessionInfo(
    val member: String,
    val member_code: String,
    val started_at: String?,
    val planned_end_at: String?,
    val remaining_minutes: Int,
)

// --- Retrofit API ---

interface DeviceApi {
    @POST("api/device/stations/{code}/heartbeat")
    suspend fun heartbeat(@Path("code") code: String): StateResponse

    @GET("api/device/stations/{code}/state")
    suspend fun state(@Path("code") code: String): StateResponse
}

object ApiClient {
    val api: DeviceApi by lazy {
        val client = OkHttpClient.Builder()
            .addInterceptor { chain ->
                val request = chain.request().newBuilder()
                    .addHeader("X-Station-Token", Config.STATION_TOKEN)
                    .addHeader("Accept", "application/json")
                    .build()
                chain.proceed(request)
            }
            .build()

        Retrofit.Builder()
            .baseUrl(Config.BASE_URL)
            .client(client)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(DeviceApi::class.java)
    }
}
