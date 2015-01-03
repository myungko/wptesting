<?php
/**
 * Plugin Name: Augustine-Test
 * Plugin URI: http://myungko.com
 * Description: A test plugin.
 * Version: 1.0.1
 * Author: Myung
 * Author URI: http://myungko.com
 * License: GPL2
 */

add_action('init', 'myStartSession', 1);
function myStartSession() {
    if(!session_id()) {
        session_start();
    }
}

add_action('wp_logout', 'myEndSession');
function myEndSession() {
	session_destroy();
}

 add_action( 'gigya_after_social_login', 'gigyaAfterSocialLogin', 10, 2 );

 function gigyaAfterSocialLogin( $gig_user, $wp_user ) {
     $_SESSION['userfname'] = $gig_user['firstName'];
     $_SESSION['userlname'] = $gig_user['lastName'];
 
	 $_SESSION['uid'] = urlencode($gig_user['UID']);
	 $_SESSION['uidsig'] = $gig_user['UIDSignature'];
	 $_SESSION['sigtimestamp'] = $gig_user['signatureTimestamp'];
		 
	 // Update the WP nickname from Gigya’s nickname.
     update_user_meta( $wp_user->ID, 'nickname', $gig_user['nickname'] );
	 
	 
 }
 
 add_action('get_user_contents_hook', 'get_user_contents_callback');
 
 function get_user_contents_callback() {
	 $acctKey = 'mbH7hYV7y4OdDUJkaVS+nMfGhv4fo61KzqXB7k7HoEg=';
	 $rootUri = 'https://api.datamarket.azure.com/Bing/Search';
	 $username = $_SESSION['userfname'];
	 if ($username) {
		 $query = urlencode("'{$username}'");
		 $serviceOp = "Web";
		 // Construct the full URI for the query.
		 $requestUri = "$rootUri/$serviceOp?\$format=json&Query=$query";
		 
		 // Encode the credentials and create the stream context.

		 $auth = base64_encode("$acctKey:$acctKey");
		 $data = array(
		 'http' => array(
		 'request_fulluri' => true,
		 // ignore_errors can help debug – remove for production. This option added in PHP 5.2.10
		 'ignore_errors' => true,
		 'header' => "Authorization: Basic $auth")
		 );

		 $context = stream_context_create($data);
		 // Get the response from Bing.
		 $response = file_get_contents($requestUri, 0, $context);
		 
		 // Decode the response. 
		 $jsonObj = json_decode($response); 
		 $resultStr = ''; 
		 // Parse each result according to its metadata type. 
		 foreach($jsonObj->d->results as $value) { 
			 switch ($value->__metadata->type) { 
				 case 'WebResult': 
				 $resultStr .= "<a href=\"{$value->Url}\" target='_blank'>{$value->Title}</a><p>{$value->Description}</p>";
				 break; 
				 case 'ImageResult': 
				 $resultStr .= "<h4>{$value->Title} ({$value->Width}x{$value->Height}) " . "{$value->FileSize} bytes)</h4>" . "<a href=\"{$value->MediaUrl}\" target='_blank'>" . "<img src=\"{$value->Thumbnail->MediaUrl}\"></a><br />"; 
				 break; 
			 }
		}
		echo $resultStr;
	 }
 }
 
 add_action('get_user_info_hook', 'get_user_info_callback');
 
 function get_user_info_callback(){
	 echo "<p> User First Name: " . $_SESSION['userfname'] . "</p>";
	 echo "<p> User Last Name: " . $_SESSION['userlname'] . "</p>";
	 echo "<p> User UID: " . $_SESSION['uid'] . "</p>";
 	 echo "<p> UID Signature: " . $_SESSION['uidsig'] . "</p>";
 	 echo "<p> Signature timestamp: " . $_SESSION['sigtimestamp'] . "</p>";
  }
 
 
 ?>