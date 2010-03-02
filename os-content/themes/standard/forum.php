<?
	get('data')->load_forum_list();
	get('data')->load_thread_list();
?>

<? get('theme')->include_header(); ?>

<div id="content">
	<div id="breadcrumb_trail">
		<? get('data')->breadcrumb_trail(' &raquo; '); ?>
	</div>
	<? /* First output forums */ ?>
	<? if(get('data')->are_categories()): while(get('data')->has_categories()): if(get('data')->are_forums()): ?>
		
		<div class="category_title">
			<h1><? get('data')->the_category('title'); ?></h1>
		</div>
		<table class="forum_list">
			<tr class="forum_list_head">
				<td class="forum_list_desc">Forum</td>
				<td class="forum_list_stats">Stats</td>
				<td class="forum_list_info">Information</td>
			</tr>
			
			<? $alt = false; while(get('data')->has_forums()): ?>
			<tr class="forum_list_item <? if($alt){ echo "alt_forum"; $alt = false; } else { $alt = true; } ?>">
				<td class="forum_list_desc">
					<h1><? get('data')->the_forum_link(); ?></h1>
					<p><? get('data')->the_forum('description'); ?></p>
				</td>
				<td class="forum_list_stats">
					<? get('data')->the_forum('threads'); ?> Threads<br />
					<? get('data')->the_forum('posts'); ?> Posts
				</td>
				<td class="forum_list_info">
					<? get('data')->the_forum('last_post_time'); ?><br />
					<span style="color:#000000">In:</span> <a href="<? get('data')->the_forum('last_thread_link'); ?>"><? get('data')->the_forum('last_thread_title'); ?></a>
				</td>
			</tr>
			<? endwhile; ?>
		</table>
	
	<? endif; endwhile; endif; ?>
	
	<? /* Now lets output threads */ ?>
	<? if(get('data')->are_threads()): ?>		
		<div class="category_title">
			<h1>Threads</h1>
		</div>
		<table class="thread_list">
			<tr class="thread_list_head">
				<td class="thread_list_desc">Thread</td>
				<td class="thread_list_started_by">Started By</td>
				<td class="thread_list_stats">Stats</td>
				<td class="thread_list_info">Information</td>
			</tr>
			
			<? $alt = false; while(get('data')->has_threads()): ?>
			<tr class="forum_list_item <? if($alt){ echo "alt_forum"; $alt = false; } else { $alt = true; } ?>">
				<td class="forum_list_desc">
					<h1><? get('data')->the_thread_link(); ?></h1>
					<p><? get('data')->the_thread('description'); ?></p>
				</td>
				<td class="thread_list_started_by">
					<? get('data')->the_thread('original_poster_link'); ?>
				</td>
				<td class="forum_list_stats">
					<? get('data')->the_thread('views'); ?> Views<br />
					<? get('data')->the_thread('posts'); ?> Posts
				</td>
				<td class="forum_list_info">
					<? get('data')->the_thread('last_post_time'); ?><br />
					<span style="color:#000000">In:</span> <a href="<? get('data')->the_thread('last_thread_link'); ?>"><? get('data')->the_thread('last_thread_title'); ?></a>
				</td>
			</tr>
			<? endwhile; ?>
		</table>
		</div>
	<? endif; ?>
	
</div>

<? get('theme')->include_footer(); ?>