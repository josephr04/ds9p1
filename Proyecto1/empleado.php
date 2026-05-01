<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['empleado'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['empleado']['rol'] == 1) {
    header('Location: index.php');
    exit;
}

require_once 'config/conexion.php';

$search       = trim($_GET['q'] ?? '');
$porPagina    = 10;
$paginaActual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset       = ($paginaActual - 1) * $porPagina;

if ($search !== '') {
    $likeSearch = "%{$search}%";

    $stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM productos WHERE idProducto LIKE ? OR nombre LIKE ?");
    $stmt->bind_param('ss', $likeSearch, $likeSearch);
    $stmt->execute();
    $totalProductos = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();

    $stmt = $conexion->prepare("SELECT * FROM productos WHERE idProducto LIKE ? OR nombre LIKE ? ORDER BY nombre ASC LIMIT ? OFFSET ?");
    $stmt->bind_param('ssii', $likeSearch, $likeSearch, $porPagina, $offset);
} else {
    $stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM productos");
    $stmt->execute();
    $totalProductos = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();

    $stmt = $conexion->prepare("SELECT * FROM productos ORDER BY nombre ASC LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $porPagina, $offset);
}

$totalPaginas = max(1, ceil($totalProductos / $porPagina));
if ($paginaActual > $totalPaginas) {
    $paginaActual = $totalPaginas;
    $offset = ($paginaActual - 1) * $porPagina;
}

$stmt->execute();
$resultados = $stmt->get_result();
$productosPagina = $resultados->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Empleado · Verificación de Productos</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #eef2ff;
            color: #0f172a;
        }

        .header-panel {
            background: linear-gradient(135deg, #4f46e5 0%, #2563eb 100%);
            color: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
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
        .form-select,
        .form-control-lg {
            border-radius: 14px;
            border-color: #cbd5e1;
            background: #f8fafc;
            min-height: 56px;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.12);
            border-color: #6366f1;
        }

        .table thead th {
            border-bottom: 1px solid #e2e8f0;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            font-size: 0.78rem;
            color: #475569;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        .table td,
        .table th {
            vertical-align: middle;
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

        .hero-chip {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .text-secondary {
            color: #64748b !important;
        }

        /* Toast Notification */
        .toast-container-custom {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(16, 185, 129, 0.3);
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        .toast-success .bi {
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Contenedor para notificaciones emergentes -->
    <div class="toast-container-custom" id="toastContainer"></div>

    <div class="container py-5">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold">Panel de Empleado</h1>
                <p class="text-muted mb-0">Verifica productos con el lector de códigos de barras y revisa los datos de inventario.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="store.php" class="btn btn-outline-secondary">Ver catálogo</a>
                <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
            </div>
        </div>

        <div class="panel-card p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <span class="badge badge-soft rounded-pill px-3 py-2 mb-2">Verificación de producto</span>
                    <h2 class="h5 fw-semibold mb-1">Escanea el código de barras</h2>
                    <p class="text-muted mb-0">Usa el lector o ingresa el código manualmente, luego presiona Enter o el botón Buscar.</p>
                </div>
                <span class="text-sm text-muted">Empleado: <?= htmlspecialchars($_SESSION['empleado']['nombre'] ?? 'sin nombre') ?></span>
            </div>

            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label mb-1">Código de Barras</label>
                    <input id="barcodeInput" type="text" class="form-control form-control-lg" placeholder="Escanear o escribir código" autocomplete="off" autofocus>
                </div>
                <div class="col-md-4 d-grid">
                    <button id="checkBtn" type="button" class="btn btn-primary btn-lg">Buscar producto</button>
                </div>
            </div>

            <div id="scanResult" class="mt-4"></div>
            <div class="text-muted small mt-2">Si tu lector de código de barras entrega la secuencia de teclas + Enter, la búsqueda se realizará automáticamente.</div>
        </div>

        <div class="panel-card p-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
                <div>
                    <h2 class="h5 fw-semibold mb-1">Inventario</h2>
                    <p class="text-muted mb-0">Busca por código o nombre y navega 10 productos por página.</p>
                </div>
                <span class="badge bg-primary bg-opacity-15 text-primary rounded-pill px-3 py-2">Mostrando <?= number_format(count($productosPagina)) ?> de <?= number_format($totalProductos) ?></span>
            </div>

            <form method="get" class="row g-3 mb-4">
                <div class="col-md-9">
                    <input type="search" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-lg" placeholder="Buscar por código o nombre...">
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-outline-primary btn-lg">Buscar</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">Img</th>
                            <th>#</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Unidad</th>
                            <th>Precio</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($productosPagina)): ?>
                            <?php foreach ($productosPagina as $index => $producto): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($producto['imagen'])): ?>
                                            <img src="imagenes/<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre'] ?? 'Producto') ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;">
                                        <?php else: ?>
                                            <div style="width: 40px; height: 40px; background: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="bi bi-image text-muted"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $offset + $index + 1 ?></td>
                                    <td class="text-primary fw-semibold"><?= htmlspecialchars($producto['idProducto'] ?? $producto['codigo'] ?? '---') ?></td>
                                    <td><?= htmlspecialchars($producto['nombre'] ?? '---') ?></td>
                                    <td><?= htmlspecialchars($producto['unidad'] ?? '---') ?></td>
                                    <td>$<?= number_format((float)($producto['precioVenta'] ?? $producto['precioCosto'] ?? 0), 2, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($producto['stock'] ?? $producto['existencias'] ?? '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">No se encontraron productos. Ajusta tu búsqueda o agrega inventario.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <nav aria-label="Paginación de productos" class="mt-4">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="empleado.php?pagina=<?= max(1, $paginaActual - 1) ?>&q=<?= urlencode($search) ?>">Anterior</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?= $i === $paginaActual ? 'active' : '' ?>">
                            <a class="page-link" href="empleado.php?pagina=<?= $i ?>&q=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
                        <a class="page-link" href="empleado.php?pagina=<?= min($totalPaginas, $paginaActual + 1) ?>&q=<?= urlencode($search) ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const barcodeInput = document.getElementById('barcodeInput');
        const scanResult = document.getElementById('scanResult');
        const checkBtn = document.getElementById('checkBtn');

        barcodeInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                buscarCodigo();
            }
        });

        checkBtn.addEventListener('click', buscarCodigo);

        function buscarCodigo() {
            const codigo = barcodeInput.value.trim();
            if (!codigo) {
                scanResult.innerHTML = `<div class="alert alert-warning mb-0">Ingresa o escanea un código válido.</div>`;
                return;
            }

            scanResult.innerHTML = `<div class="d-flex align-items-center gap-2 alert alert-secondary mb-0"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Buscando producto...</div>`;

            fetch(`controllers/ProductoController.php?codigo=${encodeURIComponent(codigo)}`)
                .then(response => response.json())
                .then(data => mostrarResultado(data))
                .catch(() => {
                    scanResult.innerHTML = `<div class="alert alert-danger mb-0">No se pudo conectar con el servidor. Revisa tu red o el archivo controlador.</div>`;
                });
        }

        function mostrarResultado(data) {
            if (!data.existe) {
                scanResult.innerHTML = `
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <strong>Producto no encontrado.</strong> El código no existe en la base de datos.
                    </div>`;
                return;
            }

            // 🎉 Mostrar toast de éxito
            mostrarToast(data.nombre ?? 'Producto');

            const precio = Number(data.venta ?? data.precioVenta ?? data.precioCosto ?? 0).toFixed(2);
            const stock = data.stock ?? data.existencias ?? '—';
            const imagenHtml = data.imagen ? `<img src="imagenes/${data.imagen}" alt="${data.nombre}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 10px;">` : `<div style="width: 80px; height: 80px; background: #e2e8f0; border-radius: 10px; display: flex; align-items: center; justify-content: center;"><i class="bi bi-image text-muted"></i></div>`;

            scanResult.innerHTML = `
                <div class="card border-success mb-4" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(5, 150, 105, 0.08) 100%); border-width: 2px;">
                    <div class="card-body p-3">
                        <div class="d-flex gap-3 align-items-start">
                            <div>${imagenHtml}</div>
                            <div style="flex: 1;">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <h6 class="mb-0 fw-bold" style="color: #059669;">✓ Detectado</h6>
                                </div>
                                <p class="mb-2 fw-bold">${data.nombre ?? '---'}</p>
                                <div class="row g-2 small">
                                    <div class="col-auto"><span class="badge bg-secondary">Código: ${data.idProducto ?? '---'}</span></div>
                                    <div class="col-auto"><span class="badge bg-success">$${precio}</span></div>
                                    <div class="col-auto"><span class="badge bg-info">Stock: ${stock}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
        }

        // 🍞 Función para mostrar toast emergente
        function mostrarToast(nombreProducto) {
            const container = document.getElementById('toastContainer');
            const toastId = 'toast-' + Date.now();

            const toastHtml = `
                <div id="${toastId}" class="toast-success d-flex align-items-center gap-3 p-4 mb-3" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>
                        <strong>¡Detectado!</strong>
                        <br>
                        <span class="text-opacity-90">${nombreProducto}</span>
                    </div>
                </div>`;

            container.insertAdjacentHTML('beforeend', toastHtml);
            const toast = document.getElementById(toastId);

            // Remover después de 4 segundos
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.3s ease-out forwards';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    </script>
</body>
</html>
