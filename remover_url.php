<?php
/**
 * Google Indexing API URL Remover
 * Permite remover URLs do índice do Google usando GitHub Actions
 */

// Pega a URL a ser removida das variáveis de ambiente ou argumentos
$url_to_remove = getenv('URL_TO_REMOVE');

if (empty($url_to_remove)) {
    // Se não houver variável de ambiente, tenta pegar do argumento da linha de comando
    $url_to_remove = $argv[1] ?? null;
}

if (empty($url_to_remove)) {
    die("Erro: Nenhuma URL fornecida para remoção. Use a variável de ambiente URL_TO_REMOVE ou passe como argumento.\n");
}

// Pega as credenciais das variáveis de ambiente
$creds_json1 = getenv('GOOGLE_CREDENTIALS_1');
$creds_json2 = getenv('GOOGLE_CREDENTIALS_2');

$accounts = array_filter([$creds_json1, $creds_json2]);

if (empty($accounts)) {
    die("Erro: Nenhuma credencial configurada (GOOGLE_CREDENTIALS_1 ou 2).\n");
}

function base64url_encode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

function get_google_access_token($json_string) {
    $json = json_decode($json_string, true);
    if (!$json) return null;
    
    $private_key = $json['private_key'];
    $client_email = $json['client_email'];

    $header = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $now = time();
    $payload = base64url_encode(json_encode([
        'iss' => $client_email,
        'sub' => $client_email,
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + 3600,
        'scope' => 'https://www.googleapis.com/auth/indexing'
    ]));

    $signature = '';
    openssl_sign("$header.$payload", $signature, $private_key, 'SHA256');
    $jwt = "$header.$payload." . base64url_encode($signature);

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));

    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);

    return $data['access_token'] ?? null;
}

function send_removal_request($url, $access_token) {
    $ch = curl_init('https://indexing.googleapis.com/v3/urlNotifications:publish');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'url' => $url,
        'type' => 'URL_DELETED'
    ]));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $http_code, 'body' => json_decode($response, true)];
}

echo "--- Iniciando Removedor de URL do Google ---\n";
echo "URL alvo: $url_to_remove\n";

$success = false;
foreach ($accounts as $index => $creds) {
    $account_num = $index + 1;
    echo "Tentando com Conta $account_num...\n";
    
    $token = get_google_access_token($creds);
    if (!$token) {
        echo "Erro ao obter token para a conta $account_num.\n";
        continue;
    }

    $result = send_removal_request($url_to_remove, $token);
    echo "Status HTTP: " . $result['code'] . "\n";
    
    if ($result['code'] == 200) {
        echo "Sucesso! A solicitação de remoção para '$url_to_remove' foi enviada.\n";
        $success = true;
        break;
    } else {
        echo "Erro na resposta: " . json_encode($result['body']) . "\n";
    }
}

if (!$success) {
    echo "\nFalha ao remover a URL com todas as contas disponíveis.\n";
    exit(1);
}

echo "\nProcesso concluído.\n";
?>
