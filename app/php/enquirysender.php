<?php
	// My modifications to mailer script from:
	// Only process POST reqeusts.
	$name      = stripslashes( $_POST['name'] );
	$email     = stripslashes( $_POST['email'] );
	$message   = stripslashes( $_POST['message'] );
	$enquirytype = stripslashes( $_POST['enquirytype']);
	$formtype =  stripslashes( $_POST['formtype']);

	$captcha = $_POST['captcha'];

	if ($formtype == 1) {
		$secret = '6LeEUocUAAAAABcxrR7FvJYrkVWIcjPEnWwKqvmz';//secret for the general enquiry form
	} else {
		$secret = '6Ld7UocUAAAAAA8-ZAHyR9BomNVzl-9bavcZkReo';// secret for the services enquiry form
	}

	// Set the from email address.
	$from = "hardscrabble.co.zw";

	$subject = $enquirytype;

	// Set the recipient email address.
	$to = "info@hardscrabble.co.zw";

	// verification function
	function verify_captcha( $sec, $req){
		// Verifying the user's response (https://developers.google.com/recaptcha/docs/verify)
		$verifyURL = 'https://www.google.com/recaptcha/api/siteverify';

		// Collect and build POST data
		$post_data = http_build_query(
			array(
				'secret' => $sec,
				'response' => $req,
				'remoteip' => (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER['REMOTE_ADDR'])
			)
		);

		// Send data on the best possible way
		if(function_exists('curl_init') && function_exists('curl_setopt') && function_exists('curl_exec')) {
			// Use cURL to get data 10x faster than using file_get_contents or other methods
			$ch =  curl_init($verifyURL);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($ch, CURLOPT_TIMEOUT, 5);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-type: application/x-www-form-urlencoded'));
				$response = curl_exec($ch);
			curl_close($ch);
		} else {
			// If server not have active cURL module, use file_get_contents
			$opts = array('http' =>
				array(
					'method'  => 'POST',
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'content' => $post_data
				)
			);
			$context  = stream_context_create($opts);
			$response = file_get_contents($verifyURL, false, $context);
		}

		// Verify all reponses and avoid PHP errors
		if($response) {
			$result = json_decode($response);
			if ($result->success===true) {
				return true;
			} else {
				return false;
			}
		}

		// Dead end
		return false;
	}

	// Build the email content.
	$message = "
				Hi,

				A visitor has been sent an enquiry about Hardscrabble.

				Name: $name
				Email: $email
				Message: $message

				Yours Sincerely,
				hardscrabble.co.zw
				";

	if ( verify_captcha( $secret, $captcha) ) {
		 // send the email
		 if( mail($to, $subject, $message))
		 {
			 echo "Success";
		 }else{
			 echo "No";
		 }

	 }
?>
