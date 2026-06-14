<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../../config.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function find_member(mysqli $link, string $id, string $email): ?array
{
    $stmt = mysqli_prepare($link, 'SELECT id, email FROM member WHERE id = ? AND email = ?');
    mysqli_stmt_bind_param($stmt, 'ss', $id, $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $foundId, $foundEmail);
    $found = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$found) {
        return null;
    }

    return [
        'id' => (string)$foundId,
        'email' => (string)$foundEmail,
    ];
}

function write_lab_mailbox(string $id, string $email, string $code): void
{
    $line = sprintf(
        "[%s] id=%s email=%s code=%s\n",
        date('Y-m-d H:i:s'),
        $id,
        $email,
        $code
    );

    file_put_contents(sys_get_temp_dir() . '/care-password-recovery-mailbox.log', $line, FILE_APPEND | LOCK_EX);
}

$mode = $_POST['mode'] ?? $_GET['mode'] ?? 'vuln';
if (!in_array($mode, ['vuln', 'safe'], true)) {
    $mode = 'vuln';
}

$id = trim((string)($_POST['id'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';
$message = '';
$issuedCode = '';
$error = '';

if ($submitted) {
    $link = care_db_connect();
    $member = find_member($link, $id, $email);
    mysqli_close($link);

    if ($id === '' || $email === '') {
        $error = '아이디와 이메일을 입력하세요.';
    } elseif (!$member) {
        $error = '일치하는 회원 정보를 찾지 못했습니다.';
    } elseif ($mode === 'vuln') {
        /*
         * 취약 모드:
         * 인증번호가 고정값이고, 웹 화면에 바로 출력된다.
         * 공격자는 메일 계정 접근 없이 인증번호를 확인할 수 있다.
         */
        $_SESSION['pr_vuln_user_id'] = $id;
        $_SESSION['pr_vuln_email'] = $email;
        $_SESSION['pr_vuln_code'] = '1234';
        $issuedCode = '1234';
        $message = '취약 모드: 인증번호가 화면에 직접 출력되었습니다.';
    } else {
        /*
         * 조치 모드:
         * 인증번호를 random_int()로 생성하고 서버 세션에 만료 시간과 함께 저장한다.
         * 실제 메일/SMS 발송은 프로젝트 범위 밖이므로 여기서는 화면에 인증번호를 출력하지 않는다.
         */
        $code = (string)random_int(100000, 999999);
        $_SESSION['pr_safe'] = [
            'user_id' => $id,
            'email' => $email,
            'code_hash' => password_hash($code, PASSWORD_DEFAULT),
            'expires_at' => time() + 300,
            'attempts' => 0,
            'verified' => false,
        ];
        write_lab_mailbox($id, $email, $code);
        $message = '조치 모드: 인증번호를 서버에 저장하고 웹 밖의 실습용 mailbox에 기록했습니다. 실제 운영에서는 등록된 이메일/SMS로만 전송합니다.';
    }
}
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>Password Recovery Request Lab</title>
    <style>
        body { font-family: sans-serif; max-width: 960px; margin: 32px auto; line-height: 1.5; }
        input, select, button { padding: 6px; margin: 4px; }
        pre, .box { background: #f7f7f7; border: 1px solid #ddd; padding: 12px; }
        .warn { color: #b00020; font-weight: bold; }
        .ok { color: #006400; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Password Recovery Lab - 1. Request</h1>

    <div class="box">
        <p>12번 취약점은 비밀번호 복구 절차가 단순하거나 임시 인증값이 노출되는지 확인한다.</p>
        <p>13번 취약점은 이후 <code>reset.php</code>를 직접 호출해 이전 단계를 생략할 수 있는지 확인한다.</p>
        <p>조치 모드의 실습용 인증번호는 웹 화면에 출력하지 않고 서버의 <code>/tmp/care-password-recovery-mailbox.log</code>에만 기록한다.</p>
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
        <label>email: <input name="email" value="<?= h($email) ?>" placeholder="victim@example.com"></label>
        <br>
        <button type="submit">Request Code</button>
    </form>

    <p>
        <a href="verify.php?mode=<?= h($mode) ?>">2. Verify</a> |
        <a href="reset.php?mode=<?= h($mode) ?>">3. Reset</a>
    </p>

    <?php if ($error !== ''): ?>
        <p class="warn"><?= h($error) ?></p>
    <?php endif; ?>

    <?php if ($message !== ''): ?>
        <p class="<?= $mode === 'vuln' ? 'warn' : 'ok' ?>"><?= h($message) ?></p>
    <?php endif; ?>

    <?php if ($issuedCode !== ''): ?>
        <h2>Issued Code</h2>
        <pre><?= h($issuedCode) ?></pre>
        <p class="warn">취약 증거: 인증번호가 메일/SMS가 아니라 웹 화면에 바로 노출된다.</p>
    <?php endif; ?>
</body>
</html>
