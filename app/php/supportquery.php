<?php
	// My modifications to mailer script from:
	// Only process POST reqeusts.
	$name      = stripslashes( $_POST['name'] );
	$email     = stripslashes( $_POST['email'] );
	$phone    = stripslashes( $_POST['phone'] );
	$message   = stripslashes( $_POST['message'] );
	$issue = stripslashes( $_POST['issue']);

	$captcha = $_POST["captcha"];

	// TODO: get right key
	$secret = '6LeHUocUAAAAAKOrMsbn3NDI684iZ3MD481PFT56'; // secret for the support enquiry form

	// Set the from email address.
	$from = "hardscrabble.co.zw";

	$subject = $issue;

	// Set the recipient email address.
	$to = "support@hardscrabble.co.zw";

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

				A client has been sent an Support Query to Hardscrabble.

				Name: $name
				Email: $email
				Message: $message
				Phone: $phone

				Yours Sincerely,
				Your website.
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
