<?
include('os-includes/config.php');
UserManager::logout_user();
header('Location: index.php');
?>