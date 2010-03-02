function OsimoDebug(enable){
	this.enabled = enable;
	this.phpDebugAnalyzed = false;
	this.init();
}

OsimoDebug.prototype.init = function(){
	var that = this;
	$(window).ready(function(){
		if(!that.enabled || $("#OsimoDebug").length == 0 || $("#OsimoDebug").html() == ''){
			that.hidePHPDebugger();
		}
		else{
			if(that.firebugEnabled()){
				that.showPHPDebugInfoFirebug();
			}
			else{
				that.showPHPDebugInfoHTML();
			}
		}
	});
}

OsimoDebug.prototype.showPHPDebugInfoFirebug = function(){
	var info = eval('('+$("#OsimoDebug").text()+')');
	if(info.msgs.length == 0 && info.errors.length == 0){
	    this.hidePHPDebugger();
	    return true;
	}
	
	if(this.firebugEnabled()){
		if(info.msgs.length > 0){
	    	console.log("OsimoDebug PHP: Messages");
	    	$.each(info.msgs,function(i,msg){
	    		console.info(decodeURIComponent(msg));
	    	});
	    }
	    if(info.errors.length > 0){
	    	console.log("OsimoDebug PHP: Errors");
	    	$.each(info.errors,function(i,error){
	    		console.error(decodeURIComponent(error));
	    	});
	    }
	}
	
	this.hidePHPDebugger();
}

OsimoDebug.prototype.showPHPDebugInfoHTML = function(){
	var info = eval('('+$("#OsimoDebug").text()+')');
	if(info.msgs.length == 0 && info.errors.length == 0){
		this.hidePHPDebugger();
		return true;
	}
	
	var HTML = '<div id="OsimoDebugWrap">';
	HTML += '<div id="OsimoDebugTab" onclick="osimo.debug.showPHPDebugInfo()">Show/Hide Debug</div>';
	HTML += '<div id="OsimoDebug">';
	HTML += "<h1>Osimo Debug Information</h1>";
	
	if(info.msgs.length > 0){
	    HTML += "<h2>Messages</h2><ul>";
	    $.each(info.msgs,function(i,msg){
	    	HTML += "<li><pre>"+decodeURIComponent(msg)+"</pre></li>";
	    });
	    HTML += '</ul>';
	}
	if(info.errors.length > 0){
	   HTML += "<h2>Errors</h2><ul>";
	    $.each(info.errors,function(i,error){
	    	HTML += "<li><pre>"+decodeURIComponent(error)+"</pre></li>";
	    });
	    HTML += '</ul>';
	}	
	
	HTML += '</div></div>';
	$("#OsimoDebug").remove();
	$('body').append(HTML);
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

OsimoDebug.prototype.hidePHPDebugger = function(){
	$("#OsimoDebug").remove();
}

OsimoDebug.prototype.firebugEnabled = function(){
	return (typeof console == 'object' && typeof console.log == 'function');
}