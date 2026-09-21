<?php
// Volunteer-signup logic for volunteer.php — pure functions, no I/O, unit-tested
// in tests/volunteer_test.php. The page wires these to the SMTP Mailer.
declare(strict_types=1);

// Whitelists: only these values are accepted from the form (guards the emails
// against arbitrary injected checkbox values). Keep in sync with volunteer.php.
const TBD_VOLUNTEER_ROLES = [
    'Pre-event work',
    'Morning-of setup',
    'Help with run of show',
    'Tear down',
    'Wherever you need me',
];
const TBD_VOLUNTEER_TIMES = [
    '7am–11am',
    '11am–3pm',
    '3pm–7pm',
];

/** Strip CR/LF so a value can't be smuggled into an email header. */
function tbd_header_safe(string $s): string {
    return trim(str_replace(["\r", "\n"], ' ', $s));
}

/** Keep only submitted values that are on the allow-list, de-duplicated. */
function tbd_clean_list($vals, array $allowed): array {
    if (!is_array($vals)) { $vals = [$vals]; }
    $out = [];
    foreach ($vals as $v) {
        $v = trim((string)$v);
        if ($v !== '' && in_array($v, $allowed, true) && !in_array($v, $out, true)) {
            $out[] = $v;
        }
    }
    return $out;
}

function tbd_first_name(string $name): string {
    $name = tbd_header_safe($name);
    $first = strtok($name, ' ');
    return $first !== false && $first !== '' ? $first : 'there';
}

/**
 * Validate a volunteer submission and, when valid, build the two emails.
 *
 * @return array{is_bot:bool, ok:bool, errors:array<string,string>, data:array,
 *               org:?array, volunteer:?array}
 */
function tbd_prepare_volunteer(array $post, array $cfg): array {
    $isBot = trim((string)($post['company'] ?? '')) !== ''; // honeypot

    $name  = trim((string)($post['name'] ?? ''));
    $email = trim((string)($post['email'] ?? ''));
    $phone = trim((string)($post['phone'] ?? ''));
    $notes = trim((string)($post['notes'] ?? ''));
    $roles = tbd_clean_list($post['roles'] ?? [], TBD_VOLUNTEER_ROLES);
    $times = tbd_clean_list($post['times'] ?? [], TBD_VOLUNTEER_TIMES);

    $errors = [];
    if ($name === '') {
        $errors['name'] = 'Please tell us your name.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    $data = compact('name', 'email', 'phone', 'roles', 'times', 'notes');

    $result = [
        'is_bot'    => $isBot,
        'ok'        => !$isBot && empty($errors),
        'errors'    => $errors,
        'data'      => $data,
        'org'       => null,
        'volunteer' => null,
    ];
    if (!$result['ok']) {
        return $result;
    }

    $result['org']       = tbd_org_email($data, $cfg);
    $result['volunteer'] = tbd_volunteer_email($data, $cfg);
    return $result;
}

/** Notification to the organizers, with Reply-To set to the volunteer. */
function tbd_org_email(array $d, array $cfg): array {
    $rolesTxt = $d['roles'] ? implode(', ', $d['roles']) : 'Not specified';
    $timesTxt = $d['times'] ? implode(', ', $d['times']) : 'Not specified';
    $phone    = $d['phone'] !== '' ? $d['phone'] : '—';
    $notes    = $d['notes'] !== '' ? $d['notes'] : '—';

    $subject = 'New volunteer signup — ' . tbd_header_safe($d['name']);

    $text = "New volunteer signup for The Big Draw\n\n"
        . "Name:  {$d['name']}\n"
        . "Email: {$d['email']}\n"
        . "Phone: {$phone}\n\n"
        . "Roles they'd help with:\n  {$rolesTxt}\n\n"
        . "Availability (time blocks):\n  {$timesTxt}\n\n"
        . "Notes:\n  {$notes}\n";

    $e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
    $html = "<h2 style='margin:0 0 12px'>New volunteer signup</h2>"
        . "<table cellpadding='6' style='border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px'>"
        . "<tr><td><b>Name</b></td><td>"  . $e($d['name'])  . "</td></tr>"
        . "<tr><td><b>Email</b></td><td>" . $e($d['email']) . "</td></tr>"
        . "<tr><td><b>Phone</b></td><td>" . $e($phone)      . "</td></tr>"
        . "<tr><td><b>Roles</b></td><td>" . $e($rolesTxt)   . "</td></tr>"
        . "<tr><td><b>Availability</b></td><td>" . $e($timesTxt) . "</td></tr>"
        . "<tr><td valign='top'><b>Notes</b></td><td>" . nl2br($e($notes)) . "</td></tr>"
        . "</table>";

    return [
        'to'       => $cfg['volunteer_to'],
        'reply_to' => $d['email'],
        'subject'  => $subject,
        'html'     => $html,
        'text'     => $text,
    ];
}

/** Friendly confirmation to the volunteer recapping what they signed up for. */
function tbd_volunteer_email(array $d, array $cfg): array {
    $rolesTxt = $d['roles'] ? implode(', ', $d['roles']) : 'Wherever we need a hand';
    $timesTxt = $d['times'] ? implode(', ', $d['times']) : 'Flexible / any time';
    $first    = tbd_first_name($d['name']);

    $subject = 'Thanks for volunteering with The Big Draw';

    $text = "Hi {$first},\n\n"
        . "Thank you for offering to help at The Big Draw! Here's what you signed up for:\n\n"
        . "Roles:        {$rolesTxt}\n"
        . "Availability: {$timesTxt}\n\n"
        . "We'll be in touch soon with the details.\n\n"
        . "— The Big Draw\n"
        . "Benefiting Big Brothers Big Sisters of Central Texas\n";

    $e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
    $html = "<p>Hi " . $e($first) . ",</p>"
        . "<p>Thank you for offering to help at <b>The Big Draw</b>! Here's what you signed up for:</p>"
        . "<table cellpadding='6' style='border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px'>"
        . "<tr><td><b>Roles</b></td><td>" . $e($rolesTxt) . "</td></tr>"
        . "<tr><td><b>Availability</b></td><td>" . $e($timesTxt) . "</td></tr>"
        . "</table>"
        . "<p><b>We'll be in touch soon</b> with the details.</p>"
        . "<p style='color:#6b6b6b'>— The Big Draw<br>Benefiting Big Brothers Big Sisters of Central Texas</p>";

    return [
        'to'      => $d['email'],
        'subject' => $subject,
        'html'    => $html,
        'text'    => $text,
    ];
}
