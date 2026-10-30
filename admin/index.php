<?php
// Admin product table: lists every product, links to edit and deletes after confirmation.

define('ROOT_PATH', '../');
require __DIR__ . '/../includes/bootstrap.php';
require_admin();

$products = get_all_products();

$page = 'admin';
$title = 'Manage Products';
$pageScript = 'admin-list.js';
require __DIR__ . '/../includes/header.php';
?>
  <main id="main-content">
    <div class="container page-head admin-head">
      <div>
        <h1>Manage products</h1>
        <p><?= e(plural(count($products), 'product')) ?> in the shop</p>
      </div>
      <a class="btn btn--accent" href="product-form.php">Add product</a>
    </div>

    <section class="container" aria-label="Products">
      <?php show_flash(); ?>

      <?php if (!$products): ?>
        <div class="empty-state">
          <h2>No products yet</h2>
          <p>Add your first product to see it in the shop.</p>
          <a class="btn btn--outline" href="product-form.php">Add product</a>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="table admin-table">
            <thead>
              <tr>
                <th scope="col"><span class="sr-only">Image</span></th>
                <th scope="col">Product</th>
                <th scope="col">Category</th>
                <th scope="col">Price</th>
                <th scope="col">Stock</th>
                <th scope="col"><span class="sr-only">Actions</span></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $product): ?>
                <?php
                $id = (int) $product['id'];
                $stock = (int) $product['stock'];
                $name = e($product['name']);
                ?>
                <tr>
                  <td class="admin-table__image"><img src="<?= e(url($product['image'])) ?>" alt="" loading="lazy"></td>
                  <td class="admin-table__name"><a href="../product.php?id=<?= $id ?>"><?= $name ?></a></td>
                  <td data-label="Category"><?= e($product['category']) ?></td>
                  <td data-label="Price" class="admin-table__price"><?= e(format_price($product['price'])) ?></td>
                  <td data-label="Stock">
                    <?php if ($stock === 0): ?>
                      <span class="stock stock--out">Sold out</span>
                    <?php elseif ($stock <= LOW_STOCK): ?>
                      <span class="stock stock--low"><?= $stock ?> left</span>
                    <?php else: ?>
                      <span class="stock stock--in"><?= $stock ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="admin-table__actions">
                    <a class="btn btn--outline btn--small" href="product-form.php?id=<?= $id ?>">Edit<span class="sr-only"> <?= $name ?></span></a>
                    <form class="delete-form" method="post" action="delete.php" data-name="<?= $name ?>">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= $id ?>">
                      <button class="btn btn--danger btn--small" type="submit">Delete<span class="sr-only"> <?= $name ?></span></button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

    <dialog class="dialog" id="delete-dialog" aria-labelledby="delete-title">
      <h2 id="delete-title">Delete this product?</h2>
      <p><strong id="delete-name"></strong> will be removed from the shop. This cannot be undone.</p>
      <div class="dialog__actions">
        <button class="btn btn--outline" id="delete-cancel" type="button" autofocus>Keep product</button>
        <button class="btn btn--danger" id="delete-confirm" type="button">Delete product</button>
      </div>
    </dialog>
  </main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
