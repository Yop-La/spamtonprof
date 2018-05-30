$(document).ready(function(){
	// console.log(window.location.href.indexOf("iref"));
	// console.log(!$.cookie('affwp_ref'));

  if(window.location.href.indexOf("iref") <= -1 ) {
	var checkExist = setInterval(function() {
	   if ($('#ligne-parrainage').length) {
		  $("#ligne-parrainage").hide();
		  clearInterval(checkExist);
	   }
	}, 100); // check every 100ms

        
  }
});