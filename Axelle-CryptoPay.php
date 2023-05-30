<?php
/**
 * Plugin Name: Axelle CryptoPay
 * Description: This plugin allows you to accept multiple forms of cryptocurrency as payment for products or services.
 * Version: 1.1.2
 * Author: Johnathon M. Horner
 * Author URI: https://github.com/jhorner6511
 * License: GNU v3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 */

add_action( 'plugins_loaded', 'crypto_payment_init' );

function crypto_payment_init() {

    // Get the list of supported cryptocurrencies.
    $cryptocurrencies = [
        'bitcoin',
        'ethereum',
        'litecoin',
        'ripple',
        'cardano',
        'dogecoin',
    ];

    // Add a new payment method to WooCommerce.
    add_action( 'woocommerce_payment_methods', function() use ( $cryptocurrencies ) {

        // Get the current currency.
        $currency = get_woocommerce_currency();

        // Loop through the supported cryptocurrencies.
        foreach ( $cryptocurrencies as $cryptocurrency ) {

            // Add a new payment method.
            add_filter( 'woocommerce_payment_gateways', function( $gateways ) use ( $cryptocurrency, $currency ) {

                $gateways[] = new CryptoPayment_Gateway( $cryptocurrency, $currency );

                return $gateways;
            } );
        }
    } );
}

class CryptoPayment_Gateway extends WC_Payment_Gateway {

    public function __construct( $cryptocurrency, $currency ) {

        $this->id = 'crypto-payment-' . $cryptocurrency;
        $this->method_title = __( 'Axelle CryptoPay (' . $cryptocurrency . ')', 'woocommerce' );
        $this->method_description = __( 'Accept payments in ' . $cryptocurrency . '.', 'woocommerce' );
        $this->supports = [
            'title',
            'description',
            'supports_products',
            'supports_sku',
            'supports_refunds',
        ];

        $this->cryptocurrency = $cryptocurrency;
        $this->currency = $currency;
    }

    public function process_payment( $order_id ) {

        // Get the order details.
        $order = wc_get_order( $order_id );

        // Get the customer's cryptocurrency address.
        $address = $order->get_meta( 'crypto_address', true );

        // Get the amount to pay.
        $amount = $order->get_total();

        // Create a new cryptocurrency transaction.
        $transaction = new CryptoTransaction( $this->cryptocurrency, $address, $amount );

        // Send the transaction.
        $transaction->send();

        // Add the transaction ID to the order.
        $order->update_meta( 'crypto_transaction_id', $transaction->getId() );

        // Mark the order as paid.
        $order->payment_complete();

        // Redirect the customer to the order details page.
        wp_redirect( wc_get_order_view_url( $order_id ) );
        exit;
    }
}

class CryptoTransaction {

    public function __construct( $cryptocurrency, $address, $amount ) {

        $this->cryptocurrency = $cryptocurrency;
        $this->address = $address;
        $this->amount = $amount;
    }

    public function send() {

        // TODO: Implement this method.
    }

    public function getId() {

        // TODO: Implement this method.
    }
}

add_filter( 'woocommerce_checkout_payment_fields', function( $fields ) use ( $cryptocurrencies ) {

    // Add a cryptocurrency selector to the checkout form.
    $fields['cryptocurrency'] = [
        'label' => __( 'Select Cryptocurrency', 'woocommerce' ),
        'type' => 'select',
        'options' => array_combine( $cryptocurrencies, $cryptocurrencies ),
    ];

    return $fields;
} );
