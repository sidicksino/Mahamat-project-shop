<?php
// Add and edit form. ?id=3 means edit. The same rules apply to both modes.

define('ROOT_PATH', '../');
require __DIR__ . '/../includes/bootstrap.php';
require_admin();

const MAX_PRICE = 999999999;
const MAX_STOCK = 1000000;

$isEdit = isset($_GET['id']);
$product = $isEdit ? get_product(to_id(query_text('id'))) : null;
$categories = get_categories();
$categoryIds = array_map('intval', array_column($categories, 'id'));

$errors = [];
$formMessage = null;
$values = [
    'name' => $product['name'] ?? '',
    'category_id' => $product['category_id'] ?? '',
    'price' => $product['price'] ?? '',
    'stock' => $product['stock'] ?? '',
    'description' => $product['description'] ?? '',
];

if (is_post() && (!$isEdit || $product !== null)) {
    foreach ($values as $field => $unused) {
        $values[$field] = post_text($field);
    }

    $price = $values['price'];
    $stock = $values['stock'];
    $file = isset($_FILES['image']) && is_int($_FILES['image']['error']) ? $_FILES['image'] : ['error' => UPLOAD_ERR_NO_FILE, 'size' => 0];
    $hasNewImage = $file['error'] !== UPLOAD_ERR_NO_FILE;

    if (strlen($values['name']) < 2 || strlen($values['name']) > 120) {
        $errors['name'] = 'Enter a product name of 2 to 120 characters.';
    }
    if (!in_array(to_id($values['category_id']), $categoryIds, true)) {
        $errors['category_id'] = 'Choose a category.';
    }
    if (!ctype_digit($price) || (int) $price < 1 || (int) $price > MAX_PRICE) {
        $errors['price'] = 'Enter a price in whole Rwandan francs, above 0.';
    }
    if (!ctype_digit($stock) || (int) $stock > MAX_STOCK) {
        $errors['stock'] = 'Enter a stock quantity of 0 or more.';
    }
    if (strlen($values['description']) < 10 || strlen($values['description']) > 1000) {
        $errors['description'] = 'Enter a description of 10 to 1000 characters.';
    }

    $imageError = upload_error($file);
    if ($imageError !== null) {
        $errors['image'] = $imageError;
    } elseif (!$hasNewImage && !$isEdit) {
        $errors['image'] = 'Add a product image.';
    } elseif ($hasNewImage && $errors) {
        // A browser cannot keep a chosen file after the form reloads
        $errors['image'] = 'Choose the image again.';
    }

    if (!csrf_is_valid()) {
        // An upload above the server limit also empties the form, including this token
        $formMessage = 'The form could not be saved. Your session expired or the image was too large. Please try again.';
    } elseif ($errors) {
        $formMessage = 'Please fix the highlighted fields and try again.';
    } else {
        $image = $isEdit ? $product['image'] : '';
        if ($hasNewImage) {
            $image = save_upload($file);
        }

        if ($image === null) {
            $errors['image'] = 'The image could not be saved. Check that the uploads folder exists and can be written to.';
            $formMessage = 'Please fix the highlighted fields and try again.';
        } else {
            $fields = [$values['name'], (int) $values['category_id'], (int) $price, (int) $stock, $values['description'], $image];

            if ($isEdit) {
                $statement = db()->prepare(
                    'UPDATE products SET name = ?, category_id = ?, price = ?, stock = ?, description = ?, image = ? WHERE id = ?'
                );
                $statement->execute(array_merge($fields, [$product['id']]));
                if ($hasNewImage) {
                    delete_upload($product['image']);
                }
                set_flash($values['name'] . ' was updated.');
            } else {
                $statement = db()->prepare(
                    'INSERT INTO products (name, category_id, price, stock, description, image) VALUES (?, ?, ?, ?, ?, ?)'
                );
                $statement->execute($fields);
                set_flash($values['name'] . ' was added.');
            }
            redirect('index.php');
        }
    }
}

$page = 'admin';
$pageScript = 'admin-form.js';
if ($isEdit && $product === null) {
    $title = 'Product Not Found';
    http_response_code(404);
} else {
    $title = $isEdit ? 'Edit ' . $product['name'] : 'Add Product';
}
require __DIR__ . '/../includes/header.php';
?>
  <main id="main-content">
    <div class="container page-head">
      <a class="back-link" href="index.php">&larr; Back to products</a>
      <h1><?= $isEdit ? 'Edit product' : 'Add product' ?></h1>
    </div>

    <section class="container">
      <?php if ($isEdit && $product === null): ?>
        <div class="empty-state">
          <h2>Product not found</h2>
          <p>This product may already have been deleted.</p>
          <a class="btn" href="index.php">Back to products</a>
        </div>
      <?php else: ?>
        <?php if ($formMessage !== null): ?>
          <?= alert($formMessage, 'error') ?>
        <?php endif; ?>

        <form class="product-form" id="product-form" method="post" action="product-form.php<?= $isEdit ? '?id=' . (int) $product['id'] : '' ?>" enctype="multipart/form-data" novalidate>
          <?= csrf_field() ?>

          <div class="product-form__fields">
            <div class="<?= field_class($errors, 'name') ?>">
              <label for="name">Product name</label>
              <input id="name" name="name" type="text" value="<?= e($values['name']) ?>" maxlength="120" aria-describedby="name-error">
              <?= field_error($errors, 'name') ?>
            </div>

            <div class="<?= field_class($errors, 'category_id') ?>">
              <label for="category_id">Category</label>
              <select id="category_id" name="category_id" aria-describedby="category_id-error">
                <option value="">Choose a category</option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?= (int) $category['id'] ?>"<?= (string) $values['category_id'] === (string) $category['id'] ? ' selected' : '' ?>><?= e($category['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <?= field_error($errors, 'category_id') ?>
            </div>

            <div class="product-form__row">
              <div class="<?= field_class($errors, 'price') ?>">
                <label for="price">Price (RWF)</label>
                <input id="price" name="price" type="number" value="<?= e($values['price']) ?>" min="1" step="1" inputmode="numeric" aria-describedby="price-error">
                <?= field_error($errors, 'price') ?>
              </div>

              <div class="<?= field_class($errors, 'stock') ?>">
                <label for="stock">Stock quantity</label>
                <input id="stock" name="stock" type="number" value="<?= e($values['stock']) ?>" min="0" step="1" inputmode="numeric" aria-describedby="stock-error">
                <?= field_error($errors, 'stock') ?>
              </div>
            </div>

            <div class="<?= field_class($errors, 'description') ?>">
              <label for="description">Description</label>
              <textarea id="description" name="description" rows="5" maxlength="1000" aria-describedby="description-error"><?= e($values['description']) ?></textarea>
              <?= field_error($errors, 'description') ?>
            </div>
          </div>

          <div class="product-form__image">
            <div class="<?= field_class($errors, 'image') ?>">
              <label for="image">Product image</label>
              <div class="image-preview" id="image-preview">
                <?php if ($product !== null): ?>
                  <img src="<?= e(url($product['image'])) ?>" alt="Selected product image">
                <?php else: ?>
                  <span>No image chosen</span>
                <?php endif; ?>
              </div>
              <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="image-help image-error">
              <p class="field__help" id="image-help">JPG, PNG or WebP, up to 5 MB.</p>
              <?= field_error($errors, 'image') ?>
            </div>
          </div>

          <div class="product-form__actions">
            <button class="btn btn--accent" type="submit"><?= $isEdit ? 'Save changes' : 'Add product' ?></button>
            <a class="btn btn--outline" href="index.php">Cancel</a>
          </div>
        </form>
      <?php endif; ?>
    </section>
  </main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
