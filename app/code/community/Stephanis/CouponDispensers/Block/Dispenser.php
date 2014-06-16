<?php

class Stephanis_CouponDispensers_Block_Dispenser
	extends Mage_Core_Block_Abstract
		implements Mage_Widget_Block_Interface {

	protected function _construct() {
		parent::_construct();
	}

	protected function _toHtml() {
		$helper = Mage::helper('coupondispensers');
		$error  = '';
		$action = Mage::getUrl( '', array(
			'_secure' => true,
			'_current' => true,
			'_use_rewrite' => true,
		) );

		$tld     = trim( $this->getData( 'tld' ), '@ ' );
		$rule_id = intval( $this->getData( 'rule_id' ) );
		$pattern = $helper->strip( $this->getData( 'pattern' ) );
		$months  = intval( $this->getData( 'months' ) );

		if ( empty( $tld ) ) {
			return '<!-- Missing $tld -->';
		}

		if ( empty( $rule_id ) ) {
			return '<!-- Missing $rule_id -->';
		}

		if ( empty( $months ) ) {
			$months = 6;
		}

		if ( empty( $pattern ) ) {
			$pattern = $helper->strip( $tld ) . '_LDLDLDLDLDLDLD';
		}

		if ( $_POST ) {
			list( $email, $error ) = $helper->work_it( $_POST, $tld, $pattern, $rule_id, $months );
		}

		ob_start();
		if ( $email && empty( $error ) ) : ?>

			<h1>Thanks! Sent the code to `<?php echo $email; ?>`</h1>

		<?php else : ?>

			<?php echo $error; ?>
			<form action="<?php echo $action ?>" method="post">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="cd_first_name">First Name</label></th>
							<td><input type="text" name="first_name" id="cd_first_name" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="cd_last_name">Last Name</label></th>
							<td><input type="text" name="last_name" id="cd_last_name" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="cd_email_user">Email</label></th>
							<td><input type="text" name="email_user" id="cd_email_user" /><span class="tld">@<?php echo $tld; ?></span></td>
						</tr>
					</tbody>
				</table>
				<input type="submit" />
			</form>

		<?php endif;
		return ob_get_clean();
	}

}
