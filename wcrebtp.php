<?php 

/**
 * @package wcrebtp
 */

/*
Plugin Name: 	Redirect Woocommerce User that Bought this Product
Plugin URI:		https://kef-kef.com/2019/07/16/redirect-woocommerce-user-that-bought-this-product/
Description:	Adds a URL field to the product page, where the logged in user will be redirected to from product page if user already bought this product.
Version:		1.0.1
Author: 		Ahiad Hazan
Author URI:		https://www.facebook.com/ahiad
License:		GPLv2 or later
Text Domain:	wcrebtp
*/


/*
  "Woocommerce Redirect Bought This Product" is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 2 of the License, or
  any later version.
   
  "Woocommerce Redirect Bought This Product" is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.
  
  You should have received a copy of the GNU General Public License
  along with "Woocommerce Redirect Bought This Product". If not, see {License URI}.
*/

defined( 'ABSPATH' ) or die( 'No No No, You Naughty People!' );

/**
 * 
 */


class wcrebtp {

  public $plugName;


  function __construct() {
    $this->plugName = plugin_basename( __FILE__ );
    set_transient( 'wcrebtp_check_if_installed', true, 5 );
    $this->check_if_woocommerce_is_installed();
  }

  // three function methods:
  // Public - can be accessed everywhere (default)
  // Protected - can be accessed only from the class itself (not "$class_name->function()" ) or by ententions
  // Private - can be accessed only from the class itself (not even via extentions).

  function check_if_woocommerce_is_installed(){
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ){
      
      add_action( 'admin_notices', 'woocommerce_not_activated' );
      function woocommerce_not_activated(){
     /* Check transient, if available display notice */
    if( get_transient( 'wcrebtp_check_if_installed' ) ){
        ?>
        <div class="notice notice-error">
            <p><?php echo __('Woocommerce must be installed for the plugin "Redirect Woocommerce User that Bought this Product" to be activated','wcrebtp'); ?></p>
        </div>
        <?php
        }
      }
      deactivate_plugins( $this->plugName );
      unset($_GET['activate']);
      delete_transient( 'wcrebtp_good_luck_admin_notice' );
    }
  }

  function register() {
    add_action('admin_enqueue_scripts', array( $this, 'enqueue' ) );
    add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
    add_filter( "plugin_action_links_$this->plugName", array( $this , 'settings_link' ) );
    add_action( 'woocommerce_before_single_product', array( $this, 'user_logged_and_purchased_product' ) , 30 );
    add_action( 'woocommerce_process_product_meta', array( $this, 'wcrebtp_save_custom_field' ) );
    add_action( 'woocommerce_product_options_general_product_data', array( $this, 'wcrebtp_create_custom_field' ) );
  }

  function enqueue() {
    // here you can import all your scripts (js and CSS)
    wp_enqueue_style( 'wcrebtppluginstyle', plugins_url( '/assets/mystyle.css', __FILE__ ) );
  }

  public function add_admin_pages() {
    add_menu_page( 'wcreBtp Plugin', __('wcreBtp','wcrebtp') , 'manage_options', 'wcrebtp_plugin' , array( $this, 'admin_index' ), 'dashicons-controls-skipback' , 80 );
  }

  public function admin_index() {
    require_once plugin_dir_path( __FILE__ ) . 'templates/index.php';
  }
  public function settings_link ( $links ) {
    $settings_link = '<a href="admin.php?page=wcrebtp_plugin">Instructions</a>';
    array_push($links , $settings_link);
    return $links;
  }

  /*check if user have bought this product */


  function user_logged_and_purchased_product() {
  global $product;
    if( !empty($product->get_meta( 'wcrebtp_redirect_bought_product' ))){
      if ( ! is_user_logged_in() || current_user_can('administrator') ) return;
      $current_user = wp_get_current_user();
      $product_redirect_link  = $product->get_meta( 'wcrebtp_redirect_bought_product' );
      if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product->get_id() ) ) {
        ?>
          <script>
            window.location.href = "<?php echo $product_redirect_link; ?>";
          </script>
        <?php
      }
    }
  }
  /*add a field in product page*/
  function wcrebtp_create_custom_field() {
   $varies = array(
   'id' => 'wcrebtp_redirect_bought_product',
   'label' => __( 'Redirection Address', 'wcrebtp' ),
   'class' => 'wcrebtp-custom-field',
   'desc_tip' => true,
   'description' => __( 'Insert the address a user who bought this product will be redirected to.', 'wcrebtp' ),
   'placeholder' => __('example:','wcrebtp') . ' https://kef-kef.com',
   );
   woocommerce_wp_text_input( $varies );
  }


  /*save this field to the product meta*/
  function wcrebtp_save_custom_field( $post_id ) {
   $product = wc_get_product( $post_id );
   $title = isset( $_POST['wcrebtp_redirect_bought_product'] ) ? esc_url_raw($_POST['wcrebtp_redirect_bought_product']) : '';
   wp_http_validate_url( $title );
   $product->update_meta_data( 'wcrebtp_redirect_bought_product', esc_url_raw( $title ) );
   $product->save();
  }


/*plugin development if bought end*/

}

if ( class_exists( 'wcrebtp' ) ) {

  $wcrebtp = new wcrebtp();
  $wcrebtp->register();
}

// activation
require_once plugin_dir_path( __FILE__ ) . 'inc/wcrebtp-plugin-activate.php';
register_activation_hook( __FILE__ , array( 'wcrebtpActivate' , 'activate' ) );

// deactivation
require_once plugin_dir_path( __FILE__ ) . 'inc/wcrebtp-plugin-deactivate.php';
register_deactivation_hook( __FILE__ , array( 'wcrebtpDeactivate' , 'deactivate' ) );

// uninstall is in a different file