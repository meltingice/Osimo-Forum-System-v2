function OsimoDebug(enable){
	this.enabled = enable;
	this.init();
}

OsimoDebug.prototype.init = function(){
	var that = this;
	$(window).ready(function(){
		if(!that.enabled || $("#OsimoDebug").length == 0 || $("#OsimoDebug").html() == ''){
			$("#OsimoDebugWrap").hide();
		}
	});
}

OsimoDebug.prototype.showPHPDebugInfo = function(){
	$("#OsimoDebug").toggle('slide',{direction:'down'},400);
	if($("#OsimoDebugTab").css('bottom') == '302px'){
		$("#OsimoDebugTab").animate({bottom:"0px"},400);
		$('body').animate({'padding-bottom':'0px'},400);
	}
	else{
		$("#OsimoDebugTab").animate({bottom:"302px"},400);
		$('body').animate({'padding-bottom':'302px'},400);
	}
	
}