<? get('data')->load_post_list(); ?>

<? get('theme')->include_header(); ?>

<div id="content">
	<? if(get('data')->are_posts()): while(get('data')->has_posts()): ?>
	<div class="forum_post">
		<div class="forum_post_user">
			<img class="user_avatar" src="<? get('data')->the_avatar_url(); ?>" />
			<p><? get('data')->the_user_profile_link(); ?></p>
		</div>
		<div id="<? get('data')->the_post_css_id() ?>" class="forum_post_content">
			<? get('data')->the_formatted_post(); ?>
		</div>
	</div>
	<? endwhile; endif; ?>
	
	<? get('theme')->include_postbox(); ?>
</div>

<? get('theme')->include_footer(); ?>