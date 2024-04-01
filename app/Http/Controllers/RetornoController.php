<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Xpendi\CnabBoleto\Cnab\Retorno\Factory as RetornoFactory;

class RetornoController extends Controller
{
    public function store(Request $request)
    {
        // Validação dos dados do request
        $request->validate([
            'file' => 'required|file|mimes:txt|max:2048', // Adapte as extensões e o tamanho máximo conforme sua necessidade
        ]);

        // Verifica se o arquivo foi enviado corretamente
        if ($request->file('file')->isValid()) {
            // Obtém o nome original do arquivo
            $fileName = $request->file('file')->getClientOriginalName();

            // Move o arquivo para o diretório desejado (por exemplo, 'uploads')
            $request->file('file')->move(public_path('uploads'), $fileName);

            $retorno = RetornoFactory::make(public_path('uploads') . '/' . $fileName);
            
            $retorno->processar();

            // Retorna uma resposta de sucesso
            return response()->json([
                'message' => 'Arquivo processado com sucesso!', 
                'arquivo' => $fileName,
                'banco' =>  $retorno->getBancoNome(),
                'data' => $retorno->toArray(),
            ]);
        } else {
            // Retorna uma resposta de erro caso o arquivo não tenha sido enviado corretamente
            return response()->json(['message' => 'Falha ao enviar o arquivo.'], 400);
        }
    }
}
