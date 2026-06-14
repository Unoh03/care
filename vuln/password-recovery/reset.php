<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../../config.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function update_password(mysqli $link, string $id, string $password): bool
{
    $stmt = mysqli_prepare($link, 'UPDATE member SET pw = ? WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'ss', $password, $id);
    mysqli_stmt_execute($stmt);
    $changed = mysqli_stmt_affected_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    return $changed;
}

$mode = $_POST['mode'] ?? $_GET['mode'] ?? 'vuln';
if (!in_array($mode, ['vuln', 'safe'], true)) {
    $mode = 'vuln';
}

$id = trim((string)($_POST['id'] ?? $_GET['id'] ?? ($_SESSION['pr_vuln_user_id'] ?? '')));
$newPassword = (string)($_POST['new_password'] ?? '');
$newPasswordCheck = (string)($_POST['new_password_check'] ?? '');
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';
$message = '';
$error = '';

if ($submitted) {
    if ($id === '' || $newPassword === '' || $newPasswordCheck === '') {
        $error = '아이디와 새 비밀번호를 입력하세요.';
    } elseif ($newPassword !== $newPasswordCheck) {
        $error = '새 비밀번호 확인 값이 일치하지 않습니다.';
    } elseif ($mode === 'safe' && strlen($newPassword) < 6) {
        $error = '조치 모드에서는 새 비밀번호를 6자 이상으로 입력하세요.';
    } else {
        if ($mode === 'safe') {
            /*
             * 조치 모드:
             * verify.php에서 인증 성공 상태가 서버 세션에 기록되어 있어야 한다.
             * 직접 reset.php만 호출하면 차단된다.
             */
            $state = $_SESSION['pr_safe'] ?? null;
            $verifiedAt = is_array($state) ? (int)($state['verified_at'] ?? 0) : 0;

            if (!is_array($state) || ($state['verified'] ?? false) !== true) {
                $error = '인증번호 검증이 완료되지 않았습니다.';
            } elseif (($state['user_id'] ?? '') !== $id) {
                $error = '인증한 계정과 비밀번호 변경 대상 계정이 다릅니다.';
            } elseif (time() - $verifiedAt > 300) {
                unset($_SESSION['pr_safe']);
                $error = '인증 완료 상태가 만료되었습니다.';
            }
        }

        if ($error === '') {
            $link = care_db_connect();
            $updated = update_password($link, $id, $newPassword);
            mysqli_close($link);

            if (!$updated) {
                $error = '해당 회원을 찾지 못했거나 비밀번호가 변경되지 않았습니다.';
            } else {
                if ($mode === 'safe') {
                    unset($_SESSION['pr_safe']);
                }

                $message = $mode === 'vuln'
                    ? '취약 모드: 인증번호 검증 단계를 거치지 않아도 비밀번호가 변경되었습니다.'
                    : '조치 모드: 인증 완료 상태를 확인한 뒤 비밀번호를 변경했습니다.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>Password Recovery Reset Lab</title>
    <style>
        body { font-family: sans-serif; max-width: 960px; margin: 32px auto; line-height: 1.5; }
        input, select, button { padding: 6px; margin: 4px; }
        pre, .box { background: #f7f7f7; border: 1px solid #ddd; padding: 12px; }
        .warn { color: #b00020; font-weight: bold; }
        .ok { color: #006400; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Password Recovery Lab - 3. Reset</h1>

    <div class="box">
        <p>13번 취약점의 핵심 증거는 vulnerable 모드에서 <code>request.php</code>, <code>verify.php</code>를 건너뛰고 이 페이지를 직접 호출해 비밀번호가 바뀌는 것이다.</p>
        <p>safe 모드는 <code>verify.php</code>가 남긴 서버 세션 상태가 없으면 비밀번호 변경을 차단한다.</p>
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
        <label>id: <input name="id" value="<?= h($id) ?>" placeholder="victim"></label>
        <br>
        <label>new password: <input type="password" name="new_password"></label>
        <br>
        <label>new password check: <input type="password" name="new_password_check"></label>
        <br>
        <button type="submit">Reset Password</button>
    </form>

    <p>
        <a href="request.php?mode=<?= h($mode) ?>">1. Request</a> |
        <a href="verify.php?mode=<?= h($mode) ?>">2. Verify</a>
    </p>

    <h2>Direct Access Test</h2>
    <pre>/vuln/password-recovery/reset.php?mode=vuln&amp;id=victim</pre>

    <?php if ($error !== ''): ?>
        <p class="warn"><?= h($error) ?></p>
    <?php endif; ?>

    <?php if ($message !== ''): ?>
        <p class="<?= $mode === 'vuln' ? 'warn' : 'ok' ?>"><?= h($message) ?></p>
    <?php endif; ?>
</body>
</html>
