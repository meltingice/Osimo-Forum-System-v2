<html lang="en">
<head>
	<meta http-equiv="x-ua-compatible" content="IE=8">
	<title>Osimo Installation</title>
	<script src="../os-includes/js/jquery/jquery.js" type="text/javascript"></script>
	<script src="../os-includes/js/jquery/jquery-ui.js" type="text/javascript"></script>
	<script src="js/backend.js" type="text/javascript"></script>
	<link href="css/styles.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<div id="logo">
		<img src="img/osimo_logo.png" alt="osimo" />
	</div>
	
	<div id="content_wrap">
		<div id="content_header">
			<h1>Osimo Installation</h1>
		</div>
		
		<div id="content_body">
			<div id="stage_1" class="stage">
				<p>Welcome to Osimo, a forum system written from the ground up to be simple to use and modify, yet powerful in its features.  The next few steps will guide you through the installation process.</p>
				
				<p>If you make a mistake, there will be a chance before installing to correct it.</p>
				
				<p style="margin-top: 20px;">Please be sure to have the following details ready:</p>
				<ul>
					<li><span>Database address</span></li>
					<li><span>Database user name &amp; password</span></li>
					<li><span>Database name</span></li>
					<li><span>Memcached server(s) <span style="color: #888888 !important">[optional]</span></span></li>
				</ul>
				
				<div class="action_buttons">
					<input type="button" value="GET STARTED" class="action_button" onclick="toStage(2)" />
				</div>
			</div>
			
			<div id="stage_2" class="stage">
				<p>Database Information:</p>
				
				<div id="database_info">
					<table id="database_fields">
						<tr>
							<td style="padding-right: 10px;">Database Name:</td>
							<td><input type="text" class="input_field" id="database_name" /></td>
						</tr>
						<tr>
							<td style="padding-right: 10px;">Username:</td>
							<td><input type="text" class="input_field" id="database_username" /></td>
						</tr>
						<tr>
							<td style="padding-right: 10px">Password:</td>
							<td><input type="password" class="input_field" id="database_password" /></td>
						</tr>
						<tr>
							<td style="padding-right: 10px">Database Host:</td>
							<td><input type="text" class="input_field" id="database_host" value="localhost" /></td>
						</tr>
					</table>
				</div>
				
				<div class="action_buttons">
					<input type="button" value="SAVE & CONTINUE" class="action_button" onclick="toStage(3)" />
				</div>
			</div>
			
			<div id="stage_3" class="stage">
				<p>Memcached Information:</p>
				<p style="color: #888888; margin-top: -10px; font-size: 13px;">If you don't have memcached installed, you may skip this step.</p>
				<p style="color: #444444; font-size: 13px;">Enter the address(es) in the form: server_ip1:port, server_ip2:port</p>
				<p style="color: #444444; margin-top: -10px; font-size: 13px;">The prefix can be anything but should be unique to this installation.</p>
				
				<div id="cache_info">
					<table id="cache_fields">
						<tr>
							<td style="padding-right: 10px;">Memcached Address(es):</td>
							<td><input type="text" class="input_field" id="cache_addresses" /></td>
						</tr>
						<tr>
							<td style="padding-right: 10px;">Memcached Prefix:</td>
							<td><input type="text" class="input_field" id="cache_prefix" /></td>
						</tr>
					</table>
				</div>
				<div class="action_buttons">
					<input type="button" value="SKIP THIS STEP" class="action_button" onclick="toStage(4, true)" />
					<input type="button" value="SAVE & CONTINUE" class="action_button" onclick="toStage(4, false)" />
				</div>
			</div>
			
			<div id="stage_4" class="stage">
				<p>Lets review...</p>
				
				<p style="">If any of these details look incorrect, please fix them before proceeding.  Click any value to edit it, and hit enter when you are done.</p>
				<table id="review_table">
					<tr>
						<td class="review_label">Database Name:</td>
						<td class="review_data" id="review_database_name"></td>
					</tr>
					<tr>
						<td class="review_label">Database Username: </td>
						<td class="review_data" id="review_database_username"></td>
					</tr>
					<tr>
						<td class="review_label">Database Password: </td>
						<td class="review_data" id="review_database_password"></td>
					</tr>
					<tr>
						<td class="review_label">Database Host: </td>
						<td class="review_data" id="review_database_host"></td>
					</tr>
					<tr>
						<td class="review_label">Memcached Address(es): </td>
						<td class="review_data" id="review_cache_addresses"></td>
					</tr>
					<tr>
						<td class="review_label">Memcached Prefix: </td>
						<td class="review_data" id="review_cache_prefix"></td>
					</tr>
				</table>
				
				<div class="action_buttons">
					<input type="button" value="INSTALL OSIMO" class="action_button" onclick="toStage(5)" />
				</div>
			</div>
			
			<div id="stage_5" class="stage">
				<p>Installation in progress...</p>
				
				<ul id="install_steps">
					<li><img src="img/icons/time.png"> Sending config to server</li>
					<li><img src="img/icons/time.png"> Writing config to disk</li>
					<li><img src="img/icons/time.png"> Connecting to database</li>
					<li><img src="img/icons/time.png"> Creating database tables</li>
					<li><img src="img/icons/time.png"> Writing config to database</li>
				</ul>
			</div>
			
			<div id="stage_6" class="stage">
				<p>Congratulations! You now have a copy of Osimo installed that is ready for use.  The next step is to visit the admin panel to finish setting up the forum.  We highly recommend that you delete this setup folder from your server to prevent any security issues.  This can be done in the admin panel, or you may do it manually yourself.</p>
				
				<div class="action_buttons">
					<input type="button" value="CONTINUE TO ADMIN PANEL" class="action_button" onclick="toStage(6)" />
				</div>
			</div>
		</div>
		
		<div id="content_footer">
			<span id="stage_1_dot" class="active_stage" style="background-color: #a2a2a2;"></span>
			<span id="stage_2_dot"></span>
			<span id="stage_3_dot"></span>
			<span id="stage_4_dot"></span>
			<span id="stage_5_dot"></span>
			<span id="stage_6_dot"></span>
		</div>
	</div>
	
	<div id="footer_logo">osimo v2</div>
</body>
</html>