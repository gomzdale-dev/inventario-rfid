<?php

namespace App\Http\Controllers\Api;

use App\Models\Etiquetas_Rfid;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EtiquetasRfidController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return response()->json(Etiquetas_Rfid::all());
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Etiquetas_Rfid $etiquetas_Rfid)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Etiquetas_Rfid $etiquetas_Rfid)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Etiquetas_Rfid $etiquetas_Rfid)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Etiquetas_Rfid $etiquetas_Rfid)
    {
        //
    }
}
