<?php
declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$mode = $_GET['mode'] ?? 'vuln';
$target = $_GET['target'] ?? '';
$output = '';
$error = '';
$cmd = '';

if (!in_array($mode, ['vuln', 'safe'], true)) {
    $mode = 'vuln';
}

if ($target !== '') {
    if ($mode === 'safe') {
        /*
         * 조치 모드:
         * IP 주소 또는 도메인처럼 필요한 형식만 허용한다.
         * 명령 연결 문자 ;, &, |, `, $, <, > 등은 허용하지 않는다.
         */
        if (!preg_match('/^[a-zA-Z0-9.-]+$/', $target)) {
            $error = '허용되지 않은 문자가 포함되어 실행을 차단했다.';
        } else {
            $cmd = 'ping -c 2 ' . escapeshellarg($target);
            $output = shell_exec($cmd . ' 2>&1') ?? '';
        }
    } else {
        /*
         * 취약 모드:
         * 사용자 입력값을 OS 명령어 문자열에 그대로 연결한다.
         */
        $cmd = 'ping -c 2 ' . $target;
        $output = shell_exec($cmd . ' 2>&1') ?? '';
    }
}
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>OS Command Injection Lab</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 960px;
            margin: 32px auto;
            line-height: 1.5;
        }
        input, select, button {
            padding: 6px;
            margin: 4px;
        }
        pre {
            background: #f4f4f4;
            border: 1px solid #ddd;
            padding: 12px;
            overflow-x: auto;
        }
        .warn {
            color: #b00020;
            font-weight: bold;
        }
        .ok {
            color: #006400;
            font-weight: bold;
        }
        .box {
            border: 1px solid #ddd;
            padding: 12px;
            margin: 12px 0;
        }
    </style>
</head>
<body>
    <h1>OS Command Injection Lab</h1>

    <div class="box">
        <p>
            이 페이지는 서버 Ping 테스트 기능을 가장한 OS Command Injection 실습 페이지다.
        </p>
        <p>
            vulnerable 모드는 사용자 입력값을 OS 명령어에 그대로 연결하고,
            safe 모드는 입력값 형식을 제한하고 <code>escapeshellarg()</code>를 적용한다.
        </p>
    </div>

    <form method="get">
        <label>
            mode:
            <select name="mode">
                <option value="vuln" <?= $mode === 'vuln' ? 'selected' : '' ?>>vulnerable</option>
                <option value="safe" <?= $mode === 'safe' ? 'selected' : '' ?>>safe</option>
            </select>
        </label>

        <label>
            target:
            <input name="target" size="40" value="<?= h($target) ?>" placeholder="127.0.0.1">
        </label>

        <button type="submit">Run Ping</button>
    </form>

    <h2>Test Cases</h2>
    <ul>
        <li>정상 요청: <code>?mode=vuln&amp;target=127.0.0.1</code></li>
        <li>공격 요청: <code>?mode=vuln&amp;target=127.0.0.1%3Bid</code></li>
        <li>조치 확인: <code>?mode=safe&amp;target=127.0.0.1%3Bid</code></li>
    </ul>

    <?php if ($target !== ''): ?>
        <h2>Constructed Command</h2>
        <pre><?= h($cmd !== '' ? $cmd : '(blocked)') ?></pre>

        <h2>Result</h2>

        <?php if ($error !== ''): ?>
            <p class="ok"><?= h($error) ?></p>
        <?php else: ?>
            <?php if ($mode === 'vuln' && preg_match('/[;&|`$<>]/', $target)): ?>
                <p class="warn">위험 입력값이 OS 명령어 문자열에 포함되었다.</p>
            <?php endif; ?>

            <pre><?= h($output) ?></pre>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>