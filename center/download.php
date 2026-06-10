<?php
    session_start();
    $id = $_SESSION['id'];
    
    if($id == ""){
       echo "<script>location.href='/member/login.php';</script>";
       exit;
    }
    
    $filename = $_GET['filename'];
    header("content-disposition: attachment; filename = " . $filename);
    
    $filename = "../data/" . $filename;
    $handle = fopen($filename, "r");
    fpassthru($handle);
    fclose($handle);
?>
