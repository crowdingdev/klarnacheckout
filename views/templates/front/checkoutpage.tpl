{if isset($klarna_error)}
	<div class="warning">{$klarna_error}</div>
{else}
	{if isset($vouchererrors) && $vouchererrors!=''}
		<div class="warning">{$vouchererrors}</div>
	{/if}
	
{literal}
 <script type="text/javascript">
  $(document).ready(function(){
   $('#kco-full-width').hide();
	$('.editkco').fancybox({
            'autoScale': true,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 500,
            'speedOut': 300,
            'autoDimensions': true,
            'centerOnScroll': true
        });
   });
 </script>
{/literal}
<script type="text/javascript">
// <![CDATA[
var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
var currencyRate = '{$currencyRate|floatval}';
var currencyFormat = '{$currencyFormat|intval}';
var currencyBlank = '{$currencyBlank|intval}';
var txtProduct = "{l s='product' js=1}";
var txtProducts = "{l s='products' js=1}";
var freeShippingTranslation = "{l s='Free Shipping!' js=1}";
var freeProductTranslation = "{l s='Free!' js=1}";
var kcourl = "{$kcourl}";
// ]]>
</script>
	
	<div id="kco-full-width">
		<div id="kco_cart_summary_div">
			{include file="./shopping-cart.tpl"}
		</div>
	</div>

	<div class="floatleft col-lg-3 col-xs-12" id="voucherdiv">
	{if $no_active_countries > 1}
	<form action="{$posturl}" method="post" id="kco_change_country">
	<h4><label for="select_carrier">{l s='Country' mod='klarnacheckout'}</label>
		<select name="kco_change_country" onchange="$('#kco_change_country').submit();">
			{if $show_sweden}<option value="sv"{if $kco_selected_country=='SE'} selected="selected"{/if}>{l s='Sweden' mod='klarnacheckout'}</option>{/if}
			{if $show_norway}<option value="no"{if $kco_selected_country=='NO'} selected="selected"{/if}>{l s='Norway' mod='klarnacheckout'}</option>{/if}
			{if $show_finland}<option value="fi"{if $kco_selected_country=='FI'} selected="selected"{/if}>{l s='Finland' mod='klarnacheckout'}</option>{/if}
		</select>
	</h4>
	</form>
	{/if}
		<h4><label for="summary">{l s='Summary' mod='klarnacheckout'}</label> <a class="editkco" href="#kco_cart_summary_div">{l s='Change cart' mod='klarnacheckout'}</a></h4>
		<div class="sum_container">
		
			{foreach from=$cart_summary.products item=product name=cart_products}
			<div id="kco_product_row_{$product.id_product}_{$product.id_product_attribute}" class="itemrow {if $smarty.foreach.cart_products.index % 2}odd{else}even{/if}">
				<span class="klarna_img"><img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'small_default')}" height="36" width="32"></span>
				<span id="klarna_quantity_{$product.id_product}_{$product.id_product_attribute}" class="klarna_quantity">{$product.cart_quantity} x </span>
				<span class="klarna_name">{$product.name|truncate:15:'...'} </span>
				<span id="klarna_price_{$product.id_product}_{$product.id_product_attribute}" class="klarna_price">{if !$priceDisplay}{displayPrice price=$product.total_wt}{else}{displayPrice price=$product.total}{/if}</span>
			</div>
			{/foreach}
             {foreach $cart_summary.gift_products as $product}
			<div id="kco_product_row_{$product.id_product}_{$product.id_product_attribute}" class="itemrow {if $smarty.foreach.cart_products.index % 2}odd{else}even{/if}">
				<span class="klarna_img"><img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'small_default')}" height="36" width="32"></span>
				<span id="klarna_quantity_{$product.id_product}_{$product.id_product_attribute}" class="klarna_quantity">{$product.cart_quantity} x </span>
				<span class="klarna_name">{$product.name|truncate:15:'...'} </span>
				<span id="klarna_price_{$product.id_product}_{$product.id_product_attribute}" class="klarna_price">{l s='Free!' mod='klarnacheckout'}</span>
			</div>
			{/foreach}
			
			<div class="klarna_separator"></div>
			
			<div class="kcoi">
			{if $use_taxes}
				{if $priceDisplay}
					<span class="klarna_totalproducts">{if $display_tax_label}{l s='Total products (tax excl.):' mod='klarnacheckout'}{else}{l s='Total products:' mod='klarnacheckout'}{/if}</span>
					<span id="kco_total_products" class="klarna_totalproducts_price kcori">{displayPrice price=$cart_summary.total_products}</span>
				{else}
					<span class="klarna_totalproducts">{if $display_tax_label}{l s='Total products (tax incl.):' mod='klarnacheckout'}{else}{l s='Total products:' mod='klarnacheckout'}{/if}</span>
					<span id="kco_total_products" class="klarna_totalproducts_price kcori">{displayPrice price=$cart_summary.total_products_wt}</span>
				{/if}
			{else}
				<span class="klarna_totalproducts">{l s='Total products:' mod='klarnacheckout'}</span>
				<span id="kco_total_products" class="klarna_totalproducts_price kcori">{displayPrice price=$cart_summary.total_products}</span>
			{/if}
			<div class="klarna_cart_total_voucher" {if $cart_summary.total_discounts == 0}style="display:none"{/if}>
				<span class="klarna_totalvouchers">
					{if $use_taxes && $display_tax_label}
						{l s='Total vouchers (tax excl.):' mod='klarnacheckout'}
					{else}
						{l s='Total vouchers:' mod='klarnacheckout'}
					{/if}
				</span>
				<span class="klarna_totalvouchers_price kcori">
					{if $use_taxes && !$priceDisplay}
						{assign var='total_discounts_negative' value=$cart_summary.total_discounts * -1}
					{else}
						{assign var='total_discounts_negative' value=$cart_summary.total_discounts_tax_exc * -1}
					{/if}
					{displayPrice price=$total_discounts_negative}
				</span>
			</div>
			<div class="klarna_cart_total_voucher" {if $cart_summary.total_wrapping == 0}style="display: none;"{/if}>
				<span class="klarna_totalgiftwrapping">
					{if $use_taxes}
						{if $display_tax_label}{l s='Total gift-wrapping (tax incl.):' mod='klarnacheckout'}{else}{l s='Total gift-wrapping:' mod='klarnacheckout'}{/if}
					{else}
						{l s='Total gift-wrapping:' mod='klarnacheckout'}
					{/if}
				</span>
				<span class="klarna_totalgiftwrapping_price kcori">
					{if $use_taxes}
						{if $priceDisplay}
							{displayPrice price=$cart_summary.total_wrapping_tax_exc}
						{else}
							{displayPrice price=$cart_summary.total_wrapping}
						{/if}
					{else}
						{displayPrice price=$cart_summary.total_wrapping_tax_exc}
					{/if}
				</span>
			</div>				
			{if $cart_summary.total_shipping_tax_exc <= 0 && !isset($cart_summary.isVirtualCart)}
				<div id="klarna_cart_total_delivery">
					<span class="klarna_shipping">{l s='Shipping:' mod='klarnacheckout'}</span>
					<span id="klarna_shipping_price" class="klarna_shipping_price kcori">{l s='Free Shipping!' mod='klarnacheckout'}</span>
				</div>
			{else}
				{if $use_taxes}
					{if $priceDisplay}
						<div id="klarna_cart_total_delivery" {if $cart_summary.total_shipping_tax_exc <= 0} style="display:none;"{/if}>
							<span class="klarna_shipping">{if $display_tax_label}{l s='Total shipping (tax excl.):' mod='klarnacheckout'}{else}{l s='Total shipping:' mod='klarnacheckout'}{/if}</span>
							<span id="klarna_shipping_price" class="klarna_shipping_price kcori">{displayPrice price=$cart_summary.total_shipping_tax_exc}</span>
						</div>
					{else}
						<div id="klarna_cart_total_delivery"{if $cart_summary.total_shipping <= 0} style="display:none;"{/if}>
							<span class="klarna_shipping">{if $display_tax_label}{l s='Total shipping (tax incl.):' mod='klarnacheckout'}{else}{l s='Total shipping:' mod='klarnacheckout'}{/if}</span>
							<span id="klarna_shipping_price" class="klarna_shipping_price kcori">{displayPrice price=$cart_summary.total_shipping}</span>
						</div>
					{/if}
				{else}
					<div id="klarna_cart_total_delivery"{if $cart_summary.total_shipping_tax_exc <= 0} style="display:none;"{/if}>
						<span class="klarna_shipping">{l s='Total shipping:' mod='klarnacheckout'}</span>
						<span id="klarna_shipping_price" class="klarna_shipping_price kcori">{displayPrice price=$cart_summary.total_shipping_tax_exc}</span>
					</div>
				{/if}
			{/if}	
			{if $use_taxes}
				<div id="klarna_cart_total_price">
					<span class="klarna_total">{l s='Total (tax excl.):' mod='klarnacheckout'}</span>
					<span class="klarna_total_price kcori" id="kco_total_price_without_tax">{displayPrice price=$cart_summary.total_price_without_tax}</span>
				</div>
				<div class="klarna_cart_total_tax">
					<span class="klarna_tax">{l s='Total tax:' mod='klarnacheckout'}</span>
					<span class="klarna_tax_price kcori" class="price" id="kco_total_tax">{displayPrice price=$cart_summary.total_tax}</span>
				</div>
			{/if}
			{if $use_taxes}
				<div id="klarna_cart_total_price_incl_tax">
					<span class="klarna_cart_total_price_incl_tax">{l s='Total:' mod='klarnacheckout'}</span>
					<span id="kco_total_price" class="klarna_cart_total_price_incl_tax_price kcori">{displayPrice price=$cart_summary.total_price}</span>
				</div>
			{else}
				<div id="klarna_cart_total_price_incl_tax">
					<span class="klarna_cart_total_price_incl_tax">{l s='Total:' mod='klarnacheckout'}</span>
					<span id="kco_total_price" class="klarna_cart_total_price_incl_tax_price kcori">{displayPrice price=$cart_summary.total_price_without_tax}</span>
				</div>
			{/if}
			</div>
		</div>
		
		{if isset($left_to_get_free_shipping) AND $left_to_get_free_shipping>0}<p>{l s='By shopping for' mod='klarnacheckout'}&nbsp;{convertPrice price=$left_to_get_free_shipping}&nbsp;{l s='more, you will qualify for free shipping.' mod='klarnacheckout'}</p>{/if}
		
		{if isset($delivery_option_list)}
			<form action="{$posturl}" method="post" id="klarnacarrier">
				<h4><label for="select_carrier">{l s='Carrier' mod='klarnacheckout'}</label></h4>
				{foreach $delivery_option_list as $id_address => $option_list}
					<div class="klarna_delivery_options">
						{foreach $option_list as $key => $option}
							<div class="klarna_delivery_option {if ($option@index % 2)}alternate_{/if}item">
								{foreach $option.carrier_list as $carrier}
										<label for="delivery_option_{$id_address}_{$option@index}">
											<img src="{if isset($carrier.logo) and $carrier.logo!=''}{$carrier.logo}{else}{$base_dir}modules/klarnacheckout/img/carrier_default.png{/if}" alt="{$carrier.instance->name}" width="100" height="55" class="klarna_carrier_img"/>
										</label>
								{/foreach}
								{if count($option_list) > 1}
									{if $option.is_best_grade}
										{if $option.is_best_price}
											<div class="klarna_delivery_option_best delivery_option_icon">
												{l s='The best price and speed' mod='klarnacheckout'}
											</div>
										{else}
											<div class="klarna_delivery_option_fast delivery_option_icon">
												{l s='The fastest' mod='klarnacheckout'}
											</div>
										{/if}
									{else}
										{if $option.is_best_price}
											<div class="klarna_delivery_option_best_price delivery_option_icon">
												{l s='The best price' mod='klarnacheckout'}
											</div>
										{/if}
									{/if}
								{/if}
								<div class="carrier_info">
									<input class="klarna_delivery_option_radio" type="radio" name="delivery_option[{$id_address}]" onchange="$('#klarnacarrier').submit()" id="delivery_option_{$id_address}_{$option@index}" value="{$key}" {if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key}checked="checked"{/if} />
									{if $option.unique_carrier}
										{foreach $option.carrier_list as $carrier}
											<div class="klarna_delivery_option_title">
												<label for="delivery_option_{$id_address}_{$option@index}">
													{$carrier.instance->name}
												</label>
											</div>
										{/foreach}
										{if isset($carrier.instance->delay[$cookie->id_lang])}
											<div class="klarna_delivery_option_delay" style="display:none;">
												{$carrier.instance->delay[$cookie->id_lang]}
											</div>
										{/if}
									{/if}
									<div class="klarna_delivery_option_price">
										{if $option.total_price_with_tax && !$free_shipping}
											{if $use_taxes == 1}
												{convertPrice price=$option.total_price_with_tax}
											{else}
												{convertPrice price=$option.total_price_without_tax}
											{/if}
										{else}
											{l s='Free!' mod='klarnacheckout'}
										{/if}
									</div>	
									<div class="clear"></div>
								</div>
								<table class="delivery_option_carrier {if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key}selected{/if} {if $option.unique_carrier}not-displayable{/if}">
									{foreach $option.carrier_list as $carrier}
										<tr>
											{if !$option.unique_carrier}
												<td class="first_item">
													<input type="hidden" value="{$carrier.instance->id}" name="id_carrier" />
													{if $carrier.logo}
														<img src="{$carrier.logo}" alt="{$carrier.instance->name}"/>
													{/if}
												</td>
												<td>
													{$carrier.instance->name}
												</td>
											{/if}
										</tr>
									{/foreach}
								</table>		
							</div>
						{/foreach}
						<div class="clear"></div>
					</div>
				{/foreach}
			</form>
		{/if}

		<form action="{$posturl}" method="post" id="klarnavoucher">
			<fieldset>
				<h4><label for="discount_name">{l s='Vouchers' mod='klarnacheckout'}</label></h4>
				<p>
					<input type="text" class="discount_name" id="discount_name" name="discount_name" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
				</p>
				<p class="submit">
					<input type="hidden" name="submitDiscount" /><input type="submit" id="submitAddDiscount" name="submitAddDiscount" value="{l s='OK' mod='klarnacheckout'}" class="button" />
				</p>
				<br />
				<div class="clear">
					{if sizeof($discounts)}
						<ul id="klarnacheckoutvouchers">
							{foreach $discounts as $discount}
								<li>
									{$discount.name} ({if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if})
									{if strlen($discount.code)}<a href="{$posturl}?deleteDiscount={$discount.id_discount}" class="price_discount_delete" title="{l s='Delete'}">{l s='Delete' mod='klarnacheckout'}</a>{/if}
								</li>
							{/foreach}
						</ul>
					{/if}
				</div>
			</fieldset>
		</form>
		
		
		<!-- message -->
			<div class="message_container">
			<form action="{$posturl}" method="post" id="klarnamessage">
					<h4 id="messageh4klarna"><label id="messagelabel" class="down" for="message">{l s='Message' mod='klarnacheckout'}</label></h4>
					<p id="messagearea">
						<textarea id="message" name="message">{$message.message}</textarea>
						<input type="submit" name="savemessagebutton" class="button" id="savemessagebutton" value="{l s='Save' mod='klarnacheckout'}" />
					</p>
			</form>
			</div>
			
			{literal}
			<script type="text/javascript">
			$(document).ready(function(){ 
			  $("#messageh4klarna").toggle(function() { 
								  $("#messagearea").fadeIn("slow");
								  $("#messagelabel").removeClass("down");
								  $("#messagelabel").addClass("up");
								  },
								   function() { 
								 $("#messagearea").fadeOut("slow");
								  $("#messagelabel").removeClass("up");
								  $("#messagelabel").addClass("down");
								});
			   });
			</script>
			{/literal}
			<!-- message -->
		
		{if $giftAllowed==1}
			<!-- gift wrapping -->
			<div class="gift_container">
			<form action="{$posturl}" method="post" id="klarnagift">
					<h4 id="giftwrappingh4klarna"><label id="giftwrappinglabelklarna" class="down" for="giftwrapping">{l s='Giftwrapping' mod='klarnacheckout'}</label></h4>
					<p id="giftmessagearea">
						<input type="checkbox" onchange="$('#klarnagift').submit();" class="giftwrapping_radio" id="gift" name="gift" value="1"{if isset($gift) AND $gift==1} checked="checked"{/if} />
						<span id="giftwrappingextracost">{l s='Additional cost:'}{displayPrice price=$gift_wrapping_price}</span>
						<textarea id="gift_message" name="gift_message">{$gift_message}</textarea>
						<input type="hidden" name="savegift" id="savegift" value="1" />
						<input type="submit" name="savegiftbutton" class="button" id="savegiftbutton" value="{l s='Save' mod='klarnacheckout'}" />
					</p>
			</form>
			</div>
			
			{literal}
			<script type="text/javascript">
			$(document).ready(function(){ 
			  $("#giftwrappingh4klarna").toggle(function() { 
								  $("#giftmessagearea").fadeIn("slow");
								  $("#giftwrappinglabelklarna").removeClass("down");
								  $("#giftwrappinglabelklarna").addClass("up");
								  },
								   function() { 
								 $("#giftmessagearea").fadeOut("slow");
								  $("#giftwrappinglabelklarna").removeClass("up");
								  $("#giftwrappinglabelklarna").addClass("down");
								});
			   });
			</script>
			{/literal}
			<!-- gift wrapping -->
			{/if}
			
	<div id="HOOK_SHOPPING_CART">{$HOOK_SHOPPING_CART}</div>

	{if !empty($HOOK_SHOPPING_CART_EXTRA)}
		<div class="clear"></div>
		<div class="cart_navigation_extra">
			<div id="HOOK_SHOPPING_CART_EXTRA">{$HOOK_SHOPPING_CART_EXTRA}</div>
		</div>
	{/if}
	</div>
	<div class="floatleft" id="checkoutdiv">
		{$klarna_checkout}
	</div>
{/if}