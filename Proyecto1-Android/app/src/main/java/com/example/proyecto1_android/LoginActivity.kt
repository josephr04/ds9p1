// =====================================================================
// LoginActivity.kt — ACTUALIZADO
// Ahora redirige según rol: 1 = AdminActivity, otro = MainActivity
// =====================================================================
package com.example.proyecto1_android

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.example.proyecto1_android.data.api.RetrofitClient
import com.example.proyecto1_android.databinding.ActivityLoginBinding
import com.example.proyecto1_android.utils.SessionManager
import kotlinx.coroutines.launch

class LoginActivity : AppCompatActivity() {

    private lateinit var binding: ActivityLoginBinding
    private lateinit var sessionManager: SessionManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        sessionManager = SessionManager(this)

        if (sessionManager.isLoggedIn()) {
            goByRole()
            return
        }

        binding = ActivityLoginBinding.inflate(layoutInflater)
        setContentView(binding.root)

        binding.btnLogin.setOnClickListener {
            val usuario    = binding.etEmail.text.toString().trim()
            val contrasena = binding.etPassword.text.toString().trim()
            if (validar(usuario, contrasena)) hacerLogin(usuario, contrasena)
        }
    }

    private fun validar(usuario: String, contrasena: String): Boolean {
        if (usuario.isEmpty()) { binding.tilEmail.error = "Ingresa tu usuario"; return false }
        binding.tilEmail.error = null
        if (contrasena.isEmpty()) { binding.tilPassword.error = "Ingresa tu contraseña"; return false }
        binding.tilPassword.error = null
        return true
    }

    private fun hacerLogin(usuario: String, contrasena: String) {
        setLoading(true)
        lifecycleScope.launch {
            try {
                val response = RetrofitClient.instance.login(usuario, contrasena)
                if (response.isSuccessful) {
                    val body = response.body()
                    if (body != null && body.status == "success") {
                        sessionManager.saveSession(body.usuario, body.nombre, body.rol)
                        goByRole()
                    } else {
                        mostrarError("Usuario o contraseña incorrectos")
                    }
                } else {
                    mostrarError("Error del servidor (${response.code()})")
                }
            } catch (e: java.net.ConnectException) {
                mostrarError("No se pudo conectar al servidor")
            } catch (e: Exception) {
                mostrarError("Error: ${e.message}")
            } finally {
                setLoading(false)
            }
        }
    }

    // ── Redirige según rol ────────────────────────────────────────
    private fun goByRole() {
        val intent = if (sessionManager.getRol() == 1) {
            Intent(this, AdminActivity::class.java)
        } else {
            Intent(this, MainActivity::class.java)
        }
        startActivity(intent)
        finish()
    }

    private fun setLoading(loading: Boolean) {
        binding.btnLogin.isEnabled = !loading
        binding.progressBar.visibility = if (loading) View.VISIBLE else View.GONE
        binding.btnLogin.text = if (loading) "Iniciando..." else "Entrar"
    }

    private fun mostrarError(msg: String) {
        Toast.makeText(this, msg, Toast.LENGTH_LONG).show()
    }
}