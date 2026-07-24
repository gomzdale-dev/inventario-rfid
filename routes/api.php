<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\TipoUsuarioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\EdificioController;
use App\Http\Controllers\Api\ActivoController;
use App\Http\Controllers\Api\EstadoActivoController;
use App\Http\Controllers\Api\MarcaController;
use App\Http\Controllers\Api\ModeloController;
use App\Http\Controllers\Api\LaboratorioController;
use App\Http\Controllers\Api\ResponsableController;

use App\Http\Controllers\Api\AlertaController;
use App\Http\Controllers\Api\RfidScanController;
use App\Http\Controllers\Api\UbicacionController;
use App\Http\Controllers\Api\DetalleInventarioController;
use App\Http\Controllers\Api\EtiquetasRfidController;
use App\Http\Controllers\Api\InventarioController;
use App\Http\Controllers\Api\MovimientoController;
use App\Http\Controllers\Api\ReporteController;

// LOGIN
Route::post('/login', [AuthController::class, 'login']);

// REPORTES
Route::match(['get', 'post'], 'reportes/exportar', [ReporteController::class, 'exportarReporte']);

// RFID DEL DISPOSITIVO ESP32
// Se deja fuera de auth:sanctum porque el ESP32 no inicia sesión como usuario.
// La seguridad se valida mediante X-Device-Key dentro del controlador.
Route::post('/rfid-scan', [RfidScanController::class, 'store']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {

    // REPORTES FUNCIONALES
    Route::get('/reportes/catalogos', [ReporteController::class, 'catalogos']);
    Route::get('/reportes/vista', [ReporteController::class, 'vistaReporte']);
    Route::get('/reportes/resumen', [ReporteController::class, 'resumenReportes']);

    // MOVIMIENTOS DE ACTIVOS
    Route::get('/movimientos/activo-rfid/{codigo}', [MovimientoController::class, 'activoPorRfid']);
    Route::get('/movimientos/catalogos', [MovimientoController::class, 'catalogos']);
    Route::apiResource('movimientos', MovimientoController::class);

    // CATÁLOGOS Y ASIGNACIONES DE ACTIVOS
    Route::get('/activo-catalogos', [ActivoController::class, 'catalogos']);
    Route::put('/activo/{id}/asignaciones', [ActivoController::class, 'actualizarAsignaciones']);
    Route::put('/activo/{id}/etiqueta-rfid', [ActivoController::class, 'actualizarEtiquetaRfid']);

    // ALERTAS
    Route::get('/alertas', [AlertaController::class, 'index']);
    Route::post('/alertas', [AlertaController::class, 'store']);
    Route::put('/alertas/{id}/leer', [AlertaController::class, 'marcarLeida']);
    Route::put('/alertas/marcar-todas', [AlertaController::class, 'marcarTodas']);
    Route::delete('/alertas/{id}', [AlertaController::class, 'destroy']);
    Route::post('/alertas/simular-rfid', [AlertaController::class, 'simularRfid']);
    Route::post('/alertas/simular-ia', [AlertaController::class, 'simularIa']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Tipo de Usuario
    Route::get('/roles', [TipoUsuarioController::class, 'index']);
    Route::apiResource('tipo-usuarios', TipoUsuarioController::class);
    Route::put('/change-password', [UsuarioController::class, 'changePassword']);

    // Usuarios
    Route::apiResource('usuarios', UsuarioController::class);

    // CATEGORIA
    Route::apiResource('categoria', CategoriaController::class);

    // EDIFICIO
    Route::apiResource('edificio', EdificioController::class);

    // MARCA
    Route::apiResource('marca', MarcaController::class);

    // MODELO
    Route::apiResource('modelo', ModeloController::class);

    // LABORATORIO
    Route::apiResource('laboratorio', LaboratorioController::class);

    // RESPONSABLE
    Route::apiResource('responsable', ResponsableController::class);

    //ESTADO DEL ACTIVO
    Route::apiResource('estado', EstadoActivoController::class);

    //ACTIVO
    Route::apiResource('activo', ActivoController::class);

    //UBICACION
    Route::apiResource('ubicacion', UbicacionController::class);

    Route::get('/inventario/catalogos', [InventarioController::class, 'catalogos']);
    Route::get('/inventario/activos-ubicacion', [InventarioController::class, 'activosPorUbicacion']);
    Route::apiResource('inventario', InventarioController::class);
    Route::apiResource('detalle', DetalleInventarioController::class);


    //INVENTARIO
    Route::apiResource('inventario', InventarioController::class);

    //DETALLE INVENTARIO
    Route::apiResource('detalle', DetalleInventarioController::class);

    //ETIQUETA
    Route::apiResource('etiqueta', EtiquetasRfidController::class);
});