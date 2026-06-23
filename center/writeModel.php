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
$upfile = $_POST['upfile'];

/*
 * [14. 악성 파일 업로드 방어 코드 1~3 - 취약 상태 캡처 전까지 주석 유지]
 *
 * 노트 대응:
 * - 웹 취약점 14번 노트 `PDF 조치 1`: 파일명 정규화와 위험 문자 제거
 * - 웹 취약점 14번 노트 `PDF 조치 2`: 확장자, MIME Type, 파일 크기 화이트리스트 검증
 * - 웹 취약점 14번 노트 `PDF 조치 3`: 서버 저장 파일명 난수화
 *
 * PDF 조치 기준:
 * 1. 파일명은 디코딩/정규화 후 특수문자, 경로 구분자, 널바이트를 제거한다.
 * 2. 확장자, MIME Type, 파일 크기를 화이트리스트 방식으로 제한한다.
 * 3. 서버 저장 파일명은 사용자가 보낸 원본 파일명이 아니라 난수화된 이름으로 저장한다.
 *
 * 조치 적용 방법:
 * 1. 아래 원본 $upfile, $tmp_file 대입 코드를 주석 처리한다.
 * 2. 이 방어 블록 주석을 해제한다.
 * 3. 하단 move_uploaded_file() 부분의 방어 블록도 함께 주석 해제한다.
 */

$upfile = '';
$tmp_file = '';

// PDF 조치 2: 확장자, MIME Type, 파일 크기 화이트리스트 기준 정의
$allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'pdf');
$allowedMimeTypes = array(
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/pdf'
);
$maxUploadSize = 2 * 1024 * 1024;

$uploadError = $_FILES['upfile']['error'] ?? UPLOAD_ERR_NO_FILE;

if($uploadError !== UPLOAD_ERR_NO_FILE){
    // PDF 조치 2: 업로드 처리 오류 확인
    if($uploadError !== UPLOAD_ERR_OK){
        echo "<script>alert('파일 업로드 중 오류가 발생했습니다.'); history.go(-1); </script>";
        exit;
    }

    // PDF 조치 2: 파일 크기 제한
    if($_FILES['upfile']['size'] > $maxUploadSize){
        echo "<script>alert('파일 크기는 2MB 이하만 허용됩니다.'); history.go(-1); </script>";
        exit;
    }

    // PDF 조치 1: 파일명 정규화와 위험 문자 제거
    $originalName = $_FILES['upfile']['name'];
    $normalizedName = basename(str_replace("\0", '', $originalName));
    $normalizedName = preg_replace('/[^A-Za-z0-9._-]/', '_', $normalizedName);

    // PDF 조치 1/2: 정규화된 파일명에서 확장자 추출
    $extension = strtolower(pathinfo($normalizedName, PATHINFO_EXTENSION));

    // PDF 조치 2: 확장자 화이트리스트 검증
    if(! in_array($extension, $allowedExtensions, true)){
        echo "<script>alert('허용되지 않는 파일 확장자입니다.'); history.go(-1); </script>";
        exit;
    }

    $tmp_file = $_FILES['upfile']['tmp_name'];

    // PDF 조치 2: MIME Type 검증
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmp_file);

    if(! in_array($mimeType, $allowedMimeTypes, true)){
        echo "<script>alert('허용되지 않는 파일 형식입니다.'); history.go(-1); </script>";
        exit;
    }

    // PDF 조치 3: 서버 저장 파일명 난수화
    $upfile = bin2hex(random_bytes(16)) . '.' . $extension;
}


$upfile = $_FILES['upfile']['name'];
$tmp_file = $_FILES['upfile']['tmp_name'];
// echo "subject : " . $subject . "<br>";
// echo "content : " . $content . "<br>";
// echo "upfile : " . $upfile . "<br>";
// echo "tmp_file : " . $tmp_file . "<br>";

// 데이터 베이스 연결 (데이터베이스 아이피주소, 데이터베이스 계정명, 패스워드, 데이터베이스 이름)
require_once __DIR__ . '/../config.php';
$link = care_db_connect() or die('연결 실패');

$query = "INSERT INTO center(id, subject, content, date, hit, filename) ";
$query = $query . "VALUES('$id','$subject', '$content', '$date', 0, '$upfile')";
/*
 * [20. 자동화 공격 방어 코드 - PDF 조치 1-2]
 *
 * PDF 조치 1:
 * 로그인 시도, 게시글 등록, 본인인증 요청에 대해 횟수 제한 또는 CAPTCHA 등
 * 일회성 확인 로직을 구현한다.
 *
 * 적용 위치:
 * D:\care\center\writeModel.php
 * DB 연결 직후, center 테이블 INSERT 전에 배치한다.
 *
 * 실습 전에는 취약 상태 캡처를 위해 주석으로 유지한다.
 */

/*
$now = time();
$postCooldown = 10;
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

$stmt = mysqli_prepare(
    $link,
    "SELECT last_post_at FROM post_rate_limit WHERE id = ? AND ip = ?"
);
mysqli_stmt_bind_param($stmt, "ss", $id, $clientIp);
mysqli_stmt_execute($stmt);
$rateResult = mysqli_stmt_get_result($stmt);
$rateRow = mysqli_fetch_assoc($rateResult);
mysqli_stmt_close($stmt);

if($rateRow){
    $lastPostAt = (int)$rateRow['last_post_at'];

    if(($now - $lastPostAt) < $postCooldown){
        echo "<script>alert('게시글은 10초에 한 번만 등록할 수 있습니다.'); history.go(-1); </script>";
        exit;
    }

    $stmt = mysqli_prepare(
        $link,
        "UPDATE post_rate_limit SET last_post_at = ? WHERE id = ? AND ip = ?"
    );
    mysqli_stmt_bind_param($stmt, "iss", $now, $id, $clientIp);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}else{
    $stmt = mysqli_prepare(
        $link,
        "INSERT INTO post_rate_limit(id, ip, last_post_at) VALUES(?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "ssi", $id, $clientIp, $now);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
*/
mysqli_query($link, $query);
mysqli_close($link);

if(is_uploaded_file($tmp_file)){
    /*
     * [14. 악성 파일 업로드 방어 코드 1~3 - 취약 상태 캡처 전까지 주석 유지]
     *
     * 노트 대응:
     * - 웹 취약점 14번 노트 `PDF 조치 1`: 업로드 경로 고정
     * - 웹 취약점 14번 노트 `PDF 조치 3`: 난수 파일명으로 저장
     *
     * 위쪽 방어 블록으로 검증과 난수 파일명 생성이 끝났다는 전제에서 사용한다.
     * realpath()로 업로드 디렉터리를 고정하고, 최종 저장 경로가 해당 디렉터리 밖으로 벗어나지 않게 한다.
     */

    // PDF 조치 1/3: 업로드 디렉터리 고정 후 난수 파일명으로 저장
    $uploadDir = realpath(__DIR__ . '/../data');
    if($uploadDir === false){
        echo "<script>alert('업로드 디렉터리를 확인할 수 없습니다.'); history.go(-1); </script>";
        exit;
    }

    $destination = $uploadDir . DIRECTORY_SEPARATOR . $upfile;
    if(! move_uploaded_file($tmp_file, $destination)){
        echo "<script>alert('파일 저장에 실패했습니다.'); history.go(-1); </script>";
        exit;
    }


    $destination = "../data/" . $upfile;
    move_uploaded_file($tmp_file, $destination);
}

/*
[14. 악성 파일 업로드 방어 코드 4 - 취약 상태 캡처 전까지 주석 유지]
노트 대응:
- 웹 취약점 14번 노트 `PDF 조치 4`: 업로드 디렉터리 실행 권한 제한
PDF 조치 기준:
4. 업로드 디렉터리에서 서버 사이드 스크립트가 실행되지 않도록 실행 권한을 제한한다.
Apache에서 조치할 경우 /data/.htaccess 또는 VirtualHost/Directory 설정에 아래와 같은 방어를 둔다.
실제 취약 상태 캡처 전에는 적용하지 않는다.
# PDF 조치 4: 업로드 디렉터리에서 PHP 계열 스크립트 실행 차단
<FilesMatch "\.(php|phtml|phar|php[0-9]?)$">
    Require all denied
</FilesMatch>
RemoveHandler .php .phtml .phar .php3 .php4 .php5 .php7 .php8
RemoveType .php .phtml .phar .php3 .php4 .php5 .php7 .php8
php_flag engine off
 */

?>
<script>location.href='list.php'; </script>
