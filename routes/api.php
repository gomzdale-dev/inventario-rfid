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

Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Tipo de Usuario
    Route::get('/roles', [TipoUsuarioController::class, 'index']);
    Route::apiResource('tipo-usuarios', TipoUsuarioController::class);

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
});
