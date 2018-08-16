/*
 * script chargé sur la page dont le slug est onboarding-prof
 */

//id des champs du formulaire



if(loggedProf.onboarding == "1"){
	console.log("dedans");
	redirect("dashboard-prof"); 
}

idFormPays = "69";
idFormIds = '74';
idFormIban = "70";

idFormIbanContent= "#nf-form-".concat(idFormIban, "-cont");

idAdresse = "1045";
idVille = "1046";
idCodePostal = "1047"; 
idPays = "990";

ajaxEnCours = 0;

idTypeId = "1054";
idRectoId = "1055";
idVersoId = "1056";

var stripe = Stripe(publicStripeKey);
var elements = stripe.elements();
var iban;

/*
 * debut : gérer la soumission du formulaire 69 : le formulaire de choix du pays d'activité
 * 
 */

var mySubmitController = Marionette.Object.extend( {

	initialize: function() {
		this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
	},

	actionSubmit: function( response ) {

		// titles form
		if(response.data.form_id == idFormPays){


			pays = response.data.fields[idPays].value;
			adresse = response.data.fields[idAdresse].value;
			ville = response.data.fields[idVille].value;
			codePostal = response.data.fields[idCodePostal].value;

			if(pays == 'FR'){
				createCustomAccnt(adresse, ville, codePostal, pays);

			}else{

				showMessage("Désolé votre lieu d'exercice doit se situer en France. Sinon nous ne pourrons pas vous payer.");

			}

		}else if(response.data.form_id == idFormIban){

			updateIbanProf();

		}else if(response.data.form_id == idFormIds){


			typeId = response.data.fields[idTypeId].value;
			fieldVersoId = response.data.fields[idVersoId];
			fieldRectoId = response.data.fields[idRectoId];

			urlVersoId = false;
			urlRectoId = false;
			if(typeId == 'id-card'){
				urlVersoId = fieldVersoId.files[0].data.file_url;

			}
			urlRectoId = fieldRectoId.files[0].data.file_url;


			uploadDocsIds(urlRectoId, urlVersoId);

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


	waitForEl("#iban-element",function(){


		// Custom styling can be passed to options when creating an Element.
		// (Note that this demo uses a wider set of styles than the guide below.)
		var style = {
				base: {
					color: '#32325d',
					fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
					fontSmoothing: 'antialiased',
					fontSize: '16px',
					'::placeholder': {
						color: '#aab7c4'
					},
					':-webkit-autofill': {
						color: '#32325d',
					},
				},
				invalid: {
					color: '#fa755a',
					iconColor: '#fa755a',
					':-webkit-autofill': {
						color: '#fa755a',
					},
				}
		};

		// Create an instance of the iban Element.
		iban = elements.create('iban', {
			style: style,
			supportedCountries: ['SEPA'],
			placeholderCountry: 'FR'
		});

		// Add an instance of the iban Element into the `iban-element` <div>.
		iban.mount('#iban-element');

		var errorMessage = document.getElementById('error-message');
		var bankName = document.getElementById('bank-name');

		iban.on('change', function(event) {
			// Handle real-time validation errors from the iban Element.
			if (event.error) {
				errorMessage.textContent = event.error.message;
				errorMessage.classList.remove('hide');
			} else {
				errorMessage.classList.add('hide');
			}

			// Display bank name corresponding to IBAN, if available.
			if (event.bankName && !event.error) {
				bankName.textContent = event.bankName;
				bankName.classList.remove('hide');
			} else {
				bankName.classList.add('hide');
			}
		});


	});


});


async function uploadDocsIds(urlRectoId, urlVersoId) {

	$("#loadingSpinner").removeClass("hide");
	$(".hide_loading").addClass("hide");
	ajaxEnCours++;


	const response = await fetch(urlRectoId);
	const rectoBlob = await response.blob();

	const data = new FormData();
	data.append('file', rectoBlob);
	data.append('purpose', 'identity_document');
	const fileResult = await fetch('https://uploads.stripe.com/v1/files', {
		method: 'POST',
		headers: {'Authorization': `Bearer ${stripe._apiKey}`},
		body: data,
	});
	const rectoFileData = await fileResult.json();

	fields = {
			legal_entity: {
				verification: {
					document: rectoFileData.id,
				},
			}
	};

	if(urlVersoId){


		const response = await fetch(urlVersoId);
		const versoBlob = await response.blob();

		const data = new FormData();
		data.append('file', versoBlob);
		data.append('purpose', 'identity_document');
		const fileResult = await fetch('https://uploads.stripe.com/v1/files', {
			method: 'POST',
			headers: {'Authorization': `Bearer ${stripe._apiKey}`},
			body: data,
		});
		const versoFileData = await fileResult.json();
		fields.legal_entity.verification["document_back"] = versoFileData.id;

	}

	const result = await stripe.createToken('account', fields);


	jQuery.post(
			ajaxurl,
			{
				'action' : 'ajaxUpdateCustomAccounts',
				'tokenId' : result.token.id,
				'testMode' : testMode,
				'refProf' : loggedProf.ref_prof

			})
			.done(function(retour){ 


				if(retour.error){

					showMessage("Il y a un problème : ".concat(retour.message, "Veuillez réessayer ou contacter l'équipe"));

				}else{

					$("#step-2").removeClass("hide");
					$("#step-1").addClass("hide");
					$("#step-0").addClass("hide");

					showMessage("Il ne reste plus qu'à donner l'IBAN et on pourra commencer.");					

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


//pour créer le token permettant de créer le custom acccount. Eclispe indique 3 erreurs de syntaxe mais il se trompe
async function createCustomAccnt(adresse, ville, codePostal, pays) {


	$("#loadingSpinner").removeClass("hide");
	$(".hide_loading").addClass("hide");
	ajaxEnCours++;

	dateNaissance = new Date(loggedProf.date_naissance);


	const result = await stripe.createToken('account', {
		legal_entity: {
			first_name: loggedProf.prenom,
			last_name: loggedProf.nom,
			type: 'individual',
			dob:{
				day: dateNaissance.getDate(),
				month: dateNaissance.getMonth()+1,
				year: dateNaissance.getYear()+1900
			},
			address: {
				line1 : adresse,
				city: ville,
				state: pays,
				postal_code : codePostal
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
				'adresse' : adresse,
				'ville' : ville,
				'codePostal' : codePostal,
				'pays' : pays,
				'refProf' : loggedProf.ref_prof
			})
			.done(function(retour){ 

				console.log("retour");
				console.log(retour);

				if(retour.error){

					showMessage("Il y a un problème : ".concat(retour.message, ". Veuillez réessayer ou contacter l'équipe"));


				}else{

					$("#step-1").removeClass("hide");
					$("#step-0").addClass("hide");

					showMessage("Plus que deux étapes et c'est fini !");


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

async function updateIbanProf(){


	const {token, error} = await stripe.createToken(iban, {

		account_holder_name: loggedProf.prenom.concat(" ",loggedProf.nom),
		account_holder_type : "individual",
		currency: "eur"
		
		});
	
	

	if(error){

		$(idFormIbanContent).effect( "shake" );
		showMessage("L'IBAN saisie est invalide. Veuillez saisir un IBAN correct.");

	}else{

		$("#loadingSpinner").removeClass("hide");
		$(".hide_loading").addClass("hide");
		ajaxEnCours++;
		jQuery.post(
				ajaxurl,
				{
					'action' : 'addIbanProf',
					'tokenId' : token.id,
					'refProf' : loggedProf.ref_prof,
					'testMode' : testMode
				})
				.done(function(retour){ 

					if(retour.error){

						showMessage("Il y a un problème : ".concat(retour.message, "Veuillez réessayer ou contacter l'équipe"));


					}else{
						redirect("dashboard-prof" ,info = "Félicitations, nous avons bien reçu votre inscription. Nous allons vérifier tout ça et revenir vers vous rapidement."  )
					}


				})
				.fail(function(err){
					console.log("erreur ajax");
					console.log(err);
					showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
				});

	}

}



