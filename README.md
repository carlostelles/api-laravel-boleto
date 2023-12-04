## API laravel-boleto

API em container docker para geração de boletos e arquivos CNAB, utilizando como base o package [laravel-boleto](https://github.com/eduardokum/laravel-boleto).

## Pré-Requisitos

- Docker 18.06.0+
- Docker Compose 

## Instalação

- Clone este repositório
```
git clone https://github.com/trezzuri/api-laravel-boleto
```

- Execute o container
```
docker compose up -d
```

## Informações Gerais

A documentação da API está disponível [aqui](https://trezzuri.github.io/api-laravel-boleto/)

A API usa o MongoDB para salvar os dados dos boletos e remessas gerados.

Os registros ficam armazenados no MongoDB até que sejam expressamente apagados através de um DELETE no endpoint da API.

Porém, os arquivos PDF e REM gerados pela API são mantidos por até **7 dias**, após isso são **apagados**. 

## Como Usar

Você pode usar uma plataforma, como por exemplo o [Postman](https://www.postman.com), para fazer a execução da API.

Por padrão, o serviço estará disponível na porta 8900 de seu host. Ex: http://localhost:8900

Envie um POST para o endpoint /api/boleto do host com um JSON contendo os dados para a geração dos boletos. Ex: http://localhost:8900/api/boleto

Exemplo de JSON a ser enviado para a API:
```
{
  "banco": "001",
  "dados": {
    "beneficiario": {
      "nome": "ACME",
      "endereco": "Rua um, 123",
      "cep": "99999-999",
      "uf": "UF",
      "cidade": "CIDADE",
      "documento": "99.999.999/9999-62"
    },
    "pagador": {
      "nome": "Cliente",
      "endereco": "Rua um, 123",
      "bairro": "Bairro",
      "cep": "99999-999",
      "uf": "UF",
      "cidade": "CIDADE",
      "documento": "099.999.999-05"
    },
    "numero": 1,
    "numeroDocumento": "DOC1234567",
    "dataVencimento": "2023-02-15",
    "valor": 12345.67,
    "multa": 0,
    "juros": 0,
    "aceite": 0,
    "especieDoc": "DM",
    "agencia": "0011",
    "conta": "22222",
    "carteira": "11",
    "convenio": "123123",
    "codigoCliente": "",
    "operacao": "",
    "posto": "",
    "range": "",
    "byte": "",
    "descricaoDemonstrativo": "Descrição do demonstrativo de cobrança",
    "instrucoes": "Descrição das instruções de cobrança",
    "logo": "",
    "modalidadeCarteira": "",
    "variacaoCarteira": "",
    "diasBaixaAutomatica": "",
    "diasProtesto": ""
  }
}
```

Em caso de sucesso, a API irá retornar algo como:
```
{
    "data": {
        "_id": "656ce688332a2e8379083ed3",
        "linha_digitavel": "00191.23124 30000.100112 00022.222111 2 92620001234567",
        "codigo_barras": "00192926200012345671231230000100110002222211",
        "numero": 1,
        "numero_documento": "DOC1234567",
        "nosso_numero": "12312300001",
        "nosso_numero_boleto": "12312300001-2",
        "pdf": "001656ce68859f3a.pdf"
    }
}
```

Para realizar o download dos arquivos gerados pela API, faça um GET no endpoint /file informando o nome do arquivo recebido no retorno do POST. Ex: http://localhost:8900/file/001656ce68859f3a.pdf

Para saber sobre o funcionamento completo da API, [consulte a documentação](https://trezzuri.github.io/api-laravel-boleto/)

## Bancos Implementados

| Código COMPE | CNAB 400 | CNAB 240 | Carteiras Disponíveis |
| ------------ | :------: | :------: | --------------------- |
| 001 - Banco do Brasil | Sim | Sim | 11, 12, 15, 17, 18, 31, 51 |
| 004 - Bnb | Sim | - | 21, 31, 41 |
| 033 - Santander | Sim | Sim | 101, 201 |
| 041 - Banrisul | Sim | Sim | 1 a 8, B a K, M, N, P a U, X |
| 077 - Inter | Sim |  - | 112 |
| 104 - CEF | Sim | Sim | RG |
| 136 - Unicred | Sim |  - | 21 |
| 224 - Fibra | Sim |  - | 0 |
| 237 - Bradesco | Sim | Sim | 02, 04, 09, 21, 26 |
| 341 - Itau | Sim | Sim | 112, 115, 188, 109, 121, 180, 110, 111 |
| 399 - HSBC | Sim |  - | CSB |
| 435 - Delcred | Sim |  - | 112, 121 |
| 643 - Pine | Sim |  - | 0 |
| 712 - Ourinvest | Sim |  - | 0 |
| 748 - Sicredi | Sim | Sim | 1, 2, 3 |
| 756 - Bancoob | Sim | Sim | 1, 3 |

Mais detalhes sobre os bancos implementados e sobre os campos disponíveis para cada banco podem ser encontrados no package [laravel-boleto](https://github.com/eduardokum/laravel-boleto).


## Customizando a imagem Docker

Caso você deseje customizar a imagem Docker:

* Faça um fork e clone do projeto
* Copie o .env.example para .env e ajuste as variáveis
* Realize suas alterações
* Modifique o nome da imagem no docker-compose.yaml, trocando a linha "image" por seu repo no Docker Hub
* Execute:

```
docker compose build
docker compose push
```


## Licença

Este repositório está licenciado sob [Licença MIT](https://github.com/trezzuri/api-laravel-boleto/blob/master/LICENSE).

O software é fornecido "tal como está", sem garantia de qualquer tipo, expressa ou implícita, incluindo, mas não se limitando às garantias de comercialização, conveniência para um propósito específico e não infração. Em nenhuma situação devem os autores ou titulares de direitos autorais serem responsáveis por qualquer reivindicação, dano ou outras responsabilidades, seja em ação de contrato, prejuízo ou outra forma, decorrente de, fora de ou em conexão com o software ou o uso ou outras relações com o software.