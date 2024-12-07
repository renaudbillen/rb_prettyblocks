
{assign var="max_columns" value=$block.settings.columns}
{assign var="grid" value=12/$max_columns}
{assign var="i" value=0}

<div class="container">
    {foreach from=$block.states key=index  item=state}
        {if 0 === $i % $max_columns}
            {if 0 !== $i}
                </div>
            {/if}
            <div class="row tw_mt-4">
        {/if}
        <div class="col-md-{$grid} tw_text-center">
            <img src="{$state.image.url}" width="250" height="auto" class="tw_mb-2">
            <h3>{$state.name}</h3>
            <p class="tw_mb-1">{$state.function nofilter}</p>
            {if '' !== $state.phone}
                <p class="tw_mb-1"><a href="tel:{$state.phone|trim}">{$state.phone}</a></p>
            {/if}
            {if '' !== $state.email}
                <p class="tw_mb-1"><a href="mailto:{$state.email}">{$state.email}</a></p>
            {/if}
        </div>

        {assign var="i" value=$i+1}
    {/foreach}

    </div>
</div>
