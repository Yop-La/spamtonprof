





var selectNiveau = false;
var selectMatiere = false;

var matiere;
var niveau;

var ajaxEnCours = 0;

var nbFormule = 0;

var rowProfilHtml; //contient le html du profil du prof (photo + texte)

var matiereBoxTemplate;

var formules = null;
var formuleChoisie = null; // formule choisie après clic sur s'inscrire
var popEssaiId = 19758;

var niveau = null;
var matiere = null;

var idFormEssai = 80;



var mySubmitController = Marionette.Object.extend( {

	initialize: function() {
		this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
	},

	actionSubmit: function( response ) {


		// form essai
		if(response.data.form_id == idFormEssai){
			jQuery("#loadingSpinner").removeClass("hide");
			jQuery(".content").addClass("hide");
			jQuery("#res_recherche").addClass('hide');
			jQuery("#no_res").addClass('hide');

			PUM.close(popEssaiId);


			fields = response.data.fields;

			console.log('fields');
			console.log(fields);

			champs = {};

			Object.values(fields).forEach(function(field){

				champs[field.label] = field.submitted_value;
				if(typeof field.submitted_value =='undefined' || field.submitted_value == ""){
					champs[field.label] = field.value;
				}


			})

			console.log('champs')
			console.log(champs)

			// soumission ajax des champs du form pour création inscription
			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'inscriptionEssai',
						'fields' : JSON.stringify(champs),
					})
					.done(function(retour){ 

						console.log(retour);

						error = retour.error;
						message = retour.message;

						if(error){


							ajaxEnCours--;
							if(ajaxEnCours == 0){



								if(message == "compte_existe_deja"){

									redirectTo("semaine-decouverte" ,"Oops : vous avez déjà un compte. Connectez vous pour faire une autre inscription ! " );
								}else if(message == "essai_deja_fait"){

									redirectTo("semaine-decouverte" ,"Oops : cous avez déjà fait un essai pour cette matière ! Venez en parler avec nous." );
								}else if(message == "deja_2_essai"){

									redirectTo("semaine-decouverte" ,"Oops : il y a déjà 2 essais en cours ! Revenez quand au moins un essai sera fini" );
								}else if(message == "eleve_deja_essai"){

									redirectTo("semaine-decouverte" ,"Oops : tu es déjà entrain de faire un essai. Reviens quand tu auras fini." );
								}else if(message == "eleve_existe_deja"){

									redirectTo("semaine-decouverte" ,"Oops : l'élève renseigné a déjà un compte. Sélectionnez le lors de l'inscription." );
								}else if(message == "parent_pas_eleve"){

									redirectTo("semaine-decouverte" ,"Oops : un parent ne peux pas s'inscrire en tant qu'élève. Venez en discuter avec nous." );
								}

							}

						}else{

							redirectTo("remerciement-eleve" ,"Félicitations. Tu pourras démarrer la semaine de découverte dans 1 jour !" );



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


	}

});





//Create a new object for custom validation of a custom field.
var myCustomFieldController = Marionette.Object.extend( {

	initialize: function() {

		// Listen to the render:view event for a field type. Example: Textbox field.
		this.listenTo( nfRadio.channel( 'textarea' ), 'render:view', this.renderViewTextArea );
		this.listenTo( nfRadio.channel( 'textbox' ), 'render:view', this.renderViewTextBox );
		this.listenTo( nfRadio.channel( 'html' ), 'render:view', this.renderViewHtml );
	},

	renderViewTextArea: function( view ) {


		var el = jQuery( view.el ).find( '.nf-element' );

		var index = 0;
		for(var i=1;i<=10;i++){
			if(jQuery(el).hasClass('matiere'.concat(i))){
				index = i;
				break;
			}
		}
		if(index == 0){
			return(false);
		}

		console.log('matiere : ')
		console.log(index);



		nomMatiere = formuleChoisie.matieres[index-1].matiere_complet.toLowerCase()

		placeholder = jQuery(el[0]).attr('placeholder')
		console.log('placeholder');
		console.log(placeholder);
		placeholder = placeholder.replace(new RegExp('matiere'.concat(index), 'g'),nomMatiere);

		jQuery(el[0]).attr('placeholder',placeholder)


		// Do stuff.
	},	renderViewTextBox: function( view ) {


		var el = jQuery( view.el ).find( '.nf-element' );

		var index = 0;
		for(var i=1;i<=10;i++){
			if(jQuery(el).hasClass('matiere'.concat(i))){
				index = i;
				break;
			}
		}
		if(index == 0){
			return(false);
		}

		nomMatiere = formuleChoisie.matieres[index-1].matiere.toLowerCase()


		jQuery(el).val(nomMatiere).change();




		// Do stuff.
	},	renderViewHtml: function( view ) {


		var el = jQuery( view.el );

		var index = 0;
		for(var i=1;i<=10;i++){
			if(jQuery(el).find('.matiere'.concat(i)).length != 0){
				index = i;
				break;
			}
		}
		if(index == 0){
			return(false);
		}

		//on remplace par le nom de matière
		nomMatiere = formuleChoisie.matieres[index-1].matiere_complet.toLowerCase()

		h5 = jQuery(el).find('h5').text();
		h5 = h5.replace('matiere'.concat(index),nomMatiere);
		jQuery(el).find('h5').text(h5);


	}


});



function formInit(){



	if(isLogged != "true"){

		waitForEl(".prospect_checkbox", function() {

			jQuery('.prospect_checkbox').prop('checked', true).change()

		});
	}else{
		waitForEl(".prospect_checkbox", function() {

			jQuery('.prospect_checkbox').prop('checked', false).change()

		});		

	}

	jQuery('#nf-field-1101').val('').change();

}

jQuery( document ).ready( function( jQuery ) {

	// pour mettre le formulaire soit en mode essai prospect soit en mode essai client

	formInit();



	// pour réinitialiser à la fermeture de la popup
	jQuery('#pum-'.concat(popEssaiId))
	.on('pumBeforeClose', function () {

		jQuery('.nf-breadcrumb[data-index="0"]').click().change()

	});


	new myCustomFieldController();

	new mySubmitController();

	// charger row profil dans matiere box template et sauvegarder le toal
	rowProfilHtml = jQuery('.row-profil').html();
	jQuery('.row-profil').remove();
	jQuery(".matiere-html .wpb_wrapper").html(rowProfilHtml);
	jQuery('.row-profil').removeClass('hide');
	matiereBoxTemplate = jQuery('#matiere-box-template')


	launchSearch('selectNiveau', 'selectMatiere');


	// pour lancer automatiquement la recherche quand les champs de recherche sont complétés
	function launchSearch(validNiveauInput, validMatiereInput){

		var fieldNiveauId = '#aa-niveau';
		var fieldMatiereId = '#aa-matiere';

		if(isOnMobile()){
			fieldNiveauId = '#aa-matiere-mobile';
			fieldMatiereId = '#aa-niveau-mobile';
		}

		waitForEl(fieldMatiereId,function(){
			console.log(fieldMatiereId);
			jQuery(fieldMatiereId).change(function(){
				hideMessage();

				console.log('change matieere');


				if(selectNiveau && selectMatiere){

					search();

				}

			});

		});

		waitForEl(fieldNiveauId,function(){
			console.log(fieldNiveauId);

			jQuery(fieldNiveauId).change(function(){
				hideMessage();

				console.log('change niveau');

				if(selectNiveau && selectMatiere){

					search();

				}

			});

		});
	}

	function search(){

		//on enlève les marqueurs d'erreurs de la barre de recherche mobile
		jQuery('.erreur-recherche').addClass('hide');
		jQuery('.mobile-search-bar .iwithtext').removeClass('red-border');

		//fermer la popup de recherche sur mobile
		PUM.close(19581);

		//lancement de la fonction de recherche
		// on balance une barre de chargement
		// on ferme la popup sur mobile
		// on bouge la formule démo
		//on charge les formules  
		console.log('recherche');
		matiere = getMatiereFieldValue();
		niveau = getNiveauFieldValue();

		jQuery("#loadingSpinner").removeClass("hide");
		jQuery(".content").addClass("hide");
		jQuery("#res_recherche").addClass('hide');
		jQuery("#no_res").addClass('hide');


		ajaxEnCours++;
		jQuery.post(
				ajaxurl,
				{
					'action' : 'ajaxGetFormules',
					'niveau' : niveau,
					'matiere' : matiere

				})
				.done(function(retour){

					console.log(retour);

					if(!retour.error){
						console.log("dans done sans erreur");
						console.log(retour);
						formules = retour.formules;
						niveau = retour.niveau;
						matiere = retour.matiere;
						console.log(formules);


						if(niveau.parent_required && isLogged == "false"){

							waitForEl(".parent_required_checkbox", function() {

								jQuery('.parent_required_checkbox').prop('checked', true).change()

							});
						}else{

							waitForEl(".parent_required_checkbox", function() {

								jQuery('.parent_required_checkbox').prop('checked', false).change()

							});

						}

						//chargement des formules

						nbFormule = formules.length;

						// on enlève le template et les tous les blocs 
						jQuery('.matiere-box').remove();


						if(nbFormule == 0){ //si pas de formules
							showMessage("Aucune formule ne correspond à la matière et au niveau demandé");

							jQuery('#keyword').text(getMatiereFieldValue().concat(' - ',getNiveauFieldValue()));


							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".content").removeClass("hide");
							jQuery("#res_recherche").addClass('hide');
							jQuery("#no_res").removeClass('hide');


						}else{ //si il y a des formules


							// pour afficher le nombre de résultats et aller dessus
							;
							jQuery(".nb_formule").text(nbFormule.toString().concat(' formule(s)'));


							var i = 0;
							formules.forEach(function(formule){

								prenom = formule.prof.prenom;

								// création du bloc prof + attribution id
								idBloc = 'formule-'.concat(formule.ref_formule);
								var blocProf = jQuery(matiereBoxTemplate).clone();
								blocProf.attr('id',idBloc).removeClass('hide');


								// edition des matières en titre
								matieres = formule.formule.split("|");
								matieres = matieres[0];
								jQuery(blocProf).find('.nom-matiere').text(matieres);

								// edition des matières en titre
								description = formule.prof.description;
								description = description.replace(/\[prenom-prof\]/g, prenom);
								description = description.replace(/\[liste-matieres\]/g, matieres);
								jQuery(blocProf).find('.content-matieres').html(description);

								//edition du prenom
								jQuery(blocProf).find('.sous-titre-prof strong').text('Avec '.concat(prenom));

								//edition de l'image
								jQuery(blocProf).find('.profil_image').attr("src",formule.prof.image_url);




								jQuery(blocProf).find(".pop-essai").attr('indexFormule',i);
								i++;

								jQuery(blocProf).find(".pop-essai").click(function(){
									PUM.open(popEssaiId);
									indexFormule = jQuery(this).attr('indexFormule');
									formuleChoisie = formules[indexFormule];

									//remplir le form d'inscription avec
									// le nombre de formule
									nbFormule = formuleChoisie.matieres.length;
									jQuery('.nb_matiere').val(nbFormule).change();
									// le niveau
									jQuery('.matiere_input').val(JSON.stringify(matiere)).change();
									jQuery('.niveau_input').val(JSON.stringify(niveau)).change();
									jQuery('.formule_input').val(JSON.stringify(formuleChoisie)).change();
									//
								})



								//insertion du bloc prof
								jQuery('.col-profils').append(blocProf);


							});
						}






					}else{
						showMessage(retour.message);

						jQuery("#loadingSpinner").addClass("hide");
						jQuery(".content").removeClass("hide");
						jQuery("#res_recherche").addClass('hide');
						jQuery("#no_res").addClass('hide');




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
						jQuery(".content").removeClass("hide");
					}
					if(nbFormule == 0){
						jQuery("#no_res").removeClass('hide');
					}else{
						jQuery("#res_recherche").removeClass('hide');


					}
					document.getElementById('find-formule').scrollIntoView();

				});


		//appel ajax pour récupérer toutes les formules correspondants à la recherche avec les profs !



	}

	function getNiveauFieldValue(){
		var niveauField;
		if(isOnMobile()){
			niveauField = '#aa-niveau-mobile';
		}else{
			niveauField = '#aa-niveau';
		}
		return(jQuery(niveauField).val());

	}

	function getMatiereFieldValue(){
		var searchBar1Id;
		if(isOnMobile()){
			matiereField = '#aa-matiere-mobile';
		}else{
			matiereField = '#aa-matiere';
		}
		return(jQuery(matiereField).val());
	}

	waitForEl('.content-matieres', function() {
		jQuery('.content-matieres').removeClass("vc_custom_1541235203923");
	});




	jQuery('.rechercher').click(function(){

		handleSearchErrorMessage();
		if(!selectMatiere){
			invalidSearchField('#aa-matiere', 'matiere');
		}
		if(!selectNiveau){
			invalidSearchField('#aa-niveau', 'niveau');
		}

		if(selectNiveau && selectMatiere){

			search();

		}

	});

	jQuery('.rechercher_mobile').click(function(){

		handleSearchErrorMessage();
		if(!selectMatiere){
			invalidSearchField('#aa-matiere-mobile', 'matiere');
		}
		if(!selectNiveau){
			invalidSearchField('#aa-niveau-mobile', 'niveau');
		}

		if(selectNiveau && selectMatiere){

			search();

		}
	});

	jQuery('.pop-essai').click(function(){

		handleSearchErrorMessage();
		if(isOnMobile()){
			handleMobileSearchBarError();
		}

		if(!selectMatiere){
			invalidSearchField('#aa-matiere', 'matiere');
		}
		if(!selectNiveau){
			invalidSearchField('#aa-niveau', 'niveau');
		}
	});





	// ne pas mettre les encarts rouges quand click inscription essai sans avoir rechercher
	// enlever l'encart rouge sur rechercher mobile quand recherche faite


	//initialisation des barres de recherches et de l'autocomplete
	initSearchBox('#aa-matiere', 'matiere', 'matiere_complet','selectMatiere');
	initSearchBox('#aa-niveau', 'niveau', 'niveau','selectNiveau');

	initSearchBox('#aa-matiere-mobile', 'matiere', 'matiere_complet','selectMatiere');
	initSearchBox('#aa-niveau-mobile', 'niveau', 'niveau','selectNiveau');


	function isNiveauSet(){
		return(getNiveauFieldValue() != "");
	}


	function isOnMobile(){
		return(jQuery('.search-prof-popup').css('display') != "none");
	}

	function isMatiereSet(){

		return(getMatiereFieldValue() != "");

	}

	function handleMobileSearchBarError(){

		var	fieldsToColor = ['.mobile-search-bar .iwithtext'];

		if(selectMatiere && selectNiveau){
			fieldsToColor = [];
		}else{
			jQuery('.erreur-recherche').removeClass('hide');
		}

		fieldsToColor.forEach(function(element) {
			jQuery(element).addClass('red-border');
		});
	}

	// pour mettre en jour les champs de recherche et afficher un message si champs vides
	function handleSearchErrorMessage(){
		var message = "";

		//affichage du message commun
		if(selectMatiere && selectNiveau){
			hideMessage();
		}else if(!selectMatiere && selectNiveau){
			message = "Veuillez d'abord choisir une matière";
		}else if(selectMatiere && !selectNiveau){
			message = "Veuillez d'abord choisir un niveau";
		}else{
			message = "Veuillez d'abord choisir un niveau et une matière";
		}

		if(message != ""){
			showMessage(message);
		}

	}

	function initSearchBox(searchBoxId, indexName, toHighLight, isValidInput){

		var client = algoliasearch('3VXJH73YCI', '679e64fbe87fa37d0d43e1fbb19e45d8');
		var index = client.initIndex(indexName);

		var boxHasOpen = false;

		//initialize autocomplete on search input (ID selector must match)
		autocomplete(searchBoxId,
				{ hint: false, autoselect: true, openOnFocus: true}, {
					source: autocomplete.sources.hits(index, {hitsPerPage: 5}),
					//value to be displayed in input control after user's suggestion selection
					displayKey: toHighLight,

					//hash of templates used when rendering dataset
					templates: {
						//'suggestion' templating function used to render a single suggestion
						suggestion: function(suggestion) {

							jQuery(searchBoxId).removeClass("bottom-black-border");



							boxHasOpen = true;

							return '<span>' +

							suggestion._highlightResult[toHighLight].value + '</span><span>'




						}
					}
				}).on('autocomplete:selected', function(event, suggestion, dataset) {
					jQuery(searchBoxId).addClass("bottom-black-border");
					jQuery(searchBoxId).addClass("not-bottom-black-border");
					window[isValidInput] = true;

					jQuery(searchBoxId).removeClass("red-border");

					jQuery(".erreur-".concat(indexName)).removeClass('hide');
					jQuery(".erreur-".concat(indexName)).addClass('hide');




				}).on('autocomplete:empty', function() {
					if(!jQuery(searchBoxId).hasClass("red-border")){
						jQuery(searchBoxId).addClass("bottom-black-border");
					}
					window[isValidInput] = false;
				}).on('autocomplete:opened', function(val) {




					console.log('ouvert');

					jQuery(searchBoxId).removeClass("bottom-black-border");
					waitForEl('.aa-suggestion', function() {
						nbSuggestion = jQuery('.aa-suggestion').length;
						suggest = jQuery('.aa-suggestion').text();

						if(nbSuggestion == 1 && suggest.toLowerCase() == jQuery(val.target).val().toLowerCase()){

							window[isValidInput] = true;
						}else{
							window[isValidInput] = false;
						}
					});


				}).on('autocomplete:updated', function(val) {

					waitForEl('.aa-suggestion', function() {



						if(jQuery('.aa-suggestions').parents("#aa-input-container").find(searchBoxId).length){
							nbSuggestion = jQuery('.aa-suggestion').length;
							suggest = jQuery('.aa-suggestion').text();
							if(nbSuggestion == 1 && suggest.toLowerCase() == jQuery(val.target).val().toLowerCase()){
								window[isValidInput] = true;
							}else{
								window[isValidInput] = false;
							}
						}
					});
				}).on('autocomplete:empty', function() {
					window[isValidInput] = false;
				});



		jQuery(searchBoxId).keyup(function() {

			if (!this.value && !jQuery(searchBoxId).hasClass("red-border")) {
				jQuery(searchBoxId).addClass("bottom-black-border");
				window[isValidInput] = false;

			}else{
				boxHasOpen = false;
			}
		});

		if(jQuery(searchBoxId).focus(function(){

			// pour mettre les bords plus en évidence lors de la saisie

			if(!jQuery(searchBoxId).hasClass("red-border") && !boxHasOpen){
				jQuery(searchBoxId).addClass("not-bottom-black-border");
				jQuery(searchBoxId).addClass("bottom-black-border");
			}else if(!jQuery(searchBoxId).hasClass("red-border") && boxHasOpen){
				jQuery(searchBoxId).addClass("not-bottom-black-border");

			}
			boxHasOpen = false;
		}));

		jQuery(searchBoxId).focusout(function(){

			if(!window[isValidInput]){
				invalidSearchField(searchBoxId, indexName);
			}else{
				jQuery(searchBoxId).removeClass("red-border");
				jQuery(".erreur-".concat(indexName)).addClass('hide');

			}

			jQuery(searchBoxId).removeClass("not-bottom-black-border");
			jQuery(searchBoxId).removeClass("bottom-black-border");

		});



	}

	function invalidSearchField(searchBoxId, indexName){
		jQuery(searchBoxId).addClass("red-border");
		jQuery(".erreur-".concat(indexName)).removeClass('hide');

	}




});