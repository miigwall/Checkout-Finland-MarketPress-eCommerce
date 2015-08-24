<?php

/**
 * Checkout.fi Integration to MarketPress
 * Author: Ajatuksella.com
 */

class MP_Gateway_CheckoutFI extends MP_Gateway_API {

	var $plugin_name = 'checkout_integration_to_marketpress';
	var $admin_name = 'checkout_integration';
	var $public_name = '';
	var $method_img_url = '';
	var $method_button_img_url = '';
	var $force_ssl;
	var $ipn_url;
	var $skip_form = false;
	var $currency;

	function on_creation() {
		global $mp;
		$settings = get_option('mp_settings');
		$this->admin_name = 'Checkout.fi integration to MarketPress';
		$this->public_name = 'Checkout.fi';
		$this->method_img_url = $mp->plugin_url . 'images/credit_card.png';
		$this->method_button_img_url = $mp->plugin_url . 'images/cc-button.png';
		$this->currency = 'EUR';
		add_action('wp_loaded', array(&$this, 'process_return_from_checkout'), 2 );
	}

	function process_payment_form() {
		return '';
	}

	function process_return_from_checkout() {
		
		global $mp;

		$paid = false;

		if (isset($_GET['REFERENCE'])) {

			$version = $_GET['VERSION'];
	    	$stamp = $_GET['STAMP'];
	    	$reference = $_GET['REFERENCE'];
	    	$payment = $_GET['PAYMENT'];
	    	$status	= $_GET['STATUS'];
	    	$algorithm = $_GET['ALGORITHM'];
	    	$mac = $_GET['MAC'];
	    	$secret = $mp->get_setting('gateways->checkout_integration_to_marketpress->demo_merchant_secret');
    	
	    	if ($algorithm == 1) {
	    		$expected_mac = strtoupper(md5("{$version}+{$stamp}+{$reference}+{$payment}+{$status}+{$algorithm}+{$secret}"));
	    	} elseif ($algorithm == 2) {
	    		$expected_mac = strtoupper(md5("{$secret}&{$version}&{$stamp}&{$reference}&{$payment}&{$status}&{$algorithm}"));
	    	} elseif ($algorithm == 3) {
	    		$expected_mac = strtoupper(hash_hmac("sha256", "{$version}&{$stamp}&{$reference}&{$payment}&{$status}&{$algorithm}", $secret));
	    	} else {
				throw new Exception('Unsuported algorithm: ' . $algorithm);
			}

// Detect if everything went ok
			$status_text = '';
			if ($expected_mac == $mac) {
	    		switch($status) {
	    			case '2':
	    			case '5':
	    			case '6':
	    			case '8':
	    			case '9':
	    			case '10':
	    				$status_text = 'The payment has been completed, and the funds have been added successfully to your account balance.';
	    				$paid = true;
	    				break;
	    			case '7':
	    			case '3':
	    			case '4':
	    				$status_text = 'The transaction has not terminated, e.g. an authorization may be awaiting completion.';
	    				$paid = true;
	    				break;
	    			case '-1':
	    				$status_text = 'A reversal has been canceled; for example, when you win a dispute and the funds for the reversal have been returned to you.';
	    				break;
	    			case '-2':
	    			case '-3':
	    			case '-4':
	    			case '-10':
	    				$status_text = 'The payment has failed.';
	    				break;
	    		}

// Update order status
	    		$mp->update_order_payment_status($reference, $status_text, $paid);

// Redirect back to successful page
				wp_redirect(mp_checkout_step_url('confirmation'));

				exit();

	    	} else {
	    		throw new Exception('MAC mismatc');
	    	}
	    }

		return '';
	}

	/**
	* Return fields you need to add to the top of the payment screen, like your credit card info fields
	*
	* @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	* @param array $shipping_info. Contains shipping info and email in case you need it
	*/
	function payment_form($cart, $shipping_info) {
		global $mp;
		return '';
	}

	/**
	* Return the chosen payment details here for final confirmation. You probably don't need
	* to post anything in the form as it should be in your $_SESSION var already.
	*
	* @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	* @param array $shipping_info. Contains shipping info and email in case you need it
	*/
	function confirm_payment_form($cart, $shipping_info) {
		global $mp;
		return '';
	}

	/**
	* Runs before page load incase you need to run any scripts before loading the success message page
	*/
	function order_confirmation($order) {
		global $mp;
		return '';
	}

	/**
	* Filters the order confirmation email message body. You may want to append something to
	* the message. Optional
	*
	* Don't forget to return!
	*/
	function order_confirmation_email($msg, $order) {
		return $msg;
	}

	/**
	* Return any html you want to show on the confirmation screen after checkout. This
	* should be a payment details box and message.
	*
	* Don't forget to return!
	*/
	function order_confirmation_msg($content, $order) {
		global $mp;
		if ($order->post_status == 'order_paid') {
			$content .= '<p>' . sprintf(__('Your payment for this order totaling %s is complete.', 'mp'), $mp->format_currency($order->mp_payment_info['currency'], $order->mp_payment_info['total'])) . '</p>';
		}
		return $content;
	}

	/**
	* Echo a settings meta box with whatever settings you need for you gateway.
	* Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
	* You can access saved settings via $settings array.
	*/
	function gateway_settings_box($settings) {
		global $mp;
		?>
		<div class="postbox">
			<h3 class='hndle' style="background: #222; box-shadow: inset 0px 15px 15px #333; text-shadow: 0px 1px 0px #000; color: #ccc;">
				<span style="color: #fff;">Checkout.fi Integration to MarketPress</span>
			</h3>
			<div class="inside">
				<table class="form-table">
					<tr>
						<th scope="row">Kauppiastunnus:</th>
						<td>
							<label><input value="<?php echo esc_attr($mp->get_setting('gateways->checkout_integration_to_marketpress->demo_merchant_id')); ?>" size="70" name="mp[gateways][checkout_integration_to_marketpress][demo_merchant_id]" type="text" /></label>					
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Turva-avain:</th>
						<td>
							<label><input value="<?php echo esc_attr($mp->get_setting('gateways->checkout_integration_to_marketpress->demo_merchant_secret')); ?>" size="70" name="mp[gateways][checkout_integration_to_marketpress][demo_merchant_secret]" type="text" /></label>					
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Keskimääräinen toimitusaika</th>
						<td>
							<label><input value="<?php echo esc_attr($mp->get_setting('gateways->checkout_integration_to_marketpress->delivery_time')); ?>" size="5" name="mp[gateways][checkout_integration_to_marketpress][delivery_time]" type="text" /> päivää</label>					
						</td>
					</tr>
				</table>
			</div>
		</div>
	<?php
	}

	/**
	* Filters posted data from your settings form. Do anything you need to the $settings['gateways']['plugin_name']
	* array. Don't forget to return!
	*/
	function process_gateway_settings($settings) {
		return $settings;
	}

	/**
     * Send given data checkout.fi using curl
	 */
	private function postData($postData) {
		if (ini_get('allow_url_fopen')) {
        	$context = stream_context_create(array(
        		'http' => array(
        			'method' => 'POST',
        			'header' => 'Content-Type: application/x-www-form-urlencoded',
        			'content' => http_build_query($postData)
        		)
        	));
        	return file_get_contents('https://payment.checkout.fi', false, $context);
        } elseif(in_array('curl', get_loaded_extensions()) ) {
            $options = array(
                CURLOPT_POST            => 1,
                CURLOPT_HEADER          => 0,
                CURLOPT_URL             => 'https://payment.checkout.fi',
                CURLOPT_FRESH_CONNECT   => 1,
                CURLOPT_RETURNTRANSFER  => 1,
                CURLOPT_FORBID_REUSE    => 1,
                CURLOPT_TIMEOUT         => 4,
                CURLOPT_POSTFIELDS      => http_build_query($postData)
            );
            $ch = curl_init();
            curl_setopt_array($ch, $options);
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        } else {
            throw new Exception("No valid method to post data. Set allow_url_fopen setting to On in php.ini file or install curl extension.");
        }
	}

	/**
	 * Use this to get correct language for Checkout.fi
	 */
	private function getLanguage() {
		if (get_bloginfo('language') == 'fi_FI') return 'FI';
		return 'EN';
	}

	/**
	* Use this to do the final payment. Create the order then process the payment. If
	* you know the payment is successful right away go ahead and change the order status
	* as well.
	* Call $mp->cart_checkout_error($msg, $context); to handle errors. If no errors
	* it will redirect to the next step.
	*
	* @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	* @param array $shipping_info. Contains shipping info and email in case you need it
	*/
	function process_payment($cart, $shipping_info) {

		global $mp;

		$settings = get_option('mp_settings');
		$totals = array();
		$coupon_code = $mp->get_coupon_code();
		foreach ($cart as $product_id => $variations) {
			foreach ($variations as $variation => $data) {
				$price = $mp->coupon_value_product($coupon_code, $data['price'] * $data['quantity'], $product_id);			
				$totals[] = $price;
			}
		}
		$total = array_sum($totals);

// Shipping line
		$shipping_tax = 0;
		if (($shipping_price = $mp->shipping_price(false)) !== false) {
			$total += $shipping_price;
			$shipping_tax = ($mp->shipping_tax_price($shipping_price) - $shipping_price);
		}

// Tax line if tax inclusive pricing is off. It it's on it would screw up the totals
		if (!$mp->get_setting('tax->tax_inclusive')) {
			$tax_price = ($mp->tax_price(false) + $shipping_tax);
			$total += $tax_price;
		}
    
// Create new Order ID
		$order_id = $mp->generate_order_id();

// Set payment information
		$payment_info = array();
		$payment_info['gateway_public_name'] = $this->admin_name;
		$payment_info['gateway_private_name'] = $this->admin_name;
		$payment_info['method'] = $this->admin_name;
		$payment_info['transaction_id'] = $order_id;
		$payment_info['status'] = array();
		$payment_info['status'][time()] = 'The payment is pending.';
		$payment_info['currency'] = $this->currency;
		$payment_info['total'] = $total;
		$payment_info['note'] = 'Purchase via Checkout.fi';

// Create new order
		$mp->create_order($order_id, $cart, $shipping_info, $payment_info, false);

		try {

			$post['VERSION']		= "0001";
			$post['STAMP']			= $order_id;
			$post['AMOUNT']			= $total;
			$post['REFERENCE']		= $order_id;
			$post['MESSAGE']		= 'Purchase via Checkout.fi';
			$post['LANGUAGE']		= $this->getLanguage();
			$post['MERCHANT']		= $mp->get_setting('gateways->checkout_integration_to_marketpress->demo_merchant_id');

			$post['RETURN']			= $return_url = mp_checkout_step_url('confirm-checkout');
			$post['CANCEL']			= $return_url;
			$post['REJECT']			= $return_url;
			$post['DELAYED']		= $return_url;

			$post['COUNTRY']		= "FIN";
			$post['CURRENCY']		= $this->currency;
			$post['DEVICE']			= "10";
			$post['CONTENT']		= "1";
			$post['TYPE']			= "0";
			$post['ALGORITHM']		= "3";
			$post['DELIVERY_DATE']	= date('Ymd', strtotime("+" . $mp->get_setting('gateways->checkout_integration_to_marketpress->delivery_time') . " days"));
			$post['FIRSTNAME']		= "".substr($shipping_info["name"], 0, 40);
			$post['FAMILYNAME']		= "".substr($shipping_info["name"], 0, 40);
			$post['ADDRESS']		= "".substr($shipping_info["address1"], 0, 40);
			$post['POSTCODE']		= "".substr($shipping_info["zip"], 0, 5);
			$post['POSTOFFICE']		= "".substr($shipping_info['city'] . " " . $shipping_info['country'], 0, 18);

			$mac = "";
			foreach($post as $value) {
				$mac .= "{$value}+";
			}
			$mac .= $mp->get_setting('gateways->checkout_integration_to_marketpress->demo_merchant_secret');
			$post['MAC'] = strtoupper(md5($mac));
	
// Post the data
			$response = $this->postData($post);

// Get the payment url from response
	   		$xml = simplexml_load_string($response);
		   
			if ($xml) {
// Redirect to checkout payment page
	   			status_header(302);
				wp_redirect($xml->paymentURL);
				exit();
			} else  {
				echo "Virhe. Maksutapahtuman luonti ei onnistunut.";
				exit();
			}

		} catch (Exception $e) {
			$mp->cart_checkout_error(sprintf(__('There was an error processing your card: "%s". Please <a href="%s">go back and try again</a>.', 'mp'), $e->getMessage(), mp_checkout_step_url('checkout')));
			return false;
		}

	}

	/**
	* INS and payment return
	*/
	function process_ipn_return() {
		global $mp;
		$settings = get_option('mp_settings');
	}

}

mp_register_gateway_plugin('MP_Gateway_CheckoutFI', 'checkout_integration_to_marketpress', 'Checkout.fi');
