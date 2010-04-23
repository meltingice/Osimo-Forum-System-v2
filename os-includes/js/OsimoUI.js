function OsimoUI(osimo){
	this.osimo = osimo;
}

OsimoUI.prototype.scrollTo = function(id){
	var offset = $(id).offset();
	$('html, body').animate({scrollTop:offset.top}, 'slow'); 
}

OsimoUI.prototype.createThreadModal = function(){
	var ui = this;
	var modal = new OsimoModal({
		title : 'Create a Thread',
		content : ui.HTML.createThread,
		autoShow : true,
		width: 500, 
		height : 400,
		modal : false,
		draggable : true,
		resizable: true, 
		onresize: function(modal){
			$("#OsimoCreateThread_editbox").css({height : modal.height - 230});
		},
		onshow: function(modal){
			$("#OsimoCreateThread").osimoeditor({
				'width' : '100%',
				'editorHeight' : '170px'
			});
		}
	});
}

/*
 * Right now this assumes you are using spaces to separate
 * the pagination. Need to make a good way around this and 
 * allow for more options.
 */
OsimoUI.prototype.updatePagination = function(data,page){
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

OsimoUI.prototype.errorElement = function(ele,event,condition){
	var element = $(ele);
	if(element.length == 0)
		return;
		
	if(condition(this.osimo,element) == true){
		if(!$(ele).hasClass('OsimoErrorElement')){
			$(ele).addClass("OsimoErrorElement");
			var that = this;
			$(ele).live(event,function(){
				that.errorElement(ele,event,condition);
			});
		}
	}
	else{
		$(ele).removeClass('OsimoErrorElement');
		$(ele).die(event);
	}
}

OsimoUI.prototype.removeErrorElement = function(ele){
	$(ele).removeClass(".OsimoErrorElement");
}

OsimoUI.prototype.validateField = function(ele, condition, callback) {
	if(!$.isFunction(condition))
		return;

	var element = $(ele);
	var that = this;
	
	if(!element.data('OsimoValidate')) {
		element.live("keyup", function() {
			that.validateField(ele, condition, callback);
		});
		element.data('OsimoValidate', true);
		element.data('OsimoValidateLast', element.attr('value'));
	}
	else if(element.data('OsimoValidateLast') == element.attr('value')) {
		return;
	} else {
		element.data('OsimoValidateLast', element.attr('value'));
	}
	
	if($.isFunction(callback)){
		condition(this.osimo, element, callback);
	} else {
		var callback = function(valid) {
			if(valid) {
				element.addClass('OsimoValidElement');
				element.removeClass('OsimoErrorElement');
			} else {
				element.addClass('OsimoErrorElement');
				element.removeClass('OsimoValidElement');
			}
		};
		
		condition(this.osimo, element, callback);
	}
}

OsimoUI.prototype.HTML = {
	createThread : '\
	<table class="OsimoModalInputTable">\
		<tr>\
			<td class="OsimoModalInput" style="text-align:right;width:115px;">Thread Title</td>\
			<td><input type="text" id="OsimoCreateThreadTitle" /></td>\
		</tr>\
		<tr>\
			<td class="OsimoModalInput" style="text-align:right;width:115px;">Thread Description</td>\
			<td><input type="text" id="OsimoCreateThreadDesc" /></td>\
		</tr>\
	</table>\
	<textarea id="OsimoCreateThread"></textarea>\
	<div id="OsimoCreateThreadActions">\
		<input type="button" value="Create Thread" onclick="osimo.createThread()" />\
		<input type="button" value="Preview Post" onclick="osimo.previewNewThread()" />\
	</div>\
	'
};