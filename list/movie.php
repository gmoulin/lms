<?php
	if( $type == 2 || ( $type == 0 && $_SESSION['movies']['page'] > 0 ) ){
		$nb = count($movies);
	} else {
		$nb = $_SESSION['movies']['numPerPage'] * $_SESSION['movies']['page'] + count($movies);
	}
	if( $nb > $_SESSION['movies']['total'] ) $nb = $_SESSION['movies']['total'];
	if( $nb > 0 ){
?>
		<div class="paginate <?php if( $nb == $_SESSION['movies']['total'] ){ ?>end<?php } else { ?>begin<?php } ?>">
			<span>
				<?php echo $nb; ?> film<?php if( $nb > 1){ echo 's'; } ?> sur <?php echo $_SESSION['movies']['total']; ?>
				<?php if( $nb != $_SESSION['movies']['total'] ){ ?>- descendez en bas de la liste pour en afficher plus <?php } ?>
			</span>
		</div>
<?php } else { ?>
		<section class="paginate end">
			<span>aucun film trouvé</span>
		</section>
<?php } ?>
<ul class="listContent new clearfix">
	<?php foreach( $movies as $movie ){ ?>
		<li style="background-image:url(image.php?cover=movie&id=<?php echo $movie['movieID']; ?>);">
			<a class="button icon detail" data-icon="}" href="<?php echo $movie['movieID']; ?>" title="voir le détail"></a>
			<?php if( !empty($movie['loanHolder']) ){ ?>
				<span class="button icon loan" title="prêté à <?php echo $movie['loanHolder'].' - '.$movie['loanDate']; ?>" data-icon="u"></span>
			<?php } ?>
			<article class="block">
				<img src="image.php?cover=movie&id=<?php echo $movie['movieID']; ?>" alt="" class="cover" />
				<dl class="info">
					<dt class="listHidden">Titre</dt>
					<dd class="title"><?php echo $movie['movieTitle']; ?></dd>

					<dt class="listHidden">Artiste<?php echo ( count($movie['artists']) > 1 ? 's' : '' ); ?></dt>
					<?php foreach( $movie['artists'] as $artist ){ ?>
						<dd class="listHidden">
							<?php echo $artist['artistFirstName'].' '.$artist['artistLastName']; ?>
							<a class="listHidden button icon update" data-icon="P" href="<?php echo $artist['artistID']; ?>" title="Mettre à jour les informations de cet artiste" rel="artist"></a>
							<a class="listHidden button icon delete" data-icon="t" href="<?php echo $artist['artistID']; ?>" title="Supprimer cet artiste" rel="artist"></a>
							<!-- artist photo -->
							<a class="button icon filter" data-icon="f" rel="artist" href="<?php echo $artist['artistFirstName'].' '.$artist['artistLastName']; ?>" title="Filtrer la liste pour n'afficher que les films de cet artiste"></a>
						</dd>
					<?php } ?>

					<?php if( !empty($movie['sagaTitle']) ){ ?>
						<dt class="listHidden">Saga</dt>
						<dd class="saga">
							<?php echo $movie['sagaTitle'].' - '.$movie['movieSagaPosition'].' / '.$movie['movieSagaSize']; ?>
							<a class="listHidden button icon update" data-icon="P" href="<?php echo $movie['sagaID']; ?>" title="Mettre à jour les informations de cette saga" rel="saga"></a>
							<a class="listHidden button icon delete" data-icon="t" href="<?php echo $movie['sagaID']; ?>" title="Supprimer cette saga" rel="saga"></a>
							<?php if( !is_null($movie['sagaSearchURL']) && $movie['sagaSearchURL'] != '' ){ ?>
								<a class="listHidden button icon externalLink" data-icon="/" href="<?php echo $movie['sagaSearchURL']; ?>" title="Rechercher les films de cette saga sur internet" target="_blank"></a>
							<?php } ?>
							<a class="button icon filter" data-icon="f" rel="saga" href="<?php echo $movie['sagaTitle']; ?>" title="Filtrer la liste pour n'afficher que les films de cette saga"></a>
						</dd>
					<?php } ?>

					<dt class="listHidden">Genre</dt>
					<dd><?php echo $movie['movieGenre']; ?></dd>

					<dt class="listHidden">Format</dt>
					<dd class="listHidden"><?php echo $movie['movieMediaType']; ?></dd>

					<dt class="listHidden">Durée</dt>
					<dd class="listHidden"><?php echo $movie['movieLength']; ?></dd>

					<dt class="listHidden">Rangement</dt>
					<dd class="listHidden">
						<?php
							echo $movie['storageRoom'].' - '.$movie['storageType'].
								( !empty($movie['storageColumn']) ? ' - '.$movie['storageColumn'] : '').
								( !empty($movie['storageLine']) ? $movie['storageLine'] : '');

							$storageCodeURL = '/storage/'.urlencode($movie['storageRoom'].'_'.$movie['storageType'].'_'.$movie['storageColumn'].$movie['storageLine']).'.png';
						?>
						<a class="button icon update" data-icon="P" href="<?php echo $movie['storageID']; ?>" title="Mettre à jour les informations de ce rangement" rel="storage"></a>
						<!--
						<a class="listHidden button icon delete" data-icon="t" href="<?php echo $movie['storageID']; ?>" title="Supprimer ce rangement" rel="storage"></a>
						-->
						<?php if( file_exists(LMS_PATH.$storageCodeURL) ){ ?>
							<a class="button icon storage" data-icon="}" href="<?php echo $storageCodeURL; ?>"></a>
						<?php } ?>
						<a class="button icon filter" data-icon="f" rel="storage" href="<?php echo $movie['storageID']; ?>" title="Filtrer la liste pour n'afficher que les films de ce rangement"></a>
					</dd>

					<?php if( !empty($movie['loanHolder']) ){ ?>
						<dt class="listHidden">Prêt</dt>
						<dd class="listHidden">
							<?php echo $movie['loanHolder'].' - '.$movie['loanDate']; ?>
							<a class="button icon delete" data-icon="t" href="<?php echo $movie['loanID']; ?>" title="Supprimer ce prêt" rel="loan"></a>
							<a class="button icon filter" data-icon="f" rel="loan" href="<?php echo $movie['loanHolder']; ?>" title="Filtrer la liste pour n'afficher que les films prêtés à cette personne"></a>
						</dd>
					<?php } ?>

					<dt class="listHidden actions">
						<a class="button icon update" data-icon="P" href="<?php echo $movie['movieID']; ?>" title="Mettre à jour les informations de ce film" rel="movie"></a>
						<a class="button icon delete" data-icon="t" href="<?php echo $movie['movieID']; ?>" title="Supprimer ce film" rel="movie"></a>
						<?php if( empty($movie['loanHolder']) ){ ?>
							<a class="button icon addLoan" data-icon="u" href="<?php echo $movie['movieID']; ?>" title="Ajouter un prêt pour ce film" rel="movie"></a>
						<?php } ?>
					</dt>
				</dl>
			</article>
		</li>
	<?php } ?>
</ul>

