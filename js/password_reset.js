/*
 * script chargé sur la page dont le slug est reset-password
 */


jQuery( document ).ready( function( $ ) {




	waitForEl("#somfrp_user_info", function() {
		jQuery("#somfrp_user_info").attr("placeholder", "Adresse email");

	});

//	waitForEl(".som-password-sent-message", function() {
//		jQuery(".som-password-sent-message > span:nth-child(1)").text("Cet adresse mail n'est pas reconnu");
//	});

});