

console.log('ok !!');


jQuery( document ).ready( function( jQuery ) {



	waitForEl('#table_1', function() {


		jQuery.post(
				ajaxurl,
				{
					'action' : 'lbcReport',
				})
				.done(function(retour){ 

					console.log(retour)



					jQuery('#table_1').DataTable( {
						data: retour.tab1,
						columns: [
							{ title: "Prénom" },
							{ title: "Ref client" },
							{ title: "Nb annonces","searchable": false }
							]
					} );


				})
				.fail(function(err){

					console.log('fail');

				});



	});
	
	waitForEl('#table_2', function() {


		jQuery.post(
				ajaxurl,
				{
					'action' : 'lbcReport',
				})
				.done(function(retour){ 

					console.log(retour)



					jQuery('#table_2').DataTable( {
						data: retour.tab2,
						columns: [
							{ title: "Prénom" },
							{ title: "Nb ads" },
							{ title: "Date création" }
							]
					} );


				})
				.fail(function(err){

					console.log('fail');

				});



	});
	
	
	waitForEl('#table_3', function() {


		jQuery.post(
				ajaxurl,
				{
					'action' : 'lbcReport',
				})
				.done(function(retour){ 

					console.log(retour)



					jQuery('#table_3').DataTable( {
						data: retour.tab3,
						columns: [
							{ title: "Ref client" },
							{ title: "Prénom client" },
							{ title: "Domain","searchable": false },
							{ title: "Sum","searchable": false },
							]
					} );


				})
				.fail(function(err){

					console.log('fail');

				});



	});



});