<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/**
 * This is the default template for the keyword suggestions
 *
 * !!!IMPORTANT!!!
 * Do not make changes directly to this file! To have permanent changes copy this
 * file to your theme directory under the "asp" folder like so:
 *    wp-content/themes/your-theme-name/asp/keyword-suggestions.php
 *
 * The keyword must always hold the 'asp_keyword' class and only
 * contain the keyword text as the content.
 *
 * You can use any WordPress function here.
 * Variables to mention:
 *      String   $phrase - the search phrase
 *      Array[]  $s_keywords - array of the keywords
 *      Array[]  $s_options - holding the search options
 *
 * You can leave empty lines for better visibility, they are cleared before output.
 *
 * MORE INFO: https://wp-dreams.com/knowledge-base/result-templating/
 *
 * @since: 4.0
 */
?>
<div class="asp_nores">

    <div class="asp_nores_header">
        <?php
        echo stripslashes( \WPDRMS\ASP\Utils\Str::resolveBracketSyntax( asp_icl_t("No results text" . " ($id)", $s_options['noresultstext']), array('phrase' => $phrase) ));
        ?>
    </div>
	<div class="asp_nores_kw_suggestions">
		<?php echo stripslashes( asp_icl_t("Did you mean? text" . " ($id)", $s_options['didyoumeantext']) ); ?>
		<?php foreach($s_keywords as $keyword): ?>
			<span class='asp_keyword'><?php echo $keyword; ?></span>
		<?php endforeach; ?>
	</div>
</div>