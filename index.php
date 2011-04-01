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

	<header class="slideMenu"  id="nav">
		<ul>
			<li>
				<a href="#book" title="Liste des livres">Livres</a>
			</li>
			<li>
				<a href="#movie" title="Liste des films">Films</a>
			</li>
			<li>
				<a href="#album" title="Liste des albums">Albums</a>
			</li>
			<li>
				<a href="#storage" title="Liste des rangements">Rangements</a>
			</li>
			<li>
				<a href="#saga" title="Liste des sagas">Sagas</a>
			</li>
			<li>
				<a href="#author" title="Liste des auteurs">Auteurs</a>
			</li>
			<li>
				<a href="#artist" title="Liste des artistes">Artistes</a>
			</li>
			<li>
				<a href="#band" title="Liste des groupes">Groupes</a>
			</li>
		</ul>
		<div id="ajax_loader"></div>
	</header>
	<section id="book" class="tab">
		<?php include( LMS_PATH . '/tabs/books.php' ); ?>
	</section>
	<section id="movie" class="tab">
		<?php include( LMS_PATH . '/tabs/movies.php' ); ?>
	</section>
	<section id="album" class="tab">
		<?php include( LMS_PATH . '/tabs/albums.php' ); ?>
	</section>
	<section id="storage" class="tab">
		<?php include( LMS_PATH . '/tabs/storages.php' ); ?>
	</section>
	<section id="saga" class="tab">
		<?php include( LMS_PATH . '/tabs/sagas.php' ); ?>
	</section>
	<section id="author" class="tab">
		<?php include( LMS_PATH . '/tabs/authors.php' ); ?>
	</section>
	<section id="artist" class="tab">
		<?php include( LMS_PATH . '/tabs/artists.php' ); ?>
	</section>
	<section id="band" class="tab">
		<?php include( LMS_PATH . '/tabs/bands.php' ); ?>
	</section>

	<?php include( LMS_PATH . '/forms/book.html' ); ?>
	<?php include( LMS_PATH . '/forms/movie.html' ); ?>
	<?php include( LMS_PATH . '/forms/album.html' ); ?>
	<?php include( LMS_PATH . '/forms/author.html' ); ?>
	<?php include( LMS_PATH . '/forms/artist.html' ); ?>
	<?php include( LMS_PATH . '/forms/band.html' ); ?>
	<?php include( LMS_PATH . '/forms/saga.html' ); ?>
	<?php include( LMS_PATH . '/forms/loan.html' ); ?>
	<?php include( LMS_PATH . '/forms/storage.html' ); ?>

	<div id="drop_overlay">
		<h1>Déposer</h1>
	</div>

	<div id="detailBox" class="box">
		<input type="radio" id="detailShow" name="toggleDetail" class="boxToggleInput" autocomplete="off" />
		<div id="detailOverlay" class="overlay">
			<input type="radio" id="storageShow" name="toggleStorage" class="boxToggleInput" autocomplete="off" />
			<div id="detail" class="wrapper data">
			</div>
			<div id="storage" class="wrapper image">
				<img id="storageImg" src="" class="boxImg" />
			</div>
		</div>
	</div>
	<input type="radio" id="detailHide" name="toggleDetail" class="boxToggleInput" autocomplete="off" />
	<input type="radio" id="storageHide" name="toggleStorage" class="boxToggleInput" autocomplete="off" />

	<div id="confirmBox" class="box">
		<input type="radio" id="confirmShow" name="toggleConfirm" class="boxToggleInput" autocomplete="off" />
		<div id="confirmOverlay" class="overlay">
			<div id="confirmForm" class="wrapper data">
				<div class="block">
					<div class="formTitle">Confirmation nécessaire</div>
					<div class="formWrapper">
					</div>
					<div class="formButtons">
						<button type="submit" id="confirmSubmit" name="confirmSubmit" class="button formButton" data-icon="t" rel="delete">Supprimer</button>
						<button type="reset" id="confirmCancel" name="confirmCancel" class="button formButton" data-icon="x" rel="cancel">Annuler</button>
					</div>
					<button class="button icon close" data-icon="X" title="Fermer">Fermer</button>
				</div>
			</div>
		</div>
	</div>
	<input type="radio" id="confirmHide" name="toggleConfirm" class="boxToggleInput" autocomplete="off" />

	<div id="editBox" class="box">
		<input type="radio" id="editShow" name="toggleEdit" class="boxToggleInput" autocomplete="off" />
		<div id="editOverlay" class="overlay">
			<input type="radio" id="previewShow" name="togglePreview" class="boxToggleInput" autocomplete="off" />
			<div id="editForm" class="wrapper data">
				<div class="block">
					<div class="formTitle"></div>
					<div class="formWrapper"></div>
					<div class="formButtons">
						<button type="submit" id="formSubmit" name="formSubmit" class="button formButton" data-icon="y" rel="">Enregistrer</button>
						<button type="reset" id="formCancel" name="formCancel" class="button formButton" data-icon="x" rel="cancel">Annuler</button>
					</div>
				</div>
			</div>
			<div id="editPreview" class="wrapper image">
				<img id="previewImg" src="" />
			</div>
		</div>
	</div>
	<input type="radio" id="editHide" name="toggleEdit" class="boxToggleInput" autocomplete="off" />
	<input type="radio" id="previewHide" name="togglePreview" class="boxToggleInput" autocomplete="off" />

<?php include('html_footer.php'); ?>
