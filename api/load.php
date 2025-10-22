<?php
// api/load.php – fetch a save-slot
header('Content-Type: application/json');
require_once __DIR__ . '/../db/pdo.php';

$slot = trim($_GET['slot'] ?? '');
if ($slot === '' || !preg_match('/^[a-zA-Z0-9_-]{1,40}$/', $slot)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid slot.']);
  exit;
}

$stmt = $pdo->prepare('SELECT slot, title, payload, updated_at FROM saves WHERE slot = :slot');
$stmt->execute([':slot' => $slot]);
$row = $stmt->fetch();

if (!$row) {
  http_response_code(404);
  echo json_encode(['ok' => false, 'error' => 'Not found']);
  exit;
}

echo json_encode(['ok' => true, 'slot' => $row['slot'], 'title' => $row['title'], 'state' => json_decode($row['payload'], true), 'updated_at' => $row['updated_at']]);
