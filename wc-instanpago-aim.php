<?php
/* Instapago AIM Payment Gateway Class */
$dir = dirname(__FILE__) . '/instapago-api/';
// base class
require_once $dir . 'class-ip-api-client.php';

use parawebs\instapago\Insta;

class InstaPago_AIM extends WC_Payment_Gateway {

	// Setup our Gateway's id, description and other values
	function __construct() {
		/// Url Paths
		$this->setup_paths_and_urls();
		// The global ID for this Payment method
		$this->id = "instapago_aim";

		// The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
		$this->method_title = __("Instapago AIM", 'instapago-aim');

		// The description for this Payment Gateway, shown on the actual Payment options page on the backend
		$this->method_description = __("Instapago AIM Payment Gateway Plug-in for WooCommerce", 'instapago-aim');

		// The title to be used for the vertical tabs that can be ordered top to bottom
		$this->title = __("Instapago AIM", 'instapago-aim');

		// If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
		$alt_image = $this->get_option('alternate_imageurl');
		$this->icon = null; // empty($alt_image) ? $this->url['assets'] . 'images/credits.png' : $alt_image;

		// Bool. Can be set to true if you want payment fields to show on the checkout
		// if doing a direct integration, which we are doing in this case
		$this->has_fields = true;

		// Supports the default credit card form
		$this->supports = array('default_credit_card_form', 'refunds');

		// This basically defines your settings which are then loaded with init_settings()
		$this->init_form_fields();
		//$this->credit_card_form();

		// After init_settings() is called, you can get the settings and load them into variables, e.g:
		// $this->title = $this->get_option( 'title' );
		$this->init_settings();

		// Turn these settings into variables we can use
		foreach ($this->settings as $setting_key => $value) {
			$this->$setting_key = $value;
		}

		// Lets check for SSL
		add_action('admin_notices', array($this, 'do_ssl_check'));

		// Save settings
		if (is_admin()) {
			// Versions over 2.0
			// Save our administration options. Since we are not going to be doing anything special
			// we have not defined 'process_admin_options' in this class so the method in the parent
			// class will be used instead
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		}
	} // End __construct()

	private function setup_paths_and_urls() {
		$this->path['plugin_file'] = __FILE__;
		$this->path['plugin_dir'] = untrailingslashit(plugin_dir_path(__FILE__));

		$this->url['plugin_dir'] = plugin_dir_url(__FILE__);
		$this->url['assets'] = $this->url['plugin_dir'] . 'assets/';
	}
	// Build the administration fields for this specific Gateway
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title' => __('Enable / Disable', 'instapago-aim'),
				'label' => __('Enable this payment gateway', 'instapago-aim'),
				'type' => 'checkbox',
				'default' => 'no',
			),
			'title' => array(
				'title' => __('Title', 'instapago-aim'),
				'type' => 'text',
				'desc_tip' => __('Payment title the customer will see during the checkout process.', 'instapago-aim'),
				'default' => __('Credit card', 'instapago-aim'),
			),
			'alternate_imageurl' => array(
				'title' => __('Alternate Image to display on checkout', 'striper'),
				'type' => 'text',
				'description' => __('Use fullly qualified url, served via https', 'striper'),
				'default' => '',
			),
			'description' => array(
				'title' => __('Description', 'instapago-aim'),
				'type' => 'textarea',
				'desc_tip' => __('Payment description the customer will see during the checkout process.', 'instapago-aim'),
				'default' => __('Pay securely using your credit card.', 'instapago-aim'),
				'css' => 'max-width:350px;',
			),
			'secret_key' => array(
				'title' => __('Llave generada desde InstaPago.', 'instapago-aim'),
				'type' => 'text',
				'desc_tip' => __('Llave generada desde InstaPago.', 'instapago-aim'),
			),
			'public_key' => array(
				'title' => __('Llave compartida ', 'instapago-aim'),
				'type' => 'password',
				'desc_tip' => __('Enviada por correo electrónico al crear la cuenta en el portal de InstaPago', 'instapago-aim'),
			),
			'debug' => array(
				'title' => __('Instapgo debug  Mode', 'instapago-aim'),
				'label' => __('Enable Test Mode', 'instapago-aim'),
				'type' => 'checkbox',
				'description' => __('Place the payment gateway in test mode.', 'instapago-aim'),
				'default' => 'no',
			),
		);
	}

	// Submit payment and handle response
	public function process_payment($order_id) {
		global $woocommerce;

		if ($this->debug == 'yes') {
			$this->log = $woocommerce->logger();
			$instapago = new Insta($this->secret_key, $this->public_key, true);
		} else {
			$instapago = new Insta($this->secret_key, $this->public_key, false);
		}

		// Get this Order's information so that we know
		// who to charge and how much
		$customer_order = new WC_Order($order_id);

		$data['Amount'] = $customer_order->order_total;
		$data['Description'] = "Compra de Productos Parawebs.dev";
		$data['CardHolder'] = preg_replace('/[^A-Z0-9 ñ]/ui', '', $_POST["instapago_aim-card-name"]);
		$data['CardHolderId'] = $_POST["instapago_aim-card-cedula"];
		$data['CardNumber'] = str_replace(array(' ', '-'), '', $_POST['instapago_aim-card-number']);
		$data['CVC'] = (isset($_POST['instapago_aim-card-cvc'])) ? $_POST['instapago_aim-card-cvc'] : '';
		$data['ExpirationDate'] = $_POST['instapago_aim-card-exp-month'] . '/' . $_POST["instapago_aim-card-exp-year"];
		$data['StatusId'] = 2; // 1 Retener (pre-autorización) o "2" Pagar (autorización).
		$data['IP'] = $_SERVER['REMOTE_ADDR'];
		$data['OrderNumber'] = str_replace("#", "", $customer_order->get_order_number());
		$data['Address'] = $customer_order->billing_address_1;
		$data['City'] = $customer_order->billing_city;
		$data['ZipCode'] = $customer_order->billing_postcode;
		$data['State'] = $customer_order->billing_state;

		$response = $instapago->makePayment($data);
		//$response->success = "true";

		// Test the code to know if the transaction went through or not.
		// 1 or 4 means the transaction was a success
		if ($response->success == "true") {
			// Payment has been successful
			$customer_order->add_order_note('Instapago-Banesco payment completed.', 'instapago-aim');
			$customer_order->add_order_note('Referencia:' . $response->reference . '\n Id: ' . $response->id, 'instapago-aim');
			// Mark order as Paid
			$customer_order->payment_complete();
			wc_add_order_item_meta($order_id, '_voucher', $response->voucher);

			// Empty the cart (Very important step)
			$woocommerce->cart->empty_cart();

			// Redirect to thank you page
			return array(
				'result' => 'success',
				'redirect' => $this->get_return_url($customer_order),
			);
		} else {
			// Transaction was not succesful
			// Add notice to the cart
			wc_add_notice($response->message, 'error');
			// Add note to the order for your reference
			$customer_order->add_order_note('Error: ' . $response->message);
		}

	}

	// Check if we are forcing SSL on checkout pages
	// Custom function not required by the Gateway
	public function do_ssl_check() {
		if ($this->enabled == "yes") {
			if (get_option('woocommerce_force_ssl_checkout') == "no") {
				echo "<div class=\"error\"><p>" . sprintf(__("<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>"), $this->method_title, admin_url('admin.php?page=wc-settings&tab=checkout')) . "</p></div>";
			}
		}
	}

	/**
	 * Process refunds.
	 * WooCommerce 2.2 or later.
	 *
	 * @param  int $order_id
	 * @param  float $amount
	 * @param  string $reason
	 * @uses   Simplify_ApiException
	 * @uses   Simplify_BadRequestException
	 * @return bool|WP_Error
	 */
	public function process_refund($order_id, $amount = null, $reason = '') {
		$payment_id = get_post_meta($order_id, '_transaction_id', true);

		$refund = array(
			'amount' => $amount * 100, // In cents.
			'payment' => $payment_id,
			'reason' => $reason,
			'reference' => $order_id,
		);

		if ('APPROVED' == 'APPROVED') {
			return true;
		} else {
			throw __('Refund was declined.', 'woocommerce');
		}

		return false;
	}

	public function payment_fields() {
		echo '<style>
			.woocommerce form .form-row-small {

			    width: 15%;
			    overflow: visible;
			}
			.woocommerce form .form-row-medium {
			    width: 40%;
			}
				.woocommerce form .form-row-large {
			    width: 50%;
			}
			.form-control{
			    display: block;
			    width: 100%;
			    height: 34px;
			    padding: 6px 12px;
			    font-size: 14px;
			    line-height: 1.42857143;
			    color: #555;
			    background-color: #fff;
			    background-image: none;
			    border: 1px solid #ccc;
			    border-radius: 0px;
			    -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
			    box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
			    -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
			    -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
			    transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
			}
			.spacer {
				padding-left: 20px;
				margin-left:20px !important;
			}
			.form-card-custom {
			    font-size: 1.5em;
			    padding: 8px !important;
			    background-repeat: no-repeat;
			    background-position: right;
			}
			</style>';
		echo '<p class="form-row form-row-medium">';
		echo '    <label for="' . esc_attr($this->id) . '-card-number">' . __('Card Number', 'woocommerce') . ' <span class="required">*</span></label>';
		echo '    <input id="' . esc_attr($this->id) . '-card-number" class="input-text wc-credit-card-form-card-number" type="text" minlength="15" maxlength="16" autocomplete="off" placeholder="•••• •••• •••• ••••" name="' . $this->id . '-card-number' . '" />';
		echo '</p>';
		echo '<p class="form-row form-row-small">';
		echo '    <label for="' . esc_attr($this->id) . '-card-cvc">' . __('Card Code', 'woocommerce') . ' <span class="required">*</span></label>';
		echo '    <input id="' . esc_attr($this->id) . '-card-cvc" class="input-text wc-credit-card-form-card-cvc" type="password" autocomplete="off" maxlength="3" placeholder="' . esc_attr__('CVC', 'woocommerce') . '" name="' . $this->id . '-card-cvc' . '" />';
		echo '</p>';
		echo '<p class="form-row  form-row-medium">';
		echo '    <label for="' . esc_attr($this->id) . '-card-exp-month">Fecha de Expiracion <span class="required">*</span></label>';
		echo '<select name="' . $this->id . '-card-exp-month" id="' . esc_attr($this->id) . '-card-exp-month" class="form-control form-row-first">
				<option value="01">01</option>
				<option value="02">02</option>
				<option value="03">03</option>
				<option value="04">04</option>
				<option value="05">05</option>
				<option value="06">06</option>
				<option value="07">07</option>
				<option value="08">08</option>
				<option value="09">09</option>
				<option value="10">10</option>
				<option value="11">11</option>
				<option value="12">12</option>
			  </select>';
		echo '<select name="' . $this->id . '-card-exp-year" id="' . esc_attr($this->id) . '-card-exp-year" class="form-control form-row-first spacer">
				<option value="2016">2016</option>
				<option value="2017>2017</option>
				<option value="2018">2018</option>
				<option value="2019">2019</option>
				<option value="2020">2020</option>
				<option value="2021">2021</option>
				<option value="2022">2022</option>
			  </select>';
		echo '</p>';
		echo '<p class="form-row form-row-large">';
		echo '    <label for="' . esc_attr($this->id) . '-card-name">Nombre en la Tarjeta<span class="required">*</span></label>';
		echo '    <input id="' . esc_attr($this->id) . '-card-name" class="input-text wc-credit-card-form-card-name form-card-custom " type="text" autocomplete="off" maxlength="30" placeholder="Nombre en la Tarjeta" name="' . $this->id . '-card-name' . '" />';
		echo '</p>';
		echo '<p class="form-row form-row-small">';
		echo '    <label for="' . esc_attr($this->id) . '-card-cedula">Numero de Cedula<span class="required">*</span></label>';
		echo '    <input id="' . esc_attr($this->id) . '-card-cedula" class="input-text wc-credit-card-form-card-cedula form-card-custom " type="text" autocomplete="off" minlength="6" maxlength="8" placeholder="Cedula" name="' . $this->id . '-card-cedula' . '" />';
		echo '</p>';
		echo '<p class="form-row form-row-wide" style="text-align:center">Esta transacción será procesada de forma segura gracias a la plataforma de:</p>';
		echo '<p style="text-align:center"><img src="' . $this->url['assets'] . 'images/credits.png' . '" alt="">		</p>';

	}
	public function validate_fields() {

		$cedula = strlen($_POST["instapago_aim-card-cedula"]);
		if (!$cedula > 6 || !$cedula > 8) {
			wc_add_notice("Numero de Cedula Incorrecto", "error");
			return false;

		}
		$cedula = strlen($_POST["instapago_aim-card-cedula"]);
		$firstnumber = (int) substr($_POST["instapago_aim-card-name"], 0, 1);
		if ($firstnumber === 3) {
			if (!preg_match("/^\d{4}$/", $cvv)) {
				//wc_add_notice("Numero de CVV Incorrecto", "error");

				return true;
			}
		} else if (!preg_match("/^\d{3}$/", $cvv)) {
			//wc_add_notice("Numero de CVV Incorrecto", "error");

			return true;
		}
		$month = $_POST['instapago_aim-card-exp-month'];
		$year = $_POST["instapago_aim-card-exp-year"];
		if (!preg_match('/^\d{1,2}$/', $month)) {
			return false; // The month isn't a one or two digit number
		} else if (!preg_match('/^\d{4}$/', $year)) {
			return false; // The year isn't four digits long
		} else if ($year < date("Y")) {
			return false; // The card is already expired
		} else if ($month < date("m") && $year == date("Y")) {
			return false; // The card is already expired
		}

		return true;

	}

} // End of instapago_aim