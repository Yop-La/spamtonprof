/*
 * script chargé sur la page dont le slug est discover_week
 *  voir process ajout matière pour ajouter une matière
 * 
 */

matiere  = "maths-physique"; // contient la matiere affiché (choisie)
matieres  = "maths-physique".split("-");
ajaxEnCours = 0;
gClasseSelect = null;

//id des champs du formulaire



idFormEssai = "72";
idFormContentEssai = "#nf-form-".concat(idFormEssai, "-cont");

idPrenomEleve = "1006";
idNomEleve = "1007";
idEmailEleve = "1009";
idPhoneEleve = "1010";
idChoixProfil = "1008";
idClasse = "1037";
idMatieres = "1039";
idProfil = "1040";



idChapterMaths = "1011";
idLacuneMaths = "1012";
idNoteMaths = "1015";

idChapterPhysique = "1019";
idLacunePhysique = "1020";
idNotePhysique = "1021";

idChapterFrench = "1034";
idLacuneFrench = "1035";
idNoteFrench = "1036";

idProche = "1014";
idPrenomProche = "1022";
idNomProche = "1025";
idMailProche = "1023";
idPhoneProche = "1026";
idRemarque 	= "1029";

idCode = "1032";

idFormAjoutEleve = "75";

idAjoutElevePrenom = "1063";
idAjoutEleveNom = "1061";
idAjoutEleveEmail = "1060";
idAjoutElevePhone = "1059";
idAjoutEleveChoixProfil = "1062";
idAjoutEleveClasse = "1088";
idAjoutEleveProfil = "1089";


if(!domain.includes("localhost")){

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




}

/*
 * debut : faire la soumission du formulaire de la popup 
 * 
 */

var mySubmitController = Marionette.Object.extend( {

	initialize: function() {
		this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
	},

	actionSubmit: function( response ) {
		console.log("form submitted");
		console.log(response);

		// form essai
		if(response.data.form_id == idFormEssai){
			$("#loadingSpinner").removeClass("hide");
			$(".hide_loading").addClass("hide");
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
						'code' : code
					})
					.done(function(retour){ 

						error = retour.error;
						message = retour.message;

						if(error){

							showMessage("Il y a un problème. Contacter l'équipe et donner leur ce message d'erreur : ".concat(message));
							ajaxEnCours--;
							if(ajaxEnCours == 0){
								$("#loadingSpinner").addClass("hide");
								$(".hide_loading").removeClass("hide");
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
							$("#loadingSpinner").addClass("hide");
							$(".hide_loading").removeClass("hide");
						}
					});
		}

		if(response.data.form_id == idFormAjoutEleve){
			$("#loadingSpinner").removeClass("hide");
			$(".hide_loading").addClass("hide");
			PUM.close(18754);

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

							showMessage("Tout se passe bien");
							console.log("eleve");
							eleve = retour.eleve;

							//ajouter l'option au select élève

							eleves.push(eleve);
							nbEleves = eleves.length;

							var newOption = new Option(eleve.prenom.concat(" ",eleve.nom), nbEleves-1, false, true);
							$('#eleve-select').append(newOption).trigger('change').val(nbEleves-1);


							showMessage(eleve.prenom.concat(" est bien ajouté ! Il ne reste plus qu'à faire l'inscription à la semaine découverte."));

						}
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							$("#loadingSpinner").addClass("hide");
							$(".hide_loading").removeClass("hide");
						}


					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							$("#loadingSpinner").addClass("hide");
							$(".hide_loading").removeClass("hide");
						}
					});
		}

	},

});

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


			$(toFieldId(idProfilBis)).val(value);
			$(toFieldId(idProfilBis)).trigger("change");


			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxGetClasses',
						'ref_profil' : model.get( 'value' )
					})
					.done(function(classes){




						$(toFieldId(idClasseBis)).find('option').remove();

						$(toFieldId(idClasseBis)).prepend("<option value='' selected='selected'></option>");

						classes.forEach(function(classe) {

							// ajouter options à form
							$(toFieldId(idClasseBis)).append($('<option>', {
								value: classe.ref_classe,
								text: classe.nom_complet
							}));



						});

						gClasseSelect = $(toFieldId(idClasseBis));

					})
					.fail(function(err){
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");

					});

		}
	},

	renderView: function( view ) {

		var el = jQuery( view.el ).find( '.nf-element' );

		if ( 'classe_1532954602744' == view.model.get( 'key' ) ) {

			if(gClasseSelect != null) {

				$(el).replaceWith(gClasseSelect);
			}
		}

	}
});




/*
 * fin : faire la soumission du formulaire de la popup 
 * 
 */

//début jquery
jQuery( document ).ready( function( $ ) {


	// quand fermeture de la popup ajout élève
	jQuery('#pum-18754')
	.on('pumBeforeClose', function () {
		if($('#eleve-select').val() == 'ajout-eleve'){
			$('#eleve-select').val(null); // Select the option with a value of '0'
			$('#eleve-select').trigger('change'); // Notify any JS components that the value changed
		}
	});

	// affichage des deux selects en haut de page 
	$('#eleve-select').select2({
		placeholder: "Choisir pour qui",
		width: '80%'
	});


	$('#matieres-select').select2({
		placeholder: 'Choisir la/les matières',
		width: '80%'
	});

	// reset des selects
	$('#matieres-select').val(null); // Select the option with a value of '0'
	$('#matieres-select').trigger('change'); // Notify any JS components that the value changed

	// remettre le formulaire à 0
	$('#eleve-select').val(null); // Select the option with a value of '0'
	$('#eleve-select').trigger('change'); // Notify any JS components that the value changed

	//preremplissage du select élève
	$.each(eleves, function(index, eleve) {
		$('#eleve-select').append(
				$('<option></option>').val(index).html(eleve.prenom.concat(" ",eleve.nom))
		);
	});

	// action quand selection d'une option eleve
	$('#eleve-select').on('select2:select', function (e) {

		var choixEleve = $('#eleve-select').val();

		if(choixEleve == "ajout-eleve"){


			PUM.open(18754);

		}else{

			//si eleve en essai
			// afficher pas possible de faire un essai
			//sinon
			// préparer form nouvelle essai 

			console.log(eleves[choixEleve]);

		}


	});

	// fin affichage des deux selects en haut de page


	if(isLogged == "true"){

		waitForEl(".choix-logout", function() {
			$(".choix-logout").remove();
		});

		waitForEl(".login", function() {
			$(".login").removeClass("hide");
		});

		if(userType == "proche"){

			nbAbosEssai = abosEssai.length;

			message = "Hello ".concat(loggedProche.prenom,", il y a encore 1 essai gratuit disponible.");
			switch (nbAbosEssai) {
			case 0:
				message = "Hello ".concat(loggedProche.prenom,", il y a encore 1 essai gratuit disponible.");
				break;
			case 1:
				aboEssai = abosEssai[0];
				message = "Hello ".concat(loggedProche.prenom,", désolé, il n'y a plus d'essai gratuit.");
				break;
			case 2:
				message = "Hello ".concat(loggedProche.prenom," désolé, il n'y a plus d'essai gratuit ");
				break;

			}
			console.log(message);
			showMessage(message);

		}

//		if(userType == "eleve"){

//		nbAbosEssai = abosEssai.length;

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
			$(".choix-login").remove();
		});

		waitForEl(".logout", function() {
			$(".logout").removeClass("hide");
		});



	}


	waitForEl(toFieldId(idClasse), function() {
		gClasseSelect = $(toFieldId(idClasse));
	});

	new mySubmitController();

	new myCustomFieldController();

	setMatieresField(matieres);

	/* pour customiser le select des matières et changer l'affichage du blco matière en fonction du choix*/

	waitForEl('#matieres-select', function() {

//		$('#select-box-matiere option[name= "defaut"]').prop('selected', true);



		checkUntil('.select2-container' , 2);

		$('#matieres-select').change(function(){

			var nouvelleMatiere = $('#matieres-select').val();

			if(nouvelleMatiere != matiere){

				$(".".concat(nouvelleMatiere)).removeClass("hide");
				$(".".concat(matiere)).addClass("hide");
				matiere = nouvelleMatiere;
				matieres  = nouvelleMatiere.split("-");

			}

			setMatieresField(matieres);

		});

	});

	/* fin : customiser le select des matières et changer l'affichage du blco matière en fonction du choix*/


	/* pour customiser le select des élèves et changer l'affichage en fonction du choix*/

//	waitForEl('#choix-eleves', function() {

//	$('#select-box-eleve option[name= "defaut"]').prop('selected', true);


//	// charger tous les élèves du compte dans le select 
//	// si élève en essai -> écrire que élève a plus le droit à essai 
//	// si élève a déjà fait essai de cette matière -> dire que élève a plus le droit à esssai
//	// si élève pas en essai -> dire que l'élève a droit à esssai

//	// permettre d'ajouter un élève -> popup sur ajouter élève	

//	});


//	/* fin pour customiser le select des élèves et changer l'affichage en fonction du choix*/

	/* debut : pour afficher popup et faire les bons affichages de matière dans la deuxième partie du formulaire */

	waitForEl('.pop-essai', function() {

		$('.pop-essai').click(function(){

			PUM.open(18006);

			$($('.nf-breadcrumb')[0]).trigger('click');

		});
	});
	/* fin : pour afficher popup et faire les bons affichages de matière dans la deuxième partie du formulaire */




//	});


	/* fin customiser le select des matières */







//	loadTextesCat();

//	waitForEl('.generateText', function() {
//	$('.generateText').click(function(){

//	if(!nomCatLoaded){

//	showMessage("Il faut d'abord choisir une catégorie de textes avant de pouvoir en générer automatiquement." );
//	return;

//	}

//	if(nbTextTot -nbTextesWritted <= 0){

//	$("#loadingSpinner").removeClass("hide");
//	$("#onglets").addClass("hide");

//	showMessage("Génération des textes en cours ... " );

//	ajaxEnCours++;


//	.always(function() {
//	ajaxEnCours--;
//	if(ajaxEnCours == 0){
//	$("#loadingSpinner").addClass("hide");
//	$("#onglets").removeClass("hide");
//	}
//	});
//	}else{
//	showMessage("Il faut encore écrire ".concat(nbTextTot -nbTextesWritted, " texte(s) afin de pouvoir en générer automatiquement"));
//	}
//	});
//	});




});

function checkUntil(selector, nbShake) {
	if (nbShake == 0) {
		return;
	} else {
		setTimeout(function() {
			$(selector).effect( "shake",500);
			nbShake--;
			checkUntil(selector, nbShake);
		}, 5000);
	}
};

/* pour remplir le champ caché matières */

function setMatieresField(matieres){



	waitForEl(toFieldId(idMatieres), function() {

		$(toFieldId(idMatieres)).val('');

		$(toFieldId(idMatieres)).trigger('change');

		matieres.forEach(function(matiere) {

			valMatiere = $(toFieldId(idMatieres)).val();

			if(valMatiere == ''){
				$(toFieldId(idMatieres)).val(valMatiere.concat(matiere));
			}else{
				$(toFieldId(idMatieres)).val(valMatiere.concat("-",matiere));
			}


			$(toFieldId(idMatieres)).trigger('change');

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








