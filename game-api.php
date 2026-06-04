<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$base   = __DIR__;

// ── List saves ────────────────────────────────────────────
if ($action === 'list-saves') {
    $saves = [];
    $savesDir = $base . '/saves/';
    if (is_dir($savesDir)) {
        foreach (glob($savesDir . '*', GLOB_ONLYDIR) as $gameDir) {
            $gameName = basename($gameDir);
            if ($gameName === 'auto') continue; // skip auto-snapshots in UI list
            $slots = glob($gameDir . '/*', GLOB_ONLYDIR);
            rsort($slots); // newest first
            foreach ($slots as $slot) {
                $slotName = basename($slot);
                $meta = '';
                $metaFile = $slot . '/save-meta.md';
                if (file_exists($metaFile)) {
                    $raw = file_get_contents($metaFile);
                    // Pull first 3 lines as preview
                    $lines = array_slice(explode("\n", trim($raw)), 0, 3);
                    $meta  = implode(' · ', array_map('trim', $lines));
                }
                $saves[] = [
                    'game'    => $gameName,
                    'slot'    => $slotName,
                    'path'    => "$gameName/$slotName",
                    'preview' => $meta ?: "$gameName / $slotName",
                    'mtime'   => filemtime($slot),
                ];
            }
        }
    }
    usort($saves, fn($a,$b) => $b['mtime'] - $a['mtime']);
    echo json_encode(['saves' => $saves]);
    exit;
}

// ── List world presets ────────────────────────────────────
if ($action === 'list-worlds') {
    $worlds = [];
    $presetsDir = $base . '/world-presets/';
    if (is_dir($presetsDir)) {
        foreach (glob($presetsDir . '*', GLOB_ONLYDIR) as $dir) {
            $name = basename($dir);
            $desc = '';
            $stateFile = $dir . '/global-state.md';
            if (file_exists($stateFile)) {
                $lines = array_slice(explode("\n", trim(file_get_contents($stateFile))), 0, 2);
                $desc  = trim(implode(' ', $lines), "# \t");
            }
            $worlds[] = ['name' => $name, 'description' => $desc ?: $name];
        }
    }
    // Always include the default
    array_unshift($worlds, ['name' => 'earth-invasion', 'description' => 'Default — Modern Earth, alien invasion, 98% of humanity in tutorial']);
    echo json_encode(['worlds' => $worlds]);
    exit;
}

// ── Send command / player message ─────────────────────────
if ($action === 'send-command' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $chatFile = $base . '/data/chat.json';
    $input    = json_decode(file_get_contents('php://input'), true);
    $command  = trim($input['command'] ?? '');
    if (!$command) { http_response_code(400); echo json_encode(['error' => 'No command']); exit; }
    if (!file_exists($chatFile)) { http_response_code(500); echo json_encode(['error' => 'Chat file missing']); exit; }

    $chat = json_decode(file_get_contents($chatFile), true);
    $msg  = [
        'id'        => count($chat['messages']) + 1,
        'sender'    => 'player',
        'content'   => $command,
        'timestamp' => date('c'),
    ];
    $chat['messages'][] = $msg;
    $chat['status']     = 'waiting_for_gm';
    file_put_contents($chatFile, json_encode($chat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
