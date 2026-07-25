<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActaEntrevista;
use Illuminate\Http\Request;

class ActaEntrevistaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('acta_entrevista.create');
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
    public function show(ActaEntrevista $actaEntrevista)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ActaEntrevista $actaEntrevista)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ActaEntrevista $actaEntrevista)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ActaEntrevista $actaEntrevista)
    {
        //
    }
}
