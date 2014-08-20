{if isset($klarna_error)}
	<div class="warning">{$klarna_error}</div>
{else}
	{if isset($vouchererrors) && $vouchererrors!=''}
		<div class="warning">{$vouchererrors}</div>
	{/if}
	
<script type="text/javascript">
// <![CDATA[
var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
var currencyRate = '{$currencyRate|floatval}';
var currencyFormat = '{$currencyFormat|intval}';
var currencyBlank = '{$currencyBlank|intval}';
var txtProduct = "{l s='product' js=1}";
var txtProducts = "{l s='products' js=1}";
var freeShippingTranslation = "{l s='Free Shipping!' js=1}";
var kcourl = "{$kcourl}";
// ]]>
</script>
	<div id="height_kco_div">
	<div id="kco_cart_summary_div">
		{include file="./shopping-cart-height.tpl"}
	</div>
	
	{if $no_active_countries > 1}
	<form action="{$posturl}" method="post" id="kco_change_country">
	<span class="cstep">{l s='Country' mod='klarnacheckout'}</span><h2>
		<select name="kco_change_country" onchange="$('#kco_change_country').submit();">
			{if $show_sweden}<option value="sv"{if $kco_selected_country=='SE'} selected="selected"{/if}>{l s='Sweden' mod='klarnacheckout'}</option>{/if}
			{if $show_norway}<option value="no"{if $kco_selected_country=='NO'} selected="selected"{/if}>{l s='Norway' mod='klarnacheckout'}</option>{/if}
			{if $show_finland}<option value="fi"{if $kco_selected_country=='FI'} selected="selected"{/if}>{l s='Finland' mod='klarnacheckout'}</option>{/if}
		</select>
	</h2>
	</form>
	{/if}
	
		{if isset($left_to_get_free_shipping) AND $left_to_get_free_shipping>0}<p>{l s='By shopping for' mod='klarnacheckout'}&nbsp;{convertPrice price=$left_to_get_free_shipping}&nbsp;{l s='more, you will qualify for free shipping.' mod='klarnacheckout'}</p>{/if}
		{if isset($delivery_option_list)}
			<form action="{$posturl}" method="post" id="klarnacarrier">
				<span class="cstep">{l s='Step 1' mod='klarnacheckout'}</span>
				<h2>{l s='Carrier' mod='klarnacheckout'}</h2>
				<table>
				{foreach $delivery_option_list as $id_address => $option_list}
						{foreach $option_list as $key => $option}
						<tr class="klarna_delivery_options">
								<td class="carrierradiokco">
									<input class="klarna_delivery_option_radio" type="radio" name="delivery_option[{$id_address}]" onchange="$('#klarnacarrier').submit()" id="delivery_option_{$id_address}_{$option@index}" value="{$key}" {if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key}checked="checked"{/if} />
								</td>
								<td class="carrierimagekco">
									{foreach $option.carrier_list as $carrier}
											<label for="delivery_option_{$id_address}_{$option@index}">
												<img src="{if isset($carrier.logo) and $carrier.logo!=''}{$carrier.logo}{else}{$base_dir}modules/klarnacheckout/img/carrier_default.png{/if}" alt="{$carrier.instance->name}" width="100" height="55" class="klarna_carrier_img"/>
											</label>
									{/foreach}
								</td>
								<td class="carrierpricekco">
									{if $option.total_price_with_tax && !$free_shipping}
										{if $use_taxes == 1}
											{convertPrice price=$option.total_price_with_tax}
										{else}
											{convertPrice price=$option.total_price_without_tax}
										{/if}
									{else}
										{l s='Free!' mod='klarnacheckout'}
									{/if}
								</td>
								<td class="carriertextkco">
								{if $option.unique_carrier}
									{foreach $option.carrier_list as $carrier}
											<label for="delivery_option_{$id_address}_{$option@index}">
												{$carrier.instance->name}
											</label>
									{/foreach}
									{if isset($carrier.instance->delay[$cookie->id_lang])}
										<div class="klarna_delivery_option_delay" style="display:none;">
											{$carrier.instance->delay[$cookie->id_lang]}
										</div>
									{/if}
								{/if}
								
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
								</td>
							</tr>
						{/foreach}
				{/foreach}
				</table>
			</form>
		{/if}

		
		
		
		<!-- message -->
			<form action="{$posturl}" method="post" id="klarnamessage">
					<span class="cstep">{l s='Step 2' mod='klarnacheckout'}</span>
					<h2>{l s='Message' mod='klarnacheckout'}</h2>
					<p id="messagearea">
						<textarea id="message" name="message" cols="192" class="kcotwidth" rows="6">{$message.message}</textarea>
						<input type="submit" name="savemessagebutton" class="button" id="savemessagebutton" value="{l s='Save' mod='klarnacheckout'}" />
					</p>
			</form>
			<!-- message -->
		
		{if $giftAllowed==1}
			<!-- gift wrapping -->
			<form action="{$posturl}" method="post" id="klarnagift">
					<span class="cstep">{l s='Step 3' mod='klarnacheckout'}</span>
					<h2>{l s='Giftwrapping' mod='klarnacheckout'}</h2>
					<p id="giftmessagearea_long">
						<input type="checkbox" onchange="$('#klarnagift').submit();" class="giftwrapping_radio" id="gift" name="gift" value="1"{if isset($gift) AND $gift==1} checked="checked"{/if} />
						<span id="giftwrappingextracost">{l s='Additional cost:' mod='klarnacheckout'}{displayPrice price=$gift_wrapping_price}</span>
						<textarea id="gift_message" name="gift_message" rows="6" cols="192" class="kcotwidth">{$gift_message}</textarea>
						<input type="hidden" name="savegift" id="savegift" value="1" />
						<input type="submit" name="savegiftbutton" class="button" id="savegiftbutton" value="{l s='Save' mod='klarnacheckout'}" />
					</p>
			</form>
			<!-- gift wrapping -->
			{/if}
			
	<div id="HOOK_SHOPPING_CART">{$HOOK_SHOPPING_CART}</div>

	{if !empty($HOOK_SHOPPING_CART_EXTRA)}
		<div class="clear"></div>
		<div class="cart_navigation_extra">
			<div id="HOOK_SHOPPING_CART_EXTRA">{$HOOK_SHOPPING_CART_EXTRA}</div>
		</div>
	{/if}

	{if $giftAllowed==1}<span class="cstep">{l s='Step 4' mod='klarnacheckout'}</span><h2>{l s='Pay for your order' mod='klarnacheckout'}</h2>{else}<span class="cstep">{l s='Step 3' mod='klarnacheckout'}</span><h2>{l s='Pay for your order' mod='klarnacheckout'}</h2>{/if}
	<div class="floatleft" id="checkoutdiv">
		{$klarna_checkout}
	</div>
	</div>
{/if}