function OsimoUI(osimo){
	this.osimo = osimo;
}

OsimoUI.prototype.scrollTo = function(id){
	var offset = $(id).offset();
	$('html, body').animate({scrollTop:offset.top}, 'slow'); 
}

OsimoUI.prototype.createThreadModal = function(){
	var modal = new OsimoModal({
		title : 'Create a Thread',
		content : '<textarea id="OsimoCreateThread"></textarea>',
		autoShow : true,
		width: 500, 
		height : 400,
		modal : false,
		draggable : true,
		resizable: true, 
		onresize: function(modal){
			$("#OsimoCreateThread_editbox").css({height : modal.height - 120});
		}
	});
	
	$("#OsimoCreateThread").osimoeditor({
		'width' : '100%',
		'editorHeight' : '280px'
	});
}