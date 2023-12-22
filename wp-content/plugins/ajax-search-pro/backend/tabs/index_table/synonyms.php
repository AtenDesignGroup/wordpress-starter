<?php
/* Prevent direct access */

use WPDRMS\ASP\Synonyms as Synonyms;

defined('ABSPATH') or die("You can't access this file directly.");

$syn = Synonyms\Manager::getInstance();
$langs = wpd_get_languages_array();
?>

<p class="infoMsg">
    <?php echo __('After adding all the synonyms you needed, make sure to re-create the index by clicking the <strong>Create new index</strong> button above.', 'ajax-search-pro'); ?>
    <?php echo sprintf(
        __('For more information check the <a href="%s" target="_blank">Synonyms Documentation</a>.', 'ajax-search-pro'),
        'https://documentation.ajaxsearchpro.com/index-table/synonyms'
    ); ?>
</p>
<div class="wpd-synonyms-list">
    <div class="wpd-synonyms-row wpd-synonyms-row-noflex wpd-sr-search">
        <div class="wpd-sr-search-left">
            <label for="wpd-syn-search-lang">
                <?php echo __('Language:', 'ajax-search-pro'); ?>
                <select id="wpd-syn-search-lang" name="wpd-syn-search-lang">
                    <option value="any"><?php echo __('Any', 'ajax-search-pro'); ?></option>
                    <?php foreach($langs as $lcode => $lang): ?>
                        <option value="<?php echo $lcode; ?>"><?php echo $lang; ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <input type="text" name="wpd-search-synonyms" id="wpd-search-synonyms" value="" placeholder="<?php echo __('Search synonims here..', 'ajax-search-pro'); ?>">
            <?php _e('or', 'ajax-search-pro'); ?> <input type="button" id="asp_syn_add" class="asp_syn_add submit wd_button_blue" value="<?php echo __('+Add new', 'ajax-search-pro'); ?>"/>
        </div>
        <div class="wpd-sr-search-right">
            <input type="button" id="asp-syn-import" class="asp_syn_add submit wd_button_syn" value="<?php echo __('Import', 'ajax-search-pro'); ?>"/>
            <input type="button" id="asp-syn-export" class="asp_syn_add submit wd_button_syn" value="<?php echo __('Export', 'ajax-search-pro'); ?>"/>
            <input type="button" id="asp-syn-remove-all" class="asp_syn_remove_all submit wd_button_syn" value="<?php echo __('Delete All', 'ajax-search-pro'); ?>"/>
            <input type="hidden" id="asp_synonyms_request_nonce" value="<?php echo wp_create_nonce( 'asp_synonyms_request_nonce' ); ?>">
        </div>
    </div>
    <div class="wpd-synonyms-row wpd-synonyms-row-head">
        <div class="wpd-synonyms-col"><?php echo __('Keyword', 'ajax-search-pro'); ?></div>
        <div class="wpd-synonyms-col"><?php echo __('Synonyms', 'ajax-search-pro'); ?></div>
        <div class="wpd-synonyms-col"></div>
    </div>
    <div id="wpd-synonyms-editor" class="wpd-synonyms-row wpd-synonyms-editor hiddend" data-update="0">
        <div class="wpd-synonyms-col">
            <input type="text" id="wpd-synonym-input" name="wpd-synonym-input" value="" placeholder="Enter original..">
            <label for="wpd-synonym-lang">
                <?php echo __('Language:', 'ajax-search-pro'); ?>
                <select id="wpd-synonym-lang" name="wpd-synonym-lang">
                    <option value=""><?php echo __('Default', 'ajax-search-pro'); ?></option>
                    <?php foreach($langs as $lcode => $lang): ?>
                        <option value="<?php echo $lcode; ?>"><?php echo $lang; ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div class="wpd-synonyms-col">
            <div data-name="wpd-tag" id="wpd-tag"></div>
        </div>
        <div class="wpd-synonyms-col">
            <input type="button" id='syn-editor-save' class="submit wd_button_blue" value="Save"/>
            <input type="button" id='syn-editor-cancel' class="submit" value="Cancel"/>
        </div>
        <div class="wpd-synonyms-err hiddend">
            <p class="errorMsg"></p>
        </div>
        <div class="wpd-syn-overlay"></div>
    </div>
    <div class="wpd-syn-results" id="wpd-syn-results">
        <?php foreach ( $syn->find('', 'any') as $k => $syn_data ): ?>
            <div class="wpd-synonyms-row" data-id="<?php echo $syn_data['id']; ?>" data-lang="<?php echo $syn_data['lang']; ?>" data-keyword="<?php echo $syn_data['keyword']; ?>">
                <div class="wpd-synonyms-col syn-kw-col">
                    <?php echo $syn_data['keyword']; ?>
                    <span>[language: <?php echo $syn_data['lang'] == '' ? 'default' : $syn_data['lang']; ?>]</span>
                </div>
                <div class="wpd-synonyms-col syn-syn-col"><?php echo str_replace(',', ', ', $syn_data['synonyms']); ?></div>
                <div class="wpd-synonyms-col">
                    <input type="button" class="syn-edit-row submit wd_button_green" value="<?php echo __('Edit', 'ajax-search-pro'); ?>"/>
                    <input type="button" class="syn-delete-row submit" value="<?php echo __('Delete', 'ajax-search-pro'); ?>"/>
                </div>
                <div class="wpd-synonyms-err hiddend">
                    <p class="errorMsg"></p>
                </div>
                <div class="wpd-syn-overlay"></div>
            </div>
        <?php endforeach; ?>
        <div class="wpd-synonyms-nores hiddend">
            <p class="errorMsg"><?php echo __('No results!', 'ajax-search-pro'); ?></p>
        </div>
        <div class="wpd-syn-overlay"></div>
    </div>
    <div id='wpd-synonyms-row-sample' class="wpd-synonyms-row hiddend" data-id="" data-lang="" data-keyword="">
        <div class="wpd-synonyms-col syn-kw-col"></div>
        <div class="wpd-synonyms-col syn-syn-col"></div>
        <div class="wpd-synonyms-col">
            <input type="button" class="syn-edit-row submit wd_button_green" value="<?php echo __('Edit', 'ajax-search-pro'); ?>"/>
            <input type="button" class="syn-delete-row submit" value="<?php echo __('Delete', 'ajax-search-pro'); ?>"/>
        </div>
        <div class="wpd-synonyms-err hiddend">
            <p class="errorMsg"></p>
        </div>
        <div class="wpd-syn-overlay"></div>
    </div>
    <div class="hiddend">
        <div id="syn-export-modal">
            <label for="syn-export-generate">
                <?php echo __('Click the button to generate a new export file', 'ajax-search-pro'); ?>
                <input id="syn-export-generate" type="button" class="wd_button wd_button_green" value="<?php echo __('Generate new', 'ajax-search-pro'); ?>"/>
            </label>
            <div id="syn-export-download" class="syn-export-download hiddend">
                <a href="#" target="_blank" download="asp_synonyms_export.json"><?php echo __('Click here to download the latest export file', 'ajax-search-pro'); ?></a>
            </div>
            <div id="syn-export-error" class="syn-export-error hiddend">
                <p class="errorMsg"></p>
                <p class="infoMsg hiddend"></p>
            </div>
            <div class="wpd-syn-overlay"></div>
        </div>
        <div id="syn-import-modal">
            <?php echo __('Enter the export file URL, or click the <strong>Upload</strong> button to upload one.<br>', 'ajax-search-pro'); ?>
            <?php
            new wpdreamsUpload("syn-import-upload", __('File URL', 'ajax-search-pro'), '');
            ?>
            <label for="syn-import-upload">
                <?php echo __('Click the button to initiate the import', 'ajax-search-pro'); ?>
                <input id="syn-import-upload" type="button" class="wd_button wd_button_green" value="<?php echo __('Import!', 'ajax-search-pro'); ?>" disabled/>
            </label>
            <p class="descMsg">
                (<?php echo __('duplicates are ignored during the import process', 'ajax-search-pro'); ?>)
            </p>
            <div id="syn-import-error" class="syn-import-error hiddend">
                <p class="errorMsg hiddend"></p>
                <p class="infoMsg hiddend"></p>
            </div>
            <div class="wpd-syn-overlay"></div>
        </div>
    </div>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("it_synonyms_as_keywords", __('Use synonyms as keywords as well?', 'ajax-search-pro'),
        $it_options['it_synonyms_as_keywords']
    ); ?>
    <p class="descMsg">
        <?php echo __('By default, the plugin only looks for synonyms by the keywords defined. When enabled, the syonomys will be treated as keywords as well. It can drastically increase the keyword database size.', 'ajax-search-pro'); ?>
        <?php echo sprintf(
            __('For more information check the <a href="%s" target="_blank">Synonyms Documentation</a>.', 'ajax-search-pro'),
            'https://documentation.ajaxsearchpro.com/index-table/synonyms'
        ); ?>
    </p>
</div>
