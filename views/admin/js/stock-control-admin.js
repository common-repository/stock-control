jQuery( document ).ready(
	function ($) {
		$( '.save-stock' ).on(
			'click',
			function () {
				var productId         = $( this ).data( 'product-id' );
				var stockCount        = $( 'input[name="stock_count[' + productId + ']"]' ).val();
				var stockStatus       = $( 'select[name="stock_status[' + productId + ']"]' ).val();
				var backorderStatus   = $( 'select[name="backorder_status[' + productId + ']"]' ).val();
				var manageStock       = $( 'input[name="manage_stock[' + productId + ']"]' ).is( ':checked' ) ? 1 : 0;
				var lowStockThreshold = $( 'input[name="low_stock_threshold[' + productId + ']"]' ).val();

				$.ajax(
					{
						url: stock_control_ajax.ajax_url,
						type: 'POST',
						data: {
							action: 'save_stock_control',
							product_id: productId,
							stock_count: stockCount,
							stock_status: stockStatus,
							backorder_status: backorderStatus,
							manage_stock: manageStock,
							low_stock_threshold: lowStockThreshold,
							security: stock_control_ajax.nonce
						},
						success: function (response) {
							if (response.success) {
								alert( 'Stock data saved successfully' );
							} else {
								var errorMessage = 'An error occurred while saving the stock data:\n';
								if (response.data.errors) {
									errorMessage += response.data.errors.join( '\n' );
								} else {
									errorMessage += response.data.message;
								}
								alert( errorMessage );
							}
						},
						error: function () {
							alert( 'An error occurred while sending the request' );
						}
					}
				);
			}
		);

		function bulkSave() {
			// Get all checked line items
			var checkedLineItems = $( 'input[name^="bulk-edit"]:checked' );

			// Check if there are any checked line items
			if (checkedLineItems.length === 0) {
				alert( 'No line items selected for bulk save.' );
				return;
			}

			// Loop through the checked line items and save them
			checkedLineItems.each(
				function () {
					var lineItem          = $( this ).closest( 'tr' );
					var productId         = lineItem.find( '.save-stock' ).data( 'product-id' );
					var stockCount        = lineItem.find( 'input[name="stock_count[' + productId + ']"]' ).val();
					var stockStatus       = lineItem.find( 'select[name="stock_status[' + productId + ']"]' ).val();
					var backorderStatus   = lineItem.find( 'select[name="backorder_status[' + productId + ']"]' ).val();
					var manageStock       = lineItem.find( 'input[name="manage_stock[' + productId + ']"]' ).is( ':checked' ) ? 1 : 0;
					var lowStockThreshold = lineItem.find( 'input[name="low_stock_threshold[' + productId + ']"]' ).val();

					$.ajax(
						{
							url: stock_control_ajax.ajax_url,
							type: 'POST',
							data: {
								action: 'save_stock_control',
								product_id: productId,
								stock_count: stockCount,
								stock_status: stockStatus,
								backorder_status: backorderStatus,
								manage_stock: manageStock,
								low_stock_threshold: lowStockThreshold,
								security: stock_control_ajax.nonce
							},
							success: function (response) {
								if (response.success) {
									console.log( 'Stock data saved successfully for product ID ' + productId );
								} else {
									var errorMessage = 'An error occurred while saving the stock data for product ID ' + productId + ':\n';
									if (response.data.errors) {
										errorMessage += response.data.errors.join( '\n' );
									} else {
										errorMessage += response.data.message;
									}
									console.error( errorMessage );
								}
							},
							error: function () {
								console.error( 'An error occurred while sending the request for product ID ' + productId );
							}
						}
					);
				}
			);

			alert( 'Bulk save completed.' );
		}

		$( '#bulk-save' ).on( 'click', bulkSave );
	}
);
