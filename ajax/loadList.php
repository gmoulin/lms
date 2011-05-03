<?php
//manage dropdown lists content for ajax requests
try {
    require_once('../conf.ini.php');

    header('Content-type: application/json');
	$expires = 60*60*24*7; //1 weeks
	header('Expires: '.gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT'); //always send else the next request will not have "If-Modified-Since" header
	header('Cache-Control: max-age=' . $expires.', must-revalidate'); //must-revalidate to force browser to used the cache control rules sended

	if( !filter_has_var(INPUT_GET, 'field') ){
		throw new Exception('Chargement des listes déroulantes : paramètre manquante.');
	} else {
		$field = filter_input(INPUT_GET, 'field', FILTER_SANITIZE_STRING);
		if( is_null($field) || $field === false ){
			throw new Exception('Chargement des listes déroulantes : liste incorrecte.');
		}

		//multiple list call for the same function
		if( stripos($field, 'storage') !== false && $field != 'storageTypeList' && $field != 'storageRoomList'){
			if( stripos($field, 'filter') !== false ){
				$field = 'movie_book_album_storagesForFilterList';
			} else {
				$field = 'movie_book_album_storagesForDropDownList';
			}
		}

		//check the request headers for "If-Modified-Since"
		$request_headers = apache_request_headers();
		$browserHasCache = ( array_key_exists('If-Modified-Since', $request_headers) ? true : false );
		if( $browserHasCache ){
			$modifiedSince = strtotime($request_headers['If-Modified-Since']);
		}

		$lastModified = 0;
		$target = ( strpos($field, 'List') !== false ? substr($field, 0, strlen($field)-4 ) : $field );

		switch ( $field ){
			/* form fields */
			case 'albumTypeList' :
					$oAlbum = new album();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oAlbum->getAlbumsTypes( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oAlbum->getAlbumsTypes( true );
				break;
			case 'bookSizeList' :
					$oBook = new book();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oBook->getBooksSizes( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oBook->getBooksSizes( true );
				break;
			case 'movieGenreList' :
					$oMovie = new movie();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oMovie->getMoviesGenres( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oMovie->getMoviesGenres( true );
				break;
			case 'movieMediaTypeList' :
					$oMovie = new movie();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oMovie->getMoviesMediaTypes( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oMovie->getMoviesMediaTypes( true );
				break;
			case 'bandGenreList' :
					$oBand = new band();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oBand->getBandsGenres( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oBand->getBandsGenres( true );
				break;
			case 'bookSagaList' :
					$oSaga = new saga();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oSaga->getSagasTitles( 'book', null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oSaga->getSagasTitles( 'book', true );
				break;
			case 'movieSagaList' :
					$oSaga = new saga();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oSaga->getSagasTitles( 'movie', null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oSaga->getSagasTitles( 'movie', true );
				break;
			case 'movie_book_album_storagesForDropDownList' :
					$oStorage = new storage();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oStorage->getStoragesForDropDownList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oStorage->getStoragesForDropDownList( true );
				break;
			case 'albumBandList' :
					$oBand = new band();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oBand->getBandsForDropDownList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oBand->getBandsForDropDownList( true );
				break;
			case 'bookAuthorList' :
					$oAuthor = new author();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oAuthor->getAuthorsForDropDownList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oAuthor->getAuthorsForDropDownList( true );
				break;
			case 'movieArtistList' :
					$oArtist = new artist();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oArtist->getArtistsForDropDownList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oArtist->getArtistsForDropDownList( true );
				break;
			case 'storageTypeList' :
					$oStorage = new storage();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oStorage->getStoragesTypesList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oStorage->getStoragesTypesList( true );
				break;
			case 'storageRoomList' :
					$oStorage = new storage();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oStorage->getStoragesRoomsList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oStorage->getStoragesRoomsList( true );
				break;

			/* common filters */
			case 'movie_book_album_storagesForFilterList' :
					$oStorage = new storage();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oStorage->getStoragesForFilterList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oStorage->getStoragesForFilterList( true );
				break;

			/* book filters */
			case 'bookTitleFilterList' :
					$oBook = new book();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oBook->getBooksTitleForFilterList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oBook->getBooksTitleForFilterList( true );
				break;
			case 'bookSagaFilterList' :
					$oSaga = new saga();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oSaga->getBooksSagasForFilterList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oSaga->getBooksSagasForFilterList( true );
				break;
			case 'bookAuthorFilterList' :
					$oAuthor = new author();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oAuthor->getAuthorsForFilterList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oAuthor->getAuthorsForFilterList( true );
				break;
			case 'bookLoanFilterList' :
					$oLoan = new loan();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oLoan->getBooksLoansForFilterList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oLoan->getBooksLoansForFilterList( true );
				break;

			/* movie filters */
			case 'movieTitleFilterList' :
					$oMovie = new movie();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oMovie->getMoviesTitleForFilterList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oMovie->getMoviesTitleForFilterList( true );
				break;
			case 'movieSagaFilterList' :
					$oSaga = new saga();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oSaga->getMoviesSagasForFilterList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oSaga->getMoviesSagasForFilterList( true );
				break;
			case 'movieArtistFilterList' :
					$oArtist = new artist();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oArtist->getArtistsForFilterList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oArtist->getArtistsForFilterList( true );
				break;
			case 'movieLoanFilterList' :
					$oLoan = new loan();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oLoan->getMoviesLoansForFilterList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oLoan->getMoviesLoansForFilterList( true );
				break;

			/* album filters */
			case 'albumTitleFilterList' :
					$oAlbum = new album();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oAlbum->getAlbumsTitleForFilterList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oAlbum->getAlbumsTitleForFilterList( true );
				break;
			case 'albumBandFilterList' :
					$oBand = new band();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oBand->getBandsForFilterList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oBand->getBandsForFilterList( true );
				break;
			case 'albumLoanFilterList' :
					$oLoan = new loan();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oLoan->getAlbumsLoansForFilterList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oLoan->getAlbumsLoansForFilterList( true );
				break;

			/* band filters */
			case 'bandNameFilterList' :
					$oBand = new band();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oBand->getBandsNameForFilterList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oBand->getBandsNameForFilterList( true );
				break;
			case 'bandGenreFilterList' :
					$oBand = new band();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oBand->getBandsGenreForFilterList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oBand->getBandsGenreForFilterList( true );
				break;


			/* saga filters */
			case 'sagaTitleFilterList' :
					$oSaga = new saga();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oSaga->getSagasForFilterList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $list) = $oSaga->getSagasForFilterList( true );
				break;

			default:
				throw new Exception('Chargement de liste : cible non reconnue.');
		}

		if( !empty($lastModified) ) header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");

		echo json_encode($list);

		die;
	}
} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo $e->getMessage();
	die;
}
?>