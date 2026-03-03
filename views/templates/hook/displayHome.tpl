<div id="shop-reviews-section" class="container">
    <div class="row">
        <div class="col-md-12">
            <h3>{l s='What our customers say about us' mod='shopreviews'}</h3>
            
            {if $customer_logged}
                <div class="review-form-section">
                    <h4>{l s='Share your experience' mod='shopreviews'}</h4>
                    <form action="{$form_action}" method="post" id="shop-review-form">
                        <div class="form-group">
                            <label for="rating">{l s='Rating' mod='shopreviews'}</label>
                            <select name="rating" id="rating" class="form-control" style="width: auto; display: inline-block;">
                                <option value="5">5 ★★★★★</option>
                                <option value="4">4 ★★★★☆</option>
                                <option value="3">3 ★★★☆☆</option>
                                <option value="2">2 ★★☆☆☆</option>
                                <option value="1">1 ★☆☆☆☆</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="review_text">{l s='Your review' mod='shopreviews'}</label>
                            <textarea name="review_text" id="review_text" class="form-control" 
                                      rows="4" maxlength="500" required 
                                      placeholder="{l s='Tell us about your experience with our shop...' mod='shopreviews'}"></textarea>
                            <small class="form-text text-muted">{l s='Maximum 500 characters' mod='shopreviews'}</small>
                        </div>
                        
                        <button type="submit" name="submitShopReview" class="btn btn-primary">
                            {l s='Submit Review' mod='shopreviews'}
                        </button>
                    </form>
                    <hr>
                </div>
            {else}
                <p class="alert alert-info">
                    {l s='Please log in to leave a review about our shop.' mod='shopreviews'}
                </p>
            {/if}
            
            {if $reviews && count($reviews) > 0}
                <div class="reviews-display">
                    <div class="row">
                        {foreach from=$reviews item=review}
                            <div class="col-md-6 col-lg-4">
                                <div class="review-card" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px;">
                                    <div class="review-header">
                                        <strong>{$review.customer_name|escape:'html':'UTF-8'}</strong>
                                        <div class="review-rating">
                                            {for $i=1 to 5}
                                                {if $i <= $review.rating}★{else}☆{/if}
                                            {/for}
                                        </div>
                                    </div>
                                    <div class="review-text">
                                        <p>{$review.review_text|escape:'html':'UTF-8'|nl2br}</p>
                                    </div>
                                    <div class="review-date">
                                        <small class="text-muted">{dateFormat date=$review.date_add full=1}</small>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                </div>
            {else}
                <p class="alert alert-info">
                    {l s='No reviews yet. Be the first to share your experience!' mod='shopreviews'}
                </p>
            {/if}
        </div>
    </div>
</div>

<style>
#shop-reviews-section {
    margin: 30px 0;
}

.review-card {
    background: #f9f9f9;
    transition: box-shadow 0.3s ease;
}

.review-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.review-rating {
    color: #ffc107;
    font-size: 18px;
    margin: 5px 0;
}

.review-form-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 5px;
    margin-bottom: 30px;
}

#shop-review-form .form-group {
    margin-bottom: 15px;
}
</style>
