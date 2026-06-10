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
			// 세션을 사용하기 위해서 실행.
    			session_start();
    			if($_SESSION['id'] == ""){
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