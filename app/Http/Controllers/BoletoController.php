<?php

namespace App\Http\Controllers;

use App\Models\Boleto;
use App\Http\Resources\BoletoResource;
use App\Http\Requests\BoletoRequest;
use Illuminate\Http\Request;

class BoletoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $boletos = Boleto::paginate(500);
        return BoletoResource::collection($boletos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BoletoRequest $request)
    {
        // cria o model boleto
        $boleto = Boleto::create($request->all());
        return new BoletoResource($boleto);
    }

    /**
     * Display the specified resource.
     */
    public function show(Boleto $boleto)
    {
        return new BoletoResource($boleto);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BoletoRequest $request, Boleto $boleto)
    {
        $boleto->update($request->all());
        return new BoletoResource($boleto);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Boleto $boleto)
    {
        return Boleto::destroy($boleto->_id);
    }
}
