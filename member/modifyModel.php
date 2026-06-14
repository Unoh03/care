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
// [10. 불충분한 인증 절차 방어 코드 - 취약 상태 캡처 전까지 주석 유지]
// modify.php의 현재 비밀번호 입력칸을 주석 해제한 뒤 함께 사용한다.
// $currentPw = $_POST['currentPw'] ?? '';

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

/*
 * [10. 불충분한 인증 절차 방어 코드 - 취약 상태 캡처 전까지 주석 유지]
 *
 * PDF 조치 기준:
 * - 개인정보 변경 같은 중요 정보 페이지 접근/수정 시 본인 인증을 재확인한다.
 * - 인증 여부는 클라이언트가 아니라 서버 사이드에서 검증한다.
 *
 * 현재 CARE는 교육용으로 비밀번호가 평문 저장되어 있으므로 아래 예시는 평문 비교를 사용한다.
 * 실무에서는 password_hash() / password_verify() 기반으로 바꾸는 것이 맞다.
 *
 * 조치 적용 방법:
 * 1. modify.php의 currentPw 입력칸 주석을 해제한다.
 * 2. 이 블록과 위쪽 $currentPw 대입 라인의 주석을 해제한다.
 * 3. 회원정보 수정 전 현재 비밀번호가 DB의 기존 비밀번호와 일치하는지 서버에서 확인한다.
 */
/*
if($currentPw == ''){
    echo "<script>alert('현재 비밀번호를 입력하세요'); history.go(-1); </script>";
    mysqli_close($link);
    exit;
}

$reauthQuery = "SELECT pw FROM member WHERE id='$id'";
$reauthResult = mysqli_query($link, $reauthQuery);
$reauthRow = mysqli_fetch_assoc($reauthResult);

if(! $reauthRow || $currentPw != $reauthRow['pw']){
    echo "<script>alert('현재 비밀번호가 일치하지 않습니다.'); history.go(-1); </script>";
    mysqli_close($link);
    exit;
}
*/

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









