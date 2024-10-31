<?php

if ( class_exists('myCRED_Module')) {

	Class myCred_Amelia_Settings extends myCRED_Module {
		
		function __construct() {
			parent::__construct('mycred_amelia');
			add_action( 'mycred_after_core_prefs', array( $this, 'mycred_amelia_display_settings' ), 10, 1 );
			add_filter( 'mycred_save_core_prefs', array( $this, 'mycred_amelia_save_settings' ), 10, 3 );
		}
		
		function mycred_amelia_display_settings( $object ) {
			
			$settings = get_option( 'mycred_amelia_settings' , array());

			if(empty($settings) || !isset($settings['mycred_amelia']['insufficient_balance_msg'])) {
				$settings['mycred_amelia']['insufficient_balance_msg'] = '<a href="/buypoints"><h4>You dont have enough Points. Click here to Buy.</h4></a>';
			}
			?>
			<div class="mycred-ui-accordion">
                <div class="mycred-ui-accordion-header">
                    <h4 class="mycred-ui-accordion-header-title">
                    	<span class="dashicons dashicons-admin-plugins static mycred-ui-accordion-header-icon"></span><label><?php _e( 'Mycred Amelia Settings', 'mycred-amelia' ); ?></label>
                    </h4>
                    <div class="mycred-ui-accordion-header-actions hide-if-no-js">
                        <button type="button" aria-expanded="true">
                            <span class="mycred-ui-toggle-indicator" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
				<div class="body mycred-ui-accordion-body" style="display:none;">
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="form-group">
								<label for="<?php echo $this->field_id( array( 'mycred_amelia' => 'insufficient_balance_msg' ) ); ?>"><?php _e( 'Insufficient balance message', 'mycred' ); ?></label>
								<input type="text" name="<?php echo $this->field_name( array( 'mycred_amelia' => 'insufficient_balance_msg' ) ); ?>" id="<?php echo $this->field_id( array( 'mycred_amelia' => 'insufficient_balance_msg' ) ); ?>" class="form-control" value="<?php echo esc_attr( $settings['mycred_amelia']['insufficient_balance_msg'] ); ?>" />
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		
		function mycred_amelia_save_settings( $new_data, $post, $object ) {
			
			$mycred_amelia = array();
			$mycred_amelia['mycred_amelia'] = $post['mycred_amelia'];
			
			update_option( 'mycred_amelia_settings', $mycred_amelia );
			
			return $new_data;
		}
	}
	$myCred_Amelia_Settings = new myCred_Amelia_Settings();
}


