<? get('theme')->include_header(); ?>

<div id="content">
	<div id="login_wrap">
		<div id="login_title">
			<h1>Sign In</h1>
		</div>
		<div id="login_desc">
			<h2>Enter your username and password</h2>
		</div>
		
		<? if(ErrorManager::is_error()): ?>
			<? $error = ErrorManager::get_error(); ?>
			<div id="error_wrap">
				<?=$error['msg']?>
			</div>
		<? endif; ?>
		
		<form id="login_form" action="<? get('theme')->login_action_url(); ?>" method="post">
			<p class="login_label">username</p>
			<input type="text" id="<? get('theme')->login_username_css_id(); ?>" name="<? get('theme')->login_username_name(); ?>" />
			<p class="login_label">password</p>
			<input type="password" id="<? get('theme')->login_password_css_id(); ?>" name="<? get('theme')->login_password_name(); ?>" />
			
			<div id="login_form_submit">
				<input type="submit" value="Login" />
			</div>
		</form>
	</div>
</div>

<? get('theme')->include_footer(); ?>