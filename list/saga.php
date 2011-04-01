<?php
	if( $type == 2 || ( $type == 0 && $_SESSION['sagas']['page'] > 0 ) ){
		$nb = count($sagas);
	} else {
		$nb = $_SESSION['sagas']['numPerPage'] * $_SESSION['sagas']['page'] + count($sagas);
	}

	if( $nb > $_SESSION['sagas']['total'] ) $nb = $_SESSION['sagas']['total'];
	if( $nb > 0 ){
?>
		<div class="paginate <?php if( $nb == $_SESSION['sagas']['total'] ){ ?>end<?php } else { ?>begin<?php } ?>">
			<span>
				<?php echo $nb; ?> saga<?php if( $nb > 1){ echo 's'; } ?> sur <?php echo $_SESSION['sagas']['total']; ?>
				<?php if( $nb != $_SESSION['sagas']['total'] ){ ?>- descendez en bas de la liste pour en afficher plus <?php } ?>
			</span>
		</div>
<?php } else { ?>
		<section class="paginate end">
			<span>aucune saga trouvée</span>
		</section>
<?php } ?>
<ul class="listContent new clearfix">
	<?php foreach( $sagas as $saga ){ ?>
		<li>
			<article class="block">
				<dl class="info">
					<dd>
						<?php echo $saga['sagaTitle']; ?>
						<?php if( !empty($saga['sagaSearchURL']) ){ ?>
							<a class="button icon externalLink" data-icon="/" href="<?php echo $saga['sagaSearchURL']; ?>" title="Rechercher les livres de la saga sur internet" target="_blank"></a>
						<?php } ?>

						<a class="button icon update" data-icon="P" href="<?php echo $saga['sagaID']; ?>" title="Mettre à jour les informations de cette saga" rel="saga"></a>
						<a class="button icon delete" data-icon="t" href="<?php echo $saga['sagaID']; ?>" title="Supprimer cette saga" rel="saga"></a>
						<a class="button icon move" data-icon="b" href="<?php echo $saga['sagaID']; ?>" title="Déplacer cette saga" rel="saga"></a>
					</dd>
				</dl>
			</article>
		</li>
	<?php } ?>
</ul>

