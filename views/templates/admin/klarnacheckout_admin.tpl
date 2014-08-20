{if $isSaved}	
	<div class="conf confirm">
		{l s='Settings updated' mod='klarnacheckout'}
	</div>
{/if}
{if $PS_GUEST_CHECKOUT_ENABLED!=1}
<div class="warning">
		{l s='Guest checkout must be enabled!' mod='klarnacheckout'}
	</div>
{/if}
<img src="../modules/klarnacheckout/img/klarnacheckout.png" style="float:left; margin-right:15px;" />
		<b>{l s='This module allows you to use Klarna checkout.' mod='klarnacheckout'}</b><br /><br />
		<br /><br /><br />
		
<form action="{$REQUEST_URI}" method="post">
<fieldset class="width3">
<legend>{l s='Settings' mod='klarnacheckout'}</legend>
	<label>{l s='Enable Ajax cart' mod='klarnacheckout'}</label>
	<div class="margin-form">
		<input type="radio" name="PS_BLOCK_CART_AJAX" id="ajax_on" value="1"{if $PS_BLOCK_CART_AJAX==1} checked="checked"{/if}/>
		<label class="t" for="ajax_on"> <img src="../img/admin/enabled.gif" alt="{l s='Yes' mod='klarnacheckout'}" title="{l s='Yes' mod='klarnacheckout'}" />{l s='Yes' mod='klarnacheckout'}</label>
		<input type="radio" name="PS_BLOCK_CART_AJAX" id="ajax_off" value="0"{if $PS_BLOCK_CART_AJAX==0} checked="checked"{/if}/>
		<label class="t" for="ajax_off"> <img src="../img/admin/disabled.gif" alt="{l s='No' mod='klarnacheckout'}" title="{l s='No' mod='klarnacheckout'}" />{l s='No' mod='klarnacheckout'}</label>
		<p class="clear">{l s='Enable AJAX mode for cart (Your theme has to be compliant)' mod='klarnacheckout'}</p>
	</div>
	
	<label>{l s='Testdrive' mod='klarnacheckout'}</label>
	<div class="margin-form">
		<input type="radio" name="KLARNACHECKOUT_TESTMODE" id="ajax_on" value="1"{if $KLARNACHECKOUT_TESTMODE==1} checked="checked"{/if}/>
		<label class="t" for="ajax_on"> <img src="../img/admin/enabled.gif" alt="{l s='Yes' mod='klarnacheckout'}" title="{l s='Yes' mod='klarnacheckout'}" />{l s='Yes' mod='klarnacheckout'}</label>
		<input type="radio" name="KLARNACHECKOUT_TESTMODE" id="ajax_off" value="0"{if $KLARNACHECKOUT_TESTMODE==0} checked="checked"{/if}/>
		<label class="t" for="ajax_off"> <img src="../img/admin/disabled.gif" alt="{l s='No' mod='klarnacheckout'}" title="{l s='No' mod='klarnacheckout'}" />{l s='No' mod='klarnacheckout'}</label>
	</div>
	
	<label>{l s='Order identifier' mod='klarnacheckout'}</label>
	<div class="margin-form">
		<input type="radio" name="KLARNACHECKOUT_ORDERID" id="id_order_on" value="1"{if $KLARNACHECKOUT_ORDERID==1} checked="checked"{/if}/>
		<label class="t" for="id_order_on"> <img src="../img/admin/enabled.gif" alt="id_order" title="id_order" />id_order</label>
		<input type="radio" name="KLARNACHECKOUT_ORDERID" id="id_order_off" value="0"{if $KLARNACHECKOUT_ORDERID==0} checked="checked"{/if}/>
		<label class="t" for="id_order_off"> <img src="../img/admin/disabled.gif" alt="Reference" title="Reference" />Reference</label>
	</div>
	
	<label>{l s='Round off total' mod='klarnacheckout'}</label>
	<div class="margin-form">
		<input type="radio" name="KLARNACHECKOUT_ROUNDOFF" id="ajax_on" value="1"{if $KLARNACHECKOUT_ROUNDOFF==1} checked="checked"{/if}/>
		<label class="t" for="ajax_on"> <img src="../img/admin/enabled.gif" alt="{l s='Yes' mod='klarnacheckout'}" title="{l s='Yes' mod='klarnacheckout'}" />{l s='Yes' mod='klarnacheckout'}</label>
		<input type="radio" name="KLARNACHECKOUT_ROUNDOFF" id="ajax_off" value="0"{if $KLARNACHECKOUT_ROUNDOFF==0} checked="checked"{/if}/>
		<label class="t" for="ajax_off"> <img src="../img/admin/disabled.gif" alt="{l s='No' mod='klarnacheckout'}" title="{l s='No' mod='klarnacheckout'}" />{l s='No' mod='klarnacheckout'}</label>
	</div>

	<label>{l s='EID' mod='klarnacheckout'}</label>
	<div class="margin-form">
		<input type="text" name="KLARNACHECKOUT_EID" value="{$KLARNACHECKOUT_EID}" />
	</div>

	<label>{l s='Secret' mod='klarnacheckout'}</label>
	<div class="margin-form">
		<input type="text" name="KLARNACHECKOUT_SECRET" value="{$KLARNACHECKOUT_SECRET}" />
	</div>
	
	<label>{l s='Use 2 column checkout' mod='klarnacheckout'}</label>
	<div class="margin-form">
		<input type="radio" name="KLARNACHECKOUT_LAYOUT" id="2cols_on" value="1"{if $KLARNACHECKOUT_LAYOUT==1} checked="checked"{/if}/>
		<label class="t" for="2cols_on"> <img src="../img/admin/enabled.gif" alt="{l s='Yes' mod='klarnacheckout'}" title="{l s='Yes' mod='klarnacheckout'}" />{l s='Yes' mod='klarnacheckout'}</label>
		<input type="radio" name="KLARNACHECKOUT_LAYOUT" id="2cols_off" value="0"{if $KLARNACHECKOUT_LAYOUT==0} checked="checked"{/if}/>
		<label class="t" for="2cols_off"> <img src="../img/admin/disabled.gif" alt="{l s='No' mod='klarnacheckout'}" title="{l s='No' mod='klarnacheckout'}" />{l s='No' mod='klarnacheckout'}</label>
	</div>
	
	<label>{l s='Enable Sweden' mod='klarnacheckout'}</label>
	<div class="margin-form">
		<input type="radio" name="KLARNACHECKOUT_SWEDEN" id="sweden_on" value="1"{if $KLARNACHECKOUT_SWEDEN==1} checked="checked"{/if}/>
		<label class="t" for="sweden_on"> <img src="../img/admin/enabled.gif" alt="{l s='Yes' mod='klarnacheckout'}" title="{l s='Yes' mod='klarnacheckout'}" />{l s='Yes' mod='klarnacheckout'}</label>
		<input type="radio" name="KLARNACHECKOUT_SWEDEN" id="sweden_off" value="0"{if $KLARNACHECKOUT_SWEDEN==0} checked="checked"{/if}/>
		<label class="t" for="sweden_off"> <img src="../img/admin/disabled.gif" alt="{l s='No' mod='klarnacheckout'}" title="{l s='No' mod='klarnacheckout'}" />{l s='No' mod='klarnacheckout'}</label>
	</div>
	
	<label>{l s='Enable Norway' mod='klarnacheckout'}</label>
	<div class="margin-form">
		<input type="radio" name="KLARNACHECKOUT_NORWAY" id="norway_on" value="1"{if $KLARNACHECKOUT_NORWAY==1} checked="checked"{/if}/>
		<label class="t" for="norway_on"> <img src="../img/admin/enabled.gif" alt="{l s='Yes' mod='klarnacheckout'}" title="{l s='Yes' mod='klarnacheckout'}" />{l s='Yes' mod='klarnacheckout'}</label>
		<input type="radio" name="KLARNACHECKOUT_NORWAY" id="norway_off" value="0"{if $KLARNACHECKOUT_NORWAY==0} checked="checked"{/if}/>
		<label class="t" for="norway_off"> <img src="../img/admin/disabled.gif" alt="{l s='No' mod='klarnacheckout'}" title="{l s='No' mod='klarnacheckout'}" />{l s='No' mod='klarnacheckout'}</label>
	</div>
	
	<label>{l s='Enable Finland' mod='klarnacheckout'}</label>
	<div class="margin-form">
		<input type="radio" name="KLARNACHECKOUT_FINLAND" id="finland_on" value="1"{if $KLARNACHECKOUT_FINLAND==1} checked="checked"{/if}/>
		<label class="t" for="finland_on"> <img src="../img/admin/enabled.gif" alt="{l s='Yes' mod='klarnacheckout'}" title="{l s='Yes' mod='klarnacheckout'}" />{l s='Yes' mod='klarnacheckout'}</label>
		<input type="radio" name="KLARNACHECKOUT_FINLAND" id="finland_off" value="0"{if $KLARNACHECKOUT_FINLAND==0} checked="checked"{/if}/>
		<label class="t" for="finland_off"> <img src="../img/admin/disabled.gif" alt="{l s='No' mod='klarnacheckout'}" title="{l s='No' mod='klarnacheckout'}" />{l s='No' mod='klarnacheckout'}</label>
	</div>
	

	<br />
	<center><input type="submit" name="submitklarnacheckoutsettings" value="{l s='Save' mod='klarnacheckout'}" class="button" /></center>
</fieldset>
</form>