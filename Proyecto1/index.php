<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Solo admins (rol 1) pueden entrar
if (!isset($_SESSION['empleado']) || $_SESSION['empleado']['rol'] != 1) {
    header('Location: store.php');
    exit;
}

// ── Manejo de página vía POST (sin parámetros en URL)
if (isset($_POST['cambiar_pagina'])) {
    $_SESSION['paginaAdmin'] = max(1, (int)$_POST['cambiar_pagina']);
    // Redirect POST → GET limpio (PRG pattern) para evitar reenvío del form al recargar
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

include "includes/head.php";

// Paginación
$porPagina    = 5;
$paginaActual = isset($_SESSION['paginaAdmin']) ? (int)$_SESSION['paginaAdmin'] : 1;

$apiBase = "http://127.0.0.1:8000/api";

// Obtener TODOS los productos para paginación
$todosProductos = json_decode(@file_get_contents("$apiBase/productos"), true) ?? [];
$totalProductos = count($todosProductos);
$totalPaginas   = max(1, ceil($totalProductos / $porPagina));

// Ajustar si la página guardada supera el total
if ($paginaActual > $totalPaginas) {
    $paginaActual = $totalPaginas;
    $_SESSION['paginaAdmin'] = $paginaActual;
}

$offset          = ($paginaActual - 1) * $porPagina;
$productosPagina = array_slice($todosProductos, $offset, $porPagina);

// Marcas y Categorías
$marcas     = json_decode(@file_get_contents("$apiBase/marcas"), true) ?? [];
$categorias = json_decode(@file_get_contents("$apiBase/categorias"), true) ?? [];

// Fallback desde productos
if (empty($marcas)) {
    $marcaMap = [];
    foreach ($todosProductos as $p) {
        $mid = $p['idMarca'];
        if (!isset($marcaMap[$mid])) $marcaMap[$mid] = $p['marca']['nombreMarc'];
    }
    foreach ($marcaMap as $id => $nombre) $marcas[] = ['idMarca' => $id, 'nombreMarc' => $nombre];
}

if (empty($categorias)) {
    $catMap = [];
    foreach ($todosProductos as $p) {
        $cid = $p['idCategoria'];
        if (!isset($catMap[$cid])) $catMap[$cid] = $p['categoria']['nombreCat'];
    }
    foreach ($catMap as $id => $nombre) $categorias[] = ['idCategoria' => $id, 'nombreCat' => $nombre];
}

// Leer resultado de operación desde sesión (en lugar de $_GET)
$resOperacion = $_SESSION['res_operacion'] ?? null;
$resMotivo    = $_SESSION['res_motivo'] ?? null;
unset($_SESSION['res_operacion'], $_SESSION['res_motivo']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - TechStore Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --bg-main: #f8fafc;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
        }

        body {
            background-color: var(--bg-main);
            font-family: 'Inter', sans-serif;
            color: #1e293b;
        }

        .modern-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: transform 0.2s ease;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 0.6rem 1rem;
            border: 1px solid #e2e8f0;
            background-color: #fcfcfd;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            border-color: #6366f1;
        }

        /* ── SELECTS COMPACTOS CON ALTURA LIMITADA ── */
        .form-select {
            max-height: 200px;
            overflow-y: auto;
        }

        /* ── LABEL CON BOTÓN COMPACTO ── */
        .label-with-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .label-with-btn .form-label {
            margin-bottom: 0;
        }

        .btn-add-item {
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: 600;
        }

        .btn-add-item:hover {
            background: #4338ca;
            transform: translateY(-1px);
        }

        .table-modern thead th {
            background-color: #f8fafc;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.05em;
            color: #94a3b8;
            padding: 1.2rem 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-modern tbody td {
            padding: 1rem;
            vertical-align: middle;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        .product-img {
            border-radius: 10px;
            object-fit: cover;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .btn-primary-custom {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.8rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            color: white;
        }

        #scanner {
            display: none;
            width: 100%;
            height: 250px;
            border-radius: 16px;
            margin: 1rem 0;
            overflow: hidden;
            background: #000;
            border: 2px solid #6366f1;
        }

        #scanner video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ── PAGINACIÓN ── */
        .page-link {
            border: none;
            color: #64748b;
            margin: 0 3px;
            border-radius: 8px !important;
            background: none;
            cursor: pointer;
        }

        .page-link:hover {
            background-color: #f1f5f9;
            color: #4f46e5;
        }

        .page-item.active .page-link {
            background: var(--primary-gradient);
            color: #fff;
        }

        .page-item.disabled .page-link {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }

        .alert-modern {
            border-radius: 12px;
            border: none;
            font-size: 0.9rem;
        }

        /* ── TOAST NOTIFICATION ── */
        #toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .toast-msg {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.9rem 1.2rem;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            font-size: 0.88rem;
            font-weight: 500;
            min-width: 260px;
            max-width: 340px;
            animation: slideInToast 0.35s cubic-bezier(.22, 1, .36, 1) forwards;
            border-left: 4px solid #6366f1;
        }

        .toast-msg.toast-success {
            border-color: #22c55e;
        }

        .toast-msg.toast-error {
            border-color: #ef4444;
        }

        .toast-msg.toast-warning {
            border-color: #f59e0b;
        }

        .toast-msg .toast-icon {
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .toast-msg.toast-success .toast-icon {
            color: #22c55e;
        }

        .toast-msg.toast-error .toast-icon {
            color: #ef4444;
        }

        .toast-msg.toast-warning .toast-icon {
            color: #f59e0b;
        }

        .toast-msg.fade-out {
            animation: fadeOutToast 0.3s ease forwards;
        }

        @keyframes slideInToast {
            from {
                opacity: 0;
                transform: translateX(60px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }

        @keyframes fadeOutToast {
            to {
                opacity: 0;
                transform: translateX(60px) scale(0.95);
            }
        }

        /* ── MODAL CONFIRM ── */
        #confirmModal .modal-content {
            border: 0;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        #confirmModal .confirm-icon-wrap {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 1rem;
        }

        #confirmModal .confirm-icon-wrap.danger {
            background: #fef2f2;
            color: #ef4444;
        }

        #confirmModal .confirm-icon-wrap.warning {
            background: #fffbeb;
            color: #f59e0b;
        }


        /* ── SCROLL PERSONALIZADO EN LISTAS ── */
        .modern-card div[style*="overflow-y: auto"]::-webkit-scrollbar {
            width: 5px;
        }

        .modern-card div[style*="overflow-y: auto"]::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .modern-card div[style*="overflow-y: auto"]::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .modern-card div[style*="overflow-y: auto"]::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col">
                <h2 class="fw-bold text-dark">Gestión de Inventario</h2>
                <p class="text-muted">Administra tus productos y stock de forma inteligente</p>
            </div>
        </div>

        <!-- ── ALERTAS desde sesión (sin parámetros en URL) ── -->
        <?php if ($resOperacion): ?>
            <div class="row">
                <div class="col-12">
                    <?php if ($resOperacion === 'success'): ?>
                        <div class="alert alert-modern alert-success d-flex align-items-center shadow-sm border-0" role="alert">
                            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                            <div>
                                <strong class="d-block">¡Operación Exitosa!</strong>
                                <span class="small">El producto ha sido guardado correctamente.</span>
                            </div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($resOperacion === 'error'): ?>
                        <?php
                        if ($resMotivo === 'duplicado') {
                            $titulo  = "Código de barras duplicado";
                            $detalle = "Ese código ya existe en la base de datos.";
                        } elseif ($resMotivo === 'imagen') {
                            $titulo  = "Formato de imagen no válido";
                            $detalle = "Solo se permiten imágenes JPG o PNG.";
                        } else {
                            $titulo  = "Hubo un problema";
                            $detalle = "No se pudo completar el registro. Verifica los datos e intenta de nuevo.";
                        }
                        ?>
                        <div class="alert alert-modern alert-danger d-flex align-items-center shadow-sm border-0" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                            <div>
                                <strong class="d-block"><?= $titulo ?></strong>
                                <span class="small"><?= $detalle ?></span>
                            </div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- ══════════════════════════════════
             FORMULARIO REGISTRO
        ══════════════════════════════════ -->
            <div class="col-lg-5">
                <div class="modern-card p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3 text-primary">
                            <i class="bi bi-plus-square-fill fs-4"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Nuevo Producto</h5>
                    </div>

                    <form action="model/registrar.php" method="POST" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label">Código de Barras</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-3">
                                    <i class="bi bi-upc"></i>
                                </span>
                                <input type="text" name="codigo" id="codigo"
                                    class="form-control border-start-0"
                                    placeholder="Escanear o digitar..." required maxlength="13">
                                <button type="button" class="btn btn-dark rounded-end-3" onclick="iniciarEscaneo()">
                                    <i class="bi bi-upc-scan"></i>
                                </button>
                            </div>
                        </div>

                        <div id="scanner"></div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Nombre del Producto</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unidad</label>
                                <input type="text" name="unidad" class="form-control" placeholder="Ej: Pza" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="label-with-btn">
                                <label class="form-label">Marca</label>
                                <button type="button" class="btn-add-item" data-bs-toggle="modal" data-bs-target="#modalNuevaMarca" title="Agregar nueva marca">
                                    <i class="bi bi-plus-lg"></i>Nueva
                                </button>
                            </div>
                            <select name="marca" id="selectMarca" class="form-select" required>
                                <option value="" disabled selected>Seleccione una marca</option>
                                <?php foreach ($marcas as $m): ?>
                                    <option value="<?= $m['idMarca'] ?>"><?= htmlspecialchars($m['nombreMarc']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="label-with-btn">
                                <label class="form-label">Categoría</label>
                                <button type="button" class="btn-add-item" data-bs-toggle="modal" data-bs-target="#modalNuevaCategoria" title="Agregar nueva categoría">
                                    <i class="bi bi-plus-lg"></i>Nueva
                                </button>
                            </div>
                            <select name="categoria" id="selectCategoria" class="form-select" required>
                                <option value="" disabled selected>Seleccione una categoría</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['idCategoria'] ?>"><?= htmlspecialchars($cat['nombreCat']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">Costo</label>
                                <input type="number" step="0.01" name="precio" class="form-control" required>
                            </div>
                            <div class="col">
                                <label class="form-label">P. Venta</label>
                                <input type="number" step="0.01" name="venta" class="form-control" required>
                            </div>
                            <label class="form-label">Descripción Corta</label>
                            <textarea name="descripcion" class="form-control" rows="2" required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Imagen Referencial</label>
                            <input type="file" name="imagen" class="form-control" accept=".jpg,.jpeg,.png" required>
                        </div>

                        <div id="mensaje"></div>

                        <button type="submit" name="btnregistrar" value="ok"
                            class="btn btn-primary-custom w-100 shadow-sm mt-2">
                            <i class="bi bi-cloud-arrow-up me-2"></i>Guardar Producto
                        </button>
                    </form>
                </div>
            </div>

            <!-- ══════════════════════════════════
             TABLA DE PRODUCTOS
        ══════════════════════════════════ -->
            <div class="col-lg-7">
                <div class="modern-card">
                    <div class="p-4 border-bottom bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Productos Registrados</h5>
                        <span class="badge bg-light text-primary rounded-pill px-3 py-2">
                            <?= $totalProductos ?> en total
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productosPagina as $datos): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?= 'http://localhost/ds9p1/Proyecto1/imagenes/' . htmlspecialchars($datos['imagen']) ?>"
                                                    class="product-img me-3" width="48" height="48">
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($datos['nombre']) ?></div>
                                                    <small class="text-muted text-uppercase" style="font-size:10px;">
                                                        ID: #<?= $datos['idProducto'] ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded-2">
                                                <?= htmlspecialchars($datos['categoria']['nombreCat']) ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold">$<?= number_format((float)$datos['precioCosto'], 2) ?></td>
                                        <td class="text-end">
                                            <div class="btn-group shadow-sm border rounded-3 p-1 bg-white">
                                                <button class="btn btn-link btn-sm text-warning p-1"
                                                    onclick="editarProducto('<?= $datos['idProducto'] ?>')">
                                                    <i class="bi bi-pencil-square fs-5"></i>
                                                </button>
                                                <button class="btn btn-link btn-sm text-danger p-1"
                                                    onclick="eliminarProducto('<?= $datos['idProducto'] ?>')">
                                                    <i class="bi bi-trash3 fs-5"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($productosPagina)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No hay productos registrados.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ── PAGINACIÓN SIN GET ── -->
                    <?php if ($totalPaginas > 1): ?>
                        <div class="p-4">
                            <nav aria-label="Paginación de productos">
                                <ul class="pagination justify-content-center mb-0">

                                    <!-- Anterior -->
                                    <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
                                        <button type="button" class="page-link shadow-sm"
                                            <?= ($paginaActual > 1) ? "onclick=\"cambiarPagina($paginaActual - 1)\"" : '' ?>>
                                            &laquo;
                                        </button>
                                    </li>

                                    <!-- Números de página -->
                                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                        <li class="page-item <?= ($i == $paginaActual) ? 'active' : '' ?>">
                                            <button type="button" class="page-link shadow-sm"
                                                onclick="cambiarPagina(<?= $i ?>)">
                                                <?= $i ?>
                                            </button>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Siguiente -->
                                    <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
                                        <button type="button" class="page-link shadow-sm"
                                            <?= ($paginaActual < $totalPaginas) ? "onclick=\"cambiarPagina($paginaActual + 1)\"" : '' ?>>
                                            &raquo;
                                        </button>
                                    </li>

                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════
         CATEGORÍAS Y MARCAS
    ══════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════
     CATEGORÍAS Y MARCAS
══════════════════════════════════════════ -->
        <div class="row g-4 mt-2">

            <!-- CATEGORÍAS -->
            <div class="col-lg-6">
                <div class="modern-card p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-info bg-opacity-10 p-2 rounded-3 me-3 text-info">
                            <i class="bi bi-tag-fill fs-4"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Categorías</h5>
                        <span class="badge bg-light text-secondary rounded-pill px-2 py-1 ms-auto" id="badgeCategorias">
                            <?= count($categorias) ?>
                        </span>
                    </div>

                    <form id="formNuevaCategoria">
                        <div class="input-group mb-3">
                            <input type="text" id="nombreCategoria" class="form-control"
                                placeholder="Nueva categoría..." maxlength="50" required>
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="bi bi-plus-lg me-1"></i>Agregar
                            </button>
                        </div>
                    </form>

                    <!-- Lista deslizable -->
                    <div style="max-height: 288px; overflow-y: auto; border-radius: 10px; border: 1px solid #f1f5f9;">
                        <table class="table table-modern table-sm mb-0">
                            <thead style="position: sticky; top: 0; z-index: 1; background: #f8fafc;">
                                <tr>
                                    <th>Nombre</th>
                                    <th class="text-end" style="width:120px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaCategorias">
                                <?php foreach ($categorias as $cat): ?>
                                    <tr class="categoria-row" data-id="<?= $cat['idCategoria'] ?>">
                                        <td><span class="cat-nombre"><?= htmlspecialchars($cat['nombreCat']) ?></span></td>
                                        <td class="text-end">
                                            <button class="btn btn-link btn-sm text-warning p-1"
                                                onclick="editarCategoria(<?= $cat['idCategoria'] ?>, '<?= htmlspecialchars(addslashes($cat['nombreCat'])) ?>')">
                                                <i class="bi bi-pencil-square fs-5"></i>
                                            </button>
                                            <button class="btn btn-link btn-sm text-danger p-1"
                                                onclick="eliminarCategoria(<?= $cat['idCategoria'] ?>)">
                                                <i class="bi bi-trash3 fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Indicador de scroll si hay más de 6 -->
                    <?php if (count($categorias) > 6): ?>
                        <div class="text-center mt-2">
                            <small class="text-muted">
                                <i class="bi bi-arrow-down-circle me-1"></i>Desliza para ver todas
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- MARCAS -->
            <div class="col-lg-6">
                <div class="modern-card p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-warning bg-opacity-10 p-2 rounded-3 me-3 text-warning">
                            <i class="bi bi-bookmark-fill fs-4"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Marcas</h5>
                        <span class="badge bg-light text-secondary rounded-pill px-2 py-1 ms-auto" id="badgeMarcas">
                            <?= count($marcas) ?>
                        </span>
                    </div>

                    <form id="formNuevaMarca">
                        <div class="input-group mb-3">
                            <input type="text" id="nombreMarca" class="form-control"
                                placeholder="Nueva marca..." maxlength="50" required>
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="bi bi-plus-lg me-1"></i>Agregar
                            </button>
                        </div>
                    </form>

                    <!-- Lista deslizable -->
                    <div style="max-height: 288px; overflow-y: auto; border-radius: 10px; border: 1px solid #f1f5f9;">
                        <table class="table table-modern table-sm mb-0">
                            <thead style="position: sticky; top: 0; z-index: 1; background: #f8fafc;">
                                <tr>
                                    <th>Nombre</th>
                                    <th class="text-end" style="width:120px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaMarcas">
                                <?php foreach ($marcas as $m): ?>
                                    <tr class="marca-row" data-id="<?= $m['idMarca'] ?>">
                                        <td><span class="marca-nombre"><?= htmlspecialchars($m['nombreMarc']) ?></span></td>
                                        <td class="text-end">
                                            <button class="btn btn-link btn-sm text-warning p-1"
                                                onclick="editarMarca(<?= $m['idMarca'] ?>, '<?= htmlspecialchars(addslashes($m['nombreMarc'])) ?>')">
                                                <i class="bi bi-pencil-square fs-5"></i>
                                            </button>
                                            <button class="btn btn-link btn-sm text-danger p-1"
                                                onclick="eliminarMarca(<?= $m['idMarca'] ?>)">
                                                <i class="bi bi-trash3 fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Indicador de scroll si hay más de 6 -->
                    <?php if (count($marcas) > 6): ?>
                        <div class="text-center mt-2">
                            <small class="text-muted">
                                <i class="bi bi-arrow-down-circle me-1"></i>Desliza para ver todas
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>


        <!-- ══════════════════════════════════════════
     MODAL NUEVA CATEGORÍA (RÁPIDO)
══════════════════════════════════════════ -->
        <div class="modal fade" id="modalNuevaCategoria" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-bottom-0 p-4">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-tag-fill text-info me-2"></i>Nueva Categoría
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 pt-0">
                        <div id="msgNuevaCategoria"></div>
                        <input type="text" id="inputNuevaCategoria" class="form-control"
                            placeholder="Ej: Accesorios" maxlength="50" required>
                    </div>
                    <div class="modal-footer border-top-0 p-4 pt-0">
                        <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary-custom px-4" onclick="crearNuevaCategoria()">
                            <i class="bi bi-plus-lg me-1"></i>Crear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════
     MODAL NUEVA MARCA (RÁPIDO)
══════════════════════════════════════════ -->
        <div class="modal fade" id="modalNuevaMarca" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-bottom-0 p-4">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-bookmark-fill text-warning me-2"></i>Nueva Marca
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 pt-0">
                        <div id="msgNuevaMarca"></div>
                        <input type="text" id="inputNuevaMarca" class="form-control"
                            placeholder="Ej: Samsung" maxlength="50" required>
                    </div>
                    <div class="modal-footer border-top-0 p-4 pt-0">
                        <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary-custom px-4" onclick="crearNuevaMarca()">
                            <i class="bi bi-plus-lg me-1"></i>Crear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════
     MODAL EDITAR CATEGORÍA
══════════════════════════════════════════ -->
        <div class="modal fade" id="modalEditarCategoria" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-bottom-0 p-4">
                        <h5 class="modal-title fw-bold">Editar Categoría</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 pt-0">
                        <input type="hidden" id="editCatId">
                        <div class="mb-3">
                            <label class="form-label">Nombre de Categoría</label>
                            <input type="text" id="editCatNombre" class="form-control" maxlength="50" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 p-4 pt-0">
                        <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary-custom px-4" onclick="guardarEdicionCategoria()">
                            <i class="bi bi-check2 me-1"></i>Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════
     MODAL EDITAR MARCA
══════════════════════════════════════════ -->
        <div class="modal fade" id="modalEditarMarca" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-bottom-0 p-4">
                        <h5 class="modal-title fw-bold">Editar Marca</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 pt-0">
                        <input type="hidden" id="editMarcId">
                        <div class="mb-3">
                            <label class="form-label">Nombre de Marca</label>
                            <input type="text" id="editMarcNombre" class="form-control" maxlength="50" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 p-4 pt-0">
                        <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary-custom px-4" onclick="guardarEdicionMarca()">
                            <i class="bi bi-check2 me-1"></i>Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════
     MODAL EDITAR PRODUCTO
══════════════════════════════════════════ -->
        <div class="modal fade" id="modalEditar" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-bottom-0 p-4">
                        <h5 class="modal-title fw-bold">Editar Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 pt-0">
                        <div id="modalMensaje"></div>
                        <input type="hidden" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="edit_nombre" class="form-control">
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">Unidad</label>
                                <input type="text" id="edit_unidad" class="form-control">
                            </div>
                            <div class="col">
                                <label class="form-label">Precio Costo</label>
                                <input type="number" step="0.01" id="edit_precio" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 p-4 pt-0">
                        <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary-custom px-4" id="btnGuardar" onclick="guardarEdicion()">
                            <span id="btnGuardarTexto"><i class="bi bi-check2 me-1"></i>Actualizar</span>
                            <span id="btnGuardarSpinner" class="d-none">
                                <span class="spinner-border spinner-border-sm me-1"></span>Guardando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════
     TOAST CONTAINER
══════════════════════════════════════════ -->
        <div id="toast-container"></div>

        <!-- ══════════════════════════════════════════
     MODAL CONFIRM GLOBAL
══════════════════════════════════════════ -->
        <div class="modal fade" id="confirmModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content p-4 text-center">
                    <div id="confirmIconWrap" class="confirm-icon-wrap danger mb-2">
                        <i id="confirmIcon" class="bi bi-trash3-fill"></i>
                    </div>
                    <h5 id="confirmTitle" class="fw-bold mb-1">¿Eliminar?</h5>
                    <p id="confirmText" class="text-muted small mb-4">Esta acción no se puede deshacer.</p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-light rounded-3 w-50 fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                        <button id="confirmBtn" class="btn w-50 fw-semibold rounded-3 btn-danger">Sí, eliminar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════
     FORM OCULTO PARA PAGINACIÓN (PRG)
══════════════════════════════════════════ -->
        <form id="formPagina" method="POST" action="" style="display:none;">
            <input type="hidden" name="cambiar_pagina" id="inputPagina" value="">
        </form>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>

        <script>
            const API_BASE = "http://127.0.0.1:8000/api";

            /* ══════════════════════════════════════════
               PAGINACIÓN SIN PARÁMETROS EN URL (PRG)
            ══════════════════════════════════════════ */
            function cambiarPagina(pagina) {
                document.getElementById("inputPagina").value = pagina;
                document.getElementById("formPagina").submit();
            }

            /* ══════════════════════════════════════════
               TOAST – notificación animada
            ══════════════════════════════════════════ */
            function showToast(msg, type = "success") {
                const icons = {
                    success: "bi-check-circle-fill",
                    error: "bi-x-circle-fill",
                    warning: "bi-exclamation-circle-fill"
                };
                const container = document.getElementById("toast-container");
                const el = document.createElement("div");
                el.className = `toast-msg toast-${type}`;
                el.innerHTML = `<i class="bi ${icons[type]} toast-icon"></i><span>${msg}</span>`;
                container.appendChild(el);
                setTimeout(() => {
                    el.classList.add("fade-out");
                    setTimeout(() => el.remove(), 320);
                }, 3000);
            }

            /* ══════════════════════════════════════════
               CONFIRM MODAL – reemplaza confirm() nativo
            ══════════════════════════════════════════ */
            function showConfirm({
                title = "¿Estás seguro?",
                text = "Esta acción no se puede deshacer.",
                btnLabel = "Sí, eliminar",
                btnClass = "btn-danger",
                iconClass = "bi-trash3-fill",
                iconType = "danger"
            } = {}) {
                return new Promise(resolve => {
                    document.getElementById("confirmTitle").textContent = title;
                    document.getElementById("confirmText").textContent = text;
                    document.getElementById("confirmBtn").textContent = btnLabel;
                    document.getElementById("confirmBtn").className = `btn w-50 fw-semibold rounded-3 ${btnClass}`;
                    document.getElementById("confirmIcon").className = `bi ${iconClass}`;
                    document.getElementById("confirmIconWrap").className = `confirm-icon-wrap ${iconType} mb-2`;

                    const modal = new bootstrap.Modal(document.getElementById("confirmModal"));
                    const btnYes = document.getElementById("confirmBtn");

                    modal.show();

                    let resolved = false;

                    const handler = () => {
                        if (resolved) return;
                        resolved = true;
                        btnYes.removeEventListener("click", handler);
                        modal.hide();
                        resolve(true);
                    };

                    document.getElementById("confirmModal").addEventListener("hidden.bs.modal", () => {
                        if (!resolved) {
                            resolved = true;
                            resolve(false);
                        }
                    }, {
                        once: true
                    });

                    btnYes.addEventListener("click", handler);
                });
            }

            /* ══════════════════════════════════════════
               CATEGORÍAS
            ══════════════════════════════════════════ */
            document.getElementById("formNuevaCategoria").addEventListener("submit", function(e) {
                e.preventDefault();
                const nombre = document.getElementById("nombreCategoria").value.trim();
                if (!nombre) {
                    showToast("Ingresa el nombre de la categoría", "warning");
                    return;
                }

                fetch(`${API_BASE}/categorias`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            nombreCat: nombre
                        })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error();
                        return res.json();
                    })
                    .then(data => {
                        document.getElementById("nombreCategoria").value = "";
                        agregarFilaCategoria(data);
                        showToast("Categoría creada exitosamente", "success");
                    })
                    .catch(() => showToast("Error al crear la categoría", "error"));
            });

            function agregarFilaCategoria(cat) {
                const tbody = document.getElementById("tablaCategorias");
                const fila = document.createElement("tr");
                fila.className = "categoria-row";
                fila.setAttribute("data-id", cat.idCategoria);
                fila.innerHTML = `
            <td><span class="cat-nombre">${htmlEscape(cat.nombreCat)}</span></td>
            <td class="text-end">
                <button class="btn btn-link btn-sm text-warning p-1"
                    onclick="editarCategoria(${cat.idCategoria}, '${htmlEscape(cat.nombreCat).replace(/'/g, "\\'")}')">
                    <i class="bi bi-pencil-square fs-5"></i>
                </button>
                <button class="btn btn-link btn-sm text-danger p-1"
                    onclick="eliminarCategoria(${cat.idCategoria})">
                    <i class="bi bi-trash3 fs-5"></i>
                </button>
            </td>`;
                tbody.appendChild(fila);

                // Agregar al select del formulario si no existe ya
                const select = document.getElementById("selectCategoria");
                if (!select.querySelector(`option[value="${cat.idCategoria}"]`)) {
                    const option = document.createElement("option");
                    option.value = cat.idCategoria;
                    option.textContent = cat.nombreCat;
                    select.appendChild(option);
                }
            }

            function editarCategoria(id, nombre) {
                document.getElementById("editCatId").value = id;
                document.getElementById("editCatNombre").value = nombre;
                new bootstrap.Modal(document.getElementById("modalEditarCategoria")).show();
            }

            function guardarEdicionCategoria() {
                const id = document.getElementById("editCatId").value;
                const nombre = document.getElementById("editCatNombre").value.trim();
                if (!nombre) {
                    showToast("Ingresa el nombre de la categoría", "warning");
                    return;
                }

                fetch(`${API_BASE}/categorias/${id}`, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            nombreCat: nombre
                        })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error();
                        return res.json();
                    })
                    .then(data => {
                        document.querySelector(`.categoria-row[data-id="${id}"] .cat-nombre`).textContent = data.nombreCat;
                        // Actualizar también el select del formulario
                        const opt = document.querySelector(`#selectCategoria option[value="${id}"]`);
                        if (opt) opt.textContent = data.nombreCat;
                        bootstrap.Modal.getInstance(document.getElementById("modalEditarCategoria")).hide();
                        showToast("Categoría actualizada", "success");
                    })
                    .catch(() => showToast("Error al actualizar la categoría", "error"));
            }

            async function eliminarCategoria(id) {
                const ok = await showConfirm({
                    title: "¿Eliminar categoría?",
                    text: "Esta acción no se puede deshacer.",
                    btnLabel: "Sí, eliminar",
                    btnClass: "btn-danger",
                    iconClass: "bi-tag-fill",
                    iconType: "danger"
                });
                if (!ok) return;

                fetch(`${API_BASE}/categorias/${id}`, {
                        method: "DELETE"
                    })
                    .then(res => {
                        if (!res.ok) throw new Error();
                        document.querySelector(`.categoria-row[data-id="${id}"]`).remove();
                        document.querySelector(`#selectCategoria option[value="${id}"]`)?.remove();
                        showToast("Categoría eliminada", "success");
                    })
                    .catch(() => showToast("Error al eliminar", "error"));
            }

            /* ══════════════════════════════════════════
               MARCAS
            ══════════════════════════════════════════ */
            document.getElementById("formNuevaMarca").addEventListener("submit", function(e) {
                e.preventDefault();
                const nombre = document.getElementById("nombreMarca").value.trim();
                if (!nombre) {
                    showToast("Ingresa el nombre de la marca", "warning");
                    return;
                }

                fetch(`${API_BASE}/marcas`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            nombreMarc: nombre
                        })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error();
                        return res.json();
                    })
                    .then(data => {
                        document.getElementById("nombreMarca").value = "";
                        agregarFilaMarca(data);
                        showToast("Marca creada exitosamente", "success");
                    })
                    .catch(() => showToast("Error al crear la marca", "error"));
            });

            function agregarFilaMarca(marc) {
                const tbody = document.getElementById("tablaMarcas");
                const fila = document.createElement("tr");
                fila.className = "marca-row";
                fila.setAttribute("data-id", marc.idMarca);
                fila.innerHTML = `
            <td><span class="marca-nombre">${htmlEscape(marc.nombreMarc)}</span></td>
            <td class="text-end">
                <button class="btn btn-link btn-sm text-warning p-1"
                    onclick="editarMarca(${marc.idMarca}, '${htmlEscape(marc.nombreMarc).replace(/'/g, "\\'")}')">
                    <i class="bi bi-pencil-square fs-5"></i>
                </button>
                <button class="btn btn-link btn-sm text-danger p-1"
                    onclick="eliminarMarca(${marc.idMarca})">
                    <i class="bi bi-trash3 fs-5"></i>
                </button>
            </td>`;
                tbody.appendChild(fila);

                // Agregar al select del formulario si no existe ya
                const select = document.getElementById("selectMarca");
                if (!select.querySelector(`option[value="${marc.idMarca}"]`)) {
                    const option = document.createElement("option");
                    option.value = marc.idMarca;
                    option.textContent = marc.nombreMarc;
                    select.appendChild(option);
                }
            }

            function editarMarca(id, nombre) {
                document.getElementById("editMarcId").value = id;
                document.getElementById("editMarcNombre").value = nombre;
                new bootstrap.Modal(document.getElementById("modalEditarMarca")).show();
            }

            function guardarEdicionMarca() {
                const id = document.getElementById("editMarcId").value;
                const nombre = document.getElementById("editMarcNombre").value.trim();
                if (!nombre) {
                    showToast("Ingresa el nombre de la marca", "warning");
                    return;
                }

                fetch(`${API_BASE}/marcas/${id}`, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            nombreMarc: nombre
                        })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error();
                        return res.json();
                    })
                    .then(data => {
                        document.querySelector(`.marca-row[data-id="${id}"] .marca-nombre`).textContent = data.nombreMarc;
                        // Actualizar también el select del formulario
                        const opt = document.querySelector(`#selectMarca option[value="${id}"]`);
                        if (opt) opt.textContent = data.nombreMarc;
                        bootstrap.Modal.getInstance(document.getElementById("modalEditarMarca")).hide();
                        showToast("Marca actualizada", "success");
                    })
                    .catch(() => showToast("Error al actualizar la marca", "error"));
            }

            async function eliminarMarca(id) {
                const ok = await showConfirm({
                    title: "¿Eliminar marca?",
                    text: "Esta acción no se puede deshacer.",
                    btnLabel: "Sí, eliminar",
                    btnClass: "btn-danger",
                    iconClass: "bi-bookmark-fill",
                    iconType: "danger"
                });
                if (!ok) return;

                fetch(`${API_BASE}/marcas/${id}`, {
                        method: "DELETE"
                    })
                    .then(res => {
                        if (!res.ok) throw new Error();
                        document.querySelector(`.marca-row[data-id="${id}"]`).remove();
                        document.querySelector(`#selectMarca option[value="${id}"]`)?.remove();
                        showToast("Marca eliminada", "success");
                    })
                    .catch(() => showToast("Error al eliminar", "error"));
            }

            /* ══════════════════════════════════════════
               UTILIDAD
            ══════════════════════════════════════════ */
            function htmlEscape(text) {
                const div = document.createElement("div");
                div.textContent = text;
                return div.innerHTML;
            }

            /* ══════════════════════════════════════════
               EDITAR PRODUCTO
            ══════════════════════════════════════════ */
            function editarProducto(id) {
                fetch(`${API_BASE}/productos/${id}`)
                    .then(res => {
                        if (!res.ok) throw new Error();
                        return res.json();
                    })
                    .then(data => {
                        document.getElementById("edit_id").value = data.idProducto;
                        document.getElementById("edit_nombre").value = data.nombre;
                        document.getElementById("edit_unidad").value = data.unidad;
                        document.getElementById("edit_precio").value = data.precioCosto;
                        document.getElementById("modalMensaje").innerHTML = "";
                        new bootstrap.Modal(document.getElementById("modalEditar")).show();
                    })
                    .catch(() => showToast("Error al obtener los datos del producto", "error"));
            }

            /* ══════════════════════════════════════════
               GUARDAR EDICIÓN PRODUCTO
            ══════════════════════════════════════════ */
            function guardarEdicion() {
                const id = document.getElementById("edit_id").value;
                const nombre = document.getElementById("edit_nombre").value.trim();
                const unidad = document.getElementById("edit_unidad").value.trim();
                const precio = document.getElementById("edit_precio").value;

                if (!nombre || !unidad || !precio) {
                    document.getElementById("modalMensaje").innerHTML = `
                <div class="alert alert-warning alert-modern py-2 mb-3">
                    <i class="bi bi-exclamation-circle me-2"></i>Completa todos los campos antes de guardar.
                </div>`;
                    return;
                }

                document.getElementById("btnGuardarTexto").classList.add("d-none");
                document.getElementById("btnGuardarSpinner").classList.remove("d-none");
                document.getElementById("btnGuardar").disabled = true;

                fetch(`${API_BASE}/productos/${id}`, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            nombre,
                            unidad,
                            precioCosto: precio
                        })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error();
                        return res.json();
                    })
                    .then(() => {
                        bootstrap.Modal.getInstance(document.getElementById("modalEditar")).hide();
                        showToast("Producto actualizado correctamente", "success");
                        setTimeout(() => location.reload(), 1200);
                    })
                    .catch(() => {
                        document.getElementById("modalMensaje").innerHTML = `
                <div class="alert alert-danger alert-modern py-2 mb-3">
                    <i class="bi bi-x-circle me-2"></i>No se pudo actualizar el producto.
                </div>`;
                    })
                    .finally(() => {
                        document.getElementById("btnGuardarTexto").classList.remove("d-none");
                        document.getElementById("btnGuardarSpinner").classList.add("d-none");
                        document.getElementById("btnGuardar").disabled = false;
                    });
            }

            /* ══════════════════════════════════════════
               ELIMINAR PRODUCTO
            ══════════════════════════════════════════ */
            async function eliminarProducto(id) {
                const ok = await showConfirm({
                    title: "¿Eliminar producto?",
                    text: "Esta acción no se puede deshacer.",
                    btnLabel: "Sí, eliminar",
                    btnClass: "btn-danger",
                    iconClass: "bi-box-seam",
                    iconType: "danger"
                });
                if (!ok) return;

                fetch(`${API_BASE}/productos/${id}`, {
                        method: "DELETE"
                    })
                    .then(res => {
                        if (!res.ok) throw new Error();
                        showToast("Producto eliminado", "success");
                        setTimeout(() => location.reload(), 1200);
                    })
                    .catch(() => showToast("Error al eliminar el producto", "error"));
            }

            /* ══════════════════════════════════════════
               BÚSQUEDA POR CÓDIGO DE BARRAS
            ══════════════════════════════════════════ */
            let timeout;

            document.getElementById("codigo").addEventListener("input", function() {
                const codigo = this.value.trim();
                clearTimeout(timeout);
                if (codigo === "") {
                    limpiarFormulario();
                    return;
                }
                timeout = setTimeout(() => buscarPorCodigo(codigo), 400);
            });

            document.getElementById("codigo").addEventListener("keydown", function(e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                    const codigo = this.value.trim();
                    if (codigo !== "") {
                        clearTimeout(timeout);
                        buscarPorCodigo(codigo);
                    }
                }
            });

            function buscarPorCodigo(codigo) {
                fetch(`${API_BASE}/productos/${codigo}`)
                    .then(res => {
                        if (res.status === 404) return {
                            existe: false
                        };
                        if (!res.ok) throw new Error("Error de red");
                        return res.json().then(data => ({
                            ...data,
                            existe: true
                        }));
                    })
                    .then(data => mostrarResultadoBusqueda(data))
                    .catch(() => {
                        const msg = document.getElementById("mensaje");
                        msg.innerHTML = `
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-wifi-off fs-5"></i>
                        <div><strong>Error de conexión</strong><br>
                        <small>No se pudo consultar el servidor.</small></div>
                    </div>`;
                        msg.className = "alert alert-danger mt-2";
                    });
            }

            function mostrarResultadoBusqueda(data) {
                const msg = document.getElementById("mensaje");
                if (data.existe) {
                    document.querySelector("[name='nombre']").value = data.nombre;
                    document.querySelector("[name='unidad']").value = data.unidad;
                    document.querySelector("[name='descripcion']").value = data.descripcion;
                    document.querySelector("[name='nombre']").readOnly = true;
                    document.querySelector("[name='unidad']").readOnly = true;
                    document.querySelector("[name='descripcion']").readOnly = true;
                    msg.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div><strong>Producto encontrado</strong><br>
                    <small>Este código ya existe en la base de datos.</small></div>
                </div>`;
                    msg.className = "alert alert-success mt-2";
                } else {
                    limpiarFormulario(false);
                    msg.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill fs-5"></i>
                    <div><strong>Producto no encontrado</strong><br>
                    <small>Este código no existe, puedes registrarlo como nuevo.</small></div>
                </div>`;
                    msg.className = "alert alert-warning mt-2";
                    document.querySelector("[name='nombre']").focus();
                }
            }

            /* ══════════════════════════════════════════
               LIMPIAR FORMULARIO
            ══════════════════════════════════════════ */
            function limpiarFormulario(limpiarCodigo = true) {
                if (limpiarCodigo) document.getElementById("codigo").value = "";
                document.querySelector("[name='nombre']").value = "";
                document.querySelector("[name='unidad']").value = "";
                document.querySelector("[name='descripcion']").value = "";
                document.querySelector("[name='nombre']").readOnly = false;
                document.querySelector("[name='unidad']").readOnly = false;
                document.querySelector("[name='descripcion']").readOnly = false;
                document.getElementById("mensaje").innerHTML = "";
                document.getElementById("mensaje").className = "";
            }

            /* ══════════════════════════════════════════
               CREAR NUEVA CATEGORÍA (RÁPIDO - MODAL)
            ══════════════════════════════════════════ */
            function crearNuevaCategoria() {
                const nombre = document.getElementById("inputNuevaCategoria").value.trim();
                if (!nombre) {
                    document.getElementById("msgNuevaCategoria").innerHTML = `
                <div class="alert alert-warning alert-modern py-2 mb-3">
                    <i class="bi bi-exclamation-circle me-2"></i>Ingresa el nombre de la categoría.
                </div>`;
                    return;
                }

                const btn = event.target;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Creando...';

                fetch(`${API_BASE}/categorias`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            nombreCat: nombre
                        })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error();
                        return res.json();
                    })
                    .then(data => {
                        // Agregar a la tabla de categorías
                        agregarFilaCategoria(data);

                        // Agregar a los selects
                        const select = document.getElementById("selectCategoria");
                        const option = document.createElement("option");
                        option.value = data.idCategoria;
                        option.textContent = data.nombreCat;
                        select.appendChild(option);
                        select.value = data.idCategoria;

                        // Limpiar y cerrar modal
                        document.getElementById("inputNuevaCategoria").value = "";
                        document.getElementById("msgNuevaCategoria").innerHTML = "";
                        bootstrap.Modal.getInstance(document.getElementById("modalNuevaCategoria")).hide();
                        showToast("Categoría creada y seleccionada", "success");
                    })
                    .catch(() => {
                        document.getElementById("msgNuevaCategoria").innerHTML = `
                <div class="alert alert-danger alert-modern py-2 mb-3">
                    <i class="bi bi-x-circle me-2"></i>Error al crear la categoría.
                </div>`;
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i>Crear';
                    });
            }

            /* ══════════════════════════════════════════
               CREAR NUEVA MARCA (RÁPIDO - MODAL)
            ══════════════════════════════════════════ */
            function crearNuevaMarca() {
                const nombre = document.getElementById("inputNuevaMarca").value.trim();
                if (!nombre) {
                    document.getElementById("msgNuevaMarca").innerHTML = `
                <div class="alert alert-warning alert-modern py-2 mb-3">
                    <i class="bi bi-exclamation-circle me-2"></i>Ingresa el nombre de la marca.
                </div>`;
                    return;
                }

                const btn = event.target;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Creando...';

                fetch(`${API_BASE}/marcas`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            nombreMarc: nombre
                        })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error();
                        return res.json();
                    })
                    .then(data => {
                        // Agregar a la tabla de marcas
                        agregarFilaMarca(data);

                        // Agregar a los selects
                        const select = document.getElementById("selectMarca");
                        const option = document.createElement("option");
                        option.value = data.idMarca;
                        option.textContent = data.nombreMarc;
                        select.appendChild(option);
                        select.value = data.idMarca;

                        // Limpiar y cerrar modal
                        document.getElementById("inputNuevaMarca").value = "";
                        document.getElementById("msgNuevaMarca").innerHTML = "";
                        bootstrap.Modal.getInstance(document.getElementById("modalNuevaMarca")).hide();
                        showToast("Marca creada y seleccionada", "success");
                    })
                    .catch(() => {
                        document.getElementById("msgNuevaMarca").innerHTML = `
                <div class="alert alert-danger alert-modern py-2 mb-3">
                    <i class="bi bi-x-circle me-2"></i>Error al crear la marca.
                </div>`;
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i>Crear';
                    });
            }

            /* ══════════════════════════════════════════
               ESCÁNER DE CÓDIGO DE BARRAS
            ══════════════════════════════════════════ */
            let escaneando = false;

            function iniciarEscaneo() {
                const scannerDiv = document.getElementById("scanner");

                if (escaneando) {
                    Quagga.stop();
                    scannerDiv.style.display = "none";
                    escaneando = false;
                    return;
                }

                scannerDiv.style.display = "block";
                escaneando = true;

                Quagga.init({
                    inputStream: {
                        name: "Live",
                        type: "LiveStream",
                        target: scannerDiv,
                        constraints: {
                            facingMode: "environment"
                        }
                    },
                    decoder: {
                        readers: ["ean_reader", "ean_8_reader", "code_128_reader", "code_39_reader", "upc_reader"]
                    }
                }, function(err) {
                    if (err) {
                        showToast("No se pudo iniciar la cámara", "error");
                        scannerDiv.style.display = "none";
                        escaneando = false;
                        return;
                    }
                    Quagga.start();
                });

                Quagga.onDetected(function(result) {
                    const code = result.codeResult.code;
                    document.getElementById("codigo").value = code;
                    Quagga.stop();
                    scannerDiv.style.display = "none";
                    escaneando = false;
                    buscarPorCodigo(code);
                });
            }
        </script>

</body>

</html>