<?php

class Currency_Pairs {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Currency_Pairs_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'CURRENCY_PAIRS_VERSION' ) ) {
			$this->version = CURRENCY_PAIRS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'currency-pairs';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		// Add Shortcode Hook For Currency Pair
		add_shortcode( 'currency-pair', [ $this, 'cp_posts_shortcode' ] );
		
		// Add Shortcode Hook For Convert Currency
		add_shortcode( 'currency-convert', [ $this, 'currency_convert_shortcode' ] );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Currency_Pairs_Loader. Orchestrates the hooks of the plugin.
	 * - Currency_Pairs_i18n. Defines internationalization functionality.
	 * - Currency_Pairs_Admin. Defines all hooks for the admin area.
	 * - Currency_Pairs_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-currency-pairs-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-currency-pairs-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-currency-pairs-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-currency-pairs-public.php';

		$this->loader = new Currency_Pairs_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Currency_Pairs_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Currency_Pairs_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Currency_Pairs_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Currency_Pairs_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Currency_Pairs_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	* Create function for display shortcode data
	*/
	public function cp_posts_shortcode( $atts ) {
		$cpId = $atts['id'];
		$currencyPair = get_the_title( $cpId );
		$targetURL	  = get_post_meta( $cpId, 'target_url', true );

		$array = explode( '/', $currencyPair );
		$from_currency = trim( $array[0] );
		$to_currency   = trim( $array[1] );

		$from_symbol = trim( $array[0] );
		$to_symbol   = trim( $array[1] );

		// Post Title For WP Option Value
		$postTitle = strtolower( str_replace( '/', '_', $currencyPair ) );

		// WP Exchange Rate Option Key
		$exchange_rate_key = 'currencypair_fx_exchange_rate_data_'.$postTitle;
		
		ob_start();

		$output = null;			
		$currencypair_fx_exchange_rate_data  = get_option( $exchange_rate_key, [] );		
		if( 
			sizeof( $currencypair_fx_exchange_rate_data ) > 0 && 
			isset( 	$currencypair_fx_exchange_rate_data['cp_post_id'] ) && 
			!empty( $currencypair_fx_exchange_rate_data['cp_post_id'] ) &&
			$currencypair_fx_exchange_rate_data['cp_post_id'] === $cpId
		) {
			$data = $currencypair_fx_exchange_rate_data['currencypair_fx_exchange_rate_data'];				
			
		} else {
			$wp_currency_exchange = "https://www.alphavantage.co/query";
			$url = add_query_arg( array(
				'function' => 'CURRENCY_EXCHANGE_RATE',
				'from_currency' => $from_currency,
				'to_currency' => $to_currency,
				'apikey' => 'DMJL6KRYSWLCRUA2',
			), $wp_currency_exchange );
			
			// @var array|WP_Error $response //
			$response = wp_remote_get( $url );
			if ( is_array( $response ) && ! is_wp_error( $response ) ) {					
				$data = json_decode( wp_remote_retrieve_body( $response ), true );
				
				$api_option_arr = array(
					'cp_post_id' => $cpId,
					'currencypair_fx_exchange_rate_data' => $data,
				);
				update_option( $exchange_rate_key, $api_option_arr );
			}			
		}
				
		if ( is_array( $data ) && sizeof( $data ) > 0 ) {							
			
			foreach( $data as $line ) {
				$From_Currency 				= "N/A";
				$From_Currency_Name			= "N/A";
				$To_Currency 				= "N/A";
				$To_Currency_Name		    = "N/A";
				$Exchange_rate   		    = "N/A";
				$Bid_price 				    = "N/A";
				$Ask_price 				    = "N/A";
				$Exchange_Rate 				= "N/A";

				// Exchange Rate
				if( isset( $line['5. Exchange Rate'] ) ) {
					$Exchange_Rate = $line['5. Exchange Rate'];
				}

				// From Currency Code
				if( isset( $line['1. From_Currency Code'] ) ) {
					$From_Currency = $line['1. From_Currency Code'];
				}

				// From Currency Name
				if( isset( $line['2. From_Currency Name'] ) ) {
					$From_Currency_Name = $line['2. From_Currency Name'];
				}								

				// To Currency Code
				if( isset( $line['3. To_Currency Code'] ) ) {
					$To_Currency = $line['3. To_Currency Code'];					
				}

				// To Currency Name
				if( isset( $line['4. To_Currency Name'] ) ) {
					$To_Currency_Name = $line['4. To_Currency Name'];
				}

				//Bid Price
				if( isset( $line['8. Bid Price'] ) ) {
					$Bid_price = $line['8. Bid Price'];
				}		
				
				//Ask Price
				if( isset( $line['9. Ask Price'] ) ) {
					$Ask_price = $line['9. Ask Price'];
				}	
			}
		}
		?>

		<div class="cp-currency-pairs">
			<section class="module-section-page-main-div">
				<div class="page-currency-data">
					<div class="upper-currency-data">
						<div class="currency-state-data">
							<h2><?php echo $currencyPair?></h2>		
							<?php 
								if( !empty( $targetURL ) ): 
								?>
								<a href="<?php echo $targetURL;?>" class="cp-target-link" target="_blank"><?php _e("Start Trading", "currency-pairs"); ?></a>				
								<?php
								endif;
							?>
						</div>
						<?php
							$summary_prev_close = 'N/A';
							$summary_open_price = 'N/A';
							$difference_price	= '';
							$table_data = array();
							$response_array = array();
							
							// WP Monthly Option Key
							$monthly_key = 'currencypair_fx_monthly_data_' . $postTitle;
	
							// Check Option has data or not
							$currencypair_fx_monthly_data = get_option( $monthly_key, [] );
							
							if( 
								sizeof( $currencypair_fx_monthly_data ) > 0 && #one month						
								isset( $currencypair_fx_monthly_data['currencypair_fx_monthly_data'] ) && 
								!empty( $currencypair_fx_monthly_data['currencypair_fx_monthly_data'] ) &&
								isset( $currencypair_fx_monthly_data['cp_post_id'] ) && 
								!empty( $currencypair_fx_monthly_data['cp_post_id'] ) && 
								$currencypair_fx_monthly_data['cp_post_id'] === $cpId
							){							
								$api_data = $currencypair_fx_monthly_data['currencypair_fx_monthly_data']['Time Series FX (Daily)'];
								if ( is_array( $api_data ) && sizeof( $api_data ) > 0 ) {	
									foreach ( $api_data as $series_date => $series_data ) {	
										
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
										}	
										
										$response_array['series_date'] 	= $series_date;
										$response_array['open_price'] 	= $open_price;
										$response_array['high_price']	= $high_price;
										$response_array['low_price'] 	= $low_price;
										$response_array['close_price'] 	= $close_price;
										
										array_push( $table_data, $response_array );
									}
								}
							}else{							
								$table_data = Currency_Pairs_Public::currency_pair_api_data_handle( $cpId );
							}						
							
							if( !empty( $table_data ) &&  count( $table_data ) > 0 ){
								$summary_prev_close = number_format( $table_data[0]['close_price'],4 );
								$summary_open_price = number_format( $table_data[0]['open_price'],4 );
							}
	
							//Exchange Rate Price
							if( !isset( $Exchange_Rate ) ){
								$Exchange_Rate = 'N/A';
							}else{
								if( is_numeric( $Exchange_Rate )  ){
									$Exchange_Rate = number_format( $Exchange_Rate, 4 );
								}
							}
	
							// Get Differecne Price
							if( is_numeric( $Exchange_Rate ) && is_numeric( $summary_prev_close ) ){
								$difference_price = $Exchange_Rate -  $summary_prev_close;
							}
								
							$price_symbol = '';
							$price_type   = '';
							if ( !empty( $difference_price ) && $difference_price > 0 ) {
								$price_symbol = '+';
								$price_type   = 'positive';
								$difference_price = number_format( $difference_price, 4 );
							}elseif ( !empty( $difference_price ) && $difference_price < 0 ) {
								//$price_symbol = '-';
								$price_type   = 'negative';
								$difference_price = number_format( $difference_price, 4 );
							}elseif ( !empty( $difference_price ) && $difference_price == 0 ) {
								$price_symbol = '';
								$price_type   = '';
								$difference_price = number_format( $difference_price, 4 );
							}
	
							// Calculate Percentage
							$per_symbol = '';
							$per_type   = '';
							$percentage = '';
							
							if ( !empty( $difference_price ) && is_numeric( $summary_prev_close ) ) {
								$percentage = $difference_price / $summary_prev_close * 100;
								if ( !empty( $difference_price ) && $difference_price > 0 ) {
									$per_symbol = '+';
									$per_type   = 'positive';
									$percentage = number_format( $percentage, 4 );
								}elseif ( !empty( $difference_price ) && $difference_price < 0 ) {
									//$per_symbol = '-';
									$per_type   = 'negative';
									$percentage = number_format( $percentage, 4 );
								}elseif ( !empty( $difference_price ) && $difference_price == 0 ) {
									$per_symbol = '';
									$per_type   = '';
									$percentage = '';
								}
							}
						?>
						<div class="currency-values-data">
							<div class="currency-inner-div">
								<span class="currency-first"><?php echo $Exchange_Rate;?></span>
								<span class="currency-changing-data <?php echo $price_type;?>"><?php echo $price_symbol.$difference_price;?></span>
								<span class="currency-changing-data <?php echo $per_type;?>">(<?php echo $per_symbol.$percentage;?>%)</span>							
							</div>
						</div>
					</div>
				</div>
			</section>
	
			<section class="tabular-chart-div">
				<div class="tabs-inner-data">
					<div class="tabset">
						<!-- Tab 1 -->
						<input type="radio" name="tabset" id="tab_sum_<?php echo $cpId;?>" aria-controls="table1" checked>
						<label for="tab_sum_<?php echo $cpId;?>"><?php _e("Summary","currency-pairs");?></label>
	
						<!-- Tab 2 -->
						<input type="radio" name="tabset" id="tab_chart_<?php echo $cpId;?>" aria-controls="chart">
						<label for="tab_chart_<?php echo $cpId;?>"><?php _e("Chart","currency-pairs");?></label>
	
						<!-- Tab 3 -->
						<input type="radio" name="tabset" id="tab_his_<?php echo $cpId;?>" aria-controls="historical-data">
						<label for="tab_his_<?php echo $cpId;?>"><?php _e("Historical Data","currency-pairs");?></label>
	
						<div class="tab-panels">
							<section id="table1" class="tab-panel summary-tab">
								<table>								
									<?php	
										if( !isset( $Bid_price ) ){
											$Bid_price = 'N/A';
										}else{										
											if( is_numeric( $Bid_price )  ){
												$Bid_price = number_format( $Bid_price, 4 );
											}
										}
	
										if( !isset( $Ask_price ) ){
											$Ask_price = 'N/A';
										}else{
											if( is_numeric( $Ask_price )  ){
												$Ask_price = number_format( $Ask_price, 4 );
											}
										}
									?>
									<tbody class="first-chart">
										<tr>
											<td data-label="PreviousClose"><?php _e("Previous Close","currency-pairs");?></td>
											<td data-label="no"><strong><?php echo $summary_prev_close;?></strong></td>
										</tr>
										<tr>
											<td scope="row" data-label="Open"><?php _e("Open","currency-pairs");?></td>
											<td data-label="no"><strong><?php echo $summary_open_price;?></strong></td>
										</tr>
										<tr>
											<td scope="row" data-label="Bid"><?php _e("Bid","currency-pairs");?></td>
											<td data-label="no"><strong><?php echo $Bid_price;?></strong></td>
										</tr>
									</tbody>
									<tbody class="second-chart">									
										<tr>
											<td scope="row" data-label="Name"><?php _e("Ask","currency-pairs");?></td>
											<td data-label="no"><strong><?php echo $Ask_price;?></strong></td>
										</tr>
									</tbody>
								</table>
							</section>
	
							<section id="chart" class="tab-panel chart-panel">
								<!-- Chart Code Start -->
								<div data-chart-id="<?php echo $cpId; ?>" data-chart-title="<?php echo get_the_title($cpId);?>" class="cp-chart-<?php echo $cpId; ?> cp-chart">
									<ul class="chart-range cp-chart-range-<?php echo $cpId; ?>" id="chart-range-<?php echo $cpId; ?>">
										<li><a href="javascript:void(0)" class="cp-range active" data-chart="<?php echo $cpId; ?>" data-range="1month"><?php _e("1M","currency-pairs");?></a></li>
										<li><a href="javascript:void(0)" class="cp-range" data-chart="<?php echo $cpId; ?>" data-range="1week"><?php _e("1W","currency-pairs");?></a></li>
										<li><a href="javascript:void(0)" class="cp-range" data-chart="<?php echo $cpId; ?>" data-range="6month"><?php _e("6M","currency-pairs");?></a></li>									
									</ul>
									<canvas id="cp-chart-<?php echo $cpId; ?>"></canvas>
								</div>
							</section>
							
							<section id="historical-data" class="tab-panel history-table">
								<div class="historical-data-div">
									<table>
										<thead>
											<tr class="thead">
												<th scope="col"><?php _e("Date","currency-pairs");?></th>
												<th scope="col"><?php _e("Open","currency-pairs");?></th>
												<th scope="col"><?php _e("High","currency-pairs");?></th>
												<th scope="col"><?php _e("Low","currency-pairs");?></th>
												<th scope="col"><?php _e("Close*","currency-pairs");?></th>
											</tr>
										</thead>
										<tbody class="historical-data">
											<?php 
												$counter = 0;
												if( !empty( $table_data ) && sizeof( $table_data ) > 0 ){
													foreach ( $table_data as $key => $data ) {
														$counter++;
														if( $counter==8 ){
															break;
														}
														
														$series_date 	= $data['series_date'];
														$open_price 	= $data['open_price'];
														$high_price		= $data['high_price'];
														$low_price 		= $data['low_price'];
														$close_price 	= $data['close_price'];
													?>
													<tr>
														<td data-label="date"><?php echo $series_date?></td>
														<td data-label="no"><?php echo $open_price?></td>
														<td data-label="no"><?php echo $high_price?></td>
														<td data-label="no"><?php echo $low_price?></td>
														<td data-label="no"><?php echo $close_price?></td>
													</tr>
													<?php 
													}
												}
											?>
										</tbody>
									</table>
								</div>
							</section>
						</div>
					</div>
				</div>
			</section>
		</div>
		<?php			
		$cpData = ob_get_clean();
		return $cpData;
	}

	/**
	 * Create function for display convert currency data
	 */
	public function currency_convert_shortcode( $atts ) {
		ob_start();
		$id = $atts['id'];
		if( !empty( $id ) ){					
			$explode_ids = explode( ",", $id );
			$firstCurrencyCode  = strtoupper( $explode_ids[0] );
			$secondCurrencyCode = strtoupper( $explode_ids[1] );			
			?>
			<section class="cp-convert-currency">
				<form id="convert_currency_form" class="container-fluid">
					<div class="form-row">
						<div class="form-group input-adjust-div">
							<label for="amount"><?php _e("Amount","currency-pairs");?></label>
							<div class="amount-input">
								<span class="currency-symbol-input">$</span>
								<input type="text" class="form-control" name="amount" value="1" id="amount" placeholder="Enter Amount">					
							</div>
						</div>

						<div class="form-group input-adjust-div">
							<div class="selectRow from_select">
								<label for="fromCurrency"><?php _e("From","currency-pairs");?></label>
								<div class="dropdown-caret">
									<span class="open_fromCurrency">
										<img src='<?php echo CURRENCY_PAIRS_ROOT_DIR_PUBLIC;?>/images/down-arrow.png'>
									</span>																
									<select class="selectpicker" id="fromCurrency" name="fromCurrency">									
										<option value="EUR" <?php echo ( $firstCurrencyCode == "EUR" ) ? 'selected':'';?> data-currency-text="Euro" data-symbol="€">EUR - Euro</option>
										<option value="AED" <?php echo ( $firstCurrencyCode == "AED" ) ? 'selected':'';?> data-currency-text="Emirates Dirham" data-symbol="د.إ">AED - Emirates Dirham</option>
										<option value="AFN" <?php echo ( $firstCurrencyCode == "AFN" ) ? 'selected':'';?> data-currency-text="Afghan Afghani" data-symbol="؋">AFN - Afghan Afghani</option>
										<option value="ALL" <?php echo ( $firstCurrencyCode == "ALL" ) ? 'selected':'';?> data-currency-text="Albanian Lek" data-symbol="L">ALL - Albanian Lek</option>
										<option value="AMD" <?php echo ( $firstCurrencyCode == "AMD" ) ? 'selected':'';?> data-currency-text="Armenian Dram" data-symbol="֏">AMD - Armenian Dram</option>
										<option value="ANG" <?php echo ( $firstCurrencyCode == "ANG" ) ? 'selected':'';?> data-currency-text="Netherlands Antillean Guilder" data-symbol="ƒ">ANG - Netherlands Antillean Guilder</option>
										<option value="AOA" <?php echo ( $firstCurrencyCode == "AOA" ) ? 'selected':'';?> data-currency-text="Angolan Kwanza" data-symbol="Kz">AOA - Angolan Kwanza</option>
										<option value="ARS" <?php echo ( $firstCurrencyCode == "ARS" ) ? 'selected':'';?> data-currency-text="Argentine Peso" data-symbol="$">ARS - Argentine Peso</option>
										<option value="AUD" <?php echo ( $firstCurrencyCode == "AUD" ) ? 'selected':'';?> data-currency-text="Australian Dollar" data-symbol="$">AUD - Australian Dollar</option>
										<option value="AWG" <?php echo ( $firstCurrencyCode == "AWG" ) ? 'selected':'';?> data-currency-text="Aruban Florin" data-symbol="ƒ">AWG - Aruban Florin</option>
										<option value="AZN" <?php echo ( $firstCurrencyCode == "AZN" ) ? 'selected':'';?> data-currency-text="Azerbaijani Manat" data-symbol="₼">AZN - Azerbaijani Manat</option>
										<option value="BAM" <?php echo ( $firstCurrencyCode == "BAM" ) ? 'selected':'';?> data-currency-text="Bosnia-Herzegovina Convertible Mark" data-symbol="KM">BAM - Bosnia-Herzegovina Convertible Mark</option>
										<option value="BBD" <?php echo ( $firstCurrencyCode == "BBD" ) ? 'selected':'';?> data-currency-text="Barbadian Dollar" data-symbol="$">BBD - Barbadian Dollar</option>
										<option value="BDT" <?php echo ( $firstCurrencyCode == "BDT" ) ? 'selected':'';?> data-currency-text="Bangladeshi Taka" data-symbol="৳">BDT - Bangladeshi Taka</option>
										<option value="BGN" <?php echo ( $firstCurrencyCode == "BGN" ) ? 'selected':'';?> data-currency-text="Bulgarian Lev" data-symbol="лв">BGN - Bulgarian Lev</option>
										<option value="BHD" <?php echo ( $firstCurrencyCode == "BHD" ) ? 'selected':'';?> data-currency-text="Bahraini Dinar" data-symbol=".د.ب">BHD - Bahraini Dinar</option>
										<option value="BIF" <?php echo ( $firstCurrencyCode == "BIF" ) ? 'selected':'';?> data-currency-text="Burundian Franc" data-symbol="FBu">BIF - Burundian Franc</option>
										<option value="BMD" <?php echo ( $firstCurrencyCode == "BMD" ) ? 'selected':'';?> data-currency-text="Bermudan Dollar" data-symbol="$">BMD - Bermudan Dollar</option>
										<option value="BND" <?php echo ( $firstCurrencyCode == "BND" ) ? 'selected':'';?> data-currency-text="Brunei Dollar" data-symbol="$">BND - Brunei Dollar</option>
										<option value="BOB" <?php echo ( $firstCurrencyCode == "BOB" ) ? 'selected':'';?> data-currency-text="Bolivian Boliviano" data-symbol="$b">BOB - Bolivian Boliviano</option>
										<option value="BRL" <?php echo ( $firstCurrencyCode == "BRL" ) ? 'selected':'';?> data-currency-text="Brazilian Real" data-symbol="R$">BRL - Brazilian Real</option>
										<option value="BSD" <?php echo ( $firstCurrencyCode == "BSD" ) ? 'selected':'';?> data-currency-text="Bahamian Dollar" data-symbol="$">BSD - Bahamian Dollar</option>
										<option value="BTC" <?php echo ( $firstCurrencyCode == "BTC" ) ? 'selected':'';?> data-currency-text="Bitcoin" data-symbol="฿">BTC - Bitcoin</option>
										<option value="BTN" <?php echo ( $firstCurrencyCode == "BTN" ) ? 'selected':'';?> data-currency-text="Bhutanese Ngultrum" data-symbol="Nu.">BTN - Bhutanese Ngultrum</option>
										<option value="BWP" <?php echo ( $firstCurrencyCode == "BWP" ) ? 'selected':'';?> data-currency-text="Botswanan Pula" data-symbol="P">BWP - Botswanan Pula</option>
										<option value="BYN" <?php echo ( $firstCurrencyCode == "BYN" ) ? 'selected':'';?> data-currency-text="Belarusian Ruble" data-symbol="Br">BYN - Belarusian Ruble</option>
										<option value="BYR" <?php echo ( $firstCurrencyCode == "BYR" ) ? 'selected':'';?> data-currency-text="Belarusian Ruble (pre-2016)" data-symbol="Br">BYR - Belarusian Ruble (pre-2016)</option>
										<option value="BZD" <?php echo ( $firstCurrencyCode == "BZD" ) ? 'selected':'';?> data-currency-text="Belize Dollar" data-symbol="BZ$">BZD - Belize Dollar</option>
										<option value="CAD" <?php echo ( $firstCurrencyCode == "CAD" ) ? 'selected':'';?> data-currency-text="Canadian Dollar" data-symbol="$">CAD - Canadian Dollar</option>
										<option value="CDF" <?php echo ( $firstCurrencyCode == "CDF" ) ? 'selected':'';?> data-currency-text="Congolese Franc" data-symbol="FC">CDF - Congolese Franc</option>
										<option value="CHF" <?php echo ( $firstCurrencyCode == "CHF" ) ? 'selected':'';?> data-currency-text="Swiss Franc" data-symbol="CHF">CHF - Swiss Franc</option>
										<option value="CLF" <?php echo ( $firstCurrencyCode == "CLF" ) ? 'selected':'';?> data-currency-text="Chilean Unit of Account (UF)" data-symbol="">CLF - Chilean Unit of Account (UF)</option>
										<option value="CLP" <?php echo ( $firstCurrencyCode == "CLP" ) ? 'selected':'';?> data-currency-text="Chilean Peso" data-symbol="$">CLP - Chilean Peso</option>
										<option value="CNH" <?php echo ( $firstCurrencyCode == "CNH" ) ? 'selected':'';?> data-currency-text="Chinese Yuan (Offshore)" data-symbol="¥">CNH - Chinese Yuan (Offshore)</option>
										<option value="CNY" <?php echo ( $firstCurrencyCode == "CNY" ) ? 'selected':'';?> data-currency-text="Chinese Yuan" data-symbol="¥">CNY - Chinese Yuan</option>
										<option value="COP" <?php echo ( $firstCurrencyCode == "COP" ) ? 'selected':'';?> data-currency-text="Colombian Peso" data-symbol="$">COP - Colombian Peso</option>
										<option value="CRC" <?php echo ( $firstCurrencyCode == "CRC" ) ? 'selected':'';?> data-currency-text="Costa Rican Colón" data-symbol="₡">CRC - Costa Rican Colón</option>
										<option value="CUC" <?php echo ( $firstCurrencyCode == "CUC" ) ? 'selected':'';?> data-currency-text="Cuban Convertible Peso" data-symbol="$">CUC - Cuban Convertible Peso</option>
										<option value="CUP" <?php echo ( $firstCurrencyCode == "CUP" ) ? 'selected':'';?> data-currency-text="Cuban Peso" data-symbol="₱">CUP - Cuban Peso</option>
										<option value="CVE" <?php echo ( $firstCurrencyCode == "CVE" ) ? 'selected':'';?> data-currency-text="Cape Verdean Escudo" data-symbol="$">CVE - Cape Verdean Escudo</option>
										<option value="CZK" <?php echo ( $firstCurrencyCode == "CZK" ) ? 'selected':'';?> data-currency-text="Czech Republic Koruna" data-symbol="Kč">CZK - Czech Republic Koruna</option>
										<option value="DJF" <?php echo ( $firstCurrencyCode == "DJF" ) ? 'selected':'';?> data-currency-text="Djiboutian Franc" data-symbol="Fdj">DJF - Djiboutian Franc</option>
										<option value="DKK" <?php echo ( $firstCurrencyCode == "DKK" ) ? 'selected':'';?> data-currency-text="Danish Krone" data-symbol="kr">DKK - Danish Krone</option>
										<option value="DOP" <?php echo ( $firstCurrencyCode == "DOP" ) ? 'selected':'';?> data-currency-text="Dominican Peso" data-symbol="RD$">DOP - Dominican Peso</option>
										<option value="DZD" <?php echo ( $firstCurrencyCode == "DZD" ) ? 'selected':'';?> data-currency-text="Algerian Dinar" data-symbol="دج">DZD - Algerian Dinar</option>
										<option value="EEK" <?php echo ( $firstCurrencyCode == "EEK" ) ? 'selected':'';?> data-currency-text="Estonian Kroon" data-symbol="kr">EEK - Estonian Kroon</option>
										<option value="EGP" <?php echo ( $firstCurrencyCode == "EGP" ) ? 'selected':'';?> data-currency-text="Egyptian Pound" data-symbol="£">EGP - Egyptian Pound</option>
										<option value="ERN" <?php echo ( $firstCurrencyCode == "ERN" ) ? 'selected':'';?> data-currency-text="Eritrean Nakfa" data-symbol="Nfk">ERN - Eritrean Nakfa</option>
										<option value="ETB" <?php echo ( $firstCurrencyCode == "ETB" ) ? 'selected':'';?> data-currency-text="Ethiopian Birr								" data-symbol="Br">ETB - Ethiopian Birr</option>								
										<option value="FJD" <?php echo ( $firstCurrencyCode == "FJD" ) ? 'selected':'';?> data-currency-text="Fijian Dollar" data-symbol="$">FJD - Fijian Dollar</option>
										<option value="FJD" <?php echo ( $firstCurrencyCode == "FJD" ) ? 'selected':'';?> data-currency-text="Fijian Dollar" data-symbol="$">FJD - Fijian Dollar</option>
										<option value="FKP" <?php echo ( $firstCurrencyCode == "FKP" ) ? 'selected':'';?> data-currency-text="Falkland Islands Pound" data-symbol="£">FKP - Falkland Islands Pound</option>
										<option value="GBP" <?php echo ( $firstCurrencyCode == "GBP" ) ? 'selected':'';?> data-currency-text="British Pound Sterling" data-symbol="£">GBP - British Pound Sterling</option>
										<option value="GEL" <?php echo ( $firstCurrencyCode == "GEL" ) ? 'selected':'';?> data-currency-text="Georgian Lari" data-symbol="₾">GEL - Georgian Lari</option>
										<option value="GGP" <?php echo ( $firstCurrencyCode == "GGP" ) ? 'selected':'';?> data-currency-text="Guernsey Pound" data-symbol="£">GGP - Guernsey Pound</option>
										<option value="GHS" <?php echo ( $firstCurrencyCode == "GHS" ) ? 'selected':'';?> data-currency-text="Ghanaian Cedi" data-symbol="GH₵">GHS - Ghanaian Cedi</option>
										<option value="GIP" <?php echo ( $firstCurrencyCode == "GIP" ) ? 'selected':'';?> data-currency-text="Gibraltar Pound" data-symbol="£">GIP - Gibraltar Pound</option>
										<option value="GMD" <?php echo ( $firstCurrencyCode == "GMD" ) ? 'selected':'';?> data-currency-text="Gambian Dalasi" data-symbol="D">GMD - Gambian Dalasi</option>
										<option value="GNF" <?php echo ( $firstCurrencyCode == "GNF" ) ? 'selected':'';?> data-currency-text="Guinean Franc" data-symbol="FG">GNF - Guinean Franc</option>
										<option value="GTQ" <?php echo ( $firstCurrencyCode == "GTQ" ) ? 'selected':'';?> data-currency-text="Guatemalan Quetzal" data-symbol="Q">GTQ - Guatemalan Quetzal</option>
										<option value="GYD" <?php echo ( $firstCurrencyCode == "GYD" ) ? 'selected':'';?> data-currency-text="Guyanaese Dollar" data-symbol="$">GYD - Guyanaese Dollar</option>
										<option value="HKD" <?php echo ( $firstCurrencyCode == "HKD" ) ? 'selected':'';?> data-currency-text="Hong Kong Dollar" data-symbol="$">HKD - Hong Kong Dollar</option>
										<option value="HNL" <?php echo ( $firstCurrencyCode == "HNL" ) ? 'selected':'';?> data-currency-text="Honduran Lempira" data-symbol="L">HNL - Honduran Lempira</option>
										<option value="HRK" <?php echo ( $firstCurrencyCode == "HRK" ) ? 'selected':'';?> data-currency-text="Croatian Kuna" data-symbol="kn">HRK - Croatian Kuna</option>
										<option value="HTG" <?php echo ( $firstCurrencyCode == "HTG" ) ? 'selected':'';?> data-currency-text="Haitian Gourde" data-symbol="G">HTG - Haitian Gourde</option>
										<option value="HUF" <?php echo ( $firstCurrencyCode == "HUF" ) ? 'selected':'';?> data-currency-text="Hungarian Forint" data-symbol="Ft">HUF - Hungarian Forint</option>
										<option value="IDR" <?php echo ( $firstCurrencyCode == "IDR" ) ? 'selected':'';?> data-currency-text="Indonesian Rupiah" data-symbol="Rp">IDR - Indonesian Rupiah</option>
										<option value="ILS" <?php echo ( $firstCurrencyCode == "ILS" ) ? 'selected':'';?> data-currency-text="Israeli New Sheqel" data-symbol="₪">ILS - Israeli New Sheqel</option>
										<option value="IMP" <?php echo ( $firstCurrencyCode == "IMP" ) ? 'selected':'';?> data-currency-text="Manx pound" data-symbol="£">IMP - Manx pound</option>
										<option value="INR" <?php echo ( $firstCurrencyCode == "INR" ) ? 'selected':'';?> data-currency-text="Indian Rupee" data-symbol="₹">INR - Indian Rupee</option>
										<option value="IQD" <?php echo ( $firstCurrencyCode == "IQD" ) ? 'selected':'';?> data-currency-text="Iraqi Dinar" data-symbol="ع.د">IQD - Iraqi Dinar</option>
										<option value="IRR" <?php echo ( $firstCurrencyCode == "IRR" ) ? 'selected':'';?> data-currency-text="Iranian Rial" data-symbol="﷼">IRR - Iranian Rial</option>
										<option value="ISK" <?php echo ( $firstCurrencyCode == "ISK" ) ? 'selected':'';?> data-currency-text="Icelandic Króna" data-symbol="kr">ISK - Icelandic Króna</option>
										<option value="JEP" <?php echo ( $firstCurrencyCode == "JEP" ) ? 'selected':'';?> data-currency-text="Jersey Pound" data-symbol="£">JEP - Jersey Pound</option>
										<option value="JMD" <?php echo ( $firstCurrencyCode == "JMD" ) ? 'selected':'';?> data-currency-text="Jamaican Dollar" data-symbol="J$">JMD - Jamaican Dollar</option>
										<option value="JOD" <?php echo ( $firstCurrencyCode == "JOD" ) ? 'selected':'';?> data-currency-text="Jordanian Dinar" data-symbol="JD">JOD - Jordanian Dinar</option>
										<option value="JPY" <?php echo ( $firstCurrencyCode == "JPY" ) ? 'selected':'';?> data-currency-text="Japanese Yen" data-symbol="¥">JPY - Japanese Yen</option>
										<option value="KES" <?php echo ( $firstCurrencyCode == "KES" ) ? 'selected':'';?> data-currency-text="Kenyan Shilling" data-symbol="KSh">KES - Kenyan Shilling</option>
										<option value="KGS" <?php echo ( $firstCurrencyCode == "KGS" ) ? 'selected':'';?> data-currency-text="Kyrgystani Som" data-symbol="лв">KGS - Kyrgystani Som</option>
										<option value="KHR" <?php echo ( $firstCurrencyCode == "KHR" ) ? 'selected':'';?> data-currency-text="Cambodian Riel" data-symbol="៛">KHR - Cambodian Riel</option>
										<option value="KMF" <?php echo ( $firstCurrencyCode == "KMF" ) ? 'selected':'';?> data-currency-text="Comorian Franc" data-symbol="CF">KMF - Comorian Franc</option>
										<option value="KPW" <?php echo ( $firstCurrencyCode == "KPW" ) ? 'selected':'';?> data-currency-text="North Korean Won" data-symbol="₩">KPW - North Korean Won</option>
										<option value="KRW" <?php echo ( $firstCurrencyCode == "KRW" ) ? 'selected':'';?> data-currency-text="South Korean Won" data-symbol="₩">KRW - South Korean Won</option>
										<option value="KWD" <?php echo ( $firstCurrencyCode == "KWD" ) ? 'selected':'';?> data-currency-text="Kuwaiti Dinar" data-symbol="KD">KWD - Kuwaiti Dinar</option>
										<option value="KYD" <?php echo ( $firstCurrencyCode == "KYD" ) ? 'selected':'';?> data-currency-text="Cayman Islands Dollar" data-symbol="$">KYD - Cayman Islands Dollar</option>
										<option value="KZT" <?php echo ( $firstCurrencyCode == "KZT" ) ? 'selected':'';?> data-currency-text="Kazakhstani Tenge" data-symbol="лв">KZT - Kazakhstani Tenge</option>
										<option value="LAK" <?php echo ( $firstCurrencyCode == "LAK" ) ? 'selected':'';?> data-currency-text="Laotian Kip" data-symbol="₭">LAK - Laotian Kip</option>
										<option value="LBP" <?php echo ( $firstCurrencyCode == "LBP" ) ? 'selected':'';?> data-currency-text="Lebanese Pound" data-symbol="£">LBP - Lebanese Pound</option>
										<option value="LKR" <?php echo ( $firstCurrencyCode == "LKR" ) ? 'selected':'';?> data-currency-text="Sri Lankan Rupee" data-symbol="₨">LKR - Sri Lankan Rupee</option>
										<option value="LRD" <?php echo ( $firstCurrencyCode == "LRD" ) ? 'selected':'';?> data-currency-text="Liberian Dollar" data-symbol="$">LRD - Liberian Dollar</option>
										<option value="LSL" <?php echo ( $firstCurrencyCode == "LSL" ) ? 'selected':'';?> data-currency-text="Lesotho Loti" data-symbol="M">LSL - Lesotho Loti</option>
										<option value="LYD" <?php echo ( $firstCurrencyCode == "LYD" ) ? 'selected':'';?> data-currency-text="Libyan Dinar" data-symbol="LD">LYD - Libyan Dinar</option>
										<option value="MAD" <?php echo ( $firstCurrencyCode == "MAD" ) ? 'selected':'';?> data-currency-text="Moroccan Dirham" data-symbol="MAD">MAD - Moroccan Dirham</option>
										<option value="MDL" <?php echo ( $firstCurrencyCode == "MDL" ) ? 'selected':'';?> data-currency-text="Moldovan Leu" data-symbol="lei">MDL - Moldovan Leu</option>
										<option value="MGA" <?php echo ( $firstCurrencyCode == "MGA" ) ? 'selected':'';?> data-currency-text="Malagasy Ariary" data-symbol="Ar">MGA - Malagasy Ariary</option>
										<option value="MKD" <?php echo ( $firstCurrencyCode == "MKD" ) ? 'selected':'';?> data-currency-text="Macedonian Denar" data-symbol="ден">MKD - Macedonian Denar</option>
										<option value="MMK" <?php echo ( $firstCurrencyCode == "MMK" ) ? 'selected':'';?> data-currency-text="Myanma Kyat" data-symbol="K">MMK - Myanma Kyat</option>
										<option value="MNT" <?php echo ( $firstCurrencyCode == "MNT" ) ? 'selected':'';?> data-currency-text="Mongolian Tugrik" data-symbol="₮">MNT - Mongolian Tugrik</option>
										<option value="MOP" <?php echo ( $firstCurrencyCode == "MOP" ) ? 'selected':'';?> data-currency-text="Macanese Pataca" data-symbol="MOP$" >MOP - Macanese Pataca</option>
										<option value="MRO" <?php echo ( $firstCurrencyCode == "MRO" ) ? 'selected':'';?> data-currency-text="Mauritanian Ouguiya (pre-2018)" data-symbol="UM">MRO - Mauritanian Ouguiya (pre-2018)</option>
										<option value="MRU" <?php echo ( $firstCurrencyCode == "MRU" ) ? 'selected':'';?> data-currency-text="Mauritanian Ouguiya" data-symbol="UM">MRU - Mauritanian Ouguiya</option>
										<option value="MTL" <?php echo ( $firstCurrencyCode == "MTL" ) ? 'selected':'';?> data-currency-text="Maltese Lira" data-symbol="Lm">MTL - Maltese Lira</option>
										<option value="MUR" <?php echo ( $firstCurrencyCode == "MUR" ) ? 'selected':'';?> data-currency-text="Mauritian Rupee" data-symbol="₨">MUR - Mauritian Rupee</option>
										<option value="MVR" <?php echo ( $firstCurrencyCode == "MVR" ) ? 'selected':'';?> data-currency-text="Maldivian Rufiyaa" data-symbol="Rf">MVR - Maldivian Rufiyaa</option>
										<option value="MWK" <?php echo ( $firstCurrencyCode == "MWK" ) ? 'selected':'';?> data-currency-text="Malawian Kwacha" data-symbol="MK">MWK - Malawian Kwacha</option>
										<option value="MXN" <?php echo ( $firstCurrencyCode == "MXN" ) ? 'selected':'';?> data-currency-text="Mexican Peso" data-symbol="$">MXN - Mexican Peso</option>
										<option value="MYR" <?php echo ( $firstCurrencyCode == "MYR" ) ? 'selected':'';?> data-currency-text="Malaysian Ringgit" data-symbol="RM">MYR - Malaysian Ringgit</option>
										<option value="MZN" <?php echo ( $firstCurrencyCode == "MZN" ) ? 'selected':'';?> data-currency-text="Mozambican Metical" data-symbol="MT">MZN - Mozambican Metical</option>
										<option value="NAD" <?php echo ( $firstCurrencyCode == "NAD" ) ? 'selected':'';?> data-currency-text="Namibian Dollar" data-symbol="$">NAD - Namibian Dollar</option>
										<option value="NGN" <?php echo ( $firstCurrencyCode == "NGN" ) ? 'selected':'';?> data-currency-text="Nigerian Naira" data-symbol="₦">NGN - Nigerian Naira</option>
										<option value="NIO" <?php echo ( $firstCurrencyCode == "NIO" ) ? 'selected':'';?> data-currency-text="Nicaraguan Córdoba" data-symbol="C$">NIO - Nicaraguan Córdoba</option>
										<option value="NOK" <?php echo ( $firstCurrencyCode == "NOK" ) ? 'selected':'';?> data-currency-text="Norwegian Krone" data-symbol="kr">NOK - Norwegian Krone</option>
										<option value="NPR" <?php echo ( $firstCurrencyCode == "NPR" ) ? 'selected':'';?> data-currency-text="Nepalese Rupee" data-symbol="₨">NPR - Nepalese Rupee</option>
										<option value="NZD" <?php echo ( $firstCurrencyCode == "NZD" ) ? 'selected':'';?> data-currency-text="New Zealand Dollar" data-symbol="$">NZD - New Zealand Dollar</option>
										<option value="OMR" <?php echo ( $firstCurrencyCode == "OMR" ) ? 'selected':'';?> data-currency-text="Omani Rial" data-symbol="﷼">OMR - Omani Rial</option>
										<option value="PAB" <?php echo ( $firstCurrencyCode == "PAB" ) ? 'selected':'';?> data-currency-text="Panamanian Balboa" data-symbol="B/.">PAB - Panamanian Balboa</option>
										<option value="PEN" <?php echo ( $firstCurrencyCode == "PEN" ) ? 'selected':'';?> data-currency-text="Peruvian Nuevo Sol" data-symbol="S/.">PEN - Peruvian Nuevo Sol</option>
										<option value="PGK" <?php echo ( $firstCurrencyCode == "PGK" ) ? 'selected':'';?> data-currency-text="Papua New Guinean Kina" data-symbol="K">PGK - Papua New Guinean Kina</option>
										<option value="PHP" <?php echo ( $firstCurrencyCode == "PHP" ) ? 'selected':'';?> data-currency-text="Philippine Peso" data-symbol="₱">PHP - Philippine Peso</option>
										<option value="PKR" <?php echo ( $firstCurrencyCode == "PKR" ) ? 'selected':'';?> data-currency-text="Pakistani Rupee" data-symbol="₨">PKR - Pakistani Rupee</option>
										<option value="PLN" <?php echo ( $firstCurrencyCode == "PLN" ) ? 'selected':'';?> data-currency-text="Polish Zloty" data-symbol="zł">PLN - Polish Zloty</option>
										<option value="PYG" <?php echo ( $firstCurrencyCode == "PYG" ) ? 'selected':'';?> data-currency-text="Paraguayan Guarani" data-symbol="Gs">PYG - Paraguayan Guarani</option>
										<option value="QAR" <?php echo ( $firstCurrencyCode == "QAR" ) ? 'selected':'';?> data-currency-text="Qatari Rial" data-symbol="﷼">QAR - Qatari Rial</option>
										<option value="RON" <?php echo ( $firstCurrencyCode == "RON" ) ? 'selected':'';?> data-currency-text="Romanian Leu" data-symbol="lei">RON - Romanian Leu</option>
										<option value="RSD" <?php echo ( $firstCurrencyCode == "RSD" ) ? 'selected':'';?> data-currency-text="Serbian Dinar" data-symbol="Дин.">RSD - Serbian Dinar</option>
										<option value="RUB" <?php echo ( $firstCurrencyCode == "RUB" ) ? 'selected':'';?> data-currency-text="Russian Ruble" data-symbol="₽">RUB - Russian Ruble</option>
										<option value="RWF" <?php echo ( $firstCurrencyCode == "RWF" ) ? 'selected':'';?> data-currency-text="Rwandan Franc" data-symbol="R₣">RWF - Rwandan Franc</option>
										<option value="SAR" <?php echo ( $firstCurrencyCode == "SAR" ) ? 'selected':'';?> data-currency-text="Saudi Riyal" data-symbol="﷼">SAR - Saudi Riyal</option>
										<option value="SBD" <?php echo ( $firstCurrencyCode == "SBD" ) ? 'selected':'';?> data-currency-text="Solomon Islands Dollar" data-symbol="$">SBD - Solomon Islands Dollar</option>
										<option value="SCR" <?php echo ( $firstCurrencyCode == "SCR" ) ? 'selected':'';?> data-currency-text="Seychellois Rupee" data-symbol="₨">SCR - Seychellois Rupee</option>
										<option value="SDG" <?php echo ( $firstCurrencyCode == "SDG" ) ? 'selected':'';?> data-currency-text="Sudanese Pound" data-symbol="ج.س.">SDG - Sudanese Pound</option>
										<option value="SEK" <?php echo ( $firstCurrencyCode == "SEK" ) ? 'selected':'';?> data-currency-text="Swedish Krona" data-symbol="kr">SEK - Swedish Krona</option>
										<option value="SGD" <?php echo ( $firstCurrencyCode == "SGD" ) ? 'selected':'';?> data-currency-text="Singapore Dollar" data-symbol="$">SGD - Singapore Dollar</option>
										<option value="SHP" <?php echo ( $firstCurrencyCode == "SHP" ) ? 'selected':'';?> data-currency-text="Saint Helena Pound" data-symbol="£">SHP - Saint Helena Pound</option>
										<option value="SLL" <?php echo ( $firstCurrencyCode == "SLL" ) ? 'selected':'';?> data-currency-text="Sierra Leonean Leone" data-symbol="Le">SLL - Sierra Leonean Leone</option>
										<option value="SOS" <?php echo ( $firstCurrencyCode == "SOS" ) ? 'selected':'';?> data-currency-text="Somali Shilling" data-symbol="S">SOS - Somali Shilling</option>
										<option value="SRD" <?php echo ( $firstCurrencyCode == "SRD" ) ? 'selected':'';?> data-currency-text="Surinamese Dollar" data-symbol="$">SRD - Surinamese Dollar</option>
										<option value="SSP" <?php echo ( $firstCurrencyCode == "SSP" ) ? 'selected':'';?> data-currency-text="South Sudanese Pound" data-symbol="$">SSP - South Sudanese Pound</option>
										<option value="STD" <?php echo ( $firstCurrencyCode == "STD" ) ? 'selected':'';?> data-currency-text="São Tomé and Príncipe Dobra (pre-2018)" data-symbol="Db">STD - São Tomé and Príncipe Dobra (pre-2018)</option>
										<option value="STN" <?php echo ( $firstCurrencyCode == "STN" ) ? 'selected':'';?> data-currency-text="São Tomé and Príncipe Dobra" data-symbol="Db">STN - São Tomé and Príncipe Dobra</option>
										<option value="SVC" <?php echo ( $firstCurrencyCode == "SVC" ) ? 'selected':'';?> data-currency-text="Salvadoran Colón" data-symbol="$">SVC - Salvadoran Colón</option>
										<option value="SYP" <?php echo ( $firstCurrencyCode == "SYP" ) ? 'selected':'';?> data-currency-text="Syrian Pound" data-symbol="£">SYP - Syrian Pound</option>
										<option value="SZL" <?php echo ( $firstCurrencyCode == "SZL" ) ? 'selected':'';?> data-currency-text="Swazi Lilangeni" data-symbol="E">SZL - Swazi Lilangeni</option>
										<option value="THB" <?php echo ( $firstCurrencyCode == "THB" ) ? 'selected':'';?> data-currency-text="Thai Baht" data-symbol="฿">THB - Thai Baht</option>
										<option value="TJS" <?php echo ( $firstCurrencyCode == "TJS" ) ? 'selected':'';?> data-currency-text="Tajikistani Somoni" data-symbol="SM">TJS - Tajikistani Somoni</option>
										<option value="TMT" <?php echo ( $firstCurrencyCode == "TMT" ) ? 'selected':'';?> data-currency-text="Turkmenistani Manat" data-symbol="T">TMT - Turkmenistani Manat</option>
										<option value="TND" <?php echo ( $firstCurrencyCode == "TND" ) ? 'selected':'';?> data-currency-text="Tunisian Dinar" data-symbol="د.ت">TND - Tunisian Dinar</option>
										<option value="TOP" <?php echo ( $firstCurrencyCode == "TOP" ) ? 'selected':'';?> data-currency-text="Tongan Paʻanga" data-symbol="T$">TOP - Tongan Paʻanga</option>
										<option value="TRY" <?php echo ( $firstCurrencyCode == "TRY" ) ? 'selected':'';?> data-currency-text="Turkish Lira" data-symbol="₺">TRY - Turkish Lira</option>
										<option value="TTD" <?php echo ( $firstCurrencyCode == "TTD" ) ? 'selected':'';?> data-currency-text="Trinidad and Tobago Dollar" data-symbol="TT$">TTD - Trinidad and Tobago Dollar</option>
										<option value="TWD" <?php echo ( $firstCurrencyCode == "TWD" ) ? 'selected':'';?> data-currency-text="New Taiwan Dollar" data-symbol="NT$">TWD - New Taiwan Dollar</option>
										<option value="TZS" <?php echo ( $firstCurrencyCode == "TZS" ) ? 'selected':'';?> data-currency-text="Tanzanian Shilling" data-symbol="TSh">TZS - Tanzanian Shilling</option>
										<option value="USD" <?php echo ( $firstCurrencyCode == "USD" ) ? 'selected':'';?> data-currency-text="US Dollar" data-symbol="$">USD - US Dollar</option>
										<option value="UAH" <?php echo ( $firstCurrencyCode == "UAH" ) ? 'selected':'';?> data-currency-text="Ukrainian Hryvnia" data-symbol="₴">UAH - Ukrainian Hryvnia</option>
										<option value="UGX" <?php echo ( $firstCurrencyCode == "UGX" ) ? 'selected':'';?> data-currency-text="Ugandan Shilling" data-symbol="USh">UGX - Ugandan Shilling</option>
										<option value="UYU" <?php echo ( $firstCurrencyCode == "UYU" ) ? 'selected':'';?> data-currency-text="Uruguayan Peso" data-symbol="$U">UYU - Uruguayan Peso</option>
										<option value="UZS" <?php echo ( $firstCurrencyCode == "UZS" ) ? 'selected':'';?> data-currency-text="Uzbekistan Som" data-symbol="лв">UZS - Uzbekistan Som</option>
										<option value="VES" <?php echo ( $firstCurrencyCode == "VES" ) ? 'selected':'';?> data-currency-text="Venezuelan Bolívar Soberano" data-symbol="Bs.F.">VES - Venezuelan Bolívar Soberano</option>
										<option value="VND" <?php echo ( $firstCurrencyCode == "VND" ) ? 'selected':'';?> data-currency-text="Vietnamese Dong" data-symbol="₫">VND - Vietnamese Dong</option>
										<option value="VUV" <?php echo ( $firstCurrencyCode == "VUV" ) ? 'selected':'';?> data-currency-text="Vanuatu Vatu" data-symbol="VT">VUV - Vanuatu Vatu</option>
										<option value="WST" <?php echo ( $firstCurrencyCode == "WST" ) ? 'selected':'';?> data-currency-text="Samoan Tala" data-symbol="WS$">WST - Samoan Tala</option>
										<option value="XAF" <?php echo ( $firstCurrencyCode == "XAF" ) ? 'selected':'';?> data-currency-text="CFA Franc BEAC" data-symbol="FCFA">XAF - CFA Franc BEAC</option>
										<option value="XAG" <?php echo ( $firstCurrencyCode == "XAG" ) ? 'selected':'';?> data-currency-text="Silver (troy ounce)" data-symbol="XAG">XAG - Silver (troy ounce)</option>
										<option value="XAU" <?php echo ( $firstCurrencyCode == "XAU" ) ? 'selected':'';?> data-currency-text="Gold (troy ounce)" data-symbol="XAU">XAU - Gold (troy ounce)</option>
										<option value="XCD" <?php echo ( $firstCurrencyCode == "XCD" ) ? 'selected':'';?> data-currency-text="East Caribbean Dollar" data-symbol="$">XCD - East Caribbean Dollar</option>
										<option value="XDR" <?php echo ( $firstCurrencyCode == "XDR" ) ? 'selected':'';?> data-currency-text="Special Drawing Rights" data-symbol="XDR">XDR - Special Drawing Rights</option>
										<option value="XOF" <?php echo ( $firstCurrencyCode == "XOF" ) ? 'selected':'';?> data-currency-text="CFA Franc BCEAO" data-symbol="CFA">XOF - CFA Franc BCEAO</option>
										<option value="XPD" <?php echo ( $firstCurrencyCode == "XPD" ) ? 'selected':'';?> data-currency-text="Palladium Ounce" data-symbol="XPD">XPD - Palladium Ounce</option>
										<option value="XPF" <?php echo ( $firstCurrencyCode == "XPF" ) ? 'selected':'';?> data-currency-text="CFP Franc" data-symbol="₣">XPF - CFP Franc</option>
										<option value="XPT" <?php echo ( $firstCurrencyCode == "XPT" ) ? 'selected':'';?> data-currency-text="Platinum Ounce" data-symbol="XPT">XPT - Platinum Ounce</option>
										<option value="YER" <?php echo ( $firstCurrencyCode == "YER" ) ? 'selected':'';?> data-currency-text="Yemeni Rial" data-symbol="﷼">YER - Yemeni Rial</option>
										<option value="ZAR" <?php echo ( $firstCurrencyCode == "ZAR" ) ? 'selected':'';?> data-currency-text="South African Rand" data-symbol="R">ZAR - South African Rand</option>
										<option value="ZMK" <?php echo ( $firstCurrencyCode == "ZMK" ) ? 'selected':'';?> data-currency-text="Zambian Kwacha (pre-2013)" data-symbol="ZK">ZMK - Zambian Kwacha (pre-2013)</option>
										<option value="ZMW" <?php echo ( $firstCurrencyCode == "ZMW" ) ? 'selected':'';?> data-currency-text="Zambian Kwacha" data-symbol="ZK">ZMW - Zambian Kwacha</option>
									</select>
								</div>			 		
							</div>
						</div> 		
						
						<div class="form-group swap-element">
							<div class="swap-currency">
								<img class="swap-cuurency-img" src="<?php echo CURRENCY_PAIRS_ROOT_DIR_PUBLIC;?>/images/currency-symbol.png" alt="">
							</div>
						</div>
						
						<div class="form-group input-adjust-div">
							<div class="selectRow to_select">
								<label for="toCurrency"><?php _e("To","currency-pairs");?></label>
								<div class="dropdown-caret">
									<span class="open_toCurrency">
										<img src='<?php echo CURRENCY_PAIRS_ROOT_DIR_PUBLIC;?>/images/down-arrow.png'>
									</span>
									<select class="selectpicker des" id="toCurrency" name="toCurrency">									
										<option value="EUR" <?php echo ( $secondCurrencyCode == "EUR" ) ? 'selected':'';?> data-currency-text="Euro" data-symbol="€">EUR - Euro</option>
										<option value="AED" <?php echo ( $secondCurrencyCode == "AED" ) ? 'selected':'';?> data-currency-text="Emirates Dirham" data-symbol="د.إ">AED - Emirates Dirham</option>
										<option value="AFN" <?php echo ( $secondCurrencyCode == "AFN" ) ? 'selected':'';?> data-currency-text="Afghan Afghani" data-symbol="؋">AFN - Afghan Afghani</option>
										<option value="ALL" <?php echo ( $secondCurrencyCode == "ALL" ) ? 'selected':'';?> data-currency-text="Albanian Lek" data-symbol="L">ALL - Albanian Lek</option>
										<option value="AMD" <?php echo ( $secondCurrencyCode == "AMD" ) ? 'selected':'';?> data-currency-text="Armenian Dram" data-symbol="֏">AMD - Armenian Dram</option>
										<option value="ANG" <?php echo ( $secondCurrencyCode == "ANG" ) ? 'selected':'';?> data-currency-text="Netherlands Antillean Guilder" data-symbol="ƒ">ANG - Netherlands Antillean Guilder</option>
										<option value="AOA" <?php echo ( $secondCurrencyCode == "AOA" ) ? 'selected':'';?> data-currency-text="Angolan Kwanza" data-symbol="Kz">AOA - Angolan Kwanza</option>
										<option value="ARS" <?php echo ( $secondCurrencyCode == "ARS" ) ? 'selected':'';?> data-currency-text="Argentine Peso" data-symbol="$">ARS - Argentine Peso</option>
										<option value="AUD" <?php echo ( $secondCurrencyCode == "AUD" ) ? 'selected':'';?> data-currency-text="Australian Dollar" data-symbol="$">AUD - Australian Dollar</option>
										<option value="AWG" <?php echo ( $secondCurrencyCode == "AWG" ) ? 'selected':'';?> data-currency-text="Aruban Florin" data-symbol="ƒ">AWG - Aruban Florin</option>
										<option value="AZN" <?php echo ( $secondCurrencyCode == "AZN" ) ? 'selected':'';?> data-currency-text="Azerbaijani Manat" data-symbol="₼">AZN - Azerbaijani Manat</option>
										<option value="BAM" <?php echo ( $secondCurrencyCode == "BAM" ) ? 'selected':'';?> data-currency-text="Bosnia-Herzegovina Convertible Mark" data-symbol="KM">BAM - Bosnia-Herzegovina Convertible Mark</option>
										<option value="BBD" <?php echo ( $secondCurrencyCode == "BBD" ) ? 'selected':'';?> data-currency-text="Barbadian Dollar" data-symbol="$">BBD - Barbadian Dollar</option>
										<option value="BDT" <?php echo ( $secondCurrencyCode == "BDT" ) ? 'selected':'';?> data-currency-text="Bangladeshi Taka" data-symbol="৳">BDT - Bangladeshi Taka</option>
										<option value="BGN" <?php echo ( $secondCurrencyCode == "BGN" ) ? 'selected':'';?> data-currency-text="Bulgarian Lev" data-symbol="лв">BGN - Bulgarian Lev</option>
										<option value="BHD" <?php echo ( $secondCurrencyCode == "BHD" ) ? 'selected':'';?> data-currency-text="Bahraini Dinar" data-symbol=".د.ب">BHD - Bahraini Dinar</option>
										<option value="BIF" <?php echo ( $secondCurrencyCode == "BIF" ) ? 'selected':'';?> data-currency-text="Burundian Franc" data-symbol="FBu">BIF - Burundian Franc</option>
										<option value="BMD" <?php echo ( $secondCurrencyCode == "BMD" ) ? 'selected':'';?> data-currency-text="Bermudan Dollar" data-symbol="$">BMD - Bermudan Dollar</option>
										<option value="BND" <?php echo ( $secondCurrencyCode == "BND" ) ? 'selected':'';?> data-currency-text="Brunei Dollar" data-symbol="$">BND - Brunei Dollar</option>
										<option value="BOB" <?php echo ( $secondCurrencyCode == "BOB" ) ? 'selected':'';?> data-currency-text="Bolivian Boliviano" data-symbol="$b">BOB - Bolivian Boliviano</option>
										<option value="BRL" <?php echo ( $secondCurrencyCode == "BRL" ) ? 'selected':'';?> data-currency-text="Brazilian Real" data-symbol="R$">BRL - Brazilian Real</option>
										<option value="BSD" <?php echo ( $secondCurrencyCode == "BSD" ) ? 'selected':'';?> data-currency-text="Bahamian Dollar" data-symbol="$">BSD - Bahamian Dollar</option>
										<option value="BTC" <?php echo ( $secondCurrencyCode == "BTC" ) ? 'selected':'';?> data-currency-text="Bitcoin" data-symbol="฿">BTC - Bitcoin</option>
										<option value="BTN" <?php echo ( $secondCurrencyCode == "BTN" ) ? 'selected':'';?> data-currency-text="Bhutanese Ngultrum" data-symbol="Nu.">BTN - Bhutanese Ngultrum</option>
										<option value="BWP" <?php echo ( $secondCurrencyCode == "BWP" ) ? 'selected':'';?> data-currency-text="Botswanan Pula" data-symbol="P">BWP - Botswanan Pula</option>
										<option value="BYN" <?php echo ( $secondCurrencyCode == "BYN" ) ? 'selected':'';?> data-currency-text="Belarusian Ruble" data-symbol="Br">BYN - Belarusian Ruble</option>
										<option value="BYR" <?php echo ( $secondCurrencyCode == "BYR" ) ? 'selected':'';?> data-currency-text="Belarusian Ruble (pre-2016)" data-symbol="Br">BYR - Belarusian Ruble (pre-2016)</option>
										<option value="BZD" <?php echo ( $secondCurrencyCode == "BZD" ) ? 'selected':'';?> data-currency-text="Belize Dollar" data-symbol="BZ$">BZD - Belize Dollar</option>
										<option value="CAD" <?php echo ( $secondCurrencyCode == "CAD" ) ? 'selected':'';?> data-currency-text="Canadian Dollar" data-symbol="$">CAD - Canadian Dollar</option>
										<option value="CDF" <?php echo ( $secondCurrencyCode == "CDF" ) ? 'selected':'';?> data-currency-text="Congolese Franc" data-symbol="FC">CDF - Congolese Franc</option>
										<option value="CHF" <?php echo ( $secondCurrencyCode == "CHF" ) ? 'selected':'';?> data-currency-text="Swiss Franc" data-symbol="CHF">CHF - Swiss Franc</option>
										<option value="CLF" <?php echo ( $secondCurrencyCode == "CLF" ) ? 'selected':'';?> data-currency-text="Chilean Unit of Account (UF)" data-symbol="">CLF - Chilean Unit of Account (UF)</option>
										<option value="CLP" <?php echo ( $secondCurrencyCode == "CLP" ) ? 'selected':'';?> data-currency-text="Chilean Peso" data-symbol="$">CLP - Chilean Peso</option>
										<option value="CNH" <?php echo ( $secondCurrencyCode == "CNH" ) ? 'selected':'';?> data-currency-text="Chinese Yuan (Offshore)" data-symbol="¥">CNH - Chinese Yuan (Offshore)</option>
										<option value="CNY" <?php echo ( $secondCurrencyCode == "CNY" ) ? 'selected':'';?> data-currency-text="Chinese Yuan" data-symbol="¥">CNY - Chinese Yuan</option>
										<option value="COP" <?php echo ( $secondCurrencyCode == "COP" ) ? 'selected':'';?> data-currency-text="Colombian Peso" data-symbol="$">COP - Colombian Peso</option>
										<option value="CRC" <?php echo ( $secondCurrencyCode == "CRC" ) ? 'selected':'';?> data-currency-text="Costa Rican Colón" data-symbol="₡">CRC - Costa Rican Colón</option>
										<option value="CUC" <?php echo ( $secondCurrencyCode == "CUC" ) ? 'selected':'';?> data-currency-text="Cuban Convertible Peso" data-symbol="$">CUC - Cuban Convertible Peso</option>
										<option value="CUP" <?php echo ( $secondCurrencyCode == "CUP" ) ? 'selected':'';?> data-currency-text="Cuban Peso" data-symbol="₱">CUP - Cuban Peso</option>
										<option value="CVE" <?php echo ( $secondCurrencyCode == "CVE" ) ? 'selected':'';?> data-currency-text="Cape Verdean Escudo" data-symbol="$">CVE - Cape Verdean Escudo</option>
										<option value="CZK" <?php echo ( $secondCurrencyCode == "CZK" ) ? 'selected':'';?> data-currency-text="Czech Republic Koruna" data-symbol="Kč">CZK - Czech Republic Koruna</option>
										<option value="DJF" <?php echo ( $secondCurrencyCode == "DJF" ) ? 'selected':'';?> data-currency-text="Djiboutian Franc" data-symbol="Fdj">DJF - Djiboutian Franc</option>
										<option value="DKK" <?php echo ( $secondCurrencyCode == "DKK" ) ? 'selected':'';?> data-currency-text="Danish Krone" data-symbol="kr">DKK - Danish Krone</option>
										<option value="DOP" <?php echo ( $secondCurrencyCode == "DOP" ) ? 'selected':'';?> data-currency-text="Dominican Peso" data-symbol="RD$">DOP - Dominican Peso</option>
										<option value="DZD" <?php echo ( $secondCurrencyCode == "DZD" ) ? 'selected':'';?> data-currency-text="Algerian Dinar" data-symbol="دج">DZD - Algerian Dinar</option>
										<option value="EEK" <?php echo ( $secondCurrencyCode == "EEK" ) ? 'selected':'';?> data-currency-text="Estonian Kroon" data-symbol="kr">EEK - Estonian Kroon</option>
										<option value="EGP" <?php echo ( $secondCurrencyCode == "EGP" ) ? 'selected':'';?> data-currency-text="Egyptian Pound" data-symbol="£">EGP - Egyptian Pound</option>
										<option value="ERN" <?php echo ( $secondCurrencyCode == "ERN" ) ? 'selected':'';?> data-currency-text="Eritrean Nakfa" data-symbol="Nfk">ERN - Eritrean Nakfa</option>
										<option value="ETB" <?php echo ( $secondCurrencyCode == "ETB" ) ? 'selected':'';?> data-currency-text="Ethiopian Birr								" data-symbol="Br">ETB - Ethiopian Birr</option>								
										<option value="FJD" <?php echo ( $secondCurrencyCode == "FJD" ) ? 'selected':'';?> data-currency-text="Fijian Dollar" data-symbol="$">FJD - Fijian Dollar</option>
										<option value="FJD" <?php echo ( $secondCurrencyCode == "FJD" ) ? 'selected':'';?> data-currency-text="Fijian Dollar" data-symbol="$">FJD - Fijian Dollar</option>
										<option value="FKP" <?php echo ( $secondCurrencyCode == "FKP" ) ? 'selected':'';?> data-currency-text="Falkland Islands Pound" data-symbol="£">FKP - Falkland Islands Pound</option>
										<option value="GBP" <?php echo ( $secondCurrencyCode == "GBP" ) ? 'selected':'';?> data-currency-text="British Pound Sterling" data-symbol="£">GBP - British Pound Sterling</option>
										<option value="GEL" <?php echo ( $secondCurrencyCode == "GEL" ) ? 'selected':'';?> data-currency-text="Georgian Lari" data-symbol="₾">GEL - Georgian Lari</option>
										<option value="GGP" <?php echo ( $secondCurrencyCode == "GGP" ) ? 'selected':'';?> data-currency-text="Guernsey Pound" data-symbol="£">GGP - Guernsey Pound</option>
										<option value="GHS" <?php echo ( $secondCurrencyCode == "GHS" ) ? 'selected':'';?> data-currency-text="Ghanaian Cedi" data-symbol="GH₵">GHS - Ghanaian Cedi</option>
										<option value="GIP" <?php echo ( $secondCurrencyCode == "GIP" ) ? 'selected':'';?> data-currency-text="Gibraltar Pound" data-symbol="£">GIP - Gibraltar Pound</option>
										<option value="GMD" <?php echo ( $secondCurrencyCode == "GMD" ) ? 'selected':'';?> data-currency-text="Gambian Dalasi" data-symbol="D">GMD - Gambian Dalasi</option>
										<option value="GNF" <?php echo ( $secondCurrencyCode == "GNF" ) ? 'selected':'';?> data-currency-text="Guinean Franc" data-symbol="FG">GNF - Guinean Franc</option>
										<option value="GTQ" <?php echo ( $secondCurrencyCode == "GTQ" ) ? 'selected':'';?> data-currency-text="Guatemalan Quetzal" data-symbol="Q">GTQ - Guatemalan Quetzal</option>
										<option value="GYD" <?php echo ( $secondCurrencyCode == "GYD" ) ? 'selected':'';?> data-currency-text="Guyanaese Dollar" data-symbol="$">GYD - Guyanaese Dollar</option>
										<option value="HKD" <?php echo ( $secondCurrencyCode == "HKD" ) ? 'selected':'';?> data-currency-text="Hong Kong Dollar" data-symbol="$">HKD - Hong Kong Dollar</option>
										<option value="HNL" <?php echo ( $secondCurrencyCode == "HNL" ) ? 'selected':'';?> data-currency-text="Honduran Lempira" data-symbol="L">HNL - Honduran Lempira</option>
										<option value="HRK" <?php echo ( $secondCurrencyCode == "HRK" ) ? 'selected':'';?> data-currency-text="Croatian Kuna" data-symbol="kn">HRK - Croatian Kuna</option>
										<option value="HTG" <?php echo ( $secondCurrencyCode == "HTG" ) ? 'selected':'';?> data-currency-text="Haitian Gourde" data-symbol="G">HTG - Haitian Gourde</option>
										<option value="HUF" <?php echo ( $secondCurrencyCode == "HUF" ) ? 'selected':'';?> data-currency-text="Hungarian Forint" data-symbol="Ft">HUF - Hungarian Forint</option>
										<option value="IDR" <?php echo ( $secondCurrencyCode == "IDR" ) ? 'selected':'';?> data-currency-text="Indonesian Rupiah" data-symbol="Rp">IDR - Indonesian Rupiah</option>
										<option value="ILS" <?php echo ( $secondCurrencyCode == "ILS" ) ? 'selected':'';?> data-currency-text="Israeli New Sheqel" data-symbol="₪">ILS - Israeli New Sheqel</option>
										<option value="IMP" <?php echo ( $secondCurrencyCode == "IMP" ) ? 'selected':'';?> data-currency-text="Manx pound" data-symbol="£">IMP - Manx pound</option>
										<option value="INR" <?php echo ( $secondCurrencyCode == "INR" ) ? 'selected':'';?> data-currency-text="Indian Rupee" data-symbol="₹">INR - Indian Rupee</option>
										<option value="IQD" <?php echo ( $secondCurrencyCode == "IQD" ) ? 'selected':'';?> data-currency-text="Iraqi Dinar" data-symbol="ع.د">IQD - Iraqi Dinar</option>
										<option value="IRR" <?php echo ( $secondCurrencyCode == "IRR" ) ? 'selected':'';?> data-currency-text="Iranian Rial" data-symbol="﷼">IRR - Iranian Rial</option>
										<option value="ISK" <?php echo ( $secondCurrencyCode == "ISK" ) ? 'selected':'';?> data-currency-text="Icelandic Króna" data-symbol="kr">ISK - Icelandic Króna</option>
										<option value="JEP" <?php echo ( $secondCurrencyCode == "JEP" ) ? 'selected':'';?> data-currency-text="Jersey Pound" data-symbol="£">JEP - Jersey Pound</option>
										<option value="JMD" <?php echo ( $secondCurrencyCode == "JMD" ) ? 'selected':'';?> data-currency-text="Jamaican Dollar" data-symbol="J$">JMD - Jamaican Dollar</option>
										<option value="JOD" <?php echo ( $secondCurrencyCode == "JOD" ) ? 'selected':'';?> data-currency-text="Jordanian Dinar" data-symbol="JD">JOD - Jordanian Dinar</option>
										<option value="JPY" <?php echo ( $secondCurrencyCode == "JPY" ) ? 'selected':'';?> data-currency-text="Japanese Yen" data-symbol="¥">JPY - Japanese Yen</option>
										<option value="KES" <?php echo ( $secondCurrencyCode == "KES" ) ? 'selected':'';?> data-currency-text="Kenyan Shilling" data-symbol="KSh">KES - Kenyan Shilling</option>
										<option value="KGS" <?php echo ( $secondCurrencyCode == "KGS" ) ? 'selected':'';?> data-currency-text="Kyrgystani Som" data-symbol="лв">KGS - Kyrgystani Som</option>
										<option value="KHR" <?php echo ( $secondCurrencyCode == "KHR" ) ? 'selected':'';?> data-currency-text="Cambodian Riel" data-symbol="៛">KHR - Cambodian Riel</option>
										<option value="KMF" <?php echo ( $secondCurrencyCode == "KMF" ) ? 'selected':'';?> data-currency-text="Comorian Franc" data-symbol="CF">KMF - Comorian Franc</option>
										<option value="KPW" <?php echo ( $secondCurrencyCode == "KPW" ) ? 'selected':'';?> data-currency-text="North Korean Won" data-symbol="₩">KPW - North Korean Won</option>
										<option value="KRW" <?php echo ( $secondCurrencyCode == "KRW" ) ? 'selected':'';?> data-currency-text="South Korean Won" data-symbol="₩">KRW - South Korean Won</option>
										<option value="KWD" <?php echo ( $secondCurrencyCode == "KWD" ) ? 'selected':'';?> data-currency-text="Kuwaiti Dinar" data-symbol="KD">KWD - Kuwaiti Dinar</option>
										<option value="KYD" <?php echo ( $secondCurrencyCode == "KYD" ) ? 'selected':'';?> data-currency-text="Cayman Islands Dollar" data-symbol="$">KYD - Cayman Islands Dollar</option>
										<option value="KZT" <?php echo ( $secondCurrencyCode == "KZT" ) ? 'selected':'';?> data-currency-text="Kazakhstani Tenge" data-symbol="лв">KZT - Kazakhstani Tenge</option>
										<option value="LAK" <?php echo ( $secondCurrencyCode == "LAK" ) ? 'selected':'';?> data-currency-text="Laotian Kip" data-symbol="₭">LAK - Laotian Kip</option>
										<option value="LBP" <?php echo ( $secondCurrencyCode == "LBP" ) ? 'selected':'';?> data-currency-text="Lebanese Pound" data-symbol="£">LBP - Lebanese Pound</option>
										<option value="LKR" <?php echo ( $secondCurrencyCode == "LKR" ) ? 'selected':'';?> data-currency-text="Sri Lankan Rupee" data-symbol="₨">LKR - Sri Lankan Rupee</option>
										<option value="LRD" <?php echo ( $secondCurrencyCode == "LRD" ) ? 'selected':'';?> data-currency-text="Liberian Dollar" data-symbol="$">LRD - Liberian Dollar</option>
										<option value="LSL" <?php echo ( $secondCurrencyCode == "LSL" ) ? 'selected':'';?> data-currency-text="Lesotho Loti" data-symbol="M">LSL - Lesotho Loti</option>
										<option value="LYD" <?php echo ( $secondCurrencyCode == "LYD" ) ? 'selected':'';?> data-currency-text="Libyan Dinar" data-symbol="LD">LYD - Libyan Dinar</option>
										<option value="MAD" <?php echo ( $secondCurrencyCode == "MAD" ) ? 'selected':'';?> data-currency-text="Moroccan Dirham" data-symbol="MAD">MAD - Moroccan Dirham</option>
										<option value="MDL" <?php echo ( $secondCurrencyCode == "MDL" ) ? 'selected':'';?> data-currency-text="Moldovan Leu" data-symbol="lei">MDL - Moldovan Leu</option>
										<option value="MGA" <?php echo ( $secondCurrencyCode == "MGA" ) ? 'selected':'';?> data-currency-text="Malagasy Ariary" data-symbol="Ar">MGA - Malagasy Ariary</option>
										<option value="MKD" <?php echo ( $secondCurrencyCode == "MKD" ) ? 'selected':'';?> data-currency-text="Macedonian Denar" data-symbol="ден">MKD - Macedonian Denar</option>
										<option value="MMK" <?php echo ( $secondCurrencyCode == "MMK" ) ? 'selected':'';?> data-currency-text="Myanma Kyat" data-symbol="K">MMK - Myanma Kyat</option>
										<option value="MNT" <?php echo ( $secondCurrencyCode == "MNT" ) ? 'selected':'';?> data-currency-text="Mongolian Tugrik" data-symbol="₮">MNT - Mongolian Tugrik</option>
										<option value="MOP" <?php echo ( $secondCurrencyCode == "MOP" ) ? 'selected':'';?> data-currency-text="Macanese Pataca" data-symbol="MOP$" >MOP - Macanese Pataca</option>
										<option value="MRO" <?php echo ( $secondCurrencyCode == "MRO" ) ? 'selected':'';?> data-currency-text="Mauritanian Ouguiya (pre-2018)" data-symbol="UM">MRO - Mauritanian Ouguiya (pre-2018)</option>
										<option value="MRU" <?php echo ( $secondCurrencyCode == "MRU" ) ? 'selected':'';?> data-currency-text="Mauritanian Ouguiya" data-symbol="UM">MRU - Mauritanian Ouguiya</option>
										<option value="MTL" <?php echo ( $secondCurrencyCode == "MTL" ) ? 'selected':'';?> data-currency-text="Maltese Lira" data-symbol="Lm">MTL - Maltese Lira</option>
										<option value="MUR" <?php echo ( $secondCurrencyCode == "MUR" ) ? 'selected':'';?> data-currency-text="Mauritian Rupee" data-symbol="₨">MUR - Mauritian Rupee</option>
										<option value="MVR" <?php echo ( $secondCurrencyCode == "MVR" ) ? 'selected':'';?> data-currency-text="Maldivian Rufiyaa" data-symbol="Rf">MVR - Maldivian Rufiyaa</option>
										<option value="MWK" <?php echo ( $secondCurrencyCode == "MWK" ) ? 'selected':'';?> data-currency-text="Malawian Kwacha" data-symbol="MK">MWK - Malawian Kwacha</option>
										<option value="MXN" <?php echo ( $secondCurrencyCode == "MXN" ) ? 'selected':'';?> data-currency-text="Mexican Peso" data-symbol="$">MXN - Mexican Peso</option>
										<option value="MYR" <?php echo ( $secondCurrencyCode == "MYR" ) ? 'selected':'';?> data-currency-text="Malaysian Ringgit" data-symbol="RM">MYR - Malaysian Ringgit</option>
										<option value="MZN" <?php echo ( $secondCurrencyCode == "MZN" ) ? 'selected':'';?> data-currency-text="Mozambican Metical" data-symbol="MT">MZN - Mozambican Metical</option>
										<option value="NAD" <?php echo ( $secondCurrencyCode == "NAD" ) ? 'selected':'';?> data-currency-text="Namibian Dollar" data-symbol="$">NAD - Namibian Dollar</option>
										<option value="NGN" <?php echo ( $secondCurrencyCode == "NGN" ) ? 'selected':'';?> data-currency-text="Nigerian Naira" data-symbol="₦">NGN - Nigerian Naira</option>
										<option value="NIO" <?php echo ( $secondCurrencyCode == "NIO" ) ? 'selected':'';?> data-currency-text="Nicaraguan Córdoba" data-symbol="C$">NIO - Nicaraguan Córdoba</option>
										<option value="NOK" <?php echo ( $secondCurrencyCode == "NOK" ) ? 'selected':'';?> data-currency-text="Norwegian Krone" data-symbol="kr">NOK - Norwegian Krone</option>
										<option value="NPR" <?php echo ( $secondCurrencyCode == "NPR" ) ? 'selected':'';?> data-currency-text="Nepalese Rupee" data-symbol="₨">NPR - Nepalese Rupee</option>
										<option value="NZD" <?php echo ( $secondCurrencyCode == "NZD" ) ? 'selected':'';?> data-currency-text="New Zealand Dollar" data-symbol="$">NZD - New Zealand Dollar</option>
										<option value="OMR" <?php echo ( $secondCurrencyCode == "OMR" ) ? 'selected':'';?> data-currency-text="Omani Rial" data-symbol="﷼">OMR - Omani Rial</option>
										<option value="PAB" <?php echo ( $secondCurrencyCode == "PAB" ) ? 'selected':'';?> data-currency-text="Panamanian Balboa" data-symbol="B/.">PAB - Panamanian Balboa</option>
										<option value="PEN" <?php echo ( $secondCurrencyCode == "PEN" ) ? 'selected':'';?> data-currency-text="Peruvian Nuevo Sol" data-symbol="S/.">PEN - Peruvian Nuevo Sol</option>
										<option value="PGK" <?php echo ( $secondCurrencyCode == "PGK" ) ? 'selected':'';?> data-currency-text="Papua New Guinean Kina" data-symbol="K">PGK - Papua New Guinean Kina</option>
										<option value="PHP" <?php echo ( $secondCurrencyCode == "PHP" ) ? 'selected':'';?> data-currency-text="Philippine Peso" data-symbol="₱">PHP - Philippine Peso</option>
										<option value="PKR" <?php echo ( $secondCurrencyCode == "PKR" ) ? 'selected':'';?> data-currency-text="Pakistani Rupee" data-symbol="₨">PKR - Pakistani Rupee</option>
										<option value="PLN" <?php echo ( $secondCurrencyCode == "PLN" ) ? 'selected':'';?> data-currency-text="Polish Zloty" data-symbol="zł">PLN - Polish Zloty</option>
										<option value="PYG" <?php echo ( $secondCurrencyCode == "PYG" ) ? 'selected':'';?> data-currency-text="Paraguayan Guarani" data-symbol="Gs">PYG - Paraguayan Guarani</option>
										<option value="QAR" <?php echo ( $secondCurrencyCode == "QAR" ) ? 'selected':'';?> data-currency-text="Qatari Rial" data-symbol="﷼">QAR - Qatari Rial</option>
										<option value="RON" <?php echo ( $secondCurrencyCode == "RON" ) ? 'selected':'';?> data-currency-text="Romanian Leu" data-symbol="lei">RON - Romanian Leu</option>
										<option value="RSD" <?php echo ( $secondCurrencyCode == "RSD" ) ? 'selected':'';?> data-currency-text="Serbian Dinar" data-symbol="Дин.">RSD - Serbian Dinar</option>
										<option value="RUB" <?php echo ( $secondCurrencyCode == "RUB" ) ? 'selected':'';?> data-currency-text="Russian Ruble" data-symbol="₽">RUB - Russian Ruble</option>
										<option value="RWF" <?php echo ( $secondCurrencyCode == "RWF" ) ? 'selected':'';?> data-currency-text="Rwandan Franc" data-symbol="R₣">RWF - Rwandan Franc</option>
										<option value="SAR" <?php echo ( $secondCurrencyCode == "SAR" ) ? 'selected':'';?> data-currency-text="Saudi Riyal" data-symbol="﷼">SAR - Saudi Riyal</option>
										<option value="SBD" <?php echo ( $secondCurrencyCode == "SBD" ) ? 'selected':'';?> data-currency-text="Solomon Islands Dollar" data-symbol="$">SBD - Solomon Islands Dollar</option>
										<option value="SCR" <?php echo ( $secondCurrencyCode == "SCR" ) ? 'selected':'';?> data-currency-text="Seychellois Rupee" data-symbol="₨">SCR - Seychellois Rupee</option>
										<option value="SDG" <?php echo ( $secondCurrencyCode == "SDG" ) ? 'selected':'';?> data-currency-text="Sudanese Pound" data-symbol="ج.س.">SDG - Sudanese Pound</option>
										<option value="SEK" <?php echo ( $secondCurrencyCode == "SEK" ) ? 'selected':'';?> data-currency-text="Swedish Krona" data-symbol="kr">SEK - Swedish Krona</option>
										<option value="SGD" <?php echo ( $secondCurrencyCode == "SGD" ) ? 'selected':'';?> data-currency-text="Singapore Dollar" data-symbol="$">SGD - Singapore Dollar</option>
										<option value="SHP" <?php echo ( $secondCurrencyCode == "SHP" ) ? 'selected':'';?> data-currency-text="Saint Helena Pound" data-symbol="£">SHP - Saint Helena Pound</option>
										<option value="SLL" <?php echo ( $secondCurrencyCode == "SLL" ) ? 'selected':'';?> data-currency-text="Sierra Leonean Leone" data-symbol="Le">SLL - Sierra Leonean Leone</option>
										<option value="SOS" <?php echo ( $secondCurrencyCode == "SOS" ) ? 'selected':'';?> data-currency-text="Somali Shilling" data-symbol="S">SOS - Somali Shilling</option>
										<option value="SRD" <?php echo ( $secondCurrencyCode == "SRD" ) ? 'selected':'';?> data-currency-text="Surinamese Dollar" data-symbol="$">SRD - Surinamese Dollar</option>
										<option value="SSP" <?php echo ( $secondCurrencyCode == "SSP" ) ? 'selected':'';?> data-currency-text="South Sudanese Pound" data-symbol="$">SSP - South Sudanese Pound</option>
										<option value="STD" <?php echo ( $secondCurrencyCode == "STD" ) ? 'selected':'';?> data-currency-text="São Tomé and Príncipe Dobra (pre-2018)" data-symbol="Db">STD - São Tomé and Príncipe Dobra (pre-2018)</option>
										<option value="STN" <?php echo ( $secondCurrencyCode == "STN" ) ? 'selected':'';?> data-currency-text="São Tomé and Príncipe Dobra" data-symbol="Db">STN - São Tomé and Príncipe Dobra</option>
										<option value="SVC" <?php echo ( $secondCurrencyCode == "SVC" ) ? 'selected':'';?> data-currency-text="Salvadoran Colón" data-symbol="$">SVC - Salvadoran Colón</option>
										<option value="SYP" <?php echo ( $secondCurrencyCode == "SYP" ) ? 'selected':'';?> data-currency-text="Syrian Pound" data-symbol="£">SYP - Syrian Pound</option>
										<option value="SZL" <?php echo ( $secondCurrencyCode == "SZL" ) ? 'selected':'';?> data-currency-text="Swazi Lilangeni" data-symbol="E">SZL - Swazi Lilangeni</option>
										<option value="THB" <?php echo ( $secondCurrencyCode == "THB" ) ? 'selected':'';?> data-currency-text="Thai Baht" data-symbol="฿">THB - Thai Baht</option>
										<option value="TJS" <?php echo ( $secondCurrencyCode == "TJS" ) ? 'selected':'';?> data-currency-text="Tajikistani Somoni" data-symbol="SM">TJS - Tajikistani Somoni</option>
										<option value="TMT" <?php echo ( $secondCurrencyCode == "TMT" ) ? 'selected':'';?> data-currency-text="Turkmenistani Manat" data-symbol="T">TMT - Turkmenistani Manat</option>
										<option value="TND" <?php echo ( $secondCurrencyCode == "TND" ) ? 'selected':'';?> data-currency-text="Tunisian Dinar" data-symbol="د.ت">TND - Tunisian Dinar</option>
										<option value="TOP" <?php echo ( $secondCurrencyCode == "TOP" ) ? 'selected':'';?> data-currency-text="Tongan Paʻanga" data-symbol="T$">TOP - Tongan Paʻanga</option>
										<option value="TRY" <?php echo ( $secondCurrencyCode == "TRY" ) ? 'selected':'';?> data-currency-text="Turkish Lira" data-symbol="₺">TRY - Turkish Lira</option>
										<option value="TTD" <?php echo ( $secondCurrencyCode == "TTD" ) ? 'selected':'';?> data-currency-text="Trinidad and Tobago Dollar" data-symbol="TT$">TTD - Trinidad and Tobago Dollar</option>
										<option value="TWD" <?php echo ( $secondCurrencyCode == "TWD" ) ? 'selected':'';?> data-currency-text="New Taiwan Dollar" data-symbol="NT$">TWD - New Taiwan Dollar</option>
										<option value="TZS" <?php echo ( $secondCurrencyCode == "TZS" ) ? 'selected':'';?> data-currency-text="Tanzanian Shilling" data-symbol="TSh">TZS - Tanzanian Shilling</option>
										<option value="USD" <?php echo ( $secondCurrencyCode == "USD" ) ? 'selected':'';?> data-currency-text="US Dollar" data-symbol="$">USD - US Dollar</option>
										<option value="UAH" <?php echo ( $secondCurrencyCode == "UAH" ) ? 'selected':'';?> data-currency-text="Ukrainian Hryvnia" data-symbol="₴">UAH - Ukrainian Hryvnia</option>
										<option value="UGX" <?php echo ( $secondCurrencyCode == "UGX" ) ? 'selected':'';?> data-currency-text="Ugandan Shilling" data-symbol="USh">UGX - Ugandan Shilling</option>
										<option value="UYU" <?php echo ( $secondCurrencyCode == "UYU" ) ? 'selected':'';?> data-currency-text="Uruguayan Peso" data-symbol="$U">UYU - Uruguayan Peso</option>
										<option value="UZS" <?php echo ( $secondCurrencyCode == "UZS" ) ? 'selected':'';?> data-currency-text="Uzbekistan Som" data-symbol="лв">UZS - Uzbekistan Som</option>
										<option value="VES" <?php echo ( $secondCurrencyCode == "VES" ) ? 'selected':'';?> data-currency-text="Venezuelan Bolívar Soberano" data-symbol="Bs.F.">VES - Venezuelan Bolívar Soberano</option>
										<option value="VND" <?php echo ( $secondCurrencyCode == "VND" ) ? 'selected':'';?> data-currency-text="Vietnamese Dong" data-symbol="₫">VND - Vietnamese Dong</option>
										<option value="VUV" <?php echo ( $secondCurrencyCode == "VUV" ) ? 'selected':'';?> data-currency-text="Vanuatu Vatu" data-symbol="VT">VUV - Vanuatu Vatu</option>
										<option value="WST" <?php echo ( $secondCurrencyCode == "WST" ) ? 'selected':'';?> data-currency-text="Samoan Tala" data-symbol="WS$">WST - Samoan Tala</option>
										<option value="XAF" <?php echo ( $secondCurrencyCode == "XAF" ) ? 'selected':'';?> data-currency-text="CFA Franc BEAC" data-symbol="FCFA">XAF - CFA Franc BEAC</option>
										<option value="XAG" <?php echo ( $secondCurrencyCode == "XAG" ) ? 'selected':'';?> data-currency-text="Silver (troy ounce)" data-symbol="XAG">XAG - Silver (troy ounce)</option>
										<option value="XAU" <?php echo ( $secondCurrencyCode == "XAU" ) ? 'selected':'';?> data-currency-text="Gold (troy ounce)" data-symbol="XAU">XAU - Gold (troy ounce)</option>
										<option value="XCD" <?php echo ( $secondCurrencyCode == "XCD" ) ? 'selected':'';?> data-currency-text="East Caribbean Dollar" data-symbol="$">XCD - East Caribbean Dollar</option>
										<option value="XDR" <?php echo ( $secondCurrencyCode == "XDR" ) ? 'selected':'';?> data-currency-text="Special Drawing Rights" data-symbol="XDR">XDR - Special Drawing Rights</option>
										<option value="XOF" <?php echo ( $secondCurrencyCode == "XOF" ) ? 'selected':'';?> data-currency-text="CFA Franc BCEAO" data-symbol="CFA">XOF - CFA Franc BCEAO</option>
										<option value="XPD" <?php echo ( $secondCurrencyCode == "XPD" ) ? 'selected':'';?> data-currency-text="Palladium Ounce" data-symbol="XPD">XPD - Palladium Ounce</option>
										<option value="XPF" <?php echo ( $secondCurrencyCode == "XPF" ) ? 'selected':'';?> data-currency-text="CFP Franc" data-symbol="₣">XPF - CFP Franc</option>
										<option value="XPT" <?php echo ( $secondCurrencyCode == "XPT" ) ? 'selected':'';?> data-currency-text="Platinum Ounce" data-symbol="XPT">XPT - Platinum Ounce</option>
										<option value="YER" <?php echo ( $secondCurrencyCode == "YER" ) ? 'selected':'';?> data-currency-text="Yemeni Rial" data-symbol="﷼">YER - Yemeni Rial</option>
										<option value="ZAR" <?php echo ( $secondCurrencyCode == "ZAR" ) ? 'selected':'';?> data-currency-text="South African Rand" data-symbol="R">ZAR - South African Rand</option>
										<option value="ZMK" <?php echo ( $secondCurrencyCode == "ZMK" ) ? 'selected':'';?> data-currency-text="Zambian Kwacha (pre-2013)" data-symbol="ZK">ZMK - Zambian Kwacha (pre-2013)</option>
										<option value="ZMW" <?php echo ( $secondCurrencyCode == "ZMW" ) ? 'selected':'';?> data-currency-text="Zambian Kwacha" data-symbol="ZK">ZMW - Zambian Kwacha</option>
									</select>			
								</div>																		
							</div>
						</div>	
						
						<div class="col-lg-12 col-md-12 col-12 cc-loader-image" style="display:none">
							<img src="<?php echo CURRENCY_PAIRS_ROOT_DIR_PUBLIC;?>/images/loader_image.gif" alt="">
						</div>

						<!-- row detailed content desc -->
						<div class="col-lg-12 col-md-12 col-12 main-data-desc-div">
							<div class="main-desc-data-analytics">
								<p class="inserted-amount cc-first-result"></p>
								<p class="rate-of-conversion cc-second-result"></p>
								<div class="unit-rates-main">
									<p class="from-convert cc-third-result"></p>
									<p class="converted cc-fourth-result"></p>
								</div>
							</div>
						</div>
					</div>		
				</form>
			</section>
			<?php
			$output_data = ob_get_clean();
			return $output_data;
		}
	}
}