<?php

class Stephanis_CouponDispensers_Model_System_Config_Source_Salesrule {

	public function toOptionArray() {
		$return = array( array( 'value' => '', 'label' => '' ) );
		$rules  = Mage::getModel('salesrule/rule')
					->getCollection()
					->addFieldToFilter( 'use_auto_generation', array( 'eq' => 1 ) );

		foreach ( $rules as $rule ) {
			$return[] = array(
				'value' => $rule->getRule_id(),
				'label' => $rule->getName(),
			);
		}

		return $return;
	}

	public function toArray() {
		$return = array();
		$rules = Mage::getModel('salesrule/rule')
					->getCollection()
					->addFieldToFilter( 'use_auto_generation', array( 'eq' => 1 ) );

		foreach ( $rules as $rule ) {
			$return[ $rule->getRule_id() ] = $rule->getName();
		}

		return $return;
	}

}
