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

if(testMode == "false"){

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

		// titles form
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

		if ( 'profil_1532954478855' != model.get( 'key' ) ) return false;

		value = model.get( 'value' );

		$(toFieldId(idProfil)).val(value);
		$(toFieldId(idProfil)).trigger("change");


		jQuery.post(
				ajaxurl,
				{
					'action' : 'ajaxGetClasses',
					'ref_profil' : model.get( 'value' )
				})
				.done(function(classes){




					$(toFieldId(idClasse)).find('option').remove();

					$(toFieldId(idClasse)).prepend("<option value='' selected='selected'></option>");

					classes.forEach(function(classe) {

						// ajouter options à form
						$(toFieldId(idClasse)).append($('<option>', {
							value: classe.ref_classe,
							text: classe.nom_complet
						}));



					});

					gClasseSelect = $(toFieldId(idClasse));

				})
				.fail(function(err){
					showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");

				});


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

	waitForEl(toFieldId(idClasse), function() {
		gClasseSelect = $(toFieldId(idClasse));
	});

	new mySubmitController();

	new myCustomFieldController();

	setMatieresField(matieres);

	/* pour customiser le select des matières et changer l'affichage du blco matière en fonction du choix*/

	waitForEl('#choix-matieres', function() {

		$("#select-box1").val('maths-physique');

		$("#select-box1").on("click" , function() {

			$(this).parent(".select-box").toggleClass("open");

		});

		$(document).mouseup(function (e)
				{
			var container = $(".select-box");

			if (container.has(e.target).length === 0)
			{
				container.removeClass("open");
			}
				});


		$("#select-box1").on("change" , function() {

			var selection = $(this).find("option:selected").text(),
			labelFor = $(this).attr("id"),
			label = $("[for='" + labelFor + "']");

			label.find(".label-desc").html(selection);

		});

		waitForEl('.select-box', function() {

			checkUntil('.select-box' , 3);

			$('#select-box1').change(function(){

				var nouvelleMatiere = $('#select-box1	').val();

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

		/* debut : pour afficher popup et faire les bons affichages de matière dans la deuxième partie du formulaire */

		waitForEl('.pop-essai', function() {


			$('.pop-essai').click(function(){

				PUM.open(18006);

				$($('.nf-breadcrumb')[0]).trigger('click');



			});
		});
		/* fin : pour afficher popup et faire les bons affichages de matière dans la deuxième partie du formulaire */




	});


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




