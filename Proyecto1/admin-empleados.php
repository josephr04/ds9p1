<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Solo administradores (rol 1) pueden acceder
if (!isset($_SESSION['empleado'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['empleado']['rol'] != 1) {
    header('Location: empleado.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include "includes/head.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados · SellFlow</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #eef2ff;
            color: #0f172a;
        }

        .panel-card {
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 22px;
            background: #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .badge-soft {
            background: rgba(99, 102, 241, 0.12);
            color: #4338ca;
        }

        .form-control,
        .form-select {
            border-radius: 14px;
            border-color: #cbd5e1;
            background: #f8fafc;
            min-height: 48px;
            font-size: 0.95rem;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.12);
            border-color: #6366f1;
            background: #fff;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.875rem;
            color: #475569;
            margin-bottom: 6px;
        }

        .table thead th {
            border-bottom: 1px solid #e2e8f0;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #475569;
            font-weight: 600;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        .table td,
        .table th {
            vertical-align: middle;
            padding: 14px 16px;
        }

        .page-link {
            border-radius: 12px;
            border-color: #e2e8f0;
            color: #475569;
        }

        .page-item.active .page-link {
            background: #4f46e5;
            border-color: #4f46e5;
            color: white;
        }

        .text-secondary { color: #64748b !important; }

        .stat-card {
            border-radius: 18px;
            border: 1px solid rgba(148,163,184,0.15);
            background: #fff;
            box-shadow: 0 8px 24px rgba(15,23,42,0.05);
            padding: 22px 24px;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -1px;
            color: #0f172a;
        }
        .stat-label {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        .stat-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 3px 10px;
            border-radius: 20px;
            margin-top: 4px;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 500;
        }
        .role-admin { background: #eff6ff; color: #1d4ed8; }
        .role-emp   { background: #f1f5f9; color: #475569; }

        .btn-action {
            padding: 6px 14px;
            font-size: 0.8rem;
            font-weight: 500;
            border-radius: 10px;
        }

        /* Modal */
        .modal-content {
            border-radius: 22px;
            border: 1px solid rgba(148,163,184,0.2);
            box-shadow: 0 24px 60px rgba(15,23,42,0.15);
        }
        .modal-header {
            border-bottom: 1px solid #f1f5f9;
            padding: 22px 28px 16px;
        }
        .modal-body { padding: 24px 28px; }
        .modal-footer {
            border-top: 1px solid #f1f5f9;
            padding: 16px 28px 22px;
        }
        .modal-title { font-weight: 600; font-size: 1.05rem; }

        /* Toast */
        .toast-container-custom {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .toast-custom {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            border-radius: 14px;
            font-size: 0.9rem;
            font-weight: 500;
            color: white;
            margin-bottom: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            animation: slideInRight 0.3s ease-out;
        }
        .toast-success { background: linear-gradient(135deg, #10b981, #059669); }
        .toast-error   { background: linear-gradient(135deg, #ef4444, #dc2626); }
        @keyframes slideInRight {
            from { transform: translateX(400px); opacity: 0; }
            to   { transform: translateX(0);     opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0);     opacity: 1; }
            to   { transform: translateX(400px); opacity: 0; }
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }
        .empty-state i { font-size: 2.5rem; margin-bottom: 12px; display: block; }

        #loadingRow td { text-align: center; padding: 48px; color: #94a3b8; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;  /* antes era #eef2ff */
            color: #0f172a;
        }
    </style>
</head>
<body>

<!-- Toast container -->
<div class="toast-container-custom" id="toastContainer"></div>

<div class="container py-5">

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Gestión de Empleados</h1>
            <p class="text-muted mb-0">Administra los usuarios con acceso al sistema.</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-label">Total</div>
                <div class="stat-value" id="stat-total">—</div>
                <span class="stat-badge" style="background:#eff6ff;color:#2563eb;">Usuarios</span>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-label">Administradores</div>
                <div class="stat-value" id="stat-admin">—</div>
                <span class="stat-badge" style="background:#fefce8;color:#ca8a04;">rol 1</span>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-label">Empleados</div>
                <div class="stat-value" id="stat-emp">—</div>
                <span class="stat-badge" style="background:#f0fdf4;color:#16a34a;">rol 2</span>
            </div>
        </div>
    </div>

    <!-- Table card -->
    <div class="panel-card p-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <span class="badge badge-soft rounded-pill px-3 py-2 mb-2">Empleados del sistema</span>
                <h2 class="h5 fw-semibold mb-0">Listado de empleados</h2>
            </div>
            <button class="btn btn-primary px-4" onclick="abrirModalCrear()">
                <i class="bi bi-plus-lg me-2"></i>Nuevo empleado
            </button>
        </div>

        <!-- Toolbar -->
        <div class="row g-2 mb-4 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius:14px 0 0 14px;border-color:#cbd5e1;">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control border-start-0 ps-0"
                        style="border-radius:0 14px 14px 0;min-height:48px;"
                        placeholder="Buscar por nombre o usuario…" oninput="renderTabla()">
                </div>
            </div>
            <div class="col-md-3">
                <select id="filterRol" class="form-select" onchange="renderTabla()">
                    <option value="">Todos los roles</option>
                    <option value="1">Administrador</option>
                    <option value="2">Empleado</option>
                </select>
            </div>
            <div class="col-md-auto ms-auto">
                <button class="btn btn-outline-secondary" onclick="cargarEmpleados()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <tr id="loadingRow">
                        <td colspan="5">
                            <div class="d-flex align-items-center justify-content-center gap-2 py-5 text-muted">
                                <span class="spinner-border spinner-border-sm"></span>
                                Cargando empleados…
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── MODAL CREAR / EDITAR ── -->
<div class="modal fade" id="modalEmpleado" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Nuevo empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Nombre</label>
                        <input id="fNombre" type="text" class="form-control" placeholder="Juan">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Apellido</label>
                        <input id="fApellido" type="text" class="form-control" placeholder="Pérez">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Usuario</label>
                        <input id="fUsuario" type="text" class="form-control" placeholder="jperez">
                        <div class="form-text" id="usuarioHint"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Rol</label>
                        <select id="fRol" class="form-select">
                            <option value="2">Empleado</option>
                            <option value="1">Administrador</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" id="passLabel">Contraseña</label>
                        <input id="fContrasena" type="password" class="form-control" placeholder="Mínimo 6 caracteres">
                        <div class="form-text" id="passHint" style="display:none;">Dejar vacío para no cambiar la contraseña</div>
                        <div class="text-danger small mt-1" id="passError" style="display:none;">Mínimo 6 caracteres</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary px-4" id="btnGuardar" onclick="guardarEmpleado()">
                    <i class="bi bi-check2 me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── MODAL ELIMINAR ── -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4">
            <div style="font-size:2.5rem;margin-bottom:12px;">🗑️</div>
            <h5 class="fw-bold mb-2">Eliminar empleado</h5>
            <p class="text-muted small mb-4" id="deleteText">¿Estás seguro? Esta acción no se puede deshacer.</p>
            <div class="d-flex gap-2 justify-content-center">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger px-4" onclick="confirmarEliminar()">Sí, eliminar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const API = 'http://127.0.0.1:8000/api'; // ← ajusta si es necesario

    let empleados  = [];
    let editandoId = null;
    let eliminandoId = null;

    const modalEmpleado = new bootstrap.Modal(document.getElementById('modalEmpleado'));
    const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminar'));

    // ── CARGAR ──
    async function cargarEmpleados() {
        document.getElementById('tbody').innerHTML = `
            <tr><td colspan="5">
                <div class="d-flex align-items-center justify-content-center gap-2 py-5 text-muted">
                    <span class="spinner-border spinner-border-sm"></span> Cargando…
                </div>
            </td></tr>`;
        try {
            const r = await fetch(`${API}/empleados`, { headers: { 'Accept': 'application/json' } });
            if (!r.ok) throw new Error();
            empleados = await r.json();
            actualizarStats();
            renderTabla();
        } catch {
            mostrarToast('Error al cargar empleados. Verifica la conexión.', 'error');
            document.getElementById('tbody').innerHTML = `
                <tr><td colspan="5">
                    <div class="empty-state">
                        <i class="bi bi-wifi-off"></i>
                        <p class="fw-semibold">Error de conexión</p>
                        <span class="small">No se pudo conectar con el servidor.</span>
                    </div>
                </td></tr>`;
        }
    }

    function actualizarStats() {
        document.getElementById('stat-total').textContent = empleados.length;
        document.getElementById('stat-admin').textContent = empleados.filter(e => String(e.rol) === '1').length;
        document.getElementById('stat-emp').textContent   = empleados.filter(e => String(e.rol) === '2').length;
    }

    function renderTabla() {
        const q   = document.getElementById('searchInput').value.toLowerCase();
        const rol = document.getElementById('filterRol').value;
        const lista = empleados.filter(e => {
            const matchQ = !q || `${e.nombre} ${e.apellido} ${e.usuario}`.toLowerCase().includes(q);
            const matchR = !rol || String(e.rol) === rol;
            return matchQ && matchR;
        });

        if (!lista.length) {
            document.getElementById('tbody').innerHTML = `
                <tr><td colspan="5">
                    <div class="empty-state">
                        <i class="bi bi-people"></i>
                        <p class="fw-semibold text-muted">Sin resultados</p>
                        <span class="small text-muted">No se encontraron empleados con esos filtros.</span>
                    </div>
                </td></tr>`;
            return;
        }

        document.getElementById('tbody').innerHTML = lista.map((e, i) => `
            <tr>
                <td class="text-muted small">${i + 1}</td>
                <td>
                    <div class="fw-semibold">${htmlEsc(e.nombre)} ${htmlEsc(e.apellido)}</div>
                </td>
                <td><span class="text-muted small">@${htmlEsc(e.usuario)}</span></td>
                <td>${rolBadge(e.rol)}</td>
                <td class="text-end">
                    <div class="d-flex gap-2 justify-content-end">
                        <button class="btn btn-outline-primary btn-action" onclick="abrirModalEditar('${e.usuario}')">
                            <i class="bi bi-pencil me-1"></i>Editar
                        </button>
                        <button class="btn btn-outline-danger btn-action" onclick="abrirModalEliminar('${e.usuario}', '${htmlEsc(e.nombre)} ${htmlEsc(e.apellido)}')">
                            <i class="bi bi-trash me-1"></i>Eliminar
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function rolBadge(rol) {
        return String(rol) === '1'
            ? `<span class="role-badge role-admin"><i class="bi bi-shield-check"></i> Administrador</span>`
            : `<span class="role-badge role-emp"><i class="bi bi-person"></i> Empleado</span>`;
    }

    // ── CREAR ──
    function abrirModalCrear() {
        editandoId = null;
        document.getElementById('modalTitulo').textContent = 'Nuevo empleado';
        document.getElementById('btnGuardar').innerHTML = '<i class="bi bi-check2 me-1"></i>Guardar empleado';
        document.getElementById('fNombre').value     = '';
        document.getElementById('fApellido').value   = '';
        document.getElementById('fUsuario').value    = '';
        document.getElementById('fUsuario').disabled = false;
        document.getElementById('fRol').value        = '2';
        document.getElementById('fContrasena').value = '';
        document.getElementById('passHint').style.display  = 'none';
        document.getElementById('passError').style.display = 'none';
        document.getElementById('passLabel').textContent   = 'Contraseña';
        document.getElementById('usuarioHint').textContent = '';
        modalEmpleado.show();
    }

        // ── EDITAR ──
    function abrirModalEditar(usuario) {
        const e = empleados.find(x => x.usuario == usuario);
        if (!e) return;
        editandoId = e.usuario;
        document.getElementById('modalTitulo').textContent = 'Editar empleado';
        document.getElementById('btnGuardar').innerHTML = '<i class="bi bi-check2 me-1"></i>Guardar cambios';
        document.getElementById('fNombre').value     = e.nombre;
        document.getElementById('fApellido').value   = e.apellido;
        document.getElementById('fUsuario').value    = e.usuario;
        document.getElementById('fUsuario').disabled = true;
        document.getElementById('fRol').value        = String(e.rol);
        document.getElementById('fContrasena').value = '';
        document.getElementById('passHint').style.display  = 'block';
        document.getElementById('passError').style.display = 'none';
        document.getElementById('passLabel').textContent   = 'Nueva contraseña (opcional)';
        document.getElementById('usuarioHint').textContent = `Usuario: @${e.usuario} (no se puede cambiar)`;
        modalEmpleado.show();
    }

    // ── GUARDAR ──
    async function guardarEmpleado() {
        const nombre     = document.getElementById('fNombre').value.trim();
        const apellido   = document.getElementById('fApellido').value.trim();
        const usuario    = document.getElementById('fUsuario').value.trim();
        const rol        = document.getElementById('fRol').value;
        const contrasena = document.getElementById('fContrasena').value;

        document.getElementById('passError').style.display = 'none';

        if (!nombre || !apellido || !usuario) {
            mostrarToast('Completa todos los campos requeridos.', 'error'); return;
        }
        if (!editandoId && contrasena.length < 6) {
            document.getElementById('passError').style.display = 'block'; return;
        }
        if (editandoId && contrasena && contrasena.length < 6) {
            document.getElementById('passError').style.display = 'block'; return;
        }

        const body = { nombre, apellido, rol };
        if (!editandoId) { body.usuario = usuario; body.contrasena = contrasena; }
        if (editandoId && contrasena) body.contrasena = contrasena;

        const btn = document.getElementById('btnGuardar');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando…';

        try {
            const url    = editandoId ? `${API}/empleados/${editandoId}` : `${API}/empleados`;
            const method = editandoId ? 'PUT' : 'POST';
            const r = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await r.json();
            if (!r.ok) {
                const msg = data.errors
                    ? Object.values(data.errors).flat().join(' ')
                    : (data.mensaje || 'Error al guardar.');
                mostrarToast(msg, 'error');
            } else {
                mostrarToast(editandoId ? '✓ Empleado actualizado.' : '✓ Empleado creado.', 'success');
                modalEmpleado.hide();
                cargarEmpleados();
            }
        } catch {
            mostrarToast('No se pudo conectar con el servidor.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-check2 me-1"></i>${editandoId ? 'Guardar cambios' : 'Guardar empleado'}`;
        }
    }

    // ── ELIMINAR ──
    function abrirModalEliminar(id, nombre) {
        eliminandoId = id;
        document.getElementById('deleteText').textContent =
            `¿Eliminar a "${nombre}"? Esta acción no se puede deshacer.`;
        modalEliminar.show();
    }

    async function confirmarEliminar() {
        if (!eliminandoId) return;
        try {
            const r = await fetch(`${API}/empleados/${eliminandoId}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json' }
            });
            if (r.ok) {
                mostrarToast('Empleado eliminado.', 'success');
                modalEliminar.hide();
                cargarEmpleados();
            } else {
                mostrarToast('No se pudo eliminar el empleado.', 'error');
            }
        } catch {
            mostrarToast('Error de conexión.', 'error');
        }
    }

    // ── TOAST ──
    function mostrarToast(msg, tipo = 'success') {
        const container = document.getElementById('toastContainer');
        const id = 'toast-' + Date.now();
        const icon = tipo === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
        container.insertAdjacentHTML('beforeend', `
            <div id="${id}" class="toast-custom toast-${tipo}">
                <i class="bi ${icon}" style="font-size:1.2rem;"></i>
                <span>${msg}</span>
            </div>`);
        const el = document.getElementById(id);
        setTimeout(() => {
            el.style.animation = 'slideOutRight 0.3s ease-out forwards';
            setTimeout(() => el.remove(), 300);
        }, 3500);
    }

    // ── UTILS ──
    function htmlEsc(str) {
        return String(str ?? '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── INIT ──
    cargarEmpleados();
</script>
</body>
</html>