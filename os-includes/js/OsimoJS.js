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
	this.ui = new OsimoUI(this);
	this.debug = new OsimoDebug(this.options.debug);
	
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
	var split = window.location.pathname.split('/');
	return (split[split.length-1] == 'thread.php');
}

OsimoJS.prototype.isForum = function(){
	var split = window.location.pathname.split('/');
	return (split[split.length-1] == 'forum.php');
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

OsimoJS.prototype.trim = function(str){
	return str.replace(/^\s*/, "").replace(/\s*$/, "").replace(/\s{2,}/, " ");
}

OsimoJS.prototype.emailIsValid = function(osimo, element, callback) {
	var email = element.attr('value');
	var pattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
	callback(pattern.test(email));
}

OsimoJS.prototype.passwordIsValid = function(osimo, element, callback) {
	var password = element.attr('value');
	callback(password.length >= 3);
}