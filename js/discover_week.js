/*
 * script chargé sur la page dont le slug est discover_week
 */

matieres  = null; // contient la matières choisies

//début jquery
jQuery( document ).ready( function( $ ) {


	waitForEl('#choix-matieres', function() {

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

			checkUntil('.select-box' , 5);

		});

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



