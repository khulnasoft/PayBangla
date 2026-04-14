<?php

defined( 'ABSPATH' ) or die( 'Direct access not allowed.' );

/**
 * Rocket gateway register
 */
add_filter( 'woocommerce_payment_gateways', 'khulnasoft_rocket_payment_gateways' );
function khulnasoft_rocket_payment_gateways( $gateways ) {
    $gateways[] = 'KhulnaSoft_Rocket';
    return $gateways;
}

/**
 * Rocket gateway init
 */
add_action( 'plugins_loaded', 'khulnasoft_rocket_plugin_activation' );
function khulnasoft_rocket_plugin_activation() {

    class KhulnaSoft_Rocket extends KhulnaSoft_Payment_Gateway {

        protected function get_gateway_id() { return 'khulnasoft_rocket'; }
        protected function get_gateway_title() { return 'Rocket'; }
        protected function get_gateway_icon() { return 'rocket.png'; }
        protected function get_mobile_number_meta_key() { return '_rocket_number'; }
        protected function get_transaction_id_meta_key() { return '_rocket_transaction'; }
        protected function get_regex_for_number() { return '/^01[3-9]\d{9}$/'; }

    }

}

/**
 * Add settings page link in plugins
 */
add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), 'khulnasoft_rocket_settings_link' );
function khulnasoft_rocket_settings_link( $links ) {
    $settings_links = array(
        '<a href="https://khulnasoft.com" target="_blank">' . __( 'Follow US', 'paybangla' ) . '</a>',
        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=khulnasoft_rocket' ) . '">' . __( 'Settings', 'paybangla' ) . '</a>'
    );
    return array_merge( $settings_links, $links );
}

/**
 * Rocket Charge Calculation
 */
add_action( 'woocommerce_cart_calculate_fees', 'khulnasoft_rocket_charge' );
function khulnasoft_rocket_charge() {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

    $chosen_gateway = WC()->session->get( 'chosen_payment_method' );
    if ( 'khulnasoft_rocket' !== $chosen_gateway ) return;

    $settings = get_option( 'woocommerce_khulnasoft_rocket_settings' );
    if ( isset( $settings['charge'] ) && 'yes' === $settings['charge'] ) {
        $percentage = (float) ( $settings['percentage'] ?? 1.8 );
        $surcharge  = round( WC()->cart->get_cart_contents_total() * ( $percentage / 100 ) );
        WC()->cart->add_fee( __( 'Rocket Charge', 'paybangla' ), $surcharge, true, '' );
    }
}
