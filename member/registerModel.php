<?php
// register.php에서 전달한 데이터를 변수에 저장
$id = $_POST['id'];
$pw = $_POST['pw'];
$pwCheck = $_POST['pwCheck'];
$name = $_POST['name'];
$mobile = $_POST['mobile'];
$address = $_POST['address'];
$email= $_POST['email'];
$date = date('Y-m-d');

if( ! ($id and $pw and $pwCheck and $name)){
    echo "<script>alert('필수 정보를 입력하세요'); history.go(-1); </script>";
    exit;
}

if($pw != $pwCheck){
    echo "<script>alert('입력한 패스워드가 동일하지 않습니다.'); history.go(-1); </script>";
    exit;
}

//데이터베이스 연결(데이터베이스 위치(ip), 데이터베이스 id, 데이터베이스 암호, 데이터베이스 이름);
require_once __DIR__ . '/../config.php';
$link = care_db_connect() or die('연결 실패');

/*
 * # 아이디 중복 체크 #
 * 데이터베이스에 아이디를 갖고 조회 후
 * 행의 개수가 0이면 중복 없음.
 * 행의 개수가 1이면 이미 가입한 회원이 있음.
 */

// 연결되어 있는 데이터베이스로 쿼리문 전달 후 결과를 반환하는 함수
$query = "SELECT * FROM member WHERE id='$id'";
$result = mysqli_query($link, $query);

// SELECT 명령 후 결과 값의 행의 개수를 반환.
$num = mysqli_num_rows($result);

if($num == 0){
    
    // INSERT INTO member(id, pw, name, mobile, address, email, date)
    //-> VALUES('test','123', '테스트', '010-1234-1234', '서울', 'test@', '2022-03-19')
    $query = "INSERT INTO member(id, pw, name, mobile, address, email, date) ";
    $query = $query . "VALUES('$id','$pw', '$name', '$mobile', '$address', '$email', '$date')";
    
    // 연결되어 있는 데이터베이스로 쿼리문 전달 후 결과를 반환하는 함수
    mysqli_query($link, $query);
    echo "<script>alert('가입 완료'); location.href='/index.php'; </script>";
}else{
    echo "<script>alert('이미 가입되어 있는 계정입니다.'); history.go(-1); </script>";
}

mysqli_close($link);
?>












