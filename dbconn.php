<?php
    //데이터베이스 연결(데이터베이스 위치(ip), 데이터베이스 id, 데이터베이스 암호, 데이터베이스 이름);
    $link = mysqli_connect("localhost", "care","123123", "care") or die('연결 실패');
    
    $id = 'admin';
    $query = "SELECT * FROM member WHERE id='$id'";
    
    // 연결되어 있는 데이터베이스로 쿼리문 전달 후 결과를 반환하는 함수
    $result = mysqli_query($link, $query);
    
    // SELECT 명령 후 결과 값의 행의 개수를 반환.
    $num = mysqli_num_rows($result);
    echo "조회한 데이터의 개수 : " . $num . "<br>";
    
    // SELECT 명령 후 결과 값을 반환.
    $row = mysqli_fetch_assoc($result);
    
    echo "id : ". $row['id']. "<br>";
    echo "pw : ". $row['pw']. "<br>";
    echo "name : ". $row['name']. "<br>";
  
    //데이터 베이스와 연결 종료(커넥션 또는 세션은 제한이 있음)
    mysqli_close($link);
    
?>

















