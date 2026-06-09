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
use App\Models\Detalle_Inventario;
use App\Http\Controllers\Api\MovimientoController;

// LOGIN
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {

    // MOVIMIENTOS DE ACTIVOS
    Route::get('/movimientos/catalogos', [MovimientoController::class, 'catalogos']);
    Route::apiResource('movimientos', MovimientoController::class);

    // CATÁLOGOS Y ASIGNACIONES DE ACTIVOS
    Route::get('/activo-catalogos', [ActivoController::class, 'catalogos']);
    Route::put('/activo/{id}/asignaciones', [ActivoController::class, 'actualizarAsignaciones']);

    // ALERTAS
    Route::get('/alertas', [AlertaController::class, 'index']);
    Route::post('/alertas', [AlertaController::class, 'store']);
    Route::put('/alertas/{id}/leer', [AlertaController::class, 'marcarLeida']);
    Route::put('/alertas/marcar-todas', [AlertaController::class, 'marcarTodas']);
    Route::delete('/alertas/{id}', [AlertaController::class, 'destroy']);
    Route::post('/alertas/simular-rfid', [AlertaController::class, 'simularRfid']);
    Route::post('/alertas/simular-ia', [AlertaController::class, 'simularIa']);

    // RFID SCAN
    Route::post('/rfid-scan', [RfidScanController::class, 'store']);

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

    //INVENTARIO
    Route::apiResource('inventario',InventarioController::class);

    //DETALLE INVENTARIO
    Route::apiResource('detalle', DetalleInventarioController::class);

    //ETIQUETA
    Route::apiResource('etiqueta', EtiquetasRfidController::class);
});
