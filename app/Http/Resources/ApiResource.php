<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use \Eduardokum\LaravelBoleto\Util;
use \Eduardokum\LaravelBoleto\Pessoa;

class ApiResource extends JsonResource
{

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        // obtém os dados de banco e layout do cnab
        $banco = $resource['banco'];
        $dados_conta = $resource['conta'];
        $layout = $resource['layout_cnab'] ?? '240';

        // verifica se o layout cnab está implementado
        $classe = '\\Eduardokum\\LaravelBoleto\\Cnab\\Remessa\\Cnab' . $layout . '\\' . Util::getBancoClass($banco);
        if (! class_exists($classe)) {
            parent::__construct([
                'status' => 1,
                'message' => 'Layout CNAB ' . $layout . ' não foi implementado para o banco ' . $banco,
                'remessa' => '',
                'data' => [],
            ]);
            return;
        }

        // transforma os dados do beneficiário e cria o objeto cnab
        $beneficiario = new Pessoa($resource['beneficiario']);
        $dados_conta['beneficiario'] = $beneficiario;
        $cnab = new $classe($dados_conta);

        // cria os boletos
        $boletos = [];
        foreach ($resource['boletos'] as $dados_boleto) {

            // preenche a url do logo informado, senão traz o logo padrão do banco
            $logo = $resource['logo'] ?? realpath(base_path() . '/vendor/eduardokum/laravel-boleto/logos/' . $banco . '.png');
            $dados_boleto['logo'] = $logo;

            // cria o resource
            $boleto_resource = BoletoResource::make($banco, $dados_boleto, $beneficiario, $dados_conta);
            $boletos[] = $boleto_resource;

            // adiciona o boleto para geração do cnab
            $cnab->addBoleto($boleto_resource['boleto']);
        }

        // gera o arquivo de remessa cnab
        $arquivo = uniqid($banco) . '.rem';
        Storage::put($arquivo, $cnab->gerar());

        // gera o resource 
        parent::__construct([
            'status' => 0,
            'message' => '',
            'remessa' => $arquivo,
            'boletos' => BoletoResource::collection($boletos),
        ]);

    }


    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
