<?php
require_once('../conf.ini.php');
header('Content-type: text/html; charset=UTF-8');

//manage cover upload via ajax posts
try {
	$rel = filter_has_var(INPUT_GET, 'rel');
	if( is_null($rel) || $rel === false ){
		throw new Exception('Gestion des couvertures : type manquant.');
	}

	$rel = filter_var($_GET['rel'], FILTER_SANITIZE_STRING);
	if( $rel === false ){
		throw new Exception('Gestion des couvertures : type incorrect.');
	}

	$isStorage = $rel == 'storage';

	if( $isStorage ){
		$path = UPLOAD_STORAGE_PATH;
	} else {
		$path = UPLOAD_COVER_PATH;
	}

	/**
	 * check image size and resize it if necessary
	 * then save the image
	 * @param resource $source : identifiant de ressource image
	 * @param string $filename : chemin complet du fichier image cible
	 * @return boolean
	 */
	function resizeIfNecessaryThenSave($source, $filename, $rel){
		$width = imagesx($source);
		$height = imagesy($source);

		$max_height = 600;
		$max_width = 500;

		$scale = min($max_height / $height, $max_width / $width);
		if( $rel != 'storage' && $scale < 1 ){
			$newwidth = $scale * $width;
			$newheight = $scale * $height;

			//create blank image with new dimensions
			$resize = imagecreatetruecolor($newwidth, $newheight);

			//resize the source
			imagecopyresized($resize, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

			//save new image
			$r = imagejpeg($resize, $filename, 90);

			imageDestroy($resize); //free memory
		} elseif( $rel != 'storage' ){
			//save new image
			$r = imagejpeg($source, $filename, 90);
		} else {
			//save new image
			$filename = UPLOAD_STORAGE_PATH.'tmp_storage.png';
			$r = imagepng($source, $filename);
		}

		imagedestroy($source); //free memory

		return $r;
	}


	// If the browser supports sendAsBinary () can use the array $_FILES
	if( count($_FILES) > 0 ){
		if( move_uploaded_file( $_FILES['upload']['tmp_name'] , $path.$_FILES['upload']['name'] ) ){

			$filename = $path.$_FILES['upload']['name'];

			$source = imagecreatefromstring(file_get_contents($filename));

			if( resizeIfNecessaryThenSave($source, $filename, $rel) === false ){
				throw new Exception('Erreur lors du chargement de la couverture.');
			}

			echo 'done';
		} else throw new Exception('Erreur lors du chargement de la couverture.');

	} elseif( isset($_GET['up']) ){
		$content = base64_decode(file_get_contents('php://input'));

		$headers = getallheaders();
		$headers = array_change_key_case($headers, CASE_UPPER);

		$filename = $path.$headers['UP-FILENAME'];

		$source = imagecreatefromstring($content);

		if( resizeIfNecessaryThenSave($source, $filename, $rel) === false ){
			throw new Exception('Erreur lors du chargement de la couverture.');
		}

		echo 'done';
	} else {
		$url = filter_has_var(INPUT_GET, 'url');
		if( is_null($url) || $url === false ){
			throw new Exception('Gestion des couvertures : adresse manquante.');
		}

		$url = filter_var($_GET['url'], FILTER_SANITIZE_STRING);
		if( $url === false ){
			throw new Exception('Gestion des couvertures : type incorrect.');
		}

		//remote image, using curl to get it
		$result = false;
		$filename = $path.basename($url);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //follow 301 and 302
		$rawdata = curl_exec($ch);

		/* Check for 404 (file not found). */
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if( $httpCode == 404 ){ //not found
			throw new Exception('Erreur lors du chargement de la couverture (fichier distant non trouvé).');
		}

		if( strpos($rawdata, "Not Found") === false ){
			$source = @imagecreatefromstring($rawdata);

			if( !$source ){
				throw new Exception('Erreur lors du chargement de la couverture (échec de la sauvegarde).');
			}

			curl_close($ch);
			unset($ch);

			if( resizeIfNecessaryThenSave($source, $filename, $rel) === false ){
				throw new Exception('Erreur lors du chargement de la couverture.');
			}

			echo basename($filename);
		} else {
			throw new Exception('Erreur lors de la récupération de la couverture.');
		}
	}
} catch (Exception $e) {
	if( isset($ch) ) curl_close($ch); //security

	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo $e->getMessage();
	die;
}
?>