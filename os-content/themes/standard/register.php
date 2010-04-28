<? get('theme')->include_header(); ?>

<div id="content">
	<div id="login_wrap">
		<div id="login_title">
			<h1>Register</h1>
		</div>
		<div id="login_desc">
			<h2>Enter your information</h2>
		</div>
		
		<? if(ErrorManager::is_error()): ?>
			<? $error = ErrorManager::get_error(); ?>
			<div id="error_wrap">
				<?=$error['msg']?>
			</div>
		<? endif; ?>
		
		<form id="login_form" action="<? get('theme')->register_action_url(); ?>" method="post">
			<p class="login_label">username <img src="<?=URL_THEME?>img/delete.png" id="username_valid" /></p>
			<input type="text" id="<? get('theme')->register_username_css_id(); ?>" name="<? get('theme')->register_username_name(); ?>" />
			<p class="login_label">email address <img src="<?=URL_THEME?>img/delete.png" id="email_valid" /></p>
			<input type="text" id="<? get('theme')->register_email_css_id(); ?>" name="<? get('theme')->register_email_name(); ?>" />
			<p class="login_label">password <img src="<?=URL_THEME?>img/delete.png" id="password_valid" /></p>
			<input type="password" id="<? get('theme')->register_password_css_id(); ?>" name="<? get('theme')->register_password_name(); ?>" />
			
			<div id="login_form_submit">
				<input type="submit" value="Register" />
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
	
	osimo.ui.validateField("#<? get('theme')->register_email_css_id(); ?>", osimo.emailIsValid, function(valid) {
		if(valid)
			$("#email_valid").attr('src','<?=URL_THEME?>img/accept.png');
		else
			$("#email_valid").attr('src','<?=URL_THEME?>img/delete.png');
	});
	
	osimo.ui.validateField("#<? get('theme')->register_password_css_id(); ?>", osimo.passwordIsValid, function(valid){
		if(valid)
			$("#password_valid").attr('src', '<?=URL_THEME?>img/accept.png');
		else
			$("#password_valid").attr('src', '<?=URL_THEME?>img/delete.png');
	});
</script>

<? get('theme')->include_footer(); ?>