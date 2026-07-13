<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Edificio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EdificioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $edificios = Edificio::where('estado','A')->get();
        return response()->json($edificios, 200);
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
            'nombre_edificio' => 'required|string|max:100'
        ]);

        $edificio = Edificio::create([
            'nombre_edificio' => $request->nombre_edificio,
            'estado' => 'A'
        ]);

        return response()->json([
            'message' => 'Edificio creado exitosamente',
            'data' => $edificio
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Edificio $edificio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Edificio $edificio)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, $id)
    {
        $edificio = Edificio::findOrFail($id);

        $request->validate([
            'nombre_edificio' => 'required|string|max:100'
        ]);

        // Validar que no existan laboratorios (salones) asociados a este edificio
        $tieneLaboratorios = DB::table('laboratorios')
            ->where('id_edificio', $id)
            ->exists();

        if ($tieneLaboratorios) {
            return response()->json([
                'message' => 'No se puede modificar este edificio porque ya tiene salones asociados.'
            ], 422);
        }

        $edificio->update([
            'nombre_edificio' => $request->nombre_edificio
        ]);

        return response()->json([
            'message' => 'Edificio actualizado exitosamente',
            'data' => $edificio
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
         $edificio = Edificio::where('id_edificio', $id)
                    ->where('estado', 'A')
                    ->firstOrFail();

        $edificio->update(['estado' => 'I']);

        return response()->json([
            'message' => 'Edificio eliminado correctamente'
        ], 200);
    }

    
}
