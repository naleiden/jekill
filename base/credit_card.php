<?php

define("APPROVED", 1);
define("DECLINED", 2);
define("ERROR", 3);
define("UNPROCESSED", 4);

class CreditCard {

	public $first_name, $last_name;
	public $address, $city, $state, $postal_code, $country;
	public $type, $card_number, $expiration_month, $expiration_year, $secure_pin;
	public $response;

	function __construct ($type, $card_number, $expiration_month, $expiration_year, $secure_pin,
				$first_name, $last_name, $address, $city, $state, $postal_code, $country="USA") {

		$this->type = $type;
		$this->card_number = $card_number;
		$this->expiration_month = $expiration_month;
		$this->expiration_year = $expiration_year;
		$this->secure_pin = $secure_pin;

		$this->first_name = $first_name;
		$this->last_name = $last_name;
		$this->address = $address;
		$this->city = $city;
		$this->state = $state;
		$this->postal_code = $postal_code;
		$this->country = $country;
	}

}

class Gateway {

	public $login_ID;
	public $transaction_key;
	public $processing_status;
	public $response;

	function __construct ($login_ID, $transaction_key, $processing_status=0) {
		$this->login_ID = $login_ID;
		$this->transaction_key = $transaction_key;
		$this->processing_status = $processing_status;
	}

	function submit_payment ($credit_card, $amount, $description="") {
		global $SETTINGS;

		$number = $credit_card->card_number;
		$expiration = $credit_card->expiration_month . $credit_card->expiration_year;
		$description = $description;

		if (!$this->processing_status) {
			$auth_net_url = "https://test.authorize.net/gateway/transact.dll";
			$auth_net_login_id = "76LRz5rw9";
			$auth_net_tran_key = "2Tk3WQ796YLy5LtL";
			$number = "4242424242424242";
			$expiration = "1217";
			$amount = "12.23";
			$description = "";
		}
		else {
			$auth_net_url = "https://secure.authorize.net/gateway/transact.dll";
			$auth_net_login_id = $this->login_ID;
			$auth_net_tran_key = $this->transaction_key;
		}

		$authnet_values = array (
			"x_login"		=> $auth_net_login_id,
			"x_version"		=> "3.1",
			"x_delim_char"		=> "|",
			"x_delim_data"		=> "TRUE",
			"x_url"			=> "FALSE",
			"x_type"		=> "AUTH_CAPTURE",
			"x_method"		=> "CC",
			"x_tran_key"		=> $auth_net_tran_key,
			"x_relay_response"	=> "FALSE",
			"x_card_num"		=> $number,
			"x_exp_date"		=> $expiration,
			"x_description"		=> $description,
			"x_amount"		=> $amount,
			"x_first_name"		=> $credit_card->first_name,
			"x_last_name"		=> $credit_card->last_name,
			"x_address"		=> $credit_card->address,
			"x_city"		=> $credit_card->city,
			"x_state"		=> $credit_card->state,
			"x_zip"			=> $credit_card->postal_code,
			"x_country"		=> $credit_card->country,
			"CustomerBirthMonth"	=> "Customer Birth Month: {$credit_card->birth_month}",
			"CustomerBirthDay"	=> "Customer Birth Day: {$credit_card->birth_day}",
			"CustomerBirthYear"	=> "Customer Birth Year: {$credit_card->birth_year}",
			"SpecialCode"		=> "Promotion: {$credit_card->promotion}"
		);

		$FIELDS = "";
		foreach($authnet_values as $key => $value)
			$FIELDS .= "$key=" . urlencode($value) . "&";

//echo $FIELDS; exit;

		$curl_handle = curl_init($auth_net_url);

		curl_setopt($curl_handle, CURLOPT_HEADER, 0);				// set to 0 to eliminate header info from response
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);			// Returns response data instead of TRUE(1)
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, rtrim($FIELDS, "& "));	// use HTTP POST to send form data
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE);		// uncomment this line if you get no gateway response.

		$response = curl_exec($curl_handle);					// execute post and get results
		$this->response = new TransactionResponse($response);

		curl_close($curl_handle);
		return $this->response->status;
	}

	function get_response () {
		return $this->response->response_text;
	}

	function get_error () {
		return $this->response->get_reason();
	}

	function is_approved ($status) {
		return ($status == 1);
	}

}

class TransactionResponse {

	public $response_text;
	public $response_reason;
	public $status = UNPROCESSED;

	public $reponse_data;

	function __construct ($response_text) {
		$this->response_text = $response_text;
		$this->response_data = array();
		$this->process_response();
	}

	function get_reason () {
		return $this->response_reason;
	}

	function process_response () {
		$response_parts = explode("|", $this->response_text);
		$i = 1;
		// TODO: Yikes.
		foreach ($response_parts as $value) {
			switch ($i) {
				case 1:	// Response Code
					$this->status = $value;
					break;
				case 2:	// Response Subcode
					$this->response_data[$i] = $value;
					break;
				case 3:	// Response Reason Code
					$this->response_data[$i] = $value;
					break;
				case 4:	// Response Reason Text
					$this->response_reason = $value;
					$this->response_data[$i] = $value;
					break;
				case 5:	// Approval Code
					$this->response_data[$i] = $value;
					break;
				case 6:	// AVS Result Code
					$this->response_data[$i] = $value;
					break;
				case 7:	// Transaction ID
					$this->response_data[$i] = $value;
					break;
				case 8:	// Invoice Number (x_invoice_num)
					$this->response_data[$i] = $value;
					break;
				case 9:	// Description (x_description)
					$this->response_data[$i] = $value;
					break;
				case 10:	// Amount (x_amount)
					$this->response_data[$i] = $value;
					break;
				case 11:	// Method (x_method)
					$this->response_data[$i] = $value;
					break;
				case 12:	// Transaction Type (x_type)
					$this->response_data[$i] = $value;
					break;
				case 13:	// Customer ID (x_cust_id)
					$this->response_data[$i] = $value;
					break;
				case 14:	// Cardholder First Name (x_first_name)
					$this->response_data[$i] = $value;
					break;
				case 15:	// Cardholder Last Name (x_last_name)
					$this->response_data[$i] = $value;
					break;
				case 16:	// Company (x_company)
					$this->response_data[$i] = $value;
					break;
				case 17:	// Billing Address (x_address)
					$this->response_data[$i] = $value;
					break;
				case 18:	// City (x_city)
					$this->response_data[$i] = $value;
					break;
				case 19:	// State (x_state)
					$this->response_data[$i] = $value;
					break;
				case 20:	// ZIP (x_zip)
					$this->response_data[$i] = $value;
					break;
				case 21:	// Country (x_country)
					$this->response_data[$i] = $value;
					break;
				case 22:	// Phone (x_phone): ";
					$this->response_data[$i] = $value;
					break;
				case 23:	// Fax (x_fax)
					$this->response_data[$i] = $value;
					break;
				case 24:	// E-Mail Address (x_email)
					$this->response_data[$i] = $value;
					break;
				case 25:	// Ship to First Name (x_ship_to_first_name)
					$this->response_data[$i] = $value;
					break;
				case 26:	// Ship to Last Name (x_ship_to_last_name)
					$this->response_data[$i] = $value;
					break;
				case 27:	// Ship to Company (x_ship_to_company)
					$this->response_data[$i] = $value;
					break;
				case 28:	// Ship to Address (x_ship_to_address)
					$this->response_data[$i] = $value;
					break;
				case 29:	// Ship to City (x_ship_to_city)
					$this->response_data[$i] = $value;
					break;
				case 30:	// Ship to State (x_ship_to_state)
					$this->response_data[$i] = $value;
					break;
				case 31:	// Ship to ZIP (x_ship_to_zip)
					$this->response_data[$i] = $value;
					break;
				case 32:	// Ship to Country (x_ship_to_country)
					$this->response_data[$i] = $value;
					break;
				case 33:	// Tax Amount (x_tax)
					$this->response_data[$i] = $value;
					break;
				case 34:	// Duty Amount (x_duty)
					$this->response_data[$i] = $value;
					break;
				case 35:	// Freight Amount (x_freight)
					$this->response_data[$i] = $value;
					break;
				case 36:	// Tax Exempt Flag (x_tax_exempt)
					$this->response_data[$i] = $value;
					break;
				case 37:	// PO Number (x_po_num)
					$this->response_data[$i] = $value;
					break;
				case 38:	// MD5 Hash
					$this->response_data[$i] = $value;
					break;
				case 39:	// Card Code Response
					if ($value == "M")
						$fval = "M = Match";
					else if ($value == "N")
						$fval = "N = No Match";
					else if ($value=="P")
						$fval = "P = Not Processed";
					else if ($value == "S")
						$fval = "S = Should have been present";
					else if ($value == "U")
						$fval = "U = Issuer unable to process request";
					else $fval = "NO VALUE RETURNED";
					$this->response_data[$i] = $value;
					break;
				case 40:	// Reserved
				case 41:
				case 42:
				case 43:
				case 44:
				case 45:
				case 46:
				case 47:
				case 48:
				case 49:
				case 50:
				case 51:
				case 52:
				case 53:
				case 54:
				case 55:
				case 55:
				case 56:
				case 57:
				case 58:
				case 59:
				case 60:
				case 61:
				case 62:
				case 63:
				case 64:
				case 65:
				case 66:
				case 67:
				case 68:
					$this->response_data[$i] = $value;
				break;

				default:
					if ($i >= 69) { // Merchant-defined
						$this->response_data[$i] = $value;
					}
					else {
						$this->response_data[$i] = $value;
					}
				break;
			}
			$i++;
		}
	}

}

?>