var osimo = new OsimoJS({
	'debug' : true
});

function OsimoJS(options){
	this.defaults = {
		'debug' : false,
		'postbox' : '#OsimoPostbox'
	};
	
	this.options = $.extend({},this.defaults,options);
	
	this.debug = new OsimoDebug(this.options.debug);
	this.editor = null;
}

OsimoJS.prototype.submitPost = function(){
	var content = $(this.options.postbox).osimoeditor('get');
	if(content == ''){
		this.debug.showError('You cannot submit a blank post.', 500, 80);
		return false;
	}
	
	var postData = {'content' : content};
	var ajax = this.processPostData(postData,'post','submitPost');
	
	$.ajax({
		type: 'POST',
		url: ajax.dest,
		data: ajax.postData,
		dataType:'json',
		success:function(data){
			new OsimoModal({width: 500, height : 100, styles : {'text-align' : 'center'}}).setTitle('Post Data').setContent(data.data).show();
		}
	});
}

OsimoJS.prototype.processPostData = function(postData, dest, trigger){
	postData.ajax_trigger = trigger;
	return {
		dest : 'os-includes/ajax/'+dest+'.ajax.php',
		postData : postData
	}
}