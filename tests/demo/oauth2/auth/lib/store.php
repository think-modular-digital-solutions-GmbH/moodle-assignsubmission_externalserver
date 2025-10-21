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
 * Demo package using OAuth2: common functions to store/retrieve token files.
 *
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber <stefan.weber@think-modular.com>
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Build a safe filename for a stored object.
 *
 * @param string $type  The record type (e.g. 'access', 'refresh', 'code')
 * @param string $id    The unique identifier (token string, code, etc.)
 * @return string       Full path to the JSON file where this record lives
 */
function store_file(string $type, string $id): string {
    $safe = preg_replace('~[^a-zA-Z0-9_-]~', '_', $id);
    return STORE_DIR . "/{$type}_{$safe}.json";
}

/**
 * Save a record as JSON to disk.
 *
 * @param string $type  The record type (e.g. 'access', 'code', 'refresh')
 * @param string $id    The unique identifier for this record
 * @param array  $data  Arbitrary data to store (user, scope, expiry, etc.)
 */
function store_put(string $type, string $id, array $data): void {
    $data['_saved_at'] = time();
    file_put_contents(store_file($type, $id), json_encode($data), LOCK_EX);
}

/**
 * Read and decode a stored record.
 *
 * @param string $type  Record type (e.g. 'access', 'code')
 * @param string $id    The identifier (token, code, etc.)
 * @return array|null   The decoded record, or null if not found or invalid
 */
function store_get(string $type, string $id): ?array {
    $f = store_file($type, $id);
    if (!is_readable($f)) return null;
    $d = json_decode((string)file_get_contents($f), true);
    return is_array($d) ? $d : null;
}

/**
 * Delete a stored record.
 *
 * @param string $type  Record type (e.g. 'code', 'access')
 * @param string $id    Identifier of the record to delete
 */
function store_del(string $type, string $id): void {
    $f = store_file($type, $id);
    if (is_file($f)) @unlink($f);
}

/**
 * Opportunistic garbage collection of expired tokens/codes.
 *
 * This function runs with a small probability on each request to clean up
 * expired tokens and one-time codes that were used but never deleted.
 */
function store_gc_opportunistic(): void {
    // ~2% chance to run
    if (mt_rand(1, 50) !== 1) return;

    $now = time();
    $dh = @opendir(STORE_DIR);
    if (!$dh) return;

    while (($f = readdir($dh)) !== false) {
        if ($f === '.' || $f === '..') continue;
        if (!preg_match('~^(access|refresh|code)_.+\.json$~', $f)) continue;

        $path = STORE_DIR . '/' . $f;
        $data = json_decode(@file_get_contents($path), true);
        if (!is_array($data)) { @unlink($path); continue; }

        // Remove expired codes/tokens. Add small skew to be safe.
        $skew = 60;
        if (isset($data['expires']) && ($data['expires'] + $skew) < $now) {
            @unlink($path);
        }

        // Also delete one-time codes that were used but never cleaned up (defensive)
        if (str_starts_with($f, 'code_') && isset($data['_saved_at']) && ($data['_saved_at'] + 86400) < $now) {
            @unlink($path);
        }
    }
    closedir($dh);
}

