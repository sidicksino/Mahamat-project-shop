<?php
// Loaded first by every page. Pages in admin/ define ROOT_PATH as '../' before requiring this file.

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', '');
}

session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

set_exception_handler('show_fatal_error');
