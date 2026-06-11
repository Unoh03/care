<?php
declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function node_text(DOMElement $parent, string $tagName): string
{
    $nodes = $parent->getElementsByTagName($tagName);

    if ($nodes->length === 0) {
        return '';
    }

    return $nodes->item(0)->textContent ?? '';
}

function extract_user(DOMElement $userNode): array
{
    return [
        'username' => node_text($userNode, 'username'),
        'role' => node_text($userNode, 'role'),
        'department' => node_text($userNode, 'department'),
    ];
}

$mode = $_POST['mode'] ?? $_GET['mode'] ?? 'vuln';

if (!in_array($mode, ['vuln', 'safe'], true)) {
    $mode = 'vuln';
}

$username = $_POST['username'] ?? $_GET['username'] ?? 'admin';
$password = $_POST['password'] ?? $_GET['password'] ?? 'admin123';
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST'
    || isset($_GET['username'], $_GET['password']);

$xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<users>
  <user>
    <username>admin</username>
    <password>admin123</password>
    <role>admin</role>
    <department>system</department>
  </user>
  <user>
    <username>doctor</username>
    <password>doctor123</password>
    <role>doctor</role>
    <department>medical</department>
  </user>
  <user>
    <username>guest</username>
    <password>guest123</password>
    <role>viewer</role>
    <department>public</department>
  </user>
</users>
XML;

$dom = new DOMDocument();
$dom->loadXML($xml);
$xpath = new DOMXPath($dom);

$query = '';
$matchedUsers = [];
$error = '';

if ($submitted) {
    if ($mode === 'safe') {
        /*
         * 조치 모드:
         * 사용자 입력값을 XPath 질의 문자열에 직접 넣지 않는다.
         * 먼저 허용 문자만 통과시키고, XML 노드 조회 후 PHP에서 문자열 비교를 수행한다.
         */
        if (
            !preg_match('/^[a-zA-Z0-9_-]{1,32}$/', $username)
            || !preg_match('/^[a-zA-Z0-9_-]{1,32}$/', $password)
        ) {
            $error = '허용되지 않은 문자가 포함되어 요청을 차단했다.';
        } else {
            $query = '//user';
            $nodes = $xpath->query($query);

            if ($nodes !== false) {
                foreach ($nodes as $node) {
                    if (!$node instanceof DOMElement) {
                        continue;
                    }

                    if (
                        node_text($node, 'username') === $username
                        && node_text($node, 'password') === $password
                    ) {
                        $matchedUsers[] = extract_user($node);
                    }
                }
            }
        }
    } else {
        /*
         * 취약 모드:
         * 사용자 입력값을 XPath 질의 문자열에 그대로 연결한다.
         */
        $query = "//user[username/text()='$username' and password/text()='$password']";
        $nodes = @$xpath->query($query);

        if ($nodes === false) {
            $error = 'XPath 질의 처리 중 오류가 발생했다.';
        } else {
            foreach ($nodes as $node) {
                if (!$node instanceof DOMElement) {
                    continue;
                }

                $matchedUsers[] = extract_user($node);
            }
        }
    }
}
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>XPath Injection Lab</title>
    <style>
        body { font-family: sans-serif; max-width: 960px; margin: 32px auto; line-height: 1.5; }
        input, select, button { padding: 6px; margin: 4px 0; }
        input[type="text"] { width: 320px; }
        pre { background: #f4f4f4; border: 1px solid #ddd; padding: 12px; overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .warn { color: #b00020; font-weight: bold; }
        .ok { color: #006400; font-weight: bold; }
        .box { border: 1px solid #ddd; padding: 12px; margin: 12px 0; }
    </style>
</head>
<body>
    <h1>XPath Injection Lab</h1>

    <div class="box">
        <p>이 페이지는 XML 사용자 데이터를 XPath 질의로 조회하는 로그인 실습 페이지다.</p>
        <p>vulnerable 모드는 사용자 입력값을 XPath 질의에 그대로 연결하고, safe 모드는 입력값 형식을 제한한 뒤 XPath 질의와 분리해서 비교한다.</p>
    </div>

    <form method="post">
        <label>
            mode:
            <select name="mode">
                <option value="vuln" <?= $mode === 'vuln' ? 'selected' : '' ?>>vulnerable</option>
                <option value="safe" <?= $mode === 'safe' ? 'selected' : '' ?>>safe</option>
            </select>
        </label>

        <h2>Login Input</h2>

        <p>
            <label>
                username:
                <input type="text" name="username" value="<?= h($username) ?>">
            </label>
        </p>

        <p>
            <label>
                password:
                <input type="text" name="password" value="<?= h($password) ?>">
            </label>
        </p>

        <button type="submit">Login</button>
    </form>

    <h2>Test Cases</h2>
    <ul>
        <li>정상 로그인: <code>username=admin</code>, <code>password=admin123</code></li>
        <li>로그인 실패: <code>username=admin</code>, <code>password=wrong</code></li>
        <li>공격 입력: <code>username=admin</code>, <code>password=' or '1'='1</code></li>
    </ul>

    <?php if ($submitted): ?>
        <h2>Constructed XPath Query</h2>
        <pre><?= h($query !== '' ? $query : '(blocked)') ?></pre>

        <h2>Result</h2>

        <?php if ($error !== ''): ?>
            <p class="ok"><?= h($error) ?></p>
        <?php elseif (count($matchedUsers) > 0): ?>
            <p class="warn">로그인 성공 또는 사용자 데이터 조회 성공</p>
            <table>
                <thead>
                    <tr>
                        <th>username</th>
                        <th>role</th>
                        <th>department</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matchedUsers as $user): ?>
                        <tr>
                            <td><?= h($user['username']) ?></td>
                            <td><?= h($user['role']) ?></td>
                            <td><?= h($user['department']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>로그인 실패 또는 조회 결과 없음</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>