<?php
/*
Plugin Name: PayBangla: #1 bKash, Rocket & Nagad Gateway for WooCommerce
Plugin URI:  https://github.com/khulnasoft/PayBangla
Description: The most advanced mobile banking solution for WooCommerce in Bangladesh. Securely accept bKash, Rocket, and Nagad payments with automated SMS notifications, real-time transaction ID verification, and a premium 3-step checkout experience.
Version:     1.1.0
Author:      KhulnaSoft IT
Author URI:  http://khulnasoft.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: paybangla
*/
defined('ABSPATH') or die('Only a foolish person try to access directly to see this white page. :-) ');

/**
 * Plugin language
 */
load_plugin_textdomain('paybangla', false, dirname(plugin_basename(__FILE__)) . '/languages');

add_action("admin_menu", "khulnasoftit_add_sms_submenu_page");

function khulnasoftit_add_sms_submenu_page()
{
  add_submenu_page(
    'woocommerce',
    'SMS API Integration Page',
    'SMS API Integration',
    'manage_options',
    'stit-sms-integration',
    'stit_sms_integration_callback'
  );
}

function stit_sms_integration_callback()
{

  if (!current_user_can('manage_options')) {
    wp_die('Unauthorized user');
  }

  if (isset($_POST['save'])) {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'sms_nonce_field')) {
      wp_die('Security check failed');
    }

    if (isset($_POST['sms-api-url'])) {
      $url = esc_url_raw($_POST["sms-api-url"]);
      update_option('sms-api-url', $url);
    }

    if (isset($_POST['sms-api-username'])) {
      $username = sanitize_text_field($_POST["sms-api-username"]);
      update_option('sms-api-username', $username);
    }

    if (isset($_POST['sms-api-password'])) {
      $password = sanitize_text_field($_POST["sms-api-password"]);
      update_option('sms-api-password', $password);
    }

    if (isset($_POST['sms-api-message'])) {
      $message = sanitize_textarea_field($_POST["sms-api-message"]);
      update_option('sms-api-message', $message);
    }

    if (isset($_POST['sms-provider'])) {
      $provider = sanitize_text_field($_POST["sms-provider"]);
      update_option('sms-provider', $provider);
    }

    echo '<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible">
<p><strong>Settings saved.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
  }

  $sms_url      = get_option('sms-api-url') ?? '';
  $sms_username = get_option('sms-api-username') ?? '';
  $sms_password = get_option('sms-api-password') ?? '';
  $sms_provider = get_option('sms-provider') ?? 'bulksmsbd';
  $sms_message  = get_option('sms-api-message') ?? "Hello {customer_name}, thank you for your order #{order_id}. View status: {site_url}/my-account/orders/";
?>
  <div class="wrap woocommerce">
    <form method="post" id="mainform" action="" enctype="multipart/form-data">
      <?php settings_errors(); ?>
      <h1>SMS API Integration</h1>
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row" class="titledesc">
              <label for="sms-provider">SMS Provider </label>
            </th>
            <td class="forminp">
              <fieldset>
                <select name="sms-provider" id="sms-provider">
                  <option value="bulksmsbd" <?php selected($sms_provider, 'bulksmsbd'); ?>>BulkSMSBD (66.45.237.70)</option>
                  <option value="greenweb" <?php selected($sms_provider, 'greenweb'); ?>>Greenweb (API Key based)</option>
                </select>
                <p class="description">Select your SMS gateway provider.</p>
              </fieldset>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row" class="titledesc">
              <label for="sms-provider">SMS Provider </label>
            </th>
            <td class="forminp">
              <fieldset>
                <select name="sms-provider" id="sms-provider">
                  <option value="bulksmsbd" <?php selected($sms_provider, 'bulksmsbd'); ?>>BulkSMSBD (66.45.237.70)</option>
                  <option value="greenweb" <?php selected($sms_provider, 'greenweb'); ?>>Greenweb (API Key based)</option>
                </select>
                <p class="description">Select your SMS gateway provider.</p>
              </fieldset>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row" class="titledesc">
              <label for="sms-api-url">API URL </label>
            </th>
            <td class="forminp">
              <fieldset>
                <legend class="screen-reader-text"><span>API URL</span></legend>
                <input class="input-text regular-input " type="url" name="sms-api-url" id="sms-api-url" style="" value="<?php echo $sms_url; ?>" placeholder="">
                <p class="description">The API link / url you have got from your sms gateway provider. It can be like this
                  ( http://66.45.237.70/api.php )</p>
              </fieldset>
            </td>
          </tr>

          <tr valign="top">
            <th scope="row" class="titledesc">
              <label for="sms-api-username">Username </label>
            </th>
            <td class="forminp">
              <fieldset>
                <legend class="screen-reader-text"><span>Username</span></legend>
                <input class="input-text regular-input " type="text" name="sms-api-username" id="sms-api-username" style="" value="<?php echo $sms_username; ?>" placeholder="">
                <p class="description">The Username of your API</p>
              </fieldset>
            </td>
          </tr>

          <tr valign="top">
            <th scope="row" class="titledesc">
              <label for="sms-api-password">Password </label>
            </th>
            <td class="forminp">
              <fieldset>
                <legend class="screen-reader-text"><span>Password</span></legend>
                <input class="input-text regular-input " type="password" name="sms-api-password" id="sms-api-password" style="" value="<?php echo $sms_password; ?>" placeholder="">
                <p class="description">The Password of your API</p>
              </fieldset>
            </td>
          </tr>

          <tr valign="top">
            <th scope="row" class="titledesc">
              <label for="sms-api-message">Message Template </label>
            </th>
            <td class="forminp">
              <fieldset>
                <legend class="screen-reader-text"><span>Message Template</span></legend>
                <textarea class="input-text regular-input" name="sms-api-message" id="sms-api-message" rows="4"><?php echo esc_textarea($sms_message); ?></textarea>
                <p class="description">Available placeholders: <code>{customer_name}</code>, <code>{order_id}</code>, <code>{site_url}</code>, <code>{order_total}</code></p>
              </fieldset>
            </td>
          </tr>


        </tbody>
      </table>

      <p class="submit">
        <?php wp_nonce_field('sms_nonce_field'); ?>
        <button name="save" class="button-primary woocommerce-save-button" type="submit" value="Save changes">Save
          changes</button>

      </p>
    </form>
  </div>
<?php
}

add_action("woocommerce_checkout_order_processed", "khulnasoftit_send_sms_when_order_received");

function khulnasoftit_send_sms_when_order_received($order_id)
{
  $order = wc_get_order($order_id);
  if (!$order) {
    return;
  }

  // Prevent duplicate SMS
  if (get_post_meta($order_id, '_stit_sms_sent', true) === 'yes') {
    return;
  }

  $sms_url      = get_option('sms-api-url') ?? '';
  $sms_username = get_option('sms-api-username') ?? '';
  $sms_password = get_option('sms-api-password') ?? '';

  if ($sms_url && $sms_username && $sms_password) {
    $url      = $sms_url;
    $username = $sms_username;
    $password = $sms_password;
    $phone    = "88" . $order->get_billing_phone();
    
    $template = get_option('sms-api-message') ?? "Hello {customer_name}, thank you for your order #{order_id}. View status: {site_url}/my-account/orders/";
    
    $placeholders = array(
        '{customer_name}' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        '{order_id}'      => $order_id,
        '{site_url}'      => site_url(),
        '{order_total}'   => $order->get_total(),
    );
    
    $message = str_replace( array_keys($placeholders), array_values($placeholders), $template );

    if ( 'greenweb' === $sms_provider ) {
        // Greenweb typically uses an API Token and a simpler endpoint
        $body = array(
            "token"   => $sms_password, // For greenweb, we use password field as Token
            "to"      => $phone,
            "message" => $message,
        );
        $final_url = $sms_url ?: 'http://api.greenweb.com.bd/api.php';
    } else {
        // Default to BulkSMSBD
        $body = array(
            "username" => $username,
            "password" => $password,
            "number"   => $phone,
            "message"  => $message,
        );
        $final_url = $sms_url ?: 'http://66.45.237.70/api.php';
    }

    $response = wp_remote_post( $final_url, array(
        'method'    => 'POST',
        'body'      => $body,
        'timeout'   => 15,
    ) );

    if ( is_wp_error( $response ) ) {
        if ( function_exists( 'wc_get_logger' ) ) {
            $logger = wc_get_logger();
            $logger->error( 'SMS failed for order #' . $order_id . ': ' . $response->get_error_message(), array( 'source' => 'khulnasoft-sms' ) );
        }
    } else {
        // Mark as sent
        $order->update_meta_data( '_stit_sms_sent', 'yes' );
        $order->save();
    }
  }
}


add_action('wp_enqueue_scripts', 'khulnasoft_payment_method_script');
function khulnasoft_payment_method_script()
{
  wp_enqueue_script('stb-script', plugins_url('assets/js/scripts.js', __FILE__), array('jquery'), '1.0', true);
  wp_enqueue_style('stb-style', plugins_url('assets/css/style.css', __FILE__));
}

/**
 * Custom admin field for file upload in WooCommerce settings
 */
add_action( 'woocommerce_admin_field_file', 'khulnasoft_admin_field_file' );
function khulnasoft_admin_field_file( $value ) {
    $id           = $value['id'] ?? '';
    $title        = $value['title'] ?? '';
    $description  = $value['description'] ?? '';
    $custom_attrs = $value['custom_attributes'] ?? array();

    ?>
    <tr valign="top">
        <th scope="row" class="titledesc">
            <label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $title ); ?></label>
        </th>
        <td class="forminp">
            <input
                name="<?php echo esc_attr( $id ); ?>"
                id="<?php echo esc_attr( $id ); ?>"
                type="file"
                <?php foreach ( $custom_attrs as $attr => $attr_value ) { echo esc_attr( $attr ) . '="' . esc_attr( $attr_value ) . '" '; } ?>
            />
            <p class="description"><?php echo esc_html( $description ); ?></p>
        </td>
    </tr>
    <?php
}

/**
 * Add enctype to WooCommerce settings form to support file uploads
 */
add_action( 'admin_footer', 'khulnasoft_add_enctype_to_wc_settings_form' );
function khulnasoft_add_enctype_to_wc_settings_form() {
    global $current_section;
    if ( in_array( $current_section, array( 'khulnasoft_bkash', 'khulnasoft_rocket', 'khulnasoft_nagad' ) ) ) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('form#mainform').attr('enctype', 'multipart/form-data');
            });
        </script>
        <?php
    }
}

/**
 * AJAX handler for Transaction ID verification
 */
add_action( 'wp_ajax_khulnasoft_verify_transaction_id', 'khulnasoft_ajax_verify_transaction_id' );
add_action( 'wp_ajax_nopriv_khulnasoft_verify_transaction_id', 'khulnasoft_ajax_verify_transaction_id' );
function khulnasoft_ajax_verify_transaction_id() {
    $tran_id = isset( $_POST['tran_id'] ) ? sanitize_text_field( $_POST['tran_id'] ) : '';
    $gateway = isset( $_POST['gateway'] ) ? sanitize_text_field( $_POST['gateway'] ) : '';

    if ( empty( $tran_id ) || empty( $gateway ) ) {
        wp_send_json_error( array( 'message' => 'Invalid data' ) );
    }

    // Determine the meta key based on gateway
    $meta_keys = array(
        'khulnasoft_bkash'  => '_bkash_transaction',
        'khulnasoft_rocket' => '_rocket_transaction',
        'khulnasoft_nagad'  => '_nagad_transaction',
    );

    if ( ! isset( $meta_keys[$gateway] ) ) {
        wp_send_json_error( array( 'message' => 'Invalid gateway' ) );
    }

    $existing_orders = wc_get_orders( array(
        'limit'      => 1,
        'meta_key'   => $meta_keys[$gateway],
        'meta_value' => $tran_id,
    ) );

    if ( ! empty( $existing_orders ) ) {
        wp_send_json_error( array( 'message' => 'This ID has already been used.' ) );
    }

    wp_send_json_success( array( 'message' => 'Valid ID' ) );
}

/**
 * Add a dashboard widget to show recent mobile payments
 */
add_action('wp_dashboard_setup', 'khulnasoft_add_dashboard_widgets');
function khulnasoft_add_dashboard_widgets() {
    wp_add_dashboard_widget(
        'khulnasoft_payments_dashboard',
        'Recent Mobile Payments',
        'khulnasoft_dashboard_widget_display'
    );
}

function khulnasoft_dashboard_widget_display() {
    $orders = wc_get_orders(array(
        'limit'   => 5,
        'status'  => array('wc-processing', 'wc-completed', 'wc-on-hold'),
        'payment_method' => array('khulnasoft_bkash', 'khulnasoft_rocket', 'khulnasoft_nagad'),
    ));

    if (empty($orders)) {
        echo '<p>No recent mobile payments found.</p>';
        return;
    }

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Order</th><th>Gateway</th><th>Amount</th><th>Status</th></tr></thead>';
    echo '<tbody>';
    foreach ($orders as $order) {
        $gateway = str_replace('khulnasoft_', '', $order->get_payment_method());
        echo '<tr>';
        echo '<td><a href="' . $order->get_edit_order_url() . '">#' . $order->get_id() . '</a></td>';
        echo '<td>' . ucfirst($gateway) . '</td>';
        echo '<td>' . $order->get_formatted_order_total() . '</td>';
        echo '<td><mark class="order-status status-' . $order->get_status() . '"><span>' . wc_get_order_status_name($order->get_status()) . '</span></mark></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '<p><a href="' . admin_url('edit.php?post_type=shop_order') . '" class="button">View all orders</a></p>';
}

add_action( 'plugins_loaded', 'khulnasoft_paybangla_init', 11 );
function khulnasoft_paybangla_init() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }
    require_once( plugin_dir_path( __FILE__ ) . 'includes/abstract-khulnasoft-gateway.php' );
    require_once( plugin_dir_path( __FILE__ ) . 'bkash.php' );
    require_once( plugin_dir_path( __FILE__ ) . 'rocket.php' );
    require_once( plugin_dir_path( __FILE__ ) . 'nagad.php' );
}

