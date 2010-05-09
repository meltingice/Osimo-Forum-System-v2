var ajax_path = "ajax.php";

$(document).ready(function() {
	$(".review_data").live('click', function() {
		injectEditField($(this));
	});
	
	$(".review_edit").live('keydown', function(e) {
		if(e.keyCode == 13)
			saveReviewField($(this));
	});
});

var config = {
	database : {},
	cache : {}
};
var curStage = 1;
function toStage(num, skip) {
	if(num < 1) return;
	
	var valid = true;
	if(num == 3) { // Database settings
		valid = storeDatabaseSettings();
	} else if(num == 4) {
		if(!skip)
			valid = storeCacheSettings();
		else
			valid = true;
			
		populateReviewStage();
	}
	
	if(!valid) return;
	
	/* Slide the content left or right */
	var loc;
	if(num < curStage) {
		loc = 610;
	} else {
		loc = -610;
	}
	
	$("#stage_"+curStage).animate({'margin-left' : loc + "px"}, 800);
	$("#stage_"+num).animate({'margin-left' : 0}, 800);
	
	/* Change the stage dots */
	$(".active_stage").animate({backgroundColor : '#d6d6d6'}, 400, function () { $(this).removeClass('active_stage'); });
	$("#stage_"+num+"_dot").animate({backgroundColor : '#a2a2a2'}, 400, function() { $(this).addClass('active_stage'); });
	
	curStage = num;
	
	if(num == 5) {
		runOsimoInstallation();
	}
}

function storeDatabaseSettings() {
	config.database.name = $("#database_name").attr('value');
	config.database.username = $("#database_username").attr('value');
	config.database.password = $("#database_password").attr('value');
	config.database.host = $("#database_host").attr('value');

	var valid = true;
	$.each(config.database, function(i, val) {
		if(val == "") {
			$("#database_" + i).addClass('error_field').live('keyup', function() {
				if($(this).attr('value') != "")
					$(this).removeClass('error_field');
			});
			valid = false;
		}
	});
	
	return valid;
}

function storeCacheSettings() {
	config.cache.addresses = $("#cache_addresses").attr('value');
	config.cache.prefix = $("#cache_prefix").attr('value');
	
	if(config.cache.addresses == "")
		return false;
		
	return true;
}

function populateReviewStage() {
	$.each(config, function(section, obj) {
		$.each(obj, function(i, val) {
			if(section == 'database' && i == 'password') {
				val = formatAsPassword(val);
			}
			
			$("#review_"+section+"_"+i).html(val);
		});
	});
}

function formatAsPassword(val) {
	var length = val.length;
	val = "";
	for(var n = 0; n < length; n++) {
	    val += "&bull; ";
	}
	
	return val;
}

function injectEditField(ele) {
	var info = ele.attr('id').split("_");
	var value = eval("config."+info[1]+"."+info[2]+";");
	var html, type;
	
	if(info[1] == 'database' && info[2] == 'password')
		type = 'password';
	else
		type = 'text';
		
	if(!value)
		value = "";
		
	closeAllReviewFields();
		
	ele.html('<input class="review_edit" type="'+type+'" value="'+value+'" />');
	ele.find('input').focus();
}

function closeAllReviewFields() {
	$.each($('.review_edit'), function() {
		var info = $(this).parent().attr('id').split("_");
		var value = eval("config."+info[1]+"."+info[2]+";");
		if(!value) {
			value = "";
		} else {
			if(info[1] == 'database' && info[2] == 'password') {
				value = formatAsPassword(value);
			}
		}
			
		$(this).parent().html(value);
	});
}

function destroyInputField(ele, value) {
	ele.parent().html(value);
}

function saveReviewField(ele) {
	var info = ele.parent().attr('id').split("_");
	eval("config."+info[1]+"."+info[2]+" = ele.attr('value');");
	
	var value;
	if(info[1] == 'database' && info[2] == 'password')
		value = formatAsPassword(ele.attr('value'));
	else
		value = ele.attr('value');
		
	destroyInputField(ele, value);
}

function showError(field, error) {
	var fields = field.split(';');
	$.each(fields, function(i, val){
		$("#review_" + val).addClass('error_field').live('keyup', function() {
			if($(this).attr('value') != "")
				$(this).removeClass('error_field');
		});
	});
	
	toStage(4, true);
}

function createAdminAccount() {
	var fields = { username : $("#admin_username").attr('value'), password : $("#admin_password").attr('value'), email : $("#admin_email").attr('value') };
	var valid = true;
	
	$.each(fields, function(i, val) {
		if(val == "" || val.length < 3) {
			valid = false;
			$("#admin_" + i).addClass('error_field').live('keyup', function() {
				$(this).removeClass('error_field');
			});
		}
	});
	
	if(!valid) return;
	
	$.ajax({
		type : 'POST',
		url : ajax_path,
		data : {step : 6, user : fields},
		dataType : 'json',
		success : function(data) {
			if(data.error) {
				alert(data.error);
				return;
			}
			
			toStage(7);
		}
	});
}

/*
 * All of the installation functions are below and
 * are executed in sequential order by passing the 
 * next function to each step so that it can be 
 * executed when the ajax call finishes and is
 * evaluated successfully.
 */
function runOsimoInstallation() {
	sendConfigToServer(writeConfigToDisk);
}

function sendConfigToServer(next_step) {
	$.ajax({
		type : 'POST',
		url : ajax_path,
		data : {step : 1, config : config},
		dataType : 'json',
		success : function(data) {
			if(data.error) {
				// show error
				return;
			}
			
			setStepSuccessful(1);
			next_step(connectToDatabase);
		}
	});
}

function writeConfigToDisk(next_step) {
	$.ajax({
		type : 'POST',
		url : ajax_path,
		data : {step : 2},
		dataType : 'json',
		success : function(data) {
			if(data.error) {
				// show error
				return;
			}
			
			setStepSuccessful(2);
			next_step(createDatabaseTables);
		}
	});
}

function connectToDatabase(next_step) {
	$.ajax({
		type : 'POST',
		url : ajax_path,
		data : {step : 3},
		dataType : 'json',
		success : function(data) {
			if(data.error) {
				var field;
				if(data.type == 0) {
					field = 'database_host';
				} else if(data.type == 1) {
					field = 'database_username;database_password';
				} else if(data.type == 2) {
					field = 'database_name';
				}
				
				showError(field, data.error);
				return;
			}
			
			setStepSuccessful(3);
			next_step(writeConfigToDatabase);
		}
	});
}

function createDatabaseTables(next_step) {
	$.ajax({
		type : 'POST',
		url : ajax_path,
		data : {step : 4},
		dataType : 'json',
		success : function(data) {
			if(data.error) {
				// show error
				return;
			}
			
			setStepSuccessful(4);
			next_step(finalizeInstallation);
		}
	});
}

function writeConfigToDatabase(next_step) {
	$.ajax({
		type : 'POST',
		url : ajax_path,
		data : {step : 5},
		dataType : 'json',
		success : function(data) {
			if(data.error) {
				// show error
				return;
			}
			
			setStepSuccessful(5);
			next_step();
		}
	});
}

function finalizeInstallation() {
	setTimeout(function() {
		toStage(6);
	}, 500);
}

function setStepSuccessful(num) {
	$("#install_steps li:nth-child("+num+")>img").attr('src','img/icons/tick.png');
}