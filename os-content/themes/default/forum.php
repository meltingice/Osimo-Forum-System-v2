<?
	get('data')->load_forum_list();
	get('data')->load_thread_list();
?>

<? get('theme')->include_header(); ?>

<div id="content">
	
	<? /* First output forums */ ?>
	<? if(get('data')->are_categories()): while(get('data')->has_categories()): if(get('data')->are_forums()): ?>

		<h1 class="forum_title"><? get('data')->the_category('title'); ?></h1>
		<div class="forum_list">
			<div class="forum_list_head">
				<div class="forum_list_desc">Description</div>
				<div class="forum_list_posts">Threads</div>
				<div class="forum_list_views">Views</div>
				<div class="forum_list_info">Information</div>
			</div>
			
			<? while(get('data')->has_forums()): ?>
			<div class="forum_list_wrap">
				<div class="forum_list_desc">
					<h1><? get('data')->the_forum_link(); ?></h1>
					<p><? get('data')->the_forum('description'); ?></p>
				</div>
				<div class="forum_list_posts"><? get('data')->the_forum('threads'); ?></div>
				<div class="forum_list_views"><? get('data')->the_forum('views'); ?></div>
				<div class="forum_list_info">
				
				</div>
			</div>
			<? endwhile; ?>
		</div>
	
	<? endif; endwhile; endif; ?>
	
	<? /* Now lets output threads */ ?>
	<? if(get('data')->are_threads()): ?>
		<h1 class="forum_title">Threads</h1>
		<div class="forum_list">
			<div class="forum_list_head">
				<div class="forum_list_desc">Description</div>
				<div class="forum_list_posts">Posts</div>
				<div class="forum_list_views">Views</div>
				<div class="forum_list_info">Information</div>
			</div>
		
			<? while(get('data')->has_threads()): ?>
			<div class="forum_list_wrap">
				<div class="forum_list_desc">
					<h1><? get('data')->the_thread_link(); ?></h1>
					<p><? get('data')->the_thread('description'); ?></p>
				</div>
				<div class="forum_list_posts"><? get('data')->the_thread('posts'); ?></div>
				<div class="forum_list_views"><? get('data')->the_thread('views'); ?></div>
				<div class="forum_list_info">
					
				</div>
			</div>
			<? endwhile; ?>
		</div>
	<? endif; ?>
	
</div>

<? get('theme')->include_footer(); ?>