<?php
require('../conf.ini.php');

set_time_limit(0);
ini_set('memory_limit', '512M');
header('Content-type: text/html; charset=UTF-8');

try {
	$nbMovies = ( isset($_GET['nb']) && !empty($_GET['nb']) ? $_GET['nb'] : 200 );

	$clean = ( isset($_GET['clean']) ? $_GET['clean'] : 0 );

	if( $clean ){
		$oCommun = new commun();

		$cleanup = $oCommun->db->prepare("DELETE FROM movie WHERE movieTitle LIKE 'test film%'");
		$cleanup->execute();

		$cleanup = $oCommun->db->prepare("DELETE FROM movies_artists WHERE movieFK NOT IN (SELECT movieID FROM movie)");
		$cleanup->execute();
	}

	$artists = array(
		'Artist Alpha',
		'Artist Beta',
		'Artist Ceta',
		'Artist Mu',
		'Artist Omega',
		'Artist Zeta',
		'Artist Delta',
		'Artist Gamma',
	);

	$sagas = array(
		'Le seigneur des anneaux',
		'Kill Bill',
		'Conan',
		'Riddick',
		'Harry Potter',
		'James Bond',
		'Underworld',
		null,
	);

	$genre = array(
		'Heroic Fantasy',
		'Science Fiction',
		'Polar',
		'Thriller',
		'Comédie',
		'Manga',
		'Animation',
		'Action',
	);

	$mediaType = array(
		'DVD',
		'BlueRay',
		'Divx',
	);

	$covers = array(
		'False-Gods.jpg',
		'Horus-Rising.jpg',
		'the-first-heretic.jpg',
	);

	$oStorage = new Storage();
	$storages = $oStorage->getStoragesForDropDownList();

	$n = 0;
	$oMovie = new movie();
	for( $i = 0; $i < $nbMovies; $i++ ){
		$saga = $sagas[array_rand($sagas)];

		$tmp = array_rand(array_flip($artists), rand(1, 3));
		if( !is_array($tmp) ) $tmp = array($tmp);

		$formData = array(
			'title'		=> 'test film '.$i,
			'genre'		=> $genre[array_rand($genre)],
			'mediaType'	=> $mediaType[array_rand($mediaType)],
			'length'	=> rand(60, 180),
			'cover'		=> chunk_split( base64_encode( file_get_contents( UPLOAD_COVER_PATH.$covers[array_rand($covers)] ) ) ),
			'saga'		=> $saga,
			'position'	=> (is_null($saga) ? null : rand(1, 15)),
			'storage'	=> $storages[array_rand($storages)]['id'],
			'artists'	=> $tmp,
		);

		if( $oMovie->movieUnicityCheck($formData) ){
			$oMovie->addMovie( $formData );
			$n++;
		}
	}

	echo $n.' movies generated';

} catch( Exception $e ){
	echo $e->getMessage();
	die;
}
?>