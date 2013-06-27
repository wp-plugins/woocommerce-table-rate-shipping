<?php
/*
Plugin Name: WooCommerce tablerate
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Plugin for fixed rate shipping depending upon the cart amount in WooCommerce.
Version: 1.0
Author: Nethues India
Author URI: http://URI_Of_The_Plugin_Author
License: GPL2
*/
?>
<?php
add_action('plugins_loaded', 'woocommerce_tablerate_init', 0);

function woocommerce_tablerate_init() {
	if (!class_exists('WC_Shipping_Method'))
		return;
	
	function add_table_rate_shipment($methods) {
		$methods[] = 'WC_Shipping_tablerate';
		return $methods;
	}
 	add_filter('woocommerce_shipping_methods', 'add_table_rate_shipment');
	class WC_Shipping_tablerate extends WC_Shipping_Method {
	
		function __construct(){
			global $woocommerce;
			
			$this->id = 'tablerate';
			
		  	if(isset($_POST['save']) ){ 
				$cntr_c	=	(isset($_REQUEST['cntr']) && $_REQUEST['cntr']!='')?$_REQUEST['cntr']:'';
				$wc_tablerate	=	(isset($_REQUEST['woocommerce_tablerate']) && $_REQUEST['woocommerce_tablerate']!='')?$_REQUEST['woocommerce_tablerate']:'Table Rate Shipping';
				if($wc_tablerate!=''){
					$this->method_title = __($wc_tablerate, 'wc_tablerate');
					$this->title = __($wc_tablerate, 'wc_tablerate');
				}
				$error_ini_amt=array();
				$error_final_amt=array();
				$error_ship_amt_amt=array();
				update_option( 'woocommerce_tablerate_settings', serialize($_POST)); 
			}
       
		   	$data = @unserialize(get_option('woocommerce_tablerate_settings'));
			
			$this->enabled = ($data['woocommerce_tablerate_enabled'] == 'yes') ? 'yes' : 'no'; 
			$wc_tablerate	=	(isset($data['woocommerce_tablerate']) && $data['woocommerce_tablerate']!='')?$data['woocommerce_tablerate']:'Table Rate Shipping';
			$this->method_title = __($wc_tablerate, 'wc_tablerate');
			$this->title = __($wc_tablerate, 'wc_tablerate');
			$this->shipping_methods	=	'WC_Shipping_tablerate';
			
			add_action( 'woocommerce_update_options_shipping_methods', array( &$this, 'process_admin_options') );
   		}
		
		function admin_options() {
			global $woocommerce;
			$data = @unserialize(get_option('woocommerce_tablerate_settings'));
      		if(isset($data['cntr']))
			{
					$cntr  = $data['cntr'];
			}
			else
			{
					$cntr = '1';
			}
			$woocommerce_tablerate_enabled_chk = ($data['woocommerce_tablerate_enabled'] == 'yes') ? 'checked' : '';  
			$wc_tablerate	=	(isset($data['woocommerce_tablerate']) && $data['woocommerce_tablerate']!='')?$data['woocommerce_tablerate']:'Table Rate Shipping';
			
			?>
			<h3><?php _e($wc_tablerate, 'wc_tablerate'); ?></h3>
			<script type="text/javascript" language="javascript" >
			jQuery('#mainform').submit(function() {
				
				var cntr	=	document.getElementById('cntr').value;
				for(var i=1;i<=cntr;i++){
					var woocommerce_tablerate_ini_amt	=	document.getElementById('woocommerce_tablerate_ini_amt'+i).value;
					var woocommerce_tablerate_final_amt	=	document.getElementById('woocommerce_tablerate_final_amt'+i).value;
					var woocommerce_tablerate_ship_amt	=	document.getElementById('woocommerce_tablerate_ship_amt'+i).value;
					
					if(woocommerce_tablerate_ini_amt==''){
						alert("Please enter Initial range of amount.");
						document.getElementById('woocommerce_tablerate_ini_amt'+i).focus();
						return false;
					}else if(isNaN(woocommerce_tablerate_ini_amt)){
						alert("Please enter only numeric value.");
						document.getElementById('woocommerce_tablerate_ini_amt'+i).focus();
						return false;
					}
					if(woocommerce_tablerate_final_amt==''){
						alert("Please enter final range of amount.");
						document.getElementById('woocommerce_tablerate_final_amt'+i).focus();
						return false;
					}else if(isNaN(woocommerce_tablerate_final_amt)){
						alert("Please enter only numeric value.");
						document.getElementById('woocommerce_tablerate_final_amt'+i).focus();
						return false;
					}
					if(woocommerce_tablerate_ship_amt==''){
						alert("Please enter shipping amount for entered range.");
						document.getElementById('woocommerce_tablerate_ship_amt'+i).focus();
						return false;
					}else if(isNaN(woocommerce_tablerate_ship_amt)){
						alert("Please enter only numeric value.");
						document.getElementById('woocommerce_tablerate_ship_amt'+i).focus();
						return false;
					}
				}
			});
			function addmore(){
				var cntr	=	document.getElementById('cntr').value;
				var newcntr	=	parseInt(cntr)+1;
				var newelement = document.createElement("div");
				newelement.setAttribute('id', 'table_content'+newcntr);
				newelement.innerHTML	='<div style="float: left; width: 199px;"><input type="text" name="woocommerce_tablerate_ini_amt'+newcntr+'" value="" id="woocommerce_tablerate_ini_amt'+newcntr+'" /></div><div style="float: left; width: 201px;"><input type="text" name="woocommerce_tablerate_final_amt'+newcntr+'" value="" id="woocommerce_tablerate_final_amt'+newcntr+'" /></div><div style="float: left; width: 206px;"><input type="text" name="woocommerce_tablerate_ship_amt'+newcntr+'" value="" id="woocommerce_tablerate_ship_amt'+newcntr+'" /></div></div>';
				
				var newcntrfinal	=	newcntr+1;
			
				var initamt	=	document.getElementById('woocommerce_tablerate_ini_amt'+newcntr).value;
				var ship_amt	=	document.getElementById('woocommerce_tablerate_ship_amt'+newcntr).value;
				document.getElementById('ini_amt_td').innerHTML	=	'<input type="text" name="woocommerce_tablerate_ini_amt'+newcntrfinal+'" value="'+initamt+'" id="woocommerce_tablerate_ini_amt'+newcntrfinal+'" />';
				document.getElementById('ship_amt_td').innerHTML	=	'<input type="text" name="woocommerce_tablerate_ship_amt'+newcntrfinal+'" value="'+ship_amt+'" id="woocommerce_tablerate_ship_amt'+newcntrfinal+'" style="margin-left:-9px;" />';
				
				
				document.getElementById('table_rate_div').appendChild(newelement);
				document.getElementById('cntr').value	=	newcntr;
				document.getElementById('rem_div').style.display='block';
			
			}
			
			function remov(){
				var cntr	=	parseInt(document.getElementById('cntr').value);
				if(cntr>0){
					document.getElementById('cntr').value=parseInt(cntr)-1;
					var parent=document.getElementById("table_rate_div");
					var child=document.getElementById('table_content'+cntr);
					parent.removeChild(child);
					
					
					var oldcntr	=	cntr+1;
					var initamt	=	document.getElementById('woocommerce_tablerate_ini_amt'+oldcntr).value;
					var ship_amt	=	document.getElementById('woocommerce_tablerate_ship_amt'+oldcntr).value;
					document.getElementById('ini_amt_td').innerHTML	=	'<input type="text" name="woocommerce_tablerate_ini_amt'+cntr+'" value="'+initamt+'" id="woocommerce_tablerate_ini_amt'+cntr+'" />';
					document.getElementById('ship_amt_td').innerHTML	=	'<input type="text" name="woocommerce_tablerate_ship_amt'+cntr+'" value="'+ship_amt+'" id="woocommerce_tablerate_ship_amt'+cntr+'" style="margin-left:-9px;" />';

				}
				if((parseInt(cntr)-1)==0){
					document.getElementById('rem_div').style.display='none';
				}

			}
			</script>

			<table style="background-color: #EEEEEE;border: 1px solid #CCCCCC;margin-top: 35px;padding: 10px;">
				<tr>
					<th style="text-align:left;">Enable/Disable</th>
					<td><input id="woocommerce_tablerate_enabled" class="" type="checkbox" <?=$woocommerce_tablerate_enabled_chk?> value="yes" name="woocommerce_tablerate_enabled" style="">
	Enable Table Rate<input type="hidden" name="availability" id="availability"  value="all" /></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td>&nbsp;</td>
				</tr>	
				<tr valign="top">
				<th class="titledesc" scope="row" align="left"><label for="woocommerce_local_pickup_title">Title</label></th>
				<td class="forminp" align="left">
				<fieldset><legend class="screen-reader-text"><span>Title</span></legend>
				<input type="text" value="<?=$wc_tablerate?>" style="" id="woocommerce_tablerate" name="woocommerce_tablerate" class="input-text wide-input "><span class="description">This controls the title which the user sees during checkout.</span>
				</fieldset></td>
				</tr>
				
				<tr>
					<th>&nbsp;</th>
					<td>&nbsp;</td>
				</tr>	
				<tr>
					<td colspan="2">
						<table>
						<tr>
							<th  style="width:200px; font-size:14px;">Initial Amount</th>
							<th  style="width:200px; font-size:14px;">Final Amount</th>
							<th  style="width:200px; font-size:14px;">Shipping Amount</th>
						</tr>
					
					
						<tr>
							<td  style=" text-align:center;" colspan="3">
								<div id="table_rate_div">
															 
									 
									 <?php
						for($i=1;$i<=$cntr;$i++){
						
						$woocommerce_tablerate_ini_amt	=	 isset($data['woocommerce_tablerate_ini_amt'.$i])?$data['woocommerce_tablerate_ini_amt'.$i]:'';
						$woocommerce_tablerate_final_amt	=	 isset($data['woocommerce_tablerate_final_amt'.$i])?$data['woocommerce_tablerate_final_amt'.$i]:'';
						$woocommerce_tablerate_ship_amt	=	 isset($data['woocommerce_tablerate_ship_amt'.$i])?$data['woocommerce_tablerate_ship_amt'.$i]:'';
							?>
						<div id="table_content<?=$i?>">
										<div style="float: left; width: 199px;"><input type="text" name="woocommerce_tablerate_ini_amt<?=$i?>" value="<?=$woocommerce_tablerate_ini_amt?>" id="woocommerce_tablerate_ini_amt<?=$i?>" />
										<?php
										if(isset($error_ini_amt[$i]) && $error_ini_amt[$i]!=''){
											echo "<br/><font color='red'>Please enter value.</font>";
										}
										
										?>
										</div>
										<div style="float: left; width: 201px;"><input type="text" name="woocommerce_tablerate_final_amt<?=$i?>" value="<?=$woocommerce_tablerate_final_amt?>" id="woocommerce_tablerate_final_amt<?=$i?>" /></div>
										<div style="float: left; width: 206px;"><input type="text" name="woocommerce_tablerate_ship_amt<?=$i?>" value="<?=$woocommerce_tablerate_ship_amt?>" id="woocommerce_tablerate_ship_amt<?=$i?>" /></div>
							 </div>
						<?
						}				
						?>
									 
								</div>
							
							
							
							
							</td>
						</tr>
					
				<tr>
					<td  style=" text-align:center;" id="ini_amt_td"><input type="text" name="woocommerce_tablerate_ini_amt<?=$cntr+1?>" value="<?=$data['woocommerce_tablerate_ini_amt'.($cntr+1)]?>" id="woocommerce_tablerate_ini_amt<?=$cntr+1?>" /></td>
					<td style=" text-align:center;">Infinite</td>
					<td style=" text-align:center;" id="ship_amt_td" ><input type="text" name="woocommerce_tablerate_ship_amt<?=$cntr+1?>" value="<?=$data['woocommerce_tablerate_ship_amt'.($cntr+1)]?>" id="woocommerce_tablerate_ship_amt<?=$cntr+1?>" style="margin-left:-9px;" /></td>
				
				</tr>
				</table>
					</td>
				</tr>
				
				<tr>
					<th>&nbsp;</th>
					<td>&nbsp;</td>
				</tr>
			</table>
				<div style="width:622px; height:50px;">
					<div style="width: 110px; margin-top: 20px; float: right;"><a href="javascript:void(0);" onclick="addmore();"  class="button-primary" >Add More </a></div>
					<div style="margin-top: 20px; width: 110px; float: right;" id="rem_div"><a href="javascript:void(0);" onclick="remov();"  class="button-primary">Remove </a></div>
					
				</div>
				<input type="hidden" name="cntr" id="cntr" value="<?=$cntr?>"  />

			<?php
		}
		   
	   function is_available() {
			global $woocommerce;
			if ( $this->enabled == "no" )
				return false;
			return true;
	      }
		  
	   function get_shipping_response() {
			global $woocommerce;
	
			// Get customer
			$customer = $woocommerce->customer;

			$customer->get_shipping_country();	

			if ( sizeof( $woocommerce->cart->get_cart() ) == 0 )
			return false;

			$product_bucket = false;

			//encoding the xml according to the destination
			$cart = $woocommerce->cart->get_cart();

			//deal with different packing type
			if ( $this->type == "per_item" ) {

				// If we are calculating per-item, flat rate boxes can be included in the main request
				foreach( $this->enabled_flat_rates as $id => $flat_rate ) {
					$this->services[ $id ] = $flat_rate['name'];
				}

				$product_bucket = $this->packing_perItem( $cart );

			} elseif ( $this->enable_custom_box && $this->weight && $this->box ) {

				// This requires a custom box
				$product_bucket = $this->packing_perOrder( $cart, $this->box, $this->length, $this->width, $this->height, $this->weight );

			}

			if ( $product_bucket ) {

				$request_queue = $this->encode( $product_bucket );
				$results = $this->post_request( $request_queue );

				// Add up the rates for each batch
				if ( sizeof( $results ) > 0 ) {
					$base_result = array_shift( $results );
					if ( is_array( $base_result ) ) {
						foreach ( $results as $key => $result ) {
							foreach ( $base_result as $id => $base ) {
								$base_result[$id]["rate"] += $result[$id]["rate"];
							}
						}
					}
				}

				# Take care first-class parcel as a special case
				if ( in_array('d0', $this->shipping_methods) && is_array($base_result) && isset($base_result[0]) ){
					$request_queue = $this->encode( $product_bucket, 'FIRST CLASS' );
					$results = $this->post_request( $request_queue );

					$first_class_id = 0;
					if ( sizeof( $results ) > 0 ) {
						$first_class = array_shift( $results );
						if ( is_array( $first_class ) ) {
							foreach ( $results as $key => $result ) {
								foreach ( $first_class as $id => $base ) {
									$first_class_id = $id;
									$first_class[$id]["rate"] += $result[$id]["rate"];
								}
							}
						}
						$base_result[$first_class_id] = $first_class[$first_class_id];
					}
				}

			}

			// Next, handle flat rates for per-order shipping
			if ( $this->type == "per_order" && ! empty( $this->enabled_flat_rates ) ) {

				$intel = $this->is_international() ? 'i' : 'd';

				foreach ( $this->enabled_flat_rates as $id ) {

					if ( ! $id )
						continue;

					$flat_rate = $this->flat_rates[ $id ];

					// Check the rate is international vs domestic
					if ( substr( $id, 0, 1 ) == $intel ) {

						$product_bucket = $this->packing_perOrder( $cart, $this->box_varify( $flat_rate['length'], $flat_rate['width'], $flat_rate['height'] ), $flat_rate['length'], $flat_rate['width'], $flat_rate['height'], $flat_rate['weight'] );

						$idnum = str_replace( array( 'i', 'd' ), '', $id );

						if ( isset( $flat_rate['cost'] ) && sizeof($product_bucket) ) {

							// Product bucket size dictates number of boxes needed

							if ( ( $customer->get_shipping_country() == 'CA' || $customer->get_shipping_country() == 'MX' ) && isset( $flat_rate['cost2'] ) )
								$base_result[ $idnum ]["rate"] = $flat_rate['cost2'] * sizeof( $product_bucket );
							else
								$base_result[ $idnum ]["rate"] = $flat_rate['cost'] * sizeof( $product_bucket );

						} else {

							// Use API
							$special_request_queue = $this->encode( $product_bucket );
							$results = $this->post_request( $special_request_queue );

							if ( sizeof( $results ) > 0 ) {

								$idnum = str_replace( array( 'i', 'd' ), '', $id );
								$special_base_result = array_shift( $results );

								// Only get the result we want
								if ( isset( $special_base_result[ $idnum ] ) )
									$base_result[ $idnum ]["rate"] = $special_base_result[ $idnum ]["rate"];
							}

						}

					}

				}

			}

			return ( isset( $base_result ) && count( $base_result ) ) ? $base_result : false;
		}
		
		function calculate_shipping(){		
			global $woocommerce;
			$data = @unserialize(get_option('woocommerce_tablerate_settings'));
			$shipamount='';
			if($data['woocommerce_tablerate_enabled']=='yes'){
			
			$cntr  = $data['cntr'];
			
			 for($i=1;$i<=$cntr+1;$i++){
				
					if(isset($data['woocommerce_tablerate_ini_amt'.$i]) && isset($data['woocommerce_tablerate_final_amt'.$i]) ){
					
						if((($woocommerce->cart->cart_contents_total)>=$data['woocommerce_tablerate_ini_amt'.$i]) && (($woocommerce->cart->cart_contents_total)<=$data['woocommerce_tablerate_final_amt'.$i]) ){
						
							$shipamount	=	$data['woocommerce_tablerate_ship_amt'.$i];
							break;
							
						}elseif((($woocommerce->cart->cart_contents_total)<=$data['woocommerce_tablerate_ini_amt'.$i]) && (($woocommerce->cart->cart_contents_total)>=$data['woocommerce_tablerate_final_amt'.$i])){
							$shipamount	=	$data['woocommerce_tablerate_ship_amt'.$i];
							break;
						}elseif(($woocommerce->cart->cart_contents_total)>=$data['woocommerce_tablerate_ini_amt'.$i] && !isset($data['woocommerce_tablerate_final_amt'.$i])){
							$shipamount	=	$data['woocommerce_tablerate_ship_amt'.$i];
							break;
						}
					}elseif(isset($data['woocommerce_tablerate_ini_amt'.$i]) && !isset($data['woocommerce_tablerate_final_amt'.$i]) && ($woocommerce->cart->cart_contents_total)>=$data['woocommerce_tablerate_ini_amt'.$i]){
						 $shipamount	=	$data['woocommerce_tablerate_ship_amt'.$i];
					}else{
						 $shipamount	=	0.00;
					}
				}
			}
			$rate = array(
							'id' 	=> $this->id,
							'label' => $this->title,
							'cost' 	=> $shipamount,
							'calc_tax' => 'per_order'
						);
			
			$this->add_rate( $rate );
		}
		
		 
		 public function load() {
			$this->title = __('Table Rate Shipping', 'wc_tablerate');
			add_action( 'init', array( $this, 'load_hooks' ) );
		 }
		 
		 public function is_woocommerce_activated() {
			if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				return true;
			} else {
				return false;
			}
		}
	}	
}
?>