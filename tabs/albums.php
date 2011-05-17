<form id="album_filter" name="album_filter" action="" method="post" class="filterForm">
	<input type="hidden" name="albumSortType" id="albumSortType" value="<?php echo ( isset($_SESSION['albumListFilters']) && isset($_SESSION['albumListFilters']['albumSortType']) && !empty($_SESSION['albumListFilters']['albumSortType']) ? $_SESSION['albumListFilters']['albumSortType'] : 0 ); ?>" class="sortTypeField" autocomplete="off" />
	<ul class="listFilter">
		<li>
			<a href="filter" class="button category filterFormSwitch" rel="album" title="Filtrer les résultats">Filtrer</a>
		</li>

		<li>
			<label for="albumSearch">Recherche</label>
			<input type="search" name="albumSearch" id="albumSearch" value="" placeholder="dans les informations textuelles" />
		</li>

		<li>
			<label for="albumTitleFilter">Titre</label>
			<input type="search" name="albumTitleFilter" id="albumTitleFilter" value="" list="albumTitleFilterList" placeholder="d'album" />
			<datalist id="albumTitleFilterList"></datalist>
		</li>

		<li>
			<label for="albumBandFilter">Groupe</label>
			<input type="search" name="albumBandFilter" id="albumBandFilter" value="" list="albumBandFilterList" placeholder="d'album" />
			<datalist id="albumBandFilterList"></datalist>
		</li>

		<li>
			<label for="albumLoanFilter">Prêt</label>
			<input type="search" name="albumLoanFilter" id="albumLoanFilter" value="" list="albumLoanFilterList" placeholder="Album prêté à..." />
			<datalist id="albumLoanFilterList"></datalist>
		</li>

		<li>
			<label for="albumStorageFilter">Rangement</label>
			<select name="albumStorageFilter" id="albumStorageFilter">
				<option value="">Album rangé où</option>
			</select>
		</li>

		<li>
			<button type="submit" name="albumSearchSubmit" class="button search" data-icon="f">Rechercher</button>
			<button type="reset" name="albumSearchCancel" class="button cancel" data-icon="x">Annuler</button>
		</li>
	</ul>

	<aside class="listSort">
		<button class="button category add" rel="album" title="Ajouter un album"></button>
		<ol>
			<li>
				Tris :
			</li>
			<li>
				<a href="0" title="trier par titre" class="button sort both" rel="album"></a>
			</li>
			<li>
				<a href="2" title="trier par groupe" class="button sort both" rel="band"></a>
			</li>
			<li>
				<a href="4" title="trier par rangement" class="button sort both" rel="storage"></a>
			</li>
			<li>
				<a href="6" title="trier par date d'ajout" class="button sort both" rel="date"></a>
			</li>
		</ol>
	</aside>
</form>
<section id="list_album" class="list thumbnails hasCovers">
	<section class="listDisplaySwitch">
		<a href="#" title="format vignette" rel="thumbnails" class="button icon disabled" data-icon="2"></a>
		<a href="#" title="format encart" rel="smallBoxes" class="button icon" data-icon="c"></a>
		<a href="#" title="format demi page" rel="bigBoxes" class="button icon" data-icon="d"></a>
	</section>
</section>
