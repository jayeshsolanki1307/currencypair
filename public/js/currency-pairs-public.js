(function( $ ) {
	'use strict';

	//Chart API Data
	$( document ).ready(function() {
		let fx_monthly_data  = currency_pair_frontend_ajax.api_monthly_data;
		let fx_weekly_data   = currency_pair_frontend_ajax.api_weekly_data;
		let fx_sixmonth_data = currency_pair_frontend_ajax.api_sixmonth_data;
		getAPIData();
	});

	// Get APi Data
	function getAPIData( ){
		let chartContainers = $( '.cp-chart' );

		chartContainers.each( function( index, chart ) {
			let cId = $( chart ).data( 'chart-id' );
			let chartTitle = $( chart ).data( 'chart-title' );
			let chartRange = $( '.cp-chart-range-'+cId ).find( 'li a.active' ).data( 'range' );
						
			let params = {
				action: "currency_pair_api_data",
				frontend_ajax_nonce: currency_pair_frontend_ajax.ajax_nonce,			
				postId: cId,			
				chartRange: chartRange,			
			};

			$.ajax({
				url: currency_pair_frontend_ajax.ajax_url,
				type: 'post',
				dataType: "JSON",
				data: params,
				success: function( response ) {

					let data = response.data;
					let trendType = response.trendtype;
					if( data ){
						let date_arr = [];
						let close_price_arr = [];
						for (let index = 0; index < data.length; index++) {
							const element = data[index];						
							date_arr.push( element.series_date );
							close_price_arr.push( element.close_price );
						}	
						let ctx = document.getElementById( 'cp-chart-' + cId );
						
						let myChart;
						
						if( typeof ctx == 'object' ) {
							ctx.remove();
						}

						$( 'div.cp-chart-'+ cId ).append( '<canvas id="cp-chart-' + cId + '"></canvas>' ); 
						ctx = document.getElementById( 'cp-chart-' + cId );
						
						let chartColor = trendType == 'Bearish' ? 'red' : 'green';						
						myChart = new Chart(ctx, {
							type: 'line',
							options: {
								plugins: {
									legend: {
										display: false
									}
								}
							},
							data: {
								labels: date_arr.reverse(),
								datasets: [
									{
										// label: '# of ' + chartTitle,
										data: close_price_arr.reverse(),
										fill: true,
										backgroundColor: chartColor, 
										borderWidth: 1,
									}
								]
							},							
						});
					}	
				}  
			}); 
		});
	}

	let chartContainers = $('.cp-chart');
	chartContainers.each( function( index, chart ) {
		let cId = $( chart ).data( 'chart-id' );
		let chartTitle = $( chart ).data( 'chart-title' );
		
		// chart filter click event
		$( document ).on('click', '.cp-range', function(e){
			e.preventDefault();
			$( 'ul.chart-range li a' ).removeClass( 'active' );
			$( this ).addClass( 'active' );			
			let chart_id = $( this ).data('chart');
			getAPIData();
		});
	});
})( jQuery );
