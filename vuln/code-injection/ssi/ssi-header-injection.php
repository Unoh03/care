<?php
declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$mode = $_POST['mode'] ?? $_GET['mode'] ?? 'vuln';

if (!in_array($mode, ['vuln', 'safe'], true)) {
    $mode = 'vuln';
}

$defaultPayload = <<<PAYLOAD
<h2>사용자 입력 영역</h2>
<p>DOCUMENT_ROOT: <!--#echo var="DOCUMENT_ROOT" --></p>
<pre><!--#exec cmd="id" --></pre>
PAYLOAD;

$content = $_POST['content'] ?? $defaultPayload;
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

$generatedDir = __DIR__ . '/generated';
$generatedFile = $generatedDir . '/result.shtml';
$generatedUrl = 'generated/result.shtml?ts=' . time();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_dir($generatedDir)) {
        if (!mkdir($generatedDir, 0750, true) && !is_dir($generatedDir)) {
            $error = 'generated 디렉터리 생성에 실패했다. 서버 디렉터리 권한을 확인해야 한다.';
        }
    }

    if ($error === '') {
        if ($mode === 'safe') {
            /*
            * 조치 모드:
            * 본문 입력값과 요청 헤더 값을 HTML 엔티티로 변환해서
            * SSI 지시어가 <!--#... --> 형태로 해석되지 못하게 만든다.
            */
            $body = h($content);
            $headerReferer = h($referer);
            $headerUserAgent = h($userAgent);
        } else {
            /*
            * 취약 모드:
            * 사용자 입력값과 요청 헤더 값을 .shtml 파일에 그대로 저장한다.
            * Apache SSI가 켜져 있으면 헤더에 삽입된 SSI 지시어도 서버에서 실행될 수 있다.
            */
            $body = $content;
            $headerReferer = $referer;
            $headerUserAgent = $userAgent;
        }

        $page = '<!doctype html>' . PHP_EOL;
        $page .= '<html lang="ko">' . PHP_EOL;
        $page .= '<head>' . PHP_EOL;
        $page .= '    <meta charset="utf-8">' . PHP_EOL;
        $page .= '    <title>Generated SSI Page</title>' . PHP_EOL;
        $page .= '    <style>body{font-family:sans-serif;max-width:960px;margin:32px auto;line-height:1.5}pre{background:#f4f4f4;border:1px solid #ddd;padding:12px;overflow-x:auto}</style>' . PHP_EOL;
        $page .= '</head>' . PHP_EOL;
        $page .= '<body>' . PHP_EOL;
        $page .= '<h1>Generated SSI Page</h1>' . PHP_EOL;
        $page .= '<p>mode: ' . h($mode) . '</p>' . PHP_EOL;
        $page .= '<hr>' . PHP_EOL;
        $page .= $body . PHP_EOL;
        $page .= '</body>' . PHP_EOL;
        
        $page .= '</html>' . PHP_EOL;

        if (file_put_contents($generatedFile, $page, LOCK_EX) === false) {
            $error = 'result.shtml 파일 저장에 실패했다. generated 디렉터리 권한을 확인해야 한다.';
        } else {
            $message = 'result.shtml 파일을 생성했다.';
        }
    }
}
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>SSI Injection Lab</title>
    <style>
        body { font-family: sans-serif; max-width: 960px; margin: 32px auto; line-height: 1.5; }
        textarea { width: 100%; height: 220px; font-family: monospace; }
        select, button { padding: 6px; margin: 4px 0; }
        pre { background: #f4f4f4; border: 1px solid #ddd; padding: 12px; overflow-x: auto; }
        .warn { color: #b00020; font-weight: bold; }
        .ok { color: #006400; font-weight: bold; }
        .box { border: 1px solid #ddd; padding: 12px; margin: 12px 0; }
    </style>
</head>
<body>
    <h1>SSI Injection Lab</h1>

    <div class="box">
        <p>이 페이지는 사용자 입력값을 <code>.shtml</code> 문서에 반영하는 SSI Injection 실습 페이지다.</p>
        <p>취약 모드는 입력값을 그대로 저장하고, 조치 모드는 입력값을 HTML 엔티티로 변환해 SSI 지시어 실행을 막는다.</p>
    </div>

    <form method="post">
        <label>
            mode:
            <select name="mode">
                <option value="vuln" <?= $mode === 'vuln' ? 'selected' : '' ?>>vulnerable</option>
                <option value="safe" <?= $mode === 'safe' ? 'selected' : '' ?>>safe</option>
            </select>
        </label>

        <h2>Input</h2>
        <textarea name="content"><?= h($content) ?></textarea>

        <br>
        <button type="submit">Generate .shtml</button>
    </form>

    <h2>Test Payloads</h2>

    <h3>1. SSI 변수 출력</h3>
    <pre>&lt;!--#echo var="DOCUMENT_ROOT" --&gt;</pre>

    <h3>2. 서버 명령 실행</h3>
    <pre>&lt;!--#exec cmd="id" --&gt;</pre>

    <?php if ($message !== ''): ?>
        <p class="ok"><?= h($message) ?></p>
        <p><a href="<?= h($generatedUrl) ?>" target="_blank">생성된 SSI 페이지 열기</a></p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p class="warn"><?= h($error) ?></p>
    <?php endif; ?>

    <h2>Generated File Path</h2>
    <pre><?= h($generatedFile) ?></pre>
</body>
</html>