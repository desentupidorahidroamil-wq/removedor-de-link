# Removedor de Link do Google

Este bot permite remover URLs específicas do índice do Google usando a Google Indexing API através do GitHub Actions.

## Configuração Necessária

### 1. Configurar Secrets
Vá em **Settings** > **Secrets and variables** > **Actions** e adicione:
- `GOOGLE_CREDENTIALS_1`: Conteúdo do primeiro JSON da conta de serviço.
- `GOOGLE_CREDENTIALS_2`: Conteúdo do segundo JSON da conta de serviço.

### 2. Ativar o Workflow
Devido a restrições de permissão de segurança, o arquivo de workflow não pôde ser criado automaticamente. Siga estes passos:
1. Clique em **Add file** > **Create new file**.
2. No nome do arquivo, digite: `.github/workflows/remover_url.yml`
3. Cole o seguinte conteúdo:

```yaml
name: Remover URL do Google

on:
  workflow_dispatch:
    inputs:
      url:
        description: 'URL que você deseja remover do Google'
        required: true
        default: ''

jobs:
  remover:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout do código
        uses: actions/checkout@v3
      - name: Configurar PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          extensions: openssl, curl
      - name: Executar Remoção de URL
        env:
          GOOGLE_CREDENTIALS_1: ${{ secrets.GOOGLE_CREDENTIALS_1 }}
          GOOGLE_CREDENTIALS_2: ${{ secrets.GOOGLE_CREDENTIALS_2 }}
          URL_TO_REMOVE: ${{ github.event.inputs.url }}
        run: php remover_url.php
```

## Como Usar
1. Vá para a aba **Actions**.
2. Selecione **Remover URL do Google**.
3. Clique em **Run workflow**, insira a URL e execute.
