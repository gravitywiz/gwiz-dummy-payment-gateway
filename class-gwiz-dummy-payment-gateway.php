<?php

defined( 'ABSPATH' ) || die();

// Include the Gravity Forms Payment Add-On Framework.
GFForms::include_payment_addon_framework();

/**
 * TODO
 *   - Allow configuring things like capture/authorize to throw errors.
 *   - Allow toggling whether to use delayed flow (e.g. Stripe Checkout)
 *   - Implement UI that shows for delayed
 *   - callback()
 */
class GWiz_Dummy_Payment_Gateway extends GFPaymentAddOn {
	private static $_instance = null;

	protected $_version = GWIZ_DUMMY_PAYMENT_GATEWAY_VERSION;

	protected $_supports_callbacks = true;

	protected $_slug = 'gwiz-dummy-payment-gateway';

	/**
	 * Defines the main plugin file.
	 */
	protected $_path = 'gwiz-dummy-payment-gateway/gwiz-dummy-payment-gateway.php';

	/**
	 * Defines the full path to this class file.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the title of this add-on.
	 */
	protected $_title = 'Gravity Wiz Dummy Payment Gateway';

	/**
	 * Defines the short title of the add-on.
	 */
	protected $_short_title = 'Dummy Gateway';

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @return GWiz_Dummy_Payment_Gateway
	 */
	public static function get_instance() {

		if ( self::$_instance == null ) {
			self::$_instance = new GWiz_Dummy_Payment_Gateway();
		}

		return self::$_instance;

	}

	/**
	 * Get the icon for this add-on.
	 *
	 * @return string
	 */
	public function get_menu_icon() {
		return 'dashicons-forms';
	}

	/**
	 * Get post payment actions config.
	 *
	 * @since 2.4
	 *
	 * @param string $feed_slug The feed slug.
	 *
	 * @return array
	 */
	public function get_post_payment_actions_config( $feed_slug ) {
		return array(
			'position' => 'before',
			'setting'  => 'conditionalLogic',
		);
	}

	/**
	 * This method is executed during the form validation process and allows the form submission process to fail with a
	 * validation error if there is anything wrong with the payment/authorization. This method is only supported by
	 * single payments. For subscriptions or recurring payments, use the GFPaymentAddOn::subscribe() method.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFPaymentAddOn::validation()
	 *
	 * @param array $feed            Current configured payment feed.
	 * @param array $submission_data Contains form field data submitted by the user as well as payment information
	 *                               (i.e. payment amount, setup fee, line items, etc...).
	 * @param array $form            The Form Object.
	 * @param array $entry           The Entry Object. NOTE: the entry hasn't been saved to the database at this point,
	 *                               so this $entry object does not have the 'ID' property and is only a memory
	 *                               representation of the entry.
	 *
	 * @return array {
	 *     Return an $authorization array.
	 *
	 *     @type bool   $is_authorized  True if the payment is authorized. Otherwise, false.
	 *     @type string $error_message  The error message, if present.
	 *     @type string $transaction_id The transaction ID.
	 *     @type array  $captured_payment {
	 *         If payment is captured, an additional array is created.
	 *
	 *         @type bool   $is_success     If the payment capture is successful.
	 *         @type string $error_message  The error message, if any.
	 *         @type string $transaction_id The transaction ID of the captured payment.
	 *         @type int    $amount         The amount of the captured payment, if successful.
	 *     }
	 * }
	 */
	public function authorize( $feed, $submission_data, $form, $entry ) {
		$this->log_debug( __METHOD__ . '(): Authorizing payment.' );

		// Generate random transaction ID
		$transaction_id = wp_rand( 100000, 999999 );

		// Success
		return array(
			'is_authorized'  => true,
			'transaction_id' => 123,
		);

		// Error
		// return array( 'error_message' => $error_message, 'is_success' => false, 'is_authorized' => false );
	}

	/**
	 * Override this method to capture a single payment that has been authorized via the authorize() method.
	 *
	 * Use only with single payments. For subscriptions, use subscribe() instead.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFPaymentAddOn::entry_post_save()
	 *
	 * @param array $authorization   Contains the result of the authorize() function.
	 * @param array $feed            Current configured payment feed.
	 * @param array $submission_data Contains form field data submitted by the user as well as payment information.
	 *                               (i.e. payment amount, setup fee, line items, etc...).
	 * @param array $form            Current form array containing all form settings.
	 * @param array $entry           Current entry array containing entry information (i.e data submitted by users).
	 *
	 * @return array {
	 *     Return an array with the information about the captured payment in the following format:
	 *
	 *     @type bool   $is_success     If the payment capture is successful.
	 *     @type string $error_message  The error message, if any.
	 *     @type string $transaction_id The transaction ID of the captured payment.
	 *     @type int    $amount         The amount of the captured payment, if successful.
	 *     @type string $payment_method The card issuer.
	 * }
	 */
	public function capture( $authorization, $feed, $submission_data, $form, $entry ) {
		// If delayed...
		return array();

		// Error
//		return array(
//			'is_success'    => false,
//			'error_message' => esc_html__( 'Cannot get payment intent data.', 'gravityformsstripe' ),
//		);

		// Success
//		return array(
//			'is_success'     => true,
//			'transaction_id' => $charge->id,
//			'amount'         => $this->get_amount_import( $charge->amount, $entry['currency'] ),
//			'payment_method' => rgpost( 'stripe_credit_card_type' ),
//		);

	}

	/**
	 * Override this method to add integration code to the payment processor in order to create a subscription.
	 *
	 * This method is executed during the form validation process and allows the form submission process to fail with a
	 * validation error if there is anything wrong when creating the subscription.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFPaymentAddOn::validation()
	 *
	 * @param array $feed            Current configured payment feed.
	 * @param array $submission_data Contains form field data submitted by the user as well as payment information
	 *                               (i.e. payment amount, setup fee, line items, etc...).
	 * @param array $form            Current form array containing all form settings.
	 * @param array $entry           Current entry array containing entry information (i.e data submitted by users).
	 *                               NOTE: the entry hasn't been saved to the database at this point, so this $entry
	 *                               object does not have the 'ID' property and is only a memory representation of the entry.
	 *
	 * @return array {
	 *     Return an $subscription array in the following format:
	 *
	 *     @type bool   $is_success      If the subscription is successful.
	 *     @type string $error_message   The error message, if applicable.
	 *     @type string $subscription_id The subscription ID.
	 *     @type int    $amount          The subscription amount.
	 *     @type array  $captured_payment {
	 *         If payment is captured, an additional array is created.
	 *
	 *         @type bool   $is_success     If the payment capture is successful.
	 *         @type string $error_message  The error message, if any.
	 *         @type string $transaction_id The transaction ID of the captured payment.
	 *         @type int    $amount         The amount of the captured payment, if successful.
	 *     }
	 *
	 * To implement an initial/setup fee for gateways that don't support setup fees as part of subscriptions, manually
	 * capture the funds for the setup fee as a separate transaction and send that payment information in the
	 * following 'captured_payment' array:
	 *
	 * 'captured_payment' => [
	 *     'name'           => 'Setup Fee',
	 *     'is_success'     => true|false,
	 *     'error_message'  => 'error message',
	 *     'transaction_id' => 'xxx',
	 *     'amount'         => 20
	 * ]
	 */
	public function subscribe( $feed, $submission_data, $form, $entry ) {
		// Error
		// return array( 'error_message' => $error_message, 'is_success' => false, 'is_authorized' => false );

		// Success
		return array(
			'is_success'      => true,
//			'subscription_id' => $stripe_response->subscription,
//			'customer_id'     => $subscription->customer,
//			'amount'          => $payment_amount,
		);
	}

	/**
	 * @return array|bool|WP_Error Return a valid GF $action or if the webhook can't be processed a WP_Error object or false.
	 */
	public function callback() {
	}
}