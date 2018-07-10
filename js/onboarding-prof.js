/*
 * script chargé sur la page dont le slug est onboarding-prof
 */

//id des champs du formulaire

idForm = "69";
idFormContent= "#nf-form-".concat(idForm, "-cont");

idPays = "990";

ajaxEnCours = 0;

/*
 * debut : gérer la soumission du formulaire 69 : le formulaire de choix du pays d'activité
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


			pays = response.data.fields[idPays].value;

			if(pays == 'FR'){
				console.log('FR');
				createCustomAccnt(pays);

			}else{

				showMessage("Désolé ce pays n'est pas supporté. Nous ne pourrons pas vous payer.");

			}

		}

	},

});


/*
 * 
 * fin : faire la soumission du formulaire de choix du pays
 * 
 */

//début jquery
jQuery( document ).ready( function( $ ) {


	new mySubmitController();



	
	
});

//pour créer le token permettant de créer le custom acccount. Eclispe indique 3 erreurs de syntaxe mais il se trompe
async function createCustomAccnt(pays) {


	$("#loadingSpinner").removeClass("hide");
	$(".hide_loading").addClass("hide");
	ajaxEnCours++;

	const stripe = Stripe(publicStripeKey);


	const result = await stripe.createToken('account', {
		legal_entity: {
			address: {
				state: pays,
			},
		},
		tos_shown_and_accepted: true,
	});


	jQuery.post(
			ajaxurl,
			{
				'action' : 'ajaxCreateStripAccount',
				'tokenId' : result.token.id,
				'testMode' : testMode,
				'pays' : pays
			})
			.done(function(retour){ 

				showMessage("Le pays choisi est ".concat(retour));

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




