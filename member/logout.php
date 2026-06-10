<?php
session_start();
session_destroy();
?>
<script>
	alert('로그 아웃');
	location.href='/index.php';
</script>

