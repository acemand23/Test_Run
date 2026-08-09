<?php
declare(strict_types=1);

/**
 * Tiny dependency-free test runner for the settlement engine.
 * Run:  php tests/settlement_test.php
 */

require dirname(__DIR__) . '/lib/Settlement.php';

$passed = 0;
$failed = 0;

function check(string $label, bool $cond): void
{
    global $passed, $failed;
    if ($cond) { $passed++; echo "  PASS  $label\n"; }
    else       { $failed++; echo "  FAIL  $label\n"; }
}

// 1) Balances always sum to zero.
$members = [1, 2, 3];
$events = [
    ['payer_id' => 2, 'shares' => [1 => 20]],   // A owes B 20
    ['payer_id' => 3, 'shares' => [2 => 10]],   // B owes C 10
    ['payer_id' => 1, 'shares' => [3 => 5]],    // C owes A 5
];
$bal = Settlement::balances($members, $events);
check('balances sum to zero', abs(array_sum($bal)) < 1e-9);

// 2) The user's worked example: A ends up owing 15 net.
check('A net balance = -15', abs($bal[1] - (-15)) < 1e-9);
check('B net balance =  10', abs($bal[2] - 10) < 1e-9);
check('C net balance =   5', abs($bal[3] - 5) < 1e-9);

// 3) The biggest debtor is "up next".
check('A is up next', Settlement::whoShouldPayNext($bal) === 1);

// 4) Simplified transfers net everyone to zero.
$transfers = Settlement::simplify($bal);
$net = array_fill_keys($members, 0.0);
foreach ($transfers as $t) {
    $net[$t['from']] -= $t['points'];
    $net[$t['to']]   += $t['points'];
}
$settles = true;
foreach ($members as $m) {
    if (abs(($net[$m] ?? 0) - $bal[$m]) > 1e-9) { $settles = false; }
}
check('simplified transfers reproduce balances', $settles);

// 5) Payer's own share is never owed.
$b2 = Settlement::balances([10, 11, 12], [
    ['payer_id' => 10, 'shares' => [10 => 30, 11 => 25, 12 => 45]],
]);
check('payer credited only others\' shares (70)', abs($b2[10] - 70) < 1e-9);
check('attendee owes their own share (-25)', abs($b2[11] - (-25)) < 1e-9);

// 6) Empty group / no events -> everyone at zero, nobody uniquely up next crashes.
$b3 = Settlement::balances([5, 6], []);
check('no events => zero balances', $b3[5] === 0.0 && $b3[6] === 0.0);
check('whoShouldPayNext handles all-zero', Settlement::whoShouldPayNext($b3) !== null);

echo "\n$passed passed, $failed failed\n";
exit($failed === 0 ? 0 : 1);
