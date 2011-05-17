<form id="movie_filter" name="movie_filter" action="" method="post" class="filterForm">
	<input type="hidden" name="movieSortType" id="movieSortType" value="<?php echo ( isset($_SESSION['bookListFilters']) && isset($_SESSION['bookListFilters']['bookSortType']) && !empty($_SESSION['bookListFilters']['bookSortType']) ? $_SESSION['bookListFilters']['bookSortType'] : 0 ); ?>" class="sortTypeField" autocomplete="off" />
	<ul class="listFilter">
		<li>
			<a href="filter" class="button category filterFormSwitch" rel="movie" title="Filtrer les résultats">Filtrer</a>
		</li>
		<li>
			<label for="movieSearch">Recherche</label>
			<input type="search" name="movieSearch" id="movieSearch" value="" placeholder="dans les informations textuelles" />
		</li>

		<li>
			<label for="movieTitleFilter">Titre</label>
			<input type="search" name="movieTitleFilter" id="movieTitleFilter" value="" list="movieTitleFilterList" placeholder="de film" />
			<datalist id="movieTitleFilterList"></datalist>
		</li>

		<li>
			<label for="movieSagaFilter">Saga</label>
			<input type="search" name="movieSagaFilter" id="movieSagaFilter" value="" list="movieSagaFilterList" placeholder="de film" />
			<datalist id="movieSagaFilterList"></datalist>
		</li>

		<li>
			<label for="movieArtistFilter">Artiste</label>
			<input type="search" name="movieArtistFilter" id="movieArtistFilter" value="" list="movieArtistFilterList" placeholder="de film" />
			<datalist id="movieArtistFilterList"></datalist>
		</li>

		<li>
			<label for="movieLoanFilter">Prêt</label>
			<input type="search" name="movieLoanFilter" id="movieLoanFilter" value="" list="movieLoanFilterList" placeholder="Film prêté à..." />
			<datalist id="movieLoanFilterList"></datalist>
		</li>

		<li>
			<label for="movieStorageFilter">Rangement</label>
			<select name="movieStorageFilter" id="movieStorageFilter">
				<option value="">Film rangé où</option>
			</select>
		</li>
		<li>
			<button type="submit" name="movieSearchSubmit" class="button search" data-icon="f">Rechercher</button>
			<button type="reset" name="movieSearchCancel" class="button cancel" data-icon="x">Annuler</button>
		</li>
	</ul>

	<aside class="listSort">
		<button class="button category add" rel="movie" title="Ajouter un film"></button>
		<ol>
			<li>
				Tris :
			</li>
			<li>
				<a href="0" title="trier par saga" class="button sort both" rel="saga"></a>
			</li>
			<li>
				<a href="2" title="trier par titre" class="button sort both" rel="movie"></a>
			</li>
			<li>
				<a href="4" title="trier par artiste" class="button sort both" rel="artist"></a>
			</li>
			<li>
				<a href="6" title="trier par rangement" class="button sort both" rel="storage"></a>
			</li>
			<li>
				<a href="8" title="trier par date d'ajout" class="button sort both" rel="date"></a>
			</li>
		</ol>
	</aside>
</form>
<section id="list_movie" class="list thumbnails hasCovers">
	<section class="listDisplaySwitch">
		<a href="#" title="format vignette" rel="thumbnails" class="button icon disabled" data-icon="2"></a>
		<a href="#" title="format encart" rel="smallBoxes" class="button icon" data-icon="c"></a>
		<a href="#" title="format demi page" rel="bigBoxes" class="button icon" data-icon="d"></a>
	</section>
</section>