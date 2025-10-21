<?php
// local/<yourplugin>/tests/demo_jwt/jwks.php
declare(strict_types=1);
require_once(__DIR__ . '/common.php');

require_demo_enabled();

$pubpem = demo_rsa_public_pem();
if (!$pubpem) json_out(['keys'=>[]]);

// Convert PEM → JWK (modulus/exponent)
$pub = openssl_pkey_get_public($pubpem);
$det = openssl_pkey_get_details($pub);
$kid = demo_kid();

$n = ''; $e = '';
if ($det && $det['type'] === OPENSSL_KEYTYPE_RSA) {
    $rsa = $det['rsa'];
    $n = b64url($rsa['n']);
    $e = b64url($rsa['e']);
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['keys'=>[[
    'kty'=>'RSA','kid'=>$kid,'alg'=>'RS256','use'=>'sig','n'=>$n,'e'=>$e
]]], JSON_UNESCAPED_SLASHES);
