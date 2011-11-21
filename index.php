<?php
	include('MustacheApi.php');

	$api = new MustacheApi('27a6948ead91e2655f8d1f7bc668c4da', '6cd1e2f90b6d3ad5860a3913de0efdeb');

	/*$api->detectFaces("https://fbcdn-sphotos-a.akamaihd.net/hphotos-ak-snc7/298727_2390653918077_1002727704_32276620_1195273975_n.jpg");*/
	$api->mustacheFromUrl('https://fbcdn-sphotos-a.akamaihd.net/hphotos-ak-ash4/297569_156612497765528_100002503369879_270848_294205784_n.jpg', 3);
?>