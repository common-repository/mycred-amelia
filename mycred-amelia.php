<?php
/**
 * Plugin Name: myCred Amelia
 * Plugin URI: https://mycred.me
 * Description: myCred-Amelia is a free add-on that connects myCred points management system with Amelia appointment-booking WordPress plugin.
 * Version: 1.1.5
 * Author: myCred
 * Author URI: http://mycred.me
 * Author Email: support@mycred.me
 * Requires at least: WP 4.8
 * Tested up to: WP 6.6.1
 */

define('MYCRED_AMELIA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MYCRED_AMELIA_PLUGIN_PATH', plugin_dir_path(__FILE__));

function mycred_amelia_enqueue_script() {
	
	$mycred = mycred();
	?>
	<script type="text/javascript">
		
		//Point type prefix
		var myCredPrefix  = "<?php echo esc_js($mycred->core['before']); ?>";

		//User balance
		<?php if( is_user_logged_in() ){?>
		var myCredBalance = parseFloat(<?php echo esc_js($mycred->get_users_balance( get_current_user_id() )); ?>);
		<?php } 
		else {
		?>
		myCredBalance = 0;
		<?php
		}
		?>
		var isUserLoggedIn = <?php echo esc_html(get_current_user_id()); ?>;
		jQuery(function($) {

			//Replace currency symbol
			$(document).ready( function () {
				repaleCurrencySymbol( '¥', myCredPrefix );
			});

			$('body').on( 'DOMNodeInserted', 'div', function () {
				repaleCurrencySymbol( '¥', myCredPrefix );
			});

		});

		function repaleCurrencySymbol( a, b ) {
		    if( window.find )
		    {
			    while( window.find( a ) ) {

			        var rng = window.getSelection().getRangeAt(0);
			        rng.deleteContents();
			        rng.insertNode( document.createTextNode( b ) );

			    }
		    }
		    else if( document.body.createTextRange ) {

				var rng = document.body.createTextRange();
				
				while( rng.findText( a ) )
				{
					rng.pasteHTML( b );
				}
		    
		    }
		}

	</script>
<?php
}
add_action( 'wp_footer', 'mycred_amelia_enqueue_script' );

//Payment through myCred Points
add_filter( 'amelia_before_payment', 'mycred_amelia_before_payment_func', 10, 2 );

function mycred_amelia_before_payment_func( $paymentData, $amount ) {


	$mycred = mycred();
	$user_balance = $mycred->get_users_balance( get_current_user_id() );

	if ( $paymentData['gateway'] == 'onSite' && floatval( $user_balance ) >= $amount ) {

		$paymentData['amount'] = $amount;
		$paymentData['status'] = 'paid';

		mycred_add(
			'amelia_booking_payment',
			get_current_user_id(),
			( -1 * $amount ),
			'Payment for booking.',
			$paymentData['customerBookingId'],
			''
		);
		
	}

	return $paymentData;
}

//Add script for ameliacustomerpanel and ameliabooking shortcode 
add_filter( 'the_content', 'mycred_amelia_check_booking_shortcode' );
 
function mycred_amelia_check_booking_shortcode( $content ) {

	$mycred = mycred();

	mycred_amelia_booking_script();
	mycred_amelia_event_script();
 
    return $content;
}

function mycred_amelia_booking_script() {
	$settings=get_option( 'mycred_amelia_settings' );

	if(empty($settings) || !isset($settings['mycred_amelia']['insufficient_balance_msg'])) {
		$settings['mycred_amelia']['insufficient_balance_msg'] = '<a href="/buypoints"><h4>You dont have enough Points. Click here to Buy.</h4></a>';
	}
	?>
	<script type="text/javascript">
		
		window.buyCredUrl = '<?php echo wp_kses_post( $settings['mycred_amelia']['insufficient_balance_msg'] ); ?>';
		window.ameliaActions = {
		 	beforeBooking: function (success = null, error = null, data) {

			    console.log('Before booking is created HOOK')
			    jQuery.ajax({
			      	type: "POST",
			      	url:  my_ajax_object.ajax_url + '?action=wpamelia_api&call=/payments/amount',
			      	data: JSON.stringify(data),
		      		contentType: "application/json; charset=utf-8",
			      	dataType: "json",
			      	success: function (resultData){
			        	if ( resultData.data.amount > myCredBalance ) {
			        		error('You dont have enough points.');
			        		jQuery('<a href="/buypoints"><h4>Click here to Buy.</h4></a>').insertBefore('.am-fs__payments-heading-main');
			        		console.log('you dont have enoug points');
			        	}else {
			        		success()
			        	}
			      	},
			      	error: function (errMsg) {
			        	error('Something went wrong. Try again');
			      	}
				})
		  	},
		}

		window.beforeConfirmBookingLoaded = function (reservation, bookable, employee, location) {
			var totalPrice = !bookable.ticketsData ? reservation.bookings[0].persons * bookable.price : bookable.price;
			console.log(totalPrice);
			jQuery(document).ready( function(){
				if( isUserLoggedIn == 0 ) {
					
					jQuery('.payment-dialog-footer').hide();
					jQuery('.am-confirmation-booking-cost').after('<h4>Login to Book.</h4>');
					return false;
					
				}
				
				if ( totalPrice > myCredBalance ) {
					jQuery('.payment-dialog-footer').hide();
					jQuery('.am-confirmation-booking-cost').after('<?php echo wp_kses_post( $settings['mycred_amelia']['insufficient_balance_msg'] ); ?>');
				}
			} );
		}
	</script>

<?php
}


function mycred_amelia_event_script() {
	$settings=get_option( 'mycred_amelia_settings' );

	if(empty($settings) || !isset($settings['mycred_amelia']['insufficient_balance_msg'])) {
		$settings['mycred_amelia']['insufficient_balance_msg'] = '<a href="/buypoints"><h4>You dont have enough Points. Click here to Buy.</h4></a>';
	}
	?>
	<script type="text/javascript">
		window.beforeConfirmBookingLoaded = function (reservation, bookable, employee, location) {
			var totalPrice = bookable.aggregatedPrice ? reservation.bookings[0].persons * bookable.price : bookable.price;
			jQuery( document ).ready( function(){
				console.log( totalPrice );
				if( isUserLoggedIn == 0 ) {
					
					jQuery('.payment-dialog-footer').hide();
					jQuery('.am-confirmation-booking-cost').after('<h4>Login to Book.</h4>');
					return false;
					
				}
				if ( totalPrice > myCredBalance ) {
					jQuery('.payment-dialog-footer').hide();
					jQuery('.am-confirmation-booking-cost').after('<?php echo wp_kses_post( $settings['mycred_amelia']['insufficient_balance_msg'] ); ?>');
				}
			} );
		}
	</script>

<?php
}

function mycred_amelia_booking_list_script() {?>

	<script type="text/javascript">
		jQuery(function($) {
			$(document).ready( function(){
				if ( $('.amelia-app-booking').length > 0 ) {

					var timer = setInterval(
						function(){

							if( $('.am-cabinet').length > 0 ) {

								$('.am-cabinet h4').each( function( i ) {
									$(this).html( $(this).html().replace( '¥', myCredPrefix ) );
								} );
								clearInterval(timer);
							}
						}, 
						1000
					);

				}
			} );
		});
	</script>

<?php
}

function mycred_amelia_enqueue() {

   	wp_localize_script( 'jquery', 'my_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_script('jquery');
    
}
add_action( 'wp_enqueue_scripts', 'mycred_amelia_enqueue' );
add_action('mycred_init', 'mycred_amelia_load_files');

function mycred_amelia_load_files() {
	include(MYCRED_AMELIA_PLUGIN_PATH . 'inc/settings.php');
}