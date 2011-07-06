<?php
try {
	require_once('conf.ini.php');

	//metadata
	$metadata['description'] = 'Librairy Content Manager - gestionnaire de bibliothèque, vidéothèque et musicothèque';
	$metadata['motscles'] = 'librairie, contenu, gestion, gestionnaire, bibliothèque, livre, roman, auteur, vidéothèque, film, acteur, musique, musicothèque, album, groupe';
	$lang = 'fr';

	if( file_exists(LMS_PATH.'/css/style.css') ) $cssTS = filemtime( LMS_PATH.'/css/style.css' );

	//ajax call for smarty cache, smarty templates_c and stash cleaning
	if( filter_has_var(INPUT_GET, 'servercache') ){
		//clean smarty templates_c
		$handle = opendir($smarty->compile_dir);
		while( $tmp = readdir($handle) ){
			if( $tmp != '..' && $tmp != '.' && $tmp != '' ){
				 if( is_file($smarty->compile_dir.DS.$tmp) ){
						 unlink($smarty->compile_dir.DS.$tmp);
				 }
			}
		}
		closedir($handle);

		//clean smarty cache
		$handle = opendir($smarty->cache_dir);
		while( $tmp = readdir($handle) ){
			if( $tmp != '..' && $tmp != '.' && $tmp != '' ){
				 if( is_file($smarty->cache_dir.DS.$tmp) ){
						 unlink($smarty->cache_dir.DS.$tmp);
				 }
			}
		}
		closedir($handle);


		if( !is_dir(STASH_PATH) ){
			echo 'stash cache not cleaned, folder missing. ('.STASH_PATH.')';
			die;
		}

		//clean stash
		$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
		StashBox::setHandler($stashFileSystem);

		$stash = new Stash($stashFileSystem);
		$result = $stash->clear();

		if( $result ) echo 'Stash cache cleaned !';
		else echo 'Failed cleaning stash cache ! Try to remove the stash folder completely.';
		die;
	}

} catch (Exception $e) {
	echo $e->getMessage();
	die;
}
?>

<?php include('html_header.php'); ?>

	<button onclick="cleanCache()">Nettoyer le cache pour ce site</button> (localStorage et applicationCache).

	<!-- Grab local. fall back to Google CDN's jQuery if necessary -->
	<script src="js/libs/jquery-1.6.1.min.js"></script>
	<script>!window.jQuery && document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.js">\x3C/script>')</script>

	<script>
		function cleanCache(){
			if( Modernizr.applicationcache ){
				/*
					@toto buggy javascript method (mozItems)
				*/
				console.log(window.applicationCache);
				if( window.applicationCache.mozItems ) console.log(window.applicationCache.mozItems);

				if( window.applicationCache.mozItems && window.applicationCache.mozItems.length ){
					for( c in window.applicationCache.mozItems ){
						window.applicationCache.mozRemove(c);
					}
				}
			}

			if( Modernizr.localstorage ){
				localStorage.clear();
			} else {
				document.cookie = "localStorage=; path=/";
			}

			/*
				ask the server for smarty and stash caches cleaning
			*/
			$.get('clean.php', 'servercache=1', function(data){
				$('body').append('<p>'+ data +'</p>');
			});
		}
	</script>

</body>
</html>