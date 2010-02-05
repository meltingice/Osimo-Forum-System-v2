<?
include('config.php');
if(!$osimo->requirePOST('osimo_username',false) || !$osimo->requirePOST('osimo_password',false)){
	header('Location: ../login.php?error=missing_data'); exit;
}

//$osimo->requirePOST('osimo_remember',true,false);

$username = $osimo->POST['osimo_username'];
$password = sha1($osimo->POST['osimo_password']);
//$remember = $osimo->POST['osimo_remember'];

if(strlen($username)<3||strlen($username)>24||preg_match('/[^\w]/', $username)||strlen($password)==0){
	header('Location: ../login.php'); exit;
}

$user = get('db')->select('*')->from('users')->where('username=%s AND password=%s',$username,$password)->row();
if(!$user){
	header('Location: ../login.php'); exit;
}

$_SESSION['user'] = $user;

header('Location: ../index.php'); exit;
?>