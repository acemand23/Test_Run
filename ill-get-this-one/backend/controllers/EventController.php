<?php
declare(strict_types=1);

final class EventController
{
    /**
     * POST /groups/{id}/events
     * Body: {
     *   payer_id?:   int   (defaults to the caller — "I'll get this one!"),
     *   description: string,
     *   occurred_on: 'YYYY-MM-DD' (optional, defaults to today),
     *   shares:      [ {user_id:int, points:int}, ... ]
     * }
     */
    public static function create(int $gid): void
    {
        $user = Auth::requireUser();
        Groups::requireMember($gid, $user['id']);

        $b       = Http::body();
        $payerId = isset($b['payer_id']) ? (int)$b['payer_id'] : $user['id'];
        $desc    = trim((string)($b['description'] ?? ''));
        $date    = trim((string)($b['occurred_on'] ?? ''));
        $shares  = $b['shares'] ?? [];

        if ($date === '') {
            $date = date('Y-m-d');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            Http::error(422, 'bad_date', 'occurred_on must be YYYY-MM-DD.');
        }
        if (!is_array($shares) || count($shares) === 0) {
            Http::error(422, 'no_shares', 'Provide at least one attendee estimate.');
        }

        // Everyone referenced must belong to the group.
        $memberIds = array_map(static fn($m) => $m['id'], Groups::members($gid));
        $memberSet = array_flip($memberIds);
        if (!isset($memberSet[$payerId])) {
            Http::error(422, 'payer_not_member', 'The payer is not a member of this group.');
        }

        // Normalise shares: last value wins per user, must be non-negative int.
        $clean = [];
        $total = 0;
        foreach ($shares as $s) {
            $uid = (int)($s['user_id'] ?? 0);
            $pts = (int)($s['points'] ?? 0);
            if (!isset($memberSet[$uid])) {
                Http::error(422, 'share_not_member', "User {$uid} is not a member of this group.");
            }
            if ($pts < 0) {
                Http::error(422, 'bad_points', 'Points cannot be negative.');
            }
            $clean[$uid] = $pts;
        }
        $total = array_sum($clean);

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $eid = Database::insert(
                'INSERT INTO events (group_id, payer_id, description, occurred_on, total_points, created_by)
                 VALUES (:g, :p, :d, :o, :t, :c)',
                ['g' => $gid, 'p' => $payerId, 'd' => $desc, 'o' => $date,
                 't' => $total, 'c' => $user['id']]
            );
            $stmt = $pdo->prepare(
                'INSERT INTO event_shares (event_id, user_id, points) VALUES (:e, :u, :p)'
            );
            foreach ($clean as $uid => $pts) {
                $stmt->execute(['e' => $eid, 'u' => $uid, 'p' => $pts]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        Http::ok(['event' => [
            'id' => $eid, 'group_id' => $gid, 'payer_id' => $payerId,
            'description' => $desc, 'occurred_on' => $date, 'total_points' => $total,
        ]]);
    }

    /** GET /groups/{id}/events — most recent first. */
    public static function index(int $gid): void
    {
        $user = Auth::requireUser();
        Groups::requireMember($gid, $user['id']);

        $events = Database::all(
            'SELECT e.id, e.payer_id, u.name AS payer_name, e.description,
                    e.occurred_on, e.total_points, e.created_at
               FROM events e
               JOIN users u ON u.id = e.payer_id
              WHERE e.group_id = :g
              ORDER BY e.occurred_on DESC, e.id DESC',
            ['g' => $gid]
        );
        if (!$events) {
            Http::ok(['events' => []]);
        }

        $ids = array_map(static fn($e) => (int)$e['id'], $events);
        $in  = implode(',', array_fill(0, count($ids), '?'));
        $shareRows = Database::all(
            "SELECT s.event_id, s.user_id, u.name, s.points
               FROM event_shares s JOIN users u ON u.id = s.user_id
              WHERE s.event_id IN ($in)",
            $ids
        );
        $byEvent = [];
        foreach ($shareRows as $s) {
            $byEvent[(int)$s['event_id']][] = [
                'user_id' => (int)$s['user_id'], 'name' => $s['name'], 'points' => (int)$s['points'],
            ];
        }

        $out = [];
        foreach ($events as $e) {
            $eid = (int)$e['id'];
            $out[] = [
                'id'           => $eid,
                'payer_id'     => (int)$e['payer_id'],
                'payer_name'   => $e['payer_name'],
                'description'  => $e['description'],
                'occurred_on'  => $e['occurred_on'],
                'total_points' => (int)$e['total_points'],
                'shares'       => $byEvent[$eid] ?? [],
            ];
        }
        Http::ok(['events' => $out]);
    }

    /** DELETE /groups/{id}/events/{eid} — payer or group admin only. */
    public static function delete(int $gid, int $eid): void
    {
        $user = Auth::requireUser();
        Groups::requireMember($gid, $user['id']);

        $event = Database::one(
            'SELECT * FROM events WHERE id = :e AND group_id = :g',
            ['e' => $eid, 'g' => $gid]
        );
        if ($event === null) {
            Http::error(404, 'event_not_found', 'That gathering does not exist.');
        }

        $role = Database::one(
            'SELECT role FROM group_members WHERE group_id = :g AND user_id = :u',
            ['g' => $gid, 'u' => $user['id']]
        );
        $isAdmin = ($role['role'] ?? '') === 'admin';
        if ((int)$event['payer_id'] !== $user['id'] && !$isAdmin) {
            Http::error(403, 'not_allowed', 'Only the payer or a group admin can delete this.');
        }

        Database::exec('DELETE FROM events WHERE id = :e', ['e' => $eid]); // shares cascade
        Http::ok(['deleted' => $eid]);
    }
}
