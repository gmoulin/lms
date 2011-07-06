<?php
require('../conf.ini.php');

set_time_limit(0);
ini_set('memory_limit', '512M');
header('Content-type: text/html; charset=UTF-8');

try {
	$nbAlbums = ( isset($_GET['nb']) && !empty($_GET['nb']) ? $_GET['nb'] : 200 );

	$clean = ( isset($_GET['clean']) ? $_GET['clean'] : 0 );

	if( $clean ){
		$oCommun = new commun();

		$cleanup = $oCommun->db->prepare("DELETE FROM album WHERE albumTitle LIKE 'test album%'");
		$cleanup->execute();

		$cleanup = $oCommun->db->prepare("DELETE FROM albums_bands WHERE albumFK NOT IN (SELECT albumID FROM album)");
		$cleanup->execute();
	}

	$bands = array(
		'The Donnas',
		'Queen',
		'ACDC',
		'Nightwish',
		'Evanescence',
		'Avril Lavigne',
		'HB',
		'The White Stripes',
	);

	$types = array(
		'bande-originale',
		'full-length',
		'EP',
		'compilation',
		'best of',
		'live',
	);

	$covers = array(
		'False-Gods.jpg',
		'Horus-Rising.jpg',
		'the-first-heretic.jpg',
	);

	$oStorage = new Storage();
	$storages = $oStorage->getStoragesForDropDownList();

	$n = 0;
	$oAlbum = new album();
	for( $i = 0; $i < $nbAlbums; $i ++ ){
		$tmp = array_rand(array_flip($bands), rand(1, 2));
		if( !is_array($tmp) ) $tmp = array($tmp);

		$formData = array(
			'title'		=> 'test album '.$i,
			'type'		=> $types[array_rand($types)],
			'cover'		=> chunk_split( base64_encode( file_get_contents( UPLOAD_COVER_PATH.$covers[array_rand($covers)] ) ) ),
			'storage'	=> $storages[array_rand($storages)]['id'],
			'bands'		=> $tmp,
		);

		if( $oAlbum->albumUnicityCheck($formData) ){
			$oAlbum->addAlbum( $formData );
			$n++;
		}
	}

	echo $n.' albums generated';

} catch( Exception $e ){
	echo $e->getMessage();
	die;
}
?>