<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laboratorio;
use Illuminate\Http\Request;

class LaboratorioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $laboratorios = Laboratorio::all();
        return response()->json($laboratorios, 200);
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
            'nombre_laboratorio' => 'required|string|max:100',
            'id_edificio'        => 'required|integer'
        ]);

        $laboratorio = Laboratorio::create([
            'nombre_laboratorio' => $request->nombre_laboratorio,
            'id_edificio'        => $request->id_edificio
        ]);

        return response()->json([
            'message' => 'Laboratorio creado correctamente',
            'data'    => $laboratorio
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Laboratorio $laboratorio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Laboratorio $laboratorio)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_laboratorio' => 'required|string|max:100',
            'id_edificio'        => 'required|integer'
        ]);

        $laboratorio = Laboratorio::where('id_laboratorio', $id)->firstOrFail();

        $laboratorio->update([
            'nombre_laboratorio' => $request->nombre_laboratorio,
            'id_edificio'        => $request->id_edificio
        ]);

        return response()->json([
            'message' => 'Laboratorio actualizado correctamente',
            'data'    => $laboratorio
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Laboratorio $laboratorio)
    {
        //
    }
}
