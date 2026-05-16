<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\TipoUsuarioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\EdificioController;
use App\Http\Controllers\Api\ActivoController;

//Login
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

//Tipo de Usuario
Route::get('/roles', [TipoUsuarioController::class, 'index']);
Route::apiResource('tipo-usuarios', TipoUsuarioController::class);

// Usuario
Route::apiResource('usuarios', UsuarioController::class);
Route::post('/usuarios', [UsuarioController::class, 'store']);
Route::put('/usuarios/{id}', [UsuarioController::class, 'update']);


//CATEGORIA
Route::get('/categoria/categorias', [CategoriaController::class, 'index']);
Route::post('/categoria', [CategoriaController::class, 'store']);
Route::put('/categoria/{id}', [CategoriaController::class, 'update']);
Route::delete('/categoria/{id}', [CategoriaController::class, 'destroy']);

//EDIFICIO
Route::get('/edificio/edificios',     [EdificioController::class, 'index']);
Route::post('/iedificio',             [EdificioController::class, 'store']);
Route::put('/edificio/{id}', [EdificioController::class, 'update']);

// ACTIVOS - Gestión de responsable, ubicación y etiqueta RFID
Route::get('/activos', [ActivoController::class, 'index']);
Route::get('/activos/catalogos', [ActivoController::class, 'catalogos']);
Route::put('/activos/{id}/asignaciones', [ActivoController::class, 'actualizarAsignaciones']);