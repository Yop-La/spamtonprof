jQuery(document).ready(function ($) {
	console.log('dedans');
	
	var node = $('<span class="countdown" >Viteeeee, le bac arrrive dans : <span id="clock"></span></span>');
	
	console.log('noeud');
	console.log($(node).find('#clock'))
	
	
	showMessage("");
	
	waitForEl("#band-message", function () {

		$("#band-message").append(node);
		
		$('#clock').countdown('2019/06/17 08:00:00', function(event) {
			  $(this).html(event.strftime('%D jours %H:%M:%S'));
		});

		
		console.log('dedans');
	});
		
	
});