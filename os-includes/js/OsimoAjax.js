OsimoJS.prototype.processPostData = function(postData, dest, trigger){
	postData.ajax_trigger = trigger;
	return {
		dest : 'os-includes/ajax/'+dest+'.ajax.php',
		postData : postData
	}
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
		success: function(data){
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
			else{
				$("#OsimoPosts").html(data.html);
				if(data.location.page != that.getPageNum()){
					that.updatePagination(data.pagination,data.location.page);
					that.updatePageHash(data.location.page);
				}
			}
		}
	});
}

OsimoJS.prototype.loadPage = function(page){
	if(this.isThread()){
		var ajax = this.processPostData({'thread' : this.getPageID(), 'page' : page},'thread','loadPage');
	}
	else if(this.isForum()){
		var ajax = this.processPostData({'forum' : this.getPageID(), 'page' : page},'forum','loadPage');
	}
	
	var that = this;
	$.ajax({
		type: 'POST',
		url: ajax.dest,
		data: ajax.postData,
		dataType: 'json',
		success: function(data){
			if(data.error){
				that.debug.showError(data.error,500,80);
				return;
			}
			
			if(that.isThread()){
				$("#OsimoPosts").html(data.html);
			}
			else if(that.isForum()){
				$("#OsimoThreads").html(data.html);
			}
			
			that.updatePagination(data.pagination,page);
			that.updatePageHash(page);
		}
	});
}