<?php
	$types = array(
		'book' => 'ce livre',
		'movie' => 'ce film',
	);
?>
<ul class="impact clearfix">
	<?php foreach( $response as $impact ){ ?>
		<li>
			<a class="button icon update" data-icon="P" href="<?php echo $impact['impactID']; ?>" title="Mettre à jour les informations de <?php echo $types[$impact['type']]; ?>" rel="<?php echo $impact['type']; ?>"></a>
			<?php echo $impact['impactTitle']; ?>
		</li>
	<?php } ?>
</ul>

