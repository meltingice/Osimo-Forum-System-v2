<html lang="en">
<head>
<? get('theme')->add_stylesheet('css/styles.css')->add_javascript('js/backend.js')->get_header(true); ?>
</head>
<body>

<div id="header_wrap">
	<div id="header">
		<h1><? get('theme')->site_title(); ?></h1>
		<h2><? get('theme')->site_description(); ?></h2>
	</div>
</div>

<div id="navbar">
	<ul>
		<li><a href="index.php">Home</a></li>
		<li><a href="#">My Profile</a></li>
		<li><a href="#">Search</a></li>
		<li><a href="#">Logout</a></li>
	</ul>
</div>
