/*
 * script chargé sur la page dont le slug est discover_week
 *  voir process ajout matière pour ajouter une matière
 * 
 */

matiere  = "maths-physique"; // contient la matiere affiché (choisie)
matieres  = "maths-physique".split("-");
ajaxEnCours = 0;
gClasseSelect = null;

choixEleveValide = false; // pour savoir si le choix de l'élève est valide

//id des champs du formulaire

idFormEssai = "68";
idFormContentEssai = "#nf-form-".concat(idFormEssai, "-cont");

idPrenomEleve = "936";
idNomEleve = "941";
idEmailEleve = "937";
idPhoneEleve = "942";
idChoixProfil = "938";
idClasse = "943";
idMatieres = "939";
idProfil = "940";

idChapterMaths = "945";
idLacuneMaths = "946";
idNoteMaths = "947";

idChapterPhysique = "949";
idLacunePhysique = "950";
idNotePhysique = "951";

idChapterFrench = "953";
idLacuneFrench = "954";
idNoteFrench = "955";

idProche = "957";
idPrenomProche = "958";
idNomProche = "960";
idMailProche = "959";
idPhoneProche = "961";


idRemarque 	= "962";

idCode = "963";
idTeleprospection = "985";

//form ajout élève

idFormAjoutEleve = "74";

idAjoutElevePrenom = "1018";
idAjoutEleveNom = "1022";
idAjoutEleveEmail = "1019";
idAjoutElevePhone = "1023";
idAjoutEleveChoixProfil = "1020";
idAjoutEleveClasse = "1024";
idAjoutEleveProfil = "1021";

//form inscription essai pour client
idFormEssaiClient = "75";

idBisMatieres = "1039";
idBisRefEleve = "1040";

idBisChapterMaths = "1028";
idBisLacuneMaths = "1029";
idBisNoteMaths = "1030";

idBisChapterPhysique = "1032";
idBisLacunePhysique = "1033";
idBisNotePhysique = "1034";

idBisChapterFrench = "1036";
idBisLacuneFrench = "1037";
idBisNoteFrench = "1038";

idBisRemarque 	= "1041";
idBisCode = "1042";



/*
 * debut : faire la soumission du formulaire de la popup 
 * 
 */

var mySubmitController = Marionette.Object.extend( {

	initialize: function() {
		this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
	},

	actionSubmit: function( response ) {


		// form essai
		if(response.data.form_id == idFormEssai){
			jQuery("#loadingSpinner").removeClass("hide");
			jQuery(".hide_loading").addClass("hide");
			PUM.close(18006);

			//récupérationdes variables du form

			matieres = response.data.fields[idMatieres].value;

			prenomEleve = response.data.fields[idPrenomEleve].value;
			nomEleve = response.data.fields[idNomEleve].value;
			emailEleve = response.data.fields[idEmailEleve].value;
			phoneEleve = response.data.fields[idPhoneEleve].value;
			profil = response.data.fields[idChoixProfil].value;
			classe = response.data.fields[idClasse].value;
			chapterMaths = response.data.fields[idChapterMaths].value;
			lacuneMaths = response.data.fields[idLacuneMaths].value;
			noteMaths = response.data.fields[idNoteMaths].value;
			chapterPhysique = response.data.fields[idChapterPhysique].value;
			lacunePhysique = response.data.fields[idLacunePhysique].value;
			notePhysique = response.data.fields[idNotePhysique].value;
			chapterFrench = response.data.fields[idChapterFrench].value;
			lacuneFrench = response.data.fields[idLacuneFrench].value;
			noteFrench = response.data.fields[idNoteFrench].value;
			proche = response.data.fields[idProche].value;
			prenomProche = response.data.fields[idPrenomProche].value;
			nomProche = response.data.fields[idNomProche].value;
			mailProche = response.data.fields[idMailProche].value;
			phoneProche = response.data.fields[idPhoneProche].value;
			remarque = response.data.fields[idRemarque].value;
			code = response.data.fields[idCode].value;
			teleprospection = response.data.fields[idTeleprospection].value;



			// soumission ajax des champs du form pour création inscription
			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxAfterSubmissionEssai',
						'prenomEleve' : prenomEleve,
						'nomEleve' : nomEleve,
						'emailEleve' : emailEleve,
						'phoneEleve' : phoneEleve,
						'profil' : profil,
						'classe' : classe,
						'chapterMaths' : chapterMaths,
						'lacuneMaths' : lacuneMaths,
						'noteMaths' : noteMaths,
						'chapterPhysique' : chapterPhysique,
						'lacunePhysique' : lacunePhysique,
						'notePhysique' : notePhysique,
						'chapterFrench' : chapterFrench,
						'lacuneFrench' : lacuneFrench,
						'noteFrench' : noteFrench,
						'proche' : proche,
						'prenomProche' : prenomProche,
						'nomProche' : nomProche,
						'mailProche' : mailProche,
						'phoneProche' : phoneProche,
						'remarque' : remarque,
						'matieres' : matieres,
						'code' : code,
						'teleprospection': teleprospection
					})
					.done(function(retour){ 

						error = retour.error;
						message = retour.message;

						if(error){

							showMessage("Il y a un problème. Contacter l'équipe et donner leur ce message d'erreur : ".concat(message));
							ajaxEnCours--;
							if(ajaxEnCours == 0){
								jQuery("#loadingSpinner").addClass("hide");
								jQuery(".hide_loading").removeClass("hide");
							}
						}else{

							if(message == "compte_existe_deja"){
								redirectTo("connexion" ,info = "Vous avez déjà un compte. Connectez vous ! " );
							}else{
								redirectTo("remerciement-eleve" ,"Félicitations. Tu pourras démarrer la semaine de découverte dans 1 jour !" );
							}

						}


					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".hide_loading").removeClass("hide");
						}
					});
		}


		// form essai
		if(response.data.form_id == idFormEssaiClient){
			jQuery("#loadingSpinner").removeClass("hide");
			jQuery(".hide_loading").addClass("hide");
			PUM.close(19464);

			//récupérationdes variables du form

			matieres = response.data.fields[idBisMatieres].value;
			refEleve = response.data.fields[idBisRefEleve].value;

			chapterMaths = response.data.fields[idBisChapterMaths].value;
			lacuneMaths = response.data.fields[idBisLacuneMaths].value;
			noteMaths = response.data.fields[idBisNoteMaths].value;
			chapterPhysique = response.data.fields[idBisChapterPhysique].value;
			lacunePhysique = response.data.fields[idBisLacunePhysique].value;
			notePhysique = response.data.fields[idBisNotePhysique].value;
			chapterFrench = response.data.fields[idBisChapterFrench].value;
			lacuneFrench = response.data.fields[idBisLacuneFrench].value;
			noteFrench = response.data.fields[idBisNoteFrench].value;
			remarque = response.data.fields[idBisRemarque].value;
			code = response.data.fields[idBisCode].value;



			// soumission ajax des champs du form pour création inscription
			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxNewEssaiClient',
						'refEleve' : refEleve,
						'chapterMaths' : chapterMaths,
						'lacuneMaths' : lacuneMaths,
						'noteMaths' : noteMaths,
						'chapterPhysique' : chapterPhysique,
						'lacunePhysique' : lacunePhysique,
						'notePhysique' : notePhysique,
						'chapterFrench' : chapterFrench,
						'lacuneFrench' : lacuneFrench,
						'noteFrench' : noteFrench,
						'remarque' : remarque,
						'matieres' : matieres,
						'code' : code
					})
					.done(function(retour){ 

						error = retour.error;
						message = retour.message;

						if(error){

							showMessage("Il y a un problème. Contacter l'équipe et donner leur ce message d'erreur : ".concat(message));
							ajaxEnCours--;
							if(ajaxEnCours == 0){
								jQuery("#loadingSpinner").addClass("hide");
								jQuery(".hide_loading").removeClass("hide");
							}
						}else{

							if(message == "compte_existe_deja"){
								redirectTo("connexion" ,info = "Vous avez déjà un compte. Connectez vous ! " );
							}else{
								redirectTo("remerciement-eleve" ,"Félicitations. Tu pourras démarrer la semaine de découverte dans 1 jour !" );
							}

						}


					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".hide_loading").removeClass("hide");
						}
					});
		}

		if(response.data.form_id == idFormAjoutEleve){
			jQuery("#loadingSpinner").removeClass("hide");
			jQuery(".hide_loading").addClass("hide");
			PUM.close(19463);

			//récupérationdes variables du form
			classe = response.data.fields[idAjoutEleveClasse].value;
			emailEleve = response.data.fields[idAjoutEleveEmail].value;
			nomEleve = response.data.fields[idAjoutEleveNom].value;
			phoneEleve = response.data.fields[idAjoutElevePhone].value;
			prenomEleve = response.data.fields[idAjoutElevePrenom].value;
			profil = response.data.fields[idAjoutEleveProfil].value;


			// soumission ajax des champs du form pour création inscription
			ajaxEnCours++;

			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxAjoutEleve',
						'classe' : classe,
						'emailEleve' : emailEleve,
						'nomEleve' : nomEleve,
						'phoneEleve' : phoneEleve,
						'profil' : profil,
						'prenomEleve' : prenomEleve
					})
					.done(function(retour){ 

						error = retour.error;
						message = retour.message;

						if(error){

							showMessage("Il y a un problème. Contacter l'équipe et donner leur ce message d'erreur : ".concat(message));

						}else{

							eleve = retour.eleve;

							//ajouter l'option au select élève

							eleves.push(eleve);
							nbEleves = eleves.length;

							var newOption = new Option(eleve.prenom.concat(" ",eleve.nom), nbEleves-1, false, true);
							jQuery(newOption).attr("intrial",false);
							jQuery('#eleve-select').append(newOption).trigger('change').val(nbEleves-1);


							showMessage(eleve.prenom.concat(" est bien ajouté ! Il ne reste plus qu'à faire l'inscription à la semaine découverte."));

							jQuery("#eleve-select + .select2-container .select2-selection").removeClass("red-border");
						}
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".hide_loading").removeClass("hide");
						}


					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".hide_loading").removeClass("hide");
						}
					});
		}

	},

});

/*
 * fin : faire la soumission du formulaire de la popup 
 * 
 */

//pour changer les choix de classe en fonction du profil
var myCustomFieldController = Marionette.Object.extend( {
	initialize: function() {


		// on the Field's model value change...
		var fieldsChannel = Backbone.Radio.channel( 'fields' );
		this.listenTo( fieldsChannel, 'change:modelValue', this.validateRequired );

		// Listen to the render:view event for fields
		this.listenTo( nfRadio.channel( 'fields' ), 'render:view', this.renderView );

	},

	validateRequired: function( model ) {



		if ( idChoixProfil == model.get( 'id' ) || idAjoutEleveChoixProfil == model.get( 'id' )) {

			value = model.get( 'value' );

			idProfilBis = idProfil;
			idClasseBis = idClasse;

			if(idAjoutEleveChoixProfil == model.get( 'id' )){
				idProfilBis = idAjoutEleveProfil
				idClasseBis = idAjoutEleveClasse;	
			}

			jQuery(toFieldId(idProfilBis)).val(value);
			jQuery(toFieldId(idProfilBis)).trigger("change");

			classes = classesByProfil[value];

			jQuery(toFieldId(idClasseBis)).find('option').remove();

			jQuery(toFieldId(idClasseBis)).prepend("<option value='' selected='selected'></option>");

			classes.forEach(function(classe) {

				// ajouter options à form
				jQuery(toFieldId(idClasseBis)).append(jQuery('<option>', {
					value: classe.ref_classe,
					text: classe.nom_complet
				}));

			});

			gClasseSelect = jQuery(toFieldId(idClasseBis));

		}
	},

	renderView: function( view ) {

		var el = jQuery( view.el ).find( '.nf-element' );

		if ( 'classe_1532954602744' == view.model.get( 'key' ) ) {

			if(gClasseSelect != null) {

				jQuery(el).replaceWith(gClasseSelect);
			}
		}

	}
});



//début jquery
jQuery( document ).ready( function( $ ) {

	jQuery('.matieres-select').select2({
		placeholder: 'Choisir la/les matières',
		width: '80%'
	});

	// reset des selects
	jQuery('.matieres-select').val(null); // Select the option with a value of '0'
	jQuery('.matieres-select').trigger('change'); // Notify any JS components that the value changed


	if(isLogged == "true"){

		nbAbosEssai = abosEssai.length;

		if(nbAbosEssai == 2){
			showMessage("Oups, on dirait que vous avez déjà deux essai en cours. Revenez quand il y aura un de terminé.");
			jQuery(".trial-row").addClass("hide");
			jQuery(".row-message").removeClass("hide");
			jQuery(".row-message p").html("Pour le moment, vous avez déjà deux essais en cours. Il sera possible de refaire un autre essai quand un de ces deux essai sera terminé.");
		}
		console.log("here");
		// quand fermeture de la popup ajout élève
		jQuery('#pum-19463')
		.on('pumBeforeClose', function () {


			if(jQuery('#eleve-select').val() == 'ajout-eleve'){
				jQuery('#eleve-select').val(null); // Select the option with a value of '0'
				jQuery('#eleve-select').trigger('change'); // Notify any JS components that the value changed
				hideMessage();
			}
		});

		// affichage des deux selects en haut de page 
		jQuery('#eleve-select').select2({
			placeholder: "Choisir pour qui",
			width: '80%'
		});

		// remettre le formulaire à 0
		jQuery('#eleve-select').val(null); // Select the option with a value of '0'
		jQuery('#eleve-select').trigger('change'); // Notify any JS components that the value changed

		//preremplissage du select élève
		jQuery.each(eleves, function(index, eleve) {
			jQuery('#eleve-select').append(
					jQuery('<option></option>').val(index).attr("inTrial",eleve.inTrial).html(eleve.prenom.concat(" ",eleve.nom))
			);
		});

		// action quand selection d'une option eleve
		jQuery('#eleve-select').on('select2:select', function (e) {

			var choixEleve = jQuery('#eleve-select').val();

			console.log(choixEleve);
			choixEleveValide = false; 

			erreur = false;
			message = "";

			if(choixEleve == "ajout-eleve"){


				PUM.open(19463);

			}else if(choixEleve == ""){

				message = "Veuillez choisir celui ou celle qui va faire la semaine d'essai.";
				erreur = true;

			}else{ // si choix d'un élève

				inTrial = jQuery('#eleve-select option:selected').attr("intrial");
				eleveChoisi = eleves[choixEleve];


				if(inTrial == "true"){
					message = "Tu es déjà entrain de faire un essai ".concat(eleveChoisi.prenom,". Reviens en demander un quand tu auras fini.");
					erreur = true;

				}else{

					choixEleveValide = true;

					jQuery("#eleve-select + .select2-container .select2-selection").removeClass("red-border");
					hideMessage();
				}




			}

			if(erreur){
				jQuery("#eleve-select + .select2-container .select2-selection").addClass("red-border")
				checkUntil("#eleve-select + .select2-container" , 1);
				showMessage(message);
			}


		});

		// fin affichage des deux selects en haut de page

		waitForEl(".choix-logout", function() {
			jQuery(".choix-logout").remove();
		});

		waitForEl(".login", function() {
			jQuery(".login").removeClass("hide");
		});

//		if(userType == "eleve"){



//		message = "Hello ".concat(loggedEleve.prenom,", il y a encore 2 essais gratuits (un pour toi et un pour ton frère ou ta soeur)");
//		switch (nbAbosEssai) {
//		case 0:
//		message = "Hello ".concat(loggedEleve.prenom,", il y a encore 2 essais gratuits (un pour toi et un pour ton frère ou ta soeur)");
//		break;
//		case 1:
//		aboEssai = abosEssai[0];
//		message = "Hello ".concat(loggedEleve.prenom,", il reste 1 essai gratuit pour ton frère ou ta soeur ");
//		break;
//		case 2:
//		message = "Hello ".concat(loggedEleve.prenom," il n'y a plus d'essai gratuit car il y a déjà deux essais en cours");
//		break;

//		}
//		showMessage(message);

	}else{

		waitForEl(".choix-login", function() {
			jQuery(".choix-login").remove();

		});

		waitForEl(".logout", function() {
			jQuery(".logout").removeClass("hide");
		});



	}


	waitForEl(toFieldId(idClasse), function() {
		gClasseSelect = jQuery(toFieldId(idClasse));
	});

	new mySubmitController();

	new myCustomFieldController();

	setMatieresField(matieres, idMatieres);
	setMatieresField(matieres, idBisMatieres);


	/* pour customiser le select des matières et changer l'affichage du blco matière en fonction du choix*/

	waitForEl('.matieres-select', function() {

//		jQuery('#select-box-matiere option[name= "defaut"]').prop('selected', true);



		checkUntil('.select2-container' , 1);

		jQuery('.matieres-select').change(function(){

			var nouvelleMatiere = jQuery('.matieres-select').val();

			if(nouvelleMatiere != matiere){

				jQuery(".".concat(nouvelleMatiere)).removeClass("hide");
				jQuery(".".concat(matiere)).addClass("hide");
				matiere = nouvelleMatiere;
				matieres  = nouvelleMatiere.split("-");

			}

			setMatieresField(matieres, idMatieres);
			setMatieresField(matieres, idBisMatieres);

		});

	});

	/* fin : customiser le select des matières et changer l'affichage du blco matière en fonction du choix*/


	/* pour customiser le select des élèves et changer l'affichage en fonction du choix*/

//	waitForEl('#choix-eleves', function() {

//	jQuery('#select-box-eleve option[name= "defaut"]').prop('selected', true);


//	// charger tous les élèves du compte dans le select 
//	// si élève en essai -> écrire que élève a plus le droit à essai 
//	// si élève a déjà fait essai de cette matière -> dire que élève a plus le droit à esssai
//	// si élève pas en essai -> dire que l'élève a droit à esssai

//	// permettre d'ajouter un élève -> popup sur ajouter élève	

//	});


//	/* fin pour customiser le select des élèves et changer l'affichage en fonction du choix*/

	/* debut : pour afficher popup et faire les bons affichages de matière dans la deuxième partie du formulaire */

	waitForEl('.pop-essai', function() {

		jQuery('.pop-essai').click(function(){

			// vérifier qu'un élève est sélectionné.



			if(isLogged == "false"){

				PUM.open(18006);

				jQuery(jQuery('.nf-breadcrumb')[0]).trigger('click');

			}else{

				if(nbAbosEssai >= 2){
					showMessage("Oups, on dirait que vous avez déjà deux essai en cours. Revenez quand il y aura un de terminé.");
					jQuery(".trial-row").addClass("hide");
					jQuery(".row-message").removeClass("hide");
					jQuery(".row-message p").html("Pour le moment, vous avez déjà deux essais en cours. Il sera possible de refaire un autre essai quand un de ces deux essai sera terminé.");
				}else if(!choixEleveValide){


					jQuery('#eleve-select').trigger('select2:select'); // s'occupe de l'affichage des erreurs utilisateur

				}else{

					PUM.open(19464);

					jQuery(jQuery('.nf-breadcrumb')[0]).trigger('click');

					//récupérer l'élève choisi

					indexEleve = jQuery('#eleve-select').val();

					eleve = eleves[indexEleve];

					console.log(eleve);

					jQuery('#prenomEleve').text(eleve.prenom);
					jQuery(toFieldId(idBisRefEleve)).val(eleve.ref_eleve);

				}

			}



		});
	});
	/* fin : pour afficher popup et faire les bons affichages de matière dans la deuxième partie du formulaire */




//	});


	/* fin customiser le select des matières */







//	loadTextesCat();

//	waitForEl('.generateText', function() {
//	jQuery('.generateText').click(function(){

//	if(!nomCatLoaded){

//	showMessage("Il faut d'abord choisir une catégorie de textes avant de pouvoir en générer automatiquement." );
//	return;

//	}

//	if(nbTextTot -nbTextesWritted <= 0){

//	jQuery("#loadingSpinner").removeClass("hide");
//	jQuery("#onglets").addClass("hide");

//	showMessage("Génération des textes en cours ... " );

//	ajaxEnCours++;


//	.always(function() {
//	ajaxEnCours--;
//	if(ajaxEnCours == 0){
//	jQuery("#loadingSpinner").addClass("hide");
//	jQuery("#onglets").removeClass("hide");
//	}
//	});
//	}else{
//	showMessage("Il faut encore écrire ".concat(nbTextTot -nbTextesWritted, " texte(s) afin de pouvoir en générer automatiquement"));
//	}
//	});
//	});




});

function checkUntil(selector, nbShake) {
	jQuery(selector).effect( "shake",500);
	nbShake--;
	if (nbShake == 0) {
		return;
	} else {
		setTimeout(function() {
			jQuery(selector).effect( "shake",500);
			checkUntil(selector, nbShake);
		}, 5000);
	}
};

/* pour remplir le champ caché matières */

function setMatieresField(matieres,idMatieres){



	waitForEl(toFieldId(idMatieres), function() {

		jQuery(toFieldId(idMatieres)).val('');

		jQuery(toFieldId(idMatieres)).trigger('change');

		matieres.forEach(function(matiere) {

			valMatiere = jQuery(toFieldId(idMatieres)).val();

			if(valMatiere == ''){
				jQuery(toFieldId(idMatieres)).val(valMatiere.concat(matiere));
			}else{
				jQuery(toFieldId(idMatieres)).val(valMatiere.concat("-",matiere));
			}


			jQuery(toFieldId(idMatieres)).trigger('change');

		});



	});
}

function addEleve (state) {
	if(state.id == ""){
		return(state.text);
	}
	PUM.open(18006);
	return("En attente d'un ajout ...");
};








