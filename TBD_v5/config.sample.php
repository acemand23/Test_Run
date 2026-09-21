<?php
// Copy this file to config.php (which is gitignored) and fill in the real
// SMTP password. NEVER commit config.php — the password is a server-only secret.
//
// The volunteer form (volunteer.php) reads this to send two emails through the
// tbdvolleyball.com mailbox: a signup notice to `volunteer_to`, and a
// confirmation to the volunteer. If config.php is missing or still has the
// placeholder password, the form falls back to a plain mailto: link.
return [
    // SMTP relay for the tbdvolleyball.com mailbox. Confirm host/port with the
    // email provider (cPanel/webmail hosts are usually mail.<domain> : 587 STARTTLS).
    'smtp_host'      => 'mail.tbdvolleyball.com',
    'smtp_port'      => 587,
    'smtp_user'      => 'questions@tbdvolleyball.com',
    'smtp_pass'      => 'CHANGE_ME',              // <-- real password, config.php only

    // What volunteers see as the sender.
    'mail_from'      => 'questions@tbdvolleyball.com',
    'mail_from_name' => 'The Big Draw',

    // Where volunteer signups are delivered (your inbox). Comma-separate for several.
    'volunteer_to'   => 'questions@tbdvolleyball.com',
];
