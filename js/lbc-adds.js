/*
 * script chargé sur la page dont le slug est lbc-adds
 */
nbPara = 0;
refTextCat = -1;
ajaxEnCours = 0;
modeEdition = "ajout";
textRowTemplate = null;
textCol = null;
baseTextes = null;
nomCatLoaded = null;
texteId = null; // pour garder l'id du texte arpès click boutton 'copier' ou 'modifier' ou 'supprimer'
nbTextesWritted = null;
nbTextTot = null;

var mySubmitController = Marionette.Object.extend( {

	initialize: function() {
		this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
	},

	actionSubmit: function( response ) {
		console.log("form submitted");
		console.log(response);

		// titles form
		if(response.data.form_id == "55"){
			$("#loadingSpinner").removeClass("hide");
			$("#onglets").addClass("hide");

			typeTitle = response.data.fields["886"].value;
			console.log(typeTitle);

			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxGetTitles',
						'typeTitle' : typeTitle 
					})
					.done(function(titles){ 
						console.log("okay");

						$('#csvTitles').empty();
						$('#csvTitles').append('<a href="' .concat(titles.csvPath,'">Download as csv</a>'));

						$("#titleTable tr").remove();

						$.each(titles.titles, function (i, item) {


							var table = document.getElementById("titleTable");
							var row = table.insertRow(0);
							var cell1 = row.insertCell(0);
							cell1.innerHTML = item.titre;



						});

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
							$("#onglets").removeClass("hide");
						}
					});

		}

		// textes form
		if(response.data.form_id == "56"){
			console.log("textes form submitted");
			typeTexte = response.data.fields["888"].value;
			console.log(typeTexte);

			$("#loadingSpinner").removeClass("hide");
			$("#onglets").addClass("hide");
			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxGetTextes',
						'typeTexte' : typeTexte 
					})
					.done(function(textes){ 
						console.log("okay");

						$('#csvTextes').empty();
						$('#csvTextes').append('<a href="' .concat(textes.csvPath,'">Download as csv</a>'));

						$("#texteTable tr").remove();

						$.each(textes.textes, function (i, item) {

							console.log(item);
							var table = document.getElementById("texteTable");
							var row = table.insertRow(0);
							var cell1 = row.insertCell(0);
							cell1.innerHTML = item.texte;

						});

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
							$("#onglets").removeClass("hide");
						}
					});
		}

		// nouvelle catégorie de textes
		if(response.data.form_id == "57"){
			console.log("nouvelle cat texte submitted");
			nomCat = response.data.fields["890"].value;
			nbPara = response.data.fields["894"].value;
			nbTexte = response.data.fields["895"].value;

			if(!(isPositiveInteger(nbPara) && isPositiveInteger(nbTexte))){
				showMessage("Le nombre de paragraphes et de textes doit être un entier positive. Veuillez refaire votre saisie");
				return;
			}
			
			console.log(Math.pow(parseInt(nbTexte), parseInt(nbPara)) >= 10000000);
			
			if( Math.pow(parseInt(nbTexte), parseInt(nbPara)) >= 10000000){
				
				showMessage("Il y a trop de paragraphes ou de textes. Veuillez en choisir moins");
				return;				
				
			}

			$("#loadingSpinner").removeClass("hide");
			$("#onglets").addClass("hide");
			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxGetTexteCat',
						'nomCat' : nomCat, 
					})

					.done(function(textCat){ 
						console.log("text cat : ");
						console.log(textCat);
						if(textCat){
							console.log("texte existe");
							showMessage("Cette catégorie de textes existe déjà. Veuillez en choisir une autre");
							return;
						}
						jQuery.post(
								ajaxurl,
								{
									'action' : 'ajaxAddNewTexteCat',
									'nomCat' : nomCat, 
									'nbTexte' : nbTexte,
									'nbPara' : nbPara
								})
								.done(function(textCat){ 
									console.log("okay");
									console.log(textCat);
									showMessage("La catégorie de texte : ".concat(nomCat," est bien ajouté. Rdv vous dans l'onglet écrire des textes maintenant."));
									loadTextCat();
								})
								.fail(function(err){
									console.log("erreur ajax add");
									console.log(err);
									showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
									return;
								});


					})
					.fail(function(err){
						console.log("erreur ajax exist");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
						return;
					})
					.always(function() {
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							$("#loadingSpinner").addClass("hide");
							$("#onglets").removeClass("hide");
						}
					});


		}

		// chargement d'une cat de texte pour rédaction
		if(response.data.form_id == "58"){
			$("#loadingSpinner").removeClass("hide");
			$("#onglets").addClass("hide");
			$("#writing-area").removeClass("hide");
			console.log("chargement d'une cat de texte pour rédaction");
			nomCat = response.data.fields["897"].value;
			nomCatLoaded = nomCat;

			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxGetTexteCat',
						'nomCat' : nomCat, 
					})
					.done(function(textCat){ 
						if(textCat != "false"){
							console.log("texte existe");
							console.log(textCat);

							nbPara = textCat.nb_paragraph;
							refTextCat = textCat.ref_texte_cat;

							nbTextTot = textCat.nb_texte;
							
							$("#nbTexte").text(nbTextTot);
							$("#nbParagraphe").text(nbPara);
							$("#nomCat").text(textCat.nom_cat);


							nbMaxPar = $('#nf-form-59-cont textarea').size() - 1;
							$.each($('#nf-form-59-cont .nf-row'), function (i, item) {

								if(i>=nbPara && i <= nbMaxPar){
									$(item).remove();	
								}
							});

							loadTextCat();
							loadBaseTextes();
							$('#nf-field-897').prop(nomCatLoaded, true);
							
							
							
							showMessage("Vous pouvez maintenant voir, modifier et ajouter des textes à la catégorie choisie");

						}else{
							showMessage("Cette catégorie de texte n'existe pas. Veuillez en choisir une autre");
							return;							
						}


					})
					.fail(function(err){
						removeLoadingSpinner = true;
						console.log("erreur ajax exist");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
						return;
					})
					.always(function() {
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							$("#loadingSpinner").addClass("hide");
							$("#onglets").removeClass("hide");
						}
					});
		}

		// ajout ou modification d'un texte
		if(response.data.form_id == "59"){
			$("#loadingSpinner").removeClass("hide");
			$("#onglets").addClass("hide");
			console.log("demande d'éditiion d'un texte");

			pars = {};

			for(var i = 0; i< nbPara ; i++){

				var j = 900 + i;
				par = {};
				par["paragraph"] = response.data.fields[j].value;
				par["indice"] = i;
				pars[i] = par;
				if(par["paragraph"] == ""){
					showMessage("Il y a un problème. Le paragraphe ".concat(i+1, " est vide"));
					return;
				}
			}
			console.log("paras");
			console.log(pars);
			
			
			if( modeEdition == "ajout"){

				ajaxEnCours++;
				jQuery.post(
						ajaxurl,
						{
							'action' : 'ajaxAddLbcParas',
							'paragraphs' : pars, 
							'refTexteCat' : refTextCat, 
						})
						.done(function(textCat){
							console.log("paragraphes ajoutés");
							loadBaseTextes();
							showMessage("Le texte a été ajouté. Reste plus qu'à en écrire un autre ou bien à générer automatiquement les textes");
						})
						.fail(function(err){
							console.log("erreur ajax exist");
							console.log(err);
							showMessage("Il y a un problème avec l'ajout du texte. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
							return;
						})
						.always(function() {
							ajaxEnCours--;
							if(ajaxEnCours == 0){
								$("#loadingSpinner").addClass("hide");
								$("#onglets").removeClass("hide");
							}
						});
				
			}else if( modeEdition == "update"){
				
				ajaxEnCours++;
				
				jQuery.post(
						ajaxurl,
						{
							'action' : 'ajaxUpdateLbcParas',
							'paragraphs' : pars, 
							'refTexte' : texteId, 
						})
						.done(function(textCat){
							console.log("paragraphes mise à jour");
							loadBaseTextes();
							showMessage("Le texte a bien été mise à jour");
						})
						.fail(function(err){
							console.log("erreur ajax exist");
							console.log(err);
							showMessage("Il y a un problème avec la modification du texte. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
							return;
						})
						.always(function() {
							ajaxEnCours--;
							if(ajaxEnCours == 0){
								$("#loadingSpinner").addClass("hide");
								$("#onglets").removeClass("hide");
							}
						});
				
			}
			
		}



	},

});

//début jquery
jQuery( document ).ready( function( $ ) {

	
	loadTextesCat();

	waitForEl('.generateText', function() {
		$('.generateText').click(function(){
			
			if(!nomCatLoaded){
				
				showMessage("Il faut d'abord choisir une catégorie de textes avant de pouvoir en générer automatiquement." );
				return;
				
			}
			
			if(nbTextTot -nbTextesWritted <= 0){
				
				$("#loadingSpinner").removeClass("hide");
				$("#onglets").addClass("hide");
				
				showMessage("Génération des textes en cours ... " );
				
				ajaxEnCours++;
				
				jQuery.post(
						ajaxurl,
						{
							'action' : 'ajaxGenerateAndSaveTexts',
							'nomCatLoaded' : nomCatLoaded, 
						})
						.done(function(textCat){
							
							loadTextesCat();
							
							showMessage("100 textes ont générés. Retrouvez les dans l'onglet consultation des textes sous la catégorie ".concat(nomCatLoaded));
						})
						.fail(function(err){
							console.log("erreur ajax exist");
							console.log(err);
							showMessage("Il y a un problème avec la génération des textes. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
							return;
						})
						.always(function() {
							ajaxEnCours--;
							if(ajaxEnCours == 0){
								$("#loadingSpinner").addClass("hide");
								$("#onglets").removeClass("hide");
							}
						});
			}else{
				showMessage("Il faut encore écrire ".concat(nbTextTot -nbTextesWritted, " texte(s) afin de pouvoir en générer automatiquement"));
			}
		});
	});
	


	waitForEl('#text-row-template', function() {
		textRowTemplate = $( '#text-row-template' ).clone();
		$( '#text-row-template' ).remove();
	});

	waitForEl('#text-col', function() {
		textCol = $( '#text-col' );
	});

	waitForEl('#nf-form-59-cont', function() {
		$('#nf-form-59-cont').addClass("hide");
	});

	// chargement des options du formulaire des titres
	waitForEl('#nf-field-886', function() {


		$("#loadingSpinner").removeClass("hide");
		$("#onglets").addClass("hide");
		ajaxEnCours++;

		jQuery.post(
				ajaxurl,
				{
					'action' : 'ajaxGetAddsTitle'
				})
				.done(function(titleTypes){ 

					$.each(titleTypes, function (i, item) {
						$('#nf-field-886').append($('<option>', { 
							value: item,
							text : item 
						}));

					});
					$('#nf-field-886').prepend("<option value='' selected='selected'></option>");

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
						$("#onglets").removeClass("hide");
					}
				});



	});



	// chargement des options du formulaire de choix textes à rédiger
	
	loadTextCat();

	new mySubmitController();

});

function loadTextCat(){
	waitForEl('#nf-field-897', function() {
		$("#loadingSpinner").removeClass("hide");
		$("#onglets").addClass("hide");
		$('#nf-field-897')
		.find('option')
		.remove();

		ajaxEnCours++;
		jQuery.post(
				ajaxurl,
				{
					'action' : 'ajaxGetTexteCats'
				})
				.done(function(texteCats){ 
					$.each(texteCats, function (i, item) {
						console.log(item.nom_cat);
						$('#nf-field-897').append($('<option>', { 
							value: item.nom_cat,
							text : item.nom_cat 
						}));

					});
					$('#nf-field-897').prepend("<option value='' selected='selected'></option>");
					$('#nf-form-59-cont').removeClass("hide");
					$('#nf-field-897 option[value="'.concat(nomCatLoaded,'"]')).prop('selected', true);
				})
				.fail(function(err){
					console.log("erreur ajax chaargement catégorie de textes");
					console.log(err);
					showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
				})
				.always(function() {
					ajaxEnCours--;
					if(ajaxEnCours == 0){
						$("#loadingSpinner").addClass("hide");
						$("#onglets").removeClass("hide");
					}
				});


	});
}


function loadBaseTextes(){
	waitForEl('#text-col', function() {
		console.log("---------");
		console.log("load base texts");
		console.log("---------");
		$("#loadingSpinner").removeClass("hide");
		$("#onglets").addClass("hide");
		ajaxEnCours++;

		$(textCol).html('');
		
		jQuery.post(
				ajaxurl,
				{
					'action' : 'ajaxGetTexts',
					'refTexteCat' : refTextCat,
				})
				.done(function(textes){ 
					baseTextes = textes;

					nbTexte = 1;
					$.each(textes, function (key, value) {

						textRow = $(textRowTemplate).clone();
						$(textRow).attr('id',key);
						$(textRow).find("strong").html("Texte n° ".concat(nbTexte,'   '));
						$(textRow).find("a").attr("href","#top");
						textContent = "";
						$.each(value, function (i, item) {
							textContent = textContent.concat(' -- para ',i+1,' -- \n', item,'\n\n');

						});
						$(textRow).find(".text-content").text(textContent);
						$(textCol).append(textRow);
						nbTexte++;
					});

					$(".modifier-texte").click(function(){
						textRow = $(this).parent().parent();
						texteId = textRow.attr("id");
						fillTextForm();
						modeEdition = "update";
						showMessage("Texte ajouté dans le formulaire de rédaction. Faites vos modifications et soumettez le texte pour enregistrer vos modifications.");
						
					});
					$(".copier-texte").click(function(){
						modeEdition = "ajout";
						textRow = $(this).parent().parent();
						console.log(" this copy : ");
						console.log($(this));
						texteId = textRow.attr("id");
						fillTextForm();
						showMessage("Texte copié dans le formulaire de rédaction. Modifiez le et ajouter le pour enregistrer un nouveau texte à la catégorie choisie");

					});
					$(".supprimer-texte").click(function(){
						textRow = $(this).parent().parent();
						texteId = textRow.attr("id");

						
						ajaxEnCours++;
						
						jQuery.post(
								ajaxurl,
								{
									'action' : 'ajaxDeleteTexte',
									'refTexte' : texteId, 
								})
								.done(function(textCat){
									console.log("texte supprimé");
									loadBaseTextes();
									showMessage("Le texte a bien été supprimé");
								})
								.fail(function(err){
									console.log("erreur ajax exist");
									console.log(err);
									showMessage("Il y a un problème avec la suppression du texte. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
									return;
								})
								.always(function() {
									ajaxEnCours--;
									if(ajaxEnCours == 0){
										$("#loadingSpinner").addClass("hide");
										$("#onglets").removeClass("hide");
									}
								});
						
					
						
					});

					fillNbTextToWrite();
					
				})
				.fail(function(err){
					removeLoadingSpinner = false;
					console.log("erreur ajax chargement des textes de bases");
					console.log(err);
					showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
				})
				.always(function() {
					ajaxEnCours--;
					if(ajaxEnCours == 0){
						$("#loadingSpinner").addClass("hide");
						$("#onglets").removeClass("hide");
					}
				});

	});
}

function fillTextForm(){
	waitForEl('#nf-form-title-59', function() {
		console.log("fill text form");
		paras = baseTextes[texteId];
		console.log(texteId);
		console.log(baseTextes);
		$.each(paras, function (i, item) {
			$('#nf-field-'.concat(900+i)).val(item);
		});

	});
}

function fillNbTextToWrite(){
	waitForEl('#nbTexteRestant', function() {
		
		ajaxEnCours++;
		jQuery.post(
				ajaxurl,
				{
					'action' : 'ajaxCountTexts',
					'nomCatLoaded' : nomCatLoaded, 
				})
				.done(function(nbTextes){
					nbTextesWritted = nbTextes;
					$('#nbTexteRestant').text(nbTextTot - nbTextesWritted);
				})
				.fail(function(err){
					console.log("erreur ajax exist");
					console.log(err);
					showMessage("Il y a un problème avec la suppression du texte. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
					return;
				})
				.always(function() {
					ajaxEnCours--;
					if(ajaxEnCours == 0){
						$("#loadingSpinner").addClass("hide");
						$("#onglets").removeClass("hide");
					}
				});
		
		
	});
}

function loadTextesCat(){
	
	
	// chargement des options du formulaire des textes
	waitForEl('#nf-field-888', function() {

		$("#loadingSpinner").removeClass("hide");
		$("#onglets").addClass("hide");
		ajaxEnCours++;		
		jQuery.post(
				ajaxurl,
				{
					'action' : 'ajaxGetAddsTexteType'
				})
				.done(function(titleTypes){ 

					$.each(titleTypes, function (i, item) {
						$('#nf-field-888').append($('<option>', { 
							value: item,
							text : item 
						}));

					});
					$('#nf-field-888').prepend("<option value='' selected='selected'></option>");

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
						$("#onglets").removeClass("hide");
					}
				});

	});
	
	
}

