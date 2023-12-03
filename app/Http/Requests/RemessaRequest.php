<?php

namespace App\Http\Requests;

use App\Console\ValidaCPFCNPJ;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use \Eduardokum\LaravelBoleto\Pessoa;
use \Eduardokum\LaravelBoleto\Util;

class RemessaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "banco" => "required|string",
            "layout" => "required|string|in:240,400",
            "boletos" => "required|array|min:1",
            "boletos.*" => "required|string|distinct",
            "dados.idRemessa" => "required|integer",
            "dados.agencia" => "required|string",
            "dados.conta"  => "required|string",
            "dados.carteira" => "required|string",
            "dados.beneficiario" => "required|array",
            "dados.beneficiario.nome" => "required|string",
            "dados.beneficiario.endereco" => "required|string",
            "dados.beneficiario.cep" => "required|string",
            "dados.beneficiario.uf" => "required|string",
            "dados.beneficiario.cidade" => "required|string",
            "dados.beneficiario.documento" => "required|string",
        ];
    }

    /**
     * Valida dados 
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {

                // somente realiza as validações seguintes se passou nas rules
                if ($validator->errors()->isNotEmpty()) 
                    return;

                // verifica se o banco está implementado
                $banco = $this->input('banco');
                $classe = Util::getBancoClass($banco);

                // valida se o beneficiario pode ser convertido em objeto Pessoa
                $beneficiario = $this->input('dados')['beneficiario'];
                $pessoa = new Pessoa($beneficiario);

                // valida o documento do beneficiario
                $doc1 = new ValidaCPFCNPJ($beneficiario['documento']);
                if ( ! $doc1->valida() ) 
                    $validator->errors()->add(
                        'dados.beneficiario.documento',
                        'Documento do beneficiário (CPF ou CNPJ) inválido'
                    );
                    
                // verifica se o layout cnab está implementado
                $layout = $this->input('layout');
                $classe = '\\Eduardokum\\LaravelBoleto\\Cnab\\Remessa\\Cnab' . $layout . '\\' . $classe;
                if (! class_exists($classe)) 
                    $validator->errors()->add(
                        'layout',
                        'Layout CNAB ' . $layout . ' não foi implementado para o banco ' . $banco
                    );
            }
        ];
    }

    // retorna json com erros
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            "status" => 400,
            "mensagem" => "Erro na validação de dados",
            "erros" => $validator->errors()
        ], 400));
    }
}
