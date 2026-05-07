<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\TipoUsuarioController;

Route::resource('tipo-usuarios', TipoUsuarioController::class);

use App\Http\Controllers\UsuarioController;

Route::resource('usuarios', UsuarioController::class);