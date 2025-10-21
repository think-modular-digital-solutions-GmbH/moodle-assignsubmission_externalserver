<?php
// local/<yourplugin>/tests/demo_jwt/token.php
declare(strict_types=1);
require_once(__DIR__ . '/common.php');
require_once(__DIR__ . '/store.php');

require_demo_enabled();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function postf(string $k, ?string $d=null){ return isset($_POST[$k])?trim((string)$_POST[$k]):$d;}

$grant = postf('grant_type','');
$cid = postf('client_id','');
$secret = postf('client_secret',null);
$expected_client = demo_cfg('clientid','demo-client');
$expected_secret = demo_cfg('clientsecret','demo-secret');
if ($cid !== $expected_client) json_out(['error'=>'unauthorized_client'],401);
if ($secret !== null && $secret !== '' && $secret !== $expected_secret) json_out(['error'=>'invalid_client'],401);

$priv = demo_rsa_private();
if (!$priv) json_out(['error'=>'server_error','error_description'=>'No RSA private key configured'],500);
$kid = demo_kid();
$issuer = base_url(); // issuer = this demo
$aud = base_url() . '/token.php';

if ($grant === 'authorization_code') {
    $code = postf('code',''); $redir = postf('redirect_uri',''); $ver = postf('code_verifier','');
    $c = store_get('code',$code); if (!$c) json_out(['error'=>'invalid_grant'],400);
    if ($c['client_id']!==$cid || $c['redirect_uri']!==$redir) json_out(['error'=>'invalid_grant'],400);
    if (time()>(int)$c['expires']) { store_delete('code',$code); json_out(['error'=>'invalid_grant'],400); }
    if (!empty($c['code_challenge'])) {
        if (b64url( hash('sha256',$ver??'', true) ) !== $c['code_challenge']) json_out(['error'=>'invalid_grant'],400);
    }
    store_delete('code',$code);

    // Build JWT access token (RS256)
    $now = time(); $ttl = (int)demo_cfg('at_ttl',3600);
    $claims = [
        'iss'=>$issuer,
        'aud'=>$aud,
        'iat'=>$now,
        'nbf'=>$now - 5,
        'exp'=>$now + $ttl,
        'sub'=>$c['user']['sub'],
        'name'=>$c['user']['name'],
        'email'=>$c['user']['email'],
        'scope'=>$c['scope']
    ];
    $header = ['typ'=>'JWT','alg'=>'RS256','kid'=>$kid];
    $h64 = b64url(json_encode($header, JSON_UNESCAPED_SLASHES));
    $p64 = b64url(json_encode($claims, JSON_UNESCAPED_SLASHES));
    $input = $h64.'.'.$p64;
    $sig = '';
    $pkey = openssl_pkey_get_private($priv);
    openssl_sign($input, $sig, $pkey, OPENSSL_ALGO_SHA256);
    openssl_free_key($pkey);
    $jwt = $input.'.'.b64url($sig);

    $resp = ['token_type'=>'Bearer','access_token'=>$jwt,'expires_in'=>$ttl,'scope'=>$c['scope']];

    if ((int)demo_cfg('issuerefresh',1)===1) {
        $refresh = bin2hex(random_bytes(32));
        store_put('refresh',$refresh,['user'=>$c['user'],'scope'=>$c['scope'],'client_id'=>$cid,'expires'=>time()+(int)demo_cfg('rt_ttl',1209600)]);
        $resp['refresh_token']=$refresh;
    }
    echo json_encode($resp, JSON_UNESCAPED_SLASHES); exit;
}

if ($grant === 'refresh_token') {
    $rt = postf('refresh_token',''); $rec = store_get('refresh',$rt);
    if (!$rec || $rec['client_id']!==$cid || time()>(int)$rec['expires']) json_out(['error'=>'invalid_grant'],400);
    // Mint a fresh JWT
    $now = time(); $ttl = (int)demo_cfg('at_ttl',3600);
    $claims = [
        'iss'=>$issuer,'aud'=>$aud,'iat'=>$now,'nbf'=>$now-5,'exp'=>$now+$ttl,
        'sub'=>$rec['user']['sub'],'name'=>$rec['user']['name'],'email'=>$rec['user']['email'],
        'scope'=>$rec['scope']
    ];
    $header = ['typ'=>'JWT','alg'=>'RS256','kid'=>$kid];
    $h64=b64url(json_encode($header, JSON_UNESCAPED_SLASHES));
    $p64=b64url(json_encode($claims, JSON_UNESCAPED_SLASHES));
    $input="$h64.$p64";
    $pkey = openssl_pkey_get_private($priv);
    openssl_sign($input, $sig, $pkey, OPENSSL_ALGO_SHA256);
    openssl_free_key($pkey);
    $jwt = $input.'.'.b64url($sig);

    echo json_encode(['token_type'=>'Bearer','access_token'=>$jwt,'expires_in'=>$ttl,'scope'=>$rec['scope']], JSON_UNESCAPED_SLASHES); exit;
}

json_out(['error'=>'unsupported_grant_type'],400);
