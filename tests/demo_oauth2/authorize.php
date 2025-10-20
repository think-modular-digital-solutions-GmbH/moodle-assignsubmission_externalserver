<?php
// local/<yourplugin>/tests/<demo>/authorize.php
declare(strict_types=1);
require_once(__DIR__ . '/common.php');
require_once(__DIR__ . '/store.php');

require_demo_enabled();

$resp = optional_param('response_type', 'code', PARAM_RAW_TRIMMED);
$client_id = required_param('client_id', PARAM_RAW_TRIMMED);
$redirect_uri = required_param('redirect_uri', PARAM_RAW);
$scope = optional_param('scope', '', PARAM_RAW);
$state = optional_param('state', '', PARAM_RAW);
$cc = optional_param('code_challenge', '', PARAM_RAW_TRIMMED);
$ccm = optional_param('code_challenge_method', '', PARAM_RAW_TRIMMED);

if ($resp !== 'code') json_out(['error' => 'unsupported_response_type'], 400);

$expected_client = demo_cfg('clientid', 'demo-client');
if ($client_id !== $expected_client) json_out(['error' => 'unauthorized_client'], 401);

// allow core callback and a plugin callback
global $CFG;
$allowed = [
    $CFG->wwwroot . '/admin/oauth2callback.php',
    $CFG->wwwroot . '/local/<yourplugin>/oauth2/callback.php'
];
$ok = false; foreach ($allowed as $u) { if (strncmp($redirect_uri, $u, strlen($u)) === 0) { $ok = true; break; } }
if (!$ok) json_out(['error' => 'invalid_request', 'error_description' => 'redirect_uri not allowed'], 400);

// PKCE
if (demo_cfg('requirepkce', 1)) {
    if ($ccm !== 'S256' || !$cc) json_out(['error' => 'invalid_request', 'error_description' => 'PKCE S256 required'], 400);
}

// fake a logged-in/consented user
$user = ['sub' => 'demo-user-123', 'name' => 'Demo User', 'email' => 'demo@example.com'];

$code = bin2hex(random_bytes(16));
store_put('code', $code, [
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'scope' => $scope,
    'code_challenge' => $cc ?: null,
    'user' => $user,
    'expires' => time() + 300
]);

$sep = (strpos($redirect_uri, '?') === false) ? '?' : '&';
redirect($redirect_uri . $sep . 'code=' . urlencode($code) . ($state !== '' ? '&state=' . urlencode($state) : ''));
