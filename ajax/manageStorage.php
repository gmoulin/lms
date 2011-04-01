<?php
//manage storages related ajax requests
try {
	require_once('../conf.ini.php');

	header('Content-type: application/json');

	$action = filter_has_var(INPUT_POST, 'action');
	if( is_null($action) || $action === false ){
		throw new Exception('Gestion des rangements : action manquante.');
	}

	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
	if( $action === false ){
		throw new Exception('Gestion des rangements : action incorrecte.');
	}

	switch ( $action ){
		case 'add' :
				$oStorage = new storage();

				$formData = $oStorage->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$oStorage->addStorage( $formData );
					$response = 'ok';

					if( isset($_SESSION['movies']) ) unset($_SESSION['movies']['list']);
					if( isset($_SESSION['books']) ) unset($_SESSION['books']['list']);
					if( isset($_SESSION['albums']) ) unset($_SESSION['albums']['list']);
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'update' :
				$oStorage = new storage();

				$formData = $oStorage->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$oStorage->updStorage( $formData );
					$response = 'ok';

					if( isset($_SESSION['movies']) ) unset($_SESSION['movies']['list']);
					if( isset($_SESSION['books']) ) unset($_SESSION['books']['list']);
					if( isset($_SESSION['albums']) ) unset($_SESSION['albums']['list']);
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'delete' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des rangements : identitifant du rangement manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des rangements : identifiant incorrect.');
				}

				$oStorage = new storage();
				$oStorage->delStorage( $id );
				$response = 'ok';

				if( isset($_SESSION['movies']) ) unset($_SESSION['movies']['list']);
				if( isset($_SESSION['books']) ) unset($_SESSION['books']['list']);
				if( isset($_SESSION['albums']) ) unset($_SESSION['albums']['list']);
			break;
		case 'impact' : //on deletion
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des rangements : identitifant du rangement manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des rangements : identifiant incorrect.');
				}

				$oStorage = new storage();
				$response = $oStorage->delStorageImpact( $id );

				include( LMS_PATH . '/list/impactStorage.php' );
				die;
			break;
		case 'relocate' : //on deletion
				$storage = filter_has_var(INPUT_POST, 'storage');
				if( is_null($storage) || $storage === false ){
					throw new Exception('Gestion des rangements : identitifant du rangement manquant.');
				}

				$storage = filter_var($_POST['storage'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $storage === false ){
					throw new Exception('Gestion des rangements : identifiant incorrect.');
				}

				$oStorage = new storage();
				$oStorage->relocateStorage( $_POST );
				$response = "ok";
			break;
		case 'get' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des rangements : identitifant du rangement manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des rangements : identifiant incorrect.');
				}

				if(    isset($_SESSION['storages'])
					&& isset($_SESSION['storages']['list'])
					&& isset($_SESSION['storages']['list'][$id])
					&& !empty($_SESSION['storages']['list'][$id])
				){
					$response = $_SESSION['storages']['list'][$id];

				} else {
					$oStorage = new storage();
					$response = $oStorage->getStorageById($id);

					if( !is_array($response) || empty($response) ){
						throw new Exception('Gestion des rangements : identitifant du rangement incorrect.');
					}

					$response = $response[0];
				}
			break;
		case 'list' :
				$type = filter_has_var(INPUT_POST, 'type');
				if( is_null($type) || $type === false ){
					throw new Exception('Gestion des rangements : type de recherche manquant.');
				}

				$type = filter_var($_POST['type'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max-range' => 2));
				if( $type === false ){
					throw new Exception('Gestion des rangements : type de recherche incorrect.');
				}

				if( !isset($_SESSION['storages']) || !isset($_SESSION['storages']['page']) ){
					$_SESSION['storages']['page'] = 0;
					$_SESSION['storages']['numPerPage'] = 120;
				}

				if( $type == 0 ){
					$_SESSION['storages']['page'] = ( isset($_SESSION['storages']['page']) ? $_SESSION['storages']['page'] : 0 );
					$_SESSION['storageListFilters'] = array();
				}
				if( $type == 1 ){
					$_SESSION['storages']['page'] = 0;
				}

				if( $type == 1 ){
					$_SESSION['storageListFilters'] = $_POST;
				} else {
					$_POST = $_SESSION['storageListFilters'];
				}

				$oStorage = new storage();
				$response = $oStorage->getStorages();
				$storages = array();
				foreach( $response as $s ){
					$storages[$s['storageID']] = $s;
				}

				//save the list on session for future pagination
				$_SESSION['storages']['list'] = $storages;
				$_SESSION['storages']['total'] = count($storages);

				if( $type == 2 || ( $type == 0 && $_SESSION['storages']['page'] > 0 ) ){
					$storages = array_slice( $storages, 0, $_SESSION['storages']['numPerPage'] * ($_SESSION['storages']['page']+1), false );
				} else {
					$storages = array_slice( $storages, $_SESSION['storages']['numPerPage'] * $_SESSION['storages']['page'], $_SESSION['storages']['numPerPage'], false );
				}

				include( LMS_PATH . '/list/storage.php' );
				die;
			break;
		case 'more':
				if( isset($_SESSION['storages']) ){
					$_SESSION['storages']['page']++;
					$storages = array_slice( $_SESSION['storages']['list'], $_SESSION['storages']['numPerPage'] * $_SESSION['storages']['page'], $_SESSION['storages']['numPerPage'], false );
					$type = 3;

					include( LMS_PATH . '/list/storage.php' );
					die;
				} else {
					throw new Exception('Gestion des rangements : pagination impossible, liste non disponible.');
				}
			break;
		default:
			throw new Exception('Gestion des rangements : action non reconnue.');
	}

	echo json_encode($response);
	die;

} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo json_encode($e->getMessage());
	die;
}
?>