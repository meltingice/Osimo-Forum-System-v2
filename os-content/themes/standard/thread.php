<?
	get('theme')->add_stylesheet('css/bbcode_styles.css');
	get('theme')->include_header();
?>

<div id="content">
	<div id="breadcrumb_trail">
		<? get('data')->breadcrumb_trail(' &raquo; '); ?>
	</div>
	
	<div id="thread_info">
		<div class="post_page_list">
			(<? get('theme')->num_pages(); ?> pages)
			<? get('theme')->preset_pagination(); ?>
		</div>	
		
		<div class="thread_actions">
			<? if(get('user')->is_logged_in()): ?>
			<div class="fancy_button" onclick="osimo.ui.scrollTo('#postbox_wrap')">
				<img src="<?=URL_THEME?>img/page_add.png" alt="new thread" /> Reply to thread
			</div>
			<? else: ?>
			<div class="fancy_button_disabled">
				You cannot reply to this thread
			</div>
			<? endif; ?>
		</div>
	</div>	
	
	<div class="thread_title">
		<h1><? get('data')->thread_title(); ?></h1>
		<h2><? get('data')->thread_description(); ?></h2>
	</div>

	<? get('data')->do_standard_loop(); ?>
	
	<? get('theme')->include_postbox(); ?>
</div>

<? get('theme')->include_footer(); ?>