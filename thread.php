<?
include('os-includes/config.php');
$osimo->requireGET('id',true);
$osimo->optionalGET('page',true);
$osimo->theme->load('thread');
?>