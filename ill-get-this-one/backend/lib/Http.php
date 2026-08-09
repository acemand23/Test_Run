<?php
declare(strict_types=1);

/** Request parsing + JSON response helpers. */
final class Http
{
    /** Decode the JSON request body into an array. */
    public static function body(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        if ($raw === '') {
            return [];
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            self::error(400, 'bad_json', 'Request body must be valid JSON.');
        }
        return $data;
    }

    /** Require a field to be present and non-empty; returns its trimmed value. */
    public static function require(array $body, string $field): string
    {
        $val = isset($body[$field]) ? trim((string)$body[$field]) : '';
        if ($val === '') {
            self::error(422, 'missing_field', "Missing required field: {$field}");
        }
        return $val;
    }

    public static function json($data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function ok($data = []): never
    {
        self::json(['ok' => true, 'data' => $data]);
    }

    public static function error(int $status, string $code, string $message): never
    {
        self::json(['ok' => false, 'error' => $code, 'message' => $message], $status);
    }

    /** Emit CORS headers and short-circuit preflight requests. */
    public static function cors(): void
    {
        $origin = Config::get('cors_origin', '*');
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Vary: Origin');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
