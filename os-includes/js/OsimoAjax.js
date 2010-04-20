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
					that.ui.updatePagination(data.pagination,data.location.page);
					that.updatePageHash(data.location.page);
				}
			}
		}
	});
}

OsimoJS.prototype.createThread = function(){
	var title = this.trim($("#OsimoCreateThreadTitle").attr('value'));
	var desc = $("#OsimoCreateThreadDesc").attr('value');
	var post = this.trim($("#OsimoCreateThread").osimoeditor('get'));
	
	if(title == ''){
		this.ui.errorElement("#OsimoCreateThreadTitle",'keyup',function(osimo,element){
			if(osimo.trim(element.attr('value')).length == 0){
				return true;
			}
			
			return false;
		});
		
		return;
	}
	else if(post == ''){
		this.ui.errorElement("#OsimoCreateThread_editbox",'keyup',function(osimo,element){
			if(osimo.trim(element.attr('value')).length == 0){
				return true;
			}
			
			return false;
		});
		
		return;
	}
	
	var ajax = this.processPostData({
		'forum' : this.getPageID(),
		'title' : $("#OsimoCreateThreadTitle").attr('value'), 
		'desc' : $("#OsimoCreateThreadDesc").attr('value'), 
		'content' : $("#OsimoCreateThread").osimoeditor('get')
	},
	'thread','createThread');
	
	var that = this;
	$.ajax({
		type: 'POST',
		url: ajax.dest,
		data: ajax.postData,
		dataType: 'json',
		success: function(data) {
			if(data.error) {
				that.debug.showError(data.error, 500, 80);
				return;
			}
			
			window.location.href = "thread.php?id=" + data.thread_id;
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
			
			that.ui.updatePagination(data.pagination,page);
			that.updatePageHash(page);
		}
	});
}

OsimoJS.prototype.usernameIsTaken = function(element) {
	return this.usernameIsTaken(this.osimo, element);
}

OsimoJS.prototype.usernameIsTaken = function(osimo, element) {
	if(osimo.usernameTaken == null) {
		osimo.usernameTaken = false;
	}
	
	if(osimo.usernameTimeout) {
		clearTimeout(osimo.usernameTimeout);
	}
	
	osimo.usernameTimeout = setTimeout(function() {
		var ajax = osimo.processPostData({'username' : element.attr('value')}, 'user', 'checkUsernameAvailable');
		
		$.ajax({
			type: 'POST',
			url: ajax.dest,
			data: ajax.postData,
			dataType: 'json',
			success: function(data) {
				if(data.error) {
					osimo.debug.showError(data.error,500,80);
					return;
				}
				
				osimo.usernameTaken = Boolean(data.status);
			}
		});
		
	}, 400);
	
	return osimo.usernameTaken;
}