```php
<?php
// api/delete.php – delete a save slot
header('Content-Type: application/json');
require_once __DIR__ . '/../db/pdo.php';

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
$slot = trim($body['slot'] ?? '');
if ($slot === '' || !preg_match('/^[a-zA-Z0-9_-]{1,40}$/', $slot)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid slot.']);
  exit;
}

$stmt = $pdo->prepare('DELETE FROM saves WHERE slot = :slot');
$stmt->execute([':slot' => $slot]);
echo json_encode(['ok' => true]);
```
