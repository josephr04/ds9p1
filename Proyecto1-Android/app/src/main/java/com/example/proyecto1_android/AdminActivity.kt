package com.example.proyecto1_android

import android.content.Intent
import android.os.Bundle
import android.view.Menu
import android.view.MenuItem
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.navigation.fragment.NavHostFragment
import androidx.navigation.ui.setupWithNavController
import com.example.proyecto1_android.databinding.ActivityAdminBinding
import com.example.proyecto1_android.utils.SessionManager

class AdminActivity : AppCompatActivity() {

    private lateinit var binding: ActivityAdminBinding
    private lateinit var sessionManager: SessionManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        sessionManager = SessionManager(this)

        if (!sessionManager.isLoggedIn() || sessionManager.getRol() != 1) {
            goToLogin()
            return
        }

        binding = ActivityAdminBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setSupportActionBar(binding.toolbar)
        supportActionBar?.title = "Admin"

        val navHost = supportFragmentManager
            .findFragmentById(R.id.nav_host_fragment_admin) as NavHostFragment
        val navController = navHost.navController
        binding.bottomNavAdmin.setupWithNavController(navController)
    }

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
                AlertDialog.Builder(this)
                    .setTitle("Cerrar sesión")
                    .setMessage("¿Estás seguro?")
                    .setPositiveButton("Salir") { _, _ ->
                        sessionManager.clearSession()
                        goToLogin()
                    }
                    .setNegativeButton("Cancelar", null)
                    .show()
                true
            }
            else -> super.onOptionsItemSelected(item)
        }
    }

    private fun mostrarPerfil() {
        val dialogView = layoutInflater.inflate(R.layout.dialog_perfil, null)

        val nombre   = sessionManager.getNombre()
        val apellido = sessionManager.getApellido()
        val usuario  = sessionManager.getUsuario()
        val rol      = "Administrador"

        dialogView.findViewById<android.widget.TextView>(R.id.tvInicial).text =
            nombre.firstOrNull()?.uppercaseChar()?.toString() ?: "?"

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

    private fun goToLogin() {
        startActivity(Intent(this, LoginActivity::class.java))
        finish()
    }
}