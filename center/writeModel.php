<?php
session_start();
$id = $_SESSION['id'];
if($id == ""){
    echo "<script>location.href='/member/login.php'; </script>";
    exit;
}
$date = date('Y-m-d');
$subject = $_POST['subject'];
$content = $_POST['content'];
// 파일 업로드를 하게 되면 form 태그 속성에 enctype="multipart/form-data"를 지정, 그럼 $_FILES 에 파일 이름 존재함.
//$upfile = $_POST['upfile'];
$upfile = $_FILES['upfile']['name'];
$tmp_file = $_FILES['upfile']['tmp_name'];
// echo "subject : " . $subject . "<br>";
// echo "content : " . $content . "<br>";
// echo "upfile : " . $upfile . "<br>";
// echo "tmp_file : " . $tmp_file . "<br>";

// 데이터 베이스 연결 (데이터베이스 아이피주소, 데이터베이스 계정명, 패스워드, 데이터베이스 이름)
$link = mysqli_connect("localhost", "care", "123123", "care") or die('연결 실패');

$query = "INSERT INTO center(id, subject, content, date, hit, filename) ";
$query = $query . "VALUES('$id','$subject', '$content', '$date', 0, '$upfile')";
mysqli_query($link, $query);
mysqli_close($link);

if(is_uploaded_file($tmp_file)){
    $destination = "../data/" . $upfile;
    move_uploaded_file($tmp_file, $destination);
}

?>
<script>location.href='list.php'; </script>






















