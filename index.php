<?php
	/*
	 * A easy example to show how it works 
	 */
	include('MustacheApi.php');
	$api = new MustacheApi('27a6948ead91e2655f8d1f7bc668c4da', '6cd1e2f90b6d3ad5860a3913de0efdeb');
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Mustache API</title>
		<style type="text/css">
			body {width: 100%; overflow: hidden;}
			#box_r {float: right; width: 50%;}
			#box_l {float: left; width: 50%; overflow: scroll;}
		</style>
	</head>
	<body>
		<div id="box_r">
			<center>
				<h1 style="color:blue;">Mustache API for Photos</h1>
				<p><h3><i>This PHP Libary creates mustaches for every face in a picture, which is checked over the <a href="http://face.com/" target="_blank">Face.com API</a></i></h3></p>
				<p><u><b>Try it out:</b></u></p>
				<p>
					<form method="post" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>">
					Image-URL:<br><input type="text" name="url"><br>
					<input type="submit" name="Button" value="Abschicken">
					</form>
				</p>
			</center>
		</div>
		<div id="box_l">
			<center>
				<?php if($_POST['url'] != ''): ?>
					<?php
						$url = $_POST['url'];
						$mustache = $api->mustacheFromUrl($url, 'r');
						$img = $api->mustachePicHtml($mustache);
					?>
					<img src="<?php echo $img; ?>" alt="generated">
				<?php else: ?>
					<img src="public/img/example_2.jpg" alt="Merkel">
				<?php endif; ?>
			</center>
		</div>
	</body>
</html>