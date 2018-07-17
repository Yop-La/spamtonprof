/*
 * script chargé sur la page dont le slug est onboarding-prof
 */

//id des champs du formulaire

idFormPays = "69";
idFormIban = "70";

idFormIbanContent= "#nf-form-".concat(idFormIban, "-cont");

idPays = "990";
idIban = "994";
ibanField = "#nf-field-994";
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
		if(response.data.form_id == idFormPays){


			pays = response.data.fields[idPays].value;

			if(pays == 'FR'){
				console.log('FR');
				createCustomAccnt(pays);

			}else{

				showMessage("Désolé ce pays n'est pas supporté. Nous ne pourrons pas vous payer.");

			}

		}else if(response.data.form_id == idFormIban){


			iban = response.data.fields[idIban].value;

			updateIbanProf(iban);

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



	stepId = "#".concat(loggedProf.onboarding_step);

	waitForEl(stepId,function(){

		$(stepId).removeClass("hide");

	});



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

				$("#step-1").removeClass("hide");
				$("#step-0").addClass("hide");

				showMessage("Il ne reste plus qu'à fournir votre IBAN");

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

function updateIbanProf(iban){



	var stripe = Stripe(publicStripeKey);
	stripe.createSource({
		type: 'sepa_debit',
		sepa_debit: {
			iban: iban,
		},
		currency: 'eur',
		owner: {
			name: "just to test iban",
		},
	}).then(function(result) {

		console.log("result stripe");
		console.log(result);
		
		if(result.error == null){

			
		
			$("#loadingSpinner").removeClass("hide");
			$(".hide_loading").addClass("hide");
			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'updateIbanProf',
						'iban' : iban
					})
					.done(function(retour){ 

						redirect("dashboard-prof" ,info = "Félicitations, nous avons bien reçu votre inscription. Nous allons vérifier tout ça et revenir vers vous rapidement."  )

					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
					});
		}else{
			
			$(idFormIbanContent).effect( "shake" );
			
			
			showMessage("L'IBAN saisie est invalide. Veuillez saisir un IBAN correct.");
			
			$(ibanField).attr('style', 'color: red !important; font-weight: 900');
			
		}
	});
}



