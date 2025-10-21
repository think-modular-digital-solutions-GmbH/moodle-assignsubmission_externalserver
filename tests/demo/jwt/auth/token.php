<?php
// This file is part of mod_extserver for Moodle - http://moodle.org/
//
// It is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Demo package using OAuth2: Token endpoint for OAuth2.
 *
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber <stefan.weber@think-modular.com>
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;

require_post();

$grant_type = postf('grant_type','');
$client_id  = postf('client_id','');
$secret     = postf('client_secret', null);

// Validate client credentials (client_credentials flow)
if ($grant_type !== 'client_credentials') {
    json_out(['error' => 'unsupported_grant_type'], 400);
}
if ($client_id !== CLIENT_ID) {
    json_out(['error' => 'unauthorized_client'], 401);
}
if ($secret !== null && $secret !== '' && $secret !== CLIENT_SECRET) {
    json_out(['error' => 'invalid_client'], 401);
}

// Load signing key
$privateKey = @file_get_contents(PRIVATE_KEY_PATH);
if ($privateKey === false) {
    json_out(['error' => 'server_error', 'error_description' => 'Private key not available at ' . PRIVATE_KEY_PATH], 500);
}

$now = time();
$exp = $now + ACCESS_TOKEN_TTL;

// Standard & useful claims
$claims = [
    'iss'   => OAUTH_ISSUER,                         // issuer
    'sub'   => 'service-account:' . $client_id,      // subject (no end user in client_credentials)
    'aud'   => OAUTH_AUDIENCE,                       // audience (optional but recommended)
    'iat'   => $now,
    'nbf'   => $now - 5,                             // small backdating for clock skew
    'exp'   => $exp,
    'jti'   => bin2hex(random_bytes(12)),            // unique token id (helps with optional revocation lists)
    'cid'   => $client_id,
];

// Header with kid (key id) for rotation
$jwt = JWT::encode($claims, $privateKey, 'RS256', JWT_KID, ['typ' => 'JWT', 'kid' => JWT_KID]);

json_out([
    'token_type'   => 'Bearer',
    'access_token' => $jwt,
    'expires_in'   => ACCESS_TOKEN_TTL,
]);
