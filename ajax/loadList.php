<?php
//manage dropdown lists content for ajax requests
try {
    require_once('../conf.ini.php');

    header('Content-type: application/json');
	$expires = 60*60*24*7; //1 weeks
	header('Expires: '.gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT'); //always send else the next request will not have "If-Modified-Since" header
	header('Cache-Control: max-age=' . $expires.', must-revalidate'); //must-revalidate to force browser to used the cache control rules sended

	if( !filter_has_var(INPUT_GET, 'field') || !filter_has_var(INPUT_GET, 'forceUpdate') ){
		throw new Exception('Chargement des listes déroulantes : paramètre manquante.');
	} else {
		$field = filter_input(INPUT_GET, 'field', FILTER_SANITIZE_STRING);
		if( is_null($field) || $field === false ){
			throw new Exception('Chargement des listes déroulantes : liste incorrecte.');
		}

		//use to force a HTTP 200 response containing a fresh list (when browser has nothing in the <select> or <datalist> element)
		$forceUpdate = filter_input(INPUT_GET, 'forceUpdate', FILTER_SANITIZE_NUMBER_INT);
		if( is_null($forceUpdate) || $forceUpdate === false ){
			throw new Exception('Chargement des listes déroulantes : paramètre incorrect.');
		}
		$forceUpdate = filter_var($forceUpdate, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
		if( is_null($forceUpdate) ){
			throw new Exception('Chargement des listes déroulantes : paramètre incorrect.');
		}

		//multiple list call for the same function
		if( stripos($field, 'storage') !== false && $field != 'storageTypeList' && $field != 'storageRoomList'){
			if( stripos($field, 'filter') !== false ){
				//movie_book_album in the name is used in _cleanSession functions
				$field = 'movie_book_album_storagesForFilterList';
			} else {
				//movie_book_album in the name is used in _cleanSession functions
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
		if( $browserHasCache ){
			$oTimestamp = new list_timestamp();
			$ts = $oTimestamp->getByName($target);
			if( !empty($ts) && !empty($ts['name']) ){
				$lastModified = strtotime($ts['stamp']);
				//browser has list in cache and list was not modified
				if( $modifiedSince == $lastModified ){
					if( !$forceUpdate ){
						header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
						die;
					}
				}
			}
		}

		//browser don't have list in cache, need to retrieve (or create if not found) the last modified date
		if( $lastModified == 0 ){
			$oTimestamp = new list_timestamp();
			$ts = $oTimestamp->getByName($target);
			if( empty($ts) ){
				$oTimestamp->updateByName($target);
				$ts = $oTimestamp->getByName($target);
			}
			$lastModified = strtotime($ts['stamp']);
		}

		if( $lastModified != 0 ) header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");

		switch ( $field ){
			/* form fields */
			case 'albumTypeList' :
					$oAlbum = new album();
					$list = $oAlbum->getAlbumsTypes();
				break;
			case 'bookSizeList' :
					$oBook = new book();
					$list = $oBook->getBooksSizes();
				break;
			case 'movieGenreList' :
					$oMovie = new movie();
					$list = $oMovie->getMoviesGenres();
				break;
			case 'movieMediaTypeList' :
					$oMovie = new movie();
					$list = $oMovie->getMoviesMediaTypes();
				break;
			case 'bandGenreList' :
					$oBand = new band();
					$list = $oBand->getBandsGenres();
				break;
			case 'bookSagaList' :
					$oSaga = new saga();
					$list = $oSaga->getSagasTitles('book');
				break;
			case 'movieSagaList' :
					$oSaga = new saga();
					$list = $oSaga->getSagasTitles('movie');
				break;
			case 'movie_book_album_storagesForDropDownList' :
					$oStorage = new storage();
					$list = $oStorage->getStoragesForDropDownList();
				break;
			case 'albumBandList' :
					$oBand = new Band();
					$list = $oBand->getBandsForDropDownList();
				break;
			case 'bookAuthorList' :
					$oAuthor = new author();
					$list = $oAuthor->getAuthorsForDropDownList();
				break;
			case 'movieArtistList' :
					$oArtist = new artist();
					$list = $oArtist->getArtistsForDropDownList();
				break;
			case 'storageTypeList' :
					$oStorage = new storage();
					$list = $oStorage->getStoragesTypesList();
				break;
			case 'storageRoomList' :
					$oStorage = new storage();
					$list = $oStorage->getStoragesRoomsList();
				break;

			/* common filters */
			case 'movie_book_album_storagesForFilterList' :
					$oStorage = new storage();
					$list = $oStorage->getStoragesForFilterList();
				break;

			/* book filters */
			case 'bookTitleFilterList' :
					$oBook = new book();
					$list = $oBook->getBooksTitleForFilterList();
				break;
			case 'bookSagaFilterList' :
					$oSaga = new saga();
					$list = $oSaga->getBooksSagasForFilterList();
				break;
			case 'bookAuthorFilterList' :
					$oAuthor = new author();
					$list = $oAuthor->getAuthorsForFilterList();
				break;
			case 'bookLoanFilterList' :
					$oLoan = new loan();
					$list = $oLoan->getBooksLoansForFilterList();
				break;

			/* movie filters */
			case 'movieTitleFilterList' :
					$oMovie = new movie();
					$list = $oMovie->getMoviesTitleForFilterList();
				break;
			case 'movieSagaFilterList' :
					$oSaga = new saga();
					$list = $oSaga->getMoviesSagasForFilterList();
				break;
			case 'movieArtistFilterList' :
					$oArtist = new artist();
					$list = $oArtist->getArtistsForFilterList();
				break;
			case 'movieLoanFilterList' :
					$oLoan = new loan();
					$list = $oLoan->getMoviesLoansForFilterList();
				break;

			/* album filters */
			case 'albumTitleFilterList' :
					$oAlbum = new album();
					$list = $oAlbum->getAlbumsTitleForFilterList();
				break;
			case 'albumBandFilterList' :
					$oBand = new Band();
					$list = $oBand->getBandsForFilterList();
				break;
			case 'albumLoanFilterList' :
					$oLoan = new loan();
					$list = $oLoan->getAlbumsLoansForFilterList();
				break;

			/* band filters */
			case 'bandNameFilterList' :
					$oAlbum = new band();
					$list = $oAlbum->getBandsNameForFilterList();
				break;
			case 'bandGenreFilterList' :
					$oAlbum = new band();
					$list = $oAlbum->getBandsGenreForFilterList();
				break;


			/* saga filters */
			case 'sagaTitleFilterList' :
					$oSaga = new saga();
					$list = $oSaga->getSagasForFilterList();
				break;

			default:
				throw new Exception('Chargement de liste : cible non reconnue.');
		}

		echo json_encode($list);

		die;
	}
} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo $e->getMessage();
	die;
}
?>