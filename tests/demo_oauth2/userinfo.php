<?php
// local/<yourplugin>/tests/<demo>/userinfo.php
declare(strict_types=1);
require_once(__DIR__ . '/common.php');
require_once(__DIR__ . '/store.php');

require_demo_enabled();

$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('~^Bearer\s+(.+)$~', $auth, $m)) json_out(['error' => 'invalid_token'], 401);
$token = $m[1];

// For opaque tokens: look up in store.
// For JWT tokens: you could skip lookup and just trust the signature;
// here we support both by checking store first.
if ($rec = store_get('access', $token)) {
    if (time() > (int)$rec['expires']) json_out(['error' => 'invalid_token'], 401);
    $user = $rec['user'];
    $scope = $rec['scope'];
} else {
    // JWT demo: accept any well-formed RS256 JWT signed by our private key.
    $parts = explode('.', $token);
    if (count($parts) !== 3) json_out(['error' => 'invalid_token'], 401);
    [$h64,$p64,$s64] = $parts;
    $header = json_decode(base64_decode(strtr($h64,'-_','+/')), true);
    $payload = json_decode(base64_decode(strtr($p64,'-_','+/')), true);
    $sig = base64_decode(strtr($s64,'-_','+/'));

    if (($header['alg'] ?? '') !== 'RS256') json_out(['error' => 'invalid_token'], 401);
    $pub = demo_rsa_public_pem();
    $ok = $pub ? openssl_verify("$h64.$p64", $sig, $pub, OPENSSL_ALGO_SHA256) === 1 : false;
    if (!$ok) json_out(['error' => 'invalid_token'], 401);

    $now = time(); $leeway = 60;
    if (($payload['exp'] ?? 0) < $now - $leeway) json_out(['error' => 'invalid_token'], 401);

    $user = ['sub' => $payload['sub'] ?? 'demo-user-123', 'name' => $payload['name'] ?? 'Demo User', 'email' => $payload['email'] ?? 'demo@example.com'];
    $scope = $payload['scope'] ?? 'openid profile email';
}

echo json_encode([
    'sub' => $user['sub'],
    'name' => $user['name'],
    'email' => $user['email'],
    'scope' => $scope
], JSON_UNESCAPED_SLASHES);
