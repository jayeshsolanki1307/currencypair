(function( $ ) {
	'use strict';
	$(document).ready(function () {		
		// Insert Data	
		$(document).on('click', '#insert_button', function(e) {
			e.preventDefault();			
			$('#select1').show();
			$('#select2').show();
			$('#target_url').val('');			
			$('#post_id').val('');
			jQuery('.add_title').show();
			jQuery('.update_title').hide();
			jQuery('#formType').val('insert');
		});

		jQuery("#insert_currency_pair_form").validate({
			rules: {
				currency1: {
					required:true,
				},
				currency2: {
					required:true,
				},target_url: {
					required:true,
				}			
			},    
			messages: 
			{
				'currency1':{
					required:'<span style="color:red;">This field is required</span>',
				},
				'currency2':{
					required:'<span style="color:red;">This field is required</span>',
				},'target_url':{
					required:'<span style="color:red;">This field is required</span>',
				}
			},errorPlacement: function(error, element) {
				error.insertAfter(element);
			},
			submitHandler: function(form) {
				
				let data = {
					action: "currency_pair_ajax_insert",
					currency_pair_nonce: currency_pair_ajax_object.ajax_nonce,
					formdata: $('#currencyPairModal form').serialize(),
					formType: $('#formType').val(),
					post_id: $('#post_id').val(),
				};
				$.ajax({
					url:currency_pair_ajax_object.ajax_url,
					type: 'post',
					dataType: "JSON",
					data: data,
					success: function(response) {
						if( response.status ){
							$('#insert_currency_pair_form')[0].reset();
							jQuery('.currency_pair_res').removeClass('error');
							jQuery('.currency_pair_res').addClass('success');
							jQuery('.currency_pair_res p').html('');
							jQuery('.currency_pair_res p').html( response.message );
							location.reload();
						}

						if( !response.status ){
							$('#insert_currency_pair_form')[0].reset();
							jQuery('.currency_pair_res').removeClass('success');
							jQuery('.currency_pair_res').addClass('error');
							jQuery('.currency_pair_res p').html( response.message );
						}
					}            
				});
			}
		});

		//Update Post Click Event
		$(document).on('click', '.edit_cp_post', function(e) {
			e.preventDefault();
			let post_id = $(this).attr("data-id");
			jQuery('#formType').val('update');
			jQuery('#post_id').val(post_id);
			jQuery('.add_title').hide();
			jQuery('.update_title').show();
			update_currency_data( post_id );
		});
		
		//Update Currency Data
		function update_currency_data( post_id ){
			
			let data = {
				action: "currency_pair_ajax_update",
				currency_pair_nonce: currency_pair_ajax_object.ajax_nonce,
				post_id: post_id,
			};
			$.ajax({
				url:currency_pair_ajax_object.ajax_url,
				type: 'post',
				dataType: "JSON",
				data: data,
				success: function(response) {
					if( response.status ){
						$('#select1').hide();
						$('#select2').hide();
						$('#target_url').val(response.target_url);
						$('#currencyPairModal').modal('show');
					}
				}            
			});
		}

		/* Copy to clipbord code start */		
		jQuery(document).on('click','.copy_btn',function(e){
			e.preventDefault();
			let element = $(this).attr('id');
			copy_to_clipboard( element );
		});

		function copy_to_clipboard( element ){
			$('#'+element).tooltip({
				trigger: 'click',
				placement: 'bottom'
			});

			$('#'+element).tooltip('hide').attr('data-original-title', "Copied!").tooltip('show');

			setTimeout(function() {
				$('#'+element).tooltip('hide');
			}, 1000);				
		}	
			
		// Clipboard
		let clipboard = new ClipboardJS('.copy_btn');
		
		clipboard.on('success', function(e) {
			//setTooltip('Copied!');
			//hideTooltip();		
		});
		
		clipboard.on('error', function(e) {
			//setTooltip('Failed!');
			//hideTooltip();			
		});		
		/* Copy to clipbord code end*/		
		  
	});
})( jQuery );
