$(document).ready(function(){
	//for .add_another positonning bug in firefox
	if( $.browser.mozilla ) $('html').addClass('mozilla');
	else if( $.browser.webkit ) $('html').addClass('webkit');

	//ajax global management
		$('#ajax_loader').ajaxStart(function(){
			console.log('ajaxStart');
			$('#ajax_loader').addClass('loading');
			$(this).empty(); //global error message deletion
		})
		.ajaxStop(function(){
			console.log('ajaxStop');
			$('#ajax_loader').removeClass('loading');
		})
		.ajaxError(function(event, xhr, settings, exception){
			console.log('ajaxError');
			if( xhr.responseText != '' ) inform("Error requesting page " + settings.url + ", error : " + xhr.responseText, 'error');
		});

	//tab change via menu and url#hash
		window.addEventListener("hashchange", tabSwitch, false);

	//launch first tab
		tabSwitch();

	//reset the modals
	//sometimes the "show" checkbox is checked on page load
		$('#editHide, #detailHide, #storageHide, #previewHide, #confirmHide').click();

	//input type number crossbrowser support
	//@todo remove when firefox fully support input type number
		$('.spinbox:not(.spinbox-active)').spinbox({
			min: 1,		// Set lower limit or null for no limit.
			max: null,	// Set upper limit or null for no limit.
			step: 1,	// Set increment size.
			reset: 0,	// reset value
		});

	//author, band and artist list in forms
		$('.add_another').click(function(event){
			console.log('add_another click');
			event.preventDefault();

			if( !$(this).is('button') ) return;

			var $list = $(this).siblings('ol'),
				$anotherBlock = $list.find('.anotherInfo:last').clone(true),
				tmp = $anotherBlock.find('input').attr('id').split('_'),
				indice = parseInt(tmp[1]);

			$anotherBlock.find('input')
				.attr('id', function(index, attr){ return attr.replace(new RegExp(indice), indice + 1); })
				.attr('name', function(index, attr){ return attr.replace(new RegExp(indice), indice + 1); })
				.val('') //reseting the value
				.siblings('label').attr('for', $anotherBlock.find('input').attr('id'));

			$list.append( $anotherBlock );
		});

		$('.delete_another').click(function(event){
			console.log('delete_another click');
			event.preventDefault();

			if( !$(this).is('button') ) return;

			//only remove if not the only one
			if( $(this).closest('ol').children('li').length > 1 ){
				$(this).closest('.anotherInfo').remove();
			} else { //reset the input
				$(this).siblings('input').val('');
			}
		});

	//tabs filter section
		$('.filterForm')
			.delegate('.filterFormSwitch', 'click', function(e){
				console.log('filterFormSwitch click');
				e.preventDefault();

				if( !$(this).is('a') ) return;

				var $ul = $(this).closest('.listFilter');
				if( !$ul.hasClass('deploy') ){
					$ul.addClass('deploy');

					//load the filters values
					$('datalist, select', $ul).loadList();

				} else {
					$ul.removeClass('deploy');
				}
			}).delegate('.search', 'click', function(e){
				console.log('search click');
				e.preventDefault();
				hideInform();
				getList(1);
			}).delegate('.cancel', click, function(e){
				console.log('cancel click');
				e.preventDefault();
				$(':input', $(this).closest('.filterForm')).val(''); //filter form reset
				hideInform();
				getList(0);
			});

	//sort result links
		$('.sort').click(function(e){
			console.log('sort buttons click');
			e.preventDefault();
			var t = $(this);

			if( !t.is('a') ) return;

			//save clicked link current icon
			var tmp = t.attr('class');
			var form = t.closest('.filterForm');

			//each index correspond to a "ORDER BY" string in php database tables classes
			var index = t.attr('href');

			//reset all links icons
			$('.sort', form).removeClass('asc').removeClass('desc').addClass('both');

			//set back clicked link icon
			t.attr('class', tmp);

			//default state -> asc
			if( t.hasClass('both') ){
				t.toggleClass('both asc');

			//asc state -> desc
			} else if( t.hasClass('asc') ){
				t.toggleClass('asc desc');
				index++;

			//desc state -> default
			} else if( t.hasClass('desc') ){
				t.toggleClass('desc both');

				//reseting sort type to 0
				$('.sort:first', form).click();
				return;
			}

			$('.sortTypeField', form).val(index);

			hideInform();
			getList(1);
		});

	//result list filter icon links
		$('.filter').live('click', function(e){
			console.log('filter click');
			e.preventDefault();

			if( !$(this).is('a') ) return;

			var rel = $(this).attr('rel');
			if( !rel.length ) return;

			rel = rel.charAt(0).toUpperCase() + rel.substr(1);

			var t = $('#nav').data('activeTab');

			$('#' + t + rel +'Filter').val( $(this).attr('href') );

			$('#' + t + '_filter .listFilter').addClass('deploy');

			hideInform();
			getList(1);

			$('#detailHide, #editHide, #previewHide, #storageHide, #confirmHide').attr('checked', 'checked');
		});

	//add, update, delete
		$('.add').click(function(e){
			e.preventDefault();
			console.log('add click');

			if( !$(this).is('button') ) return;

			var rel = $(this).attr('rel');

			//raz
			var link = '';
			if( rel == 'book' ) link = 'bookAuthors';
			else if( rel == 'album' ) link = 'albumGroups';
			else if( rel == 'movie' ) link = 'movieArtists';
			if( link != '' ){
				$('.anotherInfo:gt(0)', '#' + link).remove();

				$('.anotherInfo input', '#' + link)
					.attr('id', function(index, attr){ return attr.replace(new RegExp(attr.substr(-1)), 1); })
					.attr('name', function(index, attr){ return attr.replace(new RegExp(attr.substr(-1)), 1); })
					.siblings('label').attr('for', $('.anotherInfo input', '#' + link).attr('id'));

			}
			$(':input', '#manage_' + rel).val('');
			$('.coverStatus', '#manage_' + rel).html(function(){
				var tmp = 'Déposer ';
				if( rel == 'book' ) tmp += 'la couverture';
				else if( rel == 'movie' ) tmp += 'l\'affiche';
				else if( rel == 'album' ) tmp += 'la couverture';
				else if( rel == 'storage' ) tmp += 'la photo';
			});
			$('#editPreview').empty();
			//setting action
			$('#' + rel + 'Action').val('add');

			//hidding and emptying results
			hideInform();

			var text = '';
			if( rel == 'book' ){
				text = 'Enregistrer le nouveau livre';
				$('#bookSagaTitle').change(); //fire the change event
			} else if( rel == 'movie' ){
				text = 'Enregistrer le nouveau film';
				$('#movieSagaTitle').change(); //fire the change event
			}
			else if( rel == 'album' ) text = 'Enregistrer le nouvel album';
			else if( rel == 'saga' ) text = 'Enregistrer la nouvelle saga';
			else if( rel == 'author' ) text = 'Enregistrer le nouvel auteur';
			else if( rel == 'artist' ) text = 'Enregistrer le nouvel artiste';
			else if( rel == 'band' ) text = 'Enregistrer le nouveau groupe';
			else if( rel == 'storage' ) text = 'Enregistrer le nouveau rangement';

			//place the form
			$('#manage_' + rel)
				.append( $('<button>', { 'class': 'button icon close', 'title': 'Fermer', 'data-icon': 'X' }).text('Fermer') )
				.appendTo( $('#editForm .formWrapper').empty() );

			//set the form title
			$('#editForm .formTitle').html(text);

			//set the submit button text
			$('#formSubmit').data('save_clicked', 0).attr('rel', 'add').text('Enregistrer');

			//open the modal dialog
			$('#editShow').attr('rel', rel).click();
		});

		$('.update').live('click', function(e){
			console.log('update click');
			e.preventDefault();

			if( !$(this).is('a') ) return;

			var rel = $(this).attr('rel');
			var target = rel.charAt(0).toUpperCase() + rel.substr(1);
			var link = '';
			if( rel == 'book' ) link = 'bookAuthors';
			else if( rel == 'movie' ) link = 'movieArtists';
			else if( rel == 'album' ) link = 'albumGroups';

			//raz
			if( link != '' ) $('.anotherInfo:gt(0)', '#' + link).remove();
			$(':input', '#manage_' + rel).val('');
			$('.coverStatus', '#manage_' + rel).html(function(){
				var tmp = 'Déposer ';
				if( rel == 'book' ) tmp += 'la couverture';
				else if( rel == 'album' ) tmp += 'la couverture';
				else if( rel == 'movie' ) tmp += 'l\'affiche';
				else if( rel == 'storage' ) tmp += 'la photo';
			});
			var editPreview = $('#editPreview').empty();
			//setting action
			$('#' + rel + 'Action').val('update');

			//hidding and emptying results
			hideInform();

			var text = '';
			if( rel == 'book' ){
				text = 'Modifier les informations du livre';
				$('#bookSagaTitle').change(); //fire the change event
			}
			if( rel == 'movie' ){
				text = 'Modifier les informations du film';
				$('#movieSagaTitle').change(); //fire the change event
			}
			if( rel == 'album' ) text = 'Modifier les informations de l\'album';
			if( rel == 'saga' ) text = 'Modifier les informations de la saga';
			if( rel == 'author' ) text = 'Modifier les informations de l\'auteur';
			if( rel == 'artist' ) text = 'Modifier les informations de l\'artiste';
			if( rel == 'band' ) text = 'Modifier les informations du groupe';
			if( rel == 'storage' ) text = 'Modifier les informations du rangement';

			//place the form
			$('#manage_' + rel).appendTo( $('#editForm .formWrapper').empty() );

			$('#editForm .formTitle').html(text);
			$('#formSubmit').data('save_clicked', 0).attr('rel', 'update').text('Enregistrer');

			//open the modal dialog
			$('#editShow').attr('rel', rel).click();

			var decoder = $('<textarea>');
			//load the data and set the form fields with it
			$.post('ajax/manage' + target + '.php', 'action=get&id=' + $(this).attr('href'), function(data){
				switch( rel ){
					case 'book':
							$('#bookID').val(data.bookID);
							$('#bookTitle').val(decoder.html(data.bookTitle).val());
							$('#bookSize').val(data.bookSize);
							$('#bookCover').val(data.bookCover);
							$('<img>', { src : 'image.php?cover=book&id=' + data.bookID }).appendTo( editPreview );
							$('#bookSagaTitle').val(decoder.html(data.sagaTitle).val()).change();
							$('#bookSagaPosition').val(data.bookSagaPosition);

							//options for this select are reseted by initBookFormList()
							//at this point the list can be empty
							$('#bookStorage').data('selectedId', data.storageID);

							var indice = 1;
							$.each(data.authors, function(i, author){
								if( indice > 1 ) $('#another_author').click();
								$('#bookAuthor_' + indice).val(decoder.html(author.authorFirstName+' '+author.authorLastName).val());
								indice++;
							});
						break;
					case 'movie':
							$('#movieID').val(data.movieID);
							$('#movieTitle').val(decoder.html(data.movieTitle).val());
							$('#movieGenre').val(decoder.html(data.movieGenre).val());
							$('#movieMediaType').val(decoder.html(data.movieMediaType).val());
							$('#movieLength').val(data.movieLength);
							$('#movieCover').val(data.movieCover);
							$('<img>', { src : 'image.php?cover=movie&id=' + data.movieID }).appendTo( editPreview );
							$('#movieSagaTitle').val(decoder.html(data.sagaTitle).val()).change();
							$('#movieSagaPosition').val(data.movieSagaPosition);

							//options for this select are reseted by initMovieFormList()
							//at this point the list can be empty
							$('#movieStorage').data('selectedId', data.storageID);

							var indice = 1;
							$.each(data.artists, function(i, artist){
								if( indice > 1 ) $('#another_artist').click();
								$('#movieArtist_' + indice).val(decoder.html(artist.artistFirstName+' '+artist.artistLastName).val());
								indice++;
							});
						break;
					case 'album':
							$('#albumID').val(data.albumID);
							$('#albumTitle').val(decoder.html(data.albumTitle).val());
							$('#albumType').val(data.albumType);
							$('#albumCover').val(data.albumCover);
							$('<img>', { src : 'image.php?cover=album&id=' + data.albumID }).appendTo( editPreview );

							//options for this select are reseted by initAlbumFormList()
							//at this point the list can be empty
							$('#albumStorage').data('selectedId', data.storageID);

							var indice = 1;
							$.each(data.bands, function(i, band){
								if( indice > 1 ) $('#another_band').click();
								$('#albumBand_' + indice).val(decoder.html(band.bandName).val());
								indice++;
							});
						break;
					case 'author':
							$('#authorID').val(data.authorID);
							$('#authorFirstName').val(decoder.html(data.authorFirstName).val());
							$('#authorLastName').val(decoder.html(data.authorLastName).val());
							$('#authorWebSite').val(data.authorWebSite);
							$('#authorSearchURL').val(data.authorSearchURL);
						break;
					case 'band':
							$('#bandID').val(data.bandID);
							$('#bandName').val(decoder.html(data.bandName).val());
							$('#bandGenre').val(decoder.html(data.bandGenre).val());
							$('#bandWebSite').val(data.bandWebSite);
						break;
					case 'artist':
							$('#artistID').val(data.artistID);
							$('#artistFirstName').val(decoder.html(data.artistFirstName).val());
							$('#artistLastName').val(decoder.html(data.artistLastName).val());
							//$('#artistPhoto').val(data.artistPhoto);
							//$('<img>', { src : 'image.php?cover=artist&id=' + data.artistID }).appendTo( editPreview );
						break;
					case 'saga':
							$('#sagaID').val(data.sagaID);
							$('#sagaTitle').val(decoder.html(data.sagaTitle).val());
							$('#sagaSearchURL').val(data.sagaSearchURL);
						break;
					case 'storage':
							$('#storageID').val(data.storageID);
							$('#storageRoom').val(data.storageRoom);
							$('#storageType').val(data.storageType);
							$('#storageColumn').val(data.storageColumn);
							$('#storageLine').val(data.storageLine);
							$('<img>', { src : 'image.php?cover=storage&id=' + data.storageID }).appendTo( editPreview );
						break;
				}
			});
		});

		$('.delete').live('click', function(e){
			console.log('delete click');
			e.preventDefault();

			if( !$(this).is('a') ) return;

			//hidding and emptying results
			hideInform();

			var id = $(this).attr('href');
			var rel = $(this).attr('rel');
			var target = rel.charAt(0).toUpperCase() + rel.substr(1);

			var text = '';
			if( rel == 'book' ) text = 'Etes-vous sûr de vouloir supprimer ce livre ?';
			else if( rel == 'album' ) text = 'Etes-vous sûr de vouloir supprimer cet album ?';
			else if( rel == 'movie' ) text = 'Etes-vous sûr de vouloir supprimer ce film ?';
			else if( rel == 'saga' ) text = 'Etes-vous sûr de vouloir supprimer cette saga ?<br />Tous les livres et films listés ci-dessous seront également supprimés !';
			else if( rel == 'author' ) text = 'Etes-vous sûr de vouloir supprimer cet auteur ?<br />Tous les livres listés ci-dessous seront également supprimés !';
			else if( rel == 'artist' ) text = 'Etes-vous sûr de vouloir supprimer cet artiste ?<br />Tous les films listés ci-dessous seront également supprimés !';
			else if( rel == 'band' ) text = 'Etes-vous sûr de vouloir supprimer ce groupe ?<br />Tous les albums listés ci-dessous seront également supprimés !';
			else if( rel == 'storage' ) text = 'Etes-vous sûr de vouloir supprimer ce rangement ?';
			else if( rel == 'loan' ) text = 'Etes-vous sûr de vouloir supprimer ce prêt ?';

			var formWrapper = $('#confirmForm .formWrapper').html( $('<span class="confirmation">').append(text) );

			//set the submit button text
			$('#confirmSubmit').data('save_clicked', 0);

			//open the modal dialog
			$('#confirmShow').attr('rel', rel).data('id', id).click();

			if( rel == 'saga' || rel == 'author' || rel == 'artist' || rel == 'band' ){
				//chargement de la liste des livres ou films impactés
				$.ajax({
					url: 'ajax/manage' + target + '.php',
					type: 'POST',
					data: 'action=impact&id=' + id,
					async: false,
					dataType: 'html',
					success: function(data){
						formWrapper.append(data);
					}
				});
			}

			if( rel == 'storage' ){
				//chargement de la liste des livres ou films impactés
				$.ajax({
					url: 'ajax/manage' + target + '.php',
					type: 'POST',
					data: 'action=impact&id=' + id,
					async: false,
					dataType: 'html',
					success: function(data){
						formWrapper.append(data);
						$('select', formWrapper).loadList();

						$('#confirmSubmit').attr("disabled", "disabled");
					}
				});
			}
		});

		$('#relocate').live('click', function(e){
			console.log('relocate click');
			e.preventDefault();

			if( !$(this).is('button') ) return;

			hideInform();

			if( $('#storageList').val() == '' ){
				formErrors([['storageList', 'Le nouveau rangement est requis.', 'required']]);
			} else {
				$.post('ajax/manageStorage.php', 'action=relocate&' + $.param( $('input:checked, select', '#impactStorage'), true ), function(data){
					if( data == 'ok' ){
						//inform user
						inform('Nouvelle allocation effectuée', 'success');

						$(':input:checked', '#impactStorage').parent().remove();
						if( !$('input', '#impactStorage').length ){
							$('#impactStorage').remove();
							$('#formSubmit').attr("disabled", "");
						}
					}
				});
			}
		});

		$('.move').live('click', function(e){
			console.log('move click');
			e.preventDefault();

			if( !$(this).is('a') ) return;

			//hidding and emptying results
			hideInform();

			var id = $(this).attr('href');
			var rel = $(this).attr('rel');
			var target = rel.charAt(0).toUpperCase() + rel.substr(1);

			var text = 'Etes-vous sûr de vouloir changer le rangement de cette saga ?';

			var formWrapper = $('#editForm .formWrapper').empty();

			$('#editForm .formTitle').html('Modification du rangement');

			//set the submit button text
			$('#formSubmit').data('save_clicked', 0);

			$('#editPreview').empty();

			//chargement de la liste des livres ou films concernés
			$.ajax({
				url: 'ajax/manage' + target + '.php',
				type: 'POST',
				data: 'action=moveImpact&id=' + id,
				async: false,
				dataType: 'html',
				success: function(data){
					formWrapper.append(data);
				}
			});

			//open the modal dialog
			$('#editShow').attr('rel', 'move').data('id', id).click();
		});

		$('.addLoan').live('click', function(e){
			e.preventDefault();
			console.log('addLoan click');

			if( !$(this).is('a') ) return;

			$(':input', '#manage_loan').val('');
			$('#editPreview').empty();
			//setting action
			$('#loanAction').val('add');
			$('#loanFor').val( $(this).attr('rel') );
			$('#itemID').val( $(this).attr('href') );

			//hidding and emptying results
			hideInform();

			var text = 'Enregistrer le nouveau prêt';

			//place the form
			$('#manage_loan')
				.append( $('<button>', { 'class': 'button icon close', 'title': 'Fermer', 'data-icon': 'X' }).text('Fermer') )
				.appendTo( $('#editForm .formWrapper').empty() );

			//set the form title
			$('#editForm .formTitle').html(text);

			//set the submit button text
			$('#formSubmit').data('save_clicked', 0).attr('rel', 'add').text('Enregistrer');

			//open the modal dialog
			$('#editShow').attr('rel', 'loan').click();
		});

	//list and detail actions
		$('.storage').live('click', function(e){
			console.log('storage click');
			e.preventDefault();

			if( !$(this).is('a') ) return;

			//storage list case
			if( $(this).closest('#list_storage').length ){
				//saving for detail display after list refresh if needed
				$('#detailBox').data('link', $(this).attr('href'));
				$('#detailBox').data('tab', $('#nav').data('activeTab'));

				$('#detail').html(
					$('.block', $(this).parent()).clone(true)
						.append( $('<button>', { 'class': 'button icon close', 'title': 'Fermer', 'data-icon': 'X' }).text('Fermer') )
				);

				$('<dd>',{ 'class': 'formTitle'}).text('Détail')
					.prependTo( $('#detail .info') );

				$('#detailShow').click();

				$('#storageImg').attr('src', $(this).attr('href') );
				$('#storageShow').click();

			//in detailBox case
			} else {
				if( $('#storageShow:checked').length ) $('#storageHide').click();
				else {
					$('#storageImg').attr('src', $(this).attr('href') );
					$('#storageShow').click();
				}
			}
		});

		$('.detail').live('click', function(e){
			console.log('detail click');
			e.preventDefault();

			if( !$(this).is('a') ) return;

			//saving for detail display after list refresh if needed
			$('#detailBox').data('link', $(this).attr('href'));
			$('#detailBox').data('tab', $('#nav').data('activeTab'));

			$('#storageHide').attr('checked', 'checked');
			$('#detail').html(
				$('.block', $(this).parent()).clone(true)
					.append( $('<button>', { 'class': 'button icon close', 'title': 'Fermer', 'data-icon': 'X' }).text('Fermer') )
			);
			$('<dd>',{ 'class': 'formTitle'}).text('Détail')
				.prependTo( $('#detail .info') );

			$('#detailBox .cover').attr('src', function(){ return this.src + '&ts=' + e.timeStamp; });

			$('#detailShow').click();
		});

	//forms actions
		$('.form').each(function(){
			//add event listener for dynamic form validation
			this.addEventListener("invalid", checkField, true);
			this.addEventListener("blur", checkField, true);
			this.addEventListener("input", checkField, true);

			$(this).keypress(function(e){
				if( e.keyCode == 13 ){
					e.preventDefault();
					e.stopPropagation();
					$('#formSubmit').click();
				}
			});
		});

		//add and update
		$('#formSubmit').click(function(e){
			console.log('formSubmit click');
			e.preventDefault();
			var button = $(this);

			var section = button.closest('.wrapper').find('.form'); //#manage_xxx
			if( !section.length ){
				return;
			}
			var rel = $('#editBox .form').attr('rel');
			var target = rel.charAt(0).toUpperCase() + rel.substr(1);

			//multiple call protection
			if( button.data('save_clicked') != 1 ){
				button.data('save_clicked', 1);

				//hidding and emptying results
				hideInform();

				if( rel != 'move' ){
					$.ajax({
						url: 'ajax/manage' + target + '.php',
						data: $(':input', section).serialize(),
						type: 'POST',
						dataType: 'json',
						complete: function(){
							button.data('save_clicked', 0);
						},
						success: function(data){
							if( data == 'ok' ){
								//inform user
								inform( ( $('#' + rel + 'Action').val() == 'add' ? 'Ajout effectué' : 'Mise à jour effectuée' ), 'success' );

								//modal close
								$('#editHide').click();

								if( $('#confirmShow:checked').length ){
									//update impact list
									if( rel == 'saga' || rel == 'author' || rel == 'artist' || rel == 'band' ){
										//chargement de la liste des livres impactés
										$.ajax({
											url: 'ajax/manage' + target + '.php',
											type: 'POST',
											data: 'action=impact&id=' + id,
											async: false,
											dataType: 'html',
											success: function(data){
												$('#confirmForm .impact').remove();
												$('#confirmForm .formWrapper').append(data);
											}
										});
									}

									if( rel == 'storage' ){
										//chargement de la liste des livres impactés
										$.ajax({
											url: 'ajax/manage' + target + '.php',
											type: 'POST',
											data: 'action=impact&id=' + id,
											async: false,
											dataType: 'html',
											success: function(data){
												$('#impactStorage').remove();
												$('#confirmForm .formWrapper').append(data);
												$('select', formWrapper).loadList();

												$('#confirmSubmit').attr("disabled", "disabled");
											}
										});
									}

								} else {
									//refresh list
									getList(2);
								}
							} else {
								//inform user
								inform( 'Erreur durant la validation du formulaire', 'error' );

								//form errors display
								formErrors(data);
							}
						}
					});
				} else {
					//send storage change
					$.post('ajax/manageSaga.php', 'action=move&id=' + $('#editShow').data('id') + '&' + $('#moveSaga').serialize(), function(data){
						if( data == 'ok' ){
							//inform user
							inform('Modification effectuée', 'success');

							//modal close
							$('#editHide').click();

							//refresh list
							getList(2);
						} else {
							//inform user
							inform('Erreur durant la modification', 'error');
							//form errors display
							formErrors(data);
						}
					});
				}
			}
		});

		$('#formCancel').click(function(e){
			e.preventDefault();

			$('#editHide').click();
		});

		//delete
		$('#confirmSubmit').click(function(e){
			console.log('confirmSubmit');
			e.preventDefault();
			var button = $(this);

			var section = button.closest('.wrapper');
			var rel = $('#confirmShow').attr('rel');
			var target = rel.charAt(0).toUpperCase() + rel.substr(1);

			//multiple call protection
			if( button.data('save_clicked') != 1 ){
				button.data('save_clicked', 1);

				//hidding and emptying results
				hideInform();

				//send delete
				$.post('ajax/manage' + target + '.php', 'action=delete&id=' + $('#confirmShow').data('id'), function(data){
					if( data == 'ok' ){
						//inform user
						inform('Suppression effectuée', 'success');

						//modal close
						$('#confirmHide').click();

						//refresh list
						getList(2);
					} else {
						//inform user
						inform('Erreur durant la suppression', 'error');
						//form errors display
						formErrors(data);
					}
				});
			}
		});

		$('#confirmCancel').click(function(e){
			e.preventDefault();

			$('#confirmHide').click();
		});

	//sort init
		$('.tab').each(function(){
			if( $('.sortTypeField', this).length ){
				var activeSort = $('.sortTypeField', this).val();
				if( activeSort == '' ){
					activeSort = $('.sortTypeField', this).val(0).val();
				}
				if( activeSort % 2 == 0 ){
					$('.listSort a[href=' + activeSort + ']', this).removeClass('both desc').addClass('asc');
				} else {
					$('.listSort a[href=' + activeSort + ']', this).removeClass('both asc').addClass('desc');
				}
			}
		});

	//modals toggle
		$('#editHide').click(function(e){
			console.log('editHide click');

			if( $('.form', '#editBox').length ){
				var rel = $('.form', '#editBox').attr('rel');

				if( $('.coverStatus', '#editBox').length ){
					//event listeners cleaning
					$('html, #drop_overlay').unbind('dragenter').unbind('dragover').unbind('dragleave').unbind('dragend');

					$('html').get(0).removeEventListener("drop", dropCover, true);
				}

				//replace the form
				$('.close', '#editBox').remove();
				$('#manage_' + rel).appendTo( $('body') );
			}
		});

		$('#editShow').click(function(e){
			console.log('editShow click');

			var rel = $(this).attr('rel');
			var target = rel.charAt(0).toUpperCase() + rel.substr(1);
			var section = $('#editBox .formWrapper');

			if( $('img', '#editPreview') .length ){
				$('#previewShow').click();
			} else {
				$('#previewHide').click();
			}

			//reset validation visual infos
			$(':input, .coverStatus', section).removeClass('required valid error upload');

			if( $('.coverStatus', section).length ){
				$('html, #drop_overlay').bind('dragenter', dragEnter).bind('dragover', dragOver).bind('dragleave', dragLeave);
				$('html').get(0).addEventListener("drop", dropCover, true);
			}

			$('.block', '#editBox').append( $('<button>', { 'class': 'button icon close', 'title': 'Fermer', 'data-icon': 'X' }).text('Fermer') );

			//$('#detailHide, #storageHide').click();

			if( $('img', '#editPreview').length ) $('#previewShow').attr('checked', 'checked');

			$('datalist, select', section).loadList();
		});

		$('#detailHide, #editHide, #confirmHide').click(function(e){
			console.log('#detailHide, #editHide, #confirmHide click');
			if( !$('#detailShow:checked, #editShow:checked, #confirmShow:checked').length ){
				$('html').unbind('keypress'); //remove escape support
			}
		});

		$('.close').live('click', function(e){
			console.log('close click');
			e.preventDefault();

			if( !$(this).is('button') ) return;

			var id = $(this).closest('.box').attr('id');

			if( id == 'editBox' ){
				$('#editHide, #previewHide').click();
			} else if( id == 'detailBox' ){
				$('#detailHide, #storageHide').click();
			} else if( id == 'confirmBox' ){
				$('#confirmHide').click();
			} else {
				$('#detailHide, #editHide, #previewHide, #storageHide, #confirmHide').click();
			}
		});

	//menu link
		$('#nav a').click(function(e){
			console.log('menu link click');
			//refresh current tab if already active (url#hash will not change)
			var target = $(this).attr('href').substr(1);
			if( target == $('#nav').data('activeTab') ){
				e.preventDefault();
				hideInform();
				getList(0);
			}
			//else window.addEventListener('hashchange', tabSwitch, false);
		});

	//internet links for title in form
		$('#movieTitle').change(function(){
			$(this).siblings('label')
				.html(function(){ return $(this).text() }) //clean the label of any html tag
				.append(
					$('<a>', {
						'class': 'button icon externalLink small',
						'title': 'Rechercher sur Google Image',
						'href': 'http://www.google.com/images?q=' + $(this).val() + ' movie',
						'target': '_blank',
						'style': 'margin-left: 5px',
						'data-icon': '/'
					})
				)
				.append(
					$('<a>', {
						'class': 'button icon externalLink small',
						'title': 'Rechercher sur IMDB',
						'href': 'http://www.imdb.com/find?s=all&q=' + $(this).val() ,
						'target': '_blank',
						'style': 'margin-left: 5px',
						'data-icon': '/'
					})
				);
		});
		$('#bookTitle').change(function(){
			$(this).siblings('label')
				.html(function(){ return $(this).text() }) //clean the label of any html tag
				.append(
					$('<a>', {
						'class': 'button icon externalLink small',
						'title': 'Rechercher sur Google Image',
						'href': 'http://www.google.com/images?q=' + $(this).val() + ' book',
						'target': '_blank',
						'style': 'margin-left: 5px',
						'data-icon': '/'
					})
				)
				.append(
					$('<a>', {
						'class': 'button icon externalLink small',
						'title': 'Rechercher sur Fantastic Fiction',
						'href': 'http://www.fantasticfiction.co.uk/search/?searchfor=book&keywords=' + $(this).val() ,
						'target': '_blank',
						'style': 'margin-left: 5px',
						'data-icon': '/'
					})
				);
		});
		$('#bandName').change(function(){
			$(this).siblings('label')
				.html(function(){ return $(this).text() }) //clean the label of any html tag
				.append(
					$('<a>', {
						'class': 'button icon externalLink small',
						'title': 'Rechercher sur Google',
						'href': 'http://www.google.com/search?q=' + $(this).val() + ' music band',
						'target': '_blank',
						'style': 'margin-left: 5px',
						'data-icon': '/'
					})
				)
				.append(
					$('<a>', {
						'class': 'button icon externalLink small',
						'title': 'Rechercher sur Wikipedia',
						'href': 'http://en.wikipedia.org/w/index.php?search=' + $(this).val() + ' music band',
						'target': '_blank',
						'style': 'margin-left: 5px',
						'data-icon': '/'
					})
				)
				.append(
					$('<a>', {
						'class': 'button icon externalLink small',
						'title': 'Rechercher sur Google Image',
						'href': 'http://www.google.com/images?q=' + $(this).val() + ' music band',
						'target': '_blank',
						'style': 'margin-left: 5px',
						'data-icon': '/'
					})
				);
		});

	//saga title in form
		$('#bookSagaTitle, #movieSagaTitle').change(function(){
			console.log('sagaTitle change');
			var field = $(this);
			var form = field.closest('.form');
			var rel = form.attr('rel');
			var target = rel.charAt(0).toUpperCase() + rel.substr(1);
			var dl = field.siblings('datalist');
			var decoder = $('<textarea>');
			if( field.val() != '' && $('option[value="'+field.val()+'"]', dl).length ){
				//var rel = $(this).closest('.form').attr('rel');
				$.post('ajax/manageSaga.php', 'action=getByTitleFor'+target+'&title='+field.val(), function(saga){
					if( !jQuery.isEmptyObject(saga) ){
						if( saga.sagaSearchURL != '' && saga.sagaSearchURL != null ){
							field.siblings('label')
								.html(function(){ return $(this).text() }) //clean the label of any html tag
								.append( $('<a>', { 'class': 'button icon externalLink small', 'title': 'Détail de cette saga sur internet', 'href': decoder.html(saga.sagaSearchURL).val(), 'target': '_blank', 'style': 'margin-left: 5px', 'data-icon': '/' }) );
						}

						//setting fields only if in add mode
						if( $('#' + rel + 'Action').val() == 'add' ){
							$('#'+rel+'SagaPosition').val(saga.position).trigger('blur');

							$('#'+rel+'Storage').val(saga.storageID).trigger('blur');

							if( rel == 'book' ){
								var indice = 1;
								$('.anotherInfo:gt(0)', '#bookAuthors').remove();
								$.each( saga.authors, function(i, a){
									if( indice > 1 ) $('#another_author').click();
									$('#bookAuthor_'+indice).val(decoder.html(a).val()).trigger('blur');
									indice++;
								});
							}

							if( rel == 'movie' ){
								var indice = 1;
								$('.anotherInfo:gt(0)', '#movieArtists').remove();
								$.each( saga.artists, function(i, a){
									if( indice > 1 ) $('#another_artist').click();
									$('#movieArtist_'+indice).val(decoder.html(a).val()).trigger('blur');
									indice++;
								});
							}
						}

						$('#'+rel+'SagaPosition').focus().select();
					}
				});
			} else {
				field.siblings('label').html(function(){ return $(this).text() }); //clean the label of any html tag
			}
		});

	//help
		$('.help').click(function(e){
			e.preventDefault();

			$('#help').toggleClass('deploy');
		});

	//add form shortcut and escape support
		var isAlt = false;
		$(document).keyup(function(e){
			if( e.which == 18 ) isAlt = false;
		}).keydown(function(e){
			if( e.which == 18 ){
				isAlt = true;
				window.setTimeout(function(){ isAlt = false;}, 500); //cancel Alt after 500ms
			}
			if( e.which == 9 ) isAlt = false; //alt+tab does not fire keyup...
			else if( e.which == 65 && isAlt ){
				e.preventDefault();
				e.stopPropagation();
				isAlt = false;
				$('.add', '#'+$('#nav').data('activeTab')).click();
			} else if( e.which == 70 && isAlt ){
				e.preventDefault();
				e.stopPropagation();
				isAlt = false;
				$('.filterFormSwitch', '#'+$('#nav').data('activeTab')).click();
			} else if( e.which == 86 && isAlt ){
				e.preventDefault();
				e.stopPropagation();
				isAlt = false;
				$('.listDisplaySwitch', '#'+$('#nav').data('activeTab')).find('.disabled').siblings().first().click();
			}
		});

		addEscapeSupport();

	//button blur for link (:active ok but no :focus...)
		$('a.button').live('click', function(e){
			$(this).blur();
		});

	//list display switch
		$('a', '.listDisplaySwitch').click(function(e){
			console.log('listDisplaySwitch click');
			e.preventDefault();

			var listDisplay = $(this).closest('.listDisplaySwitch').find('a').map(function(){ return $(this).attr('rel'); }).get().join(' ');
			console.log(listDisplay);

			var wrapper = $(this).closest('.list');

			var switchTo = $(this).attr('rel');

			if( !wrapper.hasClass( switchTo ) ){
				$(this).addClass('disabled').siblings().removeClass('disabled');
				wrapper.removeClass( listDisplay + ' animDone' ).addClass(switchTo).delay(1000).addClass('animDone');
			}
		});

	//band last check date
		$('.externalLink', '#list_band').live('click', function(e){
			console.log('list_band externalLink click');
			//update the date on band web site link click
			$.post('ajax/manageBand.php', {action: 'updateLastCheckDate', id: $(this).attr('rel')});

			//date sort active
			if( $('#bandSortType').val() >= 2 ){
				var $li = $(this).closest('li');
				var $ul = $li.parent();

				if( $('#bandSortType').val() == 2 ){
					//asc sort, oldest first, so we paste the li at the end
					$li.appendTo($ul);
				} else {
					//desc sort, newest first, so we paste the li at the start
					$li.prependTo($ul);
				}
			}
		});
});

/**
 * change the current tab
 */
function tabSwitch(){
	console.log('tabSwitch');

	var target = window.location.hash.substr(1) || $('.tab:first').attr('id');

	// We have to remove it to prevent multiple calls to goto messing up
	// our current item (and there's no point either, so we save on performance)
	//window.removeEventListener('hashchange', tabSwitch, false);

	$('#nav a').removeClass('active');
	$('a[href$=' + target +']', '#nav').addClass('active');

	if( $('#'+target).length ){ // Argument is a valid tab name
		window.location.hash = '#' + target; //security if hash empty
		$('#nav').data('activeTab', target);
		hideInform();
		$('#nav').data('updating', 0); //remove multiple call protection since it's a new tab
		getList(0);
	}

	// If you attach the listener immediately again then it will catch the event
	// We have to do it asynchronously
	//setTimeout(function(){ window.addEventListener('hashchange', tabSwitch, false); }, 1000);
}

/**
 * ajax load <datalist> and <select> content
 */
$.fn.loadList = function(){
	console.log('loadList');
	return this.each(function(){
		console.log('loadList inner');
		var list = $(this);

		var forceUpdate = 0;
		if( list.children().length <= 1 ) forceUpdate = 1;

		//ask the list values to the server and create the <option>s with it
		var decoder = $('<textarea>');
		$.get( 'ajax/loadList.php?field=' + list.attr('id') + '&forceUpdate=' + forceUpdate, function(data, textStatus, jqXHR){
			//server will send a 304 status if the list has not changed and forceUpdate != 1
			if( jqXHR.status == 200 ){
				if( list.is('datalist') ) list.empty();
				else {
					var filter = false;
					if( list.attr('id').search(/Filter/) != -1 && list.val() != '' ){
						list.data('sav', list.val());
						filter = true;
					}
					list.find('option:gt(0)').remove(); //keep the first option aka "placeholder"
				}

				$.each(data, function(i, obj){
					obj.value = decoder.html(obj.value).val();
					$('<option>', { "value": ( obj.id ? obj.id : obj.value ), text: obj.value }).appendTo( list );
				});

				if( filter ) list.val(list.data('sav'));

				if( list.data('selectedId') ){
					list.val( list.data('selectedId') );
					list.removeData('selectedId');
				}
			}
		});
	});
}

/**
 * add escape key support when a modal popup is visible
 */
function addEscapeSupport(){
	$('html').unbind('keypress').keypress(function(e){
		// ESCAPE key pressed
		if( e.keyCode == 27 ){
			console.log('escape pressed');

			//inform visible ?
			if( $('#inform span:visible').length ){
				hideInform();

			//help visible ?
			} else if( $('#help').hasClass('deploy') ){
				console.log('help');
				$('.help').click();

			//drag and drop active ?
			} else if( $('#drop_overlay:visible').length ){
				console.log('drop_overlay');
				$('#drop_overlay').hide()

			//storage image visible ?
			} else if( $('#storageShow:checked').length ){
				console.log('storageHide');
				$('#storageHide').click();

			//storage image visible ?
			} else if( $('#previewShow:checked').length ){
				console.log('previewHide');
				$('#previewHide').click();

			//edit modal visible ?
			} else if( $('#editShow:checked').length ){
				console.log('editHide');
				$('#editHide').click();

			//confirm modal visible ?
			} else if( $('#confirmShow:checked').length ){
				console.log('confirmHide');
				$('#confirmHide').click();

			//detail modal visible ?
			} else if( $('#detailShow:checked').length ){
				console.log('detailHide');
				$('#detailHide').click();
			}
		}
	});
}

/**
 * display the form errors
 * use ".class + .validation-icon" css rules
 * use ".class ~ .tip" css rules
 * @param array [[field id, message, error type]]
 */
function formErrors( data ) {
	console.log('formErrors');
	$.each(data, function(index, error){
		if( error[0] != 'global' ){
			$('#' + error[0]).addClass(error[2]).siblings('.tip').remove(); //remove previous error message if present

			$('#' + error[0]).parent().append( $('<span>', { 'class': 'tip', 'text': error[1] }) ); //add error message
		}
	});
}

/**
 * Display the user information message on the main page and in the modal dialog if it is visible
 * @param string msg
 * @param string cssClass
 */
function inform( msg, cssClass ){
	console.log('inform');
	$('#inform span').addClass( cssClass ).text( msg ).parent().show();
}

/**
 * Empty and hide the user information message
 */
function hideInform(){
	console.log('hideInform');
	$('#inform span').attr('class', '').empty().parent().hide();
}

var dropTimeout = 0;
/**
 * manage the drag enter event for drag and drop
 * @param object event
 */
function dragEnter(event){
	console.log('dragEnter');
	event.preventDefault();
	$('#drop_overlay').show();
	if( event.dataTransfer ){
		event.dataTransfer.effectAllowed = "copy";
		event.dataTransfer.dropEffect = "copy";
	} else if( event.originalEvent.dataTransfer ){
		event.originalEvent.dataTransfer.effectAllowed = "copy";
		event.originalEvent.dataTransfer.dropEffect = "copy";
	}

	dropTimeout = window.setTimeout("$('#drop_overlay').hide();", 3000);
}

/**
 * manage the drag over event for drag and drop
 * @param object event
 */
function dragOver(event){
	console.log('dragOver');
	event.preventDefault();
	if( $('#drop_overlay:hidden').length ) $('#drop_overlay').show();

	clearTimeout(dropTimeout);
	dropTimeout = window.setTimeout("$('#drop_overlay').hide();", 3000);
}

/**
 * manage the drag leave event for drag and drop
 * @param object event
 */
function dragLeave(event){
	console.log('dragLeave');
	event.stopPropagation();
}

/**
 * manage the drop event for the cover
 * validate the dropped file or url
 * if image, upload it
 * if url, send it for curling
 * @param object event
 */
function dropCover(event){
	console.log('dropCover');
	event.preventDefault();

	$('#drop_overlay').hide();

	var section = $('#editBox').find('.form'); //#manage_xxx
	var rel = section.attr('rel');
	var coverStatus = $('.coverStatus', section).removeClass('required valid error upload'); //reset validation visual infos
	coverStatus.data('oldText', coverStatus.html());

	var dt = event.dataTransfer;
	var files = dt.files;

	// manage remote image
	if( files.length == 0 && dt.types.contains("application/x-moz-file-promise-url") ){
		url = dt.getData("application/x-moz-file-promise-url");

		coverStatus.addClass('upload');
		$.ajax({
			url: 'ajax/manageCover.php?rel='+rel,
			data: 'url='+dt.getData("application/x-moz-file-promise-url"),
			complete: function(e){
				coverStatus.removeClass('upload');
			},
			success: function(result){
				var timestamp = new Date().getTime();
				$('#editPreview').empty().append( $('<img>', { src: ( rel == 'storage' ? 'storage' : 'covers' ) + '/' + ( rel == 'storage' ? 'tmp_storage.png' : result )  + '?' + timestamp }) );
				$('#' + rel + 'Cover').val(result);
				coverStatus.html(coverStatus.data('oldText')).addClass('valid');
				if( !$('#previewShow:checked').length ) $('#previewShow').click();
			},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				coverStatus.html(errorThrown).addClass('error');
			}
		});
		return;
	}

	//only one cover for each cover
	if( files.length > 1 ){
		formErrors([[rel+'CoverStatus', 'Une seule image est permise, seule la première est prise en compte.', 'warning']]);
	}

	//if it's not a remote image
	var file = files[0];
	if(file.type.match(/image.(jpe?g|png|gif)/)) {
		upload(file, rel, coverStatus);
	} else {
		formErrors([[rel+'CoverStatus', 'Seule les images .jpg, .jpeg, .png or .gif sont permises.', 'error']]);
	}
}

/*
 * Upload files to the server using HTML5 File API and sendAsBinary method
 * @param object file
 */
function upload(file, rel, coverStatus){
	console.log('upload');
	if( window.FileReader ){
		hideInform();

		var reader = new FileReader();
		if( typeof(reader.addEventListener) === "function" ){
			reader.addEventListener('loadend', function(){
				var xhr = new XMLHttpRequest();
				xhr.open("POST", 'ajax/manageCover.php?up=true&rel='+rel, true);
				xhr.setRequestHeader('UP-NAME', 'upload');
				xhr.setRequestHeader('UP-FILENAME', file.name);
				xhr.setRequestHeader('UP-SIZE', file.size);
				xhr.setRequestHeader('UP-TYPE', file.type);
				xhr.send(window.btoa(reader.result));
				coverStatus.addClass('upload');


				xhr.onreadystatechange = function(){
					if( xhr.readyState == 4 ){
						coverStatus.removeClass('upload');
						if( xhr.status == 200 ){
							coverStatus.html( coverStatus.data('oldText') ).addClass('valid');
							var timestamp = new Date().getTime();
							$('#editPreview').empty().append( $('<img>', { src: ( rel == 'storage' ? 'storage' : 'covers' ) + '/' + ( rel == 'storage' ? 'tmp_storage.png' : file.name ) + '?' + timestamp }) );
							if( !$('#previewShow:checked').length ) $('#previewShow').click();
							$('#' + rel + 'Cover').val(file.name);
						} else {
							coverStatus.html(xhr.responseText).addClass('error');
						}
					}
				};
			}, true);

			reader.addEventListener('error', function(event){
				switch(event.target.error.code){
					case event.target.error.NOT_FOUND_ERR:
						coverStatus.html('Fichier non trouvé!').removeClass('upload').addClass('error');
					break;
					case event.target.error.NOT_READABLE_ERR:
						coverStatus.html('Fichier non lisible!').removeClass('upload').addClass('error');
					break;
					case event.target.error.ABORT_ERR:
					break;
					default:
						coverStatus.html('Erreur de lecture.').removeClass('upload').addClass('error');
				}
			}, true);

			reader.addEventListener('progress', function(event){
				if (event.lengthComputable) {
					coverStatus.html('Chargement : '+ Math.round((event.loaded * 100) / event.total) +'%');
				}
			}, true);

			reader.addEventListener('loadProgress', function(event){
				if (event.lengthComputable) {
					coverStatus.html('Chargement : '+ Math.round((event.loaded * 100) / event.total) +'%');
				}
			}, true);

		} else {
			//webkit
			reader.onload = function(){
				var xhr = new XMLHttpRequest();
				xhr.open("POST", 'ajax/manageCover.php?up=true&rel='+rel, true);
				xhr.setRequestHeader('UP-NAME', 'upload');
				xhr.setRequestHeader('UP-FILENAME', file.name);
				xhr.setRequestHeader('UP-SIZE', file.size);
				xhr.setRequestHeader('UP-TYPE', file.type);
				xhr.send(window.btoa(reader.result));
				coverStatus.addClass('upload');


				xhr.onload = function(){
					if( xhr.readyState == 4 ){
						coverStatus.removeClass('upload');
						if( xhr.status == 200 ){
							coverStatus.html( coverStatus.data('oldText') ).addClass('valid');
							var timestamp = new Date().getTime();
							$('#editPreview').empty().append( $('<img>', { src: ( rel == 'storage' ? 'storage' : 'covers' ) + '/' + file.name + '?' + timestamp }) );
							if( !$('#previewShow:checked').length ){
								$('#previewShow').click();
							}
							$('#' + rel + 'Cover').val(file.name);
						} else {
							coverStatus.html(xhr.responseText).addClass('error');
						}
					}
				};
			};
		}

		// The function that starts reading the file as a binary string
		reader.readAsBinaryString(file);
	} else {
		coverStatus.removeClass('upload').addClass('error')
		inform('upload non supporté', 'error');
	}
}

/**
 * dynamic form fields validation using HTML5 form validation API
 * called through javascript events listeners
 * set classes for css form validation rules
 * ".class + .validation-icon"
 * @param object event
 */
function checkField(event){
	console.log('checkField');
	var el = $(event.target);

	if( el[0].validity ){
		if( el[0].validity.valid ){
			if( el.val() != '' ) el.removeClass('required error upload').addClass('valid');
		} else if( event.type != "input" ){
			if( el[0].validity.valueMissing ){ // User hasn't typed anything
				el.removeClass('error valid upload').addClass('required');
			} else {
				el.removeClass('required valid upload').addClass('error');
			}
		} else if( el[0].validity.valueMissing ){
			el.removeClass('required valid error upload');
		}
	//for browsers with no forum validation API support
	} else {
		if( el.val() != '' ) el.removeClass('required error upload').addClass('valid');
		else el.removeClass('error valid upload').addClass('required');
	}
}

/**
 * get the list
 * @param integer type : 0 no filter, 1 filter from form, 2 use filter if present in session, 3 endless scroll pagination
 */
function getList( type ){
	console.log('getList '+type);

	var tab = $('#nav').data('activeTab');

	var t = tab.charAt(0).toUpperCase() + tab.substr(1);
	var list = '#list_'+tab;
	var filter = '#'+tab+'_filter';

	//multiple call protection
	if( $('#nav').data('updating') != 1 ){
		$('#nav').data('updating', 1);

		$('body').css('cursor', 'progress');

		$.ajax({
			url: 'ajax/manage'+t+'.php',
			data: 'action=' + ( type == 3 ? 'more' : 'list&type=' + type + '&' + $(':input', filter).serialize() ),
			type: 'POST',
			dataType: 'html',
			complete: function(){
				$('#nav').data('updating', 0);
				$('body').css('cursor', '');
			},
			success: function(data){
				//append updated list
				$(list).append( data );

				if( type != 3 ){
					//remove old list
					$('.listContent:not(.new)', list).remove();
					$('.listDisplaySwitch', list).hide();

					if( $('.paginate', list).length == 2 ){
						$('.paginate:first', list).remove();
					}

					//show new list
					$('.listContent.new', list).removeClass('new').show();

					$('.paginate', list).show();
					$('.listDisplaySwitch', list).show();

					//pagination on scroll
					if( $('.paginate', list).hasClass('begin') ){
						$(window).unbind('scroll').scroll(function(e){
							if( $('.listContent li:last', list).length
								&& !$('#detailShow:checked, #editShow:checked').length
								&& ($(this).scrollTop() + $(this).height() + 50) >= $('.listContent li:last', list).offset().top
							){
								getList(3);
							}
						});
					} else {
						$(window).unbind('scroll');
					}

					//hide detail
					if( type == 2 && $('#detailShow:checked').length ){
						$('#detailHide').click();
						var b = $('#detailBox');
						//display "refreshed" detail
						if( b.data('link') && b.data('tab') == tab ){
							var i = b.data('link');
							var timestamp = new Date().getTime();

							if( tab != 'storage' ){
								var di = $('.detail[href='+i+']', list);
							} else {
								var di = $('.storage[href='+i+']', list);
							}

							//force cover refresh by modifying the source url
							//@todo test with cache modifications
							//di.closest('li').css({ 'background-image' : 'ulr(' + this.src + '&' + timestamp + ')' })
							//	.find('.cover').attr('src', function(){ return this.src + '&' + timestamp; });

							di.click();
						}
					} else if( type == 0 ){
						$(window).scrollTop(0);
					}

					//if filters are visible, update them
					$('datalist, select', filter + ' .listFilter.deploy').loadList();
				} else {
					$('.listContent:not(.new)', list).append( $('.listContent.new li', list) );

					if( $('.paginate', list).hasClass('end') ){
						$(window).unbind('scroll');
					}

					$('.paginate:first', list).replaceWith( $('.paginate:last', list).show() );
					$('.listContent.new', list).remove();
				}
			}
		});
	}
}
