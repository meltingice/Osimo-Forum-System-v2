<?
include('os-includes/config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<script src="<?=$osimo->paths->path('os-includes/js/',false)?>jquery.js"></script>
	<script src="<?=$osimo->paths->path('os-includes/js/',false)?>jquery-ui.js"></script>
	<script src="<?=$osimo->paths->path('os-includes/js/osimo_editor/',false)?>osimo_editor.js"></script>
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