<?php
declare(strict_types=1);

final class AuthController
{
    public static function register(): void
    {
        $b     = Http::body();
        $name  = Http::require($b, 'name');
        $email = strtolower(Http::require($b, 'email'));
        $pass  = Http::require($b, 'password');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Http::error(422, 'bad_email', 'That does not look like a valid email address.');
        }
        if (strlen($pass) < 6) {
            Http::error(422, 'weak_password', 'Password must be at least 6 characters.');
        }

        $exists = Database::one('SELECT id FROM users WHERE email = :e', ['e' => $email]);
        if ($exists !== null) {
            Http::error(409, 'email_taken', 'An account with that email already exists.');
        }

        $id = Database::insert(
            'INSERT INTO users (name, email, password_hash) VALUES (:n, :e, :p)',
            ['n' => $name, 'e' => $email, 'p' => Auth::hashPassword($pass)]
        );
        $token = Auth::issueToken($id);
        Http::ok([
            'token' => $token,
            'user'  => ['id' => $id, 'name' => $name, 'email' => $email],
        ]);
    }

    public static function login(): void
    {
        $b     = Http::body();
        $email = strtolower(Http::require($b, 'email'));
        $pass  = Http::require($b, 'password');

        $user = Database::one('SELECT * FROM users WHERE email = :e', ['e' => $email]);
        if ($user === null || !Auth::verifyPassword($pass, $user['password_hash'])) {
            Http::error(401, 'bad_credentials', 'Wrong email or password.');
        }
        $token = Auth::issueToken((int)$user['id']);
        Http::ok([
            'token' => $token,
            'user'  => ['id' => (int)$user['id'], 'name' => $user['name'], 'email' => $user['email']],
        ]);
    }

    public static function me(): void
    {
        $user = Auth::requireUser();
        Http::ok(['user' => $user]);
    }

    public static function logout(): void
    {
        Auth::requireUser();
        Auth::logout();
        Http::ok(['loggedOut' => true]);
    }
}
