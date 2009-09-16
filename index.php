<?
include('os-includes/config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?
	$osimo->theme->setTitle('Osimo Test Page');
	$osimo->theme->addJavascript('os-includes/js/osimo_editor/osimo_editor.js');
	$osimo->theme->get_header();
	?>
	<script>
		$(document).ready(function(){
			$("#osimo_editor").osimoeditor();
		});
	</script>
</head>
<body>
	<textarea id="osimo_editor" name="my editor"></textarea>
</body>
</html>