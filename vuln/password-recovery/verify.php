<?php
declare(strict_types=1);
session_start();

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$mode = $_POST['mode'] ?? $_GET['mode'] ?? 'vuln';
if (!in_array($mode, ['vuln', 'safe'], true)) {
    $mode = 'vuln';
}

$code = trim((string)($_POST['code'] ?? ''));
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';
$message = '';
$error = '';

if ($submitted) {
    if ($mode === 'vuln') {
        /*
         * 취약 모드:
         * 고정 인증번호만 비교한다. 성공 상태를 reset.php가 신뢰할 방식으로 강제하지 않는다.
         * reset.php는 이 단계를 건너뛰어도 비밀번호 변경을 허용하도록 별도로 취약하게 구성되어 있다.
         */
        if ($code === ($_SESSION['pr_vuln_code'] ?? '1234')) {
            $_SESSION['pr_vuln_verified'] = true;
            $message = '취약 모드: 인증번호가 일치합니다.';
        } else {
            $error = '인증번호가 일치하지 않습니다.';
        }
    } else {
        $state = $_SESSION['pr_safe'] ?? null;

        if (!is_array($state)) {
            $error = '복구 요청 상태가 없습니다. request.php부터 진행하세요.';
        } elseif (time() > (int)$state['expires_at']) {
            unset($_SESSION['pr_safe']);
            $error = '인증번호가 만료되었습니다.';
        } elseif ((int)$state['attempts'] >= 5) {
            $error = '인증번호 입력 실패 횟수를 초과했습니다.';
        } elseif (!password_verify($code, (string)$state['code_hash'])) {
            $_SESSION['pr_safe']['attempts'] = ((int)$state['attempts']) + 1;
            $error = '인증번호가 일치하지 않습니다.';
        } else {
            $_SESSION['pr_safe']['verified'] = true;
            $_SESSION['pr_safe']['verified_at'] = time();
            $message = '조치 모드: 인증번호 검증이 완료되었습니다.';
        }
    }
}
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>Password Recovery Verify Lab</title>
    <style>
        body { font-family: sans-serif; max-width: 960px; margin: 32px auto; line-height: 1.5; }
        input, select, button { padding: 6px; margin: 4px; }
        .box { background: #f7f7f7; border: 1px solid #ddd; padding: 12px; }
        .warn { color: #b00020; font-weight: bold; }
        .ok { color: #006400; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Password Recovery Lab - 2. Verify</h1>

    <div class="box">
        <p>취약 모드는 고정 인증번호를 사용한다. 조치 모드는 만료 시간과 실패 횟수를 서버 세션에서 검증한다.</p>
    </div>

    <form method="post">
        <label>
            mode:
            <select name="mode">
                <option value="vuln" <?= $mode === 'vuln' ? 'selected' : '' ?>>vulnerable</option>
                <option value="safe" <?= $mode === 'safe' ? 'selected' : '' ?>>safe</option>
            </select>
        </label>
        <br>
        <label>code: <input name="code" value="<?= h($code) ?>" placeholder="1234"></label>
        <br>
        <button type="submit">Verify Code</button>
    </form>

    <p>
        <a href="request.php?mode=<?= h($mode) ?>">1. Request</a> |
        <a href="reset.php?mode=<?= h($mode) ?>">3. Reset</a>
    </p>

    <?php if ($error !== ''): ?>
        <p class="warn"><?= h($error) ?></p>
    <?php endif; ?>

    <?php if ($message !== ''): ?>
        <p class="ok"><?= h($message) ?></p>
    <?php endif; ?>
</body>
</html>
