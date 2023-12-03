<?php

namespace App\Models;

use App\Models\Boleto;
use MongoDB\Laravel\Eloquent\Model; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use \Eduardokum\LaravelBoleto\Util;
use \Eduardokum\LaravelBoleto\Pessoa;
use Illuminate\Support\Facades\Storage;

class Remessa extends Model
{
    use HasFactory;

    // definir a conexão com mongodb
    protected  $connection = 'mongodb';

    // equivale ao $table do MySQL
    protected  $collection = 'remessa';

    // define campos 
    protected  $fillable = ['banco', 'boletos', 'dados', 'layout'];

    /**
     * Override do método save para gerar o cnab
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {

        // obtém o código do banco
        $banco = $this->banco;

        // obtém o layout de Remessa
        $layout = $this->layout ?? '400';

        // remove do array de dados os campos vazios para evitar mensagens de erro desnecessárias
        $dados = array_filter($this->dados, fn ($item) => !empty($item));

        // prepara os dados no formato esperado pela classe 
        $dados['beneficiario'] = new Pessoa($dados['beneficiario']);

        // Cria o objeto Remessa
        $classe = '\\Eduardokum\\LaravelBoleto\\Cnab\\Remessa\\Cnab' . $layout . '\\' . Util::getBancoClass($banco);
        $obj_cnab = new $classe($dados);

        // adiciona o boleto para geração do Remessa
        foreach ($this->boletos as $id) {
            $boleto = Boleto::findOrFail($id);
            $obj_cnab->addBoleto($boleto->boleto);
        }

        // gera o arquivo de remessa 
        $arquivo = uniqid($banco) . '.rem';
        Storage::put($arquivo, $obj_cnab->gerar());
        $this->arquivo = $arquivo;

        return parent::save($options);
    }

}
