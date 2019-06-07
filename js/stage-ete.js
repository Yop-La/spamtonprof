jQuery(document).ready(function ($) {
	console.log('dedans');

	console.log('dedans');
	var node = $('<span class="countdown" >Les inscriptions au premier stage ferment dans : <span id="clock"></span>  -- Nombre de places limités -- </span> ');
	
	console.log('noeud');
	console.log($(node).find('#clock'))
	
	
	showMessage("");
	
	waitForEl("#band-message", function () {

		$("#band-message").append(node);
		
		$('#clock').countdown('2019/06/28 00:00:00', function(event) {
			  $(this).html(event.strftime('%D jours %H:%M:%S'));
		});

		
	});
		
	
});