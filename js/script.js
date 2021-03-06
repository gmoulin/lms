/**
 * Author: Guillaume Moulin <gmoulin.dev@gmail.com>
 */
var delayAjax = false,
	delayTimeout;

//cache the site via manifest if possible
if( Modernizr.applicationcache ){
	var debugCacheManifest = false;

	if( debugCacheManifest ){
		//force reload of the page if an update is available and log all the process
		var cacheStatusValues = [];
		cacheStatusValues[0] = 'uncached';
		cacheStatusValues[1] = 'idle';
		cacheStatusValues[2] = 'checking';
		cacheStatusValues[3] = 'downloading';
		cacheStatusValues[4] = 'updateready';
		cacheStatusValues[5] = 'obsolete';

		function logEvent(e) {
			var online, status, type, message;
			online = (navigator.onLine) ? 'yes' : 'no';
			status = cacheStatusValues[cache.status];
			type = e.type;
			message = 'online: ' + online;
			message+= ', event: ' + type;
			message+= ', status: ' + status;
			if (type == 'error' && navigator.onLine) {
				message+= ' (prolly a syntax error in manifest)';
			}
			console.log(message);
		}

		var cache = window.applicationCache;
		cache.addEventListener('cached', logEvent, false);
		cache.addEventListener('checking', logEvent, false);
		cache.addEventListener('downloading', logEvent, false);
		cache.addEventListener('error', logEvent, false);
		cache.addEventListener('noupdate', logEvent, false);
		cache.addEventListener('obsolete', logEvent, false);
		cache.addEventListener('progress', logEvent, false);
		cache.addEventListener('updateready', logEvent, false);

		setInterval(function(){cache.update()}, 10000);
	}

	//just force reload of the page if an update is available
	window.applicationCache.addEventListener(
		'updateready',
		function(){
			//busy visual information
			$('header').removeClass('loading');
			if( confirm('Une nouvelle version est disponible, voulez-vous recharger la page ?') ){
				window.applicationCache.swapCache();
				window.location.reload();
			} else {
				delayAjax = false;
			}
		},
		false
	);

	window.applicationCache.addEventListener(
		'checking',
		function(){
			//delay ajax calls if there is a new manifest version
			delayAjax = true;
		},
		false
	);
	window.applicationCache.addEventListener(
		'downloading',
		function(){
			$('header').addClass('loading');
		},
		false
	);
	window.applicationCache.addEventListener(
		'noupdate',
		function(){
			delayAjax = false;
			$('header').removeClass('loading');
		},
		false
	);
	window.applicationCache.addEventListener(
		'error',
		function(){
			//delay ajax calls if there is a new manifest version
			delayAjax = false;
			$('header').removeClass('loading');
			alert('Error while downloading the new version');
		},
		false
	);

	//sometimes a DOM exception is raised by update()...
	try {
		if( !$.browser.opera ) window.applicationCache.update();
	} catch(err){
		if( $.browser.opera ) window.location.reload();
	}
}

//opera mini does not support localStorage...
if( !Modernizr.localstorage ){
	(function(){
		var Storage = function(type){
			function createCookie(name, value, days){
				var date, expires;

				if( days ){
					date = new Date();
					date.setTime(date.getTime()+(days*24*60*60*1000));
					expires = "; expires="+date.toGMTString();
				} else {
					expires = "";
				}
				document.cookie = name+"="+value+expires+"; path=/";
			}

			function readCookie(name){
				var nameEQ = name + "=",
					ca = document.cookie.split(';'),
					i, c;

				for( i=0; i < ca.length; i++ ){
					c = ca[i];
					while( c.charAt(0)==' ' ){
						c = c.substring(1,c.length);
					}

					if( c.indexOf(nameEQ) == 0 ){
						return c.substring(nameEQ.length,c.length);
					}
				}
				return null;
			}

			function setData(data){
				data = JSON.stringify(data);
				if( type == 'session' ){
					window.name = data;
				} else {
					createCookie('localStorage', data, 365);
				}
			}

			function clearData(){
				if( type == 'session' ){
					window.name = '';
				} else {
					createCookie('localStorage', '', 365);
				}
			}

			function getData(){
				var data = type == 'session' ? window.name : readCookie('localStorage');
				return data ? JSON.parse(data) : {};
			}

			// initialise if there's already data
			var data = getData();

			return {
				length: 0,
				clear: function(){
					data = {};
					this.length = 0;
					clearData();
				},
				getItem: function(key){
					return data[key] === undefined ? null : data[key];
				},
				getObject: function(key){
					return data[key] === undefined ? null : JSON.parse( data[key] );
				},
				key: function(i){
					// not perfect, but works
					var ctr = 0;
					for (var k in data) {
						if (ctr == i) return k;
						else ctr++;
					}
					return null;
				},
				removeItem: function(key){
					delete data[key];
					this.length--;
					setData(data);
				},
				setItem: function(key, value){
					data[key] = value+''; // forces the value to a string
					this.length++;
					setData(data);
				},
				setObject: function(key, value){
					data[key] = JSON.stringify(value)+''; // forces the value to a string
					this.length++;
					setData(data);
				}
			};
		};

		if( typeof window.localStorage == 'undefined' ) window.localStorage = new Storage('local');
		if( typeof window.sessionStorage == 'undefined' ) window.sessionStorage = new Storage('session');

	})();
}

var subDomains = ['s1', 's2', 's3'],
	useSubDomains = false;

$(document).ready(function(){
	//improve image loading using subdomains
		if( window.location.host == 'lms.dev' || window.location.host == 'lms' ){
			useSubDomains = true;
			for( var i = 0; i < subDomains.length; i++ ){
				subDomains[ i ] = window.location.protocol + '//' + subDomains[ i ] + '.' + window.location.host + '/';
			}
		} else if( window.location.host == 'lms.kapok.fr' ){
			useSubDomains = true;
			for( var i = 0; i < subDomains.length; i++ ){
				subDomains[ i ] = window.location.protocol + '//' + subDomains[ i ] + '.kapok.fr/';
			}
		}

	//for .add_another positonning bug in firefox
		if( $.browser.mozilla ) $('html').addClass('mozilla');
		else if( $.browser.webkit ) $('html').addClass('webkit');

		if( navigator.platform.search(/Win/) != -1 ) $('html').addClass('windows');
		else if( navigator.platform.search(/Linux/) != -1 ) $('html').addClass('linux');

	//ajax global management
		$('#ajax_loader').ajaxStart(function(){
			$('#ajax_loader').addClass('loading');
		})
		.ajaxStop(function(){
			$('#ajax_loader').removeClass('loading');
		})
		.ajaxError(function(event, xhr, settings, exception){
			if( xhr.responseText != '' ) inform("Error requesting page " + settings.url + ", error : " + xhr.responseText, 'error');
		});

	//tab change via menu and url#hash
		window.addEventListener("hashchange", tabSwitch, false);

	//reset the modals
	//sometimes the "show" checkbox is checked on page load
		$('#editHide, #detailHide, #storageHide, #previewHide, #confirmHide').click();

	//input type number crossbrowser support
	//@todo TODO: remove when firefox fully support input type number
		if( !Modernizr.inputtypes.number ){
			$('.spinbox:not(.spinbox-active)').spinbox({
				min: 1,		// Set lower limit or null for no limit.
				max: 999,	// Set upper limit or null for no limit.
				step: 1,	// Set increment size.
				reset: 0,	// reset value
			});
		}

	//author, band and artist inputs in forms
		$('#editBox')
			.delegate('.add_another', 'click', function(event){
				event.preventDefault();

				if( !$(this).is('button') ) return;

				var $list = $(this).siblings('ol'),
					$anotherBlock = $('.anotherInfo:last', $list).clone(true),
					tmp = $('input', $anotherBlock).attr('id').split('_'),
					indice = parseInt(tmp[1]);

				$anotherBlock.children('input')
					.attr('id', function(index, attr){ return attr.replace(new RegExp(indice), indice + 1); })
					.attr('name', function(index, attr){ return attr.replace(new RegExp(indice), indice + 1); })
					.val('') //reseting the value
					.siblings('label').attr('for', $('input', $anotherBlock).attr('id'));


				$list.append( $anotherBlock );
			})
			.delegate('.delete_another', 'click', function(event){
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
			.each(function(){ //sort init if present
				var $this = $(this),
					$sortTypeField = $this.children('.sortTypeField');
				if( $sortTypeField.length ){
					var activeSort = $sortTypeField.val();
					//set if empty
					if( activeSort == '' ){
						activeSort = $sortTypeField.val(0).val();
					}
					//each activeSort value is linked to a "ORDER BY" sentence in the php database tables classes
					//each sort button has 2 arrows
					//class "both" -> no arrow highlighted
					//class "asc" -> top arrow highlighted
					//class "desc" -> bottom arrow highlighted
					if( activeSort % 2 == 0 ){ //even -> asc sort
						$this.find('.listSort a[href=' + activeSort + ']').removeClass('both desc').addClass('asc');
					} else { //odd -> desc sort
						$this.find('.listSort a[href=' + activeSort + ']').removeClass('both asc').addClass('desc');
					}
				}
			})
			.delegate('.filterFormSwitch', 'click', function(e){
				e.preventDefault();

				if( !$(this).is('a') ) return;

				var $ul = $(this).closest('.listFilter').toggleClass('deploy');

				if( $ul.hasClass('deploy') ){ $ul.find('datalist, select').loadList(); }
			})
			.delegate('.search', 'click', function(e){
				e.preventDefault();
				getList(1);
			})
			.delegate('.cancel', 'click', function(e){
				e.preventDefault();
				$(this).closest('.filterForm').find(':input').val(''); //filter form reset
				getList(0);
			})
			.delegate('.sort', 'click', function(e){
				e.preventDefault();
				var $this = $(this);

				if( !$this.is('a') ) return;

				//save clicked link current icon
				var c = $this.attr('class'),
					$form = $this.closest('.filterForm'),
					h = $this.attr('href');

				//reset all links icons
				$('.sort', $form).removeClass('asc').removeClass('desc').addClass('both');

				//set back clicked link icon
				$this.attr('class', c);

				//default state -> asc
				if( $this.hasClass('both') ){
					$this.toggleClass('both asc');

				//asc state -> desc
				} else if( $this.hasClass('asc') ){
					$this.toggleClass('asc desc');
					h++;

				//desc state -> default
				} else if( $this.hasClass('desc') ){
					$this.toggleClass('desc both');

					//reseting sort type to 0
					$('.sort:first', $form).click();
					return;
				}

				$form.children('.sortTypeField').val( h );

				getList(1);
			});

	//add, update, delete, relocate, move, addLoan
		$('.add').click(function(e){
			e.preventDefault();

			if( !$(this).is('button') ) return;

			var rel = $(this).attr('rel'),
				$manage = $('#manage_' + rel),
				idLink = '',
				nameLink = '',
				text = 'Enregistrer ';

			//raz
			if( rel == 'book' ){
				another = 'bookAuthors';
				idLink = 'bookAuthor';
				nameLink = 'author';
			} else if( rel == 'album' ){
				another = 'albumBands';
				idLink = 'albumBand';
				nameLink = 'band';
			} else if( rel == 'movie' ){
				another = 'movieArtists';
				idLink = 'movieArtist';
				nameLink = 'artist';
			}

			if( idLink != '' ){
				$('#' + another).children('.anotherInfo:gt(0)').remove();

				$('#' + another).find('input')
					.attr('id', idLink + '_1')
					.attr('name', nameLink + '_1')
					.siblings('label').attr('for', idLink + '_1');
			}

			$manage
				.find(':input').val('')
				.siblings('.quickLink, .tip').remove(); //clean the quick links and tips

			$manage.find('.coverStatus').html(function(){
				var tmp = 'Déposer ';
				rel == 'book' || rel == 'album' ? tmp += 'la couverture' : tmp += 'l\'affiche';
			});

			$('#editPreview').empty();
			//setting action
			$('#' + rel + 'Action').val('add');

			//hidding and emptying results
			hideInform();

			//construct the popup title text
			rel == 'book' || rel == 'movie' || rel == 'band' || rel == 'storage' ? text += ' le nouveau ' :
			rel == 'album' || rel == 'author' || rel == 'artist' ? text += ' le nouvel ' : text += ' la nouvelle ';

			if( rel == 'book' ) text += 'livre';
			else if( rel == 'movie' ) text += 'film';
			else if( rel == 'album' ) text += 'album';
			else if( rel == 'saga' ) text += 'saga';
			else if( rel == 'author' ) text += 'auteur';
			else if( rel == 'artist' ) text += 'artiste';
			else if( rel == 'band' ) text += 'groupe';
			else if( rel == 'storage' ) text = 'rangement';

			//place the form
			$manage.appendTo( $('#editForm .formWrapper').empty() );

			//set the form title
			$('#editForm .formTitle').html( text );

			//set the submit button text
			$('#formSubmit').data('save_clicked', 0).attr('rel', 'add').text('Enregistrer');

			//open the modal dialog
			$('#editShow').attr('rel', rel).click();
		});

		$('.smallBoxes, .bigBoxes, #detailBox, #confirmBox')
			.delegate('.update', 'click', function(e){
				e.preventDefault();

				if( !$(this).is('a') ) return;

				var $this = $(this),
					rel = $this.attr('rel'),
					target = rel.charAt(0).toUpperCase() + rel.substr(1),
					$manage = $('#manage_' + rel),
					link = '',
					text = 'Modifier les informations ',
					$editPreview = $('#editPreview').empty();

				if( rel == 'book' ) link = 'bookAuthors';
				else if( rel == 'movie' ) link = 'movieArtists';
				else if( rel == 'album' ) link = 'albumBands';

				//raz
				if( link != '' ) $('#' + link).children('.anotherInfo:gt(0)').remove();
				$manage.find(':input').val('');
				$manage.find(':checkbox, :radio').prop('checked', false);
				$manage.find('.coverStatus').html(function(){
					return 'Déposer ' + ( rel == 'movie' ? 'l\'affiche' : 'la couverture' );
				});

				//setting action
				$('#' + rel + 'Action').val('update');

				//hidding and emptying results
				hideInform();

				//construct the popup title text
				if( rel == 'book' ) text += 'du livre';
				else if( rel == 'movie' ) text += 'du film';
				else if( rel == 'album' ) text += 'du l\'album';
				else if( rel == 'saga' ) text += 'de la saga';
				else if( rel == 'author' ) text += 'de l\'auteur';
				else if( rel == 'artist' ) text += 'de l\'artiste';
				else if( rel == 'band' ) text += 'du groupe';
				else if( rel == 'storage' ) text = 'du rangement';

				//place the form
				$manage.appendTo( $('#editForm .formWrapper').empty() );

				$('#editForm .formTitle').html( text );
				$('#formSubmit').data('save_clicked', 0).attr('rel', 'update').text('Enregistrer');

				//open the modal dialog
				$('#editShow').attr('rel', rel).click();

				var decoder = $('<textarea>');
				//load the data and set the form fields with it
				$.post('ajax/manage' + target + '.php', 'action=get&id=' + $this.attr('href'), function(data){
					switch( rel ){
						case 'book':
								$('#bookID').val(data.bookID);
								$('#bookTitle').val(decoder.html(data.bookTitle).val()).change();
								$('#bookSize').val(data.bookSize);
								$('#bookCover').val(data.bookCover);
								$('<img>', { src : 'image.php?cover=book&id=' + data.bookID }).appendTo( $editPreview );
								$('#bookSagaTitle').val(decoder.html(data.sagaTitle).val()).change();
								$('#bookSagaPosition').val(data.bookSagaPosition);

								//options for this select are reseted by loadList()
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
								$('#movieTitle').val(decoder.html(data.movieTitle).val()).change();
								$('#movieGenre').val(decoder.html(data.movieGenre).val());
								$('#movieMediaType').val(decoder.html(data.movieMediaType).val());
								$('#movieLength').val(data.movieLength);
								$('#movieCover').val(data.movieCover);
								$('<img>', { src : 'image.php?cover=movie&id=' + data.movieID }).appendTo( $editPreview );
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
								$('#albumTitle').val(decoder.html(data.albumTitle).val()).change();
								$('#albumType').val(data.albumType);
								$('#albumCover').val(data.albumCover);
								$('<img>', { src : 'image.php?cover=album&id=' + data.albumID }).appendTo( $editPreview );

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
								$('#bandName').val(decoder.html(data.bandName).val()).change();
								$('#bandGenre').val(decoder.html(data.bandGenre).val());
								$('#bandWebSite').val(data.bandWebSite);
							break;
						case 'artist':
								$('#artistID').val(data.artistID);
								$('#artistFirstName').val(decoder.html(data.artistFirstName).val());
								$('#artistLastName').val(decoder.html(data.artistLastName).val());
							break;
						case 'saga':
								$('#sagaID').val(data.sagaID);
								$('#sagaTitle').val(decoder.html(data.sagaTitle).val());
								$('#sagaSearchURL').val(data.sagaSearchURL);
								if( data.sagaRating >= 1 ){
									$('#star'+data.sagaRating).prop('checked', true);
								}
							break;
						case 'storage':
								$('#storageID').val(data.storageID);
								$('#storageRoom').val(data.storageRoom);
								$('#storageType').val(data.storageType);
								$('#storageColumn').val(data.storageColumn);
								$('#storageLine').val(data.storageLine);
								$('<img>', { src : 'image.php?cover=storage&id=' + data.storageID }).appendTo( $editPreview );
							break;
					}
				});
			});

		$('.smallBoxes, .bigBoxes, #detailBox')
			.delegate('.delete', 'click', function(e){
				e.preventDefault();

				if( !$(this).is('a') ) return;

				//hidding and emptying results
				hideInform();

				var $this = $(this),
					id = $this.attr('href'),
					rel = $this.attr('rel'),
					target = rel.charAt(0).toUpperCase() + rel.substr(1),
					text = 'Etes-vous sûr de vouloir supprimer ',
					$formWrapper = $('#confirmForm .formWrapper'),
					$confirmSubmit = $('#confirmSubmit').data('save_clicked', 0);

				if( rel == 'book' ) text += 'ce livre ?';
				else if( rel == 'album' ) text += 'cet album ?';
				else if( rel == 'movie' ) text += 'ce film ?';
				else if( rel == 'saga' ) text += 'cette saga ?<br />Tous les livres et films listés ci-dessous seront également supprimés !';
				else if( rel == 'author' ) text += 'cet auteur ?<br />Tous les livres listés ci-dessous seront également supprimés !';
				else if( rel == 'artist' ) text += 'cet artiste ?<br />Tous les films listés ci-dessous seront également supprimés !';
				else if( rel == 'band' ) text += 'ce groupe ?<br />Tous les albums listés ci-dessous seront également supprimés !';
				else if( rel == 'storage' ) text += 'ce rangement ?';
				else if( rel == 'loan' ) text += 'ce prêt ?';

				$formWrapper.html( $('<span>', { 'class': 'confirmation' }).append( text ) );

				//set the submit button text
				$confirmSubmit.data('save_clicked', 0);

				//open the modal dialog
				$('#confirmShow').attr('rel', rel).data('id', id).click();

				if( rel == 'saga' || rel == 'author' || rel == 'artist' || rel == 'band' ){
					//chargement de la liste des livres ou films ou albums impactés
					$.ajax({
						url: 'ajax/manage' + target + '.php',
						type: 'POST',
						data: 'action=impact&id=' + id,
						async: false,
						dataType: 'html',
						success: function(data){
							$formWrapper.append(data);
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
							$formWrapper.append(data);
							$confirmSubmit.prop({ disabled: true });
						}
					});
				}
			});

		$('#editBox').delegate('#relocate', 'click', function(e){
			e.preventDefault();

			if( !$(this).is('button') ) return;

			hideInform();

			if( $('#storageList').val() == '' ){
				formErrors([['storageList', 'Le nouveau rangement est requis.', 'required']]);
			} else {
				var $impactStorage = $('#impactStorage');
				$.post('ajax/manageStorage.php', 'action=relocate&' + $.param( $impactStorage.find('input:checked, select'), true ), function(data){
					if( data == 'ok' ){
						//inform user
						inform('Nouvelle allocation effectuée', 'success');

						//clean the relocated items
						$impactStorage.find(':input:checked').parent().remove();

						//activate the confirm button when all items have been relocated
						if( !$impactStorage.find('input').length ){
							$impactStorage.remove();
							$('#confirmSubmit').removeProp("disabled");
						}
					}
				});
			}
		});

		$('.smallBoxes').delegate('.move', 'click', function(e){
			e.preventDefault();

			if( !$(this).is('a') ) return;

			//hidding and emptying results
			hideInform();

			var $this = $(this),
				id = $this.attr('href'),
				rel = $this.attr('rel'),
				target = rel.charAt(0).toUpperCase() + rel.substr(1),
				text = 'Etes-vous sûr de vouloir changer le rangement de cette saga ?',
				$formWrapper = $('#editForm .formWrapper').empty();

			$('#editForm .formTitle').html('Modification du rangement');

			//set the submit button text
			$('#formSubmit').data('save_clicked', 0);

			$('#editPreview').empty();

			//chargement de la liste des livres ou films ou albums concernés
			$.ajax({
				url: 'ajax/manage' + target + '.php',
				type: 'POST',
				data: 'action=moveImpact&id=' + id,
				async: false,
				dataType: 'html',
				success: function(data){
					$formWrapper.append(data);
				}
			});

			//open the modal dialog
			$('#editShow').attr('rel', 'move').data('id', id).click();
		});

		$('#detailBox').delegate('.addLoan', 'click', function(e){
			e.preventDefault();

			if( !$(this).is('a') ) return;

			var $this = $(this),
				$manage = $('#manage_loan'),
				text = 'Enregistrer le nouveau prêt',
				$editForm = $('#editForm');

			$manage.find(':input').val('');
			$('#editPreview').empty();
			//setting action
			$('#loanAction').val('add');
			$('#loanFor').val( $this.attr('rel') );
			$('#itemID').val( $this.attr('href') );

			//hidding and emptying results
			hideInform();

			//place the form
			$manage
				.appendTo( $editForm.find('.formWrapper').empty() );

			//set the form title
			$editForm.find('.formTitle').html( text );

			//set the submit button text
			$('#formSubmit').data('save_clicked', 0).attr('rel', 'add').text('Enregistrer');

			//open the modal dialog
			$('#editShow').attr('rel', 'loan').click();
		});

	//list actions (storage, detail, filter)
		$('.list')
			.delegate('.storage', 'click', function(e){
				e.preventDefault();
				var $this = $(this);

				//storage list case
				var $detailBox = $('#detailBox'),
					$detail = $('#detail'),
					$closeButton = $('<button>').addClass('button icon close').attr('title', 'Fermer').attr('data-icon', 'X'),
					$block = $this.parent().find('.block').clone(true);

				if( !$block.children('.close').length ){
					$block.append( $closeButton );
				}

				//saving for detail display after list refresh if needed
				$detailBox.data('link', $this.attr('href'))
					.data('tab', $('#nav').data('activeTab'));

				$detail.html( $block );

				$('#detailShow').click();

				//remove src then add it with new value to avoid flicker
				$('#storageImg').removeAttr('src').attr('src', $this.attr('href') );

				$('#storageShow').click();
			})
			.delegate('.detail', 'click', function(e){
				e.preventDefault();
				var $this = $(this);

				var $detailBox = $('#detailBox'),
					$detail = $('#detail'),
					$closeButton = $('<button>').addClass('button icon close').attr('title', 'Fermer').attr('data-icon', 'X'),
					$block = $this.parent().find('.block').clone(true);

				if( !$block.children('.close').length ){
					$block.append( $closeButton );
				}

				//saving for detail display after list refresh if needed
				$detailBox.data('link', $this.attr('href'))
					.data('tab', $('#nav').data('activeTab'));

				$('#storageHide').attr('checked', 'checked');

				$detail.html( $block );

				$('#detailShow').click();
			});

		$('.box').delegate('.storage', 'click', function(e){
			e.preventDefault();
			var $this = $(this);
			//toggle storage image show
			if( $('#storageShow:checked').length ) $('#storageHide').click();
			else {
				$('#storageImg').attr('src', $this.attr('href') );
				$('#storageShow').click();
			}
		});

		$('#list_book, #list_movie, #list_album, #detailBox').delegate('.filter', 'click', function(e){
			e.preventDefault();
			var $this = $(this);

			if( !$this.is('a') ) return;

			var rel = $this.attr('rel');
			if( !rel ) return;

			rel = rel.charAt(0).toUpperCase() + rel.substr(1);

			var tab = $('#nav').data('activeTab');

			$('#' + tab + rel +'Filter').val( $this.attr('href') );

			$('#' + tab + '_filter .listFilter').addClass('deploy');

			getList(1);

			//hide all popup
			$('#detailHide, #editHide, #previewHide, #storageHide, #confirmHide').prop({ checked: true });
		});

	//forms actions
		$('.form').each(function(){
			//add event listener for dynamic form validation
			this.addEventListener("invalid", checkField, true);
			this.addEventListener("blur", checkField, true);
			this.addEventListener("input", checkField, true);
		}).submit(function(e){
			e.preventDefault();
			$('#formSubmit').click();
		});

		//add and update
		$('#formSubmit').click(function(e){
			e.preventDefault();
			var $this = $(this),
				$section = $this.closest('.wrapper').find('.form'); //#manage_xxx

			if( !$section.length ){
				return;
			}

			var rel = $('#editBox .form').attr('rel'),
				target = rel.charAt(0).toUpperCase() + rel.substr(1);

			//multiple call protection
			if( $this.data('save_clicked') != 1 ){
				$this.data('save_clicked', 1);

				//hidding and emptying results
				hideInform();

				if( rel != 'move' ){
					$.ajax({
						url: 'ajax/manage' + target + '.php',
						data: $section.find(':input').serialize(),
						type: 'POST',
						dataType: 'json',
						complete: function(){
							$this.data('save_clicked', 0);
						},
						success: function(data){
							if( data == 'ok' ){
								var $confirmForm = $('#confirmForm');

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
												$confirmForm.find('.impact').remove();
												$confirmForm.find('.formWrapper').append(data);
											}
										});
									} else if( rel == 'storage' ){
										//chargement de la liste des rangements impactés
										$.ajax({
											url: 'ajax/manage' + target + '.php',
											type: 'POST',
											data: 'action=impact&id=' + id,
											async: false,
											dataType: 'html',
											success: function(data){
												$('#impactStorage').remove();
												$confirmForm.find('.formWrapper').append(data);

												$('#confirmSubmit').prop({ disabled: true });
											}
										});
									}

								} else {
									//refresh list
									getList(2);
								}

								//inform user
								inform( ( $('#' + rel + 'Action').val() == 'add' ? 'Ajout effectué' : 'Mise à jour effectuée' ), 'success' );

							} else {
								//inform user
								inform( 'Erreur durant la validation du formulaire', 'error' );

								//form errors display
								formErrors(data);
							}
						}
					});
				} else {
					//send storage change for the saga
					$.post('ajax/manageSaga.php', 'action=move&id=' + $('#editShow').data('id') + '&' + $('#moveSaga').serialize(), function(data){
						if( data == 'ok' ){
							//refresh list
							getList(2);
							//inform user
							inform('Modification effectuée', 'success');
							//modal close
							$('#editHide').click();

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
			e.preventDefault();
			var $this = $(this),
				$section = $this.closest('.wrapper'),
				rel = $('#confirmShow').attr('rel'),
				target = rel.charAt(0).toUpperCase() + rel.substr(1);

			//multiple call protection
			if( $this.data('save_clicked') != 1 ){
				$this.data('save_clicked', 1);

				//hidding and emptying results
				hideInform();

				//send delete
				$.post('ajax/manage' + target + '.php', 'action=delete&id=' + $('#confirmShow').data('id'), function(data){
					if( data == 'ok' ){
						//refresh list
						getList(2);
						//inform user
						inform('Suppression effectuée', 'success');
						//modal close
						$('#confirmHide').click();
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

	//modals toggle
		$('#editHide').click(function(e){
			var $editBox = $('#editBox');

			if( $editBox.find('.form').length ){
				var rel = $editBox.find('.form').attr('rel');

				if( $editBox.find('.coverStatus').length ){
					//event listeners cleaning
					$('html, #drop_overlay')
						.unbind('dragenter')
						.unbind('dragover')
						.unbind('dragleave')
						.unbind('dragend');

					$('html').get(0).removeEventListener("drop", dropCover, true);
				}

				//replace the form
				$('#manage_' + rel).appendTo( $('body') );
			}
		});

		$('#editShow').click(function(e){
			var rel = $(this).attr('rel'),
				target = rel.charAt(0).toUpperCase() + rel.substr(1),
				$section = $('#editBox .formWrapper');

			if( $('img', '#editPreview').length ){
				$('#previewShow').click();
			} else {
				$('#previewHide').click();
			}

			//reset validation visual infos
			$section.find(':input, .coverStatus').removeClass('required valid error upload');

			if( $section.find('.coverStatus').length ){
				$('html, #drop_overlay')
					.bind('dragenter', dragEnter)
					.bind('dragover', dragOver)
					.bind('dragleave', dragLeave);

				$('html').get(0).addEventListener("drop", dropCover, true);
			}

			if( $('#editPreview').find('img').length ) $('#previewShow').prop({ checked: true });

			//set the storage to Miro for albums
			if( rel == 'album' ){
				setTimeout(function(){ $('#albumStorage').val( $('#albumStorage').children(':contains("Miro")').val() ); }, 800);
			}

			$('datalist, select', $section).loadList();

			//autofocus the first field
			//pointer is not displayed inside the field but the user can type
			setTimeout(function(){ $section.find('fieldset:first input[type=text]:first').focus(); }, 1000);
		});

		$('.box').delegate('.close', 'click', function(e){
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

		$('#editShow, #detailShow, #storageShow, #previewShow, #confirmShow').click(function(e){
			$(document).unbind('keydown');
		});

		$('#editHide, #detailHide, #storageHide, #previewHide, #confirmHide').click(function(e){
			addShortcutsSupport();
		});

	//menu link
		$('#nav a').click(function(e){
			//refresh current tab if already active (url#hash will not change)
			var target = $(this).attr('href').substr(1);
			if( target == $('#nav').data('activeTab') ){
				e.preventDefault();
				getList(0);
			}
		});

	//help
		$('#helpSwitch').click(function(e){
			e.preventDefault();

			$('#help').toggleClass('deploy');
		});

	//add keyboard shortcuts and escape support
		addShortcutsSupport();
		addEscapeSupport();

	//button blur for link (:active ok but no :focus...)
		$('body').delegate('a.button', 'click', function(e){
			$(this).blur();
		});

	//list display switch
		$('.listDisplaySwitch a').click(function(e){
			e.preventDefault();
			var $this = $(this),
				listDisplay = $this.closest('.listDisplaySwitch').find('a').map(function(){ return this.rel; }).get().join(' '),
				$wrapper = $this.closest('.list'),
				switchTo = $this.attr('rel');

			$this.addClass('disabled').siblings().removeClass('disabled');

			$('#holder').removeClass( listDisplay ).addClass( switchTo ); //to get the final .item dimensions without waiting the transition duration
			$wrapper.removeClass( listDisplay ).addClass( switchTo ).children('.listContent').render('relayout');
		});

	//last check date
		$('#list_band').delegate('.externalLink', 'click', function(e){
			//update the date on band web site link click
			$.post('ajax/manageBand.php', { action: 'updateLastCheckDate', id: $(this).attr('rel') });

			//date sort active
			if( $('#bandSortType').val() >= 2 ){
				var $li = $(this).closest('li');
				var $ul = $li.parent();

				if( $('#bandSortType').val() == 2 ){
					//asc sort, oldest first, moving the li at the list end
					$li.appendTo($ul);
				} else {
					//desc sort, newest first, moving the li at the list start
					$li.prependTo($ul);
				}
			}
		});

		$('#list_saga').delegate('.externalLink', 'click', function(e){
			//update the date on band web site link click
			$.post('ajax/manageSaga.php', {action: 'updateLastCheckDate', id: $(this).attr('rel')});

			//date sort active
			if( $('#sagaSortType').val() >= 2 ){
				var $li = $(this).closest('li');
				var $ul = $li.parent();

				if( $('#sagaSortType').val() == 2 ){
					//asc sort, oldest first, moving the li at the list end
					$li.appendTo($ul);
				} else {
					//desc sort, newest first, moving the li at the list start
					$li.prependTo($ul);
				}
			}
		});

	//quick links for title in form
		var $quickLink = $('<a>', { 'class': 'button icon externalLink quickLink', 'target': '_blank', 'data-icon': '/' });
		$('#movieTitle')[0].addEventListener('input', function(){
			if( !$(this).val().length ){
				$(this).siblings('.quickLink').remove(); //clean the quick links
			}
			if( !$(this).siblings('.quickLink').length ){
				$quickLink.clone().attr('title', 'Rechercher sur Google Image').insertAfter( $(this) );
				$quickLink.clone().attr('title', 'Rechercher sur IMDB').insertAfter( $(this).siblings('.quickLink') );
			} else {
				$(this).siblings('.quickLink:first').attr('href', 'http://www.google.com/images?q=' + $(this).val() + ' movie');
				$(this).siblings('.quickLink:last').attr('href', 'http://www.imdb.com/find?s=all&q=' + $(this).val());
			}
		}, false);
		$('#bookTitle')[0].addEventListener('input', function(){
			if( !$(this).val().length ){
				$(this).siblings('.quickLink').remove(); //clean the quick links
			}
			if( !$(this).siblings('.quickLink').length ){
				$quickLink.clone().attr('title', 'Rechercher sur Google Image').insertAfter( $(this) );
				$quickLink.clone().attr('title', 'Rechercher sur Fantastic Fiction').insertAfter( $(this).siblings('.quickLink') );
			} else {
				$(this).siblings('.quickLink:first').attr('href', 'http://www.google.com/images?q=' + $(this).val() + ' book');
				$(this).siblings('.quickLink:last').attr('href', 'http://www.fantasticfiction.co.uk/search/?searchfor=book&keywords=' + $(this).val());
			}
		}, false);
		$('#albumTitle')[0].addEventListener('input', function(){
			if( !$(this).val().length ){
				$(this).siblings('.quickLink').remove(); //clean the quick links
			}
			if( !$(this).siblings('.quickLink').length ){
				$quickLink.clone().attr('title', 'Rechercher sur Google Image').insertAfter( $(this) );
			} else {
				$(this).siblings('.quickLink').attr('href', 'http://www.google.com/images?q=' + $('#albumBand_1').val() + ' ' + $(this).val() + ' music album');
			}
		}, false);
		$('#bandName')[0].addEventListener('input', function(){
			if( !$(this).val().length ){
				$(this).siblings('.quickLink').remove(); //clean the quick links
			}
			if( !$(this).siblings('.quickLink').length ){
				$quickLink.clone().attr('title', 'Rechercher sur Wikipedia').insertAfter( $(this) );
			} else {
				$(this).siblings('.quickLink').attr('href', 'http://en.wikipedia.org/w/index.php?search=' + $(this).val());
			}
		}, false);

	//saga title in form
		$('#bookSagaTitle, #movieSagaTitle').change(function(){
			var $this = $(this);
			var $form = $this.closest('.form');
			var rel = $form.attr('rel');
			var target = rel.charAt(0).toUpperCase() + rel.substr(1);
			var $dl = $this.siblings('datalist');
			var decoder = $('<textarea>');

			//is the saga present in the database
			$this.siblings('.quickLink').remove(); //clean the quick links
			if( $this.val() != '' && $('option[value="'+$this.val()+'"]', $dl).length ){
				$.post('ajax/manageSaga.php', 'action=getByTitleFor'+target+'&title='+$this.val(), function(saga){
					if( !$.isEmptyObject(saga) ){
						if( saga.sagaSearchURL != '' && saga.sagaSearchURL != null ){
							$this.siblings('.quickLink').remove() //clean the quick links

							$quickLink
								.clone()
								.attr('title', 'Détail de cette saga sur internet')
								.attr('href', decoder.html(saga.sagaSearchURL).val())
								.insertAfter( $this );
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
							} else if( rel == 'movie' ){
								var indice = 1;
								$('.anotherInfo:gt(0)', '#movieArtists').remove();
								$.each( saga.artists, function(i, a){
									if( indice > 1 ) $('#another_artist').click();
									$('#movieArtist_'+indice).val(decoder.html(a).val()).trigger('blur');
									indice++;
								});
							}
						}

						$('#'+rel+'SagaPosition').focus();
					}
				});
			}
		});

	//relayout the list on resize
		var to = null;
		$(window).resize(function(){
			if( to ) clearTimeout(to);
			to = window.setTimeout(function(){
				$('#list_' + $('#nav').data('activeTab')).children('.listContent').render('relayout');
			}, 100);
		});

	//timeout function for loadList() and reloadParts();
		function ajaxCalls(){
			if( !delayAjax ){
				if( delayTimeout ) clearTimeout(delayTimeout);
				tabSwitch();
			}
			else delayTimeout = setTimeout(function(){ ajaxCalls(); }, 1000);
		}

	//rating for saga
		$('#list_saga')
			.delegate('.rating input', 'change', function(e){
				var saga = this.id.split('_')[0].substring(4); //from format sagaX_starY
				$.post('ajax/manageSaga.php', { 'action': 'updateRating', 'id': saga, 'rating': this.value });
			});

	//"onload" ajax call for data
	ajaxCalls();

});

/**
 * change the current tab
 */
function tabSwitch(){
	var target = window.location.hash.substr(1) || $('.tab:first').attr('id');

	$('#nav a').removeClass('active');
	$('a[href$=' + target +']', '#nav').addClass('active');

	if( $('#'+target).length ){ // Argument is a valid tab name
		window.location.hash = '#' + target; //security if hash empty
		$('#nav').data('activeTab', target);
		$('#nav').data('updating', 0); //remove multiple call protection since it's a new tab
		getList(0);
	}
}

/**
 * ajax load <datalist> and <select> content
 */
$.fn.loadList = function(){
	return this.each(function(){
		var $this = $(this),
			key = $this.attr('id'),
			decoder = $('<textarea>'),
			cachedData,
			lastModified = 0;

		//if( $this.children().length <= 1 ) forceUpdate = 1;

		try {
			cachedData = localStorage.getObject(key);
			if( cachedData ){
				lastModified = cachedData.lastModified;
			}
		} catch( e ){
			alert(e);
		}

		//ask the list values to the server and create the <option>s with it
		$.ajax('ajax/loadList.php', {
			data: 'field=' + $this.attr('id'),
			dataType: 'json',
			headers: {
				'If-Modified-Since': lastModified
			},
			success: function(data, textStatus, jqXHR){
				//server will send a 304 status if the list has not changed
				if( jqXHR.status == 200 ){
					try {
						lastModified = jqXHR.getResponseHeader('Last-Modified');

						localStorage.setObject(key, {'lastModified': lastModified, 'data': data});
					} catch( e ){
						alert(e);
					}

				} else { //304
					data = cachedData.data;

					if( $this.find('option:gt(0)').length ){
						//options already present, no need to fill the field
						return;
					}
				}

				if( $this.is('datalist') ) $this.empty();
				else {
					var isFilter = false;
					if( $this.attr('id').search(/Filter/) != -1 && $this.val() != '' ){
						$this.data('sav', $this.val());
						isFilter = true;
					}
					$this.find('option:gt(0)').remove(); //keep the first option aka "placeholder"
				}

				$.each(data, function(i, obj){
					obj.value = decoder.html(obj.value).val();
					$('<option>', { "value": ( obj.id ? obj.id : obj.value ), text: obj.value }).appendTo( $this );
				});

				if( isFilter ) $this.val(list.data('sav'));

				if( $this.data('selectedId') ){
					$this.val( $this.data('selectedId') );
					$this.removeData('selectedId');
				}
			}
		});
	});
}

/**
 * replace accentued characters by non accentued counterpart
 * and remove spaces
 * used in jquery template
 */
String.prototype.urlify = function(){
	var s = this,
		accent = 'ÀÁÂÃÄÅàáâãäåÒÓÔÕÕÖØòóôõöøÈÉÊËèéêëðÇçÐÌÍÎÏìíîïÙÚÛÜùúûüÑñŠšŸÿýŽž ',
		without = ['A','A','A','A','A','A','a','a','a','a','a','a','O','O','O','O','O','O','O','o','o','o','o','o','o','E','E','E','E','e','e','e','e','e','C','c','D','I','I','I','I','i','i','i','i','U','U','U','U','u','u','u','u','N','n','S','s','Y','y','y','Z','z',''],
		result = [];

	s = s.split('');
	len = s.length;
	for (var i = 0; i < len; i++){
		var j = accent.indexOf(s[i]);
		if( j != -1 ){
			result[i] = without[j];
		} else {
			result[i] = s[i];
		}
	}
	return result.join('');
}

/**
 * test if sub domains are available
 * yes : return the full url
 * no : return relative url
 * used in jquery template
 */
function getFullUrl(partialUrl, id){
	if( useSubDomains ){
		return subDomains[ id % subDomains.length ] + partialUrl + id;
	}

	return partialUrl + id;
}

/**
 * localStorage method for caching javascript objects
 */
Storage.prototype.setObject = function(key, value){
	this.setItem(key, JSON.stringify(value));
}

Storage.prototype.getObject = function(key){
	return this.getItem(key) && JSON.parse( this.getItem(key) );
}

/**
 * storage list function to separate storages rooms and types
 * used in jquery template
 */
function isStorageChanged(i){
	var item = this.data.list[i],
		check = item.storageColumn != null && i > 0 && (this.data.list[i-1].storageRoom != item.storageRoom || this.data.list[i-1].storageType != item.storageType);

	if( check ){
		this.data.currentStorageURL = 'storage/' + item.storageRoom.urlify() + '_' + item.storageType.urlify() + '.png';
	}
	this.data.list[i].storageURL = 'storage/' + item.storageRoom.urlify() + '_' + item.storageType.urlify() + (item.storageColumn != null || item.storageLine != null ? '_' + item.storageColumn + item.storageLine : '' ) + '.png';

	return check;
}

/**
 * add escape key support when a modal popup is visible
 */
function addEscapeSupport(){
	//chrome don't catch escape with keypress event, so using keydown
	$('html').unbind('keydown').keydown(function(e){
		// ESCAPE key pressed
			if( e.keyCode == 27 ){

			//inform visible ?
			if( $('#inform span:visible').length ){
				hideInform();

			//help visible ?
			} else if( $('#help').hasClass('deploy') ){
				$('.help').click();

			//drag and drop active ?
			} else if( $('#drop_overlay:visible').length ){
				$('#drop_overlay').hide()

			//storage image visible ?
			} else if( $('#storageShow:checked').length ){
				$('#storageHide').click();

			//storage image visible ?
			} else if( $('#previewShow:checked').length ){
				$('#previewHide').click();

			//edit modal visible ?
			} else if( $('#editShow:checked').length ){
				$('#editHide').click();

			//confirm modal visible ?
			} else if( $('#confirmShow:checked').length ){
				$('#confirmHide').click();

			//detail modal visible ?
			} else if( $('#detailShow:checked').length ){
				$('#detailHide').click();
			}
		}
	});
}

/**
 * add keyboard shortcuts support when no modal popul are visible
 */
function addShortcutsSupport(){
	$(document).unbind('keydown').keydown(function(e){
		if( !$(e.target).is('input') && !$(e.target).is('textarea') ){
			//"a" pressed for add
			if( e.which == 65 ){
				$('.add', '#'+$('#nav').data('activeTab')).click();

			//"f" pressed for filter
			} else if( e.which == 70 ){
				$('.filterFormSwitch', '#'+$('#nav').data('activeTab')).click();

			//"v" pressed for switch view
			} else if( e.which == 86 ){
				//find the current view, get the next view and click() it
				var switches = $('.listDisplaySwitch a', '#'+$('#nav').data('activeTab'));
				if( !switches.length ) return; //no view switches in active Tab

				var current = $('.listDisplaySwitch', '#'+$('#nav').data('activeTab')).find('.disabled');
				if( !current.length ) return; //no active view, security


				var index = switches.index(current);
				index++;
				if( index == switches.length ) index = 0;

				switches.eq(index).click();

			//tab index for switching tab
			} else {
				var index = parseInt( String.fromCharCode( e.which ) );
				if( !isNaN(index) ){
					if( $('.tab').eq( index-1 ).length ){
						var newHash = $('.tab').eq( index-1 ).attr('id');
						if( $('#nav').data('activeTab') == newHash ) getList(0); //tab already active, refresh it
						else window.location.hash = '#' + newHash;
					}
				}
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
	$.each(data, function(index, error){
		//remove previous error message if present
		$('#' + error[0]).addClass(error[2]).siblings('.tip').remove();

		//add error message
		$('#' + error[0]).parent().append( $('<span>', { 'class': 'tip', 'text': error[1] }) );
	});
}

/**
 * Display the user information message on the main page and in the modal dialog if it is visible
 * @param string msg
 * @param string cssClass
 */
function inform( msg, cssClass ){
	$('#inform span').addClass( cssClass ).text( msg ).parent().show();
}

/**
 * Empty and hide the user information message
 */
function hideInform(){
	$('#inform span').attr('class', '').empty().parent().hide();
}

var dropTimeout = 0;
/**
 * manage the drag enter event for drag and drop
 * @param object event
 */
function dragEnter(event){
	event.preventDefault();
	$('#drop_overlay').show();
	if( event.dataTransfer ){
		event.dataTransfer.effectAllowed = "copy";
		event.dataTransfer.dropEffect = "copy";
	} else if( event.originalEvent.dataTransfer ){
		event.originalEvent.dataTransfer.effectAllowed = "copy";
		event.originalEvent.dataTransfer.dropEffect = "copy";
	}

	dropTimeout = window.setTimeout(function(){ $('#drop_overlay').hide(); }, 3000);
}

/**
 * manage the drag over event for drag and drop
 * @param object event
 */
function dragOver(event){
	event.preventDefault();
	if( $('#drop_overlay:hidden').length ) $('#drop_overlay').show();

	clearTimeout(dropTimeout);
	dropTimeout = window.setTimeout(function(){ $('#drop_overlay').hide(); }, 3000);
}

/**
 * manage the drag leave event for drag and drop
 * @param object event
 */
function dragLeave(event){
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
	event.preventDefault();

	$('#drop_overlay').hide();

	var $section = $('#editBox').find('.form'),
		rel = $section.attr('rel'),
		$coverStatus = $('.coverStatus', $section).removeClass('required valid error upload').siblings('.tip').remove(); //reset validation visual infos and error tip

	if( !$coverStatus.data('oldText') ) $coverStatus.data('oldText', $coverStatus.html());

	var dt = event.dataTransfer;
	var files = dt.files;

	// manage remote image
	if( files.length == 0 && dt.types.contains("application/x-moz-file-promise-url") ){
		hideInform();
		url = dt.getData("application/x-moz-file-promise-url");

		$coverStatus.addClass('upload');
		$.ajax({
			url: 'ajax/manageCover.php?rel='+rel,
			data: 'url='+dt.getData("application/x-moz-file-promise-url"),
			complete: function(e){
				$coverStatus.removeClass('upload');
			},
			success: function(result){
				var timestamp = new Date().getTime();
				$('#editPreview').empty().append( $('<img>', { src: 'covers/' + result + '?' + timestamp }) );
				$('#' + rel + 'Cover').val( result );
				$coverStatus.html( $coverStatus.data('oldText') ).addClass('valid');
				if( !$('#previewShow:checked').length ) $('#previewShow').click();
			},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				$coverStatus.html( errorThrown ).addClass('error');
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
		upload(file, rel, $coverStatus);
	} else {
		formErrors([[rel+'CoverStatus', 'Seule les images .jpg, .jpeg, .png or .gif sont permises.', 'error']]);
	}
}

/*
 * Upload files to the server using HTML5 File API and sendAsBinary method
 * @param object file
 */
function upload(file, rel, $coverStatus){
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
				$coverStatus.addClass('upload');


				xhr.onreadystatechange = function(){
					if( xhr.readyState == 4 ){
						$coverStatus.removeClass('upload');
						if( xhr.status == 200 ){
							$coverStatus.html( $coverStatus.data('oldText') ).addClass('valid');
							var timestamp = new Date().getTime();
							$('#editPreview').empty().append( $('<img>', { src: 'covers/' + file.name + '?' + timestamp }) );
							$('#' + rel + 'Cover').val( file.name );
							if( !$('#previewShow:checked').length ){
								$('#previewShow').click();
							}
						} else {
							$coverStatus.html( xhr.responseText ).addClass('error');
						}
					}
				};
			}, true);

			reader.addEventListener('error', function(event){
				switch(event.target.error.code){
					case event.target.error.NOT_FOUND_ERR:
						$coverStatus.html('Fichier non trouvé!').removeClass('upload').addClass('error');
					break;
					case event.target.error.NOT_READABLE_ERR:
						$coverStatus.html('Fichier non lisible!').removeClass('upload').addClass('error');
					break;
					case event.target.error.ABORT_ERR:
					break;
					default:
						$coverStatus.html('Erreur de lecture.').removeClass('upload').addClass('error');
				}
			}, true);

			reader.addEventListener('progress', function(event){
				if (event.lengthComputable) {
					$coverStatus.html('Chargement : '+ Math.round((event.loaded * 100) / event.total) +'%');
				}
			}, true);

			reader.addEventListener('loadProgress', function(event){
				if (event.lengthComputable) {
					$coverStatus.html('Chargement : '+ Math.round((event.loaded * 100) / event.total) +'%');
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
				$coverStatus.addClass('upload');


				xhr.onload = function(){
					if( xhr.readyState == 4 ){
						$coverStatus.removeClass('upload');
						if( xhr.status == 200 ){
							$coverStatus.html( $coverStatus.data('oldText') ).addClass('valid');
							var timestamp = new Date().getTime();
							$('#editPreview').empty().append( $('<img>', { src: 'covers/' + file.name + '?' + timestamp }) );
							$('#' + rel + 'Cover').val(file.name);
							if( !$('#previewShow:checked').length ){
								$('#previewShow').click();
							}
						} else {
							$coverStatus.html( xhr.responseText ).addClass('error');
						}
					}
				};
			};
		}

		// The function that starts reading the file as a binary string
		reader.readAsBinaryString(file);
	} else {
		$coverStatus.removeClass('upload').addClass('error')
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
	var $el = $(event.target);

	if( $el[0].validity ){
		if( $el[0].validity.valid ){
			if( $el.val() != '' ) $el.removeClass('required error upload').addClass('valid');
		} else if( event.type != "input" ){
			if( $el[0].validity.valueMissing ){ // User hasn't typed anything
				$el.removeClass('error valid upload').addClass('required');
			} else {
				$el.removeClass('required valid upload').addClass('error');
			}
		} else if( $el[0].validity.valueMissing ){
			$el.removeClass('required valid error upload');
		}
	}
}

/**
 * get the list
 * @param integer type : 0 no filter, 1 filter from form, 2 use filter if present in session, 3 endless scroll pagination
 */
var didScroll = false,
	interval = null,
	$last = null,
	translateTop = new RegExp("translate[3d]?\([^p]+px, ([^p]+)\)");
function getList( type ){
	hideInform();
	var $nav = $('#nav');
	if( !$nav.length ) return;

	//multiple call protection
	if( $nav.data('updating') != 1 ){
		$nav.data('updating', 1);

		var tab = $('#nav').data('activeTab');
		if( tab === undefined ) return;

		var t = tab.charAt(0).toUpperCase() + tab.substr(1),
			$list = $('#list_' + tab),
			$filter = $('#' + tab + '_filter'),
			$body = $('body');

		//prepare jQuery Template
		if( !$('#' + tab + 'PaginateTemplate').data('tmpl') ){
			$('#' + tab + 'PaginateTemplate').template( tab + 'Paginate' );
		}
		if( !$('#' + tab + 'ListTemplate').data('tmpl') ){
			$('#' + tab + 'ListTemplate').template( tab + 'List' );
		}

		$body.css('cursor', 'progress');

		$.ajax({
			url: 'ajax/manage'+ t +'.php',
			data: 'action=' + ( type == 3 ? 'more' : 'list&type=' + type + '&' + $(':input', $filter).serialize() ),
			type: 'POST',
			dataType: 'json',
			complete: function(){
				$nav.data('updating', 0);
				$body.css('cursor', '');
			},
			success: function(data){
				if( type == 0 ){
					$(window).scrollTop(0);
				}
				if( type != 3 ){
					$(window).unbind('scroll');
					clearInterval(interval);

					//remove old list
					$list.children('.paginate, .listContent').remove();

					$.tmpl( tab + 'Paginate', data).appendTo( $list );
					$.tmpl( tab + 'List', data).appendTo( $list );

					$list.children('.listContent').render();
					$last = $list.find('.item:last');

					var $paginate = $list.children('.paginate');

					//pagination on scroll
					if( !$paginate.hasClass('end') ){
						if( interval ) clearInterval( interval );
						$(window).unbind('scroll').scroll(function(e){
							didScroll = true;
						});

						interval = setInterval(function(){
							if( didScroll ){
								didScroll = false;
								if( $last.length && !$('#detailShow:checked, #editShow:checked').length ){
									var t = translateTop.exec($last.attr('style')),
										top = ( t.length == 3 ? parseFloat(t[2]) : 0 );
									if( top > 0 && ($(window).scrollTop() + $(window).height()) >= top ){
										getList(3);
									}
								}
							}
						}, 1000);
					}

					//hide detail
					if( type == 2 && $('#detailShow:checked').length ){
						$('#detailHide').click();
						var $box = $('#detailBox');
						//display "refreshed" detail
						if( $box.data('link') && $box.data('tab') == tab ){
							var i = $box.data('link');
							var timestamp = new Date().getTime();

							if( tab != 'storage' ){
								var $detailIcon = $list.find('.detail[href='+i+']');
							} else {
								var $detailIcon = $list.find('.storage[href='+i+']');
							}

							//force cover update by adding a timestamp to the url
							//remove src then add it with new value to avoid flicker
							if( $detailIcon.length ){
								var timestamp = new Date().getTime(),
									$cover = $detailIcon.parent().find('.cover'),
									src = $cover.attr('src') + '&ts=' + timestamp;

								if( $cover.length ){
									$cover.removeAttr('src').attr('src', src);
									$detailIcon.parent().css('background-image', 'url(' + src + ')');

									$detailIcon.click();
								}
							}
						}
					}

					//if filters are visible (.deploy), update them
					$filter.children('.listFilter.deploy').find('datalist, select').loadList();
				} else {
					//remove old paginate
					$list.children('.paginate').remove();
					//add new templated paginate
					$.tmpl( tab + 'Paginate', data).appendTo( $list );

					//get new list
					var newList = $.tmpl( tab + 'List', data);

					//append new list <li> to old one
					$list.children('.listContent').append( newList.children() ).render('relayout');
					$last = $list.find('.item:last');

					if( $list.children('.paginate').hasClass('end') ){
						$(window).unbind('scroll');
					}
				}
			}
		});
	}
}

