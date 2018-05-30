
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
            var $loading = $('.loadingDiv').show();
            var $formLoadForm = $('#nf-form-48-cont').hide();
            var $valider = $('.valider').hide();
            var $formPaiement = $('#nf-form-49-cont');
            var $payer = $('.payer').hide();

            var choix1 = $(".choix-formule li:nth-of-type(1)").hide();
            var choix2 = $(".choix-formule li:nth-of-type(2)").hide();
            var choix3 = $(".choix-formule li:nth-of-type(3)").hide();
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
                        $formLoadForm.show();
                        $valider.show();
                        var modelID       = model.get( 'id' );
                        var errorID       = 'custom-field-error';
                        var errorMessage  = 'Aucun compte n’a été trouvé. Veuillez refaire la recherche ou contacter notre équipe.';
                        var fieldsChannel = Backbone.Radio.channel( 'fields' );
                        fieldsChannel.request( 'add:error', modelID, errorID, errorMessage );
                        /* message pas de compte trouvé  - refaire la recherche */    
                    }else{
                        $formPaiement.show();
                        var $choixPaiement = $('.choix-paiement').hide();
                        $payer.show();
                        $(".fields-to-hide").hide(); //cacher les champs adresse_mail, tarifs et ref plan paiement stripe
                        var indiceCompte = 0;
                        comptes.forEach(function(compte) {
                            var choixCourant = choix[indiceCompte];
                            indiceCompte = indiceCompte+1;

                            $label = "<span class = 'nom-formule'> Formule " + compte.planPaiement.formule.formule + "</span><br>";
                            $label += "<span class = 'details-formule'>Pour " + compte.eleve.prenom + " - " + compte.eleve.classe + "<br>";
                            $label += compte.planPaiement.tarif + " € par semaine</span>"; 


                            $(choixCourant).find("label").html($label);
                            $(choixCourant).find("input").attr("email-value",compte.proche.adresse_mail);
                            $(choixCourant).find("input").attr("tarif",compte.planPaiement.tarif);
                            $(choixCourant).find("input").attr("ref_plan_stripe",compte.planPaiement.ref_plan_stripe);
                            $(choixCourant).find("input").attr("ref_compte",compte.ref_compte);
                            $(choixCourant).find("input").attr("prenom_eleve",compte.eleve.prenom);
                            $(choixCourant).find("input").val(compte.ref_compte);


                            $(choixCourant).find("input").change(
                                function(){
                                    if ($(this).is(':checked')) {
                                        montant = $(this).attr('tarif');
                                        emailCheckout = $(this).attr('email-value');
                                        refCompteCheckout = $(this).attr('ref_compte');
                                        planStripe = $(this).attr('ref_plan_stripe');
                                        prenomEleve = $(this).attr('prenom_eleve');
                                    }
                                });


                            if(indiceCompte == 1){
                                $(choixCourant).find("input").change();
                            }

                            choixCourant.show();
                        });

                    }

                })
                .fail(function() {
                    $formLoadForm.show();
                    var modelID       = model.get( 'id' );
                    var errorID       = 'custom-field-error';
                    var errorMessage  = 'Aucun compte n’a été trouvé. Veuillez refaire la recherche ou contacter notre équipe.';
                    var fieldsChannel = Backbone.Radio.channel( 'fields' );
                    fieldsChannel.request( 'add:error', modelID, errorID, errorMessage );
                    /* message pas de compte trouvé - refaire la recherche */
                })
                .always(function() {
                    $loading.hide();
                });

        }
    }
});


jQuery( document ).ready( function( $ ) {

    var $loading = $('.loadingDiv').hide();
    $('#top-message').hide();
    var $formLoadForm = $('#nf-form-48-cont');
    var $formPaiement = $('#nf-form-49-cont').hide();
    var $choixPaiement = $('.choix-paiement').hide();
    var $payer = $('.payer').hide();

    new myCustomFieldController(); // pour contrôler le champs de saisie de l'email


    /** début formulaire de paiement stripe **/

    var handler = StripeCheckout.configure({
      key: 'pk_live_RGW2vHysvvXHwi1SkolaUPP9',
      image: 'https://spamtonprof.com/wp-content/uploads/2018/03/logo-stripe.png',
      locale: 'auto',
      allowRememberMe: false,
      token: function(token) {
        
        var $loading = $('.loadingDiv').show();
        $('#top-message').hide();
        var $formLoadForm = $('#nf-form-48-cont').hide();
        var $formPaiement = $('#nf-form-49-cont').hide();
        var $payer = $('.payer').hide();
        
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
                    $('.accroche').html('<p style="text-align: center;">Félicitations : le paiement est passé. <span class = "prenom"> Machin </span> est bien inscrit(e). Y a t\'il une autre inscription à faire ? </p>');
                    $('.info').html('<p style="text-align: center;">Félicitations : le paiement est  passé. <span class = "prenom"> Machin </span> est bien inscrit(e). </p>');

                }else{
                    $('.accroche').html('<p style="text-align: center;">Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l\'équipe. </p>');
                    $('.info').html('<p style="text-align: center;">Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l\'équipe. </p>');
                }
            })
            .fail(function() {
                $('.accroche').html('<p style="text-align: center;">Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l\'équipe. </p>');
                $('.info').html('<p style="text-align: center;">Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l\'équipe. </p>');
            })
            .always(function() {
                $('.loadingDiv').hide();
                $('#top-message').show();
                $('#nf-form-48-cont').show();
                $('.valider').show();
                $('.prenom').html(prenomEleve);
                $($formLoadForm).find("input").attr("value","");
                $('.nf-error-field-errors').remove();
                $('..nf-error-invalid-email').remove();
                window.scrollTo(0, 0);
            });
      }
    });

    // attendre l'apparition du bouton de paiement
    waitForEl(".payer", function() {
      $('.payer ').click(function(e) {
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