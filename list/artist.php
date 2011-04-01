<?php
	if( $type == 2 || ( $type == 0 && $_SESSION['artists']['page'] > 0 ) ){
		$nb = count($artists);
	} else {
		$nb = $_SESSION['artists']['numPerPage'] * $_SESSION['artists']['page'] + count($artists);
	}

	if( $nb > $_SESSION['artists']['total'] ) $nb = $_SESSION['artists']['total'];
	if( $nb > 0 ){
?>
		<div class="paginate <?php if( $nb == $_SESSION['artists']['total'] ){ ?>end<?php } else { ?>begin<?php } ?>">
			<span>
				<?php echo $nb; ?> artiste<?php if( $nb > 1){ echo 's'; } ?> sur <?php echo $_SESSION['artists']['total']; ?>
				<?php if( $nb != $_SESSION['artists']['total'] ){ ?>- descendez en bas de la liste pour en afficher plus <?php } ?>
			</span>
		</div>
<?php } else { ?>
		<section class="paginate end">
			<span>aucun artiste trouvé</span>
		</section>
<?php } ?>
<ul class="listContent new clearfix">
	<?php foreach( $artists as $artist ){ ?>
		<li>
			<article class="block">
				<dl class="info">
					<dd>
						<?php echo $artist['artistFirstName'].' '.$artist['artistLastName']; ?>

						<a class="button icon update" data-icon="P" href="<?php echo $artist['artistID']; ?>" rel="artist" title="Mettre à jour les informations de cet artiste">
						</a>

						<a class="button icon delete" data-icon="t" href="<?php echo $artist['artistID']; ?>" rel="artist" title="Supprimer cet artiste">
						</a>
					</dd>
				</dl>
			</article>
		</li>
	<?php } ?>
</ul>

