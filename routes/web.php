<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TipoUsuarioController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActivoController;

Route::get('/', function () {

    return view('app');
});

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/api/activos', [ActivoController::class, 'index']);


Route::middleware('auth')->group(function () {
    Route::resource('usuarios', UsuarioController::class);

Route::resource('tipo-usuarios', TipoUsuarioController::class);
Route::resource('usuarios', UsuarioController::class);

});
