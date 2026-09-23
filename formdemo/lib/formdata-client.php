<?php
/**
 * Form Data client. Drop this file into any site.
 * MUST stay PHP 7.0-compatible: no arrow functions, no ??=, no typed properties.
 */

// This file ships to third-party sites (WordPress especially) where a theme's
// functions.php, a plugin, and a page template can each independently require
// it. A single file-level guard protects every function below — including any
// added later — from a fatal "Cannot redeclare" on a second include, which
// would take down the entire host page, not just the form. This replaces
// per-function function_exists() wrappers, which only protected some of the
// functions in this file and gave false reassurance about the rest.
//
// NOTE: the guard body below intentionally wraps every declaration inside
// this `if`, rather than using an early `if (defined(...)) { return; }`
// followed by unconditional declarations. PHP performs compile-time ("early")
// binding for function declarations that are not themselves nested inside a
// conditional structure, regardless of any preceding return — so an early
// return does NOT stop a second require from fatally redeclaring functions
// that are declared unconditionally later in the same file. Nesting the
// declarations inside the `if` forces runtime ("late") binding, which is what
// actually skips them on a second include. Verified with a minimal repro
// before relying on it here.
if (!defined('FORMDATA_CLIENT')) {
define('FORMDATA_CLIENT', '1.0');

function formdata_client_secret($key) {
    if (!preg_match('/^fd_[0-9a-f]{8}_([0-9a-f]{64})$/', (string)$key, $m)) { return null; }
    return $m[1];
}

/** Hidden inputs every Form Data form must include. */
function formdata_fields(array $opts) {
    $secret = formdata_client_secret($opts['key']);
    if ($secret === null) { return ''; }
    $now  = isset($opts['now']) ? (int)$opts['now'] : time();
    $sig  = hash_hmac('sha256', (string)$now, hash('sha256', $secret));
    // The honeypot is deliberately named fd_hp — never "website"/"phone"/"company",
    // which password managers and browser autofill will happily fill in, turning
    // real people into spam. Off-screen rather than type="hidden" so that naive
    // bots still see a fillable input.
    $hp = '<div style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden" aria-hidden="true">'
        . '<label>Leave this field empty'
        . '<input type="text" name="fd_hp" value="" autocomplete="off" tabindex="-1">'
        . '</label></div>';
    return $hp
        . '<input type="hidden" name="_ts" value="' . $now . '">'
        . '<input type="hidden" name="_sig" value="' . $sig . '">';
}

function formdata_client_http($url, array $data, $timeout) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, (int)$timeout);
    // Identify our traffic explicitly rather than relying on cURL's default
    // (empty) User-Agent. Some hosts run a WAF (e.g. ModSecurity) that blocks
    // a literal "curl/..." User-Agent outright; a stable, named one is both
    // more reliable and more honest in the host's logs.
    curl_setopt($ch, CURLOPT_USERAGENT, 'FormData/1.0 (+https://forms.anthonyduke.com)');
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false || $code === 0) { return null; }
    return array('code' => $code, 'body' => $body);
}

function formdata_client_mail($to, $subject, $body, $headers) {
    return @mail($to, $subject, $body, $headers);
}

/**
 * Send a submission. Never throws, never prints.
 * Returns array(ok, id, via, error).
 */
function formdata_submit(array $opts) {
    $http    = isset($opts['http']) ? $opts['http'] : 'formdata_client_http';
    $mailer  = isset($opts['mailer']) ? $opts['mailer'] : 'formdata_client_mail';
    $timeout = isset($opts['timeout']) ? (int)$opts['timeout'] : 10;
    $fields  = isset($opts['fields']) ? $opts['fields'] : array();

    $data = $fields;
    $data['_key'] = $opts['key'];
    if (isset($opts['captcha'])) { $data['cf-turnstile-response'] = $opts['captcha']; }

    $res = null;
    try {
        $res = call_user_func($http, $opts['endpoint'], $data, $timeout);
    } catch (Throwable $e) {
        $res = null;
    }

    // Default reason for the fallback path when we never got a usable response.
    $unreachableReason = 'api_unreachable';

    if ($res !== null) {
        $decoded = json_decode($res['body'], true);
        if (is_array($decoded) && array_key_exists('ok', $decoded)) {
            if (!empty($decoded['ok'])) {
                return array('ok' => true, 'id' => isset($decoded['id']) ? (int)$decoded['id'] : null,
                             'via' => 'api', 'error' => null);
            }
            // The API answered and said no (spam, bad key, oversized). That is a real
            // verdict, not an outage — emailing it would defeat the filtering.
            $err = isset($decoded['error']) ? $decoded['error'] : 'rejected';
            return array('ok' => false, 'id' => null, 'via' => 'api', 'error' => $err);
        }
        // A response came back, but it does not carry our API's JSON contract —
        // a WAF, proxy, or error page answered instead (e.g. ModSecurity's HTML
        // 406 page for a request it disliked). We never got a real verdict from
        // our API, so this must be treated exactly like a transport failure, not
        // a spam rejection — otherwise a WAF false positive silently drops a
        // real lead, which is the exact failure this whole service exists to
        // prevent.
        $unreachableReason = 'api_bad_response';
    }

    // Transport failure (or an unrecognizable response): the API is unreachable.
    // Fall back to email so no lead is lost.
    $to = isset($opts['fallback_to']) ? $opts['fallback_to'] : '';
    if ($to === '') {
        return array('ok' => false, 'id' => null, 'via' => 'api', 'error' => $unreachableReason);
    }
    $lines = array();
    foreach ($fields as $k => $v) {
        $lines[] = $k . ': ' . (is_array($v) ? implode('; ', $v) : $v);
    }
    $from    = isset($opts['fallback_from']) ? $opts['fallback_from'] : $to;
    $subject = 'FORMDATA FALLBACK — API unreachable — ' . (isset($opts['form_name']) ? $opts['form_name'] : $opts['endpoint']);
    $body    = "The Form Data API could not be reached, so this submission is being\n"
             . "emailed instead. Paste it into the admin and check the service.\n\n"
             . implode("\n", $lines) . "\n";
    $headers = 'From: ' . $from . "\r\nContent-Type: text/plain; charset=UTF-8\r\n";

    $sent = false;
    try {
        $sent = (bool)call_user_func($mailer, $to, $subject, $body, $headers);
    } catch (Throwable $e) {
        $sent = false;
    }
    if ($sent) {
        return array('ok' => true, 'id' => null, 'via' => 'fallback', 'error' => $unreachableReason);
    }
    return array('ok' => false, 'id' => null, 'via' => 'fallback', 'error' => $unreachableReason . '_and_mail_failed');
}

} // end FORMDATA_CLIENT guard
