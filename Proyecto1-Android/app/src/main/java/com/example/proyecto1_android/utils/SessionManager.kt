package com.example.proyecto1_android.utils

import android.content.Context
import android.content.SharedPreferences

class SessionManager(context: Context) {

    private val prefs: SharedPreferences =
        context.getSharedPreferences("session_prefs", Context.MODE_PRIVATE)

    companion object {
        private const val KEY_USUARIO      = "usuario"
        private const val KEY_NOMBRE       = "nombre"
        private const val KEY_APELLIDO     = "apellido"  // ✅ nuevo
        private const val KEY_ROL          = "rol"
        private const val KEY_IS_LOGGED_IN = "is_logged_in"
    }

    fun saveSession(usuario: String, nombre: String, apellido: String, rol: Int) {  // ✅
        prefs.edit().apply {
            putString(KEY_USUARIO, usuario)
            putString(KEY_NOMBRE, nombre)
            putString(KEY_APELLIDO, apellido)  // ✅
            putInt(KEY_ROL, rol)
            putBoolean(KEY_IS_LOGGED_IN, true)
            apply()
        }
    }

    fun getUsuario(): String  = prefs.getString(KEY_USUARIO, "") ?: ""
    fun getNombre(): String   = prefs.getString(KEY_NOMBRE, "") ?: ""
    fun getApellido(): String = prefs.getString(KEY_APELLIDO, "") ?: ""  // ✅
    fun getRol(): Int         = prefs.getInt(KEY_ROL, -1)
    fun isLoggedIn(): Boolean = prefs.getBoolean(KEY_IS_LOGGED_IN, false)

    fun clearSession() {
        prefs.edit().clear().apply()
    }
}