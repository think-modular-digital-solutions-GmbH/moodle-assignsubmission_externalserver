<?php
// local/<yourplugin>/tests/.../store.php
declare(strict_types=1);
require_once(__DIR__ . '/common.php');

function store_dir(): string {
    $dir = make_temp_directory('<yourplugin>_demo_oauth2');
    return $dir;
}
function store_put(string $type, string $id, array $data): void {
    $file = store_dir() . "/{$type}_" . preg_replace('~[^a-zA-Z0-9_-]~', '_', $id) . ".json";
    $data['_saved_at'] = time();
    file_put_contents($file, json_encode($data), LOCK_EX);
}
function store_get(string $type, string $id): ?array {
    $file = store_dir() . "/{$type}_" . preg_replace('~[^a-zA-Z0-9_-]~', '_', $id) . ".json";
    if (!is_readable($file)) return null;
    return json_decode((string)file_get_contents($file), true) ?: null;
}
function store_delete(string $type, string $id): void {
    $file = store_dir() . "/{$type}_" . preg_replace('~[^a-zA-Z0-9_-]~', '_', $id) . ".json";
    if (is_file($file)) @unlink($file);
}
