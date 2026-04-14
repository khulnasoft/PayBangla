<?php
defined( 'ABSPATH' ) or die();

/**
 * Abstract Payment Gateway Class for KhulnaSoft Gateways
 */
abstract class KhulnaSoft_Payment_Gateway extends WC_Payment_Gateway {

    public $charge_option;
    public $charge_percentage_option;

    public function __construct() {
        $this->id                 = $this->get_gateway_id();
        $this->title              = $this->get_option( 'title', $this->get_gateway_title() );
        $this->description        = $this->get_option( 'description', $this->get_gateway_title() . ' Payment Gateway' );
        $this->method_title       = $this->get_gateway_title();
        $this->method_description = sprintf( __( '%s Payment Gateway Options', 'paybangla' ), $this->get_gateway_title() );
        $this->icon               = plugin_dir_url( dirname( __FILE__ ) ) . 'assets/images/' . $this->get_gateway_icon();
        $this->has_fields         = true;

        $this->init_gateway_form_fields();
        $this->init_settings();

        $this->charge_option            = $this->get_option( $this->id . '_charge', 'no' );
        $this->charge_percentage_option = $this->get_option( $this->id . '_charge_percentage', '1.8' );

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_upload_file' ) );

        // Checkout validation and meta updates
        add_action( 'woocommerce_checkout_process', array( $this, 'checkout_fields_validation' ) );
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'update_order_meta' ) );

        // Admin order details
        add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'admin_order_data' ) );
        add_action( 'woocommerce_order_details_after_customer_details', array( $this, 'customer_order_details' ) );

        // Admin columns
        add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_admin_columns' ) );
        add_action( 'manage_shop_order_posts_custom_column', array( $this, 'admin_column_values' ), 2 );
    }

    abstract protected function get_gateway_id();
    abstract protected function get_gateway_title();
    abstract protected function get_gateway_icon();
    abstract protected function get_mobile_number_meta_key();
    abstract protected function get_transaction_id_meta_key();
    abstract protected function get_regex_for_number();

    public function checkout_fields_validation() {
        if ( WC()->session->get( 'chosen_payment_method' ) !== $this->id ) return;

        $number = isset( $_POST[$this->id . '_number'] ) ? sanitize_text_field( $_POST[$this->id . '_number'] ) : '';
        $tran_id = isset( $_POST[$this->id . '_transaction_id'] ) ? sanitize_text_field( $_POST[$this->id . '_transaction_id'] ) : '';

        if ( empty( $number ) ) {
            wc_add_notice( sprintf( __( 'Please add your %s number', 'paybangla' ), $this->get_gateway_title() ), 'error' );
        } elseif ( ! preg_match( $this->get_regex_for_number(), $number ) ) {
            wc_add_notice( sprintf( __( 'Incorrect %s number format.', 'paybangla' ), $this->get_gateway_title() ), 'error' );
        }

        if ( empty( $tran_id ) ) {
            wc_add_notice( sprintf( __( 'Please add your %s transaction ID', 'paybangla' ), $this->get_gateway_title() ), 'error' );
        } elseif ( ! preg_match( '/^[a-zA-Z0-9]{8,15}$/', $tran_id ) ) {
            wc_add_notice( __( 'Invalid Transaction ID format.', 'paybangla' ), 'error' );
        } else {
            // Check for duplicate transaction ID
            $existing_orders = wc_get_orders( array(
                'limit'      => 1,
                'meta_key'   => $this->get_transaction_id_meta_key(),
                'meta_value' => $tran_id,
            ) );

            if ( ! empty( $existing_orders ) ) {
                wc_add_notice( __( 'This Transaction ID has already been used. Please provide a unique one.', 'paybangla' ), 'error' );
            }
        }
    }

    public function update_order_meta( $order_id ) {
        if ( isset( $_POST['payment_method'] ) && $_POST['payment_method'] === $this->id ) {
            $number = isset( $_POST[$this->id . '_number'] ) ? sanitize_text_field( $_POST[$this->id . '_number'] ) : '';
            $tran_id = isset( $_POST[$this->id . '_transaction_id'] ) ? sanitize_text_field( $_POST[$this->id . '_transaction_id'] ) : '';

            $order = wc_get_order( $order_id );
            if ( $order ) {
                $order->update_meta_data( $this->get_mobile_number_meta_key(), $number );
                $order->update_meta_data( $this->get_transaction_id_meta_key(), $tran_id );
                $order->save();
            }
        }
    }

    public function admin_order_data( $order ) {
        if ( $order->get_payment_method() !== $this->id ) return;
        $number = $order->get_meta( $this->get_mobile_number_meta_key() );
        $tran_id = $order->get_meta( $this->get_transaction_id_meta_key() );
        ?>
        <div class="form-field form-field-wide">
            <img src='<?php echo $this->icon; ?>' alt="<?php echo $this->get_gateway_title(); ?>" style="height: 20px; vertical-align: middle;">
            <strong><?php echo $this->get_gateway_title(); ?> Details</strong>
            <table class="wp-list-table widefat fixed striped">
                <tr><th><?php _e( 'Number', 'paybangla' ); ?></th><td><?php echo esc_html( $number ); ?></td></tr>
                <tr><th><?php _e( 'Transaction ID', 'paybangla' ); ?></th><td><?php echo esc_html( $tran_id ); ?></td></tr>
            </table>
        </div>
        <?php
    }

    public function customer_order_details( $order ) {
        if ( $order->get_payment_method() !== $this->id ) return;
        $number = $order->get_meta( $this->get_mobile_number_meta_key() );
        $tran_id = $order->get_meta( $this->get_transaction_id_meta_key() );
        ?>
        <table class="woocommerce-table">
            <tr><th><?php echo $this->get_gateway_title(); ?> No:</th><td><?php echo esc_html( $number ); ?></td></tr>
            <tr><th>Transaction ID:</th><td><?php echo esc_html( $tran_id ); ?></td></tr>
        </table>
        <?php
    }

    public function add_admin_columns( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $column ) {
            $new_columns[$key] = $column;
            if ( 'order_date' === $key ) {
                $new_columns[$this->id . '_no'] = $this->get_gateway_title() . ' No';
                $new_columns[$this->id . '_tran'] = 'Tran. ID';
            }
        }
        return $new_columns;
    }

    public function admin_column_values( $column ) {
        global $post;
        $order = wc_get_order( $post->ID );
        if ( ! $order || $order->get_payment_method() !== $this->id ) return;

        if ( $column === $this->id . '_no' ) {
            echo esc_html( $order->get_meta( $this->get_mobile_number_meta_key() ) );
        }
        if ( $column === $this->id . '_tran' ) {
            echo esc_html( $order->get_meta( $this->get_transaction_id_meta_key() ) );
        }
    }

    public function init_gateway_form_fields() {
        $id = $this->id;
        $this->form_fields = array(
            'enabled'      => array(
                'title'   => __( 'Enable/Disable', 'paybangla' ),
                'type'    => 'checkbox',
                'label'   => sprintf( __( '%s Payment', 'paybangla' ), $this->get_gateway_title() ),
                'default' => 'yes',
            ),
            'title'        => array(
                'title'   => __( 'Title', 'paybangla' ),
                'type'    => 'text',
                'default' => $this->get_gateway_title(),
            ),
            'description'  => array(
                'title'   => __( 'Description', 'paybangla' ),
                'type'    => 'textarea',
                'default' => sprintf( __( 'Please complete your %s payment at first, then fill up the form below.', 'paybangla' ), $this->get_gateway_title() ),
            ),
            'order_status' => array(
                'title'       => __( 'Order Status', 'paybangla' ),
                'type'        => 'select',
                'options'     => wc_get_order_statuses(),
                'default'     => 'wc-on-hold',
            ),
            'number'       => array(
                'title'       => sprintf( __( '%s Number', 'paybangla' ), $this->get_gateway_title() ),
                'type'        => 'text',
                'description' => sprintf( __( 'Add a %s mobile no. which will be shown on checkout page', 'paybangla' ), $this->get_gateway_title() ),
            ),
            'number_type'  => array(
                'title'   => __( 'Agent/Personal', 'paybangla' ),
                'type'    => 'select',
                'options' => array(
                    'Agent'    => __( 'Agent', 'paybangla' ),
                    'Personal' => __( 'Personal', 'paybangla' ),
                ),
            ),
            'charge'       => array(
                'title'   => sprintf( __( 'Enable %s Charge', 'paybangla' ), $this->get_gateway_title() ),
                'type'    => 'checkbox',
                'label'   => __( 'Add charge to net price', 'paybangla' ),
                'default' => 'no',
            ),
            'percentage'   => array(
                'title'   => sprintf( __( '%s Charge Percentage (%%)', 'paybangla' ), $this->get_gateway_title() ),
                'type'    => 'number',
                'default' => '1.8',
                'custom_attributes' => array( 'step' => '0.01', 'min' => '0' ),
            ),
            'instructions' => array(
                'title'   => __( 'Instructions', 'paybangla' ),
                'type'    => 'textarea',
                'default' => __( 'Thanks for purchasing. We will check and update you soon.', 'paybangla' ),
            ),
            'upload_file'  => array(
                'title'       => __( 'Upload QR Code', 'paybangla' ),
                'type'        => 'file',
                'description' => __( 'Upload your QR Code image here.', 'paybangla' ),
            ),
            'file_url'     => array(
                'type' => 'hidden',
            ),
        );
    }

    public function process_upload_file() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) return;
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'woocommerce-settings' ) ) return;

        $file_key = 'woocommerce_' . $this->id . '_upload_file';
        if ( isset( $_FILES[ $file_key ] ) && ! empty( $_FILES[ $file_key ]['name'] ) ) {
            if ( ! function_exists( 'wp_handle_upload' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
            }
            $uploaded_file = $_FILES[ $file_key ];
            $file_type     = wp_check_filetype( $uploaded_file['name'] );
            if ( ! in_array( $file_type['ext'], array( 'jpg', 'jpeg', 'png', 'svg' ) ) ) {
                WC_Admin_Settings::add_error( __( 'Invalid file type.', 'paybangla' ) );
                return;
            }
            $upload = wp_handle_upload( $uploaded_file, array( 'test_form' => false ) );
            if ( isset( $upload['url'] ) && ! isset( $upload['error'] ) ) {
                $this->update_option( 'file_url', esc_url_raw( $upload['url'] ) );
            }
        }
    }

    public function payment_fields() {
        global $woocommerce;
        $charge = ( $this->get_option( 'charge' ) == 'yes' ) ? sprintf( __( ' Also note that %s%% cost will be added.', 'paybangla' ), $this->get_option( 'percentage' ) ) : '';
        echo wpautop( wptexturize( esc_html( $this->get_option( 'description' ) ) . $charge ) );

        $qr_url = $this->get_option( 'file_url' );
        if ( $qr_url ) {
            echo '<img src="' . esc_url( $qr_url ) . '" class="' . $this->id . '-qr-code-image" style="max-width:200px; display:block; margin: 10px 0;">';
        }

        echo '<div class="khulnasoft-payment-container" data-gateway="' . $this->id . '">';
        
        // Step 1: Instruction
        echo '<div class="khulnasoft-step khulnasoft-step-1">';
        echo '<span class="khulnasoft-step-badge">Step 1</span>';
        echo '<p class="khulnasoft-step-text">' . esc_html( $this->get_option( 'description' ) ) . '</p>';
        echo '</div>';

        // Step 2: Payment Details (QR + Number)
        echo '<div class="khulnasoft-step khulnasoft-step-2">';
        echo '<span class="khulnasoft-step-badge">Step 2</span>';
        
        $qr_url = $this->get_option( 'file_url' );
        if ( $qr_url ) {
            echo '<div class="khulnasoft-qr-container">';
            echo '<p class="khulnasoft-sub-label">Scan to Pay</p>';
            echo '<img src="' . esc_url( $qr_url ) . '" class="khulnasoft-qr-image ' . $this->id . '-qr-code-image">';
            echo '</div>';
        }

        echo '<div class="khulnasoft-number-container">';
        echo '<p class="khulnasoft-sub-label">' . sprintf( __( 'Send to %s account', 'stb' ), $this->get_option( 'number_type' ) ) . '</p>';
        echo '<div class="khulnasoft-copy-wrapper">';
        echo '<strong class="khulnasoft-copy-target">' . $this->get_option( 'number' ) . '</strong>';
        echo '<button type="button" class="khulnasoft-copy-btn" title="Copy Number">Copy</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // Step 3: Input Fields
        echo '<div class="khulnasoft-step khulnasoft-step-3">';
        echo '<span class="khulnasoft-step-badge">Step 3</span>';
        ?>
        <div class="khulnasoft-input-group">
            <div class="khulnasoft-field">
                <label for="<?php echo $this->id; ?>_number"><?php echo $this->get_gateway_title(); ?> Number</label>
                <div class="khulnasoft-input-wrapper">
                    <input class="input-text" type="text" name="<?php echo $this->id; ?>_number" id="<?php echo $this->id; ?>_number" placeholder="01XXXXXXXXX">
                </div>
            </div>
            <div class="khulnasoft-field">
                <label for="<?php echo $this->id; ?>_transaction_id">Transaction ID</label>
                <div class="khulnasoft-input-wrapper">
                    <input class="input-text khulnasoft-tran-id" type="text" name="<?php echo $this->id; ?>_transaction_id" id="<?php echo $this->id; ?>_transaction_id" placeholder="8N7A6D5EE7M">
                    <span class="khulnasoft-tran-feedback"></span>
                </div>
            </div>
        </div>
        <?php
        echo '</div>'; // End Step 3
        echo '</div>'; // End Container

        // BDT Conversion Notice for non-BDT stores
        if ( get_woocommerce_currency() !== 'BDT' ) {
            $rate = apply_filters( 'paybangla_bdt_exchange_rate', 120 ); // Default rate 120 BDT/USD
            $total_bdt = round( WC()->cart->get_total('') * $rate );
            echo '<div class="khulnasoft-conversion-notice">';
            echo '<small>' . sprintf( __( 'International Order: Approximately <strong>%s BDT</strong> (at 1 %s = %d BDT rate)', 'paybangla-sms' ), $total_bdt, get_woocommerce_currency(), $rate ) . '</small>';
            echo '</div>';
        }
    }

    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );
        $status = $this->get_option( 'order_status' );
        $order->update_status( $status, sprintf( __( 'Checkout with %s Payment.', 'stb' ), $this->get_gateway_title() ) );
        $order->reduce_order_stock();
        WC()->cart->empty_cart();

        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order ),
        );
    }
}
