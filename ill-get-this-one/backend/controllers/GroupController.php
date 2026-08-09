<?php
declare(strict_types=1);

final class GroupController
{
    /** POST /groups  — create a group; creator becomes admin member. */
    public static function create(): void
    {
        $user = Auth::requireUser();
        $name = Http::require(Http::body(), 'name');

        $code = Groups::makeInviteCode();
        $pdo  = Database::pdo();
        $pdo->beginTransaction();
        try {
            $gid = Database::insert(
                'INSERT INTO groups_tbl (name, invite_code, created_by) VALUES (:n, :c, :u)',
                ['n' => $name, 'c' => $code, 'u' => $user['id']]
            );
            Database::insert(
                'INSERT INTO group_members (group_id, user_id, role) VALUES (:g, :u, "admin")',
                ['g' => $gid, 'u' => $user['id']]
            );
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        Http::ok(['group' => [
            'id' => $gid, 'name' => $name, 'invite_code' => $code, 'role' => 'admin',
        ]]);
    }

    /** GET /groups — groups the caller belongs to, with their standing. */
    public static function mine(): void
    {
        $user = Auth::requireUser();
        $groups = Database::all(
            'SELECT g.id, g.name, g.invite_code, gm.role
               FROM group_members gm
               JOIN groups_tbl g ON g.id = gm.group_id
              WHERE gm.user_id = :u
              ORDER BY g.name',
            ['u' => $user['id']]
        );

        $out = [];
        foreach ($groups as $g) {
            $gid      = (int)$g['id'];
            $members  = Groups::members($gid);
            $ids      = array_map(static fn($m) => $m['id'], $members);
            $balances = Settlement::balances($ids, Groups::eventsForSettlement($gid));
            $myBal    = $balances[$user['id']] ?? 0.0;
            $out[] = [
                'id'             => $gid,
                'name'           => $g['name'],
                'invite_code'    => $g['invite_code'],
                'role'           => $g['role'],
                'member_count'   => count($members),
                'my_balance'     => round($myBal, 2),      // >0 owed to you, <0 you owe
                'my_owe_balance' => round(-$myBal, 2),      // the "you owe" number in the UI
            ];
        }
        Http::ok(['groups' => $out]);
    }

    /** POST /groups/join  {invite_code} */
    public static function join(): void
    {
        $user = Auth::requireUser();
        $code = strtoupper(Http::require(Http::body(), 'invite_code'));

        $group = Database::one('SELECT * FROM groups_tbl WHERE invite_code = :c', ['c' => $code]);
        if ($group === null) {
            Http::error(404, 'bad_code', 'No group found for that invite code.');
        }
        $gid = (int)$group['id'];

        $already = Database::one(
            'SELECT id FROM group_members WHERE group_id = :g AND user_id = :u',
            ['g' => $gid, 'u' => $user['id']]
        );
        if ($already === null) {
            Database::insert(
                'INSERT INTO group_members (group_id, user_id, role) VALUES (:g, :u, "member")',
                ['g' => $gid, 'u' => $user['id']]
            );
        }
        Http::ok(['group' => [
            'id' => $gid, 'name' => $group['name'], 'invite_code' => $group['invite_code'],
        ]]);
    }

    /** GET /groups/{id} — full standings: members, balances, settlement, next payer. */
    public static function show(int $gid): void
    {
        $user = Auth::requireUser();
        $group = Groups::requireMember($gid, $user['id']);

        $members = Groups::members($gid);
        $ids     = array_map(static fn($m) => $m['id'], $members);
        $events  = Groups::eventsForSettlement($gid);
        $balances = Settlement::balances($ids, $events);
        $nextId   = Settlement::whoShouldPayNext($balances);

        $nameById = [];
        foreach ($members as $m) {
            $nameById[$m['id']] = $m['name'];
        }

        // Standings sorted by who owes most first (most negative balance).
        $standings = [];
        foreach ($members as $m) {
            $bal = round($balances[$m['id']] ?? 0.0, 2);
            $standings[] = [
                'user_id'     => $m['id'],
                'name'        => $m['name'],
                'role'        => $m['role'],
                'balance'     => $bal,      // >0 = group owes them
                'owe_balance' => -$bal,     // >0 = they owe the group
                'is_you'      => $m['id'] === $user['id'],
                'is_up_next'  => $m['id'] === $nextId,
            ];
        }
        usort($standings, static fn($a, $b) => $a['balance'] <=> $b['balance']);

        $transfers = array_map(static function ($t) use ($nameById) {
            return [
                'from'      => $t['from'],
                'from_name' => $nameById[$t['from']] ?? '?',
                'to'        => $t['to'],
                'to_name'   => $nameById[$t['to']] ?? '?',
                'points'    => $t['points'],
            ];
        }, Settlement::simplify($balances));

        Http::ok([
            'group' => [
                'id' => $gid, 'name' => $group['name'], 'invite_code' => $group['invite_code'],
            ],
            'members'         => $members,
            'standings'       => $standings,
            'up_next'         => $nextId !== null ? [
                'user_id' => $nextId, 'name' => $nameById[$nextId] ?? '?',
            ] : null,
            'settlement'      => $transfers,
            'event_count'     => count($events),
        ]);
    }
}
