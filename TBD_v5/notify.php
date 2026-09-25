<?php
  // "Get notified" signup — captures interest via the central form-intake service
  // (forms.anthonyduke.com): the submission is stored there and a data-free
  // notification is emailed to the organizers, who read it in the login-gated
  // admin. No signup data lives in an inbox.
  //
  // Kept PHP 7.2-compatible (beta runs ea-php72): no arrow fns, ??=, typed props.
  require_once __DIR__ . '/lib/formdata-client.php';

  // Built-in defaults so the page renders without config.php. The real form KEY
  // must be supplied via config.php on the server (gitignored) for live submits;
  // the Turnstile SITE key is public by design (rendered into the page).
  $defaults = [
      'formdata_endpoint' => 'https://forms.anthonyduke.com/api/submit.php',
      'formdata_key'      => 'CHANGE_ME',
      'turnstile_sitekey' => '0x4AAAAAAFBaWGqFaK_Pwt_F',
      'fallback_to'       => 'questions@tbdvolleyball.com',
      'fallback_from'     => 'questions@tbdvolleyball.com',
  ];
  $file_cfg = is_file(__DIR__ . '/config.php') ? require __DIR__ . '/config.php' : [];
  $cfg = (is_array($file_cfg) ? $file_cfg : []) + $defaults;

  $submitted = false;      // show the thank-you panel
  $errors    = [];         // field => message
  $error_msg = '';         // general (neutral) submit error
  $old       = ['name' => '', 'email' => ''];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $old['name']  = isset($_POST['name'])  ? trim($_POST['name'])  : '';
      $old['email'] = isset($_POST['email']) ? trim($_POST['email']) : '';

      // Our own validation first — the service is not a substitute for telling a
      // visitor they forgot their email.
      if ($old['name'] === '') {
          $errors['name'] = 'Please enter your name.';
      } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
          $errors['email'] = 'Please enter a valid email address.';
      } else {
          // Pass $_POST through so the service's timing (_ts/_sig) and honeypot
          // (fd_hp) fields reach it; the API strips those before storing.
          $res = formdata_submit([
              'endpoint'      => $cfg['formdata_endpoint'],
              'key'           => $cfg['formdata_key'],
              'fields'        => $_POST,
              'captcha'       => isset($_POST['cf-turnstile-response']) ? $_POST['cf-turnstile-response'] : '',
              'form_name'     => 'The Big Draw — Notify',
              'fallback_to'   => $cfg['fallback_to'],
              'fallback_from' => $cfg['fallback_from'],
          ]);
          if (!empty($res['ok'])) {
              $submitted = true;
          } else {
              // Deliberately neutral: naming what tripped the filter only helps spammers.
              $error_msg = 'Sorry — we could not add you just now. Please try again in a moment.';
          }
      }
  }

  $title = 'Get Notified — The Big Draw'; $active = 'register';
  include 'includes/header.php';
?>

<!-- Cloudflare Turnstile (loads the widget rendered below) -->
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

<section class="pagehead"><div class="col">
  <div class="eyebrow">Register · Competitive Division</div>
  <h1>Get Notified</h1>
  <p>Competitive Division registration opens <strong>Wednesday, October&nbsp;7, 2026 at 6:00&nbsp;PM CT</strong>. Leave your name and email and we'll let you know the moment it's live.</p>
</div></section>

<section class="block"><div class="col">
  <div class="contact-grid">
    <div class="cinfo">
      <h2 class="kicker">Already open</h2>
      <p class="muted">The <strong>Community Division</strong> (Co-Ed · Rec · 4v4 · 8:30&nbsp;AM–Noon) is open now — <a href="https://tbdvolley.vballmanager.com/org/tournament.php?slug=tbdvolley&amp;event_id=121" target="_blank" rel="noopener">register here</a> if you'd rather play the recreational bracket.</p>
      <p class="muted">Questions? Email <a href="mailto:questions@tbdvolleyball.com">questions@tbdvolleyball.com</a>.</p>
    </div>

<?php if ($submitted): ?>
    <div class="lead-form" id="notifyThanks">
      <h2 class="kicker" style="margin-top:0">You're on the list — thank you!</h2>
      <div class="form-success" style="display:block">We'll email you the moment Competitive Division registration opens.</div>
      <p class="muted" style="margin-bottom:0">See you on the sand at The Big Draw. 🏐</p>
    </div>
<?php else: ?>
    <form class="lead-form" id="notifyForm" method="post" action="notify.php" novalidate>
<?php if ($error_msg !== ''): ?>
      <div class="form-success" style="display:block;background:#fdecec;border-color:#f5c2c2;color:#a12626"><?= htmlspecialchars($error_msg) ?></div>
<?php endif; ?>
      <?= formdata_fields(['key' => $cfg['formdata_key']]); ?>

      <div class="field">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" placeholder="Your name" required value="<?= htmlspecialchars($old['name']) ?>">
<?php if (isset($errors['name'])): ?><span style="color:#a12626;font-size:13px;font-weight:600"><?= htmlspecialchars($errors['name']) ?></span><?php endif; ?>
      </div>
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required value="<?= htmlspecialchars($old['email']) ?>">
<?php if (isset($errors['email'])): ?><span style="color:#a12626;font-size:13px;font-weight:600"><?= htmlspecialchars($errors['email']) ?></span><?php endif; ?>
      </div>

      <input type="hidden" name="interest" value="Competitive Division — opens Oct 7, 2026">

      <div class="field">
        <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars($cfg['turnstile_sitekey']) ?>"></div>
      </div>

      <button type="submit" class="btn grad">Notify me →</button>
    </form>
<?php endif; ?>
  </div>
</div></section>

<?php include 'includes/footer.php'; ?>
