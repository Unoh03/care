<?php include "../header.php"; ?>

<div id="img_mem"></div>
<nav id="nav_sub">
	<ul>
		<li><a href="register.php">회원 가입</a></li>
		<li><a href="modify.php">회원 수정</a></li>
		<li><a href="delete.php">회원 탈퇴</a></li>
	</ul>
</nav>
<script>
	// check함수 생성
	// id, pw, pwCheck, name 4개의 input에 데이터가 없으면 종료. 있으면 registerModel.php 로 이동
	function check(){
		var id = document.getElementById('id');
		var pw = document.getElementById('pw');
		var pwCheck = document.getElementById('pwCheck');
		var name = document.getElementById('name');
		
		if(id.value == "" ){
			alert('아이디를 입력하세요');
			id.focus();
			return;
		}
		if(pw.value == "" ){
			alert('비밀번호를 입력하세요');
			pw.focus();
			return;
		}
		if(name.value == ""){
			alert('이름을 입력하세요');
			name.focus();
			return;
		}
		if(pw.value != pwCheck.value){
			alert('두 비밀번호의 값을 확인하세요');
			pw.focus();
			return;
		}
		document.getElementById('form_reg').submit();
	}
</script>
<article id="article_sub">
<h1>회원 가입</h1>
	<form action="registerModel.php" method="post" name="f" id="form_reg">
		<fieldset class="fieldset_mem">
		<legend>필수 정보</legend>
			<label>아이디</label><input type="text" name="id" id="id" ><div class="clear"></div>
			<label>패스워드</label><input type="password" name="pw" id="pw"><div class="clear"></div>
			<label>패스워드 확인</label><input type="password" name="pwCheck" id="pwCheck"><div class="clear"></div>
			<label>이름</label><input type="text" name="name" id="name"><div class="clear"></div>
		</fieldset>
		<fieldset class="fieldset_mem">
		<legend>부가 정보</legend>
			<label>핸드폰</label><input type="text" name="mobile"><div class="clear"></div>
			<label>주소</label><input type="text" name="address"><div class="clear"></div>
			<label>이메일</label><input type="text" name="email"><div class="clear"></div>
		</fieldset>
		<div id="buttons_mem">
			<input type="button" value="회원 가입" class="submit_mem" onclick="check()">
			<input type="button" value="취소" class="cancel_mem" 
			onclick="javascript:location.href='/index.php'">
		</div>
	</form>
</article>
<?php include "../footer.php"; ?>











