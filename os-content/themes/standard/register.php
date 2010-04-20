<? get('theme')->include_header(); ?>

<div id="content">
	<div id="login_wrap">
		<div id="login_title">
			<h1>Register</h1>
		</div>
		<div id="login_desc">
			<h2>Enter your information</h2>
		</div>
		
		<form id="login_form" action="<? get('theme')->register_action_url(); ?>" method="post">
			<p class="login_label">username <img src="<?=URL_THEME?>img/delete.png" id="username_valid" /></p>
			<input type="text" id="<? get('theme')->register_username_css_id(); ?>" name="<? get('theme')->register_username_name(); ?>" />
			<p class="login_label">password</p>
			<input type="password" id="<? get('theme')->register_password_css_id(); ?>" name="<? get('theme')->register_password_name(); ?>" />
			
			<div id="login_form_submit">
				<input type="submit" value="Login" />
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">
	osimo.ui.validateField("#<? get('theme')->register_username_css_id(); ?>", osimo.usernameIsTaken, function(valid) {
		if(!valid)
			$("#username_valid").attr('src','<?=URL_THEME?>img/accept.png');
		else
			$("#username_valid").attr('src','<?=URL_THEME?>img/delete.png');
	});
</script>

<? get('theme')->include_footer(); ?>