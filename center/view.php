<?php 
    include '../header.php'; 
    // list.php에서 전달한 데이터를 받아서 변수에 저장.
    $num = $_GET['num'];
    
    // 데이터 베이스에 연결
    require_once __DIR__ . '/../config.php';
	$link = care_db_connect() or die('연결 실패');
    
    // SELECT 쿼리문 문자열 변수에 저장.
    $query = "SELECT * FROM center WHERE num='$num'";
    // 데이터베이스에 쿼리문 문자열 전달
    $result = mysqli_query($link, $query);
    
    // 조회한 데이터를 변수에 담기.
    $row = mysqli_fetch_assoc($result);
    $subject = $row['subject'];
    $content = $row['content'];
    $id = $row['id'];
    $date = $row['date'];
    $filename = $row['filename'];
    
    $hit = $row['hit'];
    $hit++;
    
    $query = "UPDATE center SET hit='$hit' WHERE num='$num'";
    mysqli_query($link, $query);
    // 데이터베이스 연결 닫기
    mysqli_close($link);
?>
<div id="img_cen"></div>
<nav id="nav_sub">
	<ul>
		<li><a href="list.php">게시글 목록</a></li>
		<li><a href="write.php">게시글 작성</a></li>
		<li><a href="delete.php">게시글 삭제</a></li>
		<li><a href="modify.php">게시글 수정</a></li>
	</ul>
</nav>
<article id="article_sub">
	<h1>게시글 보기</h1>
		<div id="view_title">
			<div class="title1"> <?=$subject?> </div>
			<div><?=$id?> | <?=$date?> | 조회 수: <?=$hit?> </div>
		</div>
		
		<div id="view_content">
			<?=$content?> 
		</div>
		
		<div id="view_file">
		첨부파일 : <a href="download.php?filename=<?=$filename?>"><?=$filename?> </a>
		</div>
		
		<div id="buttons">
			<a href="write.php"><img src="/images/write.png"></a>
			<a href="modify.php"><img src="/images/modify.png"></a>
			<a href="delete.php"><img src="/images/delete.png"></a>
			<a href="list.php"><img src="/images/list.png"></a>
		</div>
</article>

<?php include '../footer.php';?>