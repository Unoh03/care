<?php
    session_start();
    $id = $_SESSION['id'];
    
    if($id == ""){
       echo "<script>location.href='/member/login.php';</script>";
       exit;
    }

    /*
     * [15. 파일 다운로드 방어 코드 - 취약 상태 캡처 전까지 주석 유지]
     *
     * PDF 조치 기준:
     * - 다운로드 요청 파일명이 실제 허용된 파일인지 서버에서 검증한다.
     * - ../, ..\, URL 인코딩, 널바이트 같은 경로 추적 입력을 차단한다.
     * - realpath()로 최종 파일 경로를 정규화한 뒤, 지정된 다운로드 디렉터리 안에 있는지 확인한다.
     * - DB에 등록된 첨부파일명과 요청 파일명을 대조한다.
     *
     * 조치 적용 방법:
     * 1. 아래 취약 원본 블록($filename = $_GET['filename']; ... fclose($handle);)을 주석 처리한다.
     * 2. 이 방어 블록의 주석을 해제한다.
     * 3. 정상 첨부파일 다운로드는 유지되고, filename=../fd-proof.txt 같은 경로 이탈 요청은 차단되는지 확인한다.
     */
    
    $requested = $_GET['filename'] ?? '';

    if($requested === '' || preg_match('/[\/\\\\%\\x00]/', $requested) || strpos($requested, '..') !== false){
        http_response_code(400);
        echo 'invalid file name';
        exit;
    }

    if(! preg_match('/^[A-Za-z0-9._-]+$/', $requested)){
        http_response_code(400);
        echo 'invalid file name';
        exit;
    }

    $downloadDir = realpath(__DIR__ . '/../data');
    $filePath = $downloadDir === false ? false : realpath($downloadDir . DIRECTORY_SEPARATOR . $requested);

    if($downloadDir === false || $filePath === false || strpos($filePath, $downloadDir . DIRECTORY_SEPARATOR) !== 0){
        http_response_code(404);
        echo 'file not found';
        exit;
    }

    require_once __DIR__ . '/../config.php';
    $link = care_db_connect() or die('연결 실패');

    $stmt = mysqli_prepare($link, 'SELECT COUNT(*) FROM center WHERE filename = ?');
    mysqli_stmt_bind_param($stmt, 's', $requested);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $fileCount);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($link);

    if((int)$fileCount < 1){
        http_response_code(403);
        echo 'download denied';
        exit;
    }

    header('X-Content-Type-Options: nosniff');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Content-Length: ' . filesize($filePath));

    readfile($filePath);
    exit;
    
    // 취약 원본 코드 블록
    // $filename = $_GET['filename'];
    // header("content-disposition: attachment; filename = " . $filename);
    
    // $filename = "../data/" . $filename;
    // $handle = fopen($filename, "r");
    // fpassthru($handle);
    // fclose($handle);
?>
