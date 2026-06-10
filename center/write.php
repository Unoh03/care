<?php 
include '../header.php';
if($_SESSION['id'] == ""){
    echo "<script>location.href='/member/login.php'; </script>";
    exit;
}
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
	<h1>게시글 작성</h1>
	<div class="line"></div>
		<form action="writeModel.php" method="post" enctype="multipart/form-data">
    		<table>
    			<tr id="write_tr1">
    				<td class="td1">작성자</td>
    				<td class="td2"><?=$_SESSION['id']?></td>
    			</tr>
    			<tr id="write_tr2">
    				<td class="td1">제목</td>
    				<td class="td2"><input type="text" name="subject"></td>
    			</tr>			
    			<tr id="write_tr3">
    				<td class="td1">내용</td>
    				<td class="td2"><textarea name="content"></textarea></td>
    			</tr>
    			<tr id="write_tr4">
    				<td class="td1">파일</td>
    				<td class="td2"><input type="file" name="upfile"></td>
    			</tr>
    		</table>
    
    		<div id="buttons">
    			<input type="image" src="/images/ok.png">
    			<a href="list.php"><img src="/images/list.png"></a>
    		</div>
		</form>
</article>

<?php include '../footer.php';?>
