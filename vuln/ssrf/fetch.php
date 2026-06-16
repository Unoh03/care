<?php
$url = $_GET['url'] ?? '';

if ($url === '') {
    echo 'url 파라미터를 입력하세요.';
    exit;
}

$parts = parse_url($url);

if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
    exit('잘못된 URL입니다.');
}

$scheme = strtolower($parts['scheme']);
$host = strtolower($parts['host']);
$port = isset($parts['port']) ? (int)$parts['port'] : ($scheme === 'https' ? 443 : 80);

$allowedTargets = [
    '172.168.10.10' => [80],
];

// PDF 조치 1: 허용 URL/IP 화이트리스트 적용
if (!isset($allowedTargets[$host]) || !in_array($port, $allowedTargets[$host], true)) {
    exit('허용되지 않은 요청 대상입니다.');
}

function isPrivateOrLocalIp(string $ip): bool
{
    return !filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    );
}

function resolvesToBlockedIp(string $host): bool
{
    if ($host === 'localhost') {
        return true;
    }

    if (filter_var($host, FILTER_VALIDATE_IP)) {
        return isPrivateOrLocalIp($host);
    }

    $records = dns_get_record($host, DNS_A + DNS_AAAA);

    foreach ($records as $record) {
        $ip = $record['ip'] ?? $record['ipv6'] ?? null;

        if ($ip !== null && isPrivateOrLocalIp($ip)) {
            return true;
        }
    }

    return false;
}

// PDF 조치 2: 내부 네트워크 대역 및 관리용 주소 차단
if (resolvesToBlockedIp($host)) {
    exit('내부 주소 요청은 차단됩니다.');
}

// PDF 조치 4: HTTP/HTTPS 외 URL scheme 차단
if (!in_array($scheme, ['http', 'https'], true)) {
    exit('허용되지 않은 URL scheme입니다.');
}

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    // PDF 조치 4-1: redirect로 내부 주소 우회가 생기지 않도록 자동 redirect 비활성화
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_TIMEOUT => 5,
    CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
    CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

header('Content-Type: text/plain; charset=utf-8');

echo "[URL]\n" . $url . "\n\n";
echo "[HTTP_CODE]\n" . ($info['http_code'] ?? '') . "\n\n";

echo "[ERROR]\n" . $error . "\n\n";

echo "[RESPONSE]\n";
// PDF 조치 3: 운영 환경에서는 내부 응답 body와 상세 에러를 사용자에게 그대로 노출하지 않는다.
// 이 실습 페이지는 조치 전/후 차이를 확인하기 위해 제한된 길이만 출력한다.
echo substr((string)$response, 0, 2000);
