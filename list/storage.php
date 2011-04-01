<?php
	if( $type == 2 || ( $type == 0 && $_SESSION['storages']['page'] > 0 ) ){
		$nb = count($storages);
	} else {
		$nb = $_SESSION['storages']['numPerPage'] * $_SESSION['storages']['page'] + count($storages);
	}

	if( $nb > $_SESSION['storages']['total'] ) $nb = $_SESSION['storages']['total'];
	if( $nb > 0 ){
?>
		<div class="paginate <?php if( $nb == $_SESSION['storages']['total'] ){ ?>end<?php } else { ?>begin<?php } ?>">
			<span>
				<?php echo $nb; ?> rangement<?php if( $nb > 1){ echo 's'; } ?> sur <?php echo $_SESSION['storages']['total']; ?>
				<?php if( $nb != $_SESSION['storages']['total'] ){ ?>- descendez en bas de la liste pour en afficher plus <?php } ?>
			</span>
		</div>
<?php } else { ?>
		<section class="paginate end">
			<span>aucun rangement trouvé</span>
		</section>
<?php } ?>
<ul class="listContent new clearfix">
	<?php
		$currentStorage = '';
		foreach( $storages as $i => $storage ){
			if( $currentStorage != $storage['storageRoom'].$storage['storageType'] && !empty($storage['storageColumn']) ){
				if( $currentStorage != '' || $i > 0 ){
	?>
</ul>
<ul class="listContent new clearfix">
	<?php
				}
				$currentStorage = $storage['storageRoom'].$storage['storageType'];

				$currentStorageURL = stripAccents('/storage/'.$storage['storageRoom'].'_'.$storage['storageType'].'.png');
	?>
				<li style="background-image: url(<?php if( file_exists(LMS_PATH.$currentStorageURL) ){ echo $currentStorageURL; } ?>);">
					<article class="block">
						<dl class="info">
							<dt class="listHidden">Pièce</dt>
							<dd class="title">
								<?php echo $storage['storageRoom']; ?>
							</dd>
							<dt class="listHidden">Type</dt>
							<dd class="title">
								<?php echo $storage['storageType']; ?>
							</dd>
							<dd class="title">&nbsp;</dd>
						</dl>
					</article>
					<a class="button icon storage" data-icon="}" href="<?php echo $currentStorageURL; ?>"></a>
				</li>
	<?php
			}

			$storageCodeURL = stripAccents('/storage/'.$storage['storageRoom'].'_'.$storage['storageType'].( !empty($storage['storageColumn']) || !empty($storage['storageLine']) ? '_' : '' ).$storage['storageColumn'].$storage['storageLine'].'.png');
	?>

		<li style="background-image: url(<?php if( file_exists(LMS_PATH.$storageCodeURL) ){ echo $storageCodeURL; } ?>);">
			<article class="block">
				<dl class="info">
					<dt class="listHidden">Pièce</dt>
					<dd class="title">
						<?php echo $storage['storageRoom']; ?>
					</dd>
					<dt class="listHidden">Type</dt>
					<dd class="title">
						<?php echo $storage['storageType']; ?>
					</dd>
					<dd class="title detailHidden">
						<?php echo $storage['storageColumn'].$storage['storageLine']; ?>
					</dd>
					<dt class="listHidden">Colonne</dt>
					<dd class="listHidden">
						<?php echo $storage['storageColumn']; ?>
					</dd>
					<dt class="listHidden">Ligne</dt>
					<dd class="listHidden">
						<?php echo $storage['storageLine']; ?>
					</dd>
					<dt class="listHidden actions">
						<a class="button icon update" data-icon="P" href="<?php echo $storage['storageID']; ?>" title="Mettre à jour les informations de ce rangement" rel="storage"></a>
						<a class="button icon delete" data-icon="t" href="<?php echo $storage['storageID']; ?>" title="Supprimer ce rangement" rel="storage"></a>
						<?php if( file_exists(LMS_PATH.$storageCodeURL) ){ ?>
							<a class="button icon storage" data-icon="}" href="<?php echo $storageCodeURL; ?>"></a>
						<?php } ?>
					</dt>
				</dl>
			</article>
			<a class="button icon storage" data-icon="}" href="<?php echo $storageCodeURL; ?>"></a>
		</li>
	<?php } ?>
</ul>

