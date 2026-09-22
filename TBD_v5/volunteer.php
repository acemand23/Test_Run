<?php
  require __DIR__ . '/includes/volunteer_lib.php';
  // Built-in defaults so the form works with no config.php (sends via local mail()).
  // A config.php may override these and/or add smtp_* to use authenticated SMTP.
  $defaults = [
      'mail_from'      => 'questions@tbdvolleyball.com',
      'mail_from_name' => 'The Big Draw',
      'volunteer_to'   => 'questions@tbdvolleyball.com',
  ];
  $file_cfg = is_file(__DIR__ . '/config.php') ? require __DIR__ . '/config.php' : [];
  $cfg = (is_array($file_cfg) ? $file_cfg : []) + $defaults;
  $contact_email = $cfg['volunteer_to'];

  // Submission state (all false on a fresh GET).
  $submitted    = false;   // show the thank-you panel
  $errors       = [];      // field => message
  $fallback     = false;   // SMTP unavailable/failed → offer mailto instead
  $old          = ['name' => '', 'email' => '', 'phone' => '', 'notes' => '', 'roles' => [], 'times' => []];
  $mailto_href  = 'mailto:' . $contact_email;

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $prep = tbd_prepare_volunteer($_POST, $cfg);
      $old  = $prep['data'] + $old;

      if ($prep['is_bot']) {
          $submitted = true;                 // silently swallow bots, send nothing
      } elseif (!$prep['ok']) {
          $errors = $prep['errors'];
      } else {
          require __DIR__ . '/includes/mailer.php';
          $mailer = TBD_Mailer::fromConfig($cfg);
          if ($mailer->canSend()) {
              try {
                  $o = $prep['org'];
                  $mailer->send($o['to'], $o['subject'], $o['html'], $o['text'], $o['reply_to']);
                  $v = $prep['volunteer'];
                  $mailer->send($v['to'], $v['subject'], $v['html'], $v['text']);
                  $submitted = true;
              } catch (\Throwable $e) {
                  error_log('[volunteer] mail send failed: ' . $e->getMessage());
                  $fallback = true;
              }
          } else {
              $fallback = true;              // config.php missing / password not set
          }
          if ($fallback) {
              $mailto_href = 'mailto:' . $contact_email
                  . '?subject=' . rawurlencode($prep['org']['subject'])
                  . '&body='    . rawurlencode($prep['org']['text']);
          }
      }
  }

  $title = 'Get Involved — The Big Draw'; $active = 'involve';
  include 'includes/header.php';
?>

<section class="pagehead"><div class="col">
  <div class="eyebrow">Get Involved</div>
  <h1>Volunteer With Us</h1>
  <p>The Big Draw runs on volunteers. Tell us how you'd like to help and we'll be in touch with the details.</p>
</div></section>

<section class="block"><div class="col">
  <div class="contact-grid">
    <div class="cinfo">
      <h2 class="kicker">Why volunteer</h2>
      <p class="muted">Every hour you give supports Big Brothers Big Sisters of Central Texas — and it's a blast on the sand. No experience needed; we'll match you to a role that fits.</p>
      <p class="muted">Questions first? Email <a href="mailto:<?= htmlspecialchars($contact_email) ?>"><?= htmlspecialchars($contact_email) ?></a>.</p>
    </div>

<?php if ($submitted): ?>
    <div class="lead-form" id="volThanks">
      <h2 class="kicker" style="margin-top:0">You're in — thank you!</h2>
      <div class="form-success" style="display:block">Thank you! You'll receive an email with more information. We'll be in touch soon.</div>
      <p class="muted" style="margin-bottom:0">See you on the sand at The Big Draw. 🏐</p>
    </div>
<?php else: ?>
    <form class="lead-form" id="volForm" method="post" action="volunteer.php" novalidate>
<?php if ($fallback): ?>
      <div class="form-success" style="display:block;background:#fdecec;border-color:#f5c2c2;color:#a12626">
        We couldn't send that automatically just now. Please
        <a href="<?= htmlspecialchars($mailto_href) ?>" style="color:#a12626;text-decoration:underline">email us your details</a>
        — everything you entered is pre-filled — and we'll follow up.
      </div>
<?php endif; ?>
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
      <div class="field">
        <label for="phone">Phone <span class="opt">(optional)</span></label>
        <input type="tel" id="phone" name="phone" placeholder="(512) 555-0123" value="<?= htmlspecialchars($old['phone']) ?>">
      </div>

      <fieldset class="field checks">
        <legend>What would you like to help with?</legend>
<?php foreach (TBD_VOLUNTEER_ROLES as $role): ?>
        <label class="check"><input type="checkbox" name="roles[]" value="<?= htmlspecialchars($role) ?>"<?= in_array($role, $old['roles'], true) ? ' checked' : '' ?>><span><?= htmlspecialchars($role) ?></span></label>
<?php endforeach; ?>
      </fieldset>

      <fieldset class="field checks">
        <legend>When can you help? <span class="opt">(check any that work)</span></legend>
<?php foreach (TBD_VOLUNTEER_TIMES as $slot): ?>
        <label class="check"><input type="checkbox" name="times[]" value="<?= htmlspecialchars($slot) ?>"<?= in_array($slot, $old['times'], true) ? ' checked' : '' ?>><span><?= htmlspecialchars($slot) ?></span></label>
<?php endforeach; ?>
      </fieldset>

      <div class="field">
        <label for="notes">Anything else? <span class="opt">(group size, t-shirt size…)</span></label>
        <textarea id="notes" name="notes" rows="3" placeholder="Tell us anything that helps us plan"><?= htmlspecialchars($old['notes']) ?></textarea>
      </div>

      <!-- honeypot: hidden from people, tempting to bots -->
      <div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden">
        <label>Company <input type="text" name="company" tabindex="-1" autocomplete="off"></label>
      </div>

      <button type="submit" class="btn grad">Sign me up →</button>
    </form>
<?php endif; ?>
  </div>
</div></section>

<?php include 'includes/footer.php'; ?>
