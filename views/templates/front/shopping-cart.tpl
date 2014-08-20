<h1 id="cart_title">{l s='Shopping cart summary' mod='klarnacheckout'}</h1>
<div id="order-detail-content" class="table_block">
	<table id="cart_summary" class="std">
		<thead>
			<tr>
				<th class="cart_product first_item">{l s='Product' mod='klarnacheckout'}</th>
				<th class="cart_description item">{l s='Description' mod='klarnacheckout'}</th>
				<th class="cart_ref item">{l s='Ref.' mod='klarnacheckout'}</th>
				<th class="cart_unit item">{l s='Unit price' mod='klarnacheckout'}</th>
				<th class="cart_quantity item">{l s='Qty' mod='klarnacheckout'}</th>
				<th class="cart_total item">{l s='Total' mod='klarnacheckout'}</th>
				<th class="cart_delete last_item">&nbsp;</th>
			</tr>
		</thead>
		<tfoot>
		{if $use_taxes}
			{if $priceDisplay}
				<tr class="cart_total_price">
					<td colspan="5">{if $display_tax_label}{l s='Total products (tax excl.):' mod='klarnacheckout'}{else}{l s='Total products:' mod='klarnacheckout'}{/if}</td>
					<td colspan="2" class="price" id="total_product">{displayPrice price=$cart_summary.total_products}</td>
				</tr>
			{else}
				<tr class="cart_total_price">
					<td colspan="5">{if $display_tax_label}{l s='Total products (tax incl.):' mod='klarnacheckout'}{else}{l s='Total products:' mod='klarnacheckout'}{/if}</td>
					<td colspan="2" class="price" id="total_product">{displayPrice price=$cart_summary.total_products_wt}</td>
				</tr>
			{/if}
		{else}
			<tr class="cart_total_price">
				<td colspan="5">{l s='Total products:' mod='klarnacheckout'}</td>
				<td colspan="2" class="price" id="total_product">{displayPrice price=$total_products}</td>
			</tr>
		{/if}
			<tr class="cart_total_voucher" {if $cart_summary.total_discounts == 0}style="display:none"{/if}>
				<td colspan="5">
				{if $use_taxes && $display_tax_label}
					{l s='Total vouchers (tax excl.):' mod='klarnacheckout'}
				{else}
					{l s='Total vouchers:' mod='klarnacheckout'}
				{/if}
				</td>
				<td colspan="2" class="price-discount price" id="total_discount">
				{if $use_taxes && !$priceDisplay}
					{assign var='total_discounts_negative' value=$cart_summary.total_discounts * -1}
				{else}
					{assign var='total_discounts_negative' value=$cart_summary.total_discounts_tax_exc * -1}
				{/if}
				{displayPrice price=$total_discounts_negative}
				</td>
			</tr>
			<tr class="cart_total_voucher" {if $cart_summary.total_wrapping == 0}style="display: none;"{/if}>
				<td colspan="5">
				{if $use_taxes}
					{if $display_tax_label}{l s='Total gift-wrapping (tax incl.):' mod='klarnacheckout'}{else}{l s='Total gift-wrapping:' mod='klarnacheckout'}{/if}
				{else}
					{l s='Total gift-wrapping:' mod='klarnacheckout'}
				{/if}
				</td>
				<td colspan="2" class="price-discount price" id="total_wrapping">
				{if $use_taxes}
					{if $priceDisplay}
						{displayPrice price=$cart_summary.total_wrapping_tax_exc}
					{else}
						{displayPrice price=$cart_summary.total_wrapping}
					{/if}
				{else}
					{displayPrice price=$cart_summary.total_wrapping_tax_exc}
				{/if}
				</td>
			</tr>
			{if $cart_summary.total_shipping_tax_exc <= 0 && !isset($cart_summary.virtualCart)}
				<tr class="cart_total_delivery">
					<td colspan="5">{l s='Shipping:' mod='klarnacheckout'}</td>
					<td colspan="2" class="price" id="total_shipping">{l s='Free Shipping!' mod='klarnacheckout'}</td>
				</tr>
			{else}
				{if $use_taxes}
					{if $priceDisplay}
						<tr class="cart_total_delivery" {if $cart_summary.total_shipping_tax_exc <= 0} style="display:none;"{/if}>
							<td colspan="5">{if $display_tax_label}{l s='Total shipping (tax excl.):' mod='klarnacheckout'}{else}{l s='Total shipping:' mod='klarnacheckout'}{/if}</td>
							<td colspan="2" class="price" id="total_shipping">{displayPrice price=$cart_summary.total_shipping_tax_exc}</td>
						</tr>
					{else}
						<tr class="cart_total_delivery"{if $cart_summary.total_shipping <= 0} style="display:none;"{/if}>
							<td colspan="5">{if $display_tax_label}{l s='Total shipping (tax incl.):' mod='klarnacheckout'}{else}{l s='Total shipping:' mod='klarnacheckout'}{/if}</td>
							<td colspan="2" class="price" id="total_shipping" >{displayPrice price=$cart_summary.total_shipping}</td>
						</tr>
					{/if}
				{else}
					<tr class="cart_total_delivery"{if $cart_summary.total_shipping_tax_exc <= 0} style="display:none;"{/if}>
						<td colspan="5">{l s='Total shipping:' mod='klarnacheckout'}</td>
						<td colspan="2" class="price" id="total_shipping" >{displayPrice price=$cart_summary.total_shipping_tax_exc}</td>
					</tr>
				{/if}
			{/if}
			{if $use_taxes}
			<tr class="cart_total_price">
				<td colspan="5">{l s='Total (tax excl.):' mod='klarnacheckout'}</td>
				<td colspan="2" class="price" id="total_price_without_tax">{displayPrice price=$cart_summary.total_price_without_tax}</td>
			</tr>
			<tr class="cart_total_tax">
				<td colspan="5">{l s='Total tax:' mod='klarnacheckout'}</td>
				<td colspan="2" class="price" id="total_tax">{displayPrice price=$cart_summary.total_tax}</td>
			</tr>
			{/if}
			<tr class="cart_total_price">
				<td colspan="5" id="cart_voucher" class="cart_voucher">
				{if $voucherAllowed}
					{if isset($errors_discount) && $errors_discount}
						<ul class="error">
						{foreach $errors_discount as $k=>$error}
							<li>{$error|escape:'htmlall':'UTF-8'}</li>
						{/foreach}
						</ul>
					{/if}
					<form action="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}" method="post" id="voucher">
						<fieldset>
							<p class="title_block"><label for="discount_name">{l s='Vouchers' mod='klarnacheckout'}</label></p>
							<p>
								<input type="text" class="discount_name" id="discount_name" name="discount_name" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
							</p>
							<p class="submit"><input type="hidden" name="submitDiscount" /><input type="submit" name="submitAddDiscount" value="{l s='OK' mod='klarnacheckout'}" class="button" /></p>
						</fieldset>
					</form>
				{/if}
				</td>
				{if $use_taxes}
				<td colspan="2" class="price total_price_container" id="total_price_container">
					<p>{l s='Total:' mod='klarnacheckout'}</p>
					<span id="total_price">{displayPrice price=$cart_summary.total_price}</span>
				</td>
				{else}
				<td colspan="2" class="price total_price_container" id="total_price_container">
					<p>{l s='Total:' mod='klarnacheckout'}</p>
					<span id="total_price">{displayPrice price=$cart_summary.total_price_without_tax}</span>
				</td>
				{/if}
			</tr>
		</tfoot>
		<tbody>
		{foreach $cart_summary.products as $product}
			{assign var='productId' value=$product.id_product}
			{assign var='productAttributeId' value=$product.id_product_attribute}
			{assign var='quantityDisplayed' value=0}
			{assign var='odd' value=$product@iteration%2}
			{assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId) || count($cart_summary.gift_products)}
			{* Display the product line *}
			{include file="./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}
			{* Then the customized datas ones*}
			{if isset($customizedDatas.$productId.$productAttributeId)}
				{foreach $customizedDatas.$productId.$productAttributeId[$product.id_address_delivery] as $id_customization=>$customization}
					<tr id="product_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}" class="product_customization_for_{$product.id_product}_{$product.id_product_attribute}_{$product.id_address_delivery|intval} {if $odd}odd{else}even{/if} customization alternate_item {if $product@last && $customization@last && !count($cart_summary.gift_products)}last_item{/if}">
						<td></td>
						<td colspan="3">
							{foreach $customization.datas as $type => $custom_data}
								{if $type == $CUSTOMIZE_FILE}
									<div class="customizationUploaded">
										<ul class="customizationUploaded">
											{foreach $custom_data as $picture}
												<li><img src="{$pic_dir}{$picture.value}_small" alt="" class="customizationUploaded" /></li>
											{/foreach}
										</ul>
									</div>
								{elseif $type == $CUSTOMIZE_TEXTFIELD}
									<ul class="typedText">
										{foreach $custom_data as $textField}
											<li>
												{if $textField.name}
													{$textField.name}
												{else}
													{l s='Text #' mod='klarnacheckout'}{$textField@index+1}
												{/if}
												{l s=':' mod='klarnacheckout'} {$textField.value}
											</li>
										{/foreach}
										
									</ul>
								{/if}

							{/foreach}
						</td>
						<td class="cart_quantity" colspan="2">
							{if isset($cannotModify) AND $cannotModify == 1}
								<span style="float:left">{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}{else}{$product.cart_quantity-$quantityDisplayed}{/if}</span>
							{else}
								<div class="cart_quantity_button">
								<a rel="nofollow" class="cart_quantity_up" id="cart_quantity_up_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}" href="{$link->getPageLink('cart', true, NULL, "add&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_address_delivery={$product.id_address_delivery}&amp;id_customization={$id_customization}&amp;token={$token_cart}")}" title="{l s='Add' mod='klarnacheckout'}"><img src="{$img_dir}icon/quantity_up.gif" alt="{l s='Add' mod='klarnacheckout'}" width="14" height="9" /></a><br />
								{if $product.minimal_quantity < ($customization.quantity -$quantityDisplayed) OR $product.minimal_quantity <= 1}
								<a rel="nofollow" class="cart_quantity_down" id="cart_quantity_down_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}" href="{$link->getPageLink('cart', true, NULL, "add&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_address_delivery={$product.id_address_delivery}&amp;id_customization={$id_customization}&amp;op=down&amp;token={$token_cart}")}" title="{l s='Subtract' mod='klarnacheckout'}">
									<img src="{$img_dir}icon/quantity_down.gif" alt="{l s='Subtract' mod='klarnacheckout'}" width="14" height="9" />
								</a>
								{else}
								<a class="cart_quantity_down" style="opacity: 0.3;" id="cart_quantity_down_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}" href="#" title="{l s='Subtract' mod='klarnacheckout'}">
									<img src="{$img_dir}icon/quantity_down.gif" alt="{l s='Subtract' mod='klarnacheckout'}" width="14" height="9" />
								</a>
								{/if}
								</div>
								<input type="hidden" value="{$customization.quantity}" name="quantity_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}_hidden"/>
								<input size="2" type="text" value="{$customization.quantity}" class="cart_quantity_input" name="quantity_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}"/>
							{/if}
						</td>
						<td class="cart_delete">
							{if isset($cannotModify) AND $cannotModify == 1}
							{else}
								<div>
									<a rel="nofollow" class="cart_quantity_delete" id="{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}" href="{$link->getPageLink('cart', true, NULL, "delete&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;id_address_delivery={$product.id_address_delivery}&amp;token={$token_cart}")}">{l s='Delete' mod='klarnacheckout'}</a>
								</div>
							{/if}
						</td>
					</tr>
					{assign var='quantityDisplayed' value=$quantityDisplayed+$customization.quantity}
				{/foreach}
				{* If it exists also some uncustomized products *}
				{if $product.quantity-$quantityDisplayed > 0}{include file="./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}{/if}
			{/if}
		{/foreach}
		{assign var='last_was_odd' value=$product@iteration%2}
		{foreach $cart_summary.gift_products as $product}
			{assign var='productId' value=$product.id_product}
			{assign var='productAttributeId' value=$product.id_product_attribute}
			{assign var='quantityDisplayed' value=0}
			{assign var='odd' value=($product@iteration+$last_was_odd)%2}
			{assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId)}
			{assign var='cannotModify' value=1}
			{* Display the gift product line *}
			{include file="./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}
		{/foreach}
		</tbody>
	{if sizeof($discounts)}
		<tbody>
		{foreach $discounts as $discount}
			<tr class="cart_discount {if $discount@last}last_item{elseif $discount@first}first_item{else}item{/if}" id="cart_discount_{$discount.id_discount}">
				<td class="cart_discount_name" colspan="3">{$discount.name}</td>
				<td class="cart_discount_price"><span class="price-discount">
					{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}
				</span></td>
				<td class="cart_discount_delete">1</td>
				<td class="cart_discount_price">
					<span class="price-discount price">{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}</span>
				</td>
				<td class="price_discount_del">
					{if strlen($discount.code)}<a href="{$link->getModuleLink('klarnacheckout', 'checkout_klarna')}?deleteDiscount={$discount.id_discount}" class="price_discount_delete" title="{l s='Delete' mod='klarnacheckout'}">{l s='Delete' mod='klarnacheckout'}</a>{/if}
				</td>
			</tr>
		{/foreach}
		</tbody>
	{/if}
	</table>
</div>