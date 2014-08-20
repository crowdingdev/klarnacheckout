{if isset($klarna_error)}
<div class="warning">{$klarna_error}</div>
{else}
{$klarna_html}
{/if}
{if isset($HOOK_ORDER_CONFIRMATION)}
{$HOOK_ORDER_CONFIRMATION}
{/if}