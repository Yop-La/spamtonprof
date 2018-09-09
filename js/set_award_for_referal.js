jQuery( document ).ready( function( $ ) {
	// console.log(window.location.href.indexOf("iref"));
	// console.log(!jQuery.cookie('affwp_ref'));

  if(window.location.href.indexOf("iref") <= -1 ) {
	var checkExist = setInterval(function() {
	   if (jQuery('#ligne-parrainage').length) {
		  jQuery("#ligne-parrainage").hide();
		  clearInterval(checkExist);
	   }
	}, 100); // check every 100ms

        
  }
});