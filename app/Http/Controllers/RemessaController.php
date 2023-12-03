<?php

namespace App\Http\Controllers;

use App\Models\Remessa;
use App\Http\Resources\RemessaResource;
use App\Http\Requests\RemessaRequest;
use Illuminate\Http\Request;

class RemessaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $remessa = Remessa::paginate(500);
        return RemessaResource::collection($remessa);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RemessaRequest $request)
    {
        // cria o model remessa
        $remessa = Remessa::create($request->all());
        return new RemessaResource($remessa);
    }

    /**
     * Display the specified resource.
     */
    public function show(Remessa $remessa)
    {
        return new RemessaResource($remessa);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RemessaRequest $request, Remessa $remessa)
    {
        $remessa->update($request->all());
        return new RemessaResource($remessa);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Remessa $remessa)
    {
        return Remessa::destroy($remessa->_id);
    }
}
