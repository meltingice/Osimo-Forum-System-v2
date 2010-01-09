<? get('theme')->include_header(); ?>

<div id="content">
	<form id="login_form" action="<? get('theme')->login_action_url(); ?>" method="post">
		<p class="login_label">username</p>
		<input type="text" id="<? get('theme')->login_username_css_id(); ?>" name="<? get('theme')->login_username_name(); ?>" />
		<p class="login_label">password</p>
		<input type="password" id="<? get('theme')->login_password_css_id(); ?>" name="<? get('theme')->login_password_name(); ?>" />
		<br />
		<input type="submit" value="Login" />
	</form>
</div>

<? get('theme')->include_footer(); ?>