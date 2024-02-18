<?php

namespace App\Http\Controllers\Departement;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\Departement\ViewLecturesDataTable;

class LectureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ViewLecturesDataTable $dataTable)
    {
        // dd($dataTable);
        return $dataTable->render('layouts.setting');
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}