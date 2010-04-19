<?
define('IS_ADMIN_PAGE',true);
include('../os-includes/config.php');
$osimo->add_module('upgrade', new OsimoUpgrade());

$result = get('upgrade')->can_upgrade($latest);
if($result){
	echo "Upgrade to latest version: $latest";
}
else{
	echo "At latest version: $latest";
}
?>