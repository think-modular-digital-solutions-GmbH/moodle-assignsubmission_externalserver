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

require_post();

// Read POST parameters from the token request
$grant_type = postf('grant_type','');
$client_id  = postf('client_id','');
$secret     = postf('client_secret', null);

// Validate the client credentials.
if ($grant_type !== 'client_credentials') {
    json_out(['error'=>'unsupported_grant_type'], 400);
}
if ($client_id !== CLIENT_ID) {
    json_out(['error'=>'unauthorized_client'], 401);
}

if ($secret !== null && $secret !== '' && $secret !== CLIENT_SECRET) {
    json_out(['error'=>'invalid_client'], 401);
}

// Optional scope request (fallback to default if not given)
$scope = postf('scope', default_scope_string());

// Create and store a service-account access token (no user involved)
$access = bin2hex(random_bytes(24));
store_put('access', $access, [
    'user'      => [
        'sub'   => 'service-account:' . $client_id,
        'name'  => $client_id . ' (Service Account)',
        'email' => ''
    ],
    'scope'     => $scope,
    'client_id' => $client_id,
    'expires'   => time() + ACCESS_TOKEN_TTL
]);

// Return token to client
json_out([
    'token_type'   => 'Bearer',
    'access_token' => $access,
    'expires_in'   => ACCESS_TOKEN_TTL,
    'scope'        => $scope
]);

// Unknown grant type → return standard error.
json_out(['error'=>'unsupported_grant_type'], 400);
