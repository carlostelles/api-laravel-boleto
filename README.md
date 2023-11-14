## API laravel-boleto

API em containers docker para geração de boletos e arquivos CNAB, utilizando como base o package [laravel-boleto](https://github.com/eduardokum/laravel-boleto).

## Pré-Requisitos

- Docker 18.06.0+
- Docker Compose 

## Instalação

- Clone este repositório
```
git clone https://github.com/trezzuri/api-laravel-boleto
```

- Execute os containers
```
docker compose up -d
```

## Como Usar

Você pode usar uma plataforma de execução de API, como por exemplo o [Postman](https://www.postman.com), para fazer a execução da API.

Por padrão, o serviço estará disponível na porta 8900 de seu host. Ex: http://localhost:8900

Envie um POST para o endpoint /api do host com um JSON contendo os dados para a geração dos boletos. Ex: http://localhost:8900/api

A API irá gerar:
- Vários arquivos PDF, um para cada boleto informado no array "boletos" do POST
- Um arquivo CNAB de remessa, contendo os dados de todos os boletos informados no POST

A estrutura do JSON esperado pela API é a seguinte:
```
{
   "data": {
      "banco": "756",
      "layout_cnab": "400",
      "logo": "",
      "beneficiario": {
         "nome": "ACME",
         "endereco": "Rua um, 123",
         "cep": "99999-999",
         "uf": "UF",
         "cidade": "CIDADE",
         "documento": "99.999.999/9999-99"
      },
      "conta": {
         "agencia": 1111,
         "conta": 22222,
         "carteira": 1,
         "convenio": 123123
      },
      "boletos": [
         {
            "dataVencimento": "2023-02-15",
            "valor": 10000,
            "multa": false,
            "juros": false,
            "numero": 1,
            "numeroDocumento": 1,
            "pagador": {
               "nome": "Cliente",
               "endereco": "Rua um, 123",
               "bairro": "Bairro",
               "cep": "99999-999",
               "uf": "UF",
               "cidade": "CIDADE",
               "documento": "999999999-99"
            },
            "descricaoDemonstrativo": "Descrição do Demonstrativo",
            "instrucoes": "Descrição das Instruções de Cobrança",
            "aceite": "S",
            "especieDoc": "DM"
         }
      ]
   }
}
```

A API irá retornar a seguinte estrutura:
```
{
    "status": 0,
    "message": "",
    "remessa": "75665537f2c054c6.rem",
    "boletos": [
        {
            "linha_digitavel": "75691.11110 01012.312300 00000.160010 1 95320000010000",
            "codigo_barras": "75691953200000100001111101012312300000016001",
            "nosso_numero": "00000016",
            "nosso_numero_boleto": "0000001-6",
            "pdf": "75665537f2c007f9.pdf"
        }
    ]
}
```

Para realizar o download dos arquivos gerados pela API, faça um GET no endpoint /file informando o nome do arquivo recebido no retorno do POST. Ex: http://localhost:8900/file/75665537f2c054c6.rem

## Bancos Implementados

| Código COMPE | CNAB 400 | CNAB 240 |
| ------------ | :------: | :------: |
 | 001 - Banco do Brasil | Sim | Sim
 | 004 - Bnb | Sim | 
 | 033 - Santander | Sim | Sim
 | 041 - Banrisul | Sim | Sim
 | 077 - Inter | Sim | 
 | 104 - CEF | Sim | Sim
 | 136 - Unicred | Sim | 
 | 224 - Fibra | Sim | 
 | 237 - Bradesco | Sim | Sim
 | 341 - Itau | Sim | Sim
 | 399 - HSBC | Sim | 
 | 435 - Delcred | Sim | 
 | 643 - Pine | Sim | 
 | 712 - Ourinvest | Sim | 
 | 748 - Sicredi | Sim | Sim
 | 756 - Bancoob | Sim | Sim


A versão completa dos layouts implementados pode ser encontrada no package [laravel-boleto](https://github.com/eduardokum/laravel-boleto).


## Licença

Este repositório está licenciado sob [Licença MIT](https://github.com/trezzuri/api-laravel-boleto/blob/master/LICENSE).

O software é fornecido no estado em que se encontra e não fazemos promessas nem fornecemos garantias sobre o mesmo. 

Não seremos responsáveis por danos nem perdas decorrentes do uso ou da incapacidade de usar o software ou de outra forma decorrentes deste contrato. Leia atentamente esta seção; ela limita nossas obrigações perante você.