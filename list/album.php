<?php
	if( $type == 2 || ( $type == 0 && $_SESSION['albums']['page'] > 0 ) ){
		$nb = count($albums);
	} else {
		$nb = $_SESSION['albums']['numPerPage'] * $_SESSION['albums']['page'] + count($albums);
	}

	if( $nb > $_SESSION['albums']['total'] ) $nb = $_SESSION['albums']['total'];

	if( $nb > 0 ){
?>
		<section class="paginate <?php if( $nb == $_SESSION['albums']['total'] ){ ?>end<?php } else { ?>begin<?php } ?>">
			<span>
				<?php echo $nb; ?> album<?php if( $nb > 1){ echo 's'; } ?> sur <?php echo $_SESSION['albums']['total']; ?>
				<?php if( $nb != $_SESSION['albums']['total'] ){ ?>- descendez en bas de la liste pour en afficher plus <?php } ?>
			</span>
		</section>
<?php } else { ?>
		<section class="paginate end">
			<span>aucun album trouvé</span>
		</section>
<?php } ?>
<ul class="listContent new clearfix">
	<?php foreach( $albums as $album ){ ?>
		<li style="background-image:url(image.php?cover=album&id=<?php echo $album['albumID']; ?>);">
			<a class="button icon detail" data-icon="}" href="<?php echo $album['albumID']; ?>" title="voir le détail"></a>
			<?php if( !empty($album['loanHolder']) ){ ?>
				<span class="button icon loan" data-icon="u" title="prêtè à <?php echo $album['loanHolder'].' - '.$album['loanDate']; ?>"></span>
			<?php } ?>
			<article class="block">
				<img src="image.php?cover=album&id=<?php echo $album['albumID']; ?>" alt="" class="cover" />
				<dl class="info">
					<dt class="listHidden">Titre</dt>
					<dd class="title"><?php echo $album['albumTitle']; ?></dd>

					<dt class="listHidden">Auteur<?php echo ( count($album['bands']) > 1 ? 's' : '' ); ?></dt>
					<?php foreach( $album['bands'] as $band ){ ?>
						<dd class="listHidden">
							<?php echo $band['bandName'].' ('.$band['bandGenre'].')'; ?>
							<a class="listHidden button icon update" data-icon="P" href="<?php echo $band['bandID']; ?>" title="Mettre à jour les informations de ce groupe" rel="band"></a>
							<a class="listHidden button icon delete" data-icon="t" href="<?php echo $band['bandID']; ?>" title="Supprimer ce groupe" rel="band"></a>
							<?php if( !empty($band['bandWebSite']) ){ ?>
								<a class="listHidden button icon externalLink" data-icon="/" href="<?php echo $band['bandWebSite']; ?>" title="Voir le site du groupe dans une nouvelle page" target="_blank"></a>
							<?php } ?>
							<a class="button icon filter" data-icon="f" rel="band" href="<?php echo $band['bandFirstName'].' '.$band['bandLastName']; ?>" title="Filtrer la liste pour n'afficher que les albums de ce groupe"></a>
						</dd>
					<?php } ?>

					<dt class="listHidden">Type</dt>
					<dd class="listHidden"><?php echo $album['albumType']; ?></dd>

					<dt class="listHidden">Rangement</dt>
					<dd class="listHidden">
						<?php
							echo $album['storageRoom'].' - '.$album['storageType'].
								( !empty($album['storageColumn']) ? ' - '.$album['storageColumn'] : '').
								( !empty($album['storageLine']) ? $album['storageLine'] : '');

							$storageCodeURL = stripAccents('/storage/'.$album['storageRoom'].'_'.$album['storageType'].'_'.$album['storageColumn'].$album['storageLine'].'.png');
						?>
						<a class="listHidden button icon update" data-icon="P" href="<?php echo $album['storageID']; ?>" title="Mettre à jour les informations de ce rangement" rel="storage"></a>
						<!--
						<a class="listHidden button icon delete" data-icon="t" href="<?php echo $album['storageID']; ?>" title="Supprimer ce rangement" rel="storage"></a>
						-->
						<?php if( file_exists(LMS_PATH.$storageCodeURL) ){ ?>
							<a class="listHidden button icon storage" data-icon="}" href="<?php echo $storageCodeURL; ?>"></a>
						<?php } ?>
						<a class="listHidden button icon filter" data-icon="f" rel="storage" href="<?php echo $album['storageID']; ?>" title="Filtrer la liste pour n'afficher que les albums de ce rangement"></a>
					</dd>

					<?php if( !empty($album['loanHolder']) ){ ?>
						<dt class="listHidden">Prêt</dt>
						<dd class="listHidden">
							<?php echo $album['loanHolder'].' - '.$album['loanDate']; ?>
							<a class="button icon delete" data-icon="t" href="<?php echo $album['loanID']; ?>" title="Supprimer ce prêt" rel="loan"></a>
							<a class="button icon filter" data-icon="f" rel="loan" href="<?php echo $album['loanHolder']; ?>" title="Filtrer la liste pour n'afficher que les albums prêtés à cette personne"></a>
						</dd>
					<?php } ?>

					<dt class="listHidden actions">
						<a class="button icon update" data-icon="P" href="<?php echo $album['albumID']; ?>" title="Mettre à jour les informations de cet album" rel="album"></a>
						<a class="button icon delete" data-icon="t" href="<?php echo $album['albumID']; ?>" title="Supprimer cet album" rel="album"></a>
						<?php if( empty($album['loanHolder']) ){ ?>
							<a class="button icon addLoan" data-icon="u" href="<?php echo $album['albumID']; ?>" title="Ajouter un prêt pour cet album" rel="album"></a>
						<?php } ?>
					</dt>
				</dl>
			</article>
		</li>
	<?php } ?>
</ul>

