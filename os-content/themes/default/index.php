<? get('data')->load_forum_list('parent_forum=-1'); // need to make this even more automated ?>

<? get('theme')->include_header(); ?>

<div id="content">
	
	<? /* Behold... THE LOOP! Look familiar? If you've ever themed Wordpress then it should :) */ ?>
	<? if(get('data')->are_categories()): while(get('data')->has_categories()): if(get('data')->are_forums()): ?>

		<h1 class="forum_title"><? get('data')->the_category('title'); ?></h1>
		<div class="forum_list">
			<div class="forum_list_head">
				<div class="forum_list_desc">Description</div>
				<div class="forum_list_posts">Posts</div>
				<div class="forum_list_views">Views</div>
				<div class="forum_list_info">Information</div>
			</div>
			
			<? while(get('data')->has_forums()): ?>
			<div class="forum_list_wrap">
				<div class="forum_list_desc">
					<h1><? get('data')->the_forum('title'); ?></h1>
					<p><? get('data')->the_forum('description'); ?></p>
				</div>
				<div class="forum_list_posts"><? get('data')->the_forum('posts'); ?></div>
				<div class="forum_list_views"><? get('data')->the_forum('views'); ?></div>
				<div class="forum_list_info">
				
				</div>
			</div>
			<? endwhile; ?>
		</div>
	
	<? endif; endwhile; endif; ?>
	
</div>

<? get('theme')->include_footer(); ?>