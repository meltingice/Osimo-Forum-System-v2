<?
include('config.php');
if(
	!$osimo->requirePOST('osimo_username',false) || 
	!$osimo->requirePOST('osimo_password',false) ||
	!$osimo->requirePOST('osimo_email',false)
){
	header('Location: ../register.php?error=missing_data'); exit;
}

$username = $osimo->POST['osimo_username'];
$password = $osimo->POST['osimo_password'];
$email = $osimo->POST['osimo_email'];

if(strlen($username)<3||strlen($username)>24||preg_match('/[^\w]/', $username)||strlen($password)==0){
	header('Location: ../register.php?error=invalid_data'); exit;
}

/* Create the new user */
//get('db')->insert('users')->
?>