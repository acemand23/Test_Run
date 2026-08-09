<?php
declare(strict_types=1);

/** Registration, login, tokens, and the "who is calling" check. */
final class Auth
{
    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_DEFAULT);
    }

    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /** Create a login token row for a user and return the token string. */
    public static function issueToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32)); // 64 hex chars, matches CHAR(64)
        $ttl   = (int)Config::get('token_ttl_days', 90);
        Database::insert(
            'INSERT INTO auth_tokens (user_id, token, expires_at)
             VALUES (:uid, :tok, DATE_ADD(NOW(), INTERVAL :ttl DAY))',
            ['uid' => $userId, 'tok' => $token, 'ttl' => $ttl]
        );
        return $token;
    }

    /** Read the bearer token from the Authorization header. */
    public static function bearer(): ?string
    {
        $hdr = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] // some cPanel/Apache setups
            ?? '';
        if (preg_match('/Bearer\s+([0-9a-f]{64})/i', $hdr, $m)) {
            return strtolower($m[1]);
        }
        return null;
    }

    /**
     * Resolve the current user from the bearer token, or send 401.
     * Returns the user row: ['id','name','email',...].
     */
    public static function requireUser(): array
    {
        $token = self::bearer();
        if ($token === null) {
            Http::error(401, 'no_token', 'Missing or malformed Authorization header.');
        }
        $row = Database::one(
            'SELECT u.id, u.name, u.email
               FROM auth_tokens t
               JOIN users u ON u.id = t.user_id
              WHERE t.token = :tok AND t.expires_at > NOW()',
            ['tok' => $token]
        );
        if ($row === null) {
            Http::error(401, 'invalid_token', 'Session expired or invalid. Please log in again.');
        }
        $row['id'] = (int)$row['id'];
        return $row;
    }

    public static function logout(): void
    {
        $token = self::bearer();
        if ($token !== null) {
            Database::exec('DELETE FROM auth_tokens WHERE token = :tok', ['tok' => $token]);
        }
    }
}
