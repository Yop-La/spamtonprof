
jQuery( document ).ready( function( $ ) {
	
	var search = instantsearch({
		// Replace with your own values
		appId: '3VXJH73YCI',
		apiKey: '679e64fbe87fa37d0d43e1fbb19e45d8', // search only API key, no ADMIN key
		indexName: 'support_client',
		urlSync: true,
		searchParameters: {
			hitsPerPage: 10
		}
	});

	waitForEl('#search-input', function() {


		search.addWidget(
				instantsearch.widgets.searchBox({
					container: '#search-input'
				})
		);

	});

	waitForEl('#hits', function() {
		
		search.addWidget(
				instantsearch.widgets.hits({
					container: '#hits',
					templates: {
						item: document.getElementById('hit-template').innerHTML,
						empty: "We didn't find any results for the search <em>\"{{query}}\"</em>"
					}
				})
		);

	});

	waitForEl('#pagination', function() {
		search.addWidget(
				instantsearch.widgets.pagination({
					container: '#pagination'
				})
		);

	});

	waitForEl('#sort-by', function() {
		search.addWidget(
				instantsearch.widgets.sortBySelector({
					container: '#sort-by',
					autoHideContainer: true,
					indices: [
						{
							name: 'support_client',
							label: 'Nb de jours depuis dernier message'
						},
						{
							name: 'support_client_by_nb_msg',
							label: 'Nb de messages 7 derniers jours'
						}
						]
				})
		);
	});
	search.start();


});