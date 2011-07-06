<?php
//manage bands related ajax requests
try {
	require_once('../conf.ini.php');

	header('Content-type: application/json');

	$action = filter_has_var(INPUT_POST, 'action');
	if( is_null($action) || $action === false ){
		throw new Exception('Gestion des groupes : action manquante.');
	}

	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
	if( $action === false ){
		throw new Exception('Gestion des groupes : action incorrecte.');
	}

	switch ( $action ){
		case 'add' :
				$oBand = new band();

				$formData = $oBand->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$oBand->addBand( $formData );
					$response = 'ok';

					if( isset($_SESSION['bands']) ) unset($_SESSION['bands']['list']);
					if( isset($_SESSION['books']) ) unset($_SESSION['books']['list']);
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'update' :
				$oBand = new band();

				$formData = $oBand->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$oBand->updBand( $formData );
					$response = 'ok';

					if( isset($_SESSION['bands']) ) unset($_SESSION['bands']['list']);
					if( isset($_SESSION['books']) ) unset($_SESSION['books']['list']);
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'updateLastCheckDate' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des groupes : identitifant du groupe manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des groupes : identifiant incorrect.');
				}

				$oBand = new band();
				$oBand->updBandLastCheckDate( $id );
				$response = "ok";
			break;
		case 'delete' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des groupes : identitifant du groupe manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des groupes : identifiant incorrect.');
				}

				$oBand = new band();
				$oBand->delBand( $id );
				$response = "ok";

				if( isset($_SESSION['bands']) ) unset($_SESSION['bands']['list']);
				if( isset($_SESSION['books']) ) unset($_SESSION['books']['list']);
			break;
		case 'impact' : //on deletion
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des groupes : identitifant du groupe manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des groupes : identifiant incorrect.');
				}

				$oBand = new band();
				$response = $oBand->delBandImpact( $id );

				include( LMS_PATH . '/list/impact.php' );
				die;
			break;
		case 'get' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des groupes : identitifant du groupe manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des groupes : identifiant incorrect.');
				}

				if(    isset($_SESSION['bands'])
					&& isset($_SESSION['bands']['list'])
					&& isset($_SESSION['bands']['list'][$id])
					&& !empty($_SESSION['bands']['list'][$id])
				){
					$response = $_SESSION['bands']['list'][$id];

				} else {
					$oBand = new band();
					$response = $oBand->getBandById($id);

					if( !is_array($response) || empty($response) ){
						throw new Exception('Gestion des groupes : identitifant du groupe incorrect.');
					}

					$response = $response[0];
				}
			break;
		case 'list' :
				$type = filter_has_var(INPUT_POST, 'type');
				if( is_null($type) || $type === false ){
					throw new Exception('Gestion des groupes : type de recherche manquant.');
				}

				$type = filter_var($_POST['type'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max-range' => 2));
				if( $type === false ){
					throw new Exception('Gestion des groupes : type de recherche incorrect.');
				}

				if( !isset($_SESSION['bands']) || !isset($_SESSION['bands']['page']) ){
					$_SESSION['bands']['page'] = 0;
					$_SESSION['bands']['numPerPage'] = 120;
				}

				if( $type == 0 ){
					$_SESSION['bands']['page'] = ( isset($_SESSION['bands']['page']) ? $_SESSION['bands']['page'] : 0 );
					$_SESSION['bandListFilters'] = array();
				}
				if( $type == 1 ){
					$_SESSION['bands']['page'] = 0;
				}

				if( $type == 1 ){
					$_SESSION['bandListFilters'] = $_POST;
				} else {
					$_POST = $_SESSION['bandListFilters'];
				}

				$oBand = new band();
				if( $type == 0 ) $response = $oBand->getBands();
				else $response = $oBand->getBandsByFullTextSearch();

				$bands = array();
				foreach( $response as $a ){
					$bands[$a['bandID']] = $a;
				}

				//save the list on session for future pagination
				$_SESSION['bands']['list'] = $bands;
				$_SESSION['bands']['total'] = count($bands);

				if( $type == 2 || ( $type == 0 && $_SESSION['bands']['page'] > 0 ) ){
					$bands = array_slice( $bands, 0, $_SESSION['bands']['numPerPage'] * ($_SESSION['bands']['page']+1), false );
				} else {
					$bands = array_slice( $bands, $_SESSION['bands']['numPerPage'] * $_SESSION['bands']['page'], $_SESSION['bands']['numPerPage'], false );
				}

				if( $type == 2 || ( $type == 0 && $_SESSION['bands']['page'] > 0 ) ){
					$nb = count($bands);
				} else {
					$nb = $_SESSION['bands']['numPerPage'] * $_SESSION['bands']['page'] + count($bands);
				}

				if( $nb > $_SESSION['bands']['total'] ) $nb = $_SESSION['bands']['total'];

				$response = array('nb' => $nb, 'total' => $_SESSION['bands']['total'], 'list' => $bands);

			break;
		case 'more':
				if( isset($_SESSION['bands']) ){
					$_SESSION['bands']['page']++;
					$bands = array_slice( $_SESSION['bands']['list'], $_SESSION['bands']['numPerPage'] * $_SESSION['bands']['page'], $_SESSION['bands']['numPerPage'], false );
					$type = 3;
					$nb = $_SESSION['bands']['numPerPage'] * $_SESSION['bands']['page'] + count($bands);

					$response = array('nb' => $nb, 'total' => $_SESSION['bands']['total'], 'list' => $bands);

				} else {
					throw new Exception('Gestion des groupes : pagination impossible, liste non disponible.');
				}
			break;
		default:
			throw new Exception('Gestion des groupes : action non reconnue.');
	}

	echo json_encode($response);
	die;

} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo json_encode($e->getMessage());
	die;
}
?>