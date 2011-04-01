<?php
try {
	require_once('conf.ini.php');

	if( filter_has_var(INPUT_GET, 'id') && filter_has_var(INPUT_GET, 'cover') ){
		$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
		if( !is_null($id) && $id !== false ) $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, array('min_range' => 1));

		$type = filter_input(INPUT_GET, 'cover', FILTER_SANITIZE_STRING);


		if( !is_null($id) && $id !== false && !is_null($type) && $type !== false ){
			$request_headers = apache_request_headers();

			$browserHasCache = ( array_key_exists('If-Modified-Since', $request_headers) ? true : false );
			if( $browserHasCache ){
				$modifiedSince = strtotime($request_headers['If-Modified-Since']);
			}

			$expires = 60*60*24*14; //2 weeks

			$lastModified = 0;
			if( $browserHasCache ){
				if( $type != 'storage' ){
					$o = new $type();
					$f = 'get'.ucfirst($type).'DateById';
					$lastModified = $o->$f($id);
					$lastModified = strtotime($lastModified);
				} else {
					$oStorage = new storage();
					$storage = $oStorage->getStorageById( $id );
					$storageCodeURL = stripAccents('/storage/'.$storage['storageRoom'].'_'.$storage['storageType'].'_'.$storage['storageColumn'].$storage['storageLine'].'.png');

					$lastModified = filemtime(LMS_PATH.$storageCodeURL);
				}
			}

			//browser has image in cache and image was not modified
			if( $browserHasCache && $modifiedSince == $lastModified ){
				header('Expires: '.gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
				header('Cache-Control: max-age=' . $expires.', must-revalidate');
				header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");
				header('Content-Type: image/jpg');

				header($_SERVER["SERVER_PROTOCOL"]." 304");
				die;
			}

			if( $type != 'storage' ){
				$o = new $type();
				$f = 'get'.ucfirst($type).'CoverById';
				$cover = $o->$f($id);

				if( $lastModified == 0 ){
					$f = 'get'.ucfirst($type).'DateById';
					$lastModified = $o->$f($id);
					$lastModified = strtotime($lastModified);
				}
			} else {
				if( !isset($storageCodeURL) ){
					$oStorage = new storage();
					$storage = $oStorage->getStorageById( $id );
					$storageCodeURL = stripAccents('/storage/'.$storage['storageRoom'].'_'.$storage['storageType'].'_'.$storage['storageColumn'].$storage['storageLine'].'.png');
				}

				$cover = file_get_contents(LMS_PATH.$storageCodeURL);

				if( $lastModified == 0 ){
					$lastModified = filemtime(LMS_PATH.$storageCodeURL);
				}

				if( $cover === false ){
					$cover = null;
					$lastModified = 0;
				}
			}

			if( !empty($cover) ){
				header('Expires: '.gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
				header('Cache-Control: max-age=' . $expires.', must-revalidate');
				if( $lastModified != 0 ) header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");
				header('Content-Type: image/jpg');

				echo $cover;

				die;
			}
		}
	}

	if( empty($cover) ){
		//place holder image
		$ph = file_get_contents(LMS_PATH.'/img/placeholder.png');
		header('Content-Type: image/png');
		echo $ph;

		die;
	}
} catch (Exception $e) {
	echo $e->getMessage();
	die;
}
?>