<?php

defined( 'ABSPATH' ) || die();

// Include the Gravity Forms Payment Add-On Framework.
GFForms::include_payment_addon_framework();

/**
 * TODO
 *   - Callbacks (callback()) and redirect for frontend?
 *   - Subscriptions support?
 */
class GWiz_Dummy_Payment_Gateway extends GFPaymentAddOn {
	private static $_instance = null;

	protected $_version = GWIZ_DUMMY_PAYMENT_GATEWAY_VERSION;

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
	 * Add additional actions for admin.
	 */
	public function init_admin() {
		parent::init_admin();

		add_action( 'gform_payment_details', array( $this, 'maybe_add_payment_details_button' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'maybe_handle_payment_details_button' ), 10, 2 );
	}

	/**
	 * Get the icon for this add-on.
	 *
	 * @return string
	 */
	public function get_menu_icon() {
		return 'dashicons-code-standards';
	}

	/**
	 * Get post payment actions config.
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
	 * Adds buttons to payment details box if payment status allows.
	 *
	 * @param int   $form_id The ID of the form the entry belongs to.
	 * @param array $entry   The current entry object.
	 *
	 * @return void
	 */
	public function maybe_add_payment_details_button( $form_id, $entry ) {
		if ( ! $this->is_payment_gateway( $entry['id'] ) ) {
			return;
		}

		switch ( $entry['payment_status'] ) {
			case 'Authorized':
				$button['label']      = __( 'Capture Payment', 'gwiz-dummy-payment-gateway' );
				$button['api_action'] = 'capture';
				break;
			case 'Paid':
				$button['label']      = __( 'Refund Payment', 'gwiz-dummy-payment-gateway' );
				$button['api_action'] = 'refund';
				break;
            default:
                _e( 'No testing actions available for this payment status.', 'gwiz-dummy-payment-gateway' );
                return;
		}

		$spinner_url = GFCommon::get_base_url() . '/images/spinner.' . ( $this->is_gravityforms_supported( '2.5-beta' ) ? 'svg' : 'gif' );
		?>
        <input name="gwiz_dummy_gateway_nonce" type="hidden" value="<?php echo wp_create_nonce( 'gwiz_dummy_gateway_nonce' ); ?>"/>

        <button id="gwiz_dummy_gateway_<?php echo esc_attr( $button['api_action'] ); ?>"
                class="button"
                name="gwiz_dummy_gateway_action"
                value="<?php echo esc_attr( $button['api_action'] ); ?>">
			<?php echo esc_html( $button['label'] ); ?>
        </button>
		<?php
	}

	/**
	 * Handles the click of the payment details button.
	 *
	 * @return void
	 */
	public function maybe_handle_payment_details_button() {
		if ( ! rgpost( 'gwiz_dummy_gateway_action' ) || ! rgpost( 'gwiz_dummy_gateway_nonce' ) ) {
			return;
		}

		if ( ! wp_verify_nonce( rgpost( 'gwiz_dummy_gateway_nonce' ), 'gwiz_dummy_gateway_nonce' ) ) {
			return;
		}

		$entry_id = rgget( 'lid' );
		$action   = rgpost( 'gwiz_dummy_gateway_action' );

		$entry = GFAPI::get_entry( sanitize_text_field( $_POST['entry_id'] ) );

		if ( ! $entry ) {
			return;
		}

		switch ( $action ) {
			case 'capture':
				$this->complete_payment( $entry, array(
					'amount' => $entry['payment_amount'],
					'transaction_id' => 'paid-' . wp_rand( 100000, 999999 ),
				) );
				break;
			case 'refund':
				$this->refund_payment( $entry, array(
					'amount' => $entry['payment_amount'],
					'transaction_id' => 'refund-' . wp_rand( 100000, 999999 ),
				) );
				break;
		}
	}

	/**
     * Override feed settings fields to add testing settings.
     *
	 * @return array[]
	 */
    public function feed_settings_fields() {
        $fields = parent::feed_settings_fields();

	    // Remove subscription settings
	    $fields = $this->remove_subscription_settings( $fields );

        // Testing section
        $fields[] = array(
            'title'  => esc_html__( 'Testing', 'gwiz-dummy-payment-gateway' ),
            'fields' => array(
	            array(
		            'name'    => 'capture_mode',
		            'label'   => esc_html__( 'Capture Mode', 'gwiz-dummy-payment-gateway' ),
		            'type'    => 'radio',
                    'default_value' => 'immediate',
		            'choices' => array(
			            array(
				            'label' => esc_html__( 'Immediate Capture', 'gwiz-dummy-payment-gateway' ),
				            'value' => 'immediate',
			            ),
			            array(
				            'label' => esc_html__( 'Delayed Capture', 'gwiz-dummy-payment-gateway' ),
				            'value' => 'delayed',
			            ),
		            ),
	            ),
	            array(
		            'name'    => 'fail_conditions',
		            'label'   => esc_html__( 'Fail Conditions', 'gwiz-dummy-payment-gateway' ),
		            'type'    => 'checkbox',
		            'choices' => array(
			            array(
				            'label' => esc_html__( 'Fail During Authorization', 'gwiz-dummy-payment-gateway' ),
				            'name'  => 'fail_authorization',
			            ),
			            array(
				            'label' => esc_html__( 'Fail During Capture', 'gwiz-dummy-payment-gateway' ),
				            'name'  => 'fail_capture',
			            ),
		            ),
	            ),
            ),
        );

        return $fields;
    }
	/**
	 * Recursively remove subscription settings from fields.
	 *
	 * @param array $fields The fields array.
	 * @return array The modified fields array.
	 */
	private function remove_subscription_settings( $fields ) {
		foreach ($fields as $key => $field) {
			if (isset($field['name']) && $field['name'] === 'transactionType') {
				// Remove 'subscription' from transaction type choices
				$fields[$key]['choices'] = array_filter($field['choices'], function ($choice) {
					return $choice['value'] !== 'subscription';
				});
			}
			if (isset($field['title']) && $field['title'] === esc_html__('Subscription Settings', 'gwiz-dummy-payment-gateway')) {
				unset($fields[$key]);
                continue;
			}
			if (isset($field['fields'])) {
				$fields[$key]['fields'] = $this->remove_subscription_settings($field['fields']);
			}
		}
		return $fields;
	}

	/**
     * Override other settings fields to remove unneeded field map and "Options" field.
     *
	 * @return array
	 */
	public function other_settings_fields() {
	    $other_settings = array();

		$other_settings[] = array(
			'name'    => 'conditionalLogic',
			'label'   => esc_html__( 'Conditional Logic', 'gravityforms' ),
			'type'    => 'feed_condition',
			'tooltip' => '<h6>' . esc_html__( 'Conditional Logic', 'gravityforms' ) . '</h6>' . esc_html__( 'When conditions are enabled, form submissions will only be sent to the payment gateway when the conditions are met. When disabled, all form submissions will be sent to the payment gateway.', 'gravityforms' )
		);

		return $other_settings;
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

        if ( rgars( $feed, 'meta/fail_authorization' ) ) {
            return array(
                'is_authorized' => false,
                'is_success'    => false,
                'error_message' => __( 'Testing authorization failure.', 'gwiz-dummy-payment-gateway' ),
            );
        }

		// Success
		return array(
			'is_authorized'  => true,
            'amount'         => $submission_data['payment_amount'],
			'transaction_id' => 'auth-' . wp_rand( 100000, 999999 ),
		);
	}

	public function complete_authorization( &$entry, $action ) {
        // Add payment_amount to the entry so we have the amount when we capture.
        $entry['payment_amount'] = rgar( $action, 'amount' );

		return parent::complete_authorization( $entry, $action );
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
        if ( rgars( $feed, 'meta/fail_capture' ) ) {
            return array(
                'is_success'    => false,
                'error_message' => __( 'Testing capture failure.', 'gwiz-dummy-payment-gateway' ),
            );
        }

        if ( rgars( $feed, 'meta/capture_mode' ) === 'delayed' ) {
            return array();
        }

		return array(
			'is_success'     => true,
			'transaction_id' => 'paid-' . wp_rand( 100000, 999999 ),
			'amount'         => $submission_data['payment_amount'],
			'payment_method' => 'Dummy Gateway',
		);
	}

	/**
	 * Complete payment (mark entry as complete and create note).
	 *
	 * @param array $entry  Entry data.
	 * @param array $action Authorization data.
	 *
	 * @return bool
	 */
	public function complete_payment( &$entry, $action ) {
		parent::complete_payment( $entry, $action );

		$transaction_id = rgar( 'transaction_id', $action );
		$form           = GFAPI::get_form( $entry['form_id'] );
		$feed           = $this->get_payment_feed( $entry, $form );

		$this->trigger_payment_delayed_feeds( $transaction_id, $feed, $entry, $form );

		return true;
	}
}
