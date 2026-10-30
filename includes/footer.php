<?php
// Optional: $pageScript, a script in assets/js/pages/ for this page.
?>
  <footer class="site-footer">
    <div class="container">
      <div class="site-footer__grid">
        <div>
          <a class="logo" href="<?= e(url('index.php')) ?>"><?= icon('logo') ?><span>FocalPoint</span></a>
          <p>Cameras, lenses and studio gear for photographers in Kigali and across Rwanda.</p>
        </div>
        <div>
          <h3>Shop</h3>
          <ul>
            <?php foreach (get_categories() as $category): ?>
              <li><a href="<?= e(category_link($category['name'])) ?>"><?= e($category['name']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div>
          <h3>Visit</h3>
          <ul>
            <li><a href="<?= e(url('about.php')) ?>">About and contact</a></li>
            <li>Kigali, Rwanda</li>
            <li>Mon to Sat, 8am to 6pm</li>
          </ul>
        </div>
      </div>
      <div class="site-footer__bottom">
        <p>&copy; 2026 FocalPoint. Web Technology CAT project, Reg. No. 27578/2024.</p>
      </div>
    </div>
  </footer>

  <script src="<?= e(url('assets/js/main.js')) ?>"></script>
  <?php if (!empty($pageScript)): ?>
    <script src="<?= e(url('assets/js/pages/' . $pageScript)) ?>"></script>
  <?php endif; ?>
</body>
</html>
