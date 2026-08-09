<?php
declare(strict_types=1);

/** Shared group helpers used by several controllers. */
final class Groups
{
    /** Generate an unused 8-char invite code (unambiguous alphabet). */
    public static function makeInviteCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no I,O,0,1
        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $exists = Database::one(
                'SELECT id FROM groups_tbl WHERE invite_code = :c', ['c' => $code]
            );
        } while ($exists !== null);
        return $code;
    }

    /** Ensure $userId belongs to $groupId, else 403/404. Returns the group row. */
    public static function requireMember(int $groupId, int $userId): array
    {
        $group = Database::one('SELECT * FROM groups_tbl WHERE id = :id', ['id' => $groupId]);
        if ($group === null) {
            Http::error(404, 'group_not_found', 'That group does not exist.');
        }
        $member = Database::one(
            'SELECT id FROM group_members WHERE group_id = :g AND user_id = :u',
            ['g' => $groupId, 'u' => $userId]
        );
        if ($member === null) {
            Http::error(403, 'not_a_member', 'You are not a member of that group.');
        }
        return $group;
    }

    /** @return array<int,array{id:int,name:string,email:string,role:string}> */
    public static function members(int $groupId): array
    {
        $rows = Database::all(
            'SELECT u.id, u.name, u.email, gm.role
               FROM group_members gm
               JOIN users u ON u.id = gm.user_id
              WHERE gm.group_id = :g
              ORDER BY u.name',
            ['g' => $groupId]
        );
        foreach ($rows as &$r) {
            $r['id'] = (int)$r['id'];
        }
        return $rows;
    }

    /**
     * Load all events + shares for a group in the shape Settlement expects.
     * @return array<int,array{payer_id:int,shares:array<int,int>}>
     */
    public static function eventsForSettlement(int $groupId): array
    {
        $events = Database::all(
            'SELECT id, payer_id FROM events WHERE group_id = :g', ['g' => $groupId]
        );
        if (!$events) {
            return [];
        }
        $ids = array_map(static fn($e) => (int)$e['id'], $events);
        $in  = implode(',', array_fill(0, count($ids), '?'));
        $shareRows = Database::all(
            "SELECT event_id, user_id, points FROM event_shares WHERE event_id IN ($in)",
            $ids
        );
        $byEvent = [];
        foreach ($shareRows as $s) {
            $byEvent[(int)$s['event_id']][(int)$s['user_id']] = (int)$s['points'];
        }
        $out = [];
        foreach ($events as $e) {
            $out[] = [
                'payer_id' => (int)$e['payer_id'],
                'shares'   => $byEvent[(int)$e['id']] ?? [],
            ];
        }
        return $out;
    }
}
