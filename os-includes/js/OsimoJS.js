var osimo = new OsimoJS({
	'debug' : true
});

function OsimoJS(options){
	this.defaults = {
		'debug' : false,
		'postbox' : '#OsimoPostbox'
	};
	
	this.options = $.extend(this.defaults,options);
	
	this.debug = new OsimoDebug(this.options.debug);
	this.editor = null;
}

OsimoJS.prototype.submitPost = function(){
	var content = $(this.options.postbox).osimoeditor('get');
	if(content == ''){
		this.debug.showError('You cannot submit a blank post.', 500, 80);
		return false;
	}
	
	var postData = {'content' : content, 'threadID' : this.getPageID()};
	var ajax = this.processPostData(postData,'post','submitPost');
	
	var that = this;
	$.ajax({
		type: 'POST',
		url: ajax.dest,
		data: ajax.postData,
		dataType:'json',
		success:function(data){
			if(data.error){
				that.debug.showError(data.error,500,80);
				return;
			}
			
			if(data.refresh){
				window.location.href = "thread.php?id="+data.location.thread+"&page="+data.location.page+"#post_"+data.location.post;
				if(that.getPageNum() == data.location.page){
					window.location.reload();
				}
			}
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

OsimoJS.prototype.getPageID = function(){
	return this.getURLToken('id');
}

OsimoJS.prototype.getPageNum = function(){
	return this.getURLToken('page');
}

OsimoJS.prototype.getURLToken = function(tok){
	var url = window.location.search.substring(1).split('&');
	var result = false;
	$.each(url,function(i,val){
		var query = val.split('=');
		if(query[0] == tok){
			 result = query[1];
			 return true;
		}
	});
	
	return result;
}