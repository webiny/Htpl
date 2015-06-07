{extends file="master.tpl"}

{block name='centerContent'}
    <div>

        <p>Total entries: {$entries|count}</p>

        {foreach from=$entries item=foo}
            <p style="background-color: {$foo.color}">
                {include file="entry.tpl"}
            </p>
        {/foreach}

    </div>
{/block}