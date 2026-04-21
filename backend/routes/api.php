<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\TarjetaController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\FacturaDetalleController;

// ─── PÚBLICOS ────────────────────────────────────────────
Route::get('/categorias', [CategoriaController::class, 'index']);
Route::get('/categorias/{id}', [CategoriaController::class, 'show']);

Route::get('/marcas', [MarcaController::class, 'index']);
Route::get('/marcas/{id}', [MarcaController::class, 'show']);

Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/{id}', [ProductoController::class, 'show']);

Route::post('login', [EmpleadoController::class, 'login']);

// ─── PROTEGIDOS ──────────────────────────────────────────
Route::apiResource('categorias', CategoriaController::class)->except(['index', 'show']);
Route::apiResource('marcas',     MarcaController::class)->except(['index', 'show']);
Route::apiResource('productos',  ProductoController::class)->except(['index', 'show']);

Route::apiResource('empleados', EmpleadoController::class);
Route::apiResource('tarjetas',  TarjetaController::class);

Route::apiResource('facturas', FacturaController::class)->except(['update', 'destroy']);
Route::get('/facturas/{id}/detalle',  [FacturaDetalleController::class, 'index']);
Route::post('/facturas/{id}/detalle', [FacturaDetalleController::class, 'store']);

// ─── RELACIONES / FILTROS ────────────────────────────────
Route::get('/categorias/{id}/productos', [ProductoController::class, 'porCategoria']);
Route::get('/marcas/{id}/productos',     [ProductoController::class, 'porMarca']);
Route::get('/empleados/{id}/facturas',   [FacturaController::class, 'porEmpleado']);