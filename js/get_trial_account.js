
var montant = 20; // le montant du checkout par défaut
var emailCheckout = "alexandre@spamtonprof.com" ; // l'adresse mail par défaut du checkout
var refCompteCheckout = 9999999999 ; // la ref compte par défaut du checkout
var planStripe = "" ; // le plan stripe par défaut du checkout
var prenomEleve = "Cannelle" ; // le prénom retourné après soumisssion du checkout


// Create a new object for custom validation of a custom field.
var myCustomFieldController = Marionette.Object.extend( {
    initialize: function() {

        // on the Field's model value change...
        var fieldsChannel = Backbone.Radio.channel( 'fields' );
        this.listenTo( fieldsChannel, 'change:modelValue', this.validateOnChange );


        // On the Form Submission's field validaiton...
        // var submitChannel = Backbone.Radio.channel( 'submit' );
        // this.listenTo( submitChannel, 'validate:field', this.validateSubmit );

    },

    validateOnChange: function( model ) {

        var modelID       = model.get( 'id' );
        var errorID       = 'custom-field-error';
        var fieldsChannel = Backbone.Radio.channel( 'fields' );

        // Add Error
        fieldsChannel.request( 'remove:error', modelID, errorID );

        if(model.get( 'type' ) == "email" && model.get( 'id' ) == "824" && isEmail(model.get( 'value' ).trim())){
            var jQueryloading = jQuery('.loadingDiv').show();
            var jQueryformLoadForm = jQuery('#nf-form-48-cont').hide();
            var jQueryvalider = jQuery('.valider').hide();
            var jQueryformPaiement = jQuery('#nf-form-49-cont');
            var jQuerypayer = jQuery('.payer').hide();

            var choix1 = jQuery(".choix-formule li:nth-of-type(1)").hide();
            var choix2 = jQuery(".choix-formule li:nth-of-type(2)").hide();
            var choix3 = jQuery(".choix-formule li:nth-of-type(3)").hide();
            var choix = [choix1,choix2,choix3];

            jQuery.post(
                ajaxurl,
                {
                    'action': 'ajaxGetTrialAccount',
                    'email': model.get( 'value' ).trim()
                }
            )
                .done(function(comptes) {
                    if(comptes.length == 0){
                        /* afficher message pas de comptes - refaire la recherche */
                        jQueryformLoadForm.show();
                        jQueryvalider.show();
                        var modelID       = model.get( 'id' );
                        var errorID       = 'custom-field-error';
                        var errorMessage  = 'Aucun compte n’a été trouvé. Veuillez refaire la recherche ou contacter notre équipe.';
                        var fieldsChannel = Backbone.Radio.channel( 'fields' );
                        fieldsChannel.request( 'add:error', modelID, errorID, errorMessage );
                        /* message pas de compte trouvé  - refaire la recherche */    
                    }else{
                        jQueryformPaiement.show();
                        var jQuerychoixPaiement = jQuery('.choix-paiement').hide();
                        jQuerypayer.show();
                        jQuery(".fields-to-hide").hide(); //cacher les champs adresse_mail, tarifs et ref plan paiement stripe
                        var indiceCompte = 0;
                        comptes.forEach(function(compte) {
                            var choixCourant = choix[indiceCompte];
                            indiceCompte = indiceCompte+1;

                            jQuerylabel = "<span class = 'nom-formule'> Formule " + compte.planPaiement.formule.formule + "</span><br>";
                            jQuerylabel += "<span class = 'details-formule'>Pour " + compte.eleve.prenom + " - " + compte.eleve.classe + "<br>";
                            jQuerylabel += compte.planPaiement.tarif + " € par semaine</span>"; 


                            jQuery(choixCourant).find("label").html(jQuerylabel);
                            jQuery(choixCourant).find("input").attr("email-value",compte.proche.adresse_mail);
                            jQuery(choixCourant).find("input").attr("tarif",compte.planPaiement.tarif);
                            jQuery(choixCourant).find("input").attr("ref_plan_stripe",compte.planPaiement.ref_plan_stripe);
                            jQuery(choixCourant).find("input").attr("ref_compte",compte.ref_compte);
                            jQuery(choixCourant).find("input").attr("prenom_eleve",compte.eleve.prenom);
                            jQuery(choixCourant).find("input").val(compte.ref_compte);


                            jQuery(choixCourant).find("input").change(
                                function(){
                                    if (jQuery(this).is(':checked')) {
                                        montant = jQuery(this).attr('tarif');
                                        emailCheckout = jQuery(this).attr('email-value');
                                        refCompteCheckout = jQuery(this).attr('ref_compte');
                                        planStripe = jQuery(this).attr('ref_plan_stripe');
                                        prenomEleve = jQuery(this).attr('prenom_eleve');
                                    }
                                });


                            if(indiceCompte == 1){
                                jQuery(choixCourant).find("input").change();
                            }

                            choixCourant.show();
                        });

                    }

                })
                .fail(function() {
                    jQueryformLoadForm.show();
                    var modelID       = model.get( 'id' );
                    var errorID       = 'custom-field-error';
                    var errorMessage  = 'Aucun compte n’a été trouvé. Veuillez refaire la recherche ou contacter notre équipe.';
                    var fieldsChannel = Backbone.Radio.channel( 'fields' );
                    fieldsChannel.request( 'add:error', modelID, errorID, errorMessage );
                    /* message pas de compte trouvé - refaire la recherche */
                })
                .always(function() {
                    jQueryloading.hide();
                });

        }
    }
});


jQuery( document ).ready( function( $ ) {

    var jQueryloading = jQuery('.loadingDiv').hide();
    jQuery('#top-message').hide();
    var jQueryformLoadForm = jQuery('#nf-form-48-cont');
    var jQueryformPaiement = jQuery('#nf-form-49-cont').hide();
    var jQuerychoixPaiement = jQuery('.choix-paiement').hide();
    var jQuerypayer = jQuery('.payer').hide();

    new myCustomFieldController(); // pour contrôler le champs de saisie de l'email


    /** début formulaire de paiement stripe **/

    var handler = StripeCheckout.configure({
      key: 'pk_live_RGW2vHysvvXHwi1SkolaUPP9',
      image: 'https://spamtonprof.com/wp-content/uploads/2018/03/logo-stripe.png',
      locale: 'auto',
      allowRememberMe: false,
      token: function(token) {
        
        var jQueryloading = jQuery('.loadingDiv').show();
        jQuery('#top-message').hide();
        var jQueryformLoadForm = jQuery('#nf-form-48-cont').hide();
        var jQueryformPaiement = jQuery('#nf-form-49-cont').hide();
        var jQuerypayer = jQuery('.payer').hide();
        
        jQuery.post(
            ajaxurl,
            {
                'action': 'ajaxStripeSubscription',
                'ref_compte' : refCompteCheckout,
                'token': token,
                'email_parent' : emailCheckout,
                'plan_stripe' : planStripe
            }
        )
            .done(function(retour) {
                if(retour == "done"){
                    jQuery('.accroche').html('<p style="text-align: center;">Félicitations : le paiement est passé. <span class = "prenom"> Machin </span> est bien inscrit(e). Y a t\'il une autre inscription à faire ? </p>');
                    jQuery('.info').html('<p style="text-align: center;">Félicitations : le paiement est  passé. <span class = "prenom"> Machin </span> est bien inscrit(e). </p>');

                }else{
                    jQuery('.accroche').html('<p style="text-align: center;">Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l\'équipe. </p>');
                    jQuery('.info').html('<p style="text-align: center;">Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l\'équipe. </p>');
                }
            })
            .fail(function() {
                jQuery('.accroche').html('<p style="text-align: center;">Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l\'équipe. </p>');
                jQuery('.info').html('<p style="text-align: center;">Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l\'équipe. </p>');
            })
            .always(function() {
                jQuery('.loadingDiv').hide();
                jQuery('#top-message').show();
                jQuery('#nf-form-48-cont').show();
                jQuery('.valider').show();
                jQuery('.prenom').html(prenomEleve);
                jQuery(jQueryformLoadForm).find("input").attr("value","");
                jQuery('.nf-error-field-errors').remove();
                jQuery('..nf-error-invalid-email').remove();
                window.scrollTo(0, 0);
            });
      }
    });

    // attendre l'apparition du bouton de paiement
    waitForEl(".payer", function() {
      jQuery('.payer ').click(function(e) {
        // Open Checkout with further options:
        handler.open({
          name: 'SpamTonProf',
          description: 'Abonnement de '.concat(montant,' € par semaine'),
          zipCode: false,
          amount: montant*100,
          email : emailCheckout,
          currency: 'EUR'
        });
        e.preventDefault();
      });
    });

    // Close Checkout on page navigation:
    window.addEventListener('popstate', function() {
      handler.close();
    });
    
    /** fin formulaire de paiement stripe **/


});