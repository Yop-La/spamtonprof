console.log('hello');

/*


 * script chargé sur la page dont le slug est gestion-formule
 */




ajaxEnCours = 0;

//form ajout formule
var idAdFormulaForm = "76";
var idNomFormule= "1046";
var idTarif= "1047";


//edit formula form 
var idEditFormulaForm = '77';
var idSelectFormula = '1049';

var idNomFormule2 = '1050';

var idSelectMatiere = '1051';
var idSelectNiveau = '1052';



var reset = true;
var refFormule; // la ref formule en cours d'édition

var matieresChecked = null;


var mySubmitController = Marionette.Object.extend( {

	initialize: function() {
		this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
	},

	actionSubmit: function( response ) {
		console.log("form submitted");
		console.log(response);

		// add formula form
		if(response.data.form_id == idAdFormulaForm){
			jQuery("#loadingSpinner").removeClass("hide");
			jQuery("#onglets").addClass("hide");

			nomFormule = response.data.fields[idNomFormule].value;
			tarif = response.data.fields[idTarif].value;

			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxAddFormula',
						'nomFormule' : nomFormule,
						'tarif' : tarif 
					})
					.done(function(retour){

						if(!retour.error){

							//on ajoute la formule au formulaire d'édititon des formules
							jQuery('#nf-field-'.concat(idSelectFormula)).append(new Option(retour.formule.formule,retour.formule.ref_formule));

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
							jQuery("#loadingSpinner").addClass("hide");
							jQuery("#onglets").removeClass("hide");
						}
					});

		}

		// edit formula form
		if(response.data.form_id == idEditFormulaForm){
			jQuery("#loadingSpinner").removeClass("hide");
			jQuery("#onglets").addClass("hide");

			refFormule = response.data.fields[idSelectFormula].value;
			matieres = response.data.fields[idSelectMatiere].value;
			niveaux = response.data.fields[idSelectNiveau].value;
			nomFormule = response.data.fields[idNomFormule2].value;

			console.log('efit formula form submit');
			console.log(matieres);
			console.log(niveaux);

			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxEditFormula',
						'refFormule' : refFormule,
						'matieres' : matieres,
						'niveaux' : niveaux,
						'nomFormule' : nomFormule
					})
					.done(function(retour){

						if(!retour.error){
							console.log("dans done sans erreur");
							console.log(retour.message);
							formule = retour.formule;
							showMessage('La formule a bien été mise à jour');

							console.log(jQuery("#nf-field-".concat(idSelectFormula, " option[value=",formule.ref_formule,"]")).html());
							console.log(formule.formule);
							jQuery("#nf-field-".concat(idSelectFormula, " option[value=",formule.ref_formule,"]")).html(formule.formule);

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
							jQuery("#loadingSpinner").addClass("hide");
							jQuery("#onglets").removeClass("hide");
						}
					});

		}






	}

});



//pour changer les choix de classe en fonction du profil
var myCustomFieldController = Marionette.Object.extend( {
	initialize: function() {


		// on the Field's model value change...
		var fieldsChannel = Backbone.Radio.channel( 'fields' );
		this.listenTo( fieldsChannel, 'change:modelValue', this.validateRequired );

	},

	validateRequired: function( model ) {

		value = model.get( 'value' );

		// on change select formule
		if ( idSelectFormula == model.get( 'id' )) {

			value = model.get( 'value' );

			if(value == ''){
				showMesssage('Veuillez choisir une formule');

				jQuery(".select-matieres").addClass('hide');
				jQuery(".select-niveaux").addClass('hide');
				jQuery(".nom-formule").addClass('hide');
				return;
			}


			refFormule = value;

			jQuery(".select-matieres").removeClass('hide');
			jQuery(".select-niveaux").removeClass('hide');
			jQuery(".nom-formule").removeClass('hide');

			console.log(value);

			jQuery("#loadingSpinner").removeClass("hide");
			jQuery("#onglets").addClass("hide");


			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxGetFormula',
						'refFormule' : value
					})
					.done(function(retour){

						if(!retour.error){
							console.log("retour de ajax après select formule");
							formule = retour.formule;
							matieres = formule.matieres;
							classes = formule.classes;

							jQuery('#nf-field-'.concat(idNomFormule2)).val(formule.formule).change();

							// au changement de formule, unchecker tous les inputs précedemment checkés
							inputs = jQuery(".select-to-reset option");
							inputs = Object.values(inputs);
							inputs.forEach(function(input){
								jQuery(input).prop('selected', false)
							});
							jQuery('#nf-field-'.concat(idSelectMatiere)).change();

							// si il y a déjà des matières existances, les checker
							if(!matieres){
								showMessage("Veuillez commencer par définir les matières de la formule");
							}else{
								matieres = Object.values(matieres);
								matieres.forEach(function(matiere){
									jQuery("option[value='".concat(matiere,"']")).prop('selected', true);
								});
							}
							jQuery('#nf-field-'.concat(idSelectMatiere)).change();
							jQuery('#nf-field-'.concat(idSelectNiveau)).change();

							console.log("après set dmatières");


							// si il y a déjà des classes, les checker
							if(classes){
								reset = false;
								classes = Object.values(classes);
								classes.forEach(function(classe){
									jQuery("option[value='".concat(classe,"']")).prop('selected', true).change();
								});
							}

							console.log("après set classes");



							console.log(retour.formule);

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
							jQuery("#loadingSpinner").addClass("hide");
							jQuery("#onglets").removeClass("hide");
						}
					});

		}

		// on change matieres
		if ( idSelectMatiere == model.get( 'id' )) {


			console.log(" --- matières ----");
			console.log(value);

			hideMessage();

			if(!value){
				return;
			}

			if(typeof(value) == 'string'){
				return; // pour ne pas charger l'ajax à l'intialisation
			}



			if(value.length == 0){
				return; // pour ne pas charger l'ajax si pas de matières sélectionnées
			}




			jQuery("#loadingSpinner").removeClass("hide");
			jQuery("#onglets").addClass("hide");



			console.log("params avant récup busy niveaux");
			console.log(value);
			console.log(refFormule);


			// pour réinitialliser le select niveaux
			inputs = Object.values(jQuery('.select-niveaux option'));
			inputs.forEach(function(input) {

				jQuery(input).prop('disabled', false);

			});


			if(reset){

				inputs = Object.values(jQuery('.select-niveaux option'));
				inputs.forEach(function(input) {

					jQuery(input).prop('selected', false)
				});
				jQuery('#nf-field-'.concat(idSelectNiveau)).change();


			}else{
				reset = true;
			}



			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxGetBusyLevels',
						'matieres' : value,
						'refFormule' : refFormule
					})
					.done(function(retour){

						if(!retour.error){







							var niveaux = Object.values(retour.niveaux);

							console.log("busy niveaux");
							console.log(niveaux);
							console.log(retour.matieres);





							niveaux.forEach(function(niveau) {


								jQuery("option[value='".concat(niveau,"']")).prop('disabled', true);

							});

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
							jQuery("#loadingSpinner").addClass("hide");
							jQuery("#onglets").removeClass("hide");
						}
					});

		}




//		gClasseSelect = jQuery(toFieldId(idClasseBis));

	}

});



//début jquery
jQuery( document ).ready( function( $ ) {

	new mySubmitController();
	new myCustomFieldController();

});
