<?php
// api/save.php – upsert a save-slot
header('Content-Type: application/json');
require_once __DIR__ . '/../db/pdo.php';

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

$slot   = trim(($body['slot'] ?? ''));
$title  = trim(($body['title'] ?? 'Silurian run'));
$state  = $body['state'] ?? null; // arbitrary JSON object

if ($slot === '' || !preg_match('/^[a-zA-Z0-9_-]{1,40}$/', $slot)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid slot. Use 1–40 chars [a-zA-Z0-9_-].']);
  exit;
}
if (!is_array($state)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'state must be a JSON object']);
  exit;
}

try {
  $json = json_encode($state, JSON_UNESCAPED_UNICODE);
  $stmt = $pdo->prepare('INSERT INTO saves(slot, title, payload, created_at, updated_at)
    VALUES(:slot, :title, :payload, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
    ON CONFLICT(slot) DO UPDATE SET title = excluded.title, payload = excluded.payload, updated_at = CURRENT_TIMESTAMP');
  $stmt->execute([':slot' => $slot, ':title' => $title, ':payload' => $json]);
  echo json_encode(['ok' => true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
