<?php
 
class Stephanis_CouponDispensers_Helper_Generator extends Mage_Core_Helper_Abstract {

	protected $chars = array();

	public function __construct() {
		// Skip 0, 1, 5
		$this->chars['D'] = str_split( '2346789' );

		// Skip I, O, Q, S, Z
		$this->chars['L'] = str_split( 'ABCDEFGHJKLMNPRTUVWXY' );
	}

	public function validate( $pattern ) {
		if ( false !== strpos( $pattern, 'L' ) || false !== strpos( $pattern, 'D' ) ) {
			return true;
		}
		return false;
	}

	public function getCode( $pattern, $code = '' ) {
		foreach ( str_split( $pattern ) as $char ) {
			if ( isset( $this->chars[ $char ] ) ) {
				$rand = array_rand( $this->chars[ $char ] );
				$code .= $this->chars[ $char ][ $rand ];
			} else {
				$code .= $char;
			}
		}
		return $code;
	}

}
