<? if(get('user')->is_logged_in()): ?>
<div id="postbox_wrap">
	<div id="postbox_header"><h1>Reply to Thread</h1></div>
	<div id="postbox_editor">
	<? get('theme')->osimo_editor(
		array(
			'width'=>'98%',
			'editorHeight'=>'230px',
			'editorWidth'=>'100%',
			'styles'=>array(
				'margin'=>'2px auto'
			),
			'editorStyles'=>array(
				'color'=>'#333333',
				'font-family'=>'Arial, Helvetica, Verdana, sans-serif'
			)
		));
	?>
	</div>
	<div id="postbox_controls">
		<input type="button" value="Submit Post" id="postbox_submit" onclick="<? get('theme')->post_submit() ?>" />
		<input type="button" value="Preview Post" id="postbox_preview" onclick="<? get('theme')->post_preview() ?>" />
	</div>
</div>
<? endif; ?>