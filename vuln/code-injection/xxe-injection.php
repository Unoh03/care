<?php
declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function collect_libxml_errors(): string
{
    $messages = [];

    foreach (libxml_get_errors() as $error) {
        $messages[] = trim($error->message) . ' on line ' . $error->line;
    }

    libxml_clear_errors();

    return implode(PHP_EOL, $messages);
}

$mode = $_POST['mode'] ?? $_GET['mode'] ?? 'vuln';

if (!in_array($mode, ['vuln', 'safe'], true)) {
    $mode = 'vuln';
}

$defaultXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<data>Hello XML</data>
XML;

$xxePayload = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE data [
  <!ENTITY xxe SYSTEM "file:///etc/hostname">
]>
<data>&xxe;</data>
XML;

$xmlInput = $_POST['xml'] ?? $_GET['xml'] ?? $defaultXml;
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['xml']);

$error = '';
$parseErrors = '';
$rootName = '';
$textContent = '';
$serialized = '';
$optionsLabel = '';

if ($submitted) {
    libxml_use_internal_errors(true);

    $dom = new DOMDocument();

    if ($mode === 'safe') {
        /*
         * 조치 모드:
         * DTD와 외부 엔티티 선언을 차단하고, 엔티티 치환 옵션인 LIBXML_NOENT를 사용하지 않는다.
         * LIBXML_NONET은 네트워크 접근을 제한하는 보조 조치다.
         */
        $optionsLabel = 'LIBXML_NONET';

        if (preg_match('/<!\s*DOCTYPE|<!\s*ENTITY|\bSYSTEM\b|\bPUBLIC\b/i', $xmlInput)) {
            $error = 'DTD 또는 외부 엔티티 선언이 포함되어 요청을 차단했다.';
        } else {
            $loaded = $dom->loadXML($xmlInput, LIBXML_NONET);

            if (!$loaded) {
                $error = 'XML 파싱 중 오류가 발생했다.';
                $parseErrors = collect_libxml_errors();
            }
        }
    } else {
        /*
         * 취약 모드:
         * 외부 엔티티를 로드하고 엔티티 참조를 실제 값으로 치환한다.
         * 이 설정은 교육용 실습을 위해 의도적으로 취약하게 구성한 것이다.
         */
        $optionsLabel = 'LIBXML_NOENT | LIBXML_DTDLOAD';
        $loaded = $dom->loadXML($xmlInput, LIBXML_NOENT | LIBXML_DTDLOAD);

        if (!$loaded) {
            $error = 'XML 파싱 중 오류가 발생했다.';
            $parseErrors = collect_libxml_errors();
        }
    }

    if ($error === '' && $dom->documentElement instanceof DOMElement) {
        $rootName = $dom->documentElement->tagName;
        $textContent = $dom->documentElement->textContent ?? '';
        $serialized = $dom->saveXML($dom->documentElement) ?: '';
    }

    libxml_use_internal_errors(false);
}
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>XXE Injection Lab</title>
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
    <h1>XXE Injection Lab</h1>

    <div class="box">
        <p>이 페이지는 사용자가 입력한 XML을 서버에서 파싱하는 XXE Injection 실습 페이지다.</p>
        <p>vulnerable 모드는 외부 엔티티를 로드하고 치환하며, safe 모드는 DTD/ENTITY 선언을 차단하고 엔티티 치환 옵션을 사용하지 않는다.</p>
    </div>

    <form method="post">
        <label>
            mode:
            <select name="mode">
                <option value="vuln" <?= $mode === 'vuln' ? 'selected' : '' ?>>vulnerable</option>
                <option value="safe" <?= $mode === 'safe' ? 'selected' : '' ?>>safe</option>
            </select>
        </label>

        <h2>XML Input</h2>
        <textarea name="xml"><?= h($xmlInput) ?></textarea>

        <br>
        <button type="submit">Parse XML</button>
    </form>

    <h2>Test Payloads</h2>

    <h3>1. 정상 XML</h3>
    <pre><?= h($defaultXml) ?></pre>

    <h3>2. XXE payload</h3>
    <pre><?= h($xxePayload) ?></pre>

    <?php if ($submitted): ?>
        <h2>Parser Options</h2>
        <pre><?= h($optionsLabel) ?></pre>

        <h2>Result</h2>

        <?php if ($error !== ''): ?>
            <p class="ok"><?= h($error) ?></p>

            <?php if ($parseErrors !== ''): ?>
                <h3>Parse Errors</h3>
                <pre><?= h($parseErrors) ?></pre>
            <?php endif; ?>
        <?php else: ?>
            <?php if ($mode === 'vuln' && preg_match('/<!\s*DOCTYPE|<!\s*ENTITY|\bSYSTEM\b|\bPUBLIC\b/i', $xmlInput)): ?>
                <p class="warn">외부 엔티티 선언이 포함된 XML이 파싱되었다.</p>
            <?php endif; ?>

            <h3>Root Element</h3>
            <pre><?= h($rootName) ?></pre>

            <h3>Text Content</h3>
            <pre><?= h($textContent) ?></pre>

            <h3>Serialized Root Node</h3>
            <pre><?= h($serialized) ?></pre>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>