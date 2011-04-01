<?php
	if( $type == 2 || ( $type == 0 && $_SESSION['authors']['page'] > 0 ) ){
		$nb = count($authors);
	} else {
		$nb = $_SESSION['authors']['numPerPage'] * $_SESSION['authors']['page'] + count($authors);
	}

	if( $nb > $_SESSION['authors']['total'] ) $nb = $_SESSION['authors']['total'];
	if( $nb > 0 ){
?>
		<div class="paginate <?php if( $nb == $_SESSION['authors']['total'] ){ ?>end<?php } else { ?>begin<?php } ?>">
			<span>
				<?php echo $nb; ?> auteur<?php if( $nb > 1){ echo 's'; } ?> sur <?php echo $_SESSION['authors']['total']; ?>
				<?php if( $nb != $_SESSION['authors']['total'] ){ ?>- descendez en bas de la liste pour en afficher plus <?php } ?>
			</span>
		</div>
<?php } else { ?>
		<section class="paginate end">
			<span>aucun auteur trouvé</span>
		</section>
<?php } ?>
<ul class="listContent new clearfix">
	<?php foreach( $authors as $author ){ ?>
		<li>
			<article class="block">
				<dl class="info">
					<dd>
						<?php echo $author['authorFirstName'].' '.$author['authorLastName']; ?>

						<?php if( !empty($author['authorWebSite']) ){ ?>
							<a class="button icon externalLink" data-icon="/" href="<?php echo $author['authorWebSite']; ?>" title="Voir le site de l'auteur dans une nouvelle page" target="_blank"></a>
						<?php } ?>
						<?php if( !empty($author['authorSearchURL']) ){ ?>
							<a class="button icon externalLink" data-icon="/" href="<?php echo $author['authorSearchURL']; ?>" title="Rechercher les livres de l'auteur sur internet" target="_blank"></a>
						<?php } ?>

						<a class="button icon update" data-icon="P" href="<?php echo $author['authorID']; ?>" title="Mettre à jour les informations de cet auteur" rel="author">
						</a>

						<a class="button icon delete" data-icon="t" href="<?php echo $author['authorID']; ?>" title="Supprimer cet auteur" rel="author">
						</a>
					</dd>
				</dl>
			</article>
		</li>
	<?php } ?>
</ul>

