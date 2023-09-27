(function ($) {
    var defaultOptions = {
        'type'   : 'warning', // warning, info
        'header' : '',
        'headerIcons': true,
        'content': 'This is a modal!',
        'wrapContent': true,
        'leaveContent': false,
        'buttons': {
            'okay': {
                'text': 'Okay!',
                'type': 'okay',
                'click': function(e, button){}
            },
            'cancel': {
                'text': 'Cancel',
                'type': 'cancel',
                'click': function(e, button){}
            }
        },
        'layout': {
            'max-width': '480px'
        }
    };
    var icons = {
        'warning': [
            'M213.333 0C95.573 0 0 95.573 0 213.333s95.573 213.333 213.333 213.333 213.333-95.573 213.333-213.333S331.093 0 213.333 0zm21.334 320H192v-42.667h42.667V320zm0-85.333H192v-128h42.667v128z',
            '0 0 426.667 426.667'
        ],
        'info': [
            'M11.812 0C5.29 0 0 5.29 0 11.812s5.29 11.813 11.812 11.813 11.813-5.29 11.813-11.813S18.335 0 11.812 0zm2.46 18.307c-.61.24-1.093.422-1.456.548-.362.126-.783.19-1.262.19-.736 0-1.31-.18-1.717-.54s-.61-.814-.61-1.367c0-.215.014-.435.044-.66.032-.223.08-.475.148-.758l.76-2.688c.068-.258.126-.503.172-.73.046-.23.068-.442.068-.634 0-.342-.07-.582-.212-.717-.143-.134-.412-.2-.813-.2-.196 0-.398.03-.605.09-.205.063-.383.12-.53.176l.202-.828c.498-.203.975-.377 1.43-.52.455-.147.885-.22 1.29-.22.73 0 1.295.18 1.692.53.395.354.594.813.594 1.377 0 .117-.014.323-.04.617-.028.295-.08.564-.153.81l-.757 2.68c-.062.216-.117.462-.167.737-.05.274-.074.484-.074.625 0 .356.08.6.24.728.157.13.434.194.826.194.185 0 .392-.033.626-.097.232-.064.4-.12.506-.17l-.203.827zM14.136 7.43c-.353.327-.778.49-1.275.49-.496 0-.924-.163-1.28-.49-.354-.33-.533-.728-.533-1.194 0-.465.18-.865.532-1.196.356-.332.784-.497 1.28-.497.497 0 .923.165 1.275.497.353.33.53.73.53 1.196 0 .467-.177.865-.53 1.193z',
            '0 0 23.625 23.625'
        ]
    };
    var $this;
    var methods = {
        init: function (options, elem) {
            $this = this;

            $this.firstInit = true;
            $this.o = $.extend({}, defaultOptions, options);
            $this.n = {};

            $this.initSequence();
            return this;
        },
        initSequence: function() {
            $this.initElements();
            $this.initEvents();
            $this.firstInit = false;
        },
        initElements: function() {
            if ( $('#wpd_modal').length < 1 ) {
                $('body').append('<div id="wpd_modal" class="wpd-modal-type-'+$this.o.type+'"><div id="wpd_modal_head"></div><div id="wpd_modal_inner"></div><div id="wpd_modal_buttons"></div></div>');
            } else {
                $('#wpd_modal').removeClass();
                $('#wpd_modal').addClass('wpd-modal-type-'+$this.o.type);
            }
            if ( $('#wpd_modal_bg').length < 1 ) {
                $('body').append('<div id="wpd_modal_bg"></div>');
            }

            var buttons = '';
            if ( typeof($this.o.buttons) != 'undefined' ) {
                $.each($this.o.buttons, function(k, b){
                    var btn = $.extend({}, defaultOptions.buttons.okay, b);
                    buttons +=  '<div id="wpd_modal_btn_'+k+'" class="wpd-btn wpd-btn-'+b.type+'">'+b.text+'</div>';
                });
            }
            // Replace the contents, so previous handlers detach as well
            var headerContent = '<h3>' + $this.o.header + '</h3>';
            if ( $this.o.headerIcons ) {
                var $svgIcon = $('<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512"><path fill="#FFF"></path></svg>');
                if ( typeof(icons[$this.o.type]) != 'undefined' ) {
                    $svgIcon.children('path').attr('d', icons[$this.o.type][0]);
                    $svgIcon.attr('viewBox', icons[$this.o.type][1]);
                } else {
                    $svgIcon.children('path').attr('d', icons['info'][0]);
                    $svgIcon.attr('viewBox', icons['info'][1]);
                }
                headerContent = $svgIcon.get(0).outerHTML + headerContent;
            }
            if ( $this.o.header != '' ) {
                $('#wpd_modal_head').html(headerContent);
                $('#wpd_modal_head').css('display', '');
            } else {
                $('#wpd_modal_head').css('display', 'none');
            }
            if ( !$this.o.leaveContent ) {
                if (
                    typeof $this.o.content != 'string' &&
                    typeof $this.o.content.appendTo == 'function'
                ) {
                    $('#wpd_modal_inner').html('');
                    $this.o.content.appendTo('#wpd_modal_inner');
                } else {
                    if ($this.o.wrapContent)
                        $('#wpd_modal_inner').html('<p>' + $this.o.content + '</p>');
                    else
                        $('#wpd_modal_inner').html($this.o.content);
                }
            }
            $('#wpd_modal_buttons').html(buttons);

        },
        initEvents: function() {
            // Events for first single init go here
            if ( $this.firstInit ) {
                /*$('#wpd_modal_bg').off('click').on('click', function () {
                    $this.hide();
                });*/
            }
            if ( typeof($this.o.buttons) != 'undefined' ) {
                $.each($this.o.buttons, function (k, b) {
                    $('#wpd_modal_btn_'+k).on('click', function(e){
                        b.click(e, this);
                        $this.hide();
                    });
                });
            }
        },
        addButton: function(caption, type, handler) {
            if ( typeof(type) == 'undefined' || type == '' )
                type = 'error';
            if ( typeof(handler) == 'undefined' || type == '' )
                handler = function(e){};
            if ( $this.o.buttons === defaultOptions.buttons ) {
                $this.o.buttons = {};
            }
            $this.o.buttons[hashCode(type + caption)] = {
                'type': type,
                'text': caption,
                'click': handler
            };
            $this.initSequence();
        },
        options: function(options) {
            if ( typeof(options) != 'undefined' ) {
                $this.o = $.extend({}, defaultOptions, options);
                $this.initSequence();
            }
        },
        layout: function(layout) {
            $('#wpd_modal').css(layout);
        },
        show: function(options) {
            if ( typeof(options) != 'undefined' ) {
                $this.o = $.extend({}, defaultOptions, options);
                $this.initSequence();
            }

            $('#wpd_modal_bg, #wpd_modal').css({
                'display': 'block',
                'visibility': 'visible'
            });
            $('#wpd_modal').css({
                'marginLeft': -($('#wpd_modal').outerWidth() / 2),
                'marginTop': -($('#wpd_modal').outerHeight() / 2)
            });
            setTimeout(function() {
                $('#wpd_modal_bg').addClass('wpd-md-opacity-one');
                $('#wpd_modal').addClass('wpd-md-opacity-one');
            }, 20);
        },
        hide: function() {
            $('#wpd_modal_bg').removeClass('wpd-md-opacity-one');
            $('#wpd_modal').removeClass('wpd-md-opacity-one');
            setTimeout(function(){
                $('#wpd_modal_bg, #wpd_modal').css({
                    'display': 'none',
                    'visibility': 'hidden'
                });
            }, 150);
        }
    };

    // Create a plugin based on a defined object
    $.plugin = function (name, object) {
        $.fn[name] = function (options) {
            if ( typeof(options) != 'undefined' && object[options] ) {
                return object[options].apply( this, Array.prototype.slice.call( arguments, 1 ));
            } else {
                return this.each(function () {
                    if (!$.data(this, name)) {
                        $.data(this, name, Object.create(object).init(
                            options, this));
                    }
                });
            }

        };
    };
    $.plugin('WPD_Modal', methods);

    function hashCode(s){
        return s.split("").reduce(function(a,b){a=((a<<5)-a)+b.charCodeAt(0);return a&a},0);
    }

    window.WPD_Modal = $('body').WPD_Modal();
    window.WPD_Modal.options = function(o) {
        window.WPD_Modal.WPD_Modal('options', o);
    };
    window.WPD_Modal.show = function(o) {
        window.WPD_Modal.WPD_Modal('show', o);
    };
    window.WPD_Modal.hide = function() {
        window.WPD_Modal.WPD_Modal('hide');
    };
    window.WPD_Modal.layout= function(o) {
        window.WPD_Modal.WPD_Modal('layout', o);
    };
    window.WPD_Modal.addButton = function(caption, type, handler) {
        window.WPD_Modal.WPD_Modal('addButton', caption, type, handler);
    };
})(jQuery);
