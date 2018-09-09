/*
 * script chargé sur toutes les pages
 */


//début jquery
jQuery( document ).ready( function( $ ) {

	waitForEl('.deco-button', function() {
		console.log('deco button exist');
		jQuery('.deco-button').click(function(){

				jQuery.post(
						ajaxurl,
						{
							'action' : 'ajaxLogOut',
						})
						.done(function(data){
							console.log('done');
							console.log(data);
							redirectTo('', "Vous êtes bien déconnecté, à bientôt sur SpamTonProf ! ");
							
						})
						.fail(function(err){
							console.log(fail);
							redirectTo('', "Impossible de vous déconnecter. Veuillez réessayer et contacter l'équipe si le problème persiste");
							return;
						});
		
		});
	});




});



