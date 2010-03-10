<div class="thread_post">
	<div class="thread_post_wrap">
	    <div class="thread_post_header">
	    	<p class="post_author"><img src="<?=URL_THEME?>img/user_green.png" /> <? get('data')->the_post('poster_link'); ?></p>
	    	<p class="post_time">Posted <? get('data')->the_post('post_time'); ?></p>
	    </div>
	    <div class="thread_post_info">
	    	<div class="thread_post_avatar"><img src="<? get('data')->the_avatar_url(); ?>" /></div>
	    	<ul class="thread_user_info">
	    		<li><span class="tui_l">Posts:</span> <span class="tui_r"><? get('data')->post_user('posts'); ?></span></li>
	    		<li><span class="tui_l">Joined:</span> <span class="tui_r"><? get('data')->post_user('time_joined'); ?></span></li>
	    	</ul>
	    </div>
	    <div id="<? get('data')->the_post_css_id() ?>" class="thread_post_content_wrap">
	    	<div class="thread_post_content"><? get('data')->the_formatted_post(); ?></div>
	    </div>
	</div>
	<div class="thread_post_footer">
	
	</div>
</div>