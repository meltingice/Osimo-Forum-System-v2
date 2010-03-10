<?
	get('data')->load_post_list();
	get('theme')->add_stylesheet('css/bbcode_styles.css');
	get('theme')->include_header();
?>

<div id="content">
	<div id="breadcrumb_trail">
		<? get('data')->breadcrumb_trail(' &raquo; '); ?>
	</div>
	<div class="thread_title">
		<h1><? get('data')->thread_title(); ?></h1>
		<h2><? get('data')->thread_description(); ?></h2>
	</div>

	<? get('data')->do_standard_loop(); ?>
	
	<? get('theme')->include_postbox(); ?>
</div>

<? get('theme')->include_footer(); ?>