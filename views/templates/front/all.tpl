{extends file='page.tpl'}

{block name='page_title'}
    <h1>{l s='All Shop Reviews' mod='shopreviews'}</h1>
{/block}

{block name='page_content'}
    <div id="all-shop-reviews">
        
        {if $total_reviews > 0}
            <p>{l s='Total reviews:' mod='shopreviews'} <strong>{$total_reviews}</strong></p>
            
            <div class="reviews-list">
                {foreach from=$reviews item=review}
                    <div class="review-item">
                        <div class="review-header">
                            <strong>{$review.customer_name|escape:'html':'UTF-8'}</strong>
                            <span class="review-rating">
                                {for $i=1 to 5}
                                    {if $i <= $review.rating}★{else}☆{/if}
                                {/for}
                            </span>
                            <span class="review-date">{dateFormat date=$review.date_add full=1}</span>
                        </div>
                        <div class="review-text">
                            <p>{$review.review_text|escape:'html':'UTF-8'|nl2br}</p>
                        </div>
                    </div>
                    <hr>
                {/foreach}
            </div>
            
            {* Pagination *}
            {if $total_pages > 1}
                <div class="pagination-wrapper">
                    <nav aria-label="{l s='Reviews pagination' mod='shopreviews'}">
                        <ul class="pagination">
                            {if $has_previous}
                                <li class="page-item">
                                    <a class="page-link" href="{$all_reviews_url}?page={$previous_page}">
                                        {l s='Previous' mod='shopreviews'}
                                    </a>
                                </li>
                            {/if}
                            
                            {for $p=1 to $total_pages}
                                <li class="page-item {if $p == $current_page}active{/if}">
                                    <a class="page-link" href="{$all_reviews_url}?page={$p}">
                                        {$p}
                                    </a>
                                </li>
                            {/for}
                            
                            {if $has_next}
                                <li class="page-item">
                                    <a class="page-link" href="{$all_reviews_url}?page={$next_page}">
                                        {l s='Next' mod='shopreviews'}
                                    </a>
                                </li>
                            {/if}
                        </ul>
                    </nav>
                </div>
            {/if}
            
        {else}
            <p>{l s='No reviews available yet.' mod='shopreviews'}</p>
        {/if}
        
        <div class="back-to-home">
            <a href="{$link->getPageLink('index')}" class="btn btn-secondary">
                {l s='Back to homepage' mod='shopreviews'}
            </a>
        </div>
        
    </div>
{/block}
