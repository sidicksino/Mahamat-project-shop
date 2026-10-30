<?php
// Deletes one product and its uploaded image, then returns to the product table.

define('ROOT_PATH', '../');
require __DIR__ . '/../includes/bootstrap.php';
require_admin();

if (!is_post() || !csrf_is_valid()) {
    set_flash('Your session expired. Please try again.', 'error');
    redirect('index.php');
}

$product = get_product(to_id(post_text('id')));

if ($product === null) {
    set_flash('This product could not be deleted. It may already be gone.', 'error');
    redirect('index.php');
}

$statement = db()->prepare('DELETE FROM products WHERE id = ?');
$statement->execute([$product['id']]);
delete_upload($product['image']);

set_flash($product['name'] . ' was deleted.');
redirect('index.php');
