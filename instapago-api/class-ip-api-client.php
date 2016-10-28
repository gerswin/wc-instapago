<?php

namespace parawebs\instapago;

class Insta {
	/**
	 * @version 0.0.1
	 */
	const VERSION = "0.0.1";
	/**
	 * @var $API_ROOT_URL is a main URL to access the Instapago API's.
	 */
	protected static $API_ROOT_URL = "https://api.instapago.com/";
	protected static $SSL_VERIFY = false;

	public static $CURL_OPTS = [
		CURLOPT_HTTPHEADER => ["Content-Type" => "application/x-www-form-urlencoded"],
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_BINARYTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
	];

	protected $secret_key;
	protected $public_key;
	/**
	 * Constructor method. Set all variables to connect in Meli
	 *
	 * @param string $client_id
	 * @param string $client_secret
	 * @param string $access_token
	 * @param string $refresh_token
	 */
	public function __construct($KeyId, $PublicKeyId, $debug) {
		$this->secret_key = $KeyId;
		$this->public_key = $PublicKeyId;
		$this->debug = $debug;
	}

	/**
	 * Return an string with a complete Meli login url.
	 * NOTE: You can modify the $AUTH_URL to change the language of login
	 *
	 * @param array $data
	 * @return string
	 */
	public function makePayment($data) {
		$data['KeyId'] = $this->secret_key;
		$data['PublicKeyId'] = $this->public_key;
		$ch = curl_init();
		$curlConfig = [
			CURLOPT_URL => self::$API_ROOT_URL . 'payment',
			//CURLOPT_URL => 'http://requestb.in/srmrjjsr',
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query($data),
		];
		curl_setopt_array($ch, $curlConfig);
		curl_setopt_array($ch, self::$CURL_OPTS);

		if ($this->debug) {
			print_r($data);
		}
		$result = curl_exec($ch);
		return json_decode($result);

	}

	public function completePayment($data) {
		$data['KeyId'] = $this->secret_key;
		$data['PublicKeyId'] = $this->public_key;
		$ch = curl_init();
		$curlConfig = [
			CURLOPT_URL => self::$API_ROOT_URL . 'complete',
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query($data),
		];
		curl_setopt_array($ch, $curlConfig);
		curl_setopt_array($ch, self::$CURL_OPTS);

		if ($this->debug) {
			print_r($data);
			print_r(curl_getinfo($ch, CURLINFO_HTTP_CODE));
		}
		$result = curl_exec($ch);
		return json_decode($result);
	}

	public function cancelPayment($data) {
		$data['KeyId'] = $this->secret_key;
		$data['PublicKeyId'] = $this->public_key;
		$ch = curl_init(self::$API_ROOT_URL . "payment");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type" => "application/x-www-form-urlencoded"]);
		if ($this->debug) {
			print_r($data);
			print_r(curl_getinfo($ch, CURLINFO_HTTP_CODE));
		}
		$result = curl_exec($ch);
		return json_decode($result);
	}

	public function checkPayment($data) {
		$data['KeyId'] = $this->secret_key;
		$data['PublicKeyId'] = $this->public_key;
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => self::$API_ROOT_URL . 'payment?' . http_build_query($data),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"cache-control: no-cache",
				"content-type: application/x-www-form-urlencoded",
			),
		));
		if ($this->debug) {
			print_r($data);
			print_r(curl_getinfo($ch, CURLINFO_HTTP_CODE));
		}
		$result = curl_exec($ch);
		return json_decode($result);
	}

}
