<?php
try {
	//@todo getList() should have several display template (table, list, slideshow, ...)
	//@todo add crosstab filter links (saga -> books)

	require_once('conf.ini.php');

	//metadata
	$metadata['description'] = 'Librairy Content Manager - gestionnaire de bibliothèque, vidéothèque et musicothèque';
	$metadata['motscles'] = 'librairie, contenu, gestion, gestionnaire, bibliothèque, livre, roman, auteur, vidéothèque, film, acteur, musique, musicothèque, album, groupe';
	$lang = 'fr';

	$css = filemtime( LMS_PATH.'/css/style.css' );

	$js = filemtime( LMS_PATH.'/js/script.js' );
} catch (Exception $e) {
	echo $e->getMessage();
	die;
}
?>

<?php include('html_header.php'); ?>

<section id="list_book" class="list smallBoxes hasCovers">
</section>
<?php include('list/book.html'); ?>
		<!-- Grab local. fall back to Google CDN's jQuery if necessary -->
		<script src="js/libs/jquery-1.5.1.min.js"></script>
		<script>!window.jQuery && document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.js">\x3C/script>')</script>

		<!-- scripts concatenated and minified via ant build script-->
		<script src="js/libs/jquery.tmpl.min.js"></script>
		<script src="js/plugins.js"></script>
		<script src="js/script.js?v=<?php echo $js; ?>"></script>
		<!-- end scripts-->
	</body>
</html>