<?php
// login.php에서 전달한 데이터를 변수에 저장


$id = $_POST['id'];
$pw = $_POST['pw'];

// echo "id : " . $id . "<br>";
// echo "pw : " . $pw . "<br>";

//데이터베이스 연결(데이터베이스 위치(ip), 데이터베이스 id, 데이터베이스 암호, 데이터베이스 이름);
require_once __DIR__ . '/../config.php';
$link = care_db_connect() or die('연결 실패');

// 취약 $query = "SELECT * FROM member WHERE id='$id' and pw='$pw'";
$query = "SELECT * FROM member WHERE id = ? AND pw = ?";

// Prepared Statement를 사용하여 SQL Injection 방지
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "ss", $id, $pw);
mysqli_stmt_execute($stmt);

// 연결되어 있는 데이터베이스로 쿼리문 전달 후 결과를 반환하는 함수
$result = mysqli_stmt_get_result($stmt);
// 취약$result = mysqli_query($link, $query);

// SELECT 명령 후 결과 값의 행의 개수를 반환.
$num = mysqli_num_rows($result);

// 세션을 사용하기 위해서 실행.
session_start();

if($num == 1){
    // 웹 취약점 16번 PDF 조치 1: 새 로그인 시 기존 세션 ID를 폐기하고 새로운 세션 ID를 발급한다.
    session_regenerate_id(true);

    // 로그인 성공
    $_SESSION['id'] = $id;
    // SELECT 명령 후 결과 값을 반환.
    $row = mysqli_fetch_assoc($result);
    $_SESSION['name'] = $row['name'];
    $_SESSION['mobile'] = $row['mobile'];
    $_SESSION['address'] = $row['address'];
    $_SESSION['email'] = $row['email'];
    $_SESSION['num'] = $row['num'];
    // 웹 취약점 16번 PDF 조치 2: 세션 만료 판단을 위해 마지막 활동 시간을 기록한다.
    $_SESSION['last_activity'] = time();
    
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














