(function( $ ) {
	'use strict';
	
	$( document ).ready(function() {		

		$( "#fromCurrency" ).select2();
		$( "#toCurrency" ).select2();

		CurrencyConvertSubmit();		
	});	

	jQuery( document ).on( "click", ".swap-currency", function(){
		SwapDropdown();
	});

	jQuery( document ).on( "click", ".open_fromCurrency, .from_select .select2", function(){		
		$("#fromCurrency").select2("open");
	});

	jQuery( document ).on( "click", ".open_toCurrency, .to_select .select2", function(){		
		$("#toCurrency").select2("open");
	});

	// Swap Dropdown Value
	function SwapDropdown()
	{
		/*** Input Type Select Swap Code Start ***/
		let secondDropdown = document.getElementById( "toCurrency" );
		let firstDropdown  = document.getElementById( "fromCurrency" );
		let temp;
		
		temp = secondDropdown.value;
		secondDropdown.value = firstDropdown.value;
		firstDropdown.value = temp;
		/*** Input Type Select Swap Code End ***/
		
		/*** Select2 Option Swap Logic Code Start ***/
		let fromCurrency = document.getElementById( "select2-fromCurrency-container" ).innerText;
		let toCurrency   = document.getElementById( "select2-toCurrency-container" ).innerText;
		
		$('#select2-fromCurrency-container').text( toCurrency ).trigger('change');
		$('#select2-toCurrency-container').text( fromCurrency ).trigger('change');
		/*** Select2 Option Swap Logic Code End ***/
		CurrencyConvertSubmit();
	}

	
	// From currency change to replace currency symbol
	$( "#fromCurrency" ).change(function() {        
		let currencySymbol = $( 'option:selected' , this ).data( 'symbol' );
		console.log( "Change ", currencySymbol );
		jQuery( '.currency-symbol-input' ).html( '' );
		jQuery( '.currency-symbol-input' ).html( currencySymbol );
		CurrencyConvertSubmit();
    });

	// Call Submit function
	$( "#toCurrency" ).change(function() {        		
		CurrencyConvertSubmit();
    });

	/*** Amount Inpt Enter Code Start ***/
	//setup before functions
	let typingTimer;                //timer identifier
	let doneTypingInterval = 500;  //time in ms
	let $input = $( '#amount' );
	
	//on keyup, start the countdown
	$input.on( 'keyup' , function () {
		clearTimeout( typingTimer );
		typingTimer = setTimeout( doneTyping, doneTypingInterval );
	});

	//on keydown, clear the countdown 
	$input.on( 'keydown' , function () {
		clearTimeout( typingTimer );
	});

	//user is "finished typing," do something
	function doneTyping () {
		CurrencyConvertSubmit();
	}
	/*** Amount Inpt Enter Code End ***/

	// Form Submit Function
	function CurrencyConvertSubmit(){		
		jQuery( '#convert_currency_form' ).submit();
	}

	// Validation Amount and Ajax Call
	jQuery( "#convert_currency_form" ).validate({
		rules: {
			amount: {
				required:true,
				number:true
			},
		},    
		messages: 
		{
			'amount':{
				required:'<span style="color:red;">Please enter a amount</span>',
				number:'<span style="color:red;">Please enter a valid amount</span>',
			}
		},errorPlacement: function( error, element ) {
			error.insertAfter( element);
		},
		submitHandler: function( form )  {
			let currencySymbol   = $('#fromCurrency').find(":selected").data('symbol');
			let fromCurrencyText = $('#fromCurrency').find(":selected").data('currency-text');
			let toCurrencyText   = $('#toCurrency').find(":selected").data('currency-text');
			let fromCurrency     = $('#fromCurrency').find(":selected").text();
			let toCurrency       = $('#toCurrency').find(":selected").text();
			let fromCurrencyVal  = $('#fromCurrency').find(":selected").val();
			let toCurrencyVal    = $('#toCurrency').find(":selected").val();
			let amount           = $('#amount').val();
			
			jQuery( '.currency-symbol-input' ).html( '' );
			jQuery( '.currency-symbol-input' ).html( currencySymbol );
			
			let params = {
				action: "currency_convert_api",
				ajax_nonce: currency_pair_frontend_ajax.ajax_nonce,		
				fromCurrency: fromCurrency,				
				toCurrency: toCurrency,				
				fromCurrencyVal: fromCurrencyVal,				
				toCurrencyVal: toCurrencyVal,				
				fromCurrencyText: fromCurrencyText,				
				toCurrencyText: toCurrencyText,				
				amount: amount,				
			};
			
			$('.main-data-desc-div').hide();
			$('.cc-loader-image').show();
			$.ajax({
				url:currency_pair_frontend_ajax.ajax_url,
				type: 'post',
				dataType: "JSON",
				data: params,
				success: function( response ) {
					if( response.status ){
						jQuery('.main-desc-data-analytics .cc-first-result').html('');
						jQuery('.main-desc-data-analytics .cc-second-result').html('');
						jQuery('.main-desc-data-analytics .cc-third-result').html('');
						jQuery('.main-desc-data-analytics .cc-fourth-result').html('');

						jQuery('.main-desc-data-analytics .cc-first-result').html( response.amountWithFromCurrencyText );
						jQuery('.main-desc-data-analytics .cc-second-result').html( response.currencyConvertResult );
						jQuery('.main-desc-data-analytics .cc-third-result').html( response.FromCurrencyEqualstoToCurrency );
						jQuery('.main-desc-data-analytics .cc-fourth-result').html( response.ToCurrencyEqualstoFromCurrency );
					}
				},
				complete: function(){
					$('.cc-loader-image').hide();
					$('.main-data-desc-div').show();
				}            
			});
		}
	});
})( jQuery );
