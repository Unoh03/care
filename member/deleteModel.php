<?php
// delete.php에서 전달한 데이터를 받아서 변수에 저장.
session_start();
$id = $_SESSION['id'];
$pw = $_POST['pw'];
$pwCheck = $_POST['pwCheck'];

// 전달받은 데이터 검증.
// 검증 : 데이터가 입력되어 있는지, 두 패스워드가 같은지 검증
if(! ($pw and $pwCheck)){
?>
	<script>
		alert('두 비밀번호는 필수 정보입니다.'); history.go(-1);
	</script>
<?php
    exit;
}
if($pw != $pwCheck){
?>    
	<script>alert('두 비밀번호를 일치하여 입력하세요'); history.go(-1);</script>
<?php 
    exit;
}

// 데이터 베이스에 연결
$link = mysqli_connect("localhost", "care", "123123", "care") or die('연결 실패');

// 데이터베이스에 저장된 패스워드와 사용자가 입력한 패스워드를 같은지 확인.
$query = "SELECT pw FROM member WHERE id='$id'";
$result = mysqli_query($link, $query);
$row = mysqli_fetch_assoc($result);

if($pw == $row['pw']){
    $query = "DELETE FROM member WHERE id='$id'";
    // 위 쿼리문을 담고 있는 변수를 데이터베이스에 전달.
    mysqli_query($link, $query);
    // 데이터베이스 연결 닫기
    mysqli_close($link);
}else{
?> 
	<script>alert('저장된 비밀번호와 일치하여 입력하세요'); history.go(-1);</script>
<?php    
    mysqli_close($link);
    exit;
}
?>
<!-- 스크립트를 구성해서 회원삭제 출력 후 logout.php로 이동. -->
<script>alert('회원 삭제'); location.href='logout.php';</script>








