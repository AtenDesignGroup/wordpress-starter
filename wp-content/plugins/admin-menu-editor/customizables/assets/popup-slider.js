jQuery(function ($) {
	const ownEventMarker = {'isAmeSliderEvent': true};

	/**
	 * @param {JQuery} $container
	 * @param {AmePopupSliderOptions} [options]
	 * @constructor
	 */
	function PopupSlider($container, options) {
		const self = this;

		this.minWidth = 240;
		this.maxWidth = 600;

		this.$container = $container;
		this.$popup = (options && options.sliderElement) ? options.sliderElement : $container.find('.ame-popup-slider');
		this.$bar = this.$popup.find('.ame-popup-slider-bar');
		this.$activeInput = null;

		this.options = jQuery.extend(
			{
				positionParentSelector: '',
				verticalOffset: 0,
				ranges: null,
			},
			this.$popup.data('amePopupSliderOptions') || {},
			options || {}
		);

		//Ensure that the default range is always present, even if the user didn't specify it.
		if (this.options.ranges && !this.options.ranges.hasOwnProperty('_default')) {
			this.options.ranges['_default'] = {min: 0, max: 100, step: 1}
		}

		// noinspection JSUnusedGlobalSymbols - IDE fail, "slide" is a valid option for a jQuery UI slider.
		this.$bar.slider({
			slide: function (event, ui) {
				if (!self.$activeInput) {
					return;
				}

				self.$activeInput.val(ui.value);

				/*
				Trigger an event so that other scripts (e.g. Admin Customizer)
				can react to the change.

				Important: Using a custom namespace would not work here because that
				would only trigger handlers that explicitly specify that namespace.

				An event listener added using "$.on('input.custom')" will also
				receive regular "input" events, but "$.trigger('input.custom')"
				will NOT trigger handlers that listen for "input" events. It will
				only trigger handlers attached using the "custom" namespace, such
				as "input.whatever.custom".
				 */
				self.$activeInput.trigger('input', [ownEventMarker]);

				self.$activeInput.trigger('adminMenuEditor:controlValueChanged', ui.value);
			}
		});

		//Don't let the slider handle steal focus from the input.
		this.$bar.find('.ui-slider-handle').removeAttr('tabindex');

		/*
		Hide the slider when the user moves focus or interacts with something
		that's not the input field or the slider. This event handler is only
		added when the slider is shown.

		Note: This uses "mousedown" instead of "click" to keep the slider visible
		in the situation where the user begins dragging the handle and then releases
		the mouse button outside the slider box. If we used "click" instead, the click
		event would trigger on the outside element and the slider would be hidden.
		 */
		this.hideOnOutsideInteraction = function (e) {
			if (
				!self.$activeInput.is(e.target)
				&& !self.$popup.is(e.target)
				&& !jQuery.contains(self.$popup.get(0), e.target)
			) {
				self.$popup.hide();

				//Remove the event handler.
				if (self.$activeInput) {
					self.$activeInput.off('.ameSliderActiveInputChange');
				}

				self.$activeInput = null;
				self.toggleHideEventListener(false);
			}
		};
		this.isHideListenerActive = false;
	}

	PopupSlider.prototype.toggleHideEventListener = function (addListener) {
		if (this.isHideListenerActive === addListener) {
			return; //Nothing to do.
		}

		const events = 'mousedown focusin';
		const $body = $('body');
		if (addListener) {
			$body.on(events, this.hideOnOutsideInteraction);
			this.isHideListenerActive = true;
		} else {
			$body.off(events, this.hideOnOutsideInteraction);
			this.isHideListenerActive = false;
		}
	}

	/**
	 * @param {JQuery} $input
	 */
	PopupSlider.prototype.showForInput = function ($input) {
		if ((this.$activeInput === $input) && this.$popup.is(':visible')) {
			return; //Already visible and attached to the same input.
		}

		//Remove event handlers associated with the previous input.
		if (this.$activeInput) {
			this.$activeInput.off('.ameSliderActiveInputChange');
			this.toggleHideEventListener(false);
		}

		this.$activeInput = $input;
		this.$popup.show();

		//The slider should be as wide as the combination of the input and the unit
		//field (if any), or at least this.minWidth pixels.
		const unitElementId = $input.data('unit-element-id');
		const $unit = unitElementId ? this.$container.find('#' + unitElementId) : $();
		const unitWidth = $unit.length ? $unit.outerWidth() : 0;
		const inputWidth = $input.outerWidth();

		const popupWidth = Math.ceil(
			Math.max(
				this.minWidth,
				Math.min(inputWidth + unitWidth, this.maxWidth)
			)
		);
		this.$popup.outerWidth(popupWidth);

		//Position the dropdown below the input by default. Alternatively, you can
		//position it below a parent element that matches a selector.
		let $subject = $input;
		if (this.options['positionParentSelector']) {
			const $matchingParent = $input.closest(this.options['positionParentSelector']);
			if ($matchingParent.length > 0) {
				$subject = $matchingParent;
			}
		}

		const verticalOffset = this.options['verticalOffset'] || 0;

		//Don't forget about the tip/arrow.
		const $tip = this.$popup.find('.ame-popup-slider-tip');
		const tipHeight = $tip.outerHeight();
		//The visual height of the tip is smaller because it's rotated 45 degrees
		//and moved to cover the top border of the dropdown.
		const tipVisualHeight = Math.ceil(tipHeight / Math.sqrt(2)) + 1;

		let positionOptions = {
			my: 'left top+' + Math.max((tipVisualHeight + verticalOffset), 0),
			at: 'left bottom',
			of: $subject,
			collision: 'fit flip'
		};
		//Optionally, try to position the popup within the closest matching parent.
		if (this.options.positionWithinClosest) {
			const $within = $subject.closest(this.options.positionWithinClosest);
			if ($within.length > 0) {
				positionOptions.within = $within;
			}
		}
		this.$popup.position(positionOptions);

		//Try to center the tip relative to the subject, on the X axis.
		//The tip's width also looks different because it's rotated.
		const tipVisualWidth = Math.ceil($tip.outerWidth() / Math.sqrt(2)) + 2;
		const subjectWidth = $subject.outerWidth() || 0;
		//The left edge of the popup might not be actually aligned with the left
		//edge of the subject if that would cause it to overflow the window/container.
		//We need to take that into account when calculating the tip's position.
		const subjectLeft = $subject.offset().left || 0;
		const popupLeft = this.$popup.offset().left || 0;
		const desiredTipOffset = Math.round(
			(subjectLeft - popupLeft)
			+ (subjectWidth - tipVisualWidth) / 2
			- 1.5 //Fudging a bit to account for unknown bugs.
		);
		//The tip should stay within the popup's bounds.
		const minTipOffset = 1;
		const maxTipOffset = popupWidth - tipVisualWidth - 1;

		$tip.css('left', Math.max(Math.min(desiredTipOffset, maxTipOffset), minTipOffset));

		//If the popup is below the subject, show only the top tip.
		//Otherwise, show only the bottom tip.
		const popupTop = this.$popup.offset().top || 0;
		const subjectTop = $subject.offset().top || 0;
		const $topTip = $tip.filter('.ame-popup-slider-top-tip');
		const $bottomTip = $tip.filter('.ame-popup-slider-bottom-tip');
		if (popupTop > subjectTop) {
			$topTip.show();
			$bottomTip.hide();
		} else {
			$topTip.hide();
			$bottomTip.show();
		}

		//Update slider range based on the selected unit.
		const range = this.getCurrentRange($input, $unit);
		this.$bar.slider('option', {
			min: range.min,
			max: range.max,
			step: range.step
		});

		//Update the slider value.
		this.updateValueFromInput($input);

		//Update the slider when the input value changes.
		const self = this;
		$input.on('input.ameSliderActiveInputChange', function (e, extraParam) {
			//Ignore changes caused by the slider itself.
			if (extraParam === ownEventMarker) {
				return;
			}
			self.updateValueFromInput(self.$activeInput);
		});

		$input.on(
			'adminMenuEditor:observableValueChanged.ameSliderActiveInputChange',
			function (e, newValue) {
				self.updateValueDirectly(newValue);
			}
		);

		this.toggleHideEventListener(true);
	};

	PopupSlider.prototype.getCurrentRange = function ($input, $unit) {
		if (!$unit) {
			const unitElementId = $input.data('unit-element-id');
			$unit = unitElementId ? this.$container.find('#' + unitElementId) : $();
		}

		//Use the custom ranges if specified, or extract them from the input/unit fields.
		const ranges = this.options.ranges
			? this.options.ranges
			: this.getInputRanges($input, $unit);

		const unit = $unit.is('span, div') ? $unit.data('number-unit') : $unit.val();
		return ranges[unit] || ranges['_default'];
	}

	PopupSlider.prototype.getInputRanges = function ($input, $unit) {
		let ranges = $input.data('ameParsedSliderRanges');
		if (ranges) {
			return ranges;
		}

		ranges = this.extractRanges($input);
		if (!ranges) {
			ranges = this.extractRanges($unit);
			if (!ranges) {
				ranges = {};
			}
		}

		if (typeof ranges['_default'] === 'undefined') {
			ranges['_default'] = {
				min: parseFloat($input.attr('min')) || 0,
				max: parseFloat($input.attr('max')) || 100,
				step: parseFloat($input.attr('step')) || 1
			};
		}

		$input.data('ameParsedSliderRanges', ranges);
		return ranges;
	}

	PopupSlider.prototype.extractRanges = function ($element) {
		if (!$element) {
			return null;
		}

		const config = $element.data('slider-ranges');
		if (!config || (typeof config !== 'object')) {
			return null;
		}
		return config;
	};

	PopupSlider.prototype.updateValueFromInput = function ($input) {
		this.updateValueDirectly($input.val());
	};

	PopupSlider.prototype.updateValueDirectly = function (rawValue) {
		const value = ((typeof rawValue === 'string') ? parseFloat(rawValue) : rawValue) || 0;
		this.$bar.slider('value', value);
	}

	/**
	 * Create a slider for the specified container.
	 *
	 * Automatically generates the slider's markup if it doesn't exist yet.
	 *
	 * @param {JQuery} $container
	 * @param {AmePopupSliderOptions} [options]
	 */
	PopupSlider.createSlider = function ($container, options) {
		if ((typeof options === 'undefined') || (options === null)) {
			options = {};
		}

		let $popup = options.sliderElement || $container.find('.ame-popup-slider');
		if (!$popup || ($popup.length === 0)) {
			$popup = $('<div class="ame-popup-slider" style="display: none"></div>');
			$popup.append(`
				<div class="ame-popup-slider-tip ame-popup-slider-bottom-tip"></div>
				<div class="ame-popup-slider-tip ame-popup-slider-top-tip"></div>
				<div class="ame-popup-slider-bar">
					<div class="ame-popup-slider-groove"></div>
					<div class="ui-slider-handle ame-popup-slider-handle"></div>
				</div>
			`);
			//Add the slider to the body instead of the container so that it can
			//extend outside the container's bounds without being clipped.
			jQuery('body').append($popup);

			//The slider should have a higher z-index than the container
			//or any of the container's parents so that it's always visible.
			let maxZIndex = 0;
			$container.parentsUntil('body').addBack().each(function () {
				const zIndex = parseInt($(this).css('z-index'), 10);
				if ((zIndex > 0) && (zIndex > maxZIndex)) {
					maxZIndex = zIndex;
				}
			});
			if (maxZIndex > 0) {
				$popup.css('z-index', maxZIndex + 1);
			}

			options.sliderElement = $popup;
		}

		return new PopupSlider($container, options);
	}

	//Expose the class in the global scope so that Knockout components can use it.
	window.AmePopupSlider = PopupSlider;

	//Show the slider when the user clicks on an input.
	$('.ame-container-with-popup-slider .ame-input-with-popup-slider').on('click', function () {
		const $input = $(this);
		/** @type {JQuery} */
		const $container = $input.closest('.ame-container-with-popup-slider');

		let popupSlider = $container.data('amePopupSlider');
		if (!popupSlider) {
			popupSlider = new PopupSlider($container);
			$container.data('amePopupSlider', popupSlider);
		}

		popupSlider.showForInput($input);
	});
});