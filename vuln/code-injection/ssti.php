<?php
declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$autoload = __DIR__ . '/../../vendor/autoload.php';

$mode = $_POST['mode'] ?? $_GET['mode'] ?? 'vuln';

if (!in_array($mode, ['vuln', 'safe'], true)) {
    $mode = 'vuln';
}

$defaultTemplate = <<<'TWIG'
<h2>SSTI Test</h2>
<p>7 * 7 = {{ 7 * 7 }}</p>
<p>Application: {{ app_name }}</p>
<p>Current user role: {{ current_user.role }}</p>
<p>Context keys: {{ _context|keys|join(', ') }}</p>
TWIG;

$templateInput = $_POST['template'] ?? $_GET['template'] ?? $defaultTemplate;
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['template']);

$rendered = '';
$error = '';
$modeDescription = '';

$context = [
    'app_name' => 'CARE',
    'environment' => 'web-security-lab',
    'current_user' => [
        'username' => 'guest',
        'role' => 'viewer',
    ],
    'users' => [
        ['username' => 'admin', 'role' => 'admin'],
        ['username' => 'doctor', 'role' => 'doctor'],
        ['username' => 'guest', 'role' => 'viewer'],
    ],
    /*
     * 실습용 가짜 비밀값이다.
     * 실제 운영 비밀값을 템플릿 context에 노출하면 안 된다.
     */
    'lab_secret' => 'demo-secret-value-do-not-use-in-production',
];

if (!is_file($autoload)) {
    $error = 'vendor/autoload.php 파일을 찾을 수 없다. composer install 실행 여부를 확인해야 한다.';
} else {
    require_once $autoload;

    if ($submitted) {
        try {
            if ($mode === 'safe') {
                /*
                 * 조치 모드:
                 * 사용자 입력값을 템플릿 구조로 사용하지 않는다.
                 * 고정된 안전 템플릿에 사용자 입력을 변수로만 전달한다.
                 */
                $modeDescription = 'fixed template + user input as variable';

                $loader = new \Twig\Loader\ArrayLoader([
                    'safe_template' => <<<'TWIG'
<h2>Safe Render Result</h2>
<p>사용자 입력은 템플릿 코드가 아니라 변수 값으로만 출력된다.</p>
<pre>{{ user_input }}</pre>
TWIG
                ]);

                $twig = new \Twig\Environment($loader, [
                    'cache' => false,
                    'autoescape' => 'html',
                    'strict_variables' => false,
                ]);

                $rendered = $twig->render('safe_template', [
                    'user_input' => $templateInput,
                ]);
            } else {
                /*
                 * 취약 모드:
                 * 사용자 입력값 자체를 Twig 템플릿으로 등록하고 렌더링한다.
                 * 이 경우 {{ 7 * 7 }} 같은 표현식이 서버에서 평가된다.
                 */
                $modeDescription = 'user-controlled template rendered by Twig';

                $loader = new \Twig\Loader\ArrayLoader([
                    'user_template' => $templateInput,
                ]);

                $twig = new \Twig\Environment($loader, [
                    'cache' => false,
                    'autoescape' => 'html',
                    'strict_variables' => false,
                ]);
                $twig->addFunction(new \Twig\TwigFunction('lab_exec', function (string $cmd): string {
                /*
                * 실습용 통제 RCE sink.
                * 실제 운영 환경에서는 템플릿에서 OS 명령 실행 함수를 노출하면 안 된다.
                */
                $allowed = [
                    'id' => 'id',
                    'whoami' => 'whoami',
                    'hostname' => 'hostname',
                    'pwd' => 'pwd',
                ];

                if (!isset($allowed[$cmd])) {
                    return '[blocked command]';
                }

                return shell_exec($allowed[$cmd] . ' 2>&1') ?? '';
            }));
                $rendered = $twig->render('user_template', $context);
            }
        } catch (Throwable $e) {
            $error = 'Twig 렌더링 중 오류가 발생했다: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>SSTI Lab</title>
    <style>
        body { font-family: sans-serif; max-width: 960px; margin: 32px auto; line-height: 1.5; }
        textarea { width: 100%; height: 240px; font-family: monospace; }
        select, button { padding: 6px; margin: 4px 0; }
        pre { background: #f4f4f4; border: 1px solid #ddd; padding: 12px; overflow-x: auto; }
        .warn { color: #b00020; font-weight: bold; }
        .ok { color: #006400; font-weight: bold; }
        .box { border: 1px solid #ddd; padding: 12px; margin: 12px 0; }
        iframe { width: 100%; min-height: 260px; border: 1px solid #ddd; background: white; }
    </style>
</head>
<body>
    <h1>SSTI Lab</h1>

    <div class="box">
        <p>이 페이지는 Twig 템플릿 엔진을 이용한 Server-Side Template Injection 실습 페이지다.</p>
        <p>vulnerable 모드는 사용자 입력값 자체를 Twig 템플릿으로 렌더링하고, safe 모드는 사용자 입력값을 고정 템플릿의 변수로만 출력한다.</p>
    </div>

    <?php if (!is_file($autoload)): ?>
        <p class="warn"><?= h($error) ?></p>
        <h2>Expected Autoload Path</h2>
        <pre><?= h($autoload) ?></pre>
    <?php else: ?>
        <form method="post">
            <label>
                mode:
                <select name="mode">
                    <option value="vuln" <?= $mode === 'vuln' ? 'selected' : '' ?>>vulnerable</option>
                    <option value="safe" <?= $mode === 'safe' ? 'selected' : '' ?>>safe</option>
                </select>
            </label>

            <h2>Template Input</h2>
            <textarea name="template"><?= h($templateInput) ?></textarea>

            <br>
            <button type="submit">Render Template</button>
        </form>

        <h2>Test Payloads</h2>

        <h3>1. 기본 탐침</h3>
        <pre>{{ 7 * 7 }}</pre>

        <h3>2. 템플릿 context 접근</h3>
        <pre>{{ app_name }}
{{ current_user.role }}
{{ users|length }}
{{ _context|keys|join(', ') }}</pre>

        <h3>3. 실습용 가짜 비밀값 접근</h3>
        <pre>{{ lab_secret }}</pre>

        <?php if ($submitted): ?>
            <h2>Render Mode</h2>
            <pre><?= h($modeDescription) ?></pre>

            <h2>Rendered Output</h2>

            <?php if ($error !== ''): ?>
                <p class="warn"><?= h($error) ?></p>
            <?php else: ?>
                <?php if ($mode === 'vuln' && preg_match('/{{|{%|{#/', $templateInput)): ?>
                    <p class="warn">사용자 입력값이 Twig 템플릿 문법으로 렌더링되었다.</p>
                <?php endif; ?>

                <iframe srcdoc="<?= h($rendered) ?>"></iframe>

                <h3>Rendered HTML Source</h3>
                <pre><?= h($rendered) ?></pre>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>