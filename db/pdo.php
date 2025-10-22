
<?php
// db/pdo.php
// Lightweight PDO bootstrap for SQLite (default) or PostgreSQL (Render ready).
// Uses environment variables when present.

$DB_DRIVER = getenv('DB_DRIVER') ?: 'sqlite';

try {
  if ($DB_DRIVER === 'pgsql') {
    // e.g. postgres://user:pass@host:5432/dbname
    $dsn = getenv('DATABASE_URL');
    if (!$dsn) throw new Exception('DATABASE_URL not set');

    // Convert Heroku/Render style URL to PDO parts if needed
    if (str_starts_with($dsn, 'postgres://')) {
      $dsn = preg_replace('#^postgres://#', 'pgsql://', $dsn);
    }
    $url = parse_url($dsn);
    $host = $url['host'] ?? 'localhost';
    $port = $url['port'] ?? '5432';
    $user = $url['user'] ?? '';
    $pass = $url['pass'] ?? '';
    $db   = ltrim($url['path'] ?? '', '/');
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  } else {
    // SQLite
    $dataDir = __DIR__ . '/../data';
    if (!is_dir($dataDir)) {
      mkdir($dataDir, 0775, true);
    }
    $pdo = new PDO('sqlite:' . $dataDir . '/silurian.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  }

  // Ensure schema
  $pdo->exec('CREATE TABLE IF NOT EXISTS saves (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slot TEXT NOT NULL,
    title TEXT,
    payload TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
  );');
  $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_saves_slot ON saves(slot);');
} catch (Throwable $e) {
  http_response_code(500);
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'error' => 'DB error: ' . $e->getMessage()]);
  exit;
}
