<?php
// Cart page: change quantities, remove items and see the total. No checkout, as the brief excludes it.

require __DIR__ . '/includes/bootstrap.php';

$notices = cart_reconcile();
$lines = cart_lines();
$count = cart_count();
$total = array_sum(array_column($lines, 'total'));

// A hidden form that a button or box elsewhere on the line can submit through its form attribute
function cart_form(string $id, string $action, array $fields = []): string
{
    $html = '<form id="' . e($id) . '" method="post" action="cart-action.php">' . csrf_field();
    $html .= '<input type="hidden" name="action" value="' . e($action) . '">';
    $html .= '<input type="hidden" name="return" value="' . e($_SERVER['REQUEST_URI'] ?? '') . '">';
    foreach ($fields as $name => $value) {
        $html .= '<input type="hidden" name="' . e($name) . '" value="' . e($value) . '">';
    }
    return $html . '</form>';
}

$page = 'cart';
$title = 'Your Cart';
$pageScript = 'cart.js';
require __DIR__ . '/includes/header.php';
?>
  <main id="main-content">
    <div class="container page-head">
      <h1>Your cart</h1>
    </div>

    <section class="container" aria-label="Cart contents">
      <?php show_flash(); ?>
      <?php foreach ($notices as $notice): ?>
        <?= alert($notice, 'error') ?>
      <?php endforeach; ?>

      <?php if (!$lines): ?>
        <div class="empty-state">
          <h2>Your cart is empty</h2>
          <p>Browse the shop to find your next camera, lens or accessory.</p>
          <a class="btn" href="products.php">Go to the shop</a>
        </div>
      <?php else: ?>
        <div hidden>
          <?= cart_form('cart-clear', 'clear') ?>
          <?php foreach ($lines as $line): ?>
            <?php $id = (int) $line['product']['id']; ?>
            <?= cart_form('cart-set-' . $id, 'set', ['id' => $id]) ?>
            <?= cart_form('cart-less-' . $id, 'set', ['id' => $id, 'quantity' => $line['quantity'] - 1]) ?>
            <?= cart_form('cart-more-' . $id, 'set', ['id' => $id, 'quantity' => $line['quantity'] + 1]) ?>
            <?= cart_form('cart-remove-' . $id, 'remove', ['id' => $id]) ?>
          <?php endforeach; ?>
        </div>

        <div class="cart">
          <ul class="cart__lines">
            <?php foreach ($lines as $line): ?>
              <?php
              $product = $line['product'];
              $id = (int) $product['id'];
              $quantity = $line['quantity'];
              $stock = (int) $product['stock'];
              $name = e($product['name']);
              ?>
              <li class="cart-line">
                <a class="cart-line__image" href="product.php?id=<?= $id ?>">
                  <img src="<?= e(url($product['image'])) ?>" alt="<?= $name ?>">
                </a>
                <div class="cart-line__info">
                  <a class="cart-line__name" href="product.php?id=<?= $id ?>"><?= $name ?></a>
                  <p class="cart-line__unit"><?= e(format_price($product['price'])) ?> each</p>
                </div>
                <div class="stepper">
                  <button class="stepper__btn" type="submit" form="cart-less-<?= $id ?>" aria-label="Decrease quantity of <?= $name ?>"<?= $quantity <= 1 ? ' disabled' : '' ?>>&minus;</button>
                  <input class="stepper__input" type="number" name="quantity" form="cart-set-<?= $id ?>" min="1" max="<?= $stock ?>" step="1" value="<?= $quantity ?>" inputmode="numeric" aria-label="Quantity of <?= $name ?>">
                  <button class="stepper__btn" type="submit" form="cart-more-<?= $id ?>" aria-label="Increase quantity of <?= $name ?>"<?= $quantity >= $stock ? ' disabled' : '' ?>>+</button>
                </div>
                <p class="cart-line__total"><?= e(format_price($line['total'])) ?></p>
                <button class="cart-line__remove" type="submit" form="cart-remove-<?= $id ?>">Remove<span class="sr-only"> <?= $name ?></span></button>
              </li>
            <?php endforeach; ?>
          </ul>
          <aside class="cart-summary" aria-label="Order summary">
            <h2>Summary</h2>
            <p class="cart-summary__row">
              <span><?= e(plural($count, 'item')) ?></span>
              <strong id="cart-total"><?= e(format_price($total)) ?></strong>
            </p>
            <p class="cart-summary__note">Online checkout is not available yet. Visit our Kigali shop to buy these items.</p>
            <a class="btn btn--outline btn--block" href="products.php">Continue shopping</a>
            <button class="cart-summary__clear" type="submit" form="cart-clear">Clear cart</button>
          </aside>
        </div>
      <?php endif; ?>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
