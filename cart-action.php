<?php
// Handles every cart change, then returns to the page it came from.

require __DIR__ . '/includes/bootstrap.php';

$return = safe_return(post_text('return'), 'cart.php');

if (!is_post() || !csrf_is_valid()) {
    set_flash('Your session expired. Please try again.', 'error');
    redirect($return);
}

$action = post_text('action');
$items = cart_items();

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    set_flash('Your cart is now empty.');
    redirect($return);
}

$product = get_product(to_id(post_text('id')));
if ($product === null) {
    set_flash('This product is no longer available.', 'error');
    redirect($return);
}

$id = (int) $product['id'];
$stock = (int) $product['stock'];
$name = $product['name'];
$quantity = post_text('quantity') === '' ? 1 : to_id(post_text('quantity'));

if ($action === 'remove') {
    unset($items[$id]);
    $_SESSION['cart'] = $items;
    set_flash($name . ' removed from your cart.');
    redirect($return);
}

if ($quantity < 1) {
    set_flash('Enter a quantity of 1 or more.', 'error');
    redirect($return);
}

if ($action === 'add') {
    if ($stock === 0) {
        set_flash($name . ' is sold out.', 'error');
    } elseif (($items[$id] ?? 0) + $quantity > $stock) {
        set_flash('Only ' . $stock . ' of ' . $name . ' in stock.', 'error');
    } else {
        $items[$id] = ($items[$id] ?? 0) + $quantity;
        $_SESSION['cart'] = $items;
        set_flash($name . ' added to your cart.');
    }
} elseif ($action === 'set' && isset($items[$id])) {
    if ($quantity > $stock) {
        set_flash('Only ' . $stock . ' of ' . $name . ' in stock.', 'error');
    } else {
        $items[$id] = $quantity;
        $_SESSION['cart'] = $items;
        set_flash($name . ' quantity updated.');
    }
}

redirect($return);
