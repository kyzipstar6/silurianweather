```php
<?php
// api/list.php – list available save slots (simple public listing)
header('Content-Type: application/json');
require_once __DIR__ . '/../db/pdo.php';

$rows = $pdo->query('SELECT slot, title, updated_at FROM saves ORDER BY updated_at DESC LIMIT 100')->fetchAll();
echo json_encode(['ok' => true, 'saves' => $rows]);
```

---