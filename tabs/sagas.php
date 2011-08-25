<form id="saga_filter" name="saga_filter" action="" method="post" class="filterForm">
	<input type="hidden" name="sagaSortType" id="sagaSortType" value="<?php echo ( isset($_SESSION['sagaListFilters']) && isset($_SESSION['sagaListFilters']['sagaSortType']) && !empty($_SESSION['sagaListFilters']['sagaSortType']) ? $_SESSION['sagaListFilters']['sagaSortType'] : 0 ); ?>" class="sortTypeField" autocomplete="off" />
	<ul class="listFilter">
		<li>
			<a href="filter" class="button category filterFormSwitch" rel="saga" title="Filtrer les résultats">Filtrer</a>
		</li>

		<li>
			<label for="sagaTitleFilter">Titre</label>
			<input type="search" name="sagaTitleFilter" id="sagaTitleFilter" value="" list="sagaTitleFilterList" placeholder="de la saga" />
			<datalist id="sagaTitleFilterList"></datalist>
		</li>

		<li>
			<button type="submit" name="sagaSearchSubmit" class="button search" data-icon="f">Rechercher</button>
			<button type="reset" name="sagaSearchCancel" class="button cancel" data-icon="x">Annuler</button>
		</li>
	</ul>

	<aside class="listSort">
		<button class="button category add" rel="saga" class="Ajouter une saga"></button>
		<ol>
			<li>
				Tris :
			</li>
			<li>
				<a href="0" title="trier par nom" class="button sort both" rel="saga"></a>
			</li>
			<li>
				<a href="2" title="trier par date de dernière visite du site web" class="button sort both" rel="date"></a>
				<!-- href value is used in a script.js function -->
			</li>
		</ol>
	</aside>
</form>
<section id="list_saga" class="list smallBoxes"></section>
