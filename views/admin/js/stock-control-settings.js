jQuery( document ).ready(
	function ($) {
		const disableInventoryManagement = () => {
			const isVariableProduct      = $( '#product-type' ).val() === 'variable';
			const disableParentInventory = wc_stock_control.disable_parent_inventory === 'yes';

			if (isVariableProduct && disableParentInventory) {
				$( '#_manage_stock' ).prop( 'disabled', true ).prop( 'checked', false );
			} else {
				$( '#_manage_stock' ).prop( 'disabled', false );
			}
		};

		disableInventoryManagement();
		$( '#product-type' ).on( 'change', disableInventoryManagement );
	}
);
