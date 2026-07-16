<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoUsuario;
class TipoUsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {        
        return response()->json(TipoUsuario::where('estado', 'A')->get());
    }

   
}
