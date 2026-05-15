<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TipoUsuarioController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {

    return view('app');
});

<<<<<<< HEAD
=======
Route::resource('usuarios', UsuarioController::class);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/catalogos/categorias', [CatalogoController::class, 'index']);

Route::middleware('auth')->group(function () {

    Route::resource('usuarios', UsuarioController::class);

});
>>>>>>> origin/jonatan
