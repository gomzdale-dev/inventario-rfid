<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TipoUsuarioController;
use App\Http\Controllers\UsuarioController;

Route::get('/', function () {

    return view('app');
});
Route::resource('tipo-usuarios', TipoUsuarioController::class);

Route::resource('usuarios', UsuarioController::class);
