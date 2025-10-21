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
 * Demo package using OAuth2: common functions.
 *
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber <stefan.weber@think-modular.com>
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Ensure the token/code storage directory exists.
 */
if (!is_dir(STORE_DIR)) {
    @mkdir(STORE_DIR, 0770, true);
}

/**
 * Output a JSON response and terminate.
 *
 * @param array $payload  The data to send back as JSON
 * @param int   $status   HTTP status code (default: 200 OK)
 */
function json_out(array $payload, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store'); // don't cache tokens or errors
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Base64url-encode a binary string (RFC 7515).
 *
 * @param string $bin Binary data to encode
 * @return string Base64url-encoded string (no padding, URL-safe)
 */
function b64url(string $bin): string {
    return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
}

/**
 * Compute SHA-256 digest of input and return base64url-encoded string.
 * Used to verify PKCE code challenges.
 *
 * @param string $data Plaintext verifier
 * @return string Base64url-encoded SHA-256 hash
 */
function b64url_sha256(string $data): string {
    return b64url(hash('sha256', $data, true));
}

/**
 * Require that the current HTTP request method is POST.
 */
function require_post(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        json_out(
            ['error' => 'invalid_request', 'error_description' => 'POST required'],
            405
        );
    }
}

/**
 * Safe wrapper for reading a form field from $_POST.
 *
 * @param string      $key      Form parameter name
 * @param string|null $default  Default if missing
 * @return string|null
 */
function postf(string $key, ?string $default=null): ?string {
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

/**
 * Get the default scope string for this demo server.
 *
 * @return string Space-separated list of default scopes
 */
function default_scope_string(): string {
    return implode(' ', DEFAULT_SCOPES);
}


require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\CachedKeySet;

/**
 * Validate a Bearer access token from the Authorization header.
 *
 * @return array Decoded token data as array
 */
function require_bearer_token(): array {
    $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s+(.+)/i', $hdr, $m)) {
        json_out(['error' => 'invalid_token', 'error_description' => 'Missing bearer token'], 401);
    }
    $jwt = trim($m[1]);

    // Load public key for verification
    $public_key = @file_get_contents(PUBLIC_KEY_PATH);
    if ($public_key === false) {
        json_out(['error' => 'server_error', 'error_description' => 'Public key not available'], 500);
    }

    // Allow small clock skew
    \Firebase\JWT\JWT::$leeway = JWT_LEEWAY;

    try {
        $token = JWT::decode($jwt, new Key($public_key, 'RS256'));
    } catch (\Firebase\JWT\ExpiredException $e) {
        json_out(['error' => 'invalid_token', 'error_description' => 'Token expired'], 401);
    } catch (\Throwable $e) {
        json_out(['error' => 'invalid_token', 'error_description' => 'Token verification failed'], 401);
    }

    // Optional audience/issuer checks (recommended)
    if (isset($token->iss) && $token->iss !== OAUTH_ISSUER) {
        json_out(['error' => 'invalid_token', 'error_description' => 'Bad issuer'], 401);
    }
    if (isset($token->aud) && $token->aud !== OAUTH_AUDIENCE) {
        json_out(['error' => 'invalid_token', 'error_description' => 'Bad audience'], 401);
    }

    // Return token as array for downstream use
    return [
        'sub'    => $token->sub ?? null,
        'cid'    => $token->cid ?? null,
        'scope'  => $token->scope ?? '',
        'claims' => (array)$token,
    ];
}
