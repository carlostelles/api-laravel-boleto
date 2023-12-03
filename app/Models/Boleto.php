<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use \Eduardokum\LaravelBoleto\Util;
use \Eduardokum\LaravelBoleto\Pessoa;
use \Eduardokum\LaravelBoleto\Boleto\Render\Pdf;
use Carbon\Carbon;
 
class Boleto extends Model
{
    use HasFactory;

    // definir a conexão com mongodb
    protected  $connection = 'mongodb';

    // equivale ao $table do MySQL
    protected  $collection = 'boletos';

    // define campos 
    protected  $fillable = ['banco', 'dados'];

    /**
     * Override do método save para gerar o boleto
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {

        // cria o pdf do boleto
        $pdf = new Pdf();
        $pdf->addBoleto($this->boleto);
        $pdf->hideInstrucoes();
        $arquivo = uniqid($this->banco) . '.pdf';
        Storage::put($arquivo, $pdf->gerarBoleto($pdf::OUTPUT_STRING));

        // armazena os dados para retorno na api
        $this->linha_digitavel = $this->boleto->getLinhaDigitavel();
        $this->codigo_barras = $this->boleto->getCodigoBarras();
        $this->numero = $this->boleto->getNumero();
        $this->numero_documento = $this->boleto->getNumeroDocumento();
        $this->nosso_numero = $this->boleto->getNossoNumero();
        $this->nosso_numero_boleto = $this->boleto->getNossoNumeroBoleto();
        $this->pdf = $arquivo;

        return parent::save($options);
    }

    /**
     * Transforma o array dados em objeto Boleto
     */
    protected function boleto(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {

                // obtém a classe do boleto a ser gerado
                $classe = '\\Eduardokum\\LaravelBoleto\\Boleto\\' . Util::getBancoClass($attributes['banco']);

                // remove do array de dados os campos vazios para evitar mensagens de erro desnecessárias
                $dados = array_filter($attributes['dados'], fn ($item) => !empty($item));

                // prepara os dados no formato esperado pela classe 
                $dados['beneficiario'] = new Pessoa($dados['beneficiario']);
                $dados['pagador'] = new Pessoa($dados['pagador']);
                $dados['dataVencimento'] = new Carbon($dados['dataVencimento']);

                // obtém a url com o logo, senão usa o logo do banco
                $dados['logo'] = $dados['logo'] ?? realpath(base_path() . 
                    '/vendor/eduardokum/laravel-boleto/logos/' . $attributes['banco'] . '.png');

                // quebra os textos longos, se necessário
                if (isset($dados['descricaoDemonstrativo']))
                    $dados['descricaoDemonstrativo'] = is_array($dados['descricaoDemonstrativo']) ? 
                        $dados['descricaoDemonstrativo'] :
                        $this->texto_em_array($dados['descricaoDemonstrativo']);
                if (isset($dados['instrucoes']))
                    $dados['instrucoes'] = is_array($dados['instrucoes']) ? 
                        $dados['instrucoes'] :
                        $this->texto_em_array($dados['instrucoes']);

                // retorna o objeto boleto
                return new $classe($dados);                
            },
        );
    }    

    /**
     * Transforma um texto em um array de textos limitados a $max_char caracteres.
     *
     * @return array<string, mixed>
     */
    private function texto_em_array(string $texto, int $max_char = 60): array
    {
        $temp = wordwrap($texto, $max_char, "\n", true);

        return explode("\n", $temp);
    }
}
