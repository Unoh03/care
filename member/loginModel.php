<?php
// login.php에서 전달한 데이터를 변수에 저장


$id = $_POST['id'];
$pw = $_POST['pw'];

// echo "id : " . $id . "<br>";
// echo "pw : " . $pw . "<br>";

//데이터베이스 연결(데이터베이스 위치(ip), 데이터베이스 id, 데이터베이스 암호, 데이터베이스 이름);
require_once __DIR__ . '/../config.php';
$link = care_db_connect() or die('연결 실패');

$query = "SELECT * FROM member WHERE id='$id' and pw='$pw'";

// 연결되어 있는 데이터베이스로 쿼리문 전달 후 결과를 반환하는 함수
$result = mysqli_query($link, $query);

// SELECT 명령 후 결과 값의 행의 개수를 반환.
$num = mysqli_num_rows($result);

// 세션을 사용하기 위해서 실행.
session_start();

if($num == 1){
    // 로그인 성공
    $_SESSION['id'] = $id;
    // SELECT 명령 후 결과 값을 반환.
    $row = mysqli_fetch_assoc($result);
    $_SESSION['name'] = $row['name'];
    $_SESSION['mobile'] = $row['mobile'];
    $_SESSION['address'] = $row['address'];
    $_SESSION['email'] = $row['email'];
    $_SESSION['num'] = $row['num'];
    
}else{
    // 로그인 실패
?>
    <script>
    	alert('로그인 실패');
    	history.go(-1);
    </script>
<?php
    exit;
}

mysqli_close($link);
?>
<script>
	alert('로그인 성공');
	location.href='/index.php';
</script>















