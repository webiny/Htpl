<ul>
    {foreach from=$foo item=i key=k}
        {if $k=='name' || $k=='id' || $k=='item_order'}
            <li><strong>{$k}:</strong> {$i}</li>
        {/if}
    {/foreach}
</ul>