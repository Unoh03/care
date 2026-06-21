<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');

$mode = $_GET['mode'] ?? 'safe';
$data = $_GET['data'] ?? 'kisa-baseline';

if (!is_string($mode) || !in_array($mode, ['safe', 'vulnerable'], true)) {
    http_response_code(400);
    exit('mode must be safe or vulnerable.');
}

if (!is_string($data)) {
    http_response_code(400);
    exit('data must be a string.');
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', false);
}

$reflection = $mode === 'vulnerable' ? $data : h($data);
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Reflected XSS Proof Lab</title>
</head>
<body>
    <h1>Reflected XSS Proof Lab</h1>
    <p>DB를 사용하지 않는 반사형 XSS와 06번 checker의 출력 인코딩 판정용 실습 페이지다.</p>
    <p>mode: <strong><?= h($mode) ?></strong></p>

    <h2>Reflection</h2>
    <div id="reflection"><?= $reflection ?></div>

    <p>기본값은 <code>safe</code>이며, 사용자 입력을 HTML entity로 출력한다.</p>
    <p><code>?mode=vulnerable&amp;data=...</code>은 조치 전 반사형 XSS 비교 실습용이다.</p>
</body>
</html>
