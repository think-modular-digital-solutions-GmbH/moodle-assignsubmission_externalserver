<?php
// local/<yourplugin>/tests/<demo>/.well-known/openid-configuration.php
declare(strict_types=1);
require_once(__DIR__ . '/../common.php');

require_demo_enabled();
$base = base_url();

$cfg = [
  'issuer' => $base,
  'authorization_endpoint' => $base . '/authorize.php',
  'token_endpoint' => $base . '/token.php',
  'userinfo_endpoint' => $base . '/userinfo.php',
  'scopes_supported' => explode(' ', demo_cfg('scopes', 'openid profile email demo.read')),
  'response_types_supported' => ['code'],
  'grant_types_supported' => ['authorization_code','refresh_token'],
  'code_challenge_methods_supported' => ['S256'],
];

if (basename(dirname(__FILE__, 2)) === 'demo_jwt') {
  $cfg['jwks_uri'] = $base . '/jwks.php';
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($cfg, JSON_UNESCAPED_SLASHES);
