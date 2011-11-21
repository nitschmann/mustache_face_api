<?php
	//definde path to mustaches
	define('MUSTACHES', 'public/img/mustaches/');
	//includes
	include_once('lib/FaceRestClient.php');
	include_once('lib/WideImage/WideImage.php');

	class MustacheApi {

		private $FaceClient;
		private $WideImage;
		//Mustache images
		public $mustache_images;

		public function __construct($api_key, $api_secret) {
			//Create new FaceRestClient() Instance
			$this->FaceClient = new FaceRestClient($api_key, $api_secret);
			//Create new WideImage() Instance
			$this->WideImage = new WideImage();
			//Get mustaches
			$this->mustache_images = array();
			$dir = opendir(MUSTACHES);
			$i = 1;
			while($file = readdir($dir)) {
				if($file != '.' && $file != '..' && !is_dir($file)) {
					$type = explode(".",$file);
					if($type['1'] == 'png' || $type['1'] == 'gif') {
						$this->mustache_images[$i] = MUSTACHES.$file;
						//raise $i
						$i++;
					}
				}
			}	
			if(count($this->mustache_images) < 1) {
				echo 'No mustache images found!';
			}
		}

		public function mustacheFromUrl($url, $mustache) {
			$img = $url;
			$face_result = $this->FaceClient->faces_detect($img);
			//New Image Infos
			$new_img_info = array(
				'width' => $face_result['photos']['0']['width'],
				'height' => $face_result['photos']['0']['height']
			);
		}

		
	}
?>