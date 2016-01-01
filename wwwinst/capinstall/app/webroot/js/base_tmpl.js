$(function(){ 
	// side bar
	$('.bs-docs-sidenav').affix({
		offset: {
			top: 0,
			bottom: 70
		}
	});

	AlertShow();
});

function AlertOff() {
    $('#message_box').fadeOut(1000);
}

function AlertShow() {
	//  message alert
	$('#message_box').click(function() {
		$(this).fadeOut(500);
	});
	if ($('#alert').text().length) {
		$('#message_box').fadeIn(600);
		setTimeout('AlertOff()', 5000);
	}
}
