<?php
/**
 * Form Data demo config — TEMPLATE.
 *
 * Copy to config.php and fill in the real key. config.php is gitignored and
 * must never be committed: the key authenticates submissions to the central
 * service, so anyone holding it can post to this form.
 *
 * The Turnstile SITE key below is public by design — it is rendered into the
 * page HTML. Only the Turnstile SECRET is sensitive, and that lives solely on
 * forms.anthonyduke.com, never here.
 */
return array(
    'formdata_endpoint' => 'https://forms.anthonyduke.com/api/submit.php',
    'formdata_key'      => 'CHANGE_ME',
    'turnstile_sitekey' => '0x4AAAAAAFBaWGqFaK_Pwt_F',

    // Emergency fallback only. If the API is unreachable the client emails the
    // submission here rather than losing it, with an alarming subject line so a
    // fallback is a visible alarm and not a silent habit.
    'fallback_to'       => 'aduke@aduke.com',
    'fallback_from'     => 'forms@digaball.com',
);
