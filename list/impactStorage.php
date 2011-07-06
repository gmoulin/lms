<?php
	$types = array(
		'book' => 'ce livre',
		'movie' => 'ce film',
	);

if( count($response) ){
?>
	<form id="impactStorage" name="impactStorage" method="post" action="">
		<br />Vous devez au préalable définir un nouveau rangement pour les livres, films et albums suivant :<br />
		<ul class="impact clearfix">
			<?php
				$i = 0;
				foreach( $response as $impact ){ ?>
				<li>
					<input type="checkbox" id="<?php echo $impact['type']; ?>ID_<?php echo $i; ?>" name="<?php echo $impact['type']; ?>ID[]" value="<?php echo $impact['impactID']; ?>" />
					<label for="<?php echo $impact['type']; ?>ID_<?php echo $i; ?>"><?php echo $impact['impactTitle']; ?></label>
					<a class="button icon update" data-icon="P" href="<?php echo $impact['impactID']; ?>" title="Mettre à jour les informations de <?php echo $types[$impact['type']]; ?>" rel="<?php echo $impact['type']; ?>"></a>
				</li>
			<?php } ?>
		</ul>
		<label for="storageList">Rangement</label>
		<select id="storageList" name="storage" required>
			<option value="">nouveau lieu de rangement</option>
		</select>
		<button id="relocate" class="button">Enregistrer</button>
	</form>
<?php } ?>
