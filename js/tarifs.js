





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

var niveau = null;
var matiere = null;

var idFormEssai = 80;


var searching = false;

var popupSearch = 20795;
if(domain != 'spamtonprof'){
	codePromoId = 1094;
	popupSearch = 20794;
}






jQuery( document ).ready( function( jQuery ) {


	showMessage("Utilisez la barre de recherche pour trouver le tarif qui vous va.");


//	//debut timer essai	


//	var timerEssai = localStorage.getItem("timerEssai");
//	console.log('timerEssai');
//	console.log(timerEssai);

//	var printTimer = true;

//	if(!timerEssai){

//	var timerEssai = new Date().getTime() + 5*24*60*60*1000;	
//	localStorage.setItem("timerEssai", timerEssai);

//	}

//	jQuery("#band-message").html('<div id = "promo_essai">7 jours d\'essai offerts dans la matière de votre choix </div><br> <div id = "expiration_essai"> Expire dans <span id = "counter-essai"></span><div>');

//	jQuery("#counter-essai").countdown(timerEssai, {elapse: true})
//	.on('update.countdown', function(event) {
//	var el = jQuery(this);
//	if (event.elapsed) {
//	hideMessage();
//	} else {
//	el.html(event.strftime('%D jours %H h %M min %S s'));
//	if(printTimer){
//	jQuery("#top-message").removeClass("hide");
//	window.scrollTo(0, 0);
//	printTimer = false;
//	}

//	}
//	});

//	//	fin timer essai



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

		if(searching){

			return;

		}
		searching = true;

		//on enlève les marqueurs d'erreurs de la barre de recherche mobile
		jQuery('.erreur-recherche').addClass('hide');
		jQuery('.mobile-search-bar .iwithtext').removeClass('red-border');

		jQuery('#mobile_search_bar').removeClass('init_mobile_search_bar');
		jQuery('#search_bar').removeClass('init_search_bar');

		//fermer la popup de recherche sur mobile
		PUM.close(popupSearch);

		//lancement de la fonction de recherche
		// on balance une barre de chargement
		// on ferme la popup sur mobile
		// on bouge la formule démo
		//on charge les formules  
		console.log('recherche');
		matiere = getMatiereFieldValue();
		niveau = getNiveauFieldValue();

		jQuery("#loading_screen").removeClass("hide");
		jQuery(".content").addClass("hide");
		jQuery("#res_recherche").addClass('hide');
		jQuery("#no_res").addClass('hide');



		jQuery("#formula_1").addClass('hide');
		jQuery("#formula_2").addClass('hide');
		jQuery("#formula_3").addClass('hide');



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


						//chargement des formules

						nbFormule = formules.length;

						// on enlève le template et les tous les blocs 
						jQuery('.matiere-box').remove();


						if(nbFormule == 0){ //si pas de formules
							showMessage("Aucune formule ne correspond à la matière et au niveau demandé");

							jQuery('#keyword').text(getMatiereFieldValue().concat(' - ',getNiveauFieldValue()));


							jQuery("#loading_screen").addClass("hide");
							jQuery(".content").removeClass("hide");
							jQuery("#res_recherche").addClass('hide');
							jQuery("#no_res").removeClass('hide');


						}else{ //si il y a des formules


							// pour afficher le nombre de résultats et aller dessus

							var formulaSelectors = []

							nbAdditionnalLine = Math.floor(nbFormule/3)

							jQuery(".nb_formule").text(nbFormule.toString().concat(' formule(s)'));

							nbFormuleFirstLine = nbFormule;

							if(nbAdditionnalLine  == 1){

								nbFormuleFirstLine = 3;

								nbFormuleSecondLine = nbFormule % 3

								var formulaSelector = '#formula_';
								formulaSelector = '#formula_' . concat(nbFormuleSecondLine);
								formulaSelectors.push(formulaSelector)
								jQuery(formulaSelector).removeClass('hide');
							}

							var formulaSelector = '#formula_';
							formulaSelector = '#formula_' . concat(nbFormuleFirstLine);
							jQuery(formulaSelector).removeClass('hide');
							formulaSelectors.push(formulaSelector)


							var i = 0;

							//remplissge colonne par colonne de la table des matières

							formules.forEach(function(formule){
								
						

								var formulaSelector  = formulaSelectors[formulaSelectors.length-1];
								

								if(i > 2){

									formulaSelector  = formulaSelectors[0];
									

									
								}
								


								j = i % 3;

								
								var formulaClass = '.matiere'.concat(j+1); 

								matieres = formule.formule.split('|');
								matieres = matieres[0];


								// ecriture des matières
								jQuery(formulaSelector.concat(" ",formulaClass)).text(matieres);
								
								console.log(formulaClass);

								//écriture du nom de la formule
								jQuery(formulaSelector.concat(' .pricing-column h3:eq( ',j,' )')).html('<div>'.concat('Formule ',niveau.niveau,'<br></div><div>',matieres,'</div>'));

								//écriture du prix
								jQuery(formulaSelector.concat(' .pricing-column .pricing-column-content h4:eq( ',j,' )')).html(''.concat(formule.defaultPlan.tarif, ' €'));

								//écriture de l'interval
								jQuery(formulaSelector.concat(' .pricing-column .interval:eq( ',j,' )')).html('Par semaine avec '.concat(formule.matieres.length,' matière(s) incluses'));






								// récupérer le niveau

								//récupérer le prix

								console.log("avant i++");
								console.log(i);
								i++;
							});
						}






					}else{
						showMessage(retour.message);

						jQuery("#loading_screen").addClass("hide");
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
						jQuery("#loading_screen").addClass("hide");
						jQuery(".content").removeClass("hide");
					}
					if(nbFormule == 0){
						jQuery("#no_res").removeClass('hide');
					}else{
						jQuery("#res_recherche").removeClass('hide');


					}
					document.getElementById('sub_heading').scrollIntoView();

					
					searching = false;
				});





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