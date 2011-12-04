<?php
	/*
	 * Mustache Face API - A PHP Class libary to create mustaches on faces in every image
	 *
	 * A PHP libary that creates mustaches on every face in a picture checked through the face.com API. 
	 * It uses the WideImage PHP Libary (http://wideimage.sourceforge.net/) and Face.com Rest API PHP Library.
	 *
	 * @author Florian Nitschmann (info@florian-nitschmann.de)
	 * @date 04-12-11 9:00:00pm
	 * @links www.florian-nitschmann.de
	 * @copyright (C) 2011 Florian Nitschmann
	 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
	 * @version 2.0   
	 */
	
	//Check if the PHP GD-Libary is available
	if(!function_exists('gd_info')) throw new Exception('Mustache Face API requires the PHP GD extension.');
	//includes
	include_once('lib/FaceRestClient.php');
	include_once('lib/WideImage/WideImage.php');

	class MustacheApi {

		private $FaceClient;
		private $WideImage;
		//Mustache images
		public $mustache_images;

		/*
		 * __construct
		 *
		 * @access public
		 * @param String $api_key [required] - The key for the Face.com API
		 * @param String $api_secret [required] - The secret key for the Face.com API 
		 * @param String $image_folder [optional] - The realpath to the mustache images could be found
		 */
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


		/*
		 * detectMustacheImage - Detects all faces in a image from an url via the Face.com API and returns an with image infos, the detection result and a WideImage Image Object
		 * 
		 * @access public
		 * @param String $url [required] - The url where the image exists
		 * @return array   
		 */
		public function detectMustacheImage($url = null) {
			if($url != null) {
				$face_result = $this->FaceClient->faces_detect($url);

				//Image infos for new pic
				$new_img = array(
					'width' => $face_result['photos']['0']['width'],
					'height' => $face_result['photos']['0']['width'],
					'type' => getimagesize($url),
					'face_api_result' => $face_result
				);
				//WideImage
				$wide_image_obj = WideImage::load($url)->resize($new_img['width'], $new_img['height']);
				$new_img['wide_image_obj'] = $wide_image_obj;
				//Return 
				return $new_img;
			}
			else return null;
		}


		/*
		 * imgStringToImageObj - Turns a raw and real image string into a WideImage Object 
		 *
		 * @access public
		 * @param String $img_string [required] - The raw image string [In every supported format]
		 * @return Object [WideImage Object]
		 */
		public function imgStringToImageObj($img_string = null) {
			if($img_string != null) {
				$mustache_obj = WideImage::load($img_string);
				return $mustache_obj;
			}	
			else return null;
		}

	
		/*
		 * urlImageToImageObj - Turns an image from an url directly in a WideImage Object with new size (optional)
		 *
		 * @access public
		 * @param String $url [required] - The url of the image
		 * @param int $w [optional] - The new result width of the image
		 * @param int $h [optional] - The new result height of the image
		 * @return Object [WideImage Object]
		 */
		public function urlImageToImageObj($url, $w = null, $h = null) {
			if($url != null) {
				if($w > null && $h > null) $wide_image_obj = WideImage::load($url)->resize($w, $h);
				else $wide_image_obj = WideImage::load($url);
				//Return 
				return $wide_image_obj;
			}
			else return null;
		}


		/*
		 * mustacheImageFromUrl - Creates a image with mustaches directly from a url and returns result as WideImage Object
		 *
		 * @access public
		 * @param  String $url [required] - The image url 
		 * @param Char $mustache_type [optional] - The mustache image, wich is checked over the mustache images folder. [defualt = r (random)]
		 * @return Object [WideImage Object]
		 */
		public function mustacheImageFromUrl($url, $mustache_type = 'r') {
			if($url != '') {
				$result = $this->detectMustacheImage($url);
				//Image info
				$image_info = array(
					'width' => $result['width'],
					'height' => $result['height'],
					'type' => $result['type']
				);
				//Return WideImage Object
				return $this->mustachePic($result['wide_image_obj'], $image_info, $result['face_api_result'], $mustache_type);
			}
			else return null;
		}

		/*
		 * mustachePic - Creates (if possible) the mustaches on a WideImage Image Object
		 * 
		 * @access public
		 * @param Object $img [required] - The WideImage Object 
		 * @param array $img_info [required] - Infos about the image (Width, Height, Type)
		 * @param array $face_result [required] - All detected details of the Face.com API
		 * @param Char $mustche_type [optional] - See mustachePicFromUrl()
		 * @return Object [WideImage Object]
		 */
		public function mustachePic($img, $img_info = array(), $face_result = array(), $mustache_type = 'r') {
			if($img != '' && is_array($img_info) && is_array($face_result) && $mustache_type != '') {
				$mime = $img_info['type']['mime'];
				//Create pic extension based on $mime
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


		/*
		 * outputMustachePicRaw - Outputs a WideImage directly in the browser as JPEG image
		 *
		 * @access public
		 * @param Object $mustache_obj [required] - A WideImage Object. If it was not detected via mustachePic() it will output a pic without any mustaches
		 * @param int $w [optional] - The new width of the image
		 * @param int $h [optional] - The new height of the image
		 */
		public function outputMustachePicRaw($mustache_obj, $w = null, $h = null) {
			if($mustache_obj != null) {
				if($w > 0 && $h > 0) $img = $mustache_obj->resize($w, $h);
				else $img = $mustache_obj;
				//output image as jpeg
				$img->output('jpg');
			}
			else return null; 
		}


		/*
		 * returnMustachePicString - Returns a raw image string in JPEG format
		 *
		 * @access public
		 * @param Object $mustache_obj [required] - A WideImage Object. If it was not detected via mustachePic() it will output a pic without any mustaches
		 * @param int $w [optional] - The new width of the image
		 * @param int $h [optional] - The new height of the image
		 * @return String
		 */
		public function returnMustachePicString($mustache_obj,$w = null, $h = null) {
			if($mustache_obj != null) {
				if($w > 0 && $h > 0) $img = $mustache_obj->resize($w, $h)->asString('jpg');
				else $img = $mustache_obj->asString('jpg');
				//Return the image string
				return $img;
			}
			else return null;	
		}


		/*
		 * returnMustachePicHtml - Returns a string that can be used directly in your HTML source code in the <img> tag. ATTENTION: The string will be a complete Base64 encoded string in your source code, may slow down the speed and will not work in older IE browsers! If you want to attempt DO NOT use this function
		 *
		 * @access public
		 * @param Object $mustache_obj [required] - A WideImage Object. If it was not detected via mustachePic() it will output a pic without any mustaches
		 * @param int $w [optional] - The new width of the image
		 * @param int $h [optional] - The new height of the image
		 * @return String [Base64 Encoded Image]
		 */
		public function returnMustachePicHtml($mustache_obj, $w = null, $h = null) {
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

	//---End MustacheApi	
	}
?>