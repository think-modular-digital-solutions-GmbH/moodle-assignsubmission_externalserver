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
 * Demo package using OAuth2: Configuration file.
 *
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber <stefan.weber@think-modular.com>
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Show all errors.
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Hash algorith to check integrity of sent data in hash.php.
define('SECRET_KEY', 'demo-hash-key');
define('HASH_ALGO', 'sha256');

// Server must be able to write in these directories.
define('STORE_DIR', __DIR__ . '/auth/_store');  // Storage dir for temp JSON (codes/tokens)
define('UPLOAD_DIR', __DIR__ . '/upload');      // Directory for uploaded submission files

// Standalone demo OAuth2 (opaque tokens) – configure here.
define('CLIENT_ID', 'demo-client');         // Client ID
define('CLIENT_SECRET', 'demo-secret');     // Secret key.
define('ACCESS_TOKEN_TTL', 3600);           // Access token lifetime (seconds)
define('DEFAULT_SCOPES', ['openid','profile','email','demo.read']);

// Requirements and common functions.
require_once __DIR__ . '/lib/hash.php';
require_once __DIR__ . '/auth/lib/common.php';
require_once __DIR__ . '/auth/lib/store.php';

// Garbage collect expired tokens/codes.
store_gc_opportunistic();