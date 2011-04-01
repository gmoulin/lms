<?php
	if( $type == 2 || ( $type == 0 && $_SESSION['bands']['page'] > 0 ) ){
		$nb = count($bands);
	} else {
		$nb = $_SESSION['bands']['numPerPage'] * $_SESSION['bands']['page'] + count($bands);
	}

	if( $nb > $_SESSION['bands']['total'] ) $nb = $_SESSION['bands']['total'];
	if( $nb > 0 ){
?>
		<div class="paginate <?php if( $nb == $_SESSION['bands']['total'] ){ ?>end<?php } else { ?>begin<?php } ?>">
			<span>
				<?php echo $nb; ?> groupe<?php if( $nb > 1){ echo 's'; } ?> sur <?php echo $_SESSION['bands']['total']; ?>
				<?php if( $nb != $_SESSION['bands']['total'] ){ ?>- descendez en bas de la liste pour en afficher plus <?php } ?>
			</span>
		</div>
<?php } else { ?>
		<section class="paginate end">
			<span>aucun groupe trouvé</span>
		</section>
<?php } ?>
<ul class="listContent new clearfix">
	<?php foreach( $bands as $band ){ ?>
		<li>
			<article class="block">
				<dl class="info">
					<dd>
						<?php echo $band['bandName'].' ('.$band['bandGenre'].')'; ?>

						<?php if( !empty($band['bandWebSite']) ){ ?>
							<a class="button icon externalLink" data-icon="/" rel="<?php echo $band['bandID']; ?>" href="<?php echo $band['bandWebSite']; ?>" title="Voir le site du groupe dans une nouvelle page" target="_blank"></a>
						<?php } ?>

						<a class="button icon update" data-icon="P" href="<?php echo $band['bandID']; ?>" title="Mettre à jour les informations de ce groupe" rel="band">
						</a>

						<a class="button icon delete" data-icon="t" href="<?php echo $band['bandID']; ?>" title="Supprimer ce groupe" rel="band">
						</a>
					</dd>
				</dl>
			</article>
		</li>
	<?php } ?>
</ul>

