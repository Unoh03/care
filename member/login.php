<?php
include '../header.php';
?>

<div id="img_mem"></div>
<nav id="nav_sub">
	<ul>
		<li><a href="register.php">회원 가입</a></li>
		<li><a href="modify.php">회원 수정</a></li>
		<li><a href="delete.php">회원 탈퇴</a></li>
	</ul>
</nav>
<script>
	function check(){
		var id = document.getElementById('id').value;
		var pw = document.getElementById('pw').value;
		
		if(id == "" || pw == ""){
			alert('아이디 / 비밀번호를 입력하세요.');
			return;
		}
		
		document.getElementById('form_login').submit();
		
	}
</script>
<article id="article_sub">
	<h1>로그인</h1>
	
	<form action="loginModel.php" method="post" id="form_login" name="f">
		<label>아이디</label><input type="text" name="id" id="id"><div class="clear"></div>
		<label>패스워드</label><input type="password" name="pw" id="pw"><div class="clear"></div>
		
		<div id="buttons_mem">
			<input type="button" value="로그인" class="submit_mem" onclick="check()">
			<input type="button" value="취소" class="cancel_mem"
			onclick="javascript:location.href='/index.php'">
		</div>
	</form>
</article>
<?php include '../footer.php';?>









