# Removedor de Link do Google

Este bot permite remover URLs específicas do índice do Google usando a Google Indexing API através do GitHub Actions.

## Configuração Necessária

Para que o bot funcione, você precisa configurar os **Secrets** no seu repositório do GitHub:

1. Vá em **Settings** > **Secrets and variables** > **Actions**.
2. Clique em **New repository secret**.
3. Adicione os seguintes segredos:
   - `GOOGLE_CREDENTIALS_1`: Cole aqui o conteúdo completo do primeiro arquivo JSON da sua conta de serviço.
   - `GOOGLE_CREDENTIALS_2`: Cole aqui o conteúdo completo do segundo arquivo JSON da sua conta de serviço.

## Como Usar

1. No seu repositório do GitHub, vá para a aba **Actions**.
2. No menu à esquerda, selecione o workflow **Remover URL do Google**.
3. Clique no botão **Run workflow** (lado direito).
4. Insira a **URL completa** que você deseja remover no campo solicitado.
5. Clique em **Run workflow** para iniciar o processo.

O bot tentará autenticar com as contas de serviço fornecidas e enviará uma solicitação `URL_DELETED` para o Google.

## Arquivos Incluídos

- `remover_url.php`: Script principal que realiza a comunicação com a API do Google.
- `.github/workflows/remover_url.yml`: Configuração do GitHub Actions para execução manual.
