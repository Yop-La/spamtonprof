

// Create a new object for custom validation of a custom field.
var myCustomFieldController = Marionette.Object.extend( {
    initialize: function() {

        // on the Field's model value change...
        var fieldsChannel = Backbone.Radio.channel( 'fields' );
        this.listenTo( fieldsChannel, 'change:modelValue', this.validate );
    },

    validate: function( model ) {
    	// 739 : id de l'adresse élève (dépend du formulaire)
    	// 753 : id de l'adresse parent (dépend du formulaire)

        if(model.get( 'id' ) == 739 ){

        	jQuery.post(
        	    ajaxurl,
        	    {
        	        'action': 'validateInscriptionEssai',
        	        'mailEleve': model.get( 'value' ).trim()
        	    },
        	    function(response){
        	            eleveExists = response.eleveExits;
        	            if(eleveExists){
        	            	window.location.replace("http://spamtonprof.com");
        	            }
				}
        	);
        }
        if(model.get( 'id' ) == 753 ){
    		jQuery.post(
    		    ajaxurl,
    		    {
    		        'action': 'validateInscriptionEssai',
    		        'mailParent': model.get( 'value' ).trim()
    		    },
    		    function(response){
    		    	parentExits = response.parentExits;
    		    	if(parentExits){
    		    		window.location.replace("http://spamtonprof.com");
    		    	}
    		    }
    		);
    	}
	}
});

jQuery( document ).ready( function( $ ) {
    new myCustomFieldController();
});