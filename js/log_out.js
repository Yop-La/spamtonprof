/*
 * script chargé sur toutes les pages
 */


//début jquery
jQuery( document ).ready( function( $ ) {

	waitForEl('#deco-button', function() {
		$('#deco-button').click(function(){

				jQuery.post(
						ajaxurl,
						{
							'action' : 'ajaxLogOut',
						})
						.done(function(textCat){

							redirect(currentSlug, "Vous êtes bien déconnecté, à bientôt sur SpamTonProf ! ");
							
						})
						.fail(function(err){

							redirect(currentSlug, "Vous êtes bien déconnecté, à bientôt sur SpamTonProf ! ");
							return;
						});
		
		});
	});




});



