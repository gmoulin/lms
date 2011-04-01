<?php
	if( $type == 2 || ( $type == 0 && $_SESSION['books']['page'] > 0 ) ){
		$nb = count($books);
	} else {
		$nb = $_SESSION['books']['numPerPage'] * $_SESSION['books']['page'] + count($books);
	}

	if( $nb > $_SESSION['books']['total'] ) $nb = $_SESSION['books']['total'];

	if( $nb > 0 ){
?>
		<section class="paginate <?php if( $nb == $_SESSION['books']['total'] ){ ?>end<?php } else { ?>begin<?php } ?>">
			<span>
				<?php echo $nb; ?> livre<?php if( $nb > 1){ echo 's'; } ?> sur <?php echo $_SESSION['books']['total']; ?>
				<?php if( $nb != $_SESSION['books']['total'] ){ ?>- descendez en bas de la liste pour en afficher plus <?php } ?>
			</span>
		</section>
<?php } else { ?>
		<section class="paginate end">
			<span>aucun livre trouvé</span>
		</section>
<?php } ?>
<ul class="listContent new clearfix">
	<?php foreach( $books as $book ){ ?>
		<li style="background-image: url(image.php?cover=book&id=<?php echo $book['bookID']; ?>);">
			<a class="button icon detail" data-icon="}" href="<?php echo $book['bookID']; ?>" title="voir le détail"></a>
			<?php if( !empty($book['loanHolder']) ){ ?>
				<span class="button icon loan" data-icon="u" title="prêtè à <?php echo $book['loanHolder'].' - '.$book['loanDate']; ?>"></span>
			<?php } ?>
			<article class="block">
				<img src="image.php?cover=book&id=<?php echo $book['bookID']; ?>" alt="" class="cover" />
				<dl class="info">
					<dt class="listHidden">Titre</dt>
					<dd class="title"><?php echo $book['bookTitle']; ?></dd>

					<dt class="listHidden">Auteur<?php echo ( count($book['authors']) > 1 ? 's' : '' ); ?></dt>
					<?php foreach( $book['authors'] as $author ){ ?>
						<dd class="listHidden">
							<?php echo $author['authorFirstName'].' '.$author['authorLastName']; ?>
							<a class="listHidden button icon update" data-icon="P" href="<?php echo $author['authorID']; ?>" title="Mettre à jour les informations de cet auteur" rel="author"></a>
							<a class="listHidden button icon delete" data-icon="t" href="<?php echo $author['authorID']; ?>" title="Supprimer cet auteur" rel="author"></a>
							<?php if( !empty($author['authorWebSite']) ){ ?>
								<a class="listHidden button icon externalLink" data-icon="/" href="<?php echo $author['authorWebSite']; ?>" title="Voir le site de l'auteur dans une nouvelle page" target="_blank"></a>
							<?php } ?>
							<?php if( !empty($author['authorSearchURL']) ){ ?>
								<a class="listHidden button icon externalLink" data-icon="/" href="<?php echo $author['authorSearchURL']; ?>" title="Rechercher les livres de l'auteur sur internet" target="_blank"></a>
							<?php } ?>
							<a class="button icon filter" data-icon="f" rel="author" href="<?php echo $author['authorFirstName'].' '.$author['authorLastName']; ?>" title="Filtrer la liste pour n'afficher que les livres de cet auteur"></a>
						</dd>
					<?php } ?>

					<?php if( !empty($book['sagaTitle']) ){ ?>
						<dt class="listHidden">Saga</dt>
						<dd class="saga">
							<?php echo $book['sagaTitle'].' - '.$book['bookSagaPosition'].' / '.$book['bookSagaSize']; ?>
							<a class="listHidden button icon update" data-icon="P" href="<?php echo $book['sagaID']; ?>" title="Mettre à jour les informations de cette saga" rel="saga"></a>
							<a class="listHidden button icon delete" data-icon="t" href="<?php echo $book['sagaID']; ?>" title="Supprimer cette saga" rel="saga"></a>
							<?php if( !is_null($book['sagaSearchURL']) && $book['sagaSearchURL'] != '' ){ ?>
								<a class="listHidden button icon externalLink" data-icon="/" href="<?php echo $book['sagaSearchURL']; ?>" title="Rechercher les livres de cette saga sur internet" target="_blank"></a>
							<?php } ?>
							<a class="button icon filter" data-icon="f" rel="saga" href="<?php echo $book['sagaTitle']; ?>" title="Filtrer la liste pour n'afficher que les livres de cette saga"></a>
						</dd>
					<?php } ?>

					<dt class="listHidden">Format</dt>
					<dd class="listHidden"><?php echo $book['bookSize']; ?></dd>

					<dt class="listHidden">Rangement</dt>
					<dd class="listHidden">
						<?php
							echo $book['storageRoom'].' - '.$book['storageType'].
								( !empty($book['storageColumn']) ? ' - '.$book['storageColumn'] : '').
								( !empty($book['storageLine']) ? $book['storageLine'] : '');

							$storageCodeURL = stripAccents('/storage/'.$book['storageRoom'].'_'.$book['storageType'].'_'.$book['storageColumn'].$book['storageLine'].'.png');
						?>
						<a class="listHidden button icon update" data-icon="P" href="<?php echo $book['storageID']; ?>" title="Mettre à jour les informations de ce rangement" rel="storage"></a>
						<!--
						<a class="listHidden button icon delete" data-icon="t" href="<?php echo $book['storageID']; ?>" title="Supprimer ce rangement" rel="storage"></a>
						-->
						<?php if( file_exists(LMS_PATH.$storageCodeURL) ){ ?>
							<a class="listHidden button icon storage" data-icon="}" href="<?php echo $storageCodeURL; ?>"></a>
						<?php } ?>
						<a class="listHidden button icon filter" data-icon="f" rel="storage" href="<?php echo $book['storageID']; ?>" title="Filtrer la liste pour n'afficher que les livres de ce rangement"></a>
					</dd>

					<?php if( !empty($book['loanHolder']) ){ ?>
						<dt class="listHidden">Prêt</dt>
						<dd class="listHidden">
							<?php echo $book['loanHolder'].' - '.$book['loanDate']; ?>
							<a class="button icon delete" data-icon="t" href="<?php echo $book['loanID']; ?>" title="Supprimer ce prêt" rel="loan"></a>
							<a class="button icon filter" data-icon="f" rel="loan" href="<?php echo $book['loanHolder']; ?>" title="Filtrer la liste pour n'afficher que les livres prêtés à cette personne"></a>
						</dd>
					<?php } ?>

					<dt class="listHidden actions">
						<a class="button icon update" data-icon="P" href="<?php echo $book['bookID']; ?>" title="Mettre à jour les informations de ce livre" rel="book"></a>
						<a class="button icon delete" data-icon="t" href="<?php echo $book['bookID']; ?>" title="Supprimer ce livre" rel="book"></a>
						<?php if( empty($book['loanHolder']) ){ ?>
							<a class="button icon addLoan" data-icon="u" href="<?php echo $book['bookID']; ?>" title="Ajouter un prêt pour ce livre" rel="book"></a>
						<?php } ?>
					</dt>
				</dl>
			</article>
		</li>
	<?php } ?>
</ul>

