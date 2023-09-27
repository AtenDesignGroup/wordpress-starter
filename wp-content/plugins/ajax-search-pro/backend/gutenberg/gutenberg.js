'use strict';

;(function () {
    if (typeof wp == 'undefined' || typeof wp.blocks == 'undefined' || typeof wp.i18n == 'undefined' || typeof wp.i18n.__ == 'undefined' || typeof wp.blocks.registerBlockType == 'undefined' || typeof ASP_GUTENBERG == 'undefined' || typeof ASP_GUTENBERG.ids == 'undefined') return false;

    var __ = wp.i18n.__;
    var _wp$blocks = wp.blocks,
        registerBlockType = _wp$blocks.registerBlockType,
        query = _wp$blocks.query;

    var domain = 'ajax-search-pro';

    const {serverSideRender} = wp;

    /**
     * AspShortcode block
     *
     * @param {object}
     * @returns {string}
     */
    function AspShortcode(_ref) {
        var instance = _ref.instance,
            scType = _ref.scType;

        var sc = 'wd_asp';
        scType = parseInt(scType);
        if (scType === 2) {
            sc = 'wpdreams_asp_settings';
        } else if (scType === 3) {
            sc = 'wpdreams_ajaxsearchpro_results';
        }
        return '[' + sc + ' id=' + instance + ']';
    }

    registerBlockType('ajax-search-pro/block-asp-main', {
        title: __('Ajax Search Pro', domain),
        icon: React.createElement(
            'svg',
            { xmlns: 'http://www.w3.org/2000/svg', width: '512', height: '512', viewBox: '0 0 512 512' },
            React.createElement('path', { d: 'M460.355 421.59l-106.51-106.512c20.04-27.553 31.884-61.437 31.884-98.037C385.73 124.935 310.792 50 218.685 50c-92.106 0-167.04 74.934-167.04 167.04 0 92.107 74.935 167.042 167.04 167.042 34.912 0 67.352-10.773 94.184-29.158L419.945 462l40.41-40.41zM100.63 217.04c0-65.095 52.96-118.055 118.056-118.055 65.098 0 118.057 52.96 118.057 118.056 0 65.097-52.96 118.057-118.057 118.057-65.096 0-118.055-52.96-118.055-118.056z' })
        ),
        category: 'widgets',
        keywords: [__('search'), __('ajax')],
        attributes: {
            instance: {
                type: 'number',
                default: ASP_GUTENBERG.ids[0]
            },
            scType: {
                type: 'number',
                default: 1
            }
        },
        edit: function edit(props) {
            /**
             * NOTE: This function is also triggered whenever the editor is opened, as well as when the element is added
             */

            var _props$attributes = props.attributes,
                instance = _props$attributes.instance,
                scType = _props$attributes.scType,
                setAttributes = props.setAttributes,
                isSelected = props.isSelected;
            var _ASP_GUTENBERG = ASP_GUTENBERG,
                ids = _ASP_GUTENBERG.ids;

            function setscType(event) {
                var selected = event.target.querySelector('option:checked');
                var val = parseInt(selected.value);
                setAttributes({ scType: val });
                event.preventDefault();
            }
            function setInstance(event) {
                var selected = event.target.querySelector('option:checked');
                var val = parseInt(selected.value);
                setAttributes({ instance: val });
                event.preventDefault();
            }

            // Does this instance exist anymore?
            instance = parseInt(instance);
            instance = ASP_GUTENBERG.ids.indexOf(instance) === -1 ? ASP_GUTENBERG.ids[0] : instance;
            scType = parseInt(scType);
            // Save the originals
            setAttributes({
                scType: scType,
                instance: instance
            });

            let defaultEditor =  React.createElement(
                'div',
                { className: props.className },
                //Preview a block with a PHP render callback
                [
                    React.createElement(
                        'form',
                        { onSubmit: AspShortcode },
                        React.createElement(
                            'label',
                            null,
                            __('Ajax Search Pro:', domain),
                            React.createElement(
                                'select',
                                { value: scType, onChange: setscType },
                                React.createElement(
                                    'option',
                                    { value: '1' },
                                    __('Search bar', domain)
                                ),
                                React.createElement(
                                    'option',
                                    { value: '2' },
                                    __('Search settings', domain)
                                ),
                                React.createElement(
                                    'option',
                                    { value: '3' },
                                    __('Search results', domain)
                                )
                            )
                        ),
                        React.createElement(
                            'label',
                            null,
                            __('Search ID:', domain),
                            React.createElement(
                                'select',
                                { value: instance, onChange: setInstance },
                                ids.map(function (id, i) {
                                    var label = id + ' - ' + ASP_GUTENBERG.instances[id].name;
                                    return React.createElement(
                                        'option',
                                        { value: id },
                                        label
                                    );
                                })
                            )
                        )
                    )
                ]
            );

            if ( !isSelected && scType == 1 ) {
                return React.createElement(
                    'div',
                    { className: props.className },
                    //Preview a block with a PHP render callback
                    React.createElement( serverSideRender, {
                        block: 'ajax-search-pro/block-asp-main',
                        attributes: props.attributes
                    } )
                );
            } else {
                return defaultEditor;
            }

        },
        save: function save(props) {
            var _props$attributes2 = props.attributes,
                instance = _props$attributes2.instance,
                scType = _props$attributes2.scType;

            return React.createElement(AspShortcode, { instance: instance, scType: scType });
        }
    });
})();