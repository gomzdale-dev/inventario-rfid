<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Responsable;
use Illuminate\Http\Request;


class ResponsableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $responsables = Responsable::all()->map(function ($r) {
            return [
                'id'                  => $r->id,
                'nombre_responsable'  => $r->nombre . ' ' . $r->apellido,
                'codigo_empleado'     => $r->codigo_empleado
            ];
        });

        return response()->json($responsables, 200);
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
        'nombre'          => 'required|string|max:100',
        'apellido'        => 'required|string|max:100',
        'codigo_empleado' => 'required|string|max:50'
    ]);

    $responsable = Responsable::create([
        'nombre'           => $request->nombre,
        'apellido'         => $request->apellido,
        'codigo_empleado'  => $request->codigo_empleado,
        'fecha_ingreso'    => now(),
        'usuario_ingreso'  => 'SISTEMA',
        'fecha_modifica'   => now(),
        'usuario_modifica' => 'SISTEMA'
    ]);

    return response()->json([
        'message' => 'Responsable creado correctamente',
        'data'    => [
            'id'                 => $responsable->id,
            'nombre_responsable' => $responsable->nombre . ' ' . $responsable->apellido,
            'codigo_empleado'    => $responsable->codigo_empleado
        ]
    ], 201);
}

    /**
     * Display the specified resource.
     */
    public function show(Responsable $responsable)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Responsable $responsable)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'          => 'required|string|max:100',
            'apellido'        => 'required|string|max:100',
            'codigo_empleado' => 'required|string|max:50'
        ]);

        $responsable = Responsable::findOrFail($id);

        $responsable->update([
            'nombre'          => $request->nombre,
            'apellido'        => $request->apellido,
            'codigo_empleado' => $request->codigo_empleado
        ]);

        return response()->json([
            'message' => 'Responsable actualizado correctamente',
            'data'    => [
                'id'                 => $responsable->id,
                'nombre_responsable' => $responsable->nombre . ' ' . $responsable->apellido,
                'codigo_empleado'    => $responsable->codigo_empleado
            ]
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Responsable $responsable)
    {
        //
    }
}
