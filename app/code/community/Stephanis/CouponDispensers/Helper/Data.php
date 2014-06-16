<?php

class Stephanis_CouponDispensers_Helper_Data extends Mage_Core_Helper_Abstract {

	public function work_it( $post, $tld, $pattern, $rule_id, $months = 6 ) {
		$code = $error = '';
		$campaignMonitor = Mage::helper( 'coupondispensers/campaignmonitor' );

		if ( empty( $rule_id ) ) {
			return false;
		}

		$first_name = addslashes( $post['first_name'] );
		$last_name  = addslashes( $post['last_name'] );
		$email      = "{$post['email_user']}@{$tld}";
		$validator  = new Zend_Validate_EmailAddress();

		if ( ! $validator->isValid( $email ) ) {
			$error .= '<div class="error">'
					. '<p>Error #' . __LINE__ . '.</p>'
					. '<p>Invalid email address.</p>'
					. '</div>';
			return array( $email, $error );
		}

		if ( false !== strpos( $email, '+' ) ) {
			$error .= '<div class="error">'
					. '<p>Error #' . __LINE__ . '.</p>'
					. '<p>Your email address may not contain a `+`.</p>'
					. '</div>';
			return array( $email, $error );
		}

		$subscriber = $campaignMonitor->getSubscriberData( $email );

		$canSend = true;
		if ( ! empty( $subscriber ) && isset( $subscriber['custom']['last_issued_date'] ) ) {
			$canSend = $this->hasBeenXMonths( $subscriber['custom']['last_issued_date'], $months );
		}

		if ( $canSend ) {
			$code = $this->generate_single_use_coupon( $rule_id, $pattern );
			if ( empty( $code ) ) {
				$error .= '<div class="error">'
						. '<p>Error #' . __LINE__ . '.  Please try again.</p>'
						. '<p>If this error persists, please contact Customer Service.</p>'
						. '</div>';
				return array( $email, $error );
			}
			$campaignMonitor->send( $first_name, $last_name, $email, $code );
			$this->sendEmail( $first_name, $last_name, $email, $code, $months );
		} else {
			$error .= '<div class="error">'
					. '<p>Error #' . __LINE__ . '.</p>'
					. "<p>You can only request a coupon code every {$months} months, sorry!</p>"
					. '</div>';
		}

		return array( $email, $error );
	}

	public function hasBeenXMonths( $lastIssuedDate = null, $qtyMonths = 6 ) {
		$canSend = true;
		if ( $lastIssuedDate ){
			$timesince = time() - strtotime( $lastIssuedDate );
			if( $timesince < ( $qtyMonths * 30 * 24 * 60 * 60 ) ){
				$canSend = false;
			}
		}
		return $canSend;
	}

	public function sendEmail( $fname = '', $lname = '', $emailAddress = null, $code = '', $months = 6 ) {
		$storeName = Mage::app()->getStore()->getName();
		$_subject  = "Your {$storeName} Discount Code";

		$_body = "<p>Hi, $fname:</p>
					<p>Here&rsquo;s your requested discount code:</p>
					<pre>{$code}</pre>
					<p>Remember, you can only use it once, and you&rsquo;ll have to wait {$months} 
						months to get another, so choose &mdash; but choose wisely.</p>
					<p>Enjoy!</p>
					<p>{$storeName}</p>";

		$mail = new Zend_Mail();

		$mail->setSubject( $_subject );
		$mail->setBodyHtml( $_body );
		$mail->setFrom( Mage::getStoreConfig('trans_email/ident_general/email'),
						Mage::getStoreConfig('trans_email/ident_general/name') );
		$mail->setReplyTo( Mage::getStoreConfig('trans_email/ident_support/email') );
		$mail->addTo( $emailAddress, "{$fname} {$lname}" );

		$mail->send();
	}

	public function strip( $string ) {
		return preg_replace( '/[^\da-z_\-]/i', '', $string );
	}

	public function add_single_use_coupon( $rule_id, $code ) {
		$rule_id = (int) $rule_id;
		if ( empty( $rule_id ) ) {
			return false;
		}

		$code = $this->strip( $code );

		return $this->_insert_coupon( $code, $rule_id );
	}

	public function generate_single_use_coupon( $rule_id, $pattern = 'LDLDLDLDLDLDLD' ) {
		$rule_id = (int) $rule_id;
		if ( empty( $rule_id ) ) {
			return false;
		}

		$generator = Mage::helper('coupondispensers/generator');

		if ( ! $generator->validate( $pattern ) ) {
			return false;
		}

		$code = $generator->getCode( $pattern );

		return $this->_insert_coupon( $code, $rule_id );
	}

	private function _insert_coupon( $code, $rule_id ) {
		$coupon_data = array(
			'rule_id'            => $rule_id,
			'code'               => $code,
			'usage_limit'        => 1,
			'usage_per_customer' => 0,
			'expiration_date'    => NULL,
		);

		$coupon = Mage::getModel('salesrule/coupon')
					->setData( $coupon_data )
					->save();

		if ( ! $this->coupon_exists( $code ) ) {
			return null;
		}

		return $code;
	}

	public function coupon_exists( $code ) {
		$count = Mage::getModel('salesrule/coupon')
					->getCollection()
					->addFieldToFilter( 'code', array( 'eq' => $code ) )
					->count();
		return $count;
	}

}