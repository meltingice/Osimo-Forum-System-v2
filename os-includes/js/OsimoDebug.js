function OsimoDebug(enable){
	this.enabled = enable;
	this.init();
}

OsimoDebug.prototype.init = function(){
	var that = this;
	$(window).ready(function(){
		if(that.enabled && $("#OsimoDebug").length > 0 && $("#OsimoDebug").html() != ''){
			that.showPHPDebugInfo();
		}
	});
}

OsimoDebug.prototype.showPHPDebugInfo = function(){
	$("#OsimoDebug").fadeIn();
}