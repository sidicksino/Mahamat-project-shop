<?php
require __DIR__ . '/includes/bootstrap.php';

const FEATURED_COUNT = 4;

$categories = get_categories();
$inStock = array_filter(get_all_products(), function (array $product): bool {
    return (int) $product['stock'] > 0;
});

// One in-stock product per category, then topped up if a category is empty
$featured = [];
foreach ($categories as $category) {
    foreach ($inStock as $product) {
        if ((int) $product['category_id'] === (int) $category['id']) {
            $featured[$product['id']] = $product;
            break;
        }
    }
}
foreach ($inStock as $product) {
    if (count($featured) >= FEATURED_COUNT) {
        break;
    }
    $featured[$product['id']] = $product;
}
$featured = array_slice($featured, 0, FEATURED_COUNT);

$page = 'home';
$title = 'Cameras, Lenses and Studio Gear';
require __DIR__ . '/includes/header.php';
?>
  <main id="main-content">
    <section class="hero">
      <img class="hero__image" src="assets/images/banners/hero.jpg" alt="" fetchpriority="high">
      <div class="container hero__content">
        <p class="hero__specs">f/2.8 &middot; 1/250s &middot; ISO 200</p>
        <h1>Gear for the shot you came for.</h1>
        <p class="hero__lead">Cameras, lenses and studio equipment for photographers in Kigali and across Rwanda.</p>
        <div class="hero__actions">
          <a class="btn btn--accent" href="products.php">Shop all gear</a>
          <a class="btn btn--outline" href="about.php">About us</a>
        </div>
      </div>
    </section>

    <section class="section container" aria-labelledby="categories-heading">
      <div class="section-head">
        <h2 id="categories-heading">Shop by category</h2>
      </div>
      <div class="category-grid">
        <?php foreach ($categories as $category): ?>
          <a class="category-tile" href="<?= e(category_link($category['name'])) ?>">
            <img src="<?= e(url($category['image'])) ?>" alt="" loading="lazy">
            <span class="category-tile__label">
              <strong><?= e($category['name']) ?></strong>
              <span><?= e(plural((int) $category['product_count'], 'product')) ?></span>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="section section--tinted" aria-labelledby="featured-heading">
      <div class="container">
        <div class="section-head">
          <h2 id="featured-heading">Featured gear</h2>
          <a class="section-head__link" href="products.php">View all products</a>
        </div>
        <div class="product-grid">
          <?php if ($featured): ?>
            <?php foreach ($featured as $product): ?>
              <?= product_card($product) ?>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="empty-state">New gear is on its way. Please check back soon.</p>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="section container" aria-labelledby="why-heading">
      <div class="section-head">
        <h2 id="why-heading">Why buy from FocalPoint</h2>
      </div>
      <ul class="why-grid">
        <li>
          <span class="why-grid__number">01</span>
          <h3>Tested before it reaches you</h3>
          <p>Every camera and lens is checked in store, so it works from the first frame.</p>
        </li>
        <li>
          <span class="why-grid__number">02</span>
          <h3>Advice from photographers</h3>
          <p>Tell us what you shoot and your budget, and we will help you choose the right kit.</p>
        </li>
        <li>
          <span class="why-grid__number">03</span>
          <h3>Based in Kigali</h3>
          <p>Visit our shop to try gear in your hands before you decide.</p>
        </li>
      </ul>
    </section>
  </main>

  <?php show_flash_toast(); ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
