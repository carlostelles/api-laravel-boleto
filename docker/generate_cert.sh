#!/bin/sh

# Defina as variáveis de ambiente
COMMON_NAME="cnab.apis.xpendi.com.br"
DAYS=365

# Gere a chave privada
openssl genrsa -out key.pem 2048

# Gere o certificado assinado pela chave privada
openssl req -new -x509 -key key.pem -out cert.pem -days $DAYS -subj "/CN=$COMMON_NAME"
