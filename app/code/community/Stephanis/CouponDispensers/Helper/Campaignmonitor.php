<?php

class Stephanis_CouponDispensers_Helper_CampaignMonitor extends Mage_Core_Helper_Abstract {

	public function getSubscriberData( $email, $list_id = null ) {
		$api_key  = ''; // Will be a 32-char hex string
		$_list_id = ''; // Will be a 32-char hex string

		if ( empty( $api_key ) ) {
			echo 'Error: Missing `$api_key` in ' . __CLASS__ . ':' . __FUNCTION__ . '.';
			return null;
		}

		if ( empty( $list_id ) ) {
			$list_id = $coupon_dispenser;
		}

		$url = "http://api.createsend.com/api/v3/subscribers/{$list_id}.json?email={$email}";

		$client = new Varien_Http_Client();
		$client->setUri( $url );
		$client->setAuth( $api_key );
		$client->setMethod( Zend_Http_Client::GET );
		$client->setConfig( array(
			'maxredirects' => 0,
			'timeout'      => 30,
		) );

		try {
			$response   = $client->request();
			$body       = $response->getBody();
			$subscriber = Zend_Json::decode( $body );
		} catch ( Exception $e ) {
			Mage::log( $e->getMessage() );
			return null;
		}

		$subscriber['custom'] = array();
		if ( isset( $subscriber['CustomFields'] ) && count( $subscriber['CustomFields'] ) ) {
			foreach ( $subscriber['CustomFields'] as $cf ) {
				$subscriber['custom'][ $cf['Key'] ] = $cf['Value'];
			}
		}

		return $subscriber;
	}

	public function send( $fname = '', $lname = '', $emailAddress = null, $code = '' ) {
		if ( $emailAddress ){

			$submit_url = '';         // Like: `http://mycompany.createsend.com/a/b/c/defghij/`
			$field_names = array(
				'name'  => 'cm-name', // Should always be `cm-name`
				'email' => '',        // Like: `cm-defghij-defghij`
				'date'  => '',        // Like: `cm-f-abcdef`
				'code'  => '',        // Like: `cm-f-abcdeg`
			);

			$client = new Varien_Http_Client();
			$client->setUri( $submit_url );
			$client->setConfig( array(
				'maxredirects' => 0,
				'timeout' => 30,
			) );

			$data = array();
			$data[ $field_names['name'] ]  = "$fname $lname"; // Name
			$data[ $field_names['email'] ] = $emailAddress;   // Email
			$data[ $field_names['date'] ]  = date('c');       // last_issued_date
			$data[ $field_names['code'] ]  = $code;           // last_issued_code

			$camMonPost = new Varien_Object( $data );

			$client->setMethod( Zend_Http_Client::POST );
			$client->setParameterPost( $camMonPost->getData() );

			try {
				$response = $client->request();
			} catch ( Exception $e ) {
				Mage::log( $e->getMessage() );
			}

		}
	}

}
