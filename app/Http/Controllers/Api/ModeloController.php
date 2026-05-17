<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Modelo;
use Illuminate\Http\Request;


class ModeloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $modelos = Modelo::where('estado', 'A')->get();
        return response()->json($modelos, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_modelo' => 'required|string|max:100',
            'id_marca'      => 'required|integer'
        ]);

        $modelo = Modelo::create([
            'nombre_modelo' => $request->nombre_modelo,
            'id_marca'      => $request->id_marca,
            'estado'        => 'A'
        ]);

        return response()->json([
            'message' => 'Modelo creado correctamente',
            'data'    => $modelo
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(Modelo $modelo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Modelo $modelo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_modelo' => 'required|string|max:100',
            'id_marca'      => 'required|integer'
        ]);

        $modelo = Modelo::where('id_modelo', $id)
                        ->where('estado', 'A')
                        ->firstOrFail();

        $modelo->update([
            'nombre_modelo' => $request->nombre_modelo,
            'id_marca'      => $request->id_marca
        ]);

        return response()->json([
            'message' => 'Modelo actualizado correctamente',
            'data'    => $modelo
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $modelo = Modelo::where('id_modelo', $id)
                        ->where('estado', 'A')
                        ->firstOrFail();

        $modelo->update(['estado' => 'I']);

        return response()->json([
            'message' => 'Modelo eliminado correctamente'
        ], 200);
    }
}
