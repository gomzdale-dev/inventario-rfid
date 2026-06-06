<?php

namespace App\Http\Controllers\Api;

use App\Models\Ubicacion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UbicacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         return response()->json(Ubicacion::with('ubicacion')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $ubicacion = new Ubicacion();
        $ubicacion->id_laboratorio = $request->id_laboratorio;
        $ubicacion->estado = 'A';
        $ubicacion->save();
        return response([
            'message' => 'ubicacion creada exitosamente',
            'usuario' => $ubicacion
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ubicacion $ubicacion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ubicacion $ubicacion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ubicacion $ubicacion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ubicacion $ubicacion)
    {
        //
    }
}
