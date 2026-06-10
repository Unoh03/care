<?php
// 사용자의 입력 값을 전달 받아 변수에 저장
session_start();
if($_SESSION['id'] == ""){
    echo "<script>location.href='login.php';</script>";
    exit;
}
$id = $_SESSION['id'];

$pw = $_POST['pw'];
$pwCheck = $_POST['pwCheck'];
$name = $_POST['name'];
$mobile = $_POST['mobile'];
$address = $_POST['address'];
$email= $_POST['email'];

// 입력 값이 빈 데이터가 있는 지 검증.
if( ! ($pw and $pwCheck and $name)){
    echo "<script>alert('필수 정보를 입력하세요'); history.go(-1); </script>";
    exit;
}
// pw와 pwCheck의 변수의 값이 일치하는지 검증.
if($pw != $pwCheck){
    echo "<script>alert('입력한 패스워드가 동일하지 않습니다.'); history.go(-1); </script>";
    exit;
}

// 데이터 베이스 연결 (데이터베이스 아이피주소, 데이터베이스 계정명, 패스워드, 데이터베이스 이름)
require_once __DIR__ . '/../config.php';
$link = care_db_connect() or die('연결 실패');

// 업데이트 쿼리문 문자열 변수에 저장.
$query = "UPDATE member SET pw='$pw', name='$name', mobile='$mobile', 
email='$email', address='$address' WHERE id='$id'";

// 데이터베이스에 쿼리문 문자열 전달
mysqli_query($link, $query);

// 데이터베이스 연결 닫기
mysqli_close($link);
?>
<!-- 자바 스크립트를 이용해 alert 창 출력 및 logout.php로 이동. -->
<script>alert('수정 완료'); location.href='logout.php'; </script>










