<?
include('config.php');
if(
	!$osimo->requirePOST('osimo_username',false) || 
	!$osimo->requirePOST('osimo_password',false) ||
	!$osimo->requirePOST('osimo_email',false)
){
	ErrorManager::error_redirect("missing_data", "This theme is not sending all the required data to register a user.");
}

$username = $osimo->POST['osimo_username'];
$password = $osimo->POST['osimo_password'];
$email = $osimo->POST['osimo_email'];

try {
	UserManager::register_user($username, $password, $email);
	
	header('Location: ../index.php');
} catch (OsimoException $e) {
	ErrorManager::error_redirect($e->getExceptionType(), $e->getMessage(), '../register.php');
}
?>