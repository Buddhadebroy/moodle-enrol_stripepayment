<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This script waits for Payment notification from Stripe,
 * then double checks that data by sending it back to Stripe.
 * If Stripe verifies this then it sets up the enrolment for that
 * 
 * @package    enrol_stripepayment
 * @author     DualCube <admin@dualcube.com>
 * @copyright  2019 DualCube Team(https://dualcube.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
global $CFG, $PAGE;
require_once($CFG->libdir . '/enrollib.php');
require_once('Stripe/init.php');

// get currency symbol
$currency_symbol = enrol_get_plugin('stripepayment')->show_currency_symbol(strtolower($instance->currency));
$plugin = enrol_get_plugin('stripepayment');
$enable_coupon_section = !empty($plugin->get_config('enable_coupon_section')) ? true : false;
$data = optional_param('data', null, PARAM_RAW);
$enrolbtncolor = $plugin->get_config('enrolbtncolor');
?>
<script src="https://js.stripe.com/v3/"></script>
<div class="strip-wrap">
    <div class="stripe-right">
        <p class='stripe-dclr'><?php print_string("paymentrequired") ?></p>
        <div class="stripe-img">
            <img src="<?php echo $CFG->wwwroot; ?>/enrol/stripepayment/pix/stripe-payment.png">
        </div>
        <form id="form_data_new" action="" method="post">
            <input id="form_data_new_data" type="hidden" name="data" value="" />
            <input id="form_data_new_coupon_id" type="hidden" name="coupon_id" value="" />
        </form>
        <div class="paydetail">
            <div class="stripe-line-row">
                <div class="stripe-line-left"><?php echo get_string("cost") . ":<span> {$currency_symbol}{$cost}</span>"; ?></div>
            </div>
            <?php if (isset($data)) if ($cost > $data) {
                (float)$discount = $cost - $data;
                $couponid = required_param('coupon_id', PARAM_RAW);
            ?>
                <div class='stripe-line-left'><?php echo get_string("couponapplied", "enrol_stripepayment") . ":<span> - {$currency_symbol}{$discount} [<i>{$couponid}</i>] </span>"; ?></div>
            <?php } ?>
            <div id="reload">
                <?php
                $couponid = null;
                if (isset($data)) {
                    $cost = $data;
                    $couponid = required_param('coupon_id', PARAM_RAW);
                }
                $amount = enrol_get_plugin('stripepayment')->get_stripe_amount($cost, $instance->currency, false);
                echo $enable_coupon_section ? "<div class='stripe-line-left'> " . get_string("final_cost", "enrol_stripepayment") . ": <span> $currency_symbol$cost <span></div>" : '';
                if ($enable_coupon_section) { ?>
                    <div class="couponcode-wrap">
                        <span class="couponcode-text"> <?php echo get_string("couponcodedescription", "enrol_stripepayment"); ?></span>
                        <p class="stripe-cupon-input">
                            <input type=text id="coupon" />
                            <button id="apply" class="stripe-cupon-apply"><?php echo get_string("applycode", "enrol_stripepayment"); ?></button>
                        </p>
                        <div id="new_coupon"></div>
                    </div>
                <?php }
                $costvalue = str_replace(".", "", $cost);
                if ($costvalue == 000) {  ?>
                    <div id="amountequalzero" class="stripe-buy-btn">
                        <button id="card-button-zero">
                            <?php echo get_string("enrol_now", "enrol_stripepayment"); ?>
                        </button>
                    </div>
                <?php } else { ?>
                    <div id="paymentResponse" class="stripe-buy-btn">
                        <div id="buynow">
                            <button class="stripe-button" id="payButton"><?php echo get_string("buy_now", "enrol_stripepayment"); ?></button>
                        </div>
                    <?php } ?>
                    </div>
                    <?php $PAGE->requires->js_call_amd('enrol_stripepayment/stripe_payment', 'stripe_payment', array( $USER->id, $plugin->get_config('publishablekey'), $couponid, $instance->id, get_string("please_wait", "enrol_stripepayment"), get_string("buy_now", "enrol_stripepayment"), get_string("invalidcouponcode", "enrol_stripepayment"))); ?>
            </div>
        </div>
                

      <div class="paydetail wrapper">
                
           <ul>
                <li class="stripe-row">
                	<div class="details">
                		<div class="name"> Enrolment in course Hello Dolly </div>
                	</div>
                	<div class="price">
                		<div class="total-price">$0.00</div>
                	</div>
                </li>
                
                <li class="stripe-row">
                	<div class="details">
                		<div class="name"> Hello Dolly </div>
                	</div>
                	<div class="price">
                		<div class="total-price">$0.00</div>
                	</div>
                </li>                
           </ul>
                
           <div class="stripe-row">
                <div class="details">
                    <div class="name">Sub Total</div>
                </div>
                <div class="price">
                    <div class="total-price">$22.50</div>
                </div>
           </div>
                
           <div class="promotion-code">
                <div class="couponcode-wrap">
                        <span class="couponcode-text">  If you have any coupon please apply here</span>
                        <p class="stripe-cupon-input">
                            <input type=text id="coupon" />
                            <button id="apply" class="stripe-cupon-apply">Apply code</button>
                        </p>
                        <div id="new_coupon"></div>
                 </div>
           </div>
          
       </div>          
                
    </div>
</div>
<style>
    .paydetail.wrapper{
      	padding-top:3rem;
        width:60%;  
        margin:auto;         
     }           
    .paydetail .heading{
        font-size: 1rem;
  		margin: 0;
  		color: #6f6f6f;
     }
	.paydetail .price-row{
    	display:flex;
    	align-items: center;
  		gap: 0.5rem;
    }
	.paydetail .price-row .price{
    	font-size:2.375rem;
    }
	.paydetail .price-row .duration{
    	display:flex;
    	flex-direction:column;
    }
	.paydetail ul{
    	padding-top:2.5rem;
    	padding-left:0;
    	border-bottom: 0.063rem solid #eee;
    }
	.paydetail .stripe-row{
    	text-decoration:none;
    	padding:0;
    	display:flex;
    	justify-content: space-between;
    	margin-bottom:0.875rem;
    }
	.paydetail .stripe-row .details .name,
	.paydetail .stripe-row .price .total-price{
    	font-size: 1rem;
  		font-weight: 600;
    }
	.paydetail .stripe-row .duratuion{
    	color: #888787;
  		font-size: 0.85rem;
	}
	.paydetail .stripe-row .price{
    	text-align:right;
    }
	.paydetail .promotion-code{
    	color:#1177d1;
    	font-size:1.1rem;
    	border-bottom: 0.063rem solid #eee;
        padding-bottom:1rem;
    }
	.paydetail .promotion-code .couponcode-wrap{
    	width:100%;
    }
	.stripe-row.total{
    	padding-top:1rem;
    	margin-bottom:0;
    	color: #00000087;
    	font-weight:500;
    }               
   
    button#apply {
        color: #fff;
        background-color: <?php echo $enrolbtncolor; ?>;
        border: 0;
        padding: 5px 16px;
        border-radius: 0.5rem;
        font-size: 13px;
    }

    button#payButton,
    button#card-button-zero {
        color: #fff;
        background-color: <?php echo $enrolbtncolor; ?>;
        border: 0;
        padding: 5px 32px;
        border-radius: 0.25rem;
        font-size: 13px;
        box-shadow: 0 0.125rem 0.25rem #645cff2e;
        width: 100%;
    }

    
</style>
