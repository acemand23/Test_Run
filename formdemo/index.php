<?php
/**
 * Form Data — validation page.
 *
 * A deliberately generic form used to prove the whole pipeline end to end:
 * browser -> this page -> forms.anthonyduke.com API -> database -> data-free
 * notification email -> admin.
 *
 * Kept PHP 7.2-compatible: digaball.com runs ea-php72.
 */
require_once __DIR__ . '/lib/formdata-client.php';

$cfgFile = is_file(__DIR__ . '/config.php') ? __DIR__ . '/config.php' : __DIR__ . '/config.sample.php';
$cfg = require $cfgFile;

$sent  = false;
$via   = '';
$error = '';
$old   = array('name' => '', 'email' => '', 'message' => '', 'interest' => array());

$INTERESTS = array('Friday AM', 'Friday PM', 'Saturday AM', 'Saturday PM', 'Sunday');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']     = isset($_POST['name']) ? trim($_POST['name']) : '';
    $old['email']    = isset($_POST['email']) ? trim($_POST['email']) : '';
    $old['message']  = isset($_POST['message']) ? trim($_POST['message']) : '';
    $old['interest'] = (isset($_POST['interest']) && is_array($_POST['interest'])) ? $_POST['interest'] : array();

    // This page does its own validation first; the service is not a substitute
    // for telling a visitor they forgot their email address.
    if ($old['name'] === '') {
        $error = 'Please enter your name.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $res = formdata_submit(array(
            'endpoint'      => $cfg['formdata_endpoint'],
            'key'           => $cfg['formdata_key'],
            'fields'        => $_POST,
            'captcha'       => isset($_POST['cf-turnstile-response']) ? $_POST['cf-turnstile-response'] : '',
            'form_name'     => 'Validation Test Form',
            'fallback_to'   => $cfg['fallback_to'],
            'fallback_from' => $cfg['fallback_from'],
        ));

        if ($res['ok']) {
            $sent = true;
            $via  = $res['via'];
        } else {
            // Deliberately neutral: telling a visitor "you look like a bot"
            // only teaches spammers what tripped the filter.
            $error = 'Sorry — we could not submit that. Please try again.';
        }
    }
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function checked_if($needle, array $hay) {
    return in_array($needle, $hay, true) ? ' checked' : '';
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Form Data — Validation Page</title>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<style>
  :root { --ink:#1b2430; --muted:#5d6b7a; --line:#dfe5ec; --accent:#2f6fed; --bg:#f6f8fb; }
  * { box-sizing: border-box; }
  body { margin:0; background:var(--bg); color:var(--ink);
         font:16px/1.55 -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif; }
  .wrap { max-width: 38rem; margin: 0 auto; padding: 2rem 1rem 4rem; }
  header { margin-bottom: 1.5rem; }
  h1 { font-size: 1.5rem; margin: 0 0 .35rem; letter-spacing: -.01em; }
  .sub { color: var(--muted); font-size: .95rem; margin: 0; }
  .card { background:#fff; border:1px solid var(--line); border-radius:12px; padding:1.5rem; }
  label { display:block; font-weight:600; font-size:.9rem; margin-bottom:.35rem; }
  input[type=text], input[type=email], textarea {
    width:100%; padding:.6rem .7rem; border:1px solid var(--line); border-radius:8px;
    font:inherit; color:inherit; background:#fff; }
  input:focus, textarea:focus { outline:2px solid var(--accent); outline-offset:1px; border-color:transparent; }
  textarea { min-height:6.5rem; resize:vertical; }
  .field { margin-bottom:1.1rem; }
  .checks { display:grid; grid-template-columns:repeat(auto-fit,minmax(9rem,1fr)); gap:.4rem .9rem; }
  .checks label { font-weight:400; display:flex; align-items:center; gap:.45rem; margin:0; }
  .checks input { margin:0; }
  button { background:var(--accent); color:#fff; border:0; border-radius:8px;
           padding:.7rem 1.3rem; font:inherit; font-weight:600; cursor:pointer; }
  button:hover { filter:brightness(1.07); }
  .banner { border-radius:10px; padding:.85rem 1rem; margin-bottom:1.25rem; font-size:.95rem; }
  .ok   { background:#e7f6ec; border:1px solid #b6e0c4; color:#18603a; }
  .bad  { background:#fdecec; border:1px solid #f3c2c2; color:#8a1f1f; }
  .note { color:var(--muted); font-size:.85rem; margin-top:1.25rem; }
  .note code { background:#eef2f7; padding:.1rem .3rem; border-radius:4px; }
  .req { color:#b23; font-weight:400; }
</style>
</head>
<body>
<div class="wrap">

  <header>
    <h1>Form Data — Validation Page</h1>
    <p class="sub">A generic test form. Submissions are stored in the central database, not emailed.</p>
  </header>

<?php if ($sent) { ?>
  <div class="banner ok">
    <strong>Thanks — that went through.</strong><br>
    It is stored in the database and a notification has been sent.
    <?php if ($via === 'fallback') { ?>
      <br><em>Note: the API was unreachable, so this was delivered by emergency email fallback instead.</em>
    <?php } ?>
  </div>
<?php } ?>

<?php if ($error !== '') { ?>
  <div class="banner bad"><?php echo e($error); ?></div>
<?php } ?>

  <div class="card">
    <form method="post" action="">
      <?php echo formdata_fields(array('key' => $cfg['formdata_key'])); ?>

      <div class="field">
        <label for="name">Name <span class="req">*</span></label>
        <input type="text" id="name" name="name" required value="<?php echo e($old['name']); ?>">
      </div>

      <div class="field">
        <label for="email">Email <span class="req">*</span></label>
        <input type="email" id="email" name="email" required value="<?php echo e($old['email']); ?>">
      </div>

      <div class="field">
        <label>Which sessions interest you?</label>
        <div class="checks">
          <?php foreach ($INTERESTS as $opt) { ?>
            <label>
              <input type="checkbox" name="interest[]" value="<?php echo e($opt); ?>"<?php echo checked_if($opt, $old['interest']); ?>>
              <?php echo e($opt); ?>
            </label>
          <?php } ?>
        </div>
      </div>

      <div class="field">
        <label for="message">Anything else?</label>
        <textarea id="message" name="message"><?php echo e($old['message']); ?></textarea>
      </div>

      <div class="field">
        <div class="cf-turnstile" data-sitekey="<?php echo e($cfg['turnstile_sitekey']); ?>"></div>
      </div>

      <button type="submit">Submit</button>
    </form>
  </div>

  <p class="note">
    The checkbox group posts as <code>interest[]</code> — an array — which is the
    case most likely to break a form-intake pipeline, so it is here on purpose.
    The message box is free text, useful for checking that apostrophes, accents
    and HTML-looking content survive the round trip intact.
  </p>

</div>
</body>
</html>
