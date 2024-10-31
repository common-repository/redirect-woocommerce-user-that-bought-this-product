<?php 

/**
 * @package redirect-woocommerce-user-that-bought-this-product
 */


class wcrebtpActivate 

{
	public static function activate() {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if( is_plugin_active( 'woocommerce/woocommerce.php' ) ){
          set_transient( 'wcrebtp_good_luck_admin_notice', true, 5 );
        }
		flush_rewrite_rules();
	}
}



/* Add admin notice */
add_action( 'admin_notices', 'wcrebtp_admin_notice' );
function wcrebtp_admin_notice(){
     /* Check transient, if available display notice */
    if( get_transient( 'wcrebtp_good_luck_admin_notice' ) ){
        ?>
        <div class="updated notice is-dismissible">
            <p><?php echo __('You can now create a redirection to users who bought the products.','redirect-woocommerce-user-that-bought-this-product'); ?></p>
        </div>
        <?php
    }
}