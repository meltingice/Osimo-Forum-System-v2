var curStage = 1;
function toStage(num) {
	if(num < 1) return;
	
	/* Slide the content left */
	var loc;
	if(num < curStage) {
		loc = parseFloat($("#stage_"+curStage).css('margin-left')) + 610;
	} else {
		loc = parseFloat($("#stage_"+curStage).css('margin-left')) - 610;
	}
	
	$("#stage_"+curStage).animate({'margin-left' : loc + "px"}, 800);
	
	/* Change the stage dots */
	$(".active_stage").animate({backgroundColor : '#d6d6d6'}, 400, function () { $(this).removeClass('active_stage'); });
	$("#stage_"+num+"_dot").animate({backgroundColor : '#a2a2a2'}, 400, function() { $(this).addClass('active_stage'); });
	
	curStage = num;
}