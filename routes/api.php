<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\TipoUsuarioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\EdificioController;
use App\Http\Controllers\Api\MarcaController;
use App\Http\Controllers\Api\ModeloController;
use App\Http\Controllers\Api\LaboratorioController;
use App\Http\Controllers\Api\ResponsableController;

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
Route::get('/categoria', [CategoriaController::class, 'index']);
Route::post('/categoria', [CategoriaController::class, 'store']);
Route::put('/categoria/{id}', [CategoriaController::class, 'update']);
Route::delete('/categoria/{id}', [CategoriaController::class, 'destroy']);

//EDIFICIO
Route::get('/edificio',     [EdificioController::class, 'index']);
Route::post('/edificio',             [EdificioController::class, 'store']);
Route::put('/edificio/{id}', [EdificioController::class, 'update']);

//MARCA
Route::get('/marca',         [MarcaController::class, 'index']);
Route::post('/marca',        [MarcaController::class, 'store']);
Route::put('/marca/{id}',    [MarcaController::class, 'update']);
Route::delete('/marca/{id}', [MarcaController::class, 'destroy']);

//MODELO
Route::get('/modelo',         [ModeloController::class, 'index']);
Route::post('/modelo',        [ModeloController::class, 'store']);
Route::put('/modelo/{id}',    [ModeloController::class, 'update']);
Route::delete('/modelo/{id}', [ModeloController::class, 'destroy']);

//LABORATORIO
Route::get('/laboratorio',         [LaboratorioController::class, 'index']);
Route::post('/laboratorio',        [LaboratorioController::class, 'store']);
Route::put('/laboratorio/{id}',    [LaboratorioController::class, 'update']);

//RESPONSABLE
Route::get('/responsable',         [ResponsableController::class, 'index']);
Route::post('/responsable',        [ResponsableController::class, 'store']);
Route::put('/responsable/{id}',    [ResponsableController::class, 'update']);