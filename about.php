<?php
// About page and contact form. Messages are saved in the messages table.

require __DIR__ . '/includes/bootstrap.php';

$errors = [];
$formMessage = null;
$values = ['name' => '', 'email' => '', 'body' => ''];

if (is_post()) {
    foreach ($values as $field => $unused) {
        $values[$field] = post_text($field);
    }

    if (strlen($values['name']) < 2 || strlen($values['name']) > 100) {
        $errors['name'] = 'Enter your name.';
    }
    if (filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false || strlen($values['email']) > 150) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if (strlen($values['body']) < 10 || strlen($values['body']) > 1000) {
        $errors['body'] = 'Enter a message of 10 to 1000 characters.';
    }

    if (!csrf_is_valid()) {
        $formMessage = 'Your session expired. Please try again.';
    } elseif ($errors) {
        $formMessage = 'Please fix the highlighted fields and try again.';
    } else {
        $statement = db()->prepare('INSERT INTO messages (name, email, body) VALUES (?, ?, ?)');
        $statement->execute([$values['name'], $values['email'], $values['body']]);
        set_flash('Thanks, ' . $values['name'] . '. We will reply to ' . $values['email'] . ' soon.');
        redirect('about.php#contact');
    }
}

$page = 'about';
$title = 'About Us and Contact';
require __DIR__ . '/includes/header.php';
?>
  <main id="main-content">
    <section class="container about-intro">
      <div class="about-intro__text">
        <p class="eyebrow">About FocalPoint</p>
        <h1>Made for people who love making pictures.</h1>
        <p>FocalPoint is a camera and photography store based in Kigali. We stock the gear working photographers rely on, along with simple, affordable kit for anyone taking their first steps.</p>
        <p>From a first instant camera to a full studio lighting setup, we want you to leave with the right tool for the way you work, not just the most expensive one.</p>
        <a class="btn" href="products.php">Browse the shop</a>
      </div>
      <img class="about-intro__image" src="assets/images/banners/about.jpg" alt="Two photographers on a green hillside taking pictures of a city at sunrise">
    </section>

    <section class="section--tinted section" aria-labelledby="values-heading">
      <div class="container">
        <div class="section-head">
          <h2 id="values-heading">What we care about</h2>
        </div>
        <ul class="why-grid">
          <li>
            <span class="why-grid__number">01</span>
            <h3>Honest advice</h3>
            <p>We recommend what suits your work and your budget, even when that means a simpler camera.</p>
          </li>
          <li>
            <span class="why-grid__number">02</span>
            <h3>Gear you can trust</h3>
            <p>We choose every product for reliability, and check cameras and lenses before they leave the shop.</p>
          </li>
          <li>
            <span class="why-grid__number">03</span>
            <h3>A local community</h3>
            <p>We are here for Rwanda's photographers, from students to professionals.</p>
          </li>
        </ul>
      </div>
    </section>

    <section class="section container contact" id="contact" aria-labelledby="contact-heading">
      <div>
        <h2 id="contact-heading">Get in touch</h2>
        <p>Have a question about a product, or not sure what to buy? Send us a message.</p>
        <dl class="contact__details">
          <dt>Visit</dt>
          <dd>Kigali, Rwanda</dd>
          <dt>Hours</dt>
          <dd>Mon to Sat, 8am to 6pm</dd>
          <dt>Email</dt>
          <dd><a href="mailto:hello@focalpoint.example">hello@focalpoint.example</a></dd>
        </dl>
      </div>

      <div>
        <?php show_flash(); ?>
        <?php if ($formMessage !== null): ?>
          <?= alert($formMessage, 'error') ?>
        <?php endif; ?>

        <form class="contact__form" method="post" action="about.php#contact" novalidate>
          <?= csrf_field() ?>

          <div class="<?= field_class($errors, 'name') ?>">
            <label for="contact-name">Your name</label>
            <input id="contact-name" name="name" type="text" value="<?= e($values['name']) ?>" autocomplete="name" maxlength="100" aria-describedby="name-error">
            <?= field_error($errors, 'name') ?>
          </div>

          <div class="<?= field_class($errors, 'email') ?>">
            <label for="contact-email">Email address</label>
            <input id="contact-email" name="email" type="email" value="<?= e($values['email']) ?>" autocomplete="email" maxlength="150" aria-describedby="email-error">
            <?= field_error($errors, 'email') ?>
          </div>

          <div class="<?= field_class($errors, 'body') ?>">
            <label for="contact-body">Message</label>
            <textarea id="contact-body" name="body" rows="5" maxlength="1000" aria-describedby="body-error"><?= e($values['body']) ?></textarea>
            <?= field_error($errors, 'body') ?>
          </div>

          <button class="btn btn--accent" type="submit">Send message</button>
        </form>
      </div>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
