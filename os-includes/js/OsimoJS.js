var osimo = new OsimoJS({
	'debug' : true
});

function OsimoJS(options){
	this.defaults = {
		'debug' : false
	};
	
	this.options = $.extend({},this.defaults,options);
	
	this.debug = new OsimoDebug(this.options.debug);
	this.editor = null;
}