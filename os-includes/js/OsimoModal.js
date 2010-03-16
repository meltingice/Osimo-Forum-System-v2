function OsimoModal(options){
	this.options = {
		'width' : 500,
		'height' : 400,
		'title' : '',
		'content' : '',
		'autoShow' : false,
		'modal' : true,
		'draggable' : false,
		'resizable' : false,
		'onresize' : function(modal){ },
		'onshow' : function(modal){ },
		'showClose' : true,
		'styles' : {}
	};
	
	this.set(options);
	this.content = this.options.content;
	this.title = this.options.title;
	this.height = this.options.height;
	this.width = this.options.width;
	
	if(this.options.autoShow){
		this.show();
	}
}

OsimoModal.prototype.set = function(options){
	this.options = $.extend(this.options,options);
	return this;
}

OsimoModal.prototype.setContent = function(content){
	this.content = content;
	return this;
}

OsimoModal.prototype.setTitle = function(title){
	this.title = title;
	return this;
}

OsimoModal.prototype.show = function(){
	this.divCheck();
	var coords = this.centerCoords();
	$("#OsimoModalContent").html(this.content);
	$("#OsimoModalTitle").html(this.title);
	$("#OsimoModalWrap").css({top:coords.y, left:coords.x, width:this.options.width, height:this.options.height}).fadeIn();
	$("#OsimoModalContent").css(this.options.styles);
	
	if(this.options.modal){
		$("#OsimoModalBk").fadeIn();
	}
	
	if(this.options.showClose){
		$("#OsimoModalClose").show();
	}
	else{
		$("#OsimoModalClose").hide();
	}
	
	if(this.options.draggable){
		$("#OsimoModalWrap").draggable({
			handle : "#OsimoModalHeader"
		});
		$("#OsimoModalHeader").css({cursor : 'move'});
	}
	else{
		var that = this;
		$(window).resize(function(){
			var coords = that.centerCoords();
			$("#OsimoModalWrap").css({top:coords.y,left:coords.x});
		});
	}
	
	if(this.options.resizable){
		var that = this;
		$("#OsimoModalWrap").resizable({resize:function(){ that.onresize()}});
	}
	
	this.options.onshow(this);
}

OsimoModal.prototype.onresize = function(){
	this.height = $("#OsimoModalWrap").height();
	this.width = $("#OsimoModalWrap").width();
	
	if($.isFunction(this.options.onresize)){
		this.options.onresize(this);
	}
}

OsimoModal.prototype.close = function(){
	$("#OsimoModalWrap").fadeOut();
	if($("#OsimoModalBk").is(":visible")){
		$("#OsimoModalBk").fadeOut();
	}
}

OsimoModal.prototype.divCheck = function(){
	if($("#OsimoModalWrap").length == 0){
		$('body').prepend('\
			<div id="OsimoModalBk" style="display:none"></div>\
			<div id="OsimoModalWrap" style="display:none">\
				<div id="OsimoModalHeader">\
					<div id="OsimoModalTitle"></div>\
					<div id="OsimoModalClose"></div>\
				</div>\
				<div id="OsimoModalContent"></div>\
			</div>\
		');
		
		var that = this;
		$("#OsimoModalClose").live('click',function(){
			that.close();
		});
	}
}

OsimoModal.prototype.centerCoords = function(){
	var window_width = $(window).width();
	var window_height = $(window).height();
	
	var x = (window_width / 2) - (this.options.width / 2);
	var y = (window_height / 2) - (this.options.height / 2);
	
	return {"x":x,"y":y};
}