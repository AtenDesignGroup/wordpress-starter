<?php ob_start(); ?>
<label for="p_asp_post_type"><?php echo __('Post type', 'ajax-search-pro'); ?></label>
<select name="p_asp_post_type">
    <?php foreach($post_types as $post_type): ?>
        <option value="<?php echo $post_type ?>"><?php echo $post_type ?></option>
    <?php endforeach; ?>
</select>
<label for="p_asp_blog"><?php echo __('Blog', 'ajax-search-pro'); ?></label>
<select name="p_asp_blog">
    <option value="0" selected><?php echo __('Current', 'ajax-search-pro'); ?></option>
    <?php foreach($blogs as $blog): ?>
        <?php $blog_details = get_blog_details($blog->blog_id); ?>
        <option value="<?php echo $blog->blog_id; ?>"><?php echo  $blog_details->blogname; ?></option>
    <?php endforeach; ?>
</select>
<label for="p_asp_ordering"><?php echo __('Ordering', 'ajax-search-pro'); ?></label>
<select name="p_asp_ordering">
    <option value="id DESC" selected><?php echo __('ID descending', 'ajax-search-pro'); ?></option>
    <option value="id ASC"><?php echo __('ID ascending', 'ajax-search-pro'); ?></option>
    <option value="title DESC"><?php echo __('Title descending', 'ajax-search-pro'); ?></option>
    <option value="title ASC"><?php echo __('Title ascending', 'ajax-search-pro'); ?></option>
    <option value="priority DESC"><?php echo __('Priority descending', 'ajax-search-pro'); ?></option>
    <option value="priority ASC"><?php echo __('Priority ascending', 'ajax-search-pro'); ?></option>
</select>

<div style="display: inline-block;">
    <label><?php echo __('Filter', 'ajax-search-pro'); ?></label><input name="p_asp_filter" type="text" placeholder="<?php echo esc_attr__('Post title here', 'ajax-search-pro'); ?>">
</div>

<label><?php echo __('Limit', 'ajax-search-pro'); ?></label><input name="p_asp_limit" type="text" style="width: 40px;" value="20">

<input type='submit' id="p_asp_submit" class='submit' value='<?php echo esc_attr__('Filter', 'ajax-search-pro'); ?>'/>
<?php $_rr = ob_get_clean(); ?>

<?php if (ASP_DEMO): ?>
    <p class="infoMsg">DEMO MODE ENABLED - Please note, that these options are read-only</p>
<?php endif; ?>
<div class='wpdreams-slider'>
    <form name='asp_priorities' id="asp_priorities" method='post'>
        <fieldset>
            <legend><?php echo __('Filter Posts', 'ajax-search-pro'); ?></legend>
            <input type="hidden" id="asp_priorities_request_nonce" value="<?php echo wp_create_nonce( 'asp_priorities_request_nonce' ); ?>">
            <?php print $_rr; ?>
        </fieldset>
    </form>
</div>

<div id="p_asp_loader"></div>
<div id="p_asp_results"><p style="text-align:center;"><?php echo __('Click the filter to load results!', 'ajax-search-pro'); ?></p></div>