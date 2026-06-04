<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

$chatFile = __DIR__ . '/data/chat.json';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['content']) || trim($input['content']) === '') {
    http_response_code(400);
    echo json_encode(['error' => 'No content']);
    exit;
}

if (!file_exists($chatFile)) {
    http_response_code(500);
    echo json_encode(['error' => 'Chat file not found']);
    exit;
}

$chat = json_decode(file_get_contents($chatFile), true);

if ($chat['status'] !== 'waiting_for_player') {
    http_response_code(409);
    echo json_encode(['error' => 'Game master has not replied yet']);
    exit;
}

$msg = [
    'id'        => count($chat['messages']) + 1,
    'sender'    => 'player',
    'content'   => trim($input['content']),
    'timestamp' => date('c')
];

$chat['messages'][] = $msg;
$chat['status'] = 'waiting_for_gm';

file_put_contents($chatFile, json_encode($chat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['ok' => true, 'message' => $msg]);
