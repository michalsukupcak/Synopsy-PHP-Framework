<div id="contentWrapper" class="container">
    
    <div class="text-center" id="icon">
        <h3><i class="fa fa-comments-o"></i> {getString key="i18n.title"}</h3>
    </div>

    <div class="text-center">
        {assign first 1}
        {foreach from=$languages key=code item=language}
            {if $first == 1}
                {assign first 0}
            {else}
                |
            {/if}
            <a href="{$smarty.const.URL}{$code}">{$language}</a>
        {/foreach}
    </div>
    
</div>