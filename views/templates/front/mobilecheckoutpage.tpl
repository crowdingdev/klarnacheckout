<script type="text/javascript">
	$.mobile.ajaxEnabled = false;
</script>
<div data-role="content" id="kco_mobile">
<h4><label for="summary">{l s='Summary' mod='klarnacheckout'}</label></h4>
<div class="sum_container">
	{foreach from=$cart_summary.products item=product name=cart_products}
		<span class="klarna_quantity">{$product.cart_quantity} x </span><span class="klarna_name">{$product.name} </span><span class="klarna_price">{if !$priceDisplay}{displayPrice price=$product.price_wt}{else}{displayPrice price=$product.price}{/if}</span><br />
	{/foreach}
	<div class="klarna_separator"></div>
	{if $use_taxes}
		{if $priceDisplay}
			<span class="klarna_totalproducts">{if $display_tax_label}{l s='Total products (tax excl.):' mod='klarnacheckout'}{else}{l s='Total products:' mod='klarnacheckout'}{/if}</span>
			<span class="klarna_totalproducts_price">{displayPrice price=$cart_summary.total_products}</span>
		{else}
			<span class="klarna_totalproducts">{if $display_tax_label}{l s='Total products (tax incl.):' mod='klarnacheckout'}{else}{l s='Total products:' mod='klarnacheckout'}{/if}</span>
			<span class="klarna_totalproducts_price">{displayPrice price=$cart_summary.total_products_wt}</span>
		{/if}
	{else}
		<span class="klarna_totalproducts">{l s='Total products:' mod='klarnacheckout'}</span><span class="klarna_totalproducts_price">{displayPrice price=$cart_summary.total_products}</span>
	{/if}
	<div class="klarna_cart_total_voucher" {if $cart_summary.total_discounts == 0}style="display:none"{/if}>
		<span class="klarna_totalvouchers">
			{if $use_taxes && $display_tax_label}
				{l s='Total vouchers (tax excl.):' mod='klarnacheckout'}
			{else}
				{l s='Total vouchers:' mod='klarnacheckout'}
			{/if}
		</span>
		<span class="klarna_totalvouchers_price">
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
		<span class="klarna_totalgiftwrapping_price">
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
			<span class="klarna_shipping_price">{l s='Free Shipping!' mod='klarnacheckout'}</span>
		</div>
	{else}
		{if $use_taxes}
			{if $priceDisplay}
				<div id="klarna_cart_total_delivery" {if $cart_summary.total_shipping_tax_exc <= 0} style="display:none;"{/if}>
					<span class="klarna_shipping">{if $display_tax_label}{l s='Total shipping (tax excl.):' mod='klarnacheckout'}{else}{l s='Total shipping:' mod='klarnacheckout'}{/if}</span>
					<span class="klarna_shipping_price">{displayPrice price=$cart_summary.total_shipping_tax_exc}</span>
				</div>
			{else}
				<div id="klarna_cart_total_delivery"{if $cart_summary.total_shipping <= 0} style="display:none;"{/if}>
					<span class="klarna_shipping">{if $display_tax_label}{l s='Total shipping (tax incl.):' mod='klarnacheckout'}{else}{l s='Total shipping:' mod='klarnacheckout'}{/if}</span>
					<span class="klarna_shipping_price">{displayPrice price=$cart_summary.total_shipping}</span>
				</div>
			{/if}
		{else}
			<div id="klarna_cart_total_delivery"{if $cart_summary.total_shipping_tax_exc <= 0} style="display:none;"{/if}>
				<span class="klarna_shipping">{l s='Total shipping:' mod='klarnacheckout'}</span>
				<span class="klarna_shipping_price">{displayPrice price=$cart_summary.total_shipping_tax_exc}</span>
			</div>
		{/if}
	{/if}	
	{if $use_taxes}
		<div id="klarna_cart_total_price">
			<span class="klarna_total">{l s='Total (tax excl.):' mod='klarnacheckout'}</span>
			<span class="klarna_total_price" id="total_price_without_tax">{displayPrice price=$cart_summary.total_price_without_tax}</span>
		</div>
		<div class="klarna_cart_total_tax">
			<span class="klarna_tax">{l s='Total tax:' mod='klarnacheckout'}</span>
			<span class="klarna_tax_price" class="price" id="total_tax">{displayPrice price=$cart_summary.total_tax}</span>
		</div>
	{/if}
	{if $use_taxes}
		<div id="klarna_cart_total_price_incl_tax">
			<span class="klarna_cart_total_price_incl_tax">{l s='Total:' mod='klarnacheckout'}</span>
			<span class="klarna_cart_total_price_incl_tax_price">{displayPrice price=$cart_summary.total_price}</span>
		</div>
	{else}
		<div id="klarna_cart_total_price_incl_tax">
			<span class="klarna_cart_total_price_incl_tax">{l s='Total:' mod='klarnacheckout'}</span>
			<span class="klarna_cart_total_price_incl_tax_price">{displayPrice price=$cart_summary.total_price_without_tax}</span>
		</div>
	{/if}
</div>

{if isset($delivery_option_list)}
	{foreach $delivery_option_list as $id_address => $option_list}
		{foreach $option_list as $key => $option}
			{if $option.unique_carrier}
				{foreach $option.carrier_list as $carrier}
					<form action="{$posturl}" method="post" id="klarnacarrier">
						<button {if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key}style="background:#1BE028;"{/if} type="submit" id="submit_delivery_option_{$id_address}_{$option@index}" name="submit_delivery_option_{$id_address}_{$option@index}" class="ui-btn-hidden submit_button">
							{$carrier.instance->name} ({if $option.total_price_with_tax && !$free_shipping}
							{if $use_taxes == 1}
								{convertPrice price=$option.total_price_with_tax}
							{else}
								{convertPrice price=$option.total_price_without_tax}
							{/if}
							{else}
								{l s='Free!' mod='klarnacheckout'}
							{/if})
							
						</button>
						<input type="hidden" class="hidden" name="delivery_option[{$id_address}]" value="{$key}" />
					</form>
				{/foreach}
			{/if}
		{/foreach}
	{/foreach}
{/if}

{$klarna_checkout}

</div>