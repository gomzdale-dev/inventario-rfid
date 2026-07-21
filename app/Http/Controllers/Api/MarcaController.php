<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Marca;
use Illuminate\Http\Request;


class MarcaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $marcas = Marca::where('estado', 'A')->get();
        return response()->json($marcas, 200);
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
        'nombre_marca' => [
        'required',
        'string',
        'max:100',
        'regex:/^[\p{L}\s]+$/u'
    ],
    ], [
        'nombre_marca.regex' => 'El nombre de la marca solo puede contener letras y espacios.'
    ]);

        $marca = Marca::create([
            'nombre_marca' => $request->nombre_marca,
            'estado'       => 'A'
        ]);

        return response()->json([
            'message' => 'Marca creada correctamente',
            'data'    => $marca
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Marca $marca)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Marca $marca)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_marca' => 'required|string|max:100'
        ]);

        $marca = Marca::where('id_marca', $id)
                    ->where('estado', 'A')
                    ->firstOrFail();

        // Validar que no existan modelos asociados a esta marca
        $tieneModelos = \DB::table('modelos')
            ->where('id_marca', $id)
            ->exists();

        if ($tieneModelos) {
            return response()->json([
                'message' => 'No se puede modificar esta marca porque ya tiene modelos asociados.'
            ], 422);
        }

        $marca->update(['nombre_marca' => $request->nombre_marca]);

        return response()->json([
            'message' => 'Marca actualizada correctamente',
            'data'    => $marca
        ], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $marca = Marca::where('id_marca', $id)
                    ->where('estado', 'A')
                    ->firstOrFail();

        $marca->update(['estado' => 'I']);

        return response()->json([
            'message' => 'Marca eliminada correctamente'
        ], 200);
    }
}
