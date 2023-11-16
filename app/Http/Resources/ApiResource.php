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
     * @param  mixed  ...$parameters
     * @return static
     */
    public static function make(...$parameters)
    {
        // o parametro 0 deve ser a Request
        $request = $parameters[0];

        // obtém os dados do banco 
        $banco = $request['banco'];
        $compe = $banco['codigo_compe']; 

        // remove do array de dados do banco os itens vazios (não obrigatórios), 
        // para evitar mensagens de erro desnecessárias
        $banco = array_filter($banco, function ($item) {
            return !empty($item);
        });        

        // obtém dados do beneficiário e transforma em objeto
        $beneficiario = $request['beneficiario'];
        $obj_ben = new Pessoa($beneficiario);

        // obtém a url com o logo do beneficiario, senão usa o logo do banco
        $logo = $beneficiario['logo'] ?? realpath(base_path() . '/vendor/eduardokum/laravel-boleto/logos/' . $compe . '.png');

        // obtém os dados de cnab
        $cnab = $request['cnab'];
        $layout = $cnab['layout'] ?? '400';

        // verifica se o banco está implementado
        if (! class_exists('\\Eduardokum\\LaravelBoleto\\Boleto\\' . Util::getBancoClass($compe))) {
            throw new \Exception('Boleto não foi implementado para o banco ' . $compe);
        }

        // verifica se o layout cnab está implementado
        $classe = '\\Eduardokum\\LaravelBoleto\\Cnab\\Remessa\\Cnab' . $layout . '\\' . Util::getBancoClass($compe);
        if (! class_exists($classe)) {
            throw new \Exception('Layout CNAB ' . $layout . ' não foi implementado para o banco ' . $compe);
        }

        // Cria o objeto cnab
        $obj_cnab = new $classe(array_merge($banco, $cnab, ['beneficiario' => $obj_ben]));

        // cria os boletos
        $boletos = [];
        foreach ($request['boletos'] as $boleto) {

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

        // cria o objeto resource
        return new static([
            'remessa' => $arquivo,
            'boletos' => BoletoResource::collection($boletos),
        ]);

    }

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        // gera o resource 
        parent::__construct([
            'status'   => isset($resource['status'])   ? $resource['status']   : 0,
            'mensagem' => isset($resource['mensagem']) ? $resource['mensagem'] : '',
            'remessa'  => isset($resource['remessa'])  ? $resource['remessa']  : '',
            'boletos'  => isset($resource['boletos'])  ? $resource['boletos']  : [],
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
