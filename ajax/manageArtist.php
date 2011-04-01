<?php
//manage artists related ajax requests
try {
	require_once('../conf.ini.php');

	header('Content-type: application/json');

	$action = filter_has_var(INPUT_POST, 'action');
	if( is_null($action) || $action === false ){
		throw new Exception('Gestion des artistes : action manquante.');
	}

	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
	if( $action === false ){
		throw new Exception('Gestion des artistes : action incorrecte.');
	}

	switch ( $action ){
		case 'add' :
				$oArtist = new artist();

				$formData = $oArtist->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$oArtist->addArtist( $formData );
					$response = 'ok';

					if( isset($_SESSION['artists']) ) unset($_SESSION['artists']['list']);
					if( isset($_SESSION['movies']) ) unset($_SESSION['movies']['list']);
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'update' :
				$oArtist = new artist();

				$formData = $oArtist->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$oArtist->updArtist( $formData );
					$response = 'ok';

					if( isset($_SESSION['artists']) ) unset($_SESSION['artists']['list']);
					if( isset($_SESSION['movies']) ) unset($_SESSION['movies']['list']);
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'delete' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des artistes : identitifant de l\'artiste manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des artistes : identifiant incorrect.');
				}

				$oArtist = new artist();
				$oArtist->delArtist( $id );
				$response = "ok";

				if( isset($_SESSION['artists']) ) unset($_SESSION['artists']['list']);
				if( isset($_SESSION['movies']) ) unset($_SESSION['movies']['list']);
			break;
		case 'impact' : //on deletion
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des artistes : identitifant de l\'artiste manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des artistes : identifiant incorrect.');
				}

				$oArtist = new artist();
				$response = $oArtist->delArtistImpact( $id );

				include( LMS_PATH . '/list/impact.php' );
				die;
			break;
		case 'get' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des artistes : identitifant de l\'artiste manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des artistes : identifiant incorrect.');
				}

				if(    isset($_SESSION['artists'])
					&& isset($_SESSION['artists']['list'])
					&& isset($_SESSION['artists']['list'][$id])
					&& !empty($_SESSION['artists']['list'][$id])
				){
					$response = $_SESSION['artists']['list'][$id];

				} else {
					$oArtist = new artist();
					$response = $oArtist->getArtistById($id);

					if( !is_array($response) || empty($response) ){
						throw new Exception('Gestion des artistes : identitifant de l\'artiste incorrect.');
					}

					$response = $response[0];
				}
			break;
		case 'list' :
				$type = filter_has_var(INPUT_POST, 'type');
				if( is_null($type) || $type === false ){
					throw new Exception('Gestion des artistes : type de recherche manquant.');
				}

				$type = filter_var($_POST['type'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max-range' => 2));
				if( $type === false ){
					throw new Exception('Gestion des artistes : type de recherche incorrect.');
				}

				if( !isset($_SESSION['artists']) || !isset($_SESSION['artists']['page']) ){
					$_SESSION['artists']['page'] = 0;
					$_SESSION['artists']['numPerPage'] = 120;
				}

				if( $type == 0 ){
					$_SESSION['artists']['page'] = ( isset($_SESSION['artists']['page']) ? $_SESSION['artists']['page'] : 0 );
					$_SESSION['artistListFilters'] = array();
				}
				if( $type == 1 ){
					$_SESSION['artists']['page'] = 0;
				}

				if( $type == 1 ){
					$_SESSION['artistListFilters'] = $_POST;
				} else {
					$_POST = $_SESSION['artistListFilters'];
				}

				$oArtist = new artist();
				$response = $oArtist->getArtists();
				$artists = array();
				foreach( $response as $a ){
					$artists[$a['artistID']] = $a;
				}

				//save the list on session for future pagination
				$_SESSION['artists']['list'] = $artists;
				$_SESSION['artists']['total'] = count($artists);

				if( $type == 2 || ( $type == 0 && $_SESSION['artists']['page'] > 0 ) ){
					$artists = array_slice( $artists, 0, $_SESSION['artists']['numPerPage'] * ($_SESSION['artists']['page']+1), false );
				} else {
					$artists = array_slice( $artists, $_SESSION['artists']['numPerPage'] * $_SESSION['artists']['page'], $_SESSION['artists']['numPerPage'], false );
				}

				include( LMS_PATH . '/list/artist.php' );
				die;
			break;
		case 'more':
				if( isset($_SESSION['artists']) ){
					$_SESSION['artists']['page']++;
					$artists = array_slice( $_SESSION['artists']['list'], $_SESSION['artists']['numPerPage'] * $_SESSION['artists']['page'], $_SESSION['artists']['numPerPage'], false );
					$type = 3;

					include( LMS_PATH . '/list/artist.php' );
					die;
				} else {
					throw new Exception('Gestion des artistes : pagination impossible, liste non disponible.');
				}
			break;
		default:
			throw new Exception('Gestion des artistes : action non reconnue.');
	}

	echo json_encode($response);
	die;

} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo json_encode($e->getMessage());
	die;
}
?>