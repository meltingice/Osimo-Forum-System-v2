function OsimoJS(options){
	this.defaults = {
		'debug' : false,
		'postbox' : '#OsimoPostbox'
	};
	
	this.options = $.extend(this.defaults,options);
	this.editor = null;
	this.init();
}

OsimoJS.prototype.init = function(){
	if(this.isThread() || this.isForum()){
		this.curPage = this.getPageNum();
		if(this.getURLToken('page',true)){
			this.loadPage(this.curPage);
		}

		if("onhashchange" in window){
			window.onhashchange = this.executeHashChangeEvent;
			this.enableHashChangeEvent();
		}
	}
	
	this.debug = new OsimoDebug(this.options.debug);
}

OsimoJS.prototype.updatePagination = function(data,page){
	$(".OsimoPaginationWrap").html(''); //first we clear out the old pagination
	
	if(data.first){
		$(".OsimoPaginationWrap").append('<span class="OsimoPagination" onclick="osimo.loadPage(1)">First</span> ');
	}
	
	for(var i = data.start; i <= data.end; i++){
		if(i != data.start){ var before = ' '; }
		else{ var before = ''; }
		if(i != data.end){ var after = ' '; }
		else{ var after = ''; }
		
		var str = before+'<span class="OsimoPagination';
		if(i == page){ str += ' OsimoPaginationActivePage'; }
		str += '" onclick="osimo.loadPage('+i+')">'+i+'</span>'+after;
		
		$(".OsimoPaginationWrap").append(str);
	}
	
	if(data.last){
		$(".OsimoPaginationWrap").append(before+'<span class="OsimoPagination" onclick="osimo.loadPage('+data.num+')">Last</span> ');
	}
}

OsimoJS.prototype.updatePageHash = function(page){
	this.disableHashChangeEvent();
	var that = this;
	
	/* This was causing a strange race condition so
	 * I had to slow it down just barely to avoid it.
	 * 20ms extra shouldn't be be a noticible difference
	 * at all to the viewer.
	 */
	setTimeout(function(){
		window.location.hash = "#page="+page;
		setTimeout(function(){
			that.enableHashChangeEvent();
		},10);
	},10);
}

OsimoJS.prototype.getPageID = function(){
	return this.getURLToken('id');
}

OsimoJS.prototype.getPageNum = function(){
	if(window.location.hash == ''){
		return this.getURLToken('page');
	}
	else{
		return this.getURLToken('page',true);
	}
}

OsimoJS.prototype.getURLToken = function(tok,useHash){
	if(useHash){
		var url = window.location.hash.substring(1).split('&');
	}
	else{
		var url = window.location.search.substring(1).split('&');
	}
	
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

OsimoJS.prototype.isThread = function(){
	return (window.location.pathname.indexOf('thread.php') != -1);
}

OsimoJS.prototype.isForum = function(){
	return (window.location.pathname.indexOf('forum.php') != -1);
}

OsimoJS.prototype.enableHashChangeEvent = function(){
	this.hashChangeEnabled = true;
}

OsimoJS.prototype.disableHashChangeEvent = function(){
	this.hashChangeEnabled = false;
}

OsimoJS.prototype.executeHashChangeEvent = function(){
	if(!osimo.hashChangeEnabled){
		return true;
	}
	
	var newPage = osimo.getURLToken('page',true);
	if(newPage != false && newPage != this.curPage){
		osimo.loadPage(osimo.getPageNum());
	}
}