/*
 * script chargé sur la page dont le slug est inscription-prof
 */

//id des champs du formulaire

idForm = "68";
idFormContent= "#nf-form-".concat(idForm, "-cont");

idPrenom = "978";
idNom = "979";
idEmail = "977";
idMobile = "982";
idDob = "993";
idSexe = "1044";

if(!domain.includes("localhost")){

	idForm = "65";
	idFormContent= "#nf-form-".concat(idForm, "-cont");

	idPrenom = "920";
	idNom = "921";
	idEmail = "922";
	idMobile = "923";
	idDob = "924";
	idSexe = "925";

}


ajaxEnCours = 0;

/*
 * debut : gérer la soumission du formulaire 68 : le formulaire d'inscription des profs
 * 
 */

var mySubmitController = Marionette.Object.extend( {

	initialize: function() {
		this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
	},

	actionSubmit: function( response ) {
		console.log("form submitted");
		console.log(response);

		// titles form
		if(response.data.form_id == idForm){
			jQuery("#loadingSpinner").removeClass("hide");
			jQuery(".hide_loading").addClass("hide");



			prenom = response.data.fields[idPrenom].value;
			nom = response.data.fields[idNom].value;
			email = response.data.fields[idEmail].value;
			mobile = response.data.fields[idMobile].value;
			dob = response.data.fields[idDob].value;
			sexe = response.data.fields[idSexe].value;

			console.log(dob);

			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxInscriptionProf',
						'prenom' : prenom,
						'nom' : nom,
						'email' : email,
						'mobile' : mobile,
						'dob' : dob,
						'sexe' : sexe
					})
					.done(function(retour){ 
						if(retour == "account-exists"){
							redirectTo('connexion', "Vous avez déjà un compte avec cette adresse email. Connectez vous !");
						}else if(retour == "creation-compte-wp-prof"){
							showMessage("Impossible de vous créer un compte. Veuillez raffraichir la page et réssayer. Contacter l'équipe si le problème persiste");
						}else{
							redirectTo('onboarding-prof', 
							"Inscription bien validée !  Complétez votre profil pour pouvoir touchez vos premiers €€€ ! "); // on envoie le prof à onboarding-prof
						}
					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
					})
		}

	},

});


/*
 * 
 * fin : faire la soumission du formulaire des profs
 * 
 */

//début jquery
jQuery( document ).ready( function( $ ) {


	new mySubmitController();


});