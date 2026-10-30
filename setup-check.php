<?php
// Setup check: open this page first on a new computer. It lists anything that would stop the site working.

require __DIR__ . '/config/db.php';

$checks = [];

function add_check(array &$checks, string $label, bool $passed, string $help = ''): void
{
    $checks[] = ['label' => $label, 'passed' => $passed, 'help' => $help];
}

add_check($checks, 'PHP is version 7.4 or newer (this is ' . PHP_VERSION . ')', version_compare(PHP_VERSION, '7.4.0', '>='), 'Install a newer XAMPP.');

$hasDriver = extension_loaded('pdo_mysql');
add_check($checks, 'The PHP MySQL driver (pdo_mysql) is on', $hasDriver, 'Turn on extension=pdo_mysql in php.ini, then restart Apache.');

$uploads = __DIR__ . '/uploads';
add_check($checks, 'The uploads folder can be written to', is_dir($uploads) && is_writable($uploads), 'Create the uploads folder and allow Apache to write to it.');

$sample = __DIR__ . '/assets/images/products/vista-x7.jpg';
add_check($checks, 'The product images are present', is_file($sample), 'The project was not copied completely. Clone it again.');

if ($hasDriver) {
    try {
        $pdo = db();
        add_check($checks, 'Connected to the camera_store database', true);

        foreach (['categories' => 4, 'products' => 1, 'users' => 1] as $table => $minimum) {
            $count = (int) $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            add_check($checks, "The $table table has data ($count rows)", $count >= $minimum, 'Import database/camera_store.sql again.');
        }

        $pdo->query('SELECT COUNT(*) FROM messages');
        add_check($checks, 'The messages table exists', true);

        $hash = $pdo->query("SELECT password_hash FROM users WHERE username = 'admin'")->fetchColumn();
        add_check($checks, 'The default admin login works', is_string($hash) && password_verify('admin123', $hash), 'Import database/camera_store.sql again.');
    } catch (PDOException $error) {
        add_check($checks, 'The database is ready', false, 'Start MySQL in XAMPP, import database/camera_store.sql, and check the settings in config/db.php. Details: ' . $error->getMessage());
    }
}

$allPassed = !in_array(false, array_column($checks, 'passed'), true);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Setup Check | FocalPoint</title>
  <style>
    body { max-width: 44rem; margin: 3rem auto; padding: 0 1rem; font-family: sans-serif; line-height: 1.5; }
    li { margin-bottom: 0.75rem; }
    .pass { color: #1a7a43; }
    .fail { color: #b3261e; font-weight: 700; }
  </style>
</head>
<body>
  <h1>Setup check</h1>
  <p class="<?= $allPassed ? 'pass' : 'fail' ?>">
    <?= $allPassed ? 'Everything is ready.' : 'Fix the items marked FAIL, then reload this page.' ?>
  </p>
  <ul>
    <?php foreach ($checks as $check): ?>
      <li>
        <span class="<?= $check['passed'] ? 'pass' : 'fail' ?>"><?= $check['passed'] ? 'OK' : 'FAIL' ?></span>
        <?= htmlspecialchars($check['label'], ENT_QUOTES, 'UTF-8') ?>
        <?php if (!$check['passed']): ?>
          <br><?= htmlspecialchars($check['help'], ENT_QUOTES, 'UTF-8') ?>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php if ($allPassed): ?>
    <p><a href="index.php">Open the store</a></p>
  <?php endif; ?>
</body>
</html>
