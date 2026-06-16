<?php
session_start();

// 웹 취약점 16번 PDF 조치 2: 로그아웃 시 서버 세션과 브라우저의 세션 쿠키를 함께 폐기한다.
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();
?>
<script>
	alert('로그 아웃');
	location.href='/index.php';
</script>
