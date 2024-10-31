<?php 

/**
 * @package redirect-woocommerce-user-that-bought-this-product
 */

class wcrebtpDeactivate 
{
	
	public static function deactivate() {
		flush_rewrite_rules();

	}
}