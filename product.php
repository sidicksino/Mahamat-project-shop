<?php
require __DIR__ . '/includes/bootstrap.php';

const RELATED_COUNT = 4;

$product = get_product(to_id(query_text('id')));
$related = [];

if ($product !== null) {
    $stock = (int) $product['stock'];
    $statement = db()->prepare(
        PRODUCT_SELECT . ' WHERE p.category_id = ? AND p.id <> ? ORDER BY p.id LIMIT ' . RELATED_COUNT
    );
    $statement->execute([$product['category_id'], $product['id']]);
    $related = $statement->fetchAll();
} else {
    http_response_code(404);
}

$page = 'products';
$title = $product !== null ? $product['name'] : 'Product Not Found';
require __DIR__ . '/includes/header.php';
?>
  <main id="main-content">
    <div class="container">
      <?php if ($product === null): ?>
        <div class="empty-state product-missing">
          <h1>Product not found</h1>
          <p>This product may have been removed, or the link may be wrong.</p>
          <a class="btn" href="products.php">Back to the shop</a>
        </div>
      <?php else: ?>
        <nav class="breadcrumb" aria-label="Breadcrumb">
          <a href="products.php">Shop</a>
          <span aria-hidden="true">/</span>
          <a href="<?= e(category_link($product['category'])) ?>"><?= e($product['category']) ?></a>
          <span aria-hidden="true">/</span>
          <span aria-current="page"><?= e($product['name']) ?></span>
        </nav>

        <article class="product-detail">
          <div class="product-detail__media">
            <img src="<?= e(url($product['image'])) ?>" alt="<?= e($product['name']) ?>">
          </div>
          <div class="product-detail__info">
            <a class="eyebrow" href="<?= e(category_link($product['category'])) ?>"><?= e($product['category']) ?></a>
            <h1><?= e($product['name']) ?></h1>
            <p class="product-detail__price"><?= e(format_price($product['price'])) ?></p>
            <?php if ($stock === 0): ?>
              <span class="stock stock--out">Sold out</span>
            <?php elseif ($stock <= LOW_STOCK): ?>
              <span class="stock stock--low">Only <?= $stock ?> left in stock</span>
            <?php else: ?>
              <span class="stock stock--in">In stock (<?= $stock ?> available)</span>
            <?php endif; ?>
            <p class="product-detail__description"><?= e($product['description']) ?></p>

            <?php if ($stock === 0): ?>
              <button class="btn btn--block" type="button" disabled>Sold out</button>
            <?php else: ?>
              <form class="purchase" method="post" action="cart-action.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                <input type="hidden" name="return" value="<?= e($_SERVER['REQUEST_URI'] ?? '') ?>">
                <div class="field purchase__quantity">
                  <label for="quantity">Quantity</label>
                  <input id="quantity" name="quantity" type="number" min="1" max="<?= $stock ?>" step="1" value="1" inputmode="numeric">
                </div>
                <button class="btn btn--accent" type="submit">Add to cart</button>
              </form>
            <?php endif; ?>
          </div>
        </article>

        <?php if ($related): ?>
          <section class="related" aria-labelledby="related-heading">
            <div class="section-head">
              <h2 id="related-heading">More in <?= e($product['category']) ?></h2>
            </div>
            <div class="product-grid">
              <?php foreach ($related as $item): ?>
                <?= product_card($item) ?>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </main>

  <?php show_flash_toast(); ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
