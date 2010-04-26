<?
include('config.php');

if(!$osimo->requirePOST('osimo_username',false) || !$osimo->requirePOST('osimo_password',false)){
	header('Location: ../login.php?error=missing_data'); exit;
}

$username = $osimo->POST['osimo_username'];
$password = $osimo->POST['osimo_password'];

try {
	UserManager::login_user($username, $password);
	
	header('Location: ../index.php'); exit;
} catch (Exception $e) {
	header('Location: ../login.php?error='.$e->getMessage());
}
?>