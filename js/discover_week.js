/*
 * script chargé sur la page dont le slug est discover_week
 */

matiere  = "maths-physique"; // contient la matiere affiché (choisie)
matieres  = "maths-physique".split("-");
ajaxEnCours = 0;


//id des champs du formulaire

idFormEssai = "64";
idFormContentEssai = "#nf-form-".concat(idFormEssai, "-cont");

idPrenomEleve = "915";
idNomEleve = "916";
idEmailEleve = "918";
idPhoneEleve = "919";
idProfil = "948";
idClasse = "949";

idPrenomEleve = "915";
idNomEleve = "916";
idEmailEleve = "918";
idPhoneEleve = "919";
idProfil = "948";
idClasse = "949";

idChapterMaths = "920";
idLacuneMaths = "921";
idNoteMaths = "924";

idChapterPhysique = "928";
idLacunePhysique = "929";
idNotePhysique = "930";

idChapterFrench = "943";
idLacuneFrench = "947";
idNoteFrench = "946";

idProche = "923";
idPrenomProche = "931";
idNomProche = "934";
idMailProche = "932";
idPhoneProche = "935";
idRemarque 	= "938";

idCode = "941";

idMathsCoche = "953";
idPhysiqueCoche = "953";
idFrenchCoche = "955";


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
			$("#fountainTextG").removeClass("hide");
			$(idFormContentEssai).addClass("hide");


			prenomEleve = response.data.fields[idPrenomEleve].value;
			nomEleve = response.data.fields[idNomEleve].value;
			emailEleve = response.data.fields[idEmailEleve].value;
			phoneEleve = response.data.fields[idPhoneEleve].value;
			profil = response.data.fields[idProfil].value;
			classe = response.data.fields[idClasse].value;
			prenomEleve = response.data.fields[idPrenomEleve].value;
			nomEleve = response.data.fields[idNomEleve].value;
			emailEleve = response.data.fields[idEmailEleve].value;
			phoneEleve = response.data.fields[idPhoneEleve].value;
			profil = response.data.fields[idProfil].value;
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


			mathsCoche = response.data.fields[idMathsCoche].value;
			physiqueCoche = response.data.fields[idPhysiqueCoche].value;
			frenchCoche = response.data.fields[idFrenchCoche].value;

			


//			ajaxEnCours++;
//			jQuery.post(
//					ajaxurl,
//					{
//						'action' : 'ajaxGetTitles',
//						'typeTitle' : typeTitle 
//					})
//					.done(function(titles){ 
//						console.log("okay");
//
//						$('#csvTitles').empty();
//						$('#csvTitles').append('<a href="' .concat(titles.csvPath,'">Download as csv</a>'));
//
//						$("#titleTable tr").remove();
//
//						$.each(titles.titles, function (i, item) {
//
//
//							var table = document.getElementById("titleTable");
//							var row = table.insertRow(0);
//							var cell1 = row.insertCell(0);
//							cell1.innerHTML = item.titre;
//
//
//
//						});
//
//					})
//					.fail(function(err){
//						console.log("erreur ajax");
//						console.log(err);
//						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
//					})
//					.always(function() {
//						ajaxEnCours--;
//						if(ajaxEnCours == 0){
//							$("#fountainTextG").addClass("hide");
//							$("idFormContentEssai").removeClass("hide");
//						}
//					});

		}

	},

});


/*
 * fin : faire la soumission du formulaire de la popup 
 * 
 */

//début jquery
jQuery( document ).ready( function( $ ) {


	new mySubmitController();


	/* pour customiser le select des matières et changer l'affichage du blco matière en fonction du choix*/

	waitForEl('#choix-matieres', function() {
		
		$("#select-box1").val('maths-physique');

		$("select").on("click" , function() {

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


		$("select").on("change" , function() {

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






			});

		});


		/* fin : customiser le select des matières et changer l'affichage du blco matière en fonction du choix*/

		/* debut : pour afficher popup et faire les bons affichages de matière dans la deuxième partie du formulaire */

		waitForEl('.pop-essai', function() {


			$('.pop-essai').click(function(){

				PUM.open(18006);
				
				$($('.nf-breadcrumb')[0]).trigger('click');

				mathsCoche = toFieldId(idMathsCoche);
				physiqueCoche = toFieldId(idPhysiqueCoche);
				frenchCoche = toFieldId(idFrenchCoche);
				
				Array.from($(".matiere-coche")).forEach(function(element) {
					if($(element).is(':checked')){
						$(element).trigger('click');
					}
				});
				
				matieres.forEach(function(element) {
					console.log(element);
					$('.'.concat(element,"-coche")).trigger('click');
				});

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

//	jQuery.post(
//	ajaxurl,
//	{
//	'action' : 'ajaxGenerateAndSaveTexts',
//	'nomCatLoaded' : nomCatLoaded, 
//	})
//	.done(function(textCat){

//	loadTextesCat();

//	showMessage("100 textes ont générés. Retrouvez les dans l'onglet consultation des textes sous la catégorie ".concat(nomCatLoaded));
//	})
//	.fail(function(err){
//	console.log("erreur ajax exist");
//	console.log(err);
//	showMessage("Il y a un problème avec la génération des textes. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
//	return;
//	})
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
			console.log(nbShake);
			checkUntil(selector, nbShake);
		}, 5000);
	}
};

