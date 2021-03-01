<?php
// *************************************************************************
// *                                                                       *
// * Paywant Payment Gateway 											   *
// * Copyright 2016 ZEA Bilisim Hizmetleri Ltd. All rights reserved.	   *
// * Version: 1.0.0                                                        *
// * Build Date: 26 June 2016                                              *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * Email: integration@paywant.com                                        *
// * Website: https://www.paywant.com	                                   *
// *                                                                       *
// *************************************************************************

// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
	

if(!$_POST)
	exit("post yok");

$SiparisID = $_POST["SiparisID"];
$ExtraData= $_POST["ExtraData"];
$UserID= $_POST["UserID"];
$ReturnData= $_POST["ReturnData"];
$Status= $_POST["Status"];
$OdemeKanali= $_POST["OdemeKanali"];
$UrunFiyati= $_POST["UrunTutari"];
$OdemeTutari= $_POST["OdemeTutari"];
$NetKazanc= $_POST["NetKazanc"];
$Hash= $_POST["Hash"];
	  
if($SiparisID == "" || $UrunFiyati == "" || $ExtraData == "" || $UserID == "" || $ReturnData == "" || $Status == "" || $OdemeKanali == "" || $OdemeTutari == "" || $NetKazanc == "" || $Hash == "")
		  die("eksik veri");


	  if($odemeKanali == 1)
		$gatewayModuleName = basename('paywantmobile.php');
	  else if($odemeKanali == 3)
		$gatewayModuleName = basename('paywantbank.php');
	  else
		$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}


$apiKey = $gatewayParams['apiKey'];
 $secretKey = $gatewayParams['apiSecretKey'];

$hashKontrol = base64_encode(hash_hmac('sha256',"$SiparisID|$ExtraData|$UserID|$ReturnData|$Status|$OdemeKanali|$OdemeTutari|$NetKazanc".$apiKey,$secretKey,true));
if($Hash != $hashKontrol)
			   die("hash hatali");

$alreadyCompleted = mysql_num_rows(select_query('tblaccounts', 'id', array('transid' => $SiparisID)));
if ($alreadyCompleted){
	exit("OK");
}
$invoiceId = checkCbInvoiceID($ExtraData, $gatewayModuleName);

checkCbTransID($SiparisID);
$success = true;

if ($Status == "100") {
	$success = true;
} else {
	$output = "Sipariş ID: " . $SiparisID
	. "\r\nFatura ID: " . $ExtraData
	. "\r\nDurum: Başarısız";
	logTransaction($gatewayModuleName, $output, "Unsuccessful");
	$success = false;
	
	
}

if ($success) {
	 if($OdemeKanali == "1")
		$OdemeKanali = "Mobil Odeme";
	
	if($OdemeKanali == "2")
		$OdemeKanali = "Kredi Kartı";
	
	if($OdemeKanali == "3")
		$OdemeKanali = "Banka Havale/Eft/Atm";
	
	if($OdemeKanali == "4")
		$OdemeKanali = "TTNET Ödeme";
	
	if($OdemeKanali == "5")
		$OdemeKanali = "Mikrocard";
	
	if($OdemeKanali == "6")
		$OdemeKanali = "CashU";
	
	$output = "Sipariş ID: " . $SiparisID
			. "\r\nFatura ID: " . $ExtraData
			. "\r\nÖdeme Kanalı: " . $OdemeKanali
			. "\r\nDurum: Tamamlandı";
	
	addInvoicePayment($invoiceId, $SiparisID, $UrunFiyati, $OdemeTutari-$NetKazanc, $gatewayModuleName);
	logTransaction($gatewayModuleName, $output, "Successful");
    exit('OK');
}else
exit('error');
?>
