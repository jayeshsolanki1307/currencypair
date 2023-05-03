<?php

class Currency_Pairs_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		//Currency Pair Action
		add_action( 'wp_ajax_currency_pair_api_data', array( $this, 'currency_pair_api_data_handle' ) );
        add_action( 'wp_ajax_nopriv_currency_pair_api_data', array( $this, 'currency_pair_api_data_handle' ) );
		
		//Currency Convert Action
		add_action( 'wp_ajax_currency_convert_api', array( $this, 'currency_convert_api_handle' ) );
        add_action( 'wp_ajax_nopriv_currency_convert_api', array( $this, 'currency_convert_api_handle' ) );
	}
	
	/**
	 * Function for display data from API.
	 *
	 * @since    1.0.0
	 */
	public static function currency_pair_api_data_handle( $cpId = null ) {	
		date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)		
		$check_date = date('d-m-Y H:i:s');
		if (
			$cpId !== null || # for PHP Call
			isset( $_REQUEST['postId'] ) && # For JS/AJAX Call
			!empty( $_REQUEST['postId'] ) && 
			isset( $_REQUEST['frontend_ajax_nonce'] ) && 
			wp_verify_nonce( $_REQUEST['frontend_ajax_nonce'], 'currency_pair_frontend_nonce' ) 
		) {
			$functionType = 'FX_DAILY'; #default and 1Month
			if( 
				isset( $_REQUEST['chartRange'] ) && 
				$_REQUEST['chartRange'] && 
				$_REQUEST['chartRange'] == '1day' 
			){
				$functionType = 'FX_INTRADAY'; #1Day
			}elseif( 
				isset( $_REQUEST['chartRange'] ) && 
				$_REQUEST['chartRange'] && 
				$_REQUEST['chartRange'] == '1week' 
			){
				$functionType = 'FX_WEEKLY'; #1W
			}elseif( 
				isset( $_REQUEST['chartRange'] ) && 
				$_REQUEST['chartRange'] && 
				$_REQUEST['chartRange'] == '1month' 
			){
				$functionType = 'FX_DAILY'; #1Month 	
			}elseif(
				isset( $_REQUEST['chartRange'] ) && 
				$_REQUEST['chartRange'] && 
				$_REQUEST['chartRange'] == '6month' 
			){
				$functionType = 'FX_MONTHLY'; #6Month 	
			}
			
			$table_data     = array();	
			$response_array = array();
			$closing_prices = array();
			$postId         = $_REQUEST['postId'] ?? $cpId;
			$currencyPair   = get_the_title( $postId );
			
			$array 		   = explode( '/', $currencyPair );
			$from_currency = trim( $array[0] );
			$to_currency   = trim( $array[1] );			
			$from_symbol   = trim( $array[0] );
			$to_symbol     = trim( $array[1] );		
					
			// Post Title For Option Value
			$postTitle = strtolower( str_replace( '/', '_', $currencyPair ) );
			
			// WP Option Key
			$monthly_key  = 'currencypair_fx_monthly_data_' . $postTitle;			
			$weekly_key   = 'currencypair_fx_weekly_data_' . $postTitle;
			$sixmonth_key = 'currencypair_fx_sixmonth_data_' . $postTitle;
			
			// get option data 
			$currencypair_fx_monthly_data  = get_option( $monthly_key, [] );
			$currencypair_fx_weekly_data   = get_option( $weekly_key, [] );
			$currencypair_fx_sixmonth_data = get_option( $sixmonth_key, [] );
			
			// five minutes in seconds
			$api_data		  = array();
			$minute_interval  = 5; #minute
			$second_interval  = 60; #second
			$execute_interval = ( intval( $minute_interval ) * intval( $second_interval ) ); #interval
			
			if( 
				sizeof( $currencypair_fx_monthly_data ) > 0 && #one month
				isset( $_REQUEST['chartRange'] ) && 
				$_REQUEST['chartRange'] == '1month' &&
				isset( $currencypair_fx_monthly_data['currencypair_fx_monthly_data'] ) && 
				!empty( $currencypair_fx_monthly_data['currencypair_fx_monthly_data'] ) &&
				isset( $currencypair_fx_monthly_data['fx_monthly_current_time'] ) && 
				!empty( $currencypair_fx_monthly_data['fx_monthly_current_time'] ) &&
				( $currencypair_fx_monthly_data['fx_monthly_current_time']+$execute_interval ) >= time()
			) {
				$api_data =  $currencypair_fx_monthly_data['currencypair_fx_monthly_data'];		
				
			} elseif (
				sizeof( $currencypair_fx_weekly_data ) > 0 && #one week
				isset( $_REQUEST['chartRange'] ) && 
				$_REQUEST['chartRange'] == '1week' &&
				isset( $currencypair_fx_weekly_data['currencypair_fx_weekly_data'] ) && 
				!empty( $currencypair_fx_weekly_data['currencypair_fx_weekly_data'] ) &&
				isset( $currencypair_fx_weekly_data['fx_weekly_current_time'] ) && 
				!empty( $currencypair_fx_weekly_data['fx_weekly_current_time'] ) &&
				( $currencypair_fx_weekly_data['fx_weekly_current_time']+$execute_interval ) >= time()
			) {
				$api_data =  $currencypair_fx_weekly_data['currencypair_fx_weekly_data'];	
				
			} elseif (
				sizeof( $currencypair_fx_sixmonth_data ) > 0 && #six month
				isset( $_REQUEST['chartRange'] ) && 
				$_REQUEST['chartRange'] == '6month' &&
				isset( $currencypair_fx_sixmonth_data['currencypair_fx_sixmonth_data'] ) && 
				!empty( $currencypair_fx_sixmonth_data['currencypair_fx_sixmonth_data'] ) &&
				isset( $currencypair_fx_sixmonth_data['fx_sixmonth_current_time'] ) && 
				!empty( $currencypair_fx_sixmonth_data['fx_sixmonth_current_time'] ) &&
				( $currencypair_fx_sixmonth_data['fx_sixmonth_current_time']+$execute_interval ) >= time()
			) {
				$api_data =  $currencypair_fx_sixmonth_data['currencypair_fx_sixmonth_data'];	
				
			}else{				
				$api_args = "https://www.alphavantage.co/query";
				if( 
					isset( $_REQUEST['chartRange'] ) && 
					!empty( $_REQUEST['chartRange'] ) && 
					$_REQUEST['chartRange'] == '1day' #for future use this is paid
				){
					$api_url = add_query_arg( array(
						'function'	  	=> $functionType,
						'from_symbol' 	=> $from_symbol,
						'to_symbol'   	=> $to_symbol,
						'interval' 	  	=> '5min',
						'apikey' 		=> 'DMJL6KRYSWLCRUA2',
					), $api_args );
				}else{
					$api_url = add_query_arg( array(
						'function'	  	=> $functionType,
						'from_symbol' 	=> $from_symbol,
						'to_symbol'   	=> $to_symbol,
						'apikey' 		=> 'DMJL6KRYSWLCRUA2',
					), $api_args );
				}
				    
				$api_response = wp_remote_get( $api_url );
				
				if ( is_array( $api_response ) && ! is_wp_error( $api_response ) ) {	
					// API Remote Data
					$api_data = json_decode( wp_remote_retrieve_body( $api_response ), true );
					// Set current time
					$currnet_time = time();

					// Insert monthly data into database option //currencypair_fx_monthly_data
					if( 
						isset( $_REQUEST['chartRange'] ) && 
						$_REQUEST['chartRange'] == '1month' 
					){
						$api_option_arr = array(
							'cp_post_id' => $_REQUEST['postId'],
							'currencypair_fx_monthly_data' => $api_data,
							'fx_monthly_current_time' => $currnet_time
						);						
						update_option( $monthly_key, $api_option_arr );
					}

					// Insert weekly data into database option //currencypair_fx_weekly_data
					if( 
						isset( $_REQUEST['chartRange'] ) && 
						$_REQUEST['chartRange'] == '1week' 
					){
						$api_option_arr = array(
							'cp_post_id' => $_REQUEST['postId'],
							'currencypair_fx_weekly_data' => $api_data,
							'fx_weekly_current_time' => $currnet_time
						);
						update_option( $weekly_key, $api_option_arr );
					} 

					// Insert six month data into database option //currencypair_fx_sixmonth_data
					if( 
						isset( $_REQUEST['chartRange'] ) && 
						$_REQUEST['chartRange'] == '6month' 
					){
						$api_option_arr = array(
							'cp_post_id' => $_REQUEST['postId'],
							'currencypair_fx_sixmonth_data' => $api_data,
							'fx_sixmonth_current_time' => $currnet_time
						);
						update_option( $sixmonth_key, $api_option_arr );
					} 
				}
			}
			/*** ##### New Logic Code End #### ***/			
			if ( is_array( $api_data ) && sizeof( $api_data ) > 0 ) {	
			
				$open_price   = "N/A";
				$high_price   = "N/A";
				$low_price    = "N/A";
				$close_price  = "N/A";
				$time_series  = null;						
				$counter 	  = 0;
				$api_date 	  = null;
				$current_date = date( "Y-m-d" );
				foreach ( $api_data as $key => $data ) {	
					if( 
						isset( $_REQUEST['chartRange'] ) && 
						$_REQUEST['chartRange'] == '6month' && 
						$counter == 7
					) {
						break;
					} elseif ( 
						isset( $_REQUEST['chartRange'] ) && 
						$_REQUEST['chartRange'] == '1month' && 
						$counter == 31
					) {
						break;
					} elseif ( 
						isset( $_REQUEST['chartRange'] ) && 
						$_REQUEST['chartRange'] == '1week' && 	
						$counter == 9
					) {
						break;
					}			
					
					if( 
						isset( $api_data['Time Series FX (Weekly)'] ) && #default and YTD
						!empty( $api_data['Time Series FX (Weekly)'] )
					){					
						$time_series =  $api_data['Time Series FX (Weekly)'];		
					}elseif (
						isset( $api_data['Time Series FX (Monthly)'] ) && #6Month
						!empty( $api_data['Time Series FX (Monthly)'] )
					) {
						$time_series =  $api_data['Time Series FX (Monthly)'];						
					}elseif (
						isset( $api_data['Time Series FX (Daily)'] ) && #1Month
						!empty( $api_data['Time Series FX (Daily)'] )
					) {
						$time_series =  $api_data['Time Series FX (Daily)'];
					}elseif (
						isset( $api_data['Time Series FX (5min)'] ) && #1Daily
						!empty( $api_data['Time Series FX (5min)'] )
					) {
						$time_series =  $api_data['Time Series FX (5min)'];
					}			

					//check is array
					if ( is_array( $time_series ) && !is_null( $time_series ) ) {
						foreach ( $time_series as $series_date => $series_data ) {	
							$counter++;
							if( 
								isset( $_REQUEST['chartRange'] ) && 
								$_REQUEST['chartRange'] == '6month' && 
								$counter == 7
							){
								break;
							}elseif ( 
								isset( $_REQUEST['chartRange'] ) && 
								$_REQUEST['chartRange'] == '1month' && 
								$counter == 31
							) {
								break;
							}elseif ( 
								isset( $_REQUEST['chartRange'] ) && 
								$_REQUEST['chartRange'] == '1week' && 
								$counter == 9
							) {
								break;
							}	
							
							//for only 1week data
							if(  
								isset( $_REQUEST['chartRange'] ) && 
								$_REQUEST['chartRange'] == '1week' && 
								$series_date == $current_date 
							){
								continue;
							}
														
							//format date 
							$series_date = date_i18n("M d, Y", strtotime( $series_date ) );	
							
							//open price
							if( isset( $series_data['1. open'] ) && !empty( $series_data['1. open'] ) ){
								$open_price = number_format( $series_data['1. open'], 4 );
							}
	
							//high price
							if( isset( $series_data['2. high'] ) && !empty( $series_data['2. high'] ) ){
								$high_price = number_format( $series_data['2. high'], 4 );
							}
	
							//low price
							if( isset( $series_data['3. low'] ) && !empty( $series_data['3. low'] ) ){
								$low_price = number_format( $series_data['3. low'], 4 );
							}
	
							//close price
							if( isset( $series_data['4. close'] ) && !empty( $series_data['4. close'] ) ){
								$close_price = number_format( $series_data['4. close'], 4 );
								array_push( $closing_prices, number_format( $close_price, 4 ) );
							}													
							
							$response_array['series_date'] 	= $series_date;
							$response_array['open_price'] 	= $open_price;
							$response_array['high_price']	= $high_price;
							$response_array['low_price'] 	= $low_price;
							$response_array['close_price'] 	= $close_price;
							
							array_push( $table_data, $response_array );
						}
					}
				}	
			}
			
			$returns    = array();
			$trend_type = 0;

			$reverseClosingPrices = array_reverse( $closing_prices );

			if( sizeof( $reverseClosingPrices ) > 0 ){
				for ( $i = 1; $i < count( $reverseClosingPrices ); $i++ ) {
					$returns[] = ( $reverseClosingPrices[$i] - $reverseClosingPrices[$i - 1] ) / $reverseClosingPrices[$i - 1];
				}				

				$trend_type = array_sum( $returns ) / count( $returns ) > 0 ? "Bullish" : "Bearish";
			}
			
			if( isset( $_REQUEST['postId'] ) ) {
				
				$response = [
					'data' => $table_data,
					'trendtype' => $trend_type
				];

				wp_send_json( $response ); // For Chart JS
				wp_die();
			} else {				
				return $table_data; // For PHP Historical Chart
			}
		}		
    }

	/**
	 * Function for get convert currency data
	 *
	 * @since    1.0.0
	*/
	public static function currency_convert_api_handle()
	{
		$response = [];
		if(
			isset( $_REQUEST[ 'fromCurrency' ] ) &&
			!empty( $_REQUEST[ 'fromCurrency' ] ) &&
			isset( $_REQUEST[ 'toCurrency' ] ) &&
			!empty( $_REQUEST[ 'toCurrency' ] ) &&
			isset( $_REQUEST[ 'amount' ] ) &&
			!empty( $_REQUEST[ 'amount' ] ) &&
			isset( $_REQUEST['ajax_nonce'] ) && 
			wp_verify_nonce( $_REQUEST['ajax_nonce'], 'currency_pair_frontend_nonce' ) 
		){
			$amount        = $_REQUEST[ 'amount' ];
			$from_currency = $_REQUEST[ 'fromCurrency' ];
			$to_currency   = $_REQUEST[ 'toCurrency' ];

			$fromCurrencyVal   = $_REQUEST[ 'fromCurrencyVal' ];
			$toCurrencyVal   = $_REQUEST[ 'toCurrencyVal' ];

			$fromCurrencyText = $_REQUEST[ 'fromCurrencyText' ];
			$toCurrencyText   = $_REQUEST[ 'toCurrencyText' ];
			
			// $fromCurrencyExplode = explode( "-", $from_currency );
			// $fromCurrencyCode = $fromCurrencyExplode[0];
			// $fromCurrencyText = $fromCurrencyExplode[1];
			
			// $toCurrencyExplode = explode( "-", $to_currency );
			// $toCurrencyCode = $toCurrencyExplode[0];
			// $toCurrencyText = $toCurrencyExplode[1];
						
			$api_args = "https://api.apilayer.com/exchangerates_data/convert";
			$api_url = add_query_arg( array(
				'to'     => trim( $toCurrencyVal ),
				'from'   => trim( $fromCurrencyVal ),	
				'amount' => $amount,
			), $api_args );
			
			$curl = curl_init();
			curl_setopt_array( $curl, array(
				CURLOPT_URL => $api_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'GET',
				CURLOPT_HTTPHEADER => array(
					'Content-Type: text/plain',
					'apikey: v8xsHYWPiomWQ7gSlTQMPdSpnyHes7Go'
				),
			));
			$api_response = curl_exec( $curl );
						
			curl_close( $curl );
						
			if ( !empty( $api_response ) ) {
				if( gettype( $api_response == 'string' ) ){
			
					$body_response = json_decode( $api_response , true);		
					$rate = $body_response['result'];		
					
					$amountWithFromCurrencyText = $amount . " " .$fromCurrencyText . "s = ";
					$currencyConvertResult = $rate ." ". $toCurrencyText . "s";
					
					$FromCurrencyEqualstoToCurrency = "1 ". $fromCurrencyVal . " = " .( $rate / $amount ) ." ". $toCurrencyVal;
					$ToCurrencyEqualstoFromCurrency = "1 ". $toCurrencyVal . " = " . ( $amount / $rate ) ." ". $fromCurrencyVal;
					
					$response = [
						'status' => true,
						'amountWithFromCurrencyText' => $amountWithFromCurrencyText,
						'currencyConvertResult' => $currencyConvertResult,
						'FromCurrencyEqualstoToCurrency' => $FromCurrencyEqualstoToCurrency,
						'ToCurrencyEqualstoFromCurrency' => $ToCurrencyEqualstoFromCurrency,
					];										
				}
			}
		}else{		
			
			$response = [
				'status' => false
			];
		}
		wp_send_json( $response ); 
		wp_die();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		//Bootstarp CSS
		wp_enqueue_style( 'bootstarp', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), $this->version, 'all' );

		// Public CSS
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/currency-pairs-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		// Chart JS
		wp_enqueue_script( 'chart', plugin_dir_url( __FILE__ ) . 'js/chart.js', array( 'jquery' ), $this->version, true );
		
		// Popper Script
		wp_enqueue_script( 'popper', plugin_dir_url( __FILE__ ) . 'js/popper.min.js', array( 'jquery' ), $this->version, true );

		//Bootstarp JS
		wp_enqueue_script( 'bootstarp', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js', array( 'jquery' ), $this->version, true );

		// validation script
		wp_enqueue_script( 'validate', plugin_dir_url( __FILE__ ) . '/js/jquery.validate.min.js', array( 'jquery' ), $this->version, true );
		
		//Select2 JS
		wp_enqueue_script( 'select2', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), $this->version, true );

		//Convert Currency JS
		wp_enqueue_script( 'currency-convert', plugin_dir_url( __FILE__ ) . 'js/currency-convert.js', array( 'jquery' ), $this->version, true );

		//Public JS
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/currency-pairs-public.js', array( 'jquery' ), $this->version, true );

		//script object
		wp_localize_script( $this->plugin_name, 'currency_pair_frontend_ajax', array(
			'ajax_url'   	    => admin_url('admin-ajax.php'), 
			'admin_url'  	    => admin_url(), 
			'ajax_nonce'        => wp_create_nonce('currency_pair_frontend_nonce'),
			'api_monthly_data'  => get_option( 'currencypair_fx_monthly_data', [] ),		
			'api_weekly_data'   => get_option( 'currencypair_fx_weekly_data', [] ),		
			'api_sixmonth_data' => get_option( 'alphavantage_fx_sixmonth_data', [] ),
		));
	}
}
