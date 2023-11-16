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

Você pode usar uma plataforma, como por exemplo o [Postman](https://www.postman.com), para fazer a execução da API.

Por padrão, o serviço estará disponível na porta 8900 de seu host. Ex: http://localhost:8900

Envie um POST para o endpoint /api do host com um JSON contendo os dados para a geração dos boletos. Ex: http://localhost:8900/api

A API irá gerar:
- Vários arquivos PDF, um para cada boleto informado no array "boletos" do POST
- Um arquivo CNAB de remessa, contendo os dados de todos os boletos informados no POST

Exemplo de JSON a ser enviado para a API:
```
{
  "banco": {
    "codigo_compe": "001",
    "agencia": "11",
    "conta": "22222",
    "carteira": "11",
    "convenio": "123123",
    "codigoCliente": "",
    "operacao": "",
    "posto": "",
    "range": "",
    "byte": "",
    "modalidadeCarteira": "",
    "variacaoCarteira": "",
    "diasBaixaAutomatica": "",
    "diasProtesto": ""
  },
  "beneficiario": {
    "nome": "ACME",
    "endereco": "Rua um, 123",
    "cep": "99999-999",
    "uf": "UF",
    "cidade": "CIDADE",
    "documento": "99.999.999/9999-99",
    "logo": ""
  },
  "cnab": {
    "layout": "400",
    "idremessa": 1
  },
  "boletos": [
    {
      "pagador": {
        "nome": "Cliente",
        "endereco": "Rua um, 123",
        "bairro": "Bairro",
        "cep": "99999-999",
        "uf": "UF",
        "cidade": "CIDADE",
        "documento": "999.999.999-99"
      },
      "numero": 1,
      "numeroDocumento": 1,
      "dataVencimento": "2023-02-15",
      "valor": 12345.67,
      "multa": 0,
      "juros": 0,
      "descricaoDemonstrativo": "Descrição do demonstrativo de cobrança",
      "instrucoes": "Descrição das instruções de cobrança",
      "aceite": 0,
      "especieDoc": "DM"
    }
  ]
}
```

A API irá retornar a seguinte estrutura:
```
{
    "status": 0,
    "mensagem": "",
    "remessa": "001655613e9d6537.rem",
    "boletos": [
        {
            "linha_digitavel": "00191.23124 30000.100112 00022.222111 2 92620001234567",
            "codigo_barras": "00192926200012345671231230000100110002222211",
            "nosso_numero": "12312300001",
            "nosso_numero_boleto": "12312300001-2",
            "pdf": "001655613e9d5247.pdf"
        }
    ]
}
```

Para realizar o download dos arquivos gerados pela API, faça um GET no endpoint /file informando o nome do arquivo recebido no retorno do POST. Ex: http://localhost:8900/file/001655613e9d6537.rem

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


Mais detalhes sobre os bancos implementados podem ser encontrada no package [laravel-boleto](https://github.com/eduardokum/laravel-boleto).


## Licença

Este repositório está licenciado sob [Licença MIT](https://github.com/trezzuri/api-laravel-boleto/blob/master/LICENSE).

O software é fornecido "tal como está", sem garantia de qualquer tipo, expressa ou implícita, incluindo, mas não se limitando às garantias de comercialização, conveniência para um propósito específico e não infração. Em nenhuma situação devem os autores ou titulares de direitos autorais serem responsáveis por qualquer reivindicação, dano ou outras responsabilidades, seja em ação de contrato, prejuízo ou outra forma, decorrente de, fora de ou em conexão com o software ou o uso ou outras relações com o software.