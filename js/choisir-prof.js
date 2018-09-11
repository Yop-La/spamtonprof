/*
 * script chargé sur la page dont le slug est choisir-prof
 *  
 * 
 */



idForm = "69";
idFormContentEssai = "#nf-form-".concat(idForm, "-cont");

idRefAbonnement = "967";
idChoixProf = "968";



ajaxEnCours = 0;

attributionCourante = null;

var mySubmitController = Marionette.Object.extend( {

	initialize: function() {
		this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
	},

	actionSubmit: function( response ) {

		// titles form
		if(response.data.form_id == idForm){
			jQuery("#fountainTextG").removeClass("hide");
			jQuery(".hide_loading").addClass("hide");


			//récupérationdes variables du form

			refAbonnement = response.data.fields[idRefAbonnement].value;
			refProf = response.data.fields[idChoixProf].value;

			// soumission ajax des champs du form pour création inscription
			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxAttribuerProf',
						'refAbonnement' : refAbonnement,
						'refProf' : refProf,

					})
					.done(function(retour){ 

						error = retour.error;

						if(!error){
							profChoisi = retour.prof;
							showMessage("Le prof ( ".concat(profChoisi.prenom," ",profChoisi.nom," )" ," est bien attribué. C'est encore possible de le changer si besoin"));
							attributionCourante.prof = profChoisi;
							fillAttribution(attributionCourante);
						}


					})
					.fail(function(err){

						showMessage("Erreur : essayer de recharger la page ou de voir avec le ou les dev(s)");

					})
					.always(function(err){
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#fountainTextG").addClass("hide");
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

//début jquery
jQuery( document ).ready( function( $ ) {

	nbAttribution = abonnementsSansProf.length;

	if(nbAttribution == 0){

		waitForEl(".row-choix-prof, .previous, .next, .pas_attribution", function() {

			jQuery(".row-choix-prof, .previous, .next").remove();
			jQuery(".pas_attribution").removeClass("hide");

		});

	}else{

		var indiceCourant = 0;
		attributionCourante =  abonnementsSansProf[indiceCourant];

		fillAttribution(attributionCourante);
		updateIndice(indiceCourant, nbAttribution);

		waitForEl(".previous", function() {

			jQuery(".previous").click(function(){
				hideMessage();
				if(indiceCourant == 0){
					showMessage("Oups, pas possible d'accéder à l'attribution précédente car c'est la première.")
				}else{

					indiceCourant--;
					attributionCourante =  abonnementsSansProf[indiceCourant];

					fillAttribution(attributionCourante);
					updateIndice(indiceCourant, nbAttribution);

				}

			});

		});

		waitForEl(".next", function() {

			jQuery(".next").click(function(){
				hideMessage()
				if(indiceCourant == nbAttribution-1){
					showMessage("Oups, pas possible d'accéder à l'attribution suivante car c'est la dernière.")
				}else{

					indiceCourant++;
					attributionCourante =  abonnementsSansProf[indiceCourant];

					fillAttribution(attributionCourante);
					updateIndice(indiceCourant, nbAttribution);

				}
			});

		});







		new mySubmitController();
	}
});

function fillAttribution(attributionCourante){


	waitForEl(".row-choix-prof", function() {


		jQuery(".row-choix-prof").find("#profil").html(attributionCourante.eleve.profil.profil);
		jQuery(".row-choix-prof").find("#prenom-nom").html(attributionCourante.eleve.prenom.concat(' ',attributionCourante.eleve.nom));
		jQuery(".row-choix-prof").find("#date-creation").html(attributionCourante.date_creation);
		jQuery(".row-choix-prof").find("#matieres").html(attributionCourante.formule.formule);
		jQuery(".row-choix-prof").find("#classe").html(attributionCourante.eleve.classe.nom_complet);
		jQuery(".row-choix-prof").find("#remarques").html(attributionCourante.remarque_inscription);

		affichageProf = "aucun";
		if(attributionCourante.prof){
			prof = attributionCourante.prof;
			prenom = prof.prenom;
			nom = prof.nom;
			affichageProf = prenom.concat(" ", nom)  ;
		}

		jQuery(".row-choix-prof").find("#prof").html(affichageProf);

	});


	waitForEl(toFieldId(idRefAbonnement), function() {


		jQuery(toFieldId(idRefAbonnement)).val(attributionCourante.ref_abonnement);
		jQuery(toFieldId(idRefAbonnement)).trigger('change');


	});
}

function updateIndice(indiceCourant, nbAttribution){
	waitForEl("#indexFin", function() {
		jQuery("#indexCourant").html(indiceCourant+1);
		jQuery("#indexFin").html(nbAttribution);
	});
}
