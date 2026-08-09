<?php
declare(strict_types=1);

/**
 * Settlement — the "funny money" points engine.
 *
 * Model
 * -----
 * When one person picks up a bill for a gathering ("I'll get this one!"),
 * every attendee estimates their own cost in POINTS. The payer is then
 * owed each *other* attendee's estimate. The payer's own estimate is
 * their own cost and is not owed to anyone.
 *
 * Net balance for a member across all gatherings in a group:
 *   balance > 0  => CREDITOR : the group owes them (they've been generous).
 *   balance < 0  => DEBTOR   : they owe the group (they should get the next one).
 *   balance == 0 => even.
 *
 * The "owe balance" a member sees in the UI is simply -balance, so a large
 * positive owe-balance means "you owe the group the most -> your turn to pay".
 * Paying collects points, pushing your balance up (owe-balance down) and
 * everyone else's down (owe-balance up). It self-balances over time.
 *
 * All methods here are pure functions so they can be unit-tested without a DB.
 */
final class Settlement
{
    /**
     * @param int[] $memberIds        All member user-ids in the group.
     * @param array $events           Each: ['payer_id'=>int, 'shares'=>[user_id=>points, ...]]
     * @return array<int,float>       user_id => net balance
     */
    public static function balances(array $memberIds, array $events): array
    {
        $bal = [];
        foreach ($memberIds as $m) {
            $bal[(int)$m] = 0.0;
        }

        foreach ($events as $e) {
            $payer = (int)$e['payer_id'];
            $shares = $e['shares'] ?? [];
            foreach ($shares as $uid => $pts) {
                $uid = (int)$uid;
                $pts = (float)$pts;
                if (!isset($bal[$uid])) {
                    $bal[$uid] = 0.0; // member may have left; keep math consistent
                }
                if ($uid === $payer) {
                    continue; // payer's own share isn't owed to anyone
                }
                $bal[$payer] = ($bal[$payer] ?? 0.0) + $pts; // payer is owed
                $bal[$uid]  -= $pts;                          // attendee owes
            }
        }

        return $bal;
    }

    /**
     * Reduce the web of debts to the minimum set of point transfers that
     * would zero everyone out. Greedy largest-creditor / largest-debtor match.
     *
     * @param array<int,float> $balances user_id => net balance
     * @return array<int,array{from:int,to:int,points:float}>
     */
    public static function simplify(array $balances): array
    {
        $eps = 1e-9;
        $creditors = [];
        $debtors   = [];
        foreach ($balances as $uid => $b) {
            if ($b > $eps) {
                $creditors[] = ['id' => (int)$uid, 'amt' => (float)$b];
            } elseif ($b < -$eps) {
                $debtors[]   = ['id' => (int)$uid, 'amt' => (float)(-$b)];
            }
        }

        usort($creditors, static fn($a, $b) => $b['amt'] <=> $a['amt']);
        usort($debtors,   static fn($a, $b) => $b['amt'] <=> $a['amt']);

        $transfers = [];
        $i = 0;
        $j = 0;
        while ($i < count($debtors) && $j < count($creditors)) {
            $pay = min($debtors[$i]['amt'], $creditors[$j]['amt']);
            $transfers[] = [
                'from'   => $debtors[$i]['id'],
                'to'     => $creditors[$j]['id'],
                'points' => round($pay, 2),
            ];
            $debtors[$i]['amt']   -= $pay;
            $creditors[$j]['amt'] -= $pay;
            if ($debtors[$i]['amt'] < $eps) {
                $i++;
            }
            if ($creditors[$j]['amt'] < $eps) {
                $j++;
            }
        }

        return $transfers;
    }

    /**
     * The member who owes the group the most (most negative balance) — the
     * one whose turn it is to "get this one". Ties broken by lowest user id.
     *
     * @param array<int,float> $balances
     */
    public static function whoShouldPayNext(array $balances): ?int
    {
        $who = null;
        $min = null;
        foreach ($balances as $uid => $b) {
            if ($min === null || $b < $min - 1e-9
                || (abs($b - $min) <= 1e-9 && $who !== null && (int)$uid < $who)) {
                $min = $b;
                $who = (int)$uid;
            }
        }
        return $who;
    }
}
