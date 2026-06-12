<?php
$url = $_GET['url'] ?? '';

if ($url === '') {
    echo 'url 파라미터를 입력하세요.';
    exit;
}

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 5,
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
echo substr((string)$response, 0, 2000);