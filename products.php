<?php
require __DIR__ . '/includes/bootstrap.php';

const SORTS = [
    'price-asc' => 'p.price ASC, p.id ASC',
    'price-desc' => 'p.price DESC, p.id ASC',
    'name' => 'p.name ASC',
];

$categoryNames = array_column(get_categories(), 'name');
$search = query_text('q');
$category = in_array(query_text('category'), $categoryNames, true) ? query_text('category') : '';
$sort = array_key_exists(query_text('sort'), SORTS) ? query_text('sort') : 'default';

// Every word must appear somewhere. Each value is sent separately, so it cannot change the SQL.
$where = [];
$whereParams = [];
$nameMatches = [];
$nameParams = [];
foreach (preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) as $word) {
    $like = '%' . addcslashes($word, '%_\\') . '%';
    $where[] = '(p.name LIKE ? OR c.name LIKE ? OR p.description LIKE ?)';
    array_push($whereParams, $like, $like, $like);
    $nameMatches[] = 'p.name LIKE ?';
    $nameParams[] = $like;
}
if ($category !== '') {
    $where[] = 'c.name = ?';
    $whereParams[] = $category;
}

$sql = PRODUCT_SELECT . ($where ? ' WHERE ' . implode(' AND ', $where) : '');
if ($sort !== 'default') {
    $sql .= ' ORDER BY ' . SORTS[$sort];
    $params = $whereParams;
} elseif ($nameMatches) {
    // Featured order: products named after the search come before ones that only mention it
    $sql .= ' ORDER BY (' . implode(' AND ', $nameMatches) . ') DESC, p.id ASC';
    $params = array_merge($whereParams, $nameParams);
} else {
    $sql .= ' ORDER BY p.id ASC';
    $params = $whereParams;
}

$statement = db()->prepare($sql);
$statement->execute($params);
$products = $statement->fetchAll();

// Keeps the search and sort when a category chip is chosen
function chip_link(string $category, string $search, string $sort): string
{
    $query = array_filter(['q' => $search, 'category' => $category, 'sort' => $sort === 'default' ? '' : $sort], 'strlen');
    return 'products.php' . ($query ? '?' . http_build_query($query) : '');
}

$page = 'products';
$title = 'Shop All Products';
$headerSearch = $search;
$pageScript = 'products.js';
require __DIR__ . '/includes/header.php';
?>
  <main id="main-content">
    <div class="container page-head">
      <h1>Shop all gear</h1>
      <p>Find the right camera, lens or accessory for the way you work.</p>
    </div>

    <section class="container shop" aria-label="Products">
      <form class="shop-toolbar" id="shop-toolbar" method="get" action="products.php" role="search">
        <div class="shop-toolbar__search">
          <label class="sr-only" for="shop-search">Search products</label>
          <input id="shop-search" type="search" name="q" placeholder="Search by name or description" value="<?= e($search) ?>">
        </div>
        <div class="shop-toolbar__sort">
          <label for="shop-sort">Sort by</label>
          <select id="shop-sort" name="sort">
            <option value="default"<?= $sort === 'default' ? ' selected' : '' ?>>Featured</option>
            <option value="price-asc"<?= $sort === 'price-asc' ? ' selected' : '' ?>>Price: low to high</option>
            <option value="price-desc"<?= $sort === 'price-desc' ? ' selected' : '' ?>>Price: high to low</option>
            <option value="name"<?= $sort === 'name' ? ' selected' : '' ?>>Name: A to Z</option>
          </select>
        </div>
        <input type="hidden" name="category" value="<?= e($category) ?>">
        <div class="chips" id="shop-categories" role="group" aria-label="Filter by category">
          <a class="chip" href="<?= e(chip_link('', $search, $sort)) ?>"<?= $category === '' ? ' aria-current="true"' : '' ?>>All</a>
          <?php foreach ($categoryNames as $name): ?>
            <a class="chip" href="<?= e(chip_link($name, $search, $sort)) ?>"<?= $category === $name ? ' aria-current="true"' : '' ?>><?= e($name) ?></a>
          <?php endforeach; ?>
        </div>
      </form>

      <div id="shop-area">
        <p class="shop-count" role="status"><?= e(plural(count($products), 'product')) ?></p>
        <?php if ($products): ?>
          <div class="product-grid">
            <?php foreach ($products as $product): ?>
              <?= product_card($product) ?>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <h2>No products found</h2>
            <p>Try a different search or browse every category.</p>
            <a class="btn btn--outline" href="products.php">Clear search and filters</a>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php show_flash_toast(); ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
