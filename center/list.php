<?php

include "../header.php";
$link = mysqli_connect('localhost', 'care', '123123', 'care');
$mode = $_GET['mode'];
$find = $_GET['find'];
$data = $_GET['data'];
if($mode == "search"){
    if($data == ""){
        ?>
	<script>
		alert('검색어를 입력하세요');
		history.go(-1);
	</script>
	
<?php
        exit;
    }
    $query = "SELECT * FROM center WHERE $find like '%$data%'order by num desc";
}else{
    $query = "SELECT * FROM center order by num desc";
}
$result = mysqli_query($link, $query);
$totalNumRows = mysqli_num_rows($result);
$scroll = 3;
$totalPage = ceil($totalNumRows / $scroll);
$selectPage = $_GET['page'];
if(! $selectPage)
    $selectPage = 1;

$start = ($selectPage-1) * $scroll;
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
	<h1>게시글 목록</h1>
		<table id="table_list">
			<tr>
				<th>번호</th>
				<th class="title">제목</th>
				<th>작성자</th>
				<th>날짜</th>
				<th>조회수</th>
			</tr>
			<?php 
			for($i = $start; $i < ($scroll+$start) && $i < $totalNumRows ; $i++){ 
			    mysqli_data_seek($result, $i);
			    $row = mysqli_fetch_assoc($result);
			?>
    			<tr>
    				<td><?=$row['num']?></td>
    					<td class="subject">
    					<a href="view.php?num=<?=$row['num']?>"><?=$row['subject']?></a>
    				</td>
    				<td><?=$row['id']?></td>
    				<td><?=$row['date']?></td>
    				<td><?=$row['hit']?></td>
    			</tr>
    		<?php 
             }
             ?>
		</table>
		
		<form method="get" action="list.php" id="form_list">
			<select name="find">
				<option value="subject">제목</option>
				<option value="content">내용</option>
				<option value="id">작성자</option>
			</select>
			<input type="text" name="data">
			<input type="hidden" name="mode" value="search">
			<input type="submit" value="검색">
		</form>
		
		<div class="clear"></div>
		
		<div id="page_control">
			<?php 
			if($selectPage <= 1){
			?>
			    <a href="list.php?page=1">Prev</a>
			<?php 
			}else{
			    $selectPage--;
			?>
				 <a href="list.php?page=<?=$selectPage?>">Prev</a>
			<?php 
			     $selectPage++; 
			}
			
			for($i = 1; $i <= $totalPage; $i++){
			?>
			    <a href="list.php?page=<?=$i?>"><?=$i?></a>
			<?php 
			}
			if($selectPage >= $totalPage){
			?>
			    <a href="list.php?page=<?=$totalPage?>">Next</a>
			<?php 
			}else{
			    $selectPage++;
			?>
				 <a href="list.php?page=<?=$selectPage?>">Next</a>
			<?php 
			     $selectPage--; 
			}
			?>
		</div>
		
		<div id="buttons">
			<a href="write.php"><img src="/images/write.png"></a>
		</div>
		
</article>
<?php include "../footer.php"; ?>






