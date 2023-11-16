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
        // obtém os dados do banco 
        // código COMPE do banco com 3 dígitos
        $banco = $resource['banco'];
        $compe = substr('000' . $banco['codigo_compe'], -3); 

        // remove do array de dados do banco os itens vazios (não obrigatórios), 
        // para evitar mensagens de erro desnecessárias
        $banco = array_filter($banco, function ($item) {
            return !empty($item);
        });        

        // obtém dados do beneficiário e transforma em objeto
        $beneficiario = $resource['beneficiario'];
        $obj_ben = new Pessoa($beneficiario);

        // obtém a url com o logo do beneficiario, senão usa o logo do banco
        $logo = $beneficiario['logo'] ?? realpath(base_path() . '/vendor/eduardokum/laravel-boleto/logos/' . $compe . '.png');

        // obtém os dados de cnab
        $cnab = $resource['cnab'];
        $layout = $cnab['layout'] ?? '400';

        // verifica se o banco está implementado
        if (! class_exists('\\Eduardokum\\LaravelBoleto\\Boleto\\' . Util::getBancoClass($compe))) {
            parent::__construct([
                'status' => 1,
                'message' => 'Boleto não foi implementado para o banco ' . $compe,
            ]);
            return;
        }

        // verifica se o layout cnab está implementado
        $classe = '\\Eduardokum\\LaravelBoleto\\Cnab\\Remessa\\Cnab' . $layout . '\\' . Util::getBancoClass($compe);
        if (! class_exists($classe)) {
            parent::__construct([
                'status' => 1,
                'message' => 'Layout CNAB ' . $layout . ' não foi implementado para o banco ' . $compe,
            ]);
            return;
        }

        // Cria o objeto cnab
        $obj_cnab = new $classe(array_merge($banco, $cnab, ['beneficiario' => $obj_ben]));

        // cria os boletos
        $boletos = [];
        foreach ($resource['boletos'] as $boleto) {

            // preenche a url do logo
            $boleto['logo'] = $logo;

            // cria o resource
            $boleto_resource = BoletoResource::make($compe, $banco, $obj_ben, $boleto);
            $boletos[] = $boleto_resource;

            // adiciona o boleto para geração do cnab
            $obj_cnab->addBoleto($boleto_resource['boleto']);
        }

        // gera o arquivo de remessa cnab
        $arquivo = uniqid($compe) . '.rem';
        Storage::put($arquivo, $obj_cnab->gerar());

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
