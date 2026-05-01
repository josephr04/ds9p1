# Proyecto 1

## Qué hemos hecho

Este repositorio contiene un proyecto PHP simple de carrito de compras con las siguientes funcionalidades:

- Gestión de productos
- Agregar productos al carrito
- Eliminar productos del carrito
- Proceso de pago
- Página de confirmación de compra

El objetivo ha sido organizar el código y documentar cómo acceder y usar el proyecto.

## Estructura principal del proyecto

- `carrito.php` — muestra el carrito y permite eliminar productos.
- `checkout.php` — muestra el formulario de pago.
- `detalle.php` — muestra el detalle de un producto.
- `index.php` — página principal y listado de productos.
- `login.php` / `logout.php` — autenticación básica.
- `pago_exitoso.php` — página final luego del pago.
- `store.php` — gestiona el guardado de datos del carrito.

Carpetas importantes:

- `config/` — conexión a la base de datos.
- `controllers/` — controladores que procesan la lógica del carrito, pagos y productos.
- `model/` — clases y funciones de acceso a datos.
- `includes/` — elementos compartidos como el encabezado.
- `imagenes/` — imágenes usadas en el sitio.

## Cómo abrir y usar el proyecto

1. Abre el explorador de archivos.
2. Ve a `C:\xampp\htdocs\Proyecto1\`.
3. Abre `Proyecto1` en VS Code.
4. Asegúrate de tener XAMPP activo y Apache iniciado.
5. Coloca el proyecto dentro de `htdocs` si aún no está allí.
6. Abre un navegador e ingresa:
   - `http://localhost/Proyecto1/index.php`

## Notas adicionales

- El proyecto está hecho en PHP con una estructura sencilla.
- Si necesitas cambiar la conexión a la base de datos, edita `config/conexion.php`.
- El README actualiza lo que se ha trabajado hasta ahora y facilita el acceso al código.
