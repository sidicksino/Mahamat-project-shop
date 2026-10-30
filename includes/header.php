<?php
// Expects $page and $title. Optional: $headerSearch, the current search text.

$navLinks = [
    'home' => ['label' => 'Home', 'href' => 'index.php'],
    'products' => ['label' => 'Shop', 'href' => 'products.php'],
    'about' => ['label' => 'About', 'href' => 'about.php'],
    'admin' => ['label' => 'Admin', 'href' => 'admin/index.php'],
];
$activePage = $page === 'login' ? 'admin' : $page;
$cartCount = cart_count();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> | FocalPoint</title>
  <meta name="description" content="FocalPoint sells cameras, lenses and studio gear in Kigali, Rwanda.">
  <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='14' fill='%231c1a17'/%3E%3Ccircle cx='16' cy='16' r='5' fill='%23c23a24'/%3E%3C/svg%3E">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600..800&family=DM+Mono&family=DM+Sans:opsz,wght@9..40,400..700&display=swap" media="print" onload="this.media='all'">
  <link rel="stylesheet" href="<?= e(url('assets/css/base.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/layout.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/components.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/pages.css')) ?>">
</head>
<body>
  <a class="skip-link" href="#main-content">Skip to content</a>
  <header class="site-header">
    <div class="container site-header__inner">
      <a class="logo" href="<?= e(url('index.php')) ?>"><?= icon('logo') ?><span>FocalPoint</span></a>
      <nav class="site-nav" id="site-nav" aria-label="Main">
        <ul>
          <?php foreach ($navLinks as $key => $link): ?>
            <li><a class="site-nav__link<?= $key === $activePage ? ' is-active' : '' ?>" href="<?= e(url($link['href'])) ?>"<?= $key === $activePage ? ' aria-current="page"' : '' ?>><?= e($link['label']) ?></a></li>
          <?php endforeach; ?>
          <?php if (is_logged_in()): ?>
            <li>
              <form method="post" action="<?= e(url('logout.php')) ?>">
                <?= csrf_field() ?>
                <button class="site-nav__link site-nav__logout" type="submit">Log out</button>
              </form>
            </li>
          <?php endif; ?>
        </ul>
        <form class="site-search" action="<?= e(url('products.php')) ?>" method="get" role="search">
          <label class="sr-only" for="site-search-input">Search products</label>
          <input id="site-search-input" type="search" name="q" placeholder="Search cameras, lenses..." value="<?= e($headerSearch ?? '') ?>">
          <button type="submit" aria-label="Search"><?= icon('search') ?></button>
        </form>
      </nav>
      <div class="site-header__actions">
        <a class="icon-btn" href="<?= e(url('cart.php')) ?>" id="cart-link" aria-label="Cart, <?= e(plural($cartCount, 'item')) ?>"<?= $page === 'cart' ? ' aria-current="page"' : '' ?>>
          <?= icon('cart') ?>
          <span class="cart-count" id="cart-count"<?= $cartCount === 0 ? ' hidden' : '' ?>><?= $cartCount ?></span>
        </a>
        <button class="icon-btn menu-toggle" id="menu-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Open menu">
          <?= icon('menu') ?>
        </button>
      </div>
    </div>
  </header>
