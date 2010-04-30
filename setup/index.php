<html lang="en">
<head>
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
				
				<p style="margin-top: 20px;">Please be sure to have the following details ready:</p>
				<ul>
					<li><span>Database address</span></li>
					<li><span>Database user name &amp; password</span></li>
					<li><span>Database name</span></li>
					<li><span>Memcached server(s) <span style="color: #888888 !important">[optional]</span></span></li>
				</ul>
				
				<input type="button" value="GET STARTED" class="action_button" onclick="toStage(2)" />
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
				
				<input type="button" value="SAVE & CONTINUE" class="action_button" onclick="toStage(3)" />
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
				
				<input type="button" value="SKIP THIS STEP" class="action_button" onclick="toStage(4)" />
				<input type="button" value="SAVE & CONTINUE" class="action_button" onclick="toStage(4)" />
			</div>
		</div>
		
		<div id="content_footer">
			<span id="stage_1_dot" class="active_stage" style="background-color: #a2a2a2;"></span>
			<span id="stage_2_dot"></span>
			<span id="stage_3_dot"></span>
			<span id="stage_4_dot"></span>
		</div>
	</div>
	
	<div id="footer_logo">osimo v2</div>
</body>
</html>