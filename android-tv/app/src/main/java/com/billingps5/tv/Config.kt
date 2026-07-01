package com.billingps5.tv

/**
 * Per-device configuration. Set these for each Android TV / station before building.
 *  - BASE_URL: your Billing PS5 server (use trailing slash).
 *      Emulator -> host machine: "http://10.0.2.2/"
 *      Real TV on LAN:           "http://192.168.1.10/"
 *  - STATION_CODE + STATION_TOKEN: from Admin panel -> Stations (code + device_token).
 */
object Config {
    const val BASE_URL = "http://10.0.2.2/"
    const val STATION_CODE = "JKT-01-ST1"
    const val STATION_TOKEN = "PASTE_DEVICE_TOKEN_HERE"

    const val POLL_INTERVAL_MS = 5_000L
}
