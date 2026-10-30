<?php
// Shared helpers: output, messages, security, products, cart and image uploads.

const LOW_STOCK = 5;
const MAX_IMAGE_BYTES = 5242880;
const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
const PRODUCT_SELECT = 'SELECT p.id, p.category_id, p.name, p.price, p.stock, p.description, p.image, p.created_at,
    c.name AS category FROM products p INNER JOIN categories c ON c.id = p.category_id';

/* Output */

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path): string
{
    return ROOT_PATH . $path;
}

function format_price($amount): string
{
    return 'RWF ' . number_format((int) $amount);
}

function plural(int $count, string $word): string
{
    return $count . ' ' . $word . ($count === 1 ? '' : 's');
}

function icon(string $name): string
{
    $icons = [
        'logo' => '<svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="16" cy="16" r="14.5" fill="none" stroke="currentColor" stroke-width="3"/><path d="M16 1.5 21 13M28.6 8.8 19 16.2M28.6 23.2 17.2 21.6M16 30.5 11 19M3.4 23.2 13 15.8M3.4 8.8 14.8 10.4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="16" cy="16" r="3.2" fill="#c23a24"/></svg>',
        'cart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h15l-1.5 9h-12z"/><path d="M6 7 5 3H2"/><circle cx="9" cy="20" r="1.3"/><circle cx="18" cy="20" r="1.3"/></svg>',
        'menu' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>',
        'search' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>',
    ];

    return $icons[$name];
}

function field_error(array $errors, string $field): string
{
    $hidden = isset($errors[$field]) ? '' : ' hidden';
    return '<p class="field__error" id="' . e($field) . '-error"' . $hidden . '>' . e($errors[$field] ?? '') . '</p>';
}

function field_class(array $errors, string $field): string
{
    return isset($errors[$field]) ? 'field has-error' : 'field';
}

// Shown instead of a blank page or raw PHP error when something goes wrong
function show_fatal_error(Throwable $error): void
{
    error_log((string) $error);
    http_response_code(500);

    $isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Something went wrong</title></head>';
    echo '<body style="font-family:sans-serif;max-width:40rem;margin:3rem auto;padding:0 1rem">';
    echo '<h1>Something went wrong</h1>';

    if ($error instanceof PDOException) {
        echo '<p>The site could not use the database. Check that MySQL is running in the XAMPP control panel and that <code>database/camera_store.sql</code> was imported.</p>';
    } else {
        echo '<p>The page could not be loaded. Please try again.</p>';
    }
    if ($isLocal) {
        echo '<p><strong>Details:</strong> ' . e($error->getMessage()) . '</p>';
    }

    echo '</body></html>';
}

/* Requests */

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function query_text(string $key): string
{
    return isset($_GET[$key]) && is_string($_GET[$key]) ? trim($_GET[$key]) : '';
}

function post_text(string $key): string
{
    return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : '';
}

// Returns 0 for anything that is not a positive whole number
function to_id(string $value): int
{
    return ctype_digit($value) && strlen($value) <= 9 ? (int) $value : 0;
}

// Only ever returns to a page on this site, never to an address from outside
function safe_return(string $path, string $fallback): string
{
    return preg_match('#^/(?!/)[^\\\\\r\n]*$#', $path) === 1 ? $path : $fallback;
}

/* Messages that survive a redirect, then show once */

function set_flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function alert(string $message, string $type = 'success'): string
{
    $role = $type === 'error' ? 'alert' : 'status';
    return '<div class="alert alert--' . e($type) . '" role="' . $role . '">' . e($message) . '</div>';
}

function show_flash(): void
{
    $flash = take_flash();
    if ($flash !== null) {
        echo alert($flash['message'], $flash['type']);
    }
}

function show_flash_toast(): void
{
    $flash = take_flash();
    if ($flash !== null) {
        echo '<div class="toast-region" role="status" aria-live="polite"><div class="toast toast--' . e($flash['type']) . '">' . e($flash['message']) . '</div></div>';
    }
}

/* Security */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_is_valid(): bool
{
    return hash_equals(csrf_token(), post_text('csrf_token'));
}

function is_logged_in(): bool
{
    return isset($_SESSION['admin_id']);
}

// Sends visitors to login from admin pages, remembering where they were going
function require_admin(): void
{
    header('Cache-Control: no-store');
    if (is_logged_in()) {
        return;
    }

    $next = 'admin/index.php';
    if (!is_post()) {
        $query = $_SERVER['QUERY_STRING'] ?? '';
        $next = 'admin/' . basename($_SERVER['SCRIPT_NAME']) . ($query === '' ? '' : '?' . $query);
    }
    redirect(url('login.php?next=' . urlencode($next)));
}

/* Products and categories */

function get_categories(): array
{
    static $categories = null;

    if ($categories === null) {
        $categories = db()->query(
            'SELECT c.id, c.name, c.image, COUNT(p.id) AS product_count
             FROM categories c LEFT JOIN products p ON p.category_id = c.id
             GROUP BY c.id, c.name, c.image ORDER BY c.id'
        )->fetchAll();
    }

    return $categories;
}

function get_all_products(): array
{
    return db()->query(PRODUCT_SELECT . ' ORDER BY p.id')->fetchAll();
}

function get_product(int $id): ?array
{
    $statement = db()->prepare(PRODUCT_SELECT . ' WHERE p.id = ?');
    $statement->execute([$id]);
    $product = $statement->fetch();
    return $product === false ? null : $product;
}

function category_link(string $name): string
{
    return url('products.php?category=' . urlencode($name));
}

function product_card(array $product): string
{
    $soldOut = (int) $product['stock'] === 0;
    $id = (int) $product['id'];
    $link = e(url('product.php?id=' . $id));
    $image = e(url($product['image']));
    $name = e($product['name']);
    $category = e($product['category']);
    $price = format_price($product['price']);
    $badge = $soldOut ? '<span class="product-card__badge">Sold out</span>' : '';
    $action = e(url('cart-action.php'));
    $csrf = csrf_field();
    $return = e($_SERVER['REQUEST_URI'] ?? '');
    $disabled = $soldOut ? 'disabled' : '';
    $label = $soldOut ? 'Sold out' : 'Add to cart';

    return <<<HTML
      <article class="product-card">
        <a class="product-card__link" href="{$link}">
          <div class="product-card__media">
            <img src="{$image}" alt="{$name}" loading="lazy">
            {$badge}
          </div>
          <div class="product-card__body">
            <span class="eyebrow">{$category}</span>
            <h3 class="product-card__name">{$name}</h3>
            <p class="product-card__price">{$price}</p>
          </div>
        </a>
        <form class="product-card__action" method="post" action="{$action}">
          {$csrf}
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="id" value="{$id}">
          <input type="hidden" name="return" value="{$return}">
          <button class="btn btn--block" type="submit" {$disabled}>{$label}</button>
        </form>
      </article>
    HTML;
}

/* Cart: stored in the session as [product id => quantity] */

function cart_items(): array
{
    return $_SESSION['cart'] ?? [];
}

// Cart items joined with their product details. Skips products that have since been deleted.
function cart_lines(): array
{
    $items = cart_items();
    if (!$items) {
        return [];
    }

    $ids = array_keys($items);
    $marks = implode(', ', array_fill(0, count($ids), '?'));
    $statement = db()->prepare(PRODUCT_SELECT . " WHERE p.id IN ($marks) ORDER BY p.name");
    $statement->execute($ids);

    $lines = [];
    foreach ($statement->fetchAll() as $product) {
        $quantity = $items[(int) $product['id']];
        $lines[] = ['product' => $product, 'quantity' => $quantity, 'total' => (int) $product['price'] * $quantity];
    }
    return $lines;
}

function cart_count(): int
{
    return array_sum(array_column(cart_lines(), 'quantity'));
}

// Brings the cart in line with current stock. Returns a message for each change made.
function cart_reconcile(): array
{
    $messages = [];
    $items = [];
    $lines = cart_lines();

    if (count($lines) < count(cart_items())) {
        $messages[] = 'A product in your cart is no longer available, so it was removed.';
    }

    foreach ($lines as $line) {
        $product = $line['product'];
        $stock = (int) $product['stock'];

        if ($stock === 0) {
            $messages[] = $product['name'] . ' has sold out, so it was removed from your cart.';
        } elseif ($line['quantity'] > $stock) {
            $messages[] = 'Only ' . $stock . ' of ' . $product['name'] . ' left, so your quantity was reduced.';
            $items[(int) $product['id']] = $stock;
        } else {
            $items[(int) $product['id']] = $line['quantity'];
        }
    }

    $_SESSION['cart'] = $items;
    return $messages;
}

/* Image uploads */

// Returns an error message, or null when the file is acceptable or none was chosen
function upload_error(array $file): ?string
{
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE || $file['size'] > MAX_IMAGE_BYTES) {
        return 'This image is larger than 5 MB. Choose a smaller one.';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'The image could not be uploaded. Please try again.';
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false || !array_key_exists($info['mime'], IMAGE_TYPES)) {
        return 'Choose a JPG, PNG or WebP image.';
    }
    return null;
}

// Moves a checked upload into uploads/ under a random name. Returns its path, or null if it failed.
function save_upload(array $file): ?string
{
    $info = getimagesize($file['tmp_name']);
    $filename = bin2hex(random_bytes(16)) . '.' . IMAGE_TYPES[$info['mime']];

    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../uploads/' . $filename)) {
        return null;
    }
    return 'uploads/' . $filename;
}

// The sample images in assets/ are never deleted, only files in uploads/
function delete_upload(string $path): void
{
    if (strpos($path, 'uploads/') !== 0) {
        return;
    }

    $file = __DIR__ . '/../uploads/' . basename($path);
    if (is_file($file)) {
        unlink($file);
    }
}
