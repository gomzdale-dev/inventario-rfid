<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\TipoUsuarioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\EdificioController;
use App\Http\Controllers\Api\ActivoController;
use App\Http\Controllers\Api\MarcaController;
use App\Http\Controllers\Api\ModeloController;
use App\Http\Controllers\Api\LaboratorioController;
use App\Http\Controllers\Api\ResponsableController;

// LOGIN
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// TIPO DE USUARIO / ROLES
Route::get('/roles', [TipoUsuarioController::class, 'index']);
Route::apiResource('tipo-usuarios', TipoUsuarioController::class);

// USUARIOS
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('usuarios', UsuarioController::class);
    Route::put('/change-password', [UsuarioController::class, 'changePassword']);
});

// CATEGORÍAS
Route::get('/categoria', [CategoriaController::class, 'index']);
Route::post('/categoria', [CategoriaController::class, 'store']);
Route::put('/categoria/{id}', [CategoriaController::class, 'update']);
Route::delete('/categoria/{id}', [CategoriaController::class, 'destroy']);

// EDIFICIOS
Route::get('/edificio', [EdificioController::class, 'index']);
Route::post('/edificio', [EdificioController::class, 'store']);
Route::put('/edificio/{id}', [EdificioController::class, 'update']);
Route::delete('/edificio/{id}', [EdificioController::class, 'destroy']);

// MARCAS
Route::get('/marca', [MarcaController::class, 'index']);
Route::post('/marca', [MarcaController::class, 'store']);
Route::put('/marca/{id}', [MarcaController::class, 'update']);
Route::delete('/marca/{id}', [MarcaController::class, 'destroy']);

// MODELOS
Route::get('/modelo', [ModeloController::class, 'index']);
Route::post('/modelo', [ModeloController::class, 'store']);
Route::put('/modelo/{id}', [ModeloController::class, 'update']);
Route::delete('/modelo/{id}', [ModeloController::class, 'destroy']);

// LABORATORIOS
Route::get('/laboratorio', [LaboratorioController::class, 'index']);
Route::post('/laboratorio', [LaboratorioController::class, 'store']);
Route::put('/laboratorio/{id}', [LaboratorioController::class, 'update']);
Route::delete('/laboratorio/{id}', [LaboratorioController::class, 'destroy']);

// RESPONSABLES
Route::get('/responsable', [ResponsableController::class, 'index']);
Route::post('/responsable', [ResponsableController::class, 'store']);
Route::put('/responsable/{id}', [ResponsableController::class, 'update']);
Route::delete('/responsable/{id}', [ResponsableController::class, 'destroy']);

// ACTIVOS
Route::get('/activos', [ActivoController::class, 'index']);
Route::get('/activos/catalogos', [ActivoController::class, 'catalogos']);
Route::put('/activos/{id}/asignaciones', [ActivoController::class, 'actualizarAsignaciones']);