<?php
$types = array(
	'book' => 'livre(s)',
	'movie' => 'film(s)',
);

$length = count($response);
if( $length ){
?>
	<form id="moveSaga" name="moveSaga" method="post" action="" class="form" rel="move">
		<span class="confirmation">Veuillez définir un nouveau rangement pour les livres et films suivant :</span>
		<?php
			$currentStorage = '';
			$currentType = '';
			foreach( $response as $i => $move ){
				if( $currentType != '' && $currentType != $move['type'] ){
		?>
		</fieldset>
		<fieldset>
			<ol>
				<li>
					<select id="storageList_<?php echo $currentType; ?>" name="storage_<?php echo $currentType; ?>" required>
						<option value="">nouveau lieu de rangement</option>
					</select>
					<label for="storageList_<?php echo $currentType; ?>">Rangement <?php echo $types[$currentType]; ?></label>
				</li>
			</ol>
		</fieldset>
		<?php
				}
				if( $currentType != $move['type'] ){
					$currentType = $move['type']
		?>
		<fieldset class="moveWrapper">


		<?php
				}

				if( $currentStorage != '' && $currentStorage != $move['currentStorage'] ){
		?>
					</ul>
				</li>
			</ul>
		<?php
				}
				if( $currentStorage != $move['currentStorage'] ){
					$currentStorage = $move['currentStorage']
		?>
			<ul class="move">
				<li>
					<label><?php echo $currentStorage; ?></label>
					<ul>
		<?php
				}
		?>
						<li>
							<label><?php echo $move['title']; ?></label>
						</li>
		<?php

				if( $i + 1 == $length ){
		?>
					</ul>
				</li>
			</ul>
		</fieldset>
		<fieldset>
			<ol>
				<li>
					<select id="storageList_<?php echo $currentType; ?>" name="storage_<?php echo $currentType; ?>" required>
						<option value="">nouveau lieu de rangement</option>
					</select>
					<label for="storageList_<?php echo $currentType; ?>">Rangement <?php echo $types[$currentType]; ?></label>
				</li>
			</ol>
		</fieldset>
		<?php
				}
			}
		?>
	</form>
<?php } ?>
