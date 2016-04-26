<?php
	session_start();
	error_reporting(E_ALL);
	
	include "../webservice/mysql_connection.php";
	require '../webservice/GCM.php';
	define("GOOGLE_API_KEY", "AIzaSyAx8OY0E2K1vvX_H8Sa3yaewelF_aaGGxQ");
	extract($_REQUEST);
	$main_id = $id;
	$total_amount = $price;
	require_once 'MangoPay/Autoloader.php';
	$api = new MangoPay\MangoPayApi();

	// configuration
	$api->Config->ClientId = 'brainpulse';
	$api->Config->ClientPassword = 'o1RPOg0KyE9uxnNxN2UV7EhMwDoHpbju5G2r4CPJWOMCMqEZpe';
	$api->Config->TemporaryFolder = '/tmp/';
	
	$result = $api->PayIns->Get($_SESSION["MangoPayDemo"]["PayInCardWeb"]);
	$status =$result->Status;
	if($status=='SUCCEEDED'){
		
		if($type=='message'){
			$my_avail_req11="select * from pre_message where id=$id";
			$fetch_available=selectone($my_avail_req11);

			$availability_id=$fetch_available['avail_id'];
			$job_id=$fetch_available['job_id'];
			
			$q="update pre_message set status='2',chat_archive='1' where id=$id";
			if(mysql_query($q)){
				extract($fetch_available);
				$sql = "INSERT into pre_job_list values('','$id', '$job_id', '$avail_id', '$comp_id', '$title', '$start_date', '$end_date', '$start_time', '$end_time', '$no_of_days', '$company_id', '$no_of_person_need', '$required_service','2', '$company_to', '$company_from', '$payment_by', '$payment_to', '$amount_per_hour', '$chat_archive', NOW(), 'upcoming', '', '$street', '$street_no', '$city', '$zip', '$country','$amount')";
				mysql_query($sql);
				
				$job_list_id=mysql_insert_id();
				$Payment_by_invoice = date('Ymd').$job_list_id.$payment_by;
				$Payment_to_invoice = date('Ymd').$job_list_id.$payment_to;
				
				$pay_sql = "INSERT into pre_payment values('','$job_list_id','$payment_by', '$payment_to', '$total_amount', '$qmh_user_charge', '$qmh_agency_charge', '1', '$Payment_by_invoice', '$Payment_to_invoice', '$payment_mode', NOW(), '', '', '', '')";
				mysql_query($pay_sql);
				echo json_encode(array('result'=>true, 'message'=>"payment Succesfull"));
			}
		}
		if($type=='direct'){
			$my_avail_req11="select * from directly_requested where id=$id";
			$fetch_available=selectone($my_avail_req11);

			$availability_id=$fetch_available['avail_id'];
			$job_id=$fetch_available['job_id'];
			
			$q="update directly_requested set status='2',chat_archive='1' where id=$id";
			if(mysql_query($q)){
				extract($fetch_available);
				$sql = "INSERT into pre_job_list values('','$id', '$job_id', '$avail_id', '$comp_id', '$title', '$start_date', '$end_date', '$start_time', '$end_time', '$no_of_days', '$company_id', '$no_of_person_need', '$required_service','2', '$company_to', '$company_from', '$payment_by', '$payment_to', '$amount_per_hour', '$chat_archive', NOW(), 'upcoming', '', '$street', '$street_no', '$city', '$zip', '$country','$amount')";
				mysql_query($sql);
				
				$job_list_id=mysql_insert_id();
				$Payment_by_invoice = date('Ymd').$job_list_id.$payment_by;
				$Payment_to_invoice = date('Ymd').$job_list_id.$payment_to;
				
				$pay_sql = "INSERT into pre_payment values('','$job_list_id','$payment_by', '$payment_to', '$total_amount', '$qmh_user_charge', '$qmh_agency_charge', '1', '$Payment_by_invoice', '$Payment_to_invoice', '$payment_mode', NOW(), '', '', '', '')";
				mysql_query($pay_sql);
				echo json_encode(array('result'=>true, 'message'=>"payment Succesfull"));
				
			}
		}
	}
	else{
		echo "payment not done.";
	}
	
?>