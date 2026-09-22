<?php
// OPTIONAL config. The volunteer form (volunteer.php) works WITHOUT this file:
// by default it sends through the host's local mail() (the website and the
// tbdvolleyball.com mailbox are on the same server), and uses questions@ as both
// the sender and the signup recipient.
//
// Create config.php (copy this file) ONLY to change those addresses or to force
// authenticated SMTP instead of local mail(). config.php is gitignored — never
// commit it, since smtp_pass is a secret. Any key you omit falls back to the
// built-in default in volunteer.php.
return [
    // Where volunteer signups are delivered. Comma-separate for several.
    'volunteer_to'   => 'questions@tbdvolleyball.com',

    // What volunteers see as the sender.
    'mail_from'      => 'questions@tbdvolleyball.com',
    'mail_from_name' => 'The Big Draw',

    // --- Authenticated SMTP (optional) ---------------------------------------
    // Uncomment and fill these to send via SMTP instead of local mail(). Leave
    // them out (or leave smtp_pass as CHANGE_ME) to keep using local mail().
    // 'smtp_host' => 'localhost',
    // 'smtp_port' => 587,
    // 'smtp_user' => 'questions@tbdvolleyball.com',
    // 'smtp_pass' => 'CHANGE_ME',
];
