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
			jQuery("#loading_screen").removeClass("hide");
			jQuery(".hide_loading").addClass("hide");

			//récupération des variables du form
			fields = response.data.fields;
			champs = {};
			Object.values(fields).forEach(function(field){

				champs[field.label] = field.submitted_value;
				if(typeof field.submitted_value =='undefined' || field.submitted_value == ""){
					champs[field.label] = field.value;
				}


			})

			// soumission ajax des champs du form pour création inscription
			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxAttribuerProf',
						'fields' : JSON.stringify(champs)

					})
					.done(function(retour){ 


						error = retour.error;

						if(!error){
							statut = retour.statut;
							action = retour.action;

							message = "Rien à dire. Bizarre ... Prévenez un dev !!";
							if(action == 'refuser-la-demande'){
								message = "La demande vient d'être refusé ! Ce n'est plus possible de changer. Les emails sont partis.";
								redirectTo('back-office/choisir-prof',message);
								
							}else if(action == "compte-test"){
								message = "Le compte de test vient d'être désactivé. C'est encore possible de changer si besoin";
							}else if(action == 'attribuer-un-prof'){
								profChoisi = retour.prof;
								message = "Le prof ( ".concat(profChoisi.prenom," ",profChoisi.nom," )" ," est bien attribué. C'est encore possible de le changer si besoin");
								attributionCourante.prof = profChoisi;
							}
							showMessage(message);

							attributionCourante.statut = statut;
							fillAttribution(attributionCourante);
						}


					})
					.fail(function(err){

						showMessage("Erreur : essayer de recharger la page ou de voir avec le ou les dev(s)");

					})
					.always(function(err){
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loading_screen").addClass("hide");
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

		console.log(attributionCourante);

		jQuery(".row-choix-prof").find("#email-eleve").html(attributionCourante.eleve.email);

		jQuery(".row-choix-prof").find("#ref-abonnement").html(attributionCourante.ref_abonnement);
		jQuery(".row-choix-prof").find("#date-inscription").html(attributionCourante.date_creation);


		if(attributionCourante.proche){
			jQuery(".row-choix-prof").find("#email-parent").html(attributionCourante.proche.email);
		}else{
			jQuery(".row-choix-prof").find("#email-parent").html('...');
		}

		jQuery(".row-choix-prof").find("#date-demarrage").html(attributionCourante.debut_essai);

		jQuery(".row-choix-prof").find("#statut-abo").html(attributionCourante.statut.statut_abonnement);

		jQuery(".row-choix-prof").find("#prenom-nom").html(attributionCourante.eleve.prenom.concat(' ',attributionCourante.eleve.nom));
		jQuery(".row-choix-prof").find("#matieres").html(attributionCourante.formule.formule);
		jQuery(".row-choix-prof").find("#classe").html(attributionCourante.eleve.niveau.niveau);
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
