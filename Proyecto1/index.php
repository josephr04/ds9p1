<?php
require_once "config/conexion.php";
include "includes/head.php";
?>
<?php
$porPagina = 5;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaActual - 1) * $porPagina;

$totalResult = $conexion->query("SELECT COUNT(*) as total FROM productos");
$totalProductos = $totalResult->fetch_object()->total;
$totalPaginas = ceil($totalProductos / $porPagina);
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

        .page-link {
            border: none;
            color: #64748b;
            margin: 0 3px;
            border-radius: 8px !important;
        }

        .page-item.active .page-link {
            background: var(--primary-gradient);
        }

        .alert-modern {
            border-radius: 12px;
            border: none;
            font-size: 0.9rem;
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

        <!-- ✅ ALERTAS CON MOTIVO DETALLADO -->
        <?php if (isset($_GET['res'])): ?>
            <div class="row">
                <div class="col-12">
                    <?php if ($_GET['res'] == 'success'): ?>
                        <div class="alert alert-modern alert-success d-flex align-items-center shadow-sm border-0" role="alert">
                            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                            <div>
                                <strong class="d-block">¡Operación Exitosa!</strong>
                                <span class="small">El producto ha sido guardado correctamente.</span>
                            </div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php elseif ($_GET['res'] == 'error'): ?>
                        <?php
                        $motivo = $_GET['motivo'] ?? '';
                        if ($motivo === 'duplicado') {
                            $titulo = "Código de barras duplicado";
                            $detalle = "Ese código ya existe en la base de datos.";
                        } elseif ($motivo === 'imagen') {
                            $titulo = "Formato de imagen no válido";
                            $detalle = "Solo se permiten imágenes JPG o PNG.";
                        } else {
                            $titulo = "Hubo un problema";
                            $detalle = "No se pudo completar el registro. Verifica los datos e intenta de nuevo.";
                        }
                        ?>
                        <div class="alert alert-modern alert-danger d-flex align-items-center shadow-sm border-0" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                            <div>
                                <strong class="d-block"><?= $titulo ?></strong>
                                <span class="small"><?= $detalle ?></span>
                            </div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <script>
                setTimeout(() => {
                    const url = new URL(window.location);
                    url.searchParams.delete('res');
                    url.searchParams.delete('motivo');
                    window.history.replaceState({}, document.title, url);
                }, 3000);
            </script>
        <?php endif; ?>

        <div class="row g-4">
            <!-- FORMULARIO REGISTRO -->
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
                                <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-upc"></i></span>
                                <input type="text" name="codigo" id="codigo" class="form-control border-start-0" placeholder="Escanear o digitar..." required maxlength="13">
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
                            <label class="form-label">Marca</label>
                            <select name="marca" class="form-select" required>
                                <option value="" disabled selected>Seleccione una marca</option>
                                <?php
                                $marcas = $conexion->query("SELECT * FROM marca");
                                while ($m = $marcas->fetch_object()) {
                                    echo "<option value='$m->idMarca'>$m->nombreMarc</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Categoría</label>
                            <select name="categoria" class="form-select" required>
                                <option value="" disabled selected>Seleccione una categoría</option>
                                <?php
                                $categorias = $conexion->query("SELECT * FROM categoria");
                                while ($cat = $categorias->fetch_object()) {
                                    echo "<option value='$cat->idCategoria'>$cat->nombreCat</option>";
                                }
                                ?>
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
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción Corta</label>
                            <textarea name="descripcion" class="form-control" rows="2" required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Imagen Referencial</label>
                            <input type="file" name="imagen" class="form-control" accept=".jpg,.jpeg,.png" required>
                        </div>

                        <div id="mensaje"></div>

                        <button type="submit" name="btnregistrar" value="ok" class="btn btn-primary-custom w-100 shadow-sm mt-2">
                            <i class="bi bi-cloud-arrow-up me-2"></i>Guardar Producto
                        </button>
                    </form>
                </div>
            </div>

            <!-- TABLA DE PRODUCTOS -->
            <div class="col-lg-7">
                <div class="modern-card">
                    <div class="p-4 border-bottom bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Productos Registrados</h5>
                        <span class="badge bg-light text-primary rounded-pill px-3 py-2"><?= $totalProductos ?> en total</span>
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
                                <?php
                                $sql = $conexion->query("
                                    SELECT p.*, c.nombreCat 
                                    FROM productos p 
                                    LEFT JOIN categoria c ON p.idCategoria = c.idCategoria
                                    LIMIT $porPagina OFFSET $offset
                                ");
                                while ($datos = $sql->fetch_object()) { ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?= 'http://localhost/Proyecto%201/imagenes/' . $datos->imagen ?>" class="product-img me-3" width="48" height="48">
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($datos->nombre) ?></div>
                                                    <small class="text-muted text-uppercase" style="font-size: 10px;">ID: #<?= $datos->idProducto ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded-2">
                                                <?= htmlspecialchars($datos->nombreCat) ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold">$<?= number_format($datos->precioCosto, 2) ?></td>
                                        <td class="text-end">
                                            <div class="btn-group shadow-sm border rounded-3 p-1 bg-white">
                                                <button class="btn btn-link btn-sm text-warning p-1" onclick="editarProducto('<?= $datos->idProducto ?>')">
                                                    <i class="bi bi-pencil-square fs-5"></i>
                                                </button>
                                                <button class="btn btn-link btn-sm text-danger p-1" onclick="eliminarProducto('<?= $datos->idProducto ?>')">
                                                    <i class="bi bi-trash3 fs-5"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINACIÓN -->
                    <div class="p-4">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link shadow-sm" href="?pagina=<?= $paginaActual - 1 ?>">&laquo;</a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                    <li class="page-item <?= ($i == $paginaActual) ? 'active' : '' ?>">
                                        <a class="page-link shadow-sm" href="?pagina=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
                                    <a class="page-link shadow-sm" href="?pagina=<?= $paginaActual + 1 ?>">&raquo;</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR -->
    <div class="modal fade" id="modalEditar" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 p-4">
                    <h5 class="modal-title fw-bold">Editar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div id="modalMensaje"></div>
                    <form id="formEditar">
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
                    </form>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancelar</button>
                    <!-- ✅ Spinner de carga visible durante el guardado -->
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>

    <script>
        // ============================================================
        // EDITAR PRODUCTO — abre el modal con los datos actuales
        // ============================================================
        function editarProducto(id) {
            fetch("controllers/obtenerProductoController.php?id=" + id)
                .then(res => res.json())
                .then(data => {
                    document.getElementById("edit_id").value = data.idProducto;
                    document.getElementById("edit_nombre").value = data.nombre;
                    document.getElementById("edit_unidad").value = data.unidad;
                    document.getElementById("edit_precio").value = data.precioCosto;
                    document.getElementById("modalMensaje").innerHTML = "";

                    let modal = new bootstrap.Modal(document.getElementById('modalEditar'));
                    modal.show();
                })
                .catch(() => alert("Error al obtener los datos del producto."));
        }

        // ============================================================
        // GUARDAR EDICIÓN — hace el POST al backend y recarga la tabla
        // ============================================================
        function guardarEdicion() {
            let id = document.getElementById("edit_id").value;
            let nombre = document.getElementById("edit_nombre").value.trim();
            let unidad = document.getElementById("edit_unidad").value.trim();
            let precio = document.getElementById("edit_precio").value;

            if (!nombre || !unidad || !precio) {
                document.getElementById("modalMensaje").innerHTML = `
                    <div class="alert alert-warning alert-modern py-2 mb-3">
                        <i class="bi bi-exclamation-circle me-2"></i>Completa todos los campos antes de guardar.
                    </div>`;
                return;
            }

            // Mostrar spinner
            document.getElementById("btnGuardarTexto").classList.add("d-none");
            document.getElementById("btnGuardarSpinner").classList.remove("d-none");
            document.getElementById("btnGuardar").disabled = true;

            let formData = new FormData();
            formData.append("id", id);
            formData.append("nombre", nombre);
            formData.append("unidad", unidad);
            formData.append("precio", precio);

            fetch("controllers/actualizarProductoController.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.ok) {
                        bootstrap.Modal.getInstance(document.getElementById('modalEditar')).hide();
                        location.reload();
                    } else {
                        document.getElementById("modalMensaje").innerHTML = `
                        <div class="alert alert-danger alert-modern py-2 mb-3">
                            <i class="bi bi-x-circle me-2"></i>${data.mensaje}
                        </div>`;
                    }
                })
                .catch(() => {
                    document.getElementById("modalMensaje").innerHTML = `
                    <div class="alert alert-danger alert-modern py-2 mb-3">
                        <i class="bi bi-wifi-off me-2"></i>Error de conexión al guardar.
                    </div>`;
                })
                .finally(() => {
                    // Restaurar botón
                    document.getElementById("btnGuardarTexto").classList.remove("d-none");
                    document.getElementById("btnGuardarSpinner").classList.add("d-none");
                    document.getElementById("btnGuardar").disabled = false;
                });
        }

        // ============================================================
        // ELIMINAR PRODUCTO
        // ============================================================
        function eliminarProducto(id) {
            if (confirm('¿Desea eliminar este producto? Esta acción no se puede deshacer.')) {
                fetch("controllers/eliminarProductoController.php?id=" + encodeURIComponent(id))
                    .then(res => res.json())
                    .then(data => {
                        if (data.ok) {
                            location.reload();
                        } else {
                            alert("Error al eliminar: " + data.mensaje);
                        }
                    })
                    .catch(() => alert("Error de conexión al intentar eliminar."));
            }
        }

        // ============================================================
        // BÚSQUEDA AUTOMÁTICA POR CÓDIGO (al escribir)
        // ============================================================
        let timeout;

        document.getElementById("codigo").addEventListener("input", function() {
            let codigo = this.value.trim();
            clearTimeout(timeout);

            if (codigo === "") {
                limpiarFormulario();
                return;
            }

            timeout = setTimeout(() => {
                fetch("controllers/ProductoController.php?codigo=" + encodeURIComponent(codigo))
                    .then(res => res.json())
                    .then(data => mostrarResultadoBusqueda(data))
                    .catch(() => {
                        let mensaje = document.getElementById("mensaje");
                        mensaje.innerHTML = `
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-wifi-off fs-5"></i>
                                <div><strong>Error de conexión</strong><br>
                                <small>No se pudo consultar el servidor.</small></div>
                            </div>`;
                        mensaje.className = "alert alert-danger mt-2";
                    });
            }, 400);
        });

        // BÚSQUEDA CON LECTOR FÍSICO (tecla Enter)
        document.getElementById("codigo").addEventListener("keydown", function(e) {
            if (e.keyCode === 13) {
                e.preventDefault();
                let codigo = this.value.trim();
                if (codigo !== "") {
                    clearTimeout(timeout); // Cancelar el debounce pendiente
                    fetch("controllers/ProductoController.php?codigo=" + encodeURIComponent(codigo))
                        .then(res => res.json())
                        .then(data => mostrarResultadoBusqueda(data))
                        .catch(err => console.error("Error:", err));
                }
            }
        });

        // Función compartida para mostrar resultado de búsqueda
        function mostrarResultadoBusqueda(data) {
            let mensaje = document.getElementById("mensaje");

            if (data.existe) {
                document.querySelector("[name='nombre']").value = data.nombre;
                document.querySelector("[name='unidad']").value = data.unidad;
                document.querySelector("[name='descripcion']").value = data.descripcion;

                document.querySelector("[name='nombre']").readOnly = true;
                document.querySelector("[name='unidad']").readOnly = true;
                document.querySelector("[name='descripcion']").readOnly = true;

                mensaje.innerHTML = `
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <div><strong>Producto encontrado</strong><br>
                        <small>Este código ya existe en la base de datos.</small></div>
                    </div>`;
                mensaje.className = "alert alert-success mt-2";

            } else {
                limpiarFormulario(false);
                mensaje.innerHTML = `
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-circle-fill fs-5"></i>
                        <div><strong>Producto no encontrado</strong><br>
                        <small>Este código no existe, puedes registrarlo como nuevo.</small></div>
                    </div>`;
                mensaje.className = "alert alert-warning mt-2";
                document.querySelector("[name='nombre']").focus();
            }
        }

        // ============================================================
        // LIMPIAR FORMULARIO
        // ============================================================
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
    </script>

</body>

</html>