<?php
// 웹 취약점 16번 PDF 조치 2: 세션 종료 시간 설정 및 자동 로그아웃 구현.
session_start();

$sessionTimeout = 600; // 실습용 10분

if (!empty($_SESSION['id'])) {
    if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $sessionTimeout) {
        $_SESSION = [];
        session_destroy();

        echo "<script>alert('세션이 만료되었습니다. 다시 로그인하세요.'); location.href='/member/login.php';</script>";
        exit;
    }

    $_SESSION['last_activity'] = time();
}
?>
<html>
<head>
	<title>index</title>
	<link type="text/css" rel="stylesheet" href="/css/main.css" >
	<link type="text/css" rel="stylesheet" href="/css/sub.css" >
</head>
<body>
	<div id="wrap"> 
		<header>
			<div class="login">
			<?php
			if(empty($_SESSION['id'])){
			?>
				<a href="/member/login.php"> Login </a>
				|
				<a href="/member/register.php"> Register </a>
			<?php }else{?>
				<a href="/member/logout.php"> Logout </a>
				|
				<a href="/member/modify.php"> Modify </a>
			<?php }?>
			</div>
			<div class="logo"> 
				<h1> <a href="/index.php">CARE</a></h1> 
			</div>
			<nav id="nav_index">
				<ul>
					<li><a href="/index.php"> HOME </a></li>
					<li><a href="#"> COMPANY </a></li>
					<li><a href="#"> MEMBER </a></li>
					<li><a href="/center/list.php"> CUSTOMER CENTER </a></li>
				</ul>
			</nav>
		</header>
