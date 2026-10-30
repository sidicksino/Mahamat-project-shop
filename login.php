<?php
// Admin login: checks the password against the users table, then returns to the page the visitor wanted.

require __DIR__ . '/includes/bootstrap.php';

$requested = is_post() ? post_text('next') : query_text('next');

// Only ever redirects to a page inside admin/, never to an address from the URL
$destination = preg_match('#^admin/[\w.-]+\.php(\?[\w=&-]*)?$#', $requested) === 1 ? $requested : 'admin/index.php';

if (is_logged_in()) {
    redirect($destination);
}

$errors = [];
$formMessage = null;
$username = '';

if (is_post()) {
    $username = post_text('username');
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';

    if ($username === '') {
        $errors['username'] = 'Enter your username.';
    }
    if ($password === '') {
        $errors['password'] = 'Enter your password.';
    }

    if (!csrf_is_valid()) {
        $formMessage = 'Your session expired. Please try again.';
    } elseif (!$errors) {
        $statement = db()->prepare('SELECT id, password_hash FROM users WHERE username = ?');
        $statement->execute([$username]);
        $user = $statement->fetch();

        if ($user !== false && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $user['id'];
            redirect($destination);
        }
        $formMessage = 'The username or password is incorrect.';
    }
}

$page = 'login';
$title = 'Admin Login';
require __DIR__ . '/includes/header.php';
?>
  <main id="main-content">
    <section class="container login">
      <div class="login__card">
        <h1>Admin login</h1>
        <p class="login__lead">Log in to add, edit and delete products.</p>
        <?php show_flash(); ?>
        <?php if ($formMessage !== null): ?>
          <?= alert($formMessage, 'error') ?>
        <?php endif; ?>

        <form method="post" action="login.php" novalidate>
          <?= csrf_field() ?>
          <input type="hidden" name="next" value="<?= e($destination) ?>">

          <div class="<?= field_class($errors, 'username') ?>">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" value="<?= e($username) ?>" autocomplete="username" autocapitalize="none" spellcheck="false" aria-describedby="username-error">
            <?= field_error($errors, 'username') ?>
          </div>

          <div class="<?= field_class($errors, 'password') ?>">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" aria-describedby="password-error">
            <?= field_error($errors, 'password') ?>
          </div>

          <button class="btn btn--accent btn--block" type="submit">Log in</button>
        </form>
      </div>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
