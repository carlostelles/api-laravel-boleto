<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use \Eduardokum\LaravelBoleto\Util;
use \Eduardokum\LaravelBoleto\Pessoa;
use \Eduardokum\LaravelBoleto\Boleto\Render\Pdf;
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
        // o parametro 0 é o codigo COMPE do banco 
        $compe = $parameters[0];
        $classe = '\\Eduardokum\\LaravelBoleto\\Boleto\\' . Util::getBancoClass($compe);

        // o parâmetro 1 são os dados do banco
        $banco = $parameters[1];

        // o parâmetro 2 é o beneficiário
        $obj_ben = $parameters[2];
        
        // o parâmetro 3 são os dados do boleto
        $boleto = $parameters[3];

        // prepara os dados no formato esperado pela classe e cria o objeto
        $obj_boleto = new $classe(array_merge(
            $banco, 
            $boleto, [
            'beneficiario' => $obj_ben,
            'pagador' => new Pessoa($boleto['pagador']),
            'dataVencimento' => new Carbon($boleto['dataVencimento']),
            'descricaoDemonstrativo' => is_array($boleto['descricaoDemonstrativo']) ? 
                $boleto['descricaoDemonstrativo'] :
                BoletoResource::texto_em_array($boleto['descricaoDemonstrativo']),
            'instrucoes' => is_array($boleto['instrucoes']) ? 
                $boleto['instrucoes'] :
                BoletoResource::texto_em_array($boleto['instrucoes']),
        ]));

        // cria o pdf do boleto
        $pdf = new Pdf();
        $pdf->addBoleto($obj_boleto);
        $pdf->hideInstrucoes();
        $arquivo = uniqid($compe) . '.pdf';
        Storage::put($arquivo, $pdf->gerarBoleto($pdf::OUTPUT_STRING));

        // cria o objeto resource
        return new static([
            'boleto' => $obj_boleto,
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
            'numero' => $this['boleto']->getNumero(),
            'numero_documento' => $this['boleto']->getNumeroDocumento(),
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
    private static function texto_em_array(string $texto, int $max_char = 60): array
    {
        $temp = wordwrap($texto, $max_char, "\n", true);

        return explode("\n", $temp);
    }
}
