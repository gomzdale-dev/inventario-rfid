<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = DB::table('categorias')
            ->select('id_categoria', 'nombre_categoria', 'estado')
            ->where('estado', 'A')
            ->get();

        return response()->json($categorias);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_categoria' => 'required|string|max:255',
        ]);

        $categoria = Categoria::create([
            'nombre_categoria' => $validated['nombre_categoria'],
            'estado'           => 'A',
            'fecha_ingreso'    => now(),
            'usuario_ingreso'  => 'SISTEMA',
            'fecha_modifica'   => now(),
            'usuario_modifica' => 'SISTEMA',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada exitosamente',
            'data'    => $categoria,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre_categoria' => 'required|string|max:255',
        ]);

        $categoria = Categoria::findOrFail($id);
        $categoria->update(['nombre_categoria' => $validated['nombre_categoria']]);

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente',
            'data'    => $categoria,
        ]);
    }

    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->update(['estado' => 'I']);

        return response()->json([
            'success' => true,
            'message' => 'Categoría desactivada exitosamente',
            'data'    => $categoria,
        ]);
    }
}