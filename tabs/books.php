<form id="book_filter" name="book_filter" action="" method="post" class="filterForm">
	<input type="hidden" name="bookSortType" id="bookSortType" value="<?php echo ( isset($_SESSION['bookListFilters']) && isset($_SESSION['bookListFilters']['bookSortType']) && !empty($_SESSION['bookListFilters']['bookSortType']) ? $_SESSION['bookListFilters']['bookSortType'] : 0 ); ?>" class="sortTypeField" autocomplete="off" />
	<ul class="listFilter">
		<li>
			<a href="filter" class="button category filterFormSwitch" rel="book" title="Filtrer les résultats">Filtrer</a>
		</li>

		<li>
			<label for="bookSearch">Recherche</label>
			<input type="search" name="bookSearch" id="bookSearch" value="" placeholder="dans les informations textuelles" />
		</li>

		<li>
			<label for="bookTitleFilter">Titre</label>
			<input type="search" name="bookTitleFilter" id="bookTitleFilter" value="" list="bookTitleFilterList" placeholder="de livre" />
			<datalist id="bookTitleFilterList"></datalist>
		</li>

		<li>
			<label for="bookSagaFilter">Saga</label>
			<input type="search" name="bookSagaFilter" id="bookSagaFilter" value="" list="bookSagaFilterList" placeholder="de livre" />
			<datalist id="bookSagaFilterList"></datalist>
		</li>

		<li>
			<label for="bookAuthorFilter">Auteur</label>
			<input type="search" name="bookAuthorFilter" id="bookAuthorFilter" value="" list="bookAuthorFilterList" placeholder="de livre" />
			<datalist id="bookAuthorFilterList"></datalist>
		</li>

		<li>
			<label for="bookLoanFilter">Prêt</label>
			<input type="search" name="bookLoanFilter" id="bookLoanFilter" value="" list="bookLoanFilterList" placeholder="Livre prêté à..." />
			<datalist id="bookLoanFilterList"></datalist>
		</li>

		<li>
			<label for="bookStorageFilter">Rangement</label>
			<select name="bookStorageFilter" id="bookStorageFilter">
				<option value="">Livre rangé où</option>
			</select>
		</li>

		<li>
			<button type="submit" name="bookSearchSubmit" class="button search" data-icon="f">Rechercher</button>
			<button type="reset" name="bookSearchCancel" class="button cancel" data-icon="x">Annuler</button>
		</li>
	</ul>

	<aside class="listSort">
		<button class="button category add" rel="book" title="Ajouter un livre"></button>
		<ol>
			<li>
				Tris :
			</li>
			<li>
				<a href="0" title="trier par saga" class="button sort both" rel="saga"></a>
			</li>
			<li>
				<a href="2" title="trier par titre" class="button sort both" rel="book"></a>
			</li>
			<li>
				<a href="4" title="trier par auteur" class="button sort both" rel="author"></a>
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
<section id="list_book" class="list smallBoxes hasCovers">
	<section id="listDisplaySwitch">
		<a href="#" title="format vignette" rel="smallBoxes" class="button icon disabled" data-icon="c"></a>
		<a href="#" title="format rectangle" rel="rectangleBoxes" class="button icon" data-icon="d"></a>
	</section>
</section>
