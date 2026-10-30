<?php
require __DIR__ . '/includes/bootstrap.php';

if (is_post() && csrf_is_valid()) {
    unset($_SESSION['admin_id']);
    session_regenerate_id(true);
    set_flash('You have been logged out.');
}

redirect('login.php');
