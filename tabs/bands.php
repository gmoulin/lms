<form id="band_filter" name="band_filter" action="" method="post" class="filterForm">
	<input type="hidden" name="bandSortType" id="bandSortType" value="<?php echo ( isset($_SESSION['bandListFilters']) && isset($_SESSION['bandListFilters']['bandSortType']) && !empty($_SESSION['bandListFilters']['bandSortType']) ? $_SESSION['bandListFilters']['bandSortType'] : 0 ); ?>" class="sortTypeField" autocomplete="off" />
	<ul class="listFilter">
		<li>
			<a href="filter" class="button category filterFormSwitch" rel="band" title="Filtrer les résultats">Filtrer</a>
		</li>

		<li>
			<label for="bandNameFilter">Nom</label>
			<input type="search" name="bandNameFilter" id="bandNameFilter" value="" list="bandNameFilterList" placeholder="du groupe" />
			<datalist id="bandNameFilterList"></datalist>
		</li>

		<li>
			<label for="bandGenreFilter">Genre</label>
			<input type="search" name="bandGenreFilter" id="bandGenreFilter" value="" list="bandGenreFilterList" placeholder="du groupe" />
			<datalist id="bandGenreFilterList"></datalist>
		</li>

		<li>
			<button type="submit" name="bandSearchSubmit" class="button search" data-icon="f">Rechercher</button>
			<button type="reset" name="bandSearchCancel" class="button cancel" data-icon="x">Annuler</button>
		</li>
	</ul>

	<aside class="listSort">
		<button class="button category add" rel="band" class="Ajouter un groupe"></button>
		<ol>
			<li>
				Tris :
			</li>
			<li>
				<a href="0" title="trier par nom" class="button sort both" rel="band"></a>
			</li>
			<li>
				<a href="2" title="trier par date de dernière visite du site web" class="button sort both" rel="date"></a>
				<!-- href value is used in a script.js function -->
			</li>
		</ol>
	</aside>
</form>
<section id="list_band" class="list smallBoxes"></section>
