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
ajaxEnCours = 0;
var mySubmitController = Marionette.Object.extend( {

	initialize: function() {
		this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
	},

	actionSubmit: function( response ) {

		console.log("form submitted");
		console.log(response);

		// titles form
		if(response.data.form_id == idFormLogIn){
			$("#loadingSpinner").removeClass("hide");
			$(".hide_loading").addClass("hide");
			ajaxEnCours++;

			console.log('dedans');

			username = response.data.fields[idUsername].value;
			password = response.data.fields[idPassword].value;


			console.log(username);
			console.log(password);


			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxCheckLogIn',
						'password' : password,
						'username' : username

					})
					.done(function(canLog){ 
						console.log(canLog);
						if(canLog){
							$('#nf-field-'.concat(idUsername)).val('');
							$('#nf-field-'.concat(idPassword)).val('');
							redirect('dashboard-eleve', "Bienvenue sur SpamTonProf ! ");
						}else{
							showMessage("L'adresse mail ou le mot passe est incorrect");
						}
					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page puis réessayer. Contactez l'équipe si le problème persiste");
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							$("#loadingSpinner").addClass("hide");
							$(".hide_loading").removeClass("hide");
						}
					});
		}

	},

});


//début jquery
jQuery( document ).ready( function( $ ) {

	new mySubmitController();
	
	waitForEl(".hide_loading",function(){
		
		if(isLogged == "true"){
			$(".hide_loading").addClass("hide");
			$(".for_logged_user").removeClass("hide");
		}
		
	});

});



