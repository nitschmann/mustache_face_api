<?php
	include('../MustacheApi.php');

	$api = new MustacheApi('27a6948ead91e2655f8d1f7bc668c4da', '6cd1e2f90b6d3ad5860a3913de0efdeb');

	/*$api->detectFaces("https://fbcdn-sphotos-a.akamaihd.net/hphotos-ak-snc7/298727_2390653918077_1002727704_32276620_1195273975_n.jpg");*/
$mustache = $api->mustacheFromUrl('https://fbcdn-sphotos-a.akamaihd.net/hphotos-ak-snc7/298727_2390653918077_1002727704_32276620_1195273975_n.jpg', 1);

echo '<img src="'.$api->mustachePicHtml($mustache).'">';
?>
<p>This is cool!</p>