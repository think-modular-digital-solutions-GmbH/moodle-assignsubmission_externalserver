<?php
// local/<yourplugin>/tests/demo_oauth2/token.php
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

if ($grant === 'authorization_code') {
    $code = postf('code',''); $redir = postf('redirect_uri',''); $ver = postf('code_verifier','');
    $rec = store_get('code',$code); if (!$rec) json_out(['error'=>'invalid_grant'],400);
    if ($rec['client_id']!==$cid || $rec['redirect_uri']!==$redir) json_out(['error'=>'invalid_grant'],400);
    if (time() > (int)$rec['expires']) { store_delete('code',$code); json_out(['error'=>'invalid_grant'],400); }
    if (!empty($rec['code_challenge'])) {
        if (b64url_sha256($ver ?? '') !== $rec['code_challenge']) json_out(['error'=>'invalid_grant'],400);
    }
    store_delete('code',$code);

    $access = bin2hex(random_bytes(24));
    $atttl = (int)demo_cfg('at_ttl',3600);
    store_put('access',$access,[
        'user'=>$rec['user'],'scope'=>$rec['scope'],'client_id'=>$cid,'expires'=>time()+$atttl
    ]);
    $resp = ['token_type'=>'Bearer','access_token'=>$access,'expires_in'=>$atttl,'scope'=>$rec['scope']];

    if ((int)demo_cfg('issuerefresh',1)===1) {
        $refresh = bin2hex(random_bytes(32));
        store_put('refresh',$refresh,['user'=>$rec['user'],'scope'=>$rec['scope'],'client_id'=>$cid,'expires'=>time()+(int)demo_cfg('rt_ttl',1209600)]);
        $resp['refresh_token']=$refresh;
    }
    echo json_encode($resp, JSON_UNESCAPED_SLASHES); exit;
}

if ($grant === 'refresh_token') {
    $rt = postf('refresh_token',''); $rec = store_get('refresh',$rt);
    if (!$rec || $rec['client_id']!==$cid || time()>(int)$rec['expires']) json_out(['error'=>'invalid_grant'],400);
    $access = bin2hex(random_bytes(24)); $atttl=(int)demo_cfg('at_ttl',3600);
    store_put('access',$access,['user'=>$rec['user'],'scope'=>$rec['scope'],'client_id'=>$cid,'expires'=>time()+$atttl]);
    echo json_encode(['token_type'=>'Bearer','access_token'=>$access,'expires_in'=>$atttl,'scope'=>$rec['scope']], JSON_UNESCAPED_SLASHES); exit;
}

json_out(['error'=>'unsupported_grant_type'],400);
