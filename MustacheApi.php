<?php
	/*
	 * Mustache Face API - A PHP Class libary to create mustaches on faces in every image
	 *
	 * A PHP libary that creates mustaches on every face in a picture checked through the face.com API. 
	 * It uses the WideImage PHP Libary (http://wideimage.sourceforge.net/) and Face.com Rest API PHP Library.
	 *
	 * @author Florian Nitschmann (info@florian-nitschmann.de)
	 * @links www.florian-nitschmann.de
	 * @copyright (C) 2011 Florian Nitschmann
	 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)  
	 */
	//Check for GD-Libary
	if(!function_exists('gd_info')) throw new Exception('Mustache Face API requires the PHP GD extension.');
	//includes
	include_once('lib/FaceRestClient.php');
	include_once('lib/WideImage/WideImage.php');

	class MustacheApi {

		private $FaceClient;
		private $WideImage;
		//Mustache images
		public $mustache_images;


		public function __construct($api_key, $api_secret, $image_folder = null) {
			//Create new FaceRestClient() Instance
			$this->FaceClient = new FaceRestClient($api_key, $api_secret);
			//Create new WideImage() Instance
			$this->WideImage = new WideImage();
			//Get mustaches
			$this->mustache_images = array();
			//Folder with mustache images
			if($image_folder == null) $path = dirname(__FILE__).DS.'public'.DS.'img'.DS.'mustaches'.DS; 
			else $path = $image_folder;

			$dir = opendir($path);
			$i = 1;
			while($file = readdir($dir)) {
				if($file != '.' && $file != '..' && !is_dir($file)) {
					$type = explode(".",$file);
					if($type['1'] == 'png') {
						$this->mustache_images[$i] = $path.$file;
						//raise $i
						$i++;
					}
				}
			}	
			if(count($this->mustache_images) < 1) {
				echo 'No mustache images found!';
			}
		}

		
		public function mustacheFromUrl($url, $mustache_type = 1) {
			if($url != '') {
				$face_result = $this->FaceClient->faces_detect($url);
				//New Image Infos
				$new_img_info = array(
					'width' => $face_result['photos']['0']['width'],
					'height' => $face_result['photos']['0']['height'],
					'type' => getimagesize($url)
				);
				//Create new WideImage
				$new_img = WideImage::load($url)->resize($new_img_info['width'], $new_img_info['height']);
				//Return WideImage Object with mustache
				return $this->mustachePic($new_img, $new_img_info, $face_result, $mustache_type);
			}
			else return false;
		}
		

		public function mustachePicHtml($mustache_obj, $w = null, $h = null) {
			if($mustache_obj != null) {
				if($w > 0 && $h > 0) $img = $mustache_obj->resize($w, $h);
				else $img = $mustache_obj;
				$img = imagecreatefromstring($img->asString('jpg')); 

				$img = base64_encode(WideImage::load($img));
				$string = 'data:image/jpeg;base64,'.$img;
				return $string;
			}
			else return null;
		}


		public function mustacheOutputPic($mustache_obj, $w = null, $h = null) {
			if($mustache_obj != null) {
				if($w > 0 && $h > 0) $img = $mustache_obj->resize($w, $h);
				else $img = $mustache_obj;
				//output image as jpeg
				$img->output('jpg');
			}
			else return null; 
		}


		public function mustachePicRaw($mustache_obj,$w = null, $h = null) {
			if($mustache_obj != null) {
				if($w > 0 && $h > 0) $img = $mustache_obj->resize($w, $h)->asString('jpg');
				else $img = $mustache_obj->asString('jpg');
				//return string
				return $img;
			}
			else return null;	
		}

		public function imgStringToObj($img_string = null) {
			if($img_string != null) {
				$mustache_obj = WideImage::load($img_string);
				return $mustache_obj;
			}	
			else return null;
		}

		protected function mustachePic($img, $img_info = array(), $face_result = array(), $mustache_type) {
			if($img != '' && is_array($img_info) && is_array($face_result) && $mustache_type != '') {
				$mime = $img_info['type']['mime'];
				
				//Create pic extension based $mime
				$extension;
				if($mime = "image/jpeg") $extension = 'jpeg';
				else if($mime = "image/png") $extension = 'png';
				else if($mime = "image/gif") $extension = 'gif';
				
				//create new image
				$img = imagecreatefromstring($img);
				$img_info = $img_info['type'];
				
				//mustache type
				if(is_numeric($mustache_type) && $mustache_type <= count($this->mustache_images)) {
					$mustache = $this->mustache_images[$mustache_type];
				}
				else {
					if($mustache_type == 'r' || $mustache_type > count($this->mustache_images)) {
						$r = rand(1, count($this->mustache_images));
						$mustache = $this->mustache_images[$r];
					}
					else $mustache = $this->mustache_images[$r]; 
				} 
				$mustache_info = getimagesize($mustache);

				//faces
				$tags = $face_result['photos']['0']['tags'];
				if($tags > 0) {
					foreach($tags as $tag) {
						//resize and rotate each mustache
						$x_r = round($tag['mouth_right']['x']);
						$x_l = round($tag['mouth_left']['x']);
						
						$mouth_w = ((($x_r-$x_l)*($img_info['0']/100))+20);
			
						$new_mustache_p = round($mouth_w/($mustache_info['0']/100));
						$mouth_h = ($mustache_info['1']/100)*$new_mustache_p;

						$rotation = $tag['roll'];

						$new_mustache = WideImage::load($mustache)->resize($mouth_w, $mouth_h)->rotate($rotation);
						
						//create new and modified mustache
						$mustache_img = imagecreatefromstring($new_mustache->asString('png'));
						$mustache_new_info = array(
							'w' => imagesx($mustache_img),
							'h' => imagesy($mustache_img)
						);
						
						//mustache position on pic
						$des_x = ($tag['mouth_left']['x']*($img_info[0]/100))-8;
						
						$des_y;

						if($tag['mouth_left']['y'] > $tag['mouth_center']['y'] || $tag['mouth_right']['y'] > $tag['mouth_center']['y']) {
							if($tag['mouth_left']['y'] > $tag['mouth_right']['y']) {
								$des_y = (($tag['mouth_left']['y']-(($tag['mouth_left']['y']-$tag['nose']['y'])/1.2))*($img_info[1]/100));
							}
							else {
								$des_y = (($tag['mouth_right']['y']-(($tag['mouth_left']['y']-$tag['nose']['y'])/1.2))*($img_info[1]/100));
							}
						}
						else {
							$des_y = (($tag['mouth_center']['y']-(($tag['mouth_left']['y']-$tag['nose']['y'])/1.2))*($img_info[1]/100));
						}
							
						//insert mustaches on pic
						imagecopy($img, $mustache_img, $des_x, $des_y, 0, 0, $mustache_new_info['w'], $mustache_new_info['h']);
					}
					//change pic in jpeg format and return a WideImage Object
					return WideImage::load($img);
				}
				else return null;
 			}
			else return null;
		} 

		
	}
?>