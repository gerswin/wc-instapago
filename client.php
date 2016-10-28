<?php
/**
 * Instapago API Client Class
 *
 * @version 0.0.1
 * @license GPL 3 or later http://www.gnu.org/licenses/gpl.html
 */
$dir = dirname(__FILE__) . '/instapago-api/';
// base class
require_once $dir . 'class-ip-api-client.php';

use parawebs\instapago\Insta;

$instapago = new Insta('5D5FA562-5B5B-4FB7-88A9-5598F68678CD', 'e72c3ef1417c39b0a573a8ed3611d104', false);

/*
/////MAke Payment Example/////
$data['Amount'] = 200;
$data['Description'] = "Compra de Prueba Api Rest PHP";
$data['CardHolder'] = "Gerswin Pineda";
$data['CardHolderId'] = "16745665";
$data['CardNumber'] = "4111111111111111";
$data['CVC'] = "121";
$data['ExpirationDate'] = "10/2017";
$data['StatusId'] = 1; // 1 Retener (pre-autorización) o "2" Pagar (autorización).
$data['IP'] = "201.208.51.171";
$data['OrderNumber'] = "12131";
$data['Address'] = "Calle 6 Centro";
$data['ZipCode'] = "5003";
$data['State'] = "Tachira";

print_r($instapago->makePayment($data));
////////////////// END PAYMENT///////////////
 */

/*
/////Complete Payment Example/////
$data['Id'] = '975e33d4-1daa-4705-bbb0-b5da04b28363';
$data['Amount'] = 400;

print_r($instapago->completePayment($data));
////////////////// END PAYMENT///////////////
 */

/*
/////Cancel Payment Example/////
$data['Id'] = '37141030-ab99-4990-b131-3469982e5c33';
print_r($instapago->cancelPayment($data));
////////////////// END PAYMENT///////////////
 */
/*
/////Check Payment Example/////
$data['Id'] = '37141030-ab99-4990-b131-3469982e5c33';
print_r($instapago->checkPayment($data));
////////////////// END PAYMENT///////////////
 */