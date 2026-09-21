<?php
// Thin SMTP mail sender over the vendored PHPMailer (lib/PHPMailer).
// Config (host/port/user/pass/from) comes from config.php — see config.sample.php.
declare(strict_types=1);

require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

final class TBD_Mailer {
    public function __construct(
        private string $host,
        private int $port,
        private string $user,
        private string $pass,
        private string $fromEmail,
        private string $fromName
    ) {}

    public static function fromConfig(array $cfg): self {
        return new self(
            (string)($cfg['smtp_host'] ?? ''),
            (int)   ($cfg['smtp_port'] ?? 587),
            (string)($cfg['smtp_user'] ?? ''),
            (string)($cfg['smtp_pass'] ?? ''),
            (string)($cfg['mail_from'] ?? ''),
            (string)($cfg['mail_from_name'] ?? 'The Big Draw'),
        );
    }

    /** True only when enough config is present to attempt a send. */
    public function isConfigured(): bool {
        return $this->host !== '' && $this->user !== '' && $this->pass !== ''
            && $this->pass !== 'CHANGE_ME' && $this->fromEmail !== '';
    }

    /** Send one HTML+text email. Throws PHPMailer\PHPMailer\Exception on failure. */
    public function send(string $to, string $subject, string $html, string $text, ?string $replyTo = null): void {
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
        if ($replyTo !== null && $replyTo !== '') {
            $m->addReplyTo($replyTo);
        }
        $m->isHTML(true);
        $m->Subject = $subject;
        $m->Body    = $html;
        $m->AltBody = $text;
        $m->send();
    }
}
