<?php

namespace App\Http\Controllers;

use App\Models\Retorno;
use App\Http\Resources\RetornoResource;
use App\Http\Requests\RetornoRequest;
use Illuminate\Http\Request;

class RetornoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $retorno = Retorno::paginate(500);
        return RetornoResource::collection($retorno);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RetornoRequest $request)
    {
        // cria o model retorno
        $retorno = Retorno::create($request->all());
        return new RetornoResource($retorno);
    }

    /**
     * Display the specified resource.
     */
    public function show(Retorno $retorno)
    {
        return new RetornoResource($retorno);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RetornoRequest $request, Retorno $retorno)
    {
        $retorno->update($request->all());
        return new RetornoResource($retorno);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Retorno $retorno)
    {
        return Retorno::destroy($retorno->_id);
    }
}
