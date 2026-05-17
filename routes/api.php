<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\TipoUsuarioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\EdificioController;

//Login
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout',[AuthController::class, 'logout']);

//Tipo de Usuario
Route::get('/roles', [TipoUsuarioController::class, 'index']);
Route::apiResource('tipo-usuarios', TipoUsuarioController::class);

// Usuario
Route::middleware(['auth:sanctum'])->group(function () {
Route::apiResource('usuarios', UsuarioController::class);});

//CATEGORIA
Route::get('/categoria/categorias', [CategoriaController::class, 'index']);
Route::post('/categoria', [CategoriaController::class, 'store']);
Route::put('/categoria/{id}', [CategoriaController::class, 'update']);
Route::delete('/categoria/{id}', [CategoriaController::class, 'destroy']);

//EDIFICIO
Route::get('/edificio/edificios',     [EdificioController::class, 'index']);
Route::post('/iedificio',             [EdificioController::class, 'store']);
Route::put('/edificio/{id}', [EdificioController::class, 'update']);
