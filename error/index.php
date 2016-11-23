<!DOCTYPE HTML>
<html>
	<head>
		<title>HTTP <?php echo $_GET["code"] ?></title>
		<style>
			body{
				background: black;
				text-align: center;
			}
		</style>
	</head>
	<body>
		<?php echo "<img src='https://httpstatusdogs.com/img/".$_GET["code"].".jpg'>" ?>
	</body>
</html>
