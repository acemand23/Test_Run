<?php
// Thin SMTP mail sender over the vendored PHPMailer (lib/PHPMailer).
// Config (host/port/user/pass/from) comes from config.php — see config.sample.php.
// Kept to a PHP 7.x baseline (no typed properties / promotion / union types),
// matching the production host.
declare(strict_types=1);

require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

final class TBD_Mailer {
    private $host;
    private $port;
    private $user;
    private $pass;
    private $fromEmail;
    private $fromName;

    public function __construct($host, $port, $user, $pass, $fromEmail, $fromName) {
        $this->host      = (string) $host;
        $this->port      = (int) $port;
        $this->user      = (string) $user;
        $this->pass      = (string) $pass;
        $this->fromEmail = (string) $fromEmail;
        $this->fromName  = (string) $fromName;
    }

    public static function fromConfig(array $cfg): self {
        return new self(
            isset($cfg['smtp_host']) ? $cfg['smtp_host'] : '',
            isset($cfg['smtp_port']) ? $cfg['smtp_port'] : 587,
            isset($cfg['smtp_user']) ? $cfg['smtp_user'] : '',
            isset($cfg['smtp_pass']) ? $cfg['smtp_pass'] : '',
            isset($cfg['mail_from']) ? $cfg['mail_from'] : '',
            isset($cfg['mail_from_name']) ? $cfg['mail_from_name'] : 'The Big Draw'
        );
    }

    /** True only when enough config is present to attempt a send. */
    public function isConfigured(): bool {
        return $this->host !== '' && $this->user !== '' && $this->pass !== ''
            && $this->pass !== 'CHANGE_ME' && $this->fromEmail !== '';
    }

    /** Send one HTML+text email. Throws PHPMailer\PHPMailer\Exception on failure. */
    public function send($to, $subject, $html, $text, $replyTo = '') {
        $m = new PHPMailer(true);
        $m->isSMTP();
        $m->CharSet    = PHPMailer::CHARSET_UTF8;
        $m->Host       = $this->host;
        $m->Port       = $this->port;
        $m->SMTPAuth   = true;
        $m->Username   = $this->user;
        $m->Password   = $this->pass;
        $m->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $m->setFrom($this->fromEmail, $this->fromName);
        $m->addAddress($to);
        if ($replyTo !== '' && $replyTo !== null) {
            $m->addReplyTo($replyTo);
        }
        $m->isHTML(true);
        $m->Subject = $subject;
        $m->Body    = $html;
        $m->AltBody = $text;
        $m->send();
    }
}
