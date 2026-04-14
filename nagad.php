<?php

defined( 'ABSPATH' ) or die( 'Direct access not allowed.' );

/**
 * Nagad gateway register
 */
add_filter( 'woocommerce_payment_gateways', 'khulnasoft_nagad_payment_gateways' );
function khulnasoft_nagad_payment_gateways( $gateways ) {
    $gateways[] = 'KhulnaSoft_Nagad';
    return $gateways;
}

/**
 * Nagad gateway init
 */
add_action( 'plugins_loaded', 'khulnasoft_nagad_plugin_activation' );
function khulnasoft_nagad_plugin_activation() {

    class KhulnaSoft_Nagad extends KhulnaSoft_Payment_Gateway {

        protected function get_gateway_id() { return 'khulnasoft_nagad'; }
        protected function get_gateway_title() { return 'Nagad'; }
        protected function get_gateway_icon() { return 'nagad.png'; }
        protected function get_mobile_number_meta_key() { return '_nagad_number'; }
        protected function get_transaction_id_meta_key() { return '_nagad_transaction'; }
        protected function get_regex_for_number() { return '/^01[3-9]\d{8}$/'; }

    }

}

/**
 * Add settings page link in plugins
 */
add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), 'khulnasoft_nagad_settings_link' );
function khulnasoft_nagad_settings_link( $links ) {
    $settings_links = array(
        '<a href="https://khulnasoft.com" target="_blank">' . __( 'Follow US', 'paybangla' ) . '</a>',
        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=khulnasoft_nagad' ) . '">' . __( 'Settings', 'paybangla' ) . '</a>'
    );
    return array_merge( $settings_links, $links );
}

/**
 * Nagad Charge Calculation
 */
add_action( 'woocommerce_cart_calculate_fees', 'khulnasoft_nagad_charge' );
function khulnasoft_nagad_charge() {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

    $chosen_gateway = WC()->session->get( 'chosen_payment_method' );
    if ( 'khulnasoft_nagad' !== $chosen_gateway ) return;

    $settings = get_option( 'woocommerce_khulnasoft_nagad_settings' );
    if ( isset( $settings['charge'] ) && 'yes' === $settings['charge'] ) {
        $percentage = (float) ( $settings['percentage'] ?? 1.45 );
        $surcharge  = round( WC()->cart->get_cart_contents_total() * ( $percentage / 100 ) );
        WC()->cart->add_fee( __( 'Nagad Charge', 'paybangla' ), $surcharge, true, '' );
    }
}
