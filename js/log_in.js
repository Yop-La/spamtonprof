/*
 * script chargé sur la page de log in
 */


/*
 * 
 *  pour vérifier la bonne saisie de identifiant - mot de passe et avertir l'utilisateur
 * 
 */

idFormLogIn = "65";

idUsername = "956";
idPassword = "957";


if(!domain.includes("localhost")){

	idFormLogIn = "64";

	idUsername = "916";
	idPassword = "917";

	
	
}

ajaxEnCours = 0;
wpUser = null;
var mySubmitController = Marionette.Object.extend( {

	
	
	initialize: function() {
		this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
		console.log("submit controler loaded");
	},

	actionSubmit: function( response ) {

		console.log("form submitted");
		console.log(response);

		// titles form
		if(response.data.form_id == idFormLogIn){
			jQuery("#loadingSpinner").removeClass("hide");
			jQuery(".hide_loading").addClass("hide");
			ajaxEnCours++;

			console.log('dedans');

			username = response.data.fields[idUsername].value;
			password = response.data.fields[idPassword].value;


			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxCheckLogIn',
						'password' : password,
						'username' : username

					})
					.done(function(retour){ 

						if(retour){
							jQuery('#nf-field-'.concat(idUsername)).val('');
							jQuery('#nf-field-'.concat(idPassword)).val('');

							if('client' in retour){
								redirectTo(retour.redirection, "Bienvenue sur SpamTonProf ! ");	
							}else if('prof' in retour){
								redirectTo(retour.redirection, retour.message);	
							}else{
								redirectTo('', "Bienvenue sur SpamTonProf ! ");	
							}
							
						}else{
							showMessage("L'adresse mail ou le mot passe est incorrect");
							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".hide_loading").removeClass("hide");
						}
					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page puis réessayer. Contactez l'équipe si le problème persiste");
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".hide_loading").removeClass("hide");
						}
					});
		}

	},

});


//début jquery
jQuery( document ).ready( function( $ ) {

	console.log("jquery begin");
	
	showMessage("Si c'est votre première connexion, cliquez sur mot de passe oublié et saisissez l'adresse mail utilisée à l'inscription.");
	
	new mySubmitController();
	
	waitForEl(".hide_loading",function(){
		
		if(isLogged == "true"){
			jQuery(".hide_loading").addClass("hide");
			jQuery(".for_logged_user").removeClass("hide");
		}
		
	});

});



