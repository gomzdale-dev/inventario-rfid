<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\TipoUsuarioController;
use App\Http\Controllers\Api\AuthController;

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