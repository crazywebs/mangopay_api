<?php
	
	session_start();
	error_reporting(E_ALL);
	include "../webservice/mysql_connection.php";
	extract($_REQUEST);
	
	if($type=='direct'){
		$job_datas="select payment_by from directly_requested where id=$id";
		$job_data = selectone($job_datas);
		$company_id =$job_data['payment_by'];
	}
	if($type=='message'){
		$job_datas="select payment_by from pre_message where id=$id";
		$job_data = selectone($job_datas);
		$company_id =$job_data['payment_by'];
	}
	
	$user_datas="select * from pre_user where user_id=(select user_id from pre_company where company_id=$company_id)";
	$user_data = selectone($user_datas);
	$email = $user_data['email_id'];
	$first_name = $user_data['company_name'];
	
	require_once 'MangoPay/Autoloader.php';
	$api = new MangoPay\MangoPayApi();

	// configuration
	$api->Config->ClientId = 'xyz';
	$api->Config->ClientPassword = 'xyz';
	$api->Config->TemporaryFolder = '/tmp/';
	//echo "<pre>";print_r($api);die;
	//  For user creation.
	//function createuser(){
	$User = new MangoPay\UserNatural();
	//print_r($User);
	$User->Email = $email;
	$User->FirstName = $first_name;
	$User->LastName = $first_name;
	$User->Birthday = 121271;
	$User->Nationality = "FR";
	$User->CountryOfResidence = "ZA";
	$result = $api->Users->Create($User);
	$_SESSION["MangoPayDemo"]["UserNatural"] = $result->Id;
	//print_r($result);
	//}
	
	//function createwallet(){
	 // Create Wallet for Natural User
	$Wallet = new MangoPay\Wallet();
	$Wallet->Owners = array($_SESSION["MangoPayDemo"]["UserNatural"]);
	$Wallet->Description = "Demo wallet for User 1";
	$Wallet->Currency = "EUR";
	$result = $api->Wallets->Create($Wallet);
	//print_r($result);die;
	$_SESSION["MangoPayDemo"]["WalletForNaturalUser"] = $result->Id;
	//}
	
	//Create Legal User
	/* $User = new MangoPay\UserLegal();
	$User->Name = "Name Legal Test";
	$User->LegalPersonType = "BUSINESS";
	$User->Email = "spatel2092@gmail.com";
	$User->LegalRepresentativeFirstName = "Santosh";
	$User->LegalRepresentativeLastName = "Patel";
	$User->LegalRepresentativeBirthday = 121271;
	$User->LegalRepresentativeNationality = "FR";
	$User->LegalRepresentativeCountryOfResidence = "ZA";
	$result = $api->Users->Create($User);
	$_SESSION["MangoPayDemo"]["UserLegal"] = $result->Id;
	//echo "hello";print_r($result);die;
	
	//Create Wallet for Legal User
	//Note that there is no difference between a Wallet for a Natural User and a Legal User
	$Wallet = new MangoPay\Wallet();
	$Wallet->Owners = array($_SESSION["MangoPayDemo"]["UserLegal"]);
	$Wallet->Description = "Demo wallet for User 2";
	$Wallet->Currency = "EUR";
	$result = $api->Wallets->Create($Wallet);
	$_SESSION["MangoPayDemo"]["WalletForLegalUser"] = $result->Id; */
	
	//function pay(){
	//Create PayIn Card Web
	$PayIn = new MangoPay\PayIn();
	$PayIn->CreditedWalletId = $_SESSION["MangoPayDemo"]["WalletForNaturalUser"];
	$PayIn->AuthorId = $_SESSION["MangoPayDemo"]["UserNatural"];
	$PayIn->PaymentType = "CARD";
	$PayIn->PaymentDetails = new MangoPay\PayInPaymentDetailsCard();
	$PayIn->PaymentDetails->CardType = "CB_VISA_MASTERCARD";
	$PayIn->DebitedFunds = new MangoPay\Money();
	$PayIn->DebitedFunds->Currency = "EUR";
	$PayIn->DebitedFunds->Amount = 2500;
	$PayIn->Fees = new MangoPay\Money();
	$PayIn->Fees->Currency = "EUR";
	$PayIn->Fees->Amount = 150;
	$PayIn->ExecutionType = "WEB";
	$PayIn->ExecutionDetails = new MangoPay\PayInExecutionDetailsWeb();
	$PayIn->ExecutionDetails->ReturnURL = "http".(isset($_SERVER['HTTPS']) ? "s" : null)."://xyz.com?id=".$id."&type=".$type."&price=".$price."&qmh_user_charge=".$qmh_user_charge."&qmh_agency_charge=".$qmh_agency_charge."&payment_mode=".$payment_mode;
	$PayIn->ExecutionDetails->Culture = "EN";
	$result = $api->PayIns->Create($PayIn);
	$_SESSION["MangoPayDemo"]["PayInCardWeb"] = $result->Id;
	//echo "<pre>";print_r($result);die;
	header('Location: '.$result->ExecutionDetails->RedirectURL);
	//}
	
	/*
	//$create = createuser();
	//$wallet = createwallet();
	//$pay = pay();
	//echo "hello";
	//function check(){
	//Review PayIn Card Web
	//$result = $api->PayIns->Get($_SESSION["MangoPayDemo"]["PayInCardWeb"]);
	//print_r($result);die;
	//}
	
	/*
	//Create Card Registration
	$cardRegister = new \MangoPay\CardRegistration();
	$cardRegister->UserId = $_SESSION["MangoPayDemo"]["UserNatural"];
	$cardRegister->Currency = "EUR";
	$result = $mangoPayApi->CardRegistrations->Create($cardRegister);
	
	//Finish Card Registration
	$cardRegisterPut = $mangoPayApi->CardRegistrations->Get($_SESSION["MangoPayDemo"]["CardReg"]);
	$cardRegisterPut->RegistrationData = isset($_GET['data']) ? 'data=' . $_GET['data'] : 'errorCode=' . $_GET['errorCode'];
	$result = $mangoPayApi->CardRegistrations->Update($cardRegisterPut);
	
	//Do PayIn Card Direct
	$PayIn = new \MangoPay\PayIn();
	$PayIn->CreditedWalletId = $_SESSION["MangoPayDemo"]["WalletForNaturalUser"];
	$PayIn->AuthorId = $_SESSION["MangoPayDemo"]["UserNatural"];
	$PayIn->PaymentType = "CARD";
	$PayIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsCard();
	$PayIn->DebitedFunds = new \MangoPay\Money();
	$PayIn->DebitedFunds->Currency = "EUR";
	$PayIn->DebitedFunds->Amount = 599;
	$PayIn->Fees = new \MangoPay\Money();
	$PayIn->Fees->Currency = "EUR";
	$PayIn->Fees->Amount = 0;
	$PayIn->ExecutionType = "DIRECT";
	$PayIn->ExecutionDetails = new \MangoPay\PayInExecutionDetailsDirect();
	$PayIn->ExecutionDetails->SecureModeReturnURL = "http".(isset($_SERVER['HTTPS']) ? "s" : null)."://".$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"]."?stepId=".($stepId+1);
	$PayIn->ExecutionDetails->CardId = $_SESSION["MangoPayDemo"]["Card"];
	$result = $mangoPayApi->PayIns->Create($PayIn);
	
	//Setup a PreAuth
	$CardPreAuthorization = new \MangoPay\CardPreAuthorization();
	$CardPreAuthorization->AuthorId = $_SESSION["MangoPayDemo"]["UserNatural"];
	$CardPreAuthorization->DebitedFunds = new \MangoPay\Money();
	$CardPreAuthorization->DebitedFunds->Currency = "EUR";
	$CardPreAuthorization->DebitedFunds->Amount = 1500;
	$CardPreAuthorization->SecureMode = "DEFAULT";
	$CardPreAuthorization->CardId = $_SESSION["MangoPayDemo"]["Card"];
	$CardPreAuthorization->SecureModeReturnURL = "http".(isset($_SERVER['HTTPS']) ? "s" : null)."://".$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"]."?stepId=".($stepId+1);
	$result = $mangoPayApi->CardPreAuthorizations->Create($CardPreAuthorization);
	
	//Do a PayIn PreAuth
	$PayIn = new \MangoPay\PayIn();
	$PayIn->CreditedWalletId = $_SESSION["MangoPayDemo"]["WalletForNaturalUser"];
	$PayIn->AuthorId = $_SESSION["MangoPayDemo"]["UserNatural"];
	$PayIn->PaymentType = "CARD";
	$PayIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsPreAuthorized();
	$PayIn->PaymentDetails->PreauthorizationId = $_SESSION["MangoPayDemo"]["PreAuth"];
	$PayIn->DebitedFunds = new \MangoPay\Money();
	$PayIn->DebitedFunds->Currency = "EUR";
	$PayIn->DebitedFunds->Amount = 950;
	$PayIn->Fees = new \MangoPay\Money();
	$PayIn->Fees->Currency = "EUR";
	$PayIn->Fees->Amount = 550;
	$PayIn->ExecutionType = "DIRECT";
	$PayIn->ExecutionDetails = new \MangoPay\PayInExecutionDetailsDirect();
	$result = $mangoPayApi->PayIns->Create($PayIn);
	
	//Do a PayIn Refund
	$PayInId = $_SESSION["MangoPayDemo"]["PayInCardWeb"];
	$Refund = new \MangoPay\Refund();
	$Refund->AuthorId = $_SESSION["MangoPayDemo"]["UserNatural"];
	$Refund->DebitedFunds = new \MangoPay\Money();
	$Refund->DebitedFunds->Currency = "EUR";
	$Refund->DebitedFunds->Amount = 650;
	$Refund->Fees = new \MangoPay\Money();
	$Refund->Fees->Currency = "EUR";
	$Refund->Fees->Amount = -50;
	$result = $mangoPayApi->PayIns->CreateRefund($PayInId, $Refund);
	
	//Do a Transfer
	$Transfer = new \MangoPay\Transfer();
	$Transfer->AuthorId = $_SESSION["MangoPayDemo"]["UserNatural"];
	$Transfer->DebitedFunds = new \MangoPay\Money();
	$Transfer->DebitedFunds->Currency = "EUR";
	$Transfer->DebitedFunds->Amount = 760;
	$Transfer->Fees = new \MangoPay\Money();
	$Transfer->Fees->Currency = "EUR";
	$Transfer->Fees->Amount = 150;
	$Transfer->DebitedWalletID = $_SESSION["MangoPayDemo"]["WalletForNaturalUser"];
	$Transfer->CreditedWalletId = $_SESSION["MangoPayDemo"]["WalletForLegalUser"];
	$result = $mangoPayApi->Transfers->Create($Transfer);
	*/
	/* //Create a Bank Account (of IBAN type)
	$UserId = $_SESSION["MangoPayDemo"]["UserLegal"];
	$BankAccount = new MangoPay\BankAccount();
	$BankAccount->Type = "IBAN";
	$BankAccount->Details = new MangoPay\BankAccountDetailsIBAN();
	$BankAccount->Details->IBAN = "FR7618829754160173622224154";
	$BankAccount->Details->BIC = "CMBRFR2BCME";
	$BankAccount->OwnerName = "Joe Bloggs";
	$BankAccount->OwnerAddress = "1 Mangopay Street";
	$result = $api->Users->CreateBankAccount($UserId, $BankAccount);
	//$_SESSION["MangoPayDemo"]["BankAccount"] = $result->Id;
	print_r($result);die;
	//Do a PayOut
	$PayOut = new MangoPay\PayOut();
	$PayOut->AuthorId = $_SESSION["MangoPayDemo"]["UserLegal"];
	$PayOut->DebitedWalletID = $_SESSION["MangoPayDemo"]["WalletForLegalUser"];
	$PayOut->DebitedFunds = new MangoPay\Money();
	$PayOut->DebitedFunds->Currency = "EUR";
	$PayOut->DebitedFunds->Amount = 610;
	$PayOut->Fees = new MangoPay\Money();
	$PayOut->Fees->Currency = "EUR";
	$PayOut->Fees->Amount = 125;
	$PayOut->PaymentType = "BANK_WIRE";
	$PayOut->MeanOfPaymentDetails = new MangoPay\PayOutPaymentDetailsBankWire();
	$PayOut->MeanOfPaymentDetails->BankAccountId = $_SESSION["MangoPayDemo"]["BankAccount"];
	$result = $api->PayOuts->Create($PayOut);
	print_r($result); */
	/*
	//Submit a KYC Document
	//create the doc
	$KycDocument = new \MangoPay\KycDocument();
	$KycDocument->Type = "IDENTITY_PROOF";
	$result = $mangoPayApi->Users->CreateKycDocument($_SESSION["MangoPayDemo"]["UserNatural"], $KycDocument);
	$KycDocumentId = $result->Id;
	 
	//add a page to this doc
	$result2 = $mangoPayApi->Users->CreateKycPageFromFile($_SESSION["MangoPayDemo"]["UserNatural"], $KycDocumentId, "logo.png");
	 
	//submit the doc for validation
	$KycDocument = new MangoPay\KycDocument();
	$KycDocument->Id = $KycDocumentId;
	$KycDocument->Status = "VALIDATION_ASKED";
	$result3 = $mangoPayApi->Users->UpdateKycDocument($_SESSION["MangoPayDemo"]["UserNatural"], $KycDocument);
	//Submit a KYC Document end
	
	//Do a Transfer Refund
	$TransferId = $_SESSION["MangoPayDemo"]["Transfer"];
	$Refund = new \MangoPay\Refund();
	$Refund->AuthorId = $_SESSION["MangoPayDemo"]["UserNatural"];
	$Refund->DebitedFunds = new \MangoPay\Money();
	$Refund->DebitedFunds->Currency = "EUR";
	$Refund->DebitedFunds->Amount = 760;//Note that partial Refunds for Transfers are not possible
	$Refund->Fees = new \MangoPay\Money();
	$Refund->Fees->Currency = "EUR";
	$Refund->Fees->Amount = -150;
	$result = $mangoPayApi->Transfers->CreateRefund($TransferId, $Refund);
	
	// */
	
	
?>