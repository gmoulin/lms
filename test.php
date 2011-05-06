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

<style>
	.listContent .item {
		  -webkit-transition-property: -webkit-transform, height, width;
			 -moz-transition-property:    -moz-transform, height, width;
			   -o-transition-property:    -moz-transform, height, width;
				  transition-property:         transform, height, width;
		  -webkit-transition-duration: 0.8s;
			 -moz-transition-duration: 0.8s;
			   -o-transition-duration: 0.8s;
				  transition-duration: 0.8s;
		  -webkit-transition-delay: 0, 0.4s, 0.8s;
			 -moz-transition-delay: 0, 0.4s, 0.8s;
			   -o-transition-delay: 0, 0.4s, 0.8s;
				  transition-delay: 0, 0.4s, 0.8s;
	}

	.listContent {
		height: 100%;
		text-align: left;
		visibility: hidden;
	}

	.listContent .cover {
		display: none;
	}

	.item.hidden {
		z-index: 1;
		pointeur-events: none;
	}

	.item {
		height: 200px;
		width:  280px;
		z-index: 2;
		margin: 5px; /*Creates the 10px gap between each column and each line*/
		padding: 3px;
		background-position: center center;
		background-repeat: no-repeat;
		background-size: auto 100%;
		border-radius: 10px;
		background-color: #000;
		border: 1px solid #CCC;

		-webkit-box-shadow: inset 0 1px rgba(255, 255, 255, 0.3),
							inset 0 10px 20px rgba(255, 255, 255, 0.25),
							inset 0 -10px 10px rgba(0, 0, 0, 0.3);
		box-shadow: inset 0 1px rgba(255, 255, 255, 0.3),
					inset 0 10px 20px rgba(255, 255, 255, 0.25),
					inset 0 -10px 10px rgba(0, 0, 0, 0.3);
	}

	.item .detail {
		float: right;
	}

	.list:not(.hasCovers) .block {
		height: auto;
	}

	.listContent:not(.big) .listHidden {
		display: none;
	}

	.listContent:not(.big) .info {
		display: block;
		width: 100%;
		position: absolute;
		bottom: 0;
		text-align: left;
	}

	.big .item {
		background-position: left top;
		background-size: 39% auto;

		width: 48%;
		width: -webkit-calc(50% - 26px);
		width: -moz-calc(50% - 26px);
		width: -o-calc(50% - 26px);
		width: calc(50% - 26px);
		height: 500px;

		position: relative;
	}

	.big .item .close,
	.big .item .detail {
		display: none;
	}


	.big .info {
		display: block;
		text-align: left;

		margin: 0 0 0 40%;

		padding-right: 20px;
		padding-bottom: 15px;

		overflow: hidden;
		max-height: 385px;
	}

	.big dt {
		float: left;
		clear: left;
		width: 105px;
		padding-right: 5px;
		font-size: 1.1em;
	}

	.big dd {
		display: block;
		float: left;
		margin-right: 7px;
		font-size: 1.1em;
		position: relative;
	}

	.big dd .button {
		display: none;
	}
	.big dd:hover .button {
		display: inline-block;
		position: absolute;
		left: 0;
		top: 2.2em;
	}
	.big dd .button:nth-child(2) {
		left: 1.7em;
	}
	.big dd .button:nth-child(3) {
		left: 3.4em;
	}
	.big dd .button:nth-child(4) {
		left: 5.1em;
	}

	.big .actions {
		position: absolute;
		right: 7px;
		top: -3px;
		width: auto;
		padding: 0;
	}

	.big .formTitle {
		font-size: 2em;
	}

	#holder {
		visibility: hidden;
		height: 0;
		overflow: hidden;
		margin-top: 0;
		margin-bottom: 0;
	}
</style>

<section class="tab" id="book">
	<form class="filterForm" method="post" action="" name="book_filter" id="book_filter">
		<input type="hidden" autocomplete="off" class="sortTypeField" value="0" id="bookSortType" name="bookSortType">
		<ul class="listFilter">
			<li>
				<a title="Filtrer les résultats" rel="book" class="button category filterFormSwitch" href="filter">Filtrer</a>
			</li>

			<li>
				<label for="bookSearch">Recherche</label>
				<input type="search" placeholder="dans les informations textuelles" value="" id="bookSearch" name="bookSearch">
			</li>

			<li>
				<label for="bookTitleFilter">Titre</label>
				<input type="search" placeholder="de livre" list="bookTitleFilterList" value="" id="bookTitleFilter" name="bookTitleFilter">
				<datalist id="bookTitleFilterList"></datalist>
			</li>

			<li>
				<label for="bookSagaFilter">Saga</label>
				<input type="search" placeholder="de livre" list="bookSagaFilterList" value="" id="bookSagaFilter" name="bookSagaFilter">
				<datalist id="bookSagaFilterList"></datalist>
			</li>

			<li>
				<label for="bookAuthorFilter">Auteur</label>
				<input type="search" placeholder="de livre" list="bookAuthorFilterList" value="" id="bookAuthorFilter" name="bookAuthorFilter">
				<datalist id="bookAuthorFilterList"></datalist>
			</li>

			<li>
				<label for="bookLoanFilter">Prêt</label>
				<input type="search" placeholder="Livre prêté à..." list="bookLoanFilterList" value="" id="bookLoanFilter" name="bookLoanFilter">
				<datalist id="bookLoanFilterList"></datalist>
			</li>

			<li>
				<label for="bookStorageFilter">Rangement</label>
				<select id="bookStorageFilter" name="bookStorageFilter">
					<option value="">Livre rangé où</option>
				</select>
			</li>

			<li>
				<button data-icon="f" class="button search" name="bookSearchSubmit" type="submit">Rechercher</button>
				<button data-icon="x" class="button cancel" name="bookSearchCancel" type="reset">Annuler</button>
			</li>
		</ul>

		<aside class="listSort">
			<button title="Ajouter un livre" rel="book" class="button category add"></button>
			<ol>
				<li>
					Tris :
				</li>
				<li>
					<a rel="saga" class="button sort asc" title="trier par saga" href="0"></a>
				</li>
				<li>
					<a rel="book" class="button sort both" title="trier par titre" href="2"></a>
				</li>
				<li>
					<a rel="author" class="button sort both" title="trier par auteur" href="4"></a>
				</li>
				<li>
					<a rel="storage" class="button sort both" title="trier par rangement" href="6"></a>
				</li>
				<li>
					<a rel="date" class="button sort both" title="trier par date d'ajout" href="8"></a>
				</li>
			</ol>
		</aside>
	</form>
	<section id="list_book" class="list">
		<section class="listDisplaySwitch">
			<a href="#" title="format vignette" rel="smallBoxes" class="button icon disabled" data-icon="c"></a>
			<a href="#" title="format rectangle" rel="rectangleBoxes" class="button icon" data-icon="d"></a>
		</section>
		<section class="paginate begin">
			<span>30 livres sur 398 - descendez en bas de la liste pour en afficher plus</span>
		</section>
		<section class="listContent">
			<article class="item" style="background-image: url(image.php?cover=book&amp;id=98);">
				<a title="voir le détail" href="98" data-icon="}" class="button icon detail"></a>
				<div class="block">
					<button data-icon="X" title="Fermer" class="button icon close listHidden">Fermer</button>
					<img class="cover" alt="" src="image.php?cover=book&amp;id=98">
					<dl class="info">
						<dd class="listHidden formTitle">Détail</dd>

						<dt class="listHidden">Titre</dt>
						<dd class="title">You Slay Me</dd>
						<dt class="listHidden">Auteur</dt>
						<dd class="listHidden">
							Katie MacAlister
							<a rel="author" title="Mettre à jour les informations de cet auteur" href="5" data-icon="P" class="listHidden button icon update"></a>
							<a rel="author" title="Supprimer cet auteur" href="5" data-icon="t" class="listHidden button icon delete"></a>
							<a target="_blank" title="Voir le site de l'auteur dans une nouvelle page" href="http://katiemacalister.com/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a target="_blank" title="Rechercher les livres de l'auteur sur internet" href="http://katiemacalister.com/books/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de cet auteur" href="Katie MacAlister" rel="author" data-icon="f" class="button icon filter"></a>
						</dd>
						<dt class="listHidden">Saga</dt>
						<dd class="saga">
							Aisling Grey - 1 / 4
							<a rel="saga" title="Mettre à jour les informations de cette saga" href="5" data-icon="P" class="listHidden button icon update"></a>
							<a rel="saga" title="Supprimer cette saga" href="5" data-icon="t" class="listHidden button icon delete"></a>
							<a target="_blank" title="Rechercher les livres de cette saga sur internet" href="http://katiemacalister.com/books/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de cette saga" href="Aisling Grey" rel="saga" data-icon="f" class="button icon filter"></a>
						</dd>
						<dt class="listHidden">Format</dt>
						<dd class="listHidden">poche</dd>
						<dt class="listHidden">Rangement</dt>
						<dd class="listHidden">
							chambre sous-sol - billy angle - A2
							<a rel="storage" title="Mettre à jour les informations de ce rangement" href="39" data-icon="P" class="listHidden button icon update"></a>
							<a href="storage/chambresous-sol_billyangle_A2.png" data-icon="}" class="listHidden button icon storage"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de ce rangement" href="39" rel="storage" data-icon="f" class="listHidden button icon filter"></a>
						</dd>
						<dt class="listHidden actions">
							<a rel="book" title="Mettre à jour les informations de ce livre" href="98" data-icon="P" class="button icon update"></a>
							<a rel="book" title="Supprimer ce livre" href="98" data-icon="t" class="button icon delete"></a>
							<a rel="book" title="Ajouter un prêt pour ce livre" href="98" data-icon="u" class="button icon addLoan"></a>
						</dt>
					</dl>
				</div>
			</article>
			<article class="item" style="background-image: url(image.php?cover=book&amp;id=98);">
				<a title="voir le détail" href="98" data-icon="}" class="button icon detail"></a>
				<div class="block">
					<button data-icon="X" title="Fermer" class="button icon close listHidden">Fermer</button>
					<img class="cover" alt="" src="image.php?cover=book&amp;id=98">
					<dl class="info">
						<dd class="listHidden formTitle">Détail</dd>

						<dt class="listHidden">Titre</dt>
						<dd class="title">You Slay Me</dd>
						<dt class="listHidden">Auteur</dt>
						<dd class="listHidden">
							Katie MacAlister
							<a rel="author" title="Mettre à jour les informations de cet auteur" href="5" data-icon="P" class="listHidden button icon update"></a>
							<a rel="author" title="Supprimer cet auteur" href="5" data-icon="t" class="listHidden button icon delete"></a>
							<a target="_blank" title="Voir le site de l'auteur dans une nouvelle page" href="http://katiemacalister.com/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a target="_blank" title="Rechercher les livres de l'auteur sur internet" href="http://katiemacalister.com/books/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de cet auteur" href="Katie MacAlister" rel="author" data-icon="f" class="button icon filter"></a>
						</dd>
						<dt class="listHidden">Saga</dt>
						<dd class="saga">
							Aisling Grey - 1 / 4
							<a rel="saga" title="Mettre à jour les informations de cette saga" href="5" data-icon="P" class="listHidden button icon update"></a>
							<a rel="saga" title="Supprimer cette saga" href="5" data-icon="t" class="listHidden button icon delete"></a>
							<a target="_blank" title="Rechercher les livres de cette saga sur internet" href="http://katiemacalister.com/books/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de cette saga" href="Aisling Grey" rel="saga" data-icon="f" class="button icon filter"></a>
						</dd>
						<dt class="listHidden">Format</dt>
						<dd class="listHidden">poche</dd>
						<dt class="listHidden">Rangement</dt>
						<dd class="listHidden">
							chambre sous-sol - billy angle - A2
							<a rel="storage" title="Mettre à jour les informations de ce rangement" href="39" data-icon="P" class="listHidden button icon update"></a>
							<a href="storage/chambresous-sol_billyangle_A2.png" data-icon="}" class="listHidden button icon storage"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de ce rangement" href="39" rel="storage" data-icon="f" class="listHidden button icon filter"></a>
						</dd>
						<dt class="listHidden actions">
							<a rel="book" title="Mettre à jour les informations de ce livre" href="98" data-icon="P" class="button icon update"></a>
							<a rel="book" title="Supprimer ce livre" href="98" data-icon="t" class="button icon delete"></a>
							<a rel="book" title="Ajouter un prêt pour ce livre" href="98" data-icon="u" class="button icon addLoan"></a>
						</dt>
					</dl>
				</div>
			</article>
			<article class="item" style="background-image: url(image.php?cover=book&amp;id=98);">
				<a title="voir le détail" href="98" data-icon="}" class="button icon detail"></a>
				<div class="block">
					<button data-icon="X" title="Fermer" class="button icon close listHidden">Fermer</button>
					<img class="cover" alt="" src="image.php?cover=book&amp;id=98">
					<dl class="info">
						<dd class="listHidden formTitle">Détail</dd>

						<dt class="listHidden">Titre</dt>
						<dd class="title">You Slay Me</dd>
						<dt class="listHidden">Auteur</dt>
						<dd class="listHidden">
							Katie MacAlister
							<a rel="author" title="Mettre à jour les informations de cet auteur" href="5" data-icon="P" class="listHidden button icon update"></a>
							<a rel="author" title="Supprimer cet auteur" href="5" data-icon="t" class="listHidden button icon delete"></a>
							<a target="_blank" title="Voir le site de l'auteur dans une nouvelle page" href="http://katiemacalister.com/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a target="_blank" title="Rechercher les livres de l'auteur sur internet" href="http://katiemacalister.com/books/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de cet auteur" href="Katie MacAlister" rel="author" data-icon="f" class="button icon filter"></a>
						</dd>
						<dt class="listHidden">Saga</dt>
						<dd class="saga">
							Aisling Grey - 1 / 4
							<a rel="saga" title="Mettre à jour les informations de cette saga" href="5" data-icon="P" class="listHidden button icon update"></a>
							<a rel="saga" title="Supprimer cette saga" href="5" data-icon="t" class="listHidden button icon delete"></a>
							<a target="_blank" title="Rechercher les livres de cette saga sur internet" href="http://katiemacalister.com/books/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de cette saga" href="Aisling Grey" rel="saga" data-icon="f" class="button icon filter"></a>
						</dd>
						<dt class="listHidden">Format</dt>
						<dd class="listHidden">poche</dd>
						<dt class="listHidden">Rangement</dt>
						<dd class="listHidden">
							chambre sous-sol - billy angle - A2
							<a rel="storage" title="Mettre à jour les informations de ce rangement" href="39" data-icon="P" class="listHidden button icon update"></a>
							<a href="storage/chambresous-sol_billyangle_A2.png" data-icon="}" class="listHidden button icon storage"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de ce rangement" href="39" rel="storage" data-icon="f" class="listHidden button icon filter"></a>
						</dd>
						<dt class="listHidden actions">
							<a rel="book" title="Mettre à jour les informations de ce livre" href="98" data-icon="P" class="button icon update"></a>
							<a rel="book" title="Supprimer ce livre" href="98" data-icon="t" class="button icon delete"></a>
							<a rel="book" title="Ajouter un prêt pour ce livre" href="98" data-icon="u" class="button icon addLoan"></a>
						</dt>
					</dl>
				</div>
			</article>
			<article class="item" style="background-image: url(image.php?cover=book&amp;id=98);">
				<a title="voir le détail" href="98" data-icon="}" class="button icon detail"></a>
				<div class="block">
					<button data-icon="X" title="Fermer" class="button icon close listHidden">Fermer</button>
					<img class="cover" alt="" src="image.php?cover=book&amp;id=98">
					<dl class="info">
						<dd class="listHidden formTitle">Détail</dd>

						<dt class="listHidden">Titre</dt>
						<dd class="title">You Slay Me</dd>
						<dt class="listHidden">Auteur</dt>
						<dd class="listHidden">
							Katie MacAlister
							<a rel="author" title="Mettre à jour les informations de cet auteur" href="5" data-icon="P" class="listHidden button icon update"></a>
							<a rel="author" title="Supprimer cet auteur" href="5" data-icon="t" class="listHidden button icon delete"></a>
							<a target="_blank" title="Voir le site de l'auteur dans une nouvelle page" href="http://katiemacalister.com/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a target="_blank" title="Rechercher les livres de l'auteur sur internet" href="http://katiemacalister.com/books/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de cet auteur" href="Katie MacAlister" rel="author" data-icon="f" class="button icon filter"></a>
						</dd>
						<dt class="listHidden">Saga</dt>
						<dd class="saga">
							Aisling Grey - 1 / 4
							<a rel="saga" title="Mettre à jour les informations de cette saga" href="5" data-icon="P" class="listHidden button icon update"></a>
							<a rel="saga" title="Supprimer cette saga" href="5" data-icon="t" class="listHidden button icon delete"></a>
							<a target="_blank" title="Rechercher les livres de cette saga sur internet" href="http://katiemacalister.com/books/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de cette saga" href="Aisling Grey" rel="saga" data-icon="f" class="button icon filter"></a>
						</dd>
						<dt class="listHidden">Format</dt>
						<dd class="listHidden">poche</dd>
						<dt class="listHidden">Rangement</dt>
						<dd class="listHidden">
							chambre sous-sol - billy angle - A2
							<a rel="storage" title="Mettre à jour les informations de ce rangement" href="39" data-icon="P" class="listHidden button icon update"></a>
							<a href="storage/chambresous-sol_billyangle_A2.png" data-icon="}" class="listHidden button icon storage"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de ce rangement" href="39" rel="storage" data-icon="f" class="listHidden button icon filter"></a>
						</dd>
						<dt class="listHidden actions">
							<a rel="book" title="Mettre à jour les informations de ce livre" href="98" data-icon="P" class="button icon update"></a>
							<a rel="book" title="Supprimer ce livre" href="98" data-icon="t" class="button icon delete"></a>
							<a rel="book" title="Ajouter un prêt pour ce livre" href="98" data-icon="u" class="button icon addLoan"></a>
						</dt>
					</dl>
				</div>
			</article>
			<article class="item" style="background-image: url(image.php?cover=book&amp;id=98);">
				<a title="voir le détail" href="98" data-icon="}" class="button icon detail"></a>
				<div class="block">
					<button data-icon="X" title="Fermer" class="button icon close listHidden">Fermer</button>
					<img class="cover" alt="" src="image.php?cover=book&amp;id=98">
					<dl class="info">
						<dd class="listHidden formTitle">Détail</dd>

						<dt class="listHidden">Titre</dt>
						<dd class="title">You Slay Me</dd>
						<dt class="listHidden">Auteur</dt>
						<dd class="listHidden">
							Katie MacAlister
							<a rel="author" title="Mettre à jour les informations de cet auteur" href="5" data-icon="P" class="listHidden button icon update"></a>
							<a rel="author" title="Supprimer cet auteur" href="5" data-icon="t" class="listHidden button icon delete"></a>
							<a target="_blank" title="Voir le site de l'auteur dans une nouvelle page" href="http://katiemacalister.com/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a target="_blank" title="Rechercher les livres de l'auteur sur internet" href="http://katiemacalister.com/books/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de cet auteur" href="Katie MacAlister" rel="author" data-icon="f" class="button icon filter"></a>
						</dd>
						<dt class="listHidden">Saga</dt>
						<dd class="saga">
							Aisling Grey - 1 / 4
							<a rel="saga" title="Mettre à jour les informations de cette saga" href="5" data-icon="P" class="listHidden button icon update"></a>
							<a rel="saga" title="Supprimer cette saga" href="5" data-icon="t" class="listHidden button icon delete"></a>
							<a target="_blank" title="Rechercher les livres de cette saga sur internet" href="http://katiemacalister.com/books/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de cette saga" href="Aisling Grey" rel="saga" data-icon="f" class="button icon filter"></a>
						</dd>
						<dt class="listHidden">Format</dt>
						<dd class="listHidden">poche</dd>
						<dt class="listHidden">Rangement</dt>
						<dd class="listHidden">
							chambre sous-sol - billy angle - A2
							<a rel="storage" title="Mettre à jour les informations de ce rangement" href="39" data-icon="P" class="listHidden button icon update"></a>
							<a href="storage/chambresous-sol_billyangle_A2.png" data-icon="}" class="listHidden button icon storage"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de ce rangement" href="39" rel="storage" data-icon="f" class="listHidden button icon filter"></a>
						</dd>
						<dt class="listHidden actions">
							<a rel="book" title="Mettre à jour les informations de ce livre" href="98" data-icon="P" class="button icon update"></a>
							<a rel="book" title="Supprimer ce livre" href="98" data-icon="t" class="button icon delete"></a>
							<a rel="book" title="Ajouter un prêt pour ce livre" href="98" data-icon="u" class="button icon addLoan"></a>
						</dt>
					</dl>
				</div>
			</article>
			<article class="item" style="background-image: url(image.php?cover=book&amp;id=98);">
				<a title="voir le détail" href="98" data-icon="}" class="button icon detail"></a>
				<div class="block">
					<button data-icon="X" title="Fermer" class="button icon close listHidden">Fermer</button>
					<img class="cover" alt="" src="image.php?cover=book&amp;id=98">
					<dl class="info">
						<dd class="listHidden formTitle">Détail</dd>

						<dt class="listHidden">Titre</dt>
						<dd class="title">You Slay Me</dd>
						<dt class="listHidden">Auteur</dt>
						<dd class="listHidden">
							Katie MacAlister
							<a rel="author" title="Mettre à jour les informations de cet auteur" href="5" data-icon="P" class="listHidden button icon update"></a>
							<a rel="author" title="Supprimer cet auteur" href="5" data-icon="t" class="listHidden button icon delete"></a>
							<a target="_blank" title="Voir le site de l'auteur dans une nouvelle page" href="http://katiemacalister.com/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a target="_blank" title="Rechercher les livres de l'auteur sur internet" href="http://katiemacalister.com/books/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de cet auteur" href="Katie MacAlister" rel="author" data-icon="f" class="button icon filter"></a>
						</dd>
						<dt class="listHidden">Saga</dt>
						<dd class="saga">
							Aisling Grey - 1 / 4
							<a rel="saga" title="Mettre à jour les informations de cette saga" href="5" data-icon="P" class="listHidden button icon update"></a>
							<a rel="saga" title="Supprimer cette saga" href="5" data-icon="t" class="listHidden button icon delete"></a>
							<a target="_blank" title="Rechercher les livres de cette saga sur internet" href="http://katiemacalister.com/books/" data-icon="/" class="listHidden button icon externalLink"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de cette saga" href="Aisling Grey" rel="saga" data-icon="f" class="button icon filter"></a>
						</dd>
						<dt class="listHidden">Format</dt>
						<dd class="listHidden">poche</dd>
						<dt class="listHidden">Rangement</dt>
						<dd class="listHidden">
							chambre sous-sol - billy angle - A2
							<a rel="storage" title="Mettre à jour les informations de ce rangement" href="39" data-icon="P" class="listHidden button icon update"></a>
							<a href="storage/chambresous-sol_billyangle_A2.png" data-icon="}" class="listHidden button icon storage"></a>
							<a title="Filtrer la liste pour n'afficher que les livres de ce rangement" href="39" rel="storage" data-icon="f" class="listHidden button icon filter"></a>
						</dd>
						<dt class="listHidden actions">
							<a rel="book" title="Mettre à jour les informations de ce livre" href="98" data-icon="P" class="button icon update"></a>
							<a rel="book" title="Supprimer ce livre" href="98" data-icon="t" class="button icon delete"></a>
							<a rel="book" title="Ajouter un prêt pour ce livre" href="98" data-icon="u" class="button icon addLoan"></a>
						</dt>
					</dl>
				</div>
			</article>
		</section>
	</section>
</section>

		<!-- Grab local. fall back to Google CDN's jQuery if necessary -->
		<script src="js/libs/jquery-1.6.min.js"></script>
		<script>!window.jQuery && document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.js">\x3C/script>')</script>

		<!-- scripts concatenated and minified via ant build script-->
		<script src="js/libs/jquery.tmpl.min.js"></script>
		<script src="js/plugins.js"></script>
		<script src="js/script.js?v=<?php echo $js; ?>"></script>
		<!-- end scripts-->

		<script>
			$(document).ready(function(){
				$('.listContent', '#list_book').render();

				/*
					$('#holder').addClass('big');
					setTimeout(function(){ $('.listContent', '#list_book').addClass('big').render('relayout'); }, 100);

					$('#holder').removeClass('big');
					setTimeout(function(){ $('.listContent', '#list_book').removeClass('big').render('relayout'); }, 100);
				*/
			});

			(function( $ ){
				$.fn.render = function( method ){
					var cssRuleName = function () {
						var vendors = ["Moz", "Webkit", "Khtml", "O", "Ms"],
						    prefixes = ["-moz-", "-webkit-", "-khtml-", "-o-", "-ms-"],
							stamp = {};
						return function( rule ){
							el = document.documentElement;
							var styles = el.style,
								capitalRule, i, length;

							if( arguments.length === 1 && typeof stamp[ rule ] === "string" ) return stamp[ rule ];
							if( typeof styles[ rule ] === "string") return stamp[ rule ] = rule;
							capitalRule = rule.charAt(0).toUpperCase() + rule.slice(1);

							length = vendors.length;
							for( i = 0; i < length; i++ ){
								if( typeof styles[ vendors[i] + capitalRule ] === "string" ) return stamp[ rule ] = prefixes[i] + rule
							}
						}
					}();

					var self = this,
						$container, $items, $holder,
						defaults = {},
						settings = {},
						helper = {
							translate: Modernizr.csstransforms3d ? function( pos ){
								return "translate3d(" + pos[0] + "px, " + pos[1] + "px, 0)";
							} : function( pos ){
								return "translate(" + pos[0] + "px, " + pos[1] + "px)";
							},
							rules: function( rule, value ){
								var style = {};
								style[cssRuleName( rule )] = helper.translate(value);
								return style;
							},
							initRow: function( h ){
								settings.rows[ settings.currentRow ] = {
									width: 0,
									height: h,
									elements: []
								};
							}
						},
						methods = {
							init: function( options ){
								settings = $.extend({}, defaults, options)

								// iterate through all the DOM elements we are attaching the plugin to
								return this.each(function(){

									$container = $(this);
									$items = $container.children();

									methods.create();
									methods.resetLayout();
									methods.layout();
								});
							},
							create: function(){
								$holder = $('<div>', { id: 'holder', class: $container.attr('class') }).removeClass('listContent').append( $items.eq(0).clone().css({'background-image': null}).empty() );
								$holder.appendTo('body');

								settings.styles = [];
								$container.css({
									position: "relative",
								});
								$items.css({
									position: "absolute",
									top: 0,
									left: '50%'
								});
							},
							relayout: function(){
								return this.each(function(){
									$container = $(this);
									$items = $container.children();
									$holder = $('#holder');

									methods.resetLayout();
									methods.layout();
								});
							},
							layout: function(){
								settings.styles.push({
									$el: $container,
									styles: {'visibility': 'visible'}
								});

								settings.width = $container.width();

								settings.holderOuterWidth = $holder.children().outerWidth(true);
								settings.holderOuterHeight = $holder.children().outerHeight(true);

								//put each item in a row in order to get the combined row items width
								$items.each(function(){
									var $item = $(this);

									if( settings.rows[ settings.currentRow ].width + settings.holderOuterWidth > settings.width ){
										settings.currentRow++;
										helper.initRow( settings.rows[ settings.currentRow - 1 ].height + settings.holderOuterHeight );
									}

									settings.rows[ settings.currentRow ].width += settings.holderOuterWidth;
									settings.rows[ settings.currentRow ].elements.push( $item );
								});

								//for "centered" layout, calculate the left "margin" form the row width and the combined row items width
								$.each(settings.rows, function(i, row){
									settings.pos.x = (settings.width / 2) * -1 + (settings.width - row.width) / 2;
									settings.pos.y = row.height;

									//calculate the styles for the row items
									$.each(row.elements, function(i, $item){
										settings.styles.push({
											$el: $item,
											styles: helper.rules('transform', [ settings.pos.x, settings.pos.y ])
										});

										settings.pos.x += settings.holderOuterWidth;
									});
								});

								//applying styles
								$.each(settings.styles, function(i, couple){
									couple.$el.css(couple.styles);
								});

								//memory cleanning
								settings.rows = [];
								settings.styles = [];
							},
							resetLayout: function(){
								settings.pos = {
									x: 0,
									y: 0,
									height: 0
								};
								settings.styles = [];
								settings.rows = [];
								settings.currentRow = 0;
								helper.initRow( 0 );
							}
						};


					// if a method as the given argument exists
					if( methods[ method ] ){
						// call the respective method
						return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));

					// if an object is given as method OR nothing is given as argument
					} else if( typeof method === 'object' || !method ){
						// call the initialization method
						return methods.init.apply(this, arguments);

					// otherwise
					} else {
						// trigger an error
						$.error( 'Method "' +  method + '" does not exist in render plugin!');
					}
				}
			})(jQuery);
		</script>
	</body>
</html>