<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\EdificioController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//CATEGORIA
Route::get('/categoria/categorias', [CategoriaController::class, 'index']);
Route::post('/categoria', [CategoriaController::class, 'store']);
Route::put('/categoria/{id}', [CategoriaController::class, 'update']);
Route::delete('/categoria/{id}', [CategoriaController::class, 'destroy']);

//EDIFICIO
Route::get('/edificio/edificios',     [EdificioController::class, 'index']);
Route::post('/iedificio',             [EdificioController::class, 'store']);
Route::put('/edificio/{id}', [EdificioController::class, 'update']);
