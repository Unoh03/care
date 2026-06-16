<?php
header('Content-Type: text/plain; charset=utf-8');

$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
$allowedLocalAddresses = ['127.0.0.1', '::1'];

if (!in_array($remoteAddr, $allowedLocalAddresses, true)) {
    http_response_code(403);
    echo "[DENIED]\n";
    echo "This proof page is only available from localhost.\n";
    echo "remote_addr=" . $remoteAddr . "\n";
    exit;
}

echo "[SSRF_INTERNAL_PROOF]\n";
echo "proof=care-ssrf-local-only-proof\n";
echo "remote_addr=" . $remoteAddr . "\n";
echo "message=This response is visible only when the CARE server requests its own localhost address.\n";
