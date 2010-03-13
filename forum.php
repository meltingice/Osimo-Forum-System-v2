<?
include('os-includes/config.php');
$osimo->requireGET('id',true);
$osimo->optionalGET('page',true);
get('theme')->load('forum');
?>