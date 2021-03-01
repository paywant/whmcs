<?php
// *************************************************************************
// *                                                                       *
// * Paywant Payment Gateway 											   *
// * Copyright 2016 ZEA Bilisim Hizmetleri Ltd. All rights reserved.	   *
// * Version: 1.1.0                                                        *
// * Build Date: 26 June 2016                                              *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * Email: integration@paywant.com                                        *
// * Website: https://www.paywant.com	                                   *
// *                                                                       *
// *************************************************************************

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define Paywant gateway configuration options.
 *
 * @return array
 */
function paywantmobil_MetaData()
{
    return array(
        'DisplayName' => 'Paywant Mobil Ödeme',
        'APIVersion' => '1.1'
    );
}

function paywantmobil_config()
{
    return array(
		"FriendlyName" => array(
        "Type" => "System",
        "Value" => "Paywant Mobil Ödeme"
        ),
        'durum' => array(
            'FriendlyName' => 'Durum',
            'Type' => 'yesno',
            'Description' => 'Aktif etmek için işaretleyin'
        ),
        'apiKey' => array(
            'FriendlyName' => 'API Key',
            'Type' => 'text',
            'Size' => '255',
            'Default' => ''
        ),
        'apiSecretKey' => array(
            'FriendlyName' => 'API Secret Key',
            'Type' => 'text',
            'Size' => '255',
            'Default' => ''
        ),
        'commissionType' => array(
            'FriendlyName' => 'Komisyon Tipi',
            'Type' => 'dropdown',
            'Options' => array(
                '1' => 'Komisyonu Üstlen',
                '2' => 'Komisyonu Müşteriye Yansıt'
            ),
            'Description' => 'Lütfen bir komisyonlandırma tipi seçiniz.'
        ),
        'info' => array(
            'FriendlyName' => '<strong>Önemli!</strong>',
			'Description' => ''
        ),
       'odemeKanallari' => array(
            'FriendlyName' => 'Ödeme Kanalları',
            'Type' => 'dropdown',
            'Options' => array(
                '1' => 'Hepsi',
                '2' => 'Seçili Olanlar'
            ),
            'Description' => 'Lütfen kabul edeceğiniz ödeme türünü seçiniz.'
        ),
        'mobilePayment' => array(
            'FriendlyName' => 'Mobil Ödeme',
            'Type' => 'yesno',
            'Default' => '1',
			'Description' => 'Mobil Ödeme ile ödeme kabul etmek için tıklayınız.'
        ),
        'ccPayment' => array(
            'FriendlyName' => 'Kredi Kartı',
            'Type' => 'yesno',
            'Default' => '1',
			'Description' => 'Kredi Kartı ile ödeme kabul etmek için tıklayınız.'
        ),
        'bankPayment' => array(
            'FriendlyName' => 'Havale/Eft/Atm',
            'Type' => 'yesno',
            'Default' => '1',
			'Description' => '4\'ü masrafsız 11 Bankadan havale/eft/atm ile ödeme kabul etmek için tıklayınız. <span style="color:red"><strong> (7/24 Kontrol)</span></p>'
        ),
        'mikroPayment' => array(
            'FriendlyName' => 'Mikrocard',
            'Type' => 'yesno',
            'Default' => '1',
			'Description' => 'Ön ödemeli kart Mikrocard ile ödeme kabul etmek için tıklayınız.'
        ),
        'info' => array(
            'FriendlyName' => '<strong>Önemli!</strong>',
			'Description' => ''
        )
    );
}

/**
 * Payment link.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @return string
 */
 
function paywantIPAddress1()
	{
		if(getenv("HTTP_CLIENT_IP")) 
		{
			$ip = getenv("HTTP_CLIENT_IP");
		} 
		else if(getenv("HTTP_X_FORWARDED_FOR")) 
		{
			$ip = getenv("HTTP_X_FORWARDED_FOR");
			if (strstr($ip, ',')) 
			{
				$tmp = explode (',', $ip);
				$ip = trim($tmp[0]);
			}
		} 
		else 
		{
			$ip = getenv("REMOTE_ADDR");
		}
		return $ip;
	}
		
function paywantmobil_link($params)
{
    // Invoice
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currency = $params['currency'];

    // Client
    $email = $params['clientdetails']['email'];
    $phone = $params['clientdetails']['phonenumber'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $postalCode = $params['clientdetails']['postcode'];
    $city = $params['clientdetails']['city'];
    $country = $params['clientdetails']['country'];

    // System
    $companyName = $params['companyname'];

	$printIt = "-";
    // Config Options
    if ($params['durum'] == 'on') {
		if($currency == "TRY")
		{
			$apiKey = $params['apiKey'];
			$secretKey = $params['apiSecretKey'];
			$userIPAdresi = paywantIPAddress1();
			$paymentChannels = array();
			if($params['odemeKanallari'] == '2')
			{
					if($params['mobilePayment'] == 'on')
						$paymentChannels[] = 1;
					if($params['ccPayment'] == 'on')
						$paymentChannels[] = 2;
					if($params['bankPayment'] == 'on')
						$paymentChannels[] = 3;
					if($params['mikroPayment'] == 'on')
						$paymentChannels[] = 5;
				
			}else
				$paymentChannels[] = 0;
		 
			
			
			
			$productData = array(
				"name" =>  $description,
				"amount" => $amount*100,
				"extraData" => $invoiceId,
				"paymentChannel" => 1,
				"commissionType" => $params['commissionType']
			);
			
			$Hash = base64_encode(hash_hmac('sha256',"$email|$email|-1".$apiKey,$secretKey,true));

			$postData = array(
			'apiKey' => $apiKey,
			'hash' => $Hash,
			'returnData'=> $email,
			'userEmail' => $email,
			'userIPAddress' => $userIPAdresi,
			'userID' => -1,
			'proApi' => true,
			'productData' => $productData
			);

			$postData = http_build_query($postData);
			
			$curl = curl_init();
			
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://secure.paywant.com/gateway",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => $postData,
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);
			// echo $response;

			if ($err) {
			  $printIt = "cURL Error #:" . $err;
			} else {
			  $jsonDecode = json_decode($response,false);
			  if($jsonDecode->status == "true")
			  {
			  $printIt = "<a href=".$jsonDecode->message."><img src=\"https://www.paywant.com/bimage/paywant_ile_ode_whmcs.png\" alt=\"Paywant ile öde\"/></a>";
			  }else{
				$printIt = $response;
			  }

			}
		}else
			$printIt =  "<strong><font color='red'>Paywant ile sadece Türk Lirası türünden ödeme yapılabilir.</font></strong>";
    } else
        $printIt =  "<strong><font color='red'>Bu ödeme yöntemi kullanılabilir durumda değil.</font></strong>";



    return $printIt;
}

?>
