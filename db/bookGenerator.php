<?php
require('../conf.ini.php');

set_time_limit(0);
ini_set('memory_limit', '512M');
header('Content-type: text/html; charset=UTF-8');

try {
	$nbBooks = ( isset($_GET['nb']) && !empty($_GET['nb']) ? $_GET['nb'] : 200 );

	$clean = ( isset($_GET['clean']) ? $_GET['clean'] : 0 );

	if( $clean ){
		$oCommun = new commun();

		$cleanup = $oCommun->db->prepare("DELETE FROM book WHERE bookTitle LIKE 'test livre%'");
		$cleanup->execute();

		$cleanup = $oCommun->db->prepare("DELETE FROM books_authors WHERE bookFK NOT IN (SELECT bookID FROM book)");
		$cleanup->execute();
	}

	$authors = array(
		'John Doe',
		'Frederic Simon',
		'Nicholas Fuchs',
		'Hélène Nicaise',
		'Delphine Meurice',
		'Shirley David',
		'Jean-Rémi Jobert',
		'Alexandre Lefebvre',
	);

	$sagas = array(
		'Les dragons de Pern',
		'Warhammer 40k: Ultramarines',
		'Warhammer 40k: Blood Angels',
		'Jane Frost',
		'Téméraire',
		'Warhammer 40k: Space Wolves',
		'Warhammer 40k: Horus Heresy',
		'Warhammer 40k: Soul Drinker',
		'Warhammer 40k: Word Bearer',
		null,
	);

	$sizes = array(
		'broché',
		'relié',
		'bande-dessinée',
		'audio',
		'ebook',
	);

	$covers = array(
		'False-Gods.jpg',
		'Horus-Rising.jpg',
		'the-first-heretic.jpg',
	);

	$oStorage = new Storage();
	$storages = $oStorage->getStoragesForDropDownList();

	$n = 0;
	$oBook = new book();
	for( $i = 0; $i < $nbBooks; $i ++ ){
		$saga = $sagas[array_rand($sagas)];

		$tmp = array_rand(array_flip($authors), rand(1, 2));
		if( !is_array($tmp) ) $tmp = array($tmp);

		$formData = array(
			'title'		=> 'test livre '.$i,
			'size'		=> $sizes[array_rand($sizes)],
			'cover'		=> chunk_split( base64_encode( file_get_contents( UPLOAD_COVER_PATH.$covers[array_rand($covers)] ) ) ),
			'saga'		=> $saga,
			'position'	=> (is_null($saga) ? null : rand(1, 15)),
			'storage'	=> $storages[array_rand($storages)]['id'],
			'authors'	=> $tmp,
		);

		if( $oBook->bookUnicityCheck($formData) ){
			$oBook->addBook( $formData );
			$n++;
		}
	}

	echo $n.' books generated';

} catch( Exception $e ){
	echo $e->getMessage();
	die;
}
?>