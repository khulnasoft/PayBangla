<?php

defined( 'ABSPATH' ) or die( 'Direct access not allowed.' );

/**
 * bKash gateway register
 */
add_filter( 'woocommerce_payment_gateways', 'khulnasoft_bkash_payment_gateways' );
function khulnasoft_bkash_payment_gateways( $gateways ) {
    $gateways[] = 'KhulnaSoft_Bkash';
    return $gateways;
}

/**
 * bKash gateway init
 */
add_action( 'plugins_loaded', 'khulnasoft_bkash_plugin_activation' );
function khulnasoft_bkash_plugin_activation() {

    class KhulnaSoft_Bkash extends KhulnaSoft_Payment_Gateway {

        protected function get_gateway_id() { return 'khulnasoft_bkash'; }
        protected function get_gateway_title() { return 'bKash'; }
        protected function get_gateway_icon() { return 'bkash.png'; }
        protected function get_mobile_number_meta_key() { return '_bkash_number'; }
        protected function get_transaction_id_meta_key() { return '_bkash_transaction'; }
        protected function get_regex_for_number() { return '/^01[3-9]\d{8}$/'; }

    }

}

/**
 * Add settings page link in plugins
 */
add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), 'khulnasoft_bkash_settings_link' );
function khulnasoft_bkash_settings_link( $links ) {
    $settings_links = array(
        '<a href="https://khulnasoft.com" target="_blank">' . __( 'Follow US', 'paybangla' ) . '</a>',
        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=khulnasoft_bkash' ) . '">' . __( 'Settings', 'paybangla' ) . '</a>'
    );
    return array_merge( $settings_links, $links );
}

/**
 * bKash Charge Calculation
 */
add_action( 'woocommerce_cart_calculate_fees', 'khulnasoft_bkash_charge' );
function khulnasoft_bkash_charge() {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

    $chosen_gateway = WC()->session->get( 'chosen_payment_method' );
    if ( 'khulnasoft_bkash' !== $chosen_gateway ) return;

    $settings = get_option( 'woocommerce_khulnasoft_bkash_settings' );
    if ( isset( $settings['charge'] ) && 'yes' === $settings['charge'] ) {
        $percentage = (float) ( $settings['percentage'] ?? 1.85 );
        $surcharge  = round( WC()->cart->get_cart_contents_total() * ( $percentage / 100 ) );
        WC()->cart->add_fee( __( 'bKash Charge', 'paybangla' ), $surcharge, true, '' );
    }
}
