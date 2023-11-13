<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Boleto\Render\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BoletoResource extends JsonResource
{

    /**
     * Create a new resource instance.
     *
     * @param  mixed  ...$parameters
     * @return static
     */
    public static function make(...$parameters)
    {
        // o parametro 0 é o codigo do banco
        $banco = $parameters[0];
        $classe = '\\Eduardokum\\LaravelBoleto\\Boleto\\' . Util::getBancoClass($banco);

        // o parâmetro 1 são os dados do boleto
        $dados = $parameters[1];

        // o parâmetro 2 é o beneficiário
        $dados['beneficiario'] = $parameters[2];
        
        // o parâmetro 3 são os dados de conta, para inserir no array de dados do boleto
        foreach ($parameters[3] as $item => $valor) {
            $dados[$item] = $valor;
        }

        // transforma os dados para o formato esperado pela classe de boleto
        $dados['pagador'] = new Pessoa($dados['pagador']);
        $dados['dataVencimento'] = new Carbon($dados['dataVencimento']);
        $dados['descricaoDemonstrativo'] = BoletoResource::texto_em_array($dados['descricaoDemonstrativo']);
        $dados['instrucoes'] = BoletoResource::texto_em_array($dados['instrucoes']);

        // cria o objeto boleto com a classe correspondente
        $boleto = new $classe($dados);

        // cria o pdf do boleto
        $pdf = new Pdf();
        $pdf->addBoleto($boleto);
        $arquivo = uniqid($banco) . '.pdf';
        Storage::put($arquivo, $pdf->gerarBoleto($pdf::OUTPUT_STRING));

        // cria o objeto resource
        return new static([
            'boleto' => $boleto,
            'pdf' => $arquivo
        ]);

    }   
        
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'linha_digitavel' => $this['boleto']->getLinhaDigitavel(),
            'codigo_barras' => $this['boleto']->getCodigoBarras(),
            'nosso_numero' => $this['boleto']->getNossoNumero(),
            'nosso_numero_boleto' => $this['boleto']->getNossoNumeroBoleto(),
            'pdf' => $this['pdf'],
        ];
    }

    /**
     * Transforma um texto em um array de textos limitados a $max_char caracteres.
     *
     * @return array<string, mixed>
     */
    private static function texto_em_array(string $texto, int $max_char = 40): array
    {
        $temp = wordwrap($texto, $max_char, "\n", true);

        return explode("\n", $temp);
    }
}
