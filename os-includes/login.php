<?
include('config.php');

if(!$osimo->requirePOST('osimo_username',false) || !$osimo->requirePOST('osimo_password',false)){
	ErrorManager::error_redirect('missing_data', 'This theme is not sending all of the required data needed to login a user.');
}

$username = $osimo->POST['osimo_username'];
$password = $osimo->POST['osimo_password'];

try {
	UserManager::login_user($username, $password);
	
	header('Location: ../index.php'); exit;
} catch (OsimoException $e) {
	ErrorManager::error_redirect($e->getExceptionType(), $e->getMessage(), '../login.php');
}
?>