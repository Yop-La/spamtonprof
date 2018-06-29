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
			$("#loadingSpinner").removeClass("hide");
			$(".hide_loading").addClass("hide");



			prenom = response.data.fields[idPrenom].value;
			nom = response.data.fields[idNom].value;
			email = response.data.fields[idEmail].value;
			mobile = response.data.fields[idMobile].value;

			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxInscriptionProf',
						'prenom' : prenom,
						'nom' : nom,
						'email' : email,
						'mobile' : mobile
					})
					.done(function(retour){ 
						if(retour == "account-exists"){
							redirect('connexion', "Vous avez déjà un compte avec cette adresse email. Connectez vous !");
						}else if(retour == "creation-compte-wp-prof"){
							showMessage("Impossible de vous créer un compte. Veuillez raffraichir la page et réssayer. Contacter l'équipe si le problème persiste");
						}else if(retour == "ok"){
							redirect('dashboard-prof', "Inscription bien validée !  Complétez votre profil pour pouvoir touchez vos premiers €€€ ! ");
						}
					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
					})
					.always(function() {
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							$("#loadingSpinner").addClass("hide");
							$(".hide_loading").removeClass("hide");
						}

					});

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


//	/* pour customiser le select des matières et changer l'affichage du blco matière en fonction du choix*/
//
//	waitForEl('#choix-matieres', function() {
//
//		$("#select-box1").val('maths-physique');
//
//		$("select").on("click" , function() {
//
//			$(this).parent(".select-box").toggleClass("open");
//
//		});
//	});

});