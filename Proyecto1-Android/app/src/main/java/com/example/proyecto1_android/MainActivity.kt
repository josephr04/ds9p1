package com.example.proyecto1_android

import android.content.Intent
import android.os.Bundle
import android.view.Menu
import android.view.MenuItem
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.navigation.fragment.NavHostFragment
import androidx.navigation.ui.setupWithNavController
import com.example.proyecto1_android.databinding.ActivityMainBinding
import com.example.proyecto1_android.utils.SessionManager

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private lateinit var sessionManager: SessionManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        sessionManager = SessionManager(this)

        // Si no hay sesión activa → ir al login
        if (!sessionManager.isLoggedIn()) {
            goToLogin()
            return
        }

        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar?.title = when (sessionManager.getRol()) {
            1    -> "Admin"
            else -> "Empleado"
        }

        // ── Tu navegación original, sin cambios ──────────────────
        val navHost = supportFragmentManager
            .findFragmentById(R.id.nav_host_fragment) as NavHostFragment
        val navController = navHost.navController
        binding.bottomNav.setupWithNavController(navController)
    }

    // Menú de 3 puntos (esquina superior derecha)
    override fun onCreateOptionsMenu(menu: Menu): Boolean {
        menuInflater.inflate(R.menu.menu_main, menu)
        return true
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        return when (item.itemId) {
            R.id.action_perfil -> {
                mostrarPerfil()
                true
            }
            R.id.action_logout -> {
                confirmarLogout()
                true
            }
            else -> super.onOptionsItemSelected(item)
        }
    }

    private fun mostrarPerfil() {
        val dialogView = layoutInflater.inflate(R.layout.dialog_perfil, null)

        val nombre   = sessionManager.getNombre() ?: "?"
        val apellido = sessionManager.getApellido() ?: "?"
        val usuario  = sessionManager.getUsuario() ?: "?"
        val rol      = when (sessionManager.getRol()) {
            1    -> "Administrador"
            2    -> "Empleado"
            else -> "Rol ${sessionManager.getRol()}"
        }

        // Avatar muestra inicial del nombre
        dialogView.findViewById<android.widget.TextView>(R.id.tvInicial).text =
            nombre.firstOrNull()?.uppercaseChar()?.toString() ?: "?"

        // Nombre completo arriba
        dialogView.findViewById<android.widget.TextView>(R.id.tvNombrePerfil).text =
            "$nombre $apellido"

        dialogView.findViewById<android.widget.TextView>(R.id.tvRolBadge).text       = rol
        dialogView.findViewById<android.widget.TextView>(R.id.tvUsuarioPerfil).text   = usuario
        dialogView.findViewById<android.widget.TextView>(R.id.tvNombreCompletoPerfil).text = nombre
        dialogView.findViewById<android.widget.TextView>(R.id.tvApellidoPerfil).text  = apellido

        AlertDialog.Builder(this)
            .setView(dialogView)
            .setPositiveButton("Cerrar", null)
            .show()
    }

    private fun confirmarLogout() {
        AlertDialog.Builder(this)
            .setTitle("Cerrar sesión")
            .setMessage("¿Estás seguro de que quieres salir?")
            .setPositiveButton("Salir") { _, _ ->
                sessionManager.clearSession()
                goToLogin()
            }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    private fun goToLogin() {
        startActivity(Intent(this, LoginActivity::class.java))
        finish()
    }
}