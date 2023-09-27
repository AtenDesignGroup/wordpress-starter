/* Copyright 2012 Mozilla Foundation
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

import { FindState } from './pdf_find_controller.js';

const MATCHES_COUNT_LIMIT = 1000;

/**
 * Creates a "search bar" given a set of DOM elements that act as controls
 * for searching or for setting search preferences in the UI. This object
 * also sets up the appropriate events for the controls. Actual searching
 * is done by PDFFindController.
 */
class PDFFindBar {
	constructor(options, eventBus, l10n) {
		this.opened = false;

		this.bar = options.bar;
		this.toggleButton = options.toggleButton;
		this.findField = options.findField;
		this.highlightAll = options.highlightAllCheckbox;
		this.caseSensitive = options.caseSensitiveCheckbox;
		this.matchDiacritics = options.matchDiacriticsCheckbox;
		this.entireWord = options.entireWordCheckbox;
		this.findMsg = options.findMsg;
		this.findResultsCount = options.findResultsCount;
		this.findPreviousButton = options.findPreviousButton;
		this.findNextButton = options.findNextButton;
		this.eventBus = eventBus;
		this.l10n = l10n;

		// Add event listeners to the DOM elements
		for (let i = 0; i < this.toggleButton.length; i++) {
			this.toggleButton[i].addEventListener('click', () => {
				this.toggle(i);
			});
		}
		for (let i = 0; i < this.findField.length; i++) {
			this.findField[i].addEventListener('input', () => {
				this.dispatchEvent([i], '');
			});
		}
		for (let i = 0; i < this.bar.length; i++) {
			window.parent.document.addEventListener('keydown', (e) => {
				switch (e.keyCode) {
					case 27: // Escape
						this.close(i);
						break;
				}
			});
			document.addEventListener('keydown', (e) => {
				switch (e.keyCode) {
					case 13: // Enter
						if (e.target === this.findField[i]) {
							this.dispatchEvent([i], 'again', e.shiftKey);
						}
						break;
					case 27: // Escape
						this.close(i);
						break;
				}
			});
		}
		for (let i = 0; i < this.findPreviousButton.length; i++) {
			this.findPreviousButton[i].addEventListener('click', () => {
				this.dispatchEvent([i], 'again', true);
			});
		}
		for (let i = 0; i < this.findNextButton.length; i++) {
			this.findNextButton[i].addEventListener('click', () => {
				this.dispatchEvent([i], 'again', false);
			});
		}
		for (let i = 0; i < this.highlightAll.length; i++) {
			this.highlightAll[i].addEventListener('click', () => {
				this.dispatchEvent([i], 'highlightallchange');
			});
		}
		for (let i = 0; i < this.caseSensitive.length; i++) {
			this.caseSensitive[i].addEventListener('click', () => {
				this.dispatchEvent([i], 'casesensitivitychange');
			});
		}
		for (let i = 0; i < this.entireWord.length; i++) {
			this.entireWord[i].addEventListener('click', () => {
				this.dispatchEvent([i], 'entirewordchange');
			});
		}
		for (let i = 0; i < this.matchDiacritics.length; i++) {
			this.matchDiacritics[i].addEventListener('click', () => {
				this.dispatchEvent([i], 'diacriticmatchingchange');
			});
		}
		this.eventBus._on('resize', this.#adjustWidth.bind(this));
	}

	reset() {
		this.updateUIState();
	}

	dispatchEvent(index, type, findPrev = false) {
		this.eventBus.dispatch('find', {
			source: this,
			type,
			query: this.findField[index].value,
			phraseSearch: true,
			caseSensitive: this.caseSensitive[index].checked,
			entireWord: this.entireWord[index].checked,
			highlightAll: this.highlightAll[index].checked,
			findPrevious: findPrev[index],
			matchDiacritics: this.matchDiacritics[index].checked,
		});
	}

	updateUIState(state, previous, matchesCount) {
		let findMsg = Promise.resolve('');
		let status = '';

		switch (state) {
			case FindState.FOUND:
				break;
			case FindState.PENDING:
				status = 'pending';
				break;
			case FindState.NOT_FOUND:
				findMsg = this.l10n.get('find_not_found');
				status = 'notFound';
				break;
			case FindState.WRAPPED:
				findMsg = this.l10n.get(
					`find_reached_${previous ? 'top' : 'bottom'}`,
				);
				break;
		}
		for (let i = 0; i < this.findField.length; i++) {
			this.findField[i].setAttribute('data-status', status);
			this.findField[i].setAttribute(
				'aria-invalid',
				state === FindState.NOT_FOUND,
			);
		}
		for (let i = 0; i < this.findMsg.length; i++) {
			findMsg.then((msg) => {
				this.findMsg[i].textContent = msg;
				this.#adjustWidth();
			});

			this.updateResultsCount(matchesCount);
		}
	}

	updateResultsCount({ current = 0, total = 0 } = {}) {
		const limit = MATCHES_COUNT_LIMIT;
		let matchCountMsg = Promise.resolve('');

		if (total > 0) {
			if (total > limit) {
				let key = 'find_match_count_limit';

				if (
					typeof PDFJSDev !== 'undefined' &&
					PDFJSDev.test('MOZCENTRAL')
				) {
					// TODO: Remove this hard-coded `[other]` form once plural support has
					// been implemented in the mozilla-central specific `l10n.js` file.
					key += '[other]';
				}
				matchCountMsg = this.l10n.get(key, { limit });
			} else {
				let key = 'find_match_count';

				if (
					typeof PDFJSDev !== 'undefined' &&
					PDFJSDev.test('MOZCENTRAL')
				) {
					// TODO: Remove this hard-coded `[other]` form once plural support has
					// been implemented in the mozilla-central specific `l10n.js` file.
					key += '[other]';
				}
				matchCountMsg = this.l10n.get(key, { current, total });
			}
		}
		matchCountMsg.then((msg) => {
			for (let i = 0; i < this.findResultsCount.length; i++) {
				this.findResultsCount[i].textContent = msg;
			}
			// Since `updateResultsCount` may be called from `PDFFindController`,
			// ensure that the width of the findbar is always updated correctly.
			this.#adjustWidth();
		});
	}

	open(i) {
		if (!this.opened) {
			this.opened = true;
			this.toggleButton[i].classList.add('toggled');
			this.toggleButton[i].setAttribute('aria-expanded', 'true');
			this.bar[i].classList.remove('hidden');
			this.findField[i].select();
			this.findField[i].focus();
		}

		this.#adjustWidth();
	}

	close(i) {
		if (!this.opened) {
			return;
		}
		this.opened = false;
		this.toggleButton[i].classList.remove('toggled');
		this.toggleButton[i].setAttribute('aria-expanded', 'false');
		this.bar[i].classList.add('hidden');

		this.eventBus.dispatch('findbarclose', { source: this });
	}

	toggle(i) {
		if (this.opened) {
			this.close(i);
		} else {
			this.open(i);
		}
	}

	#adjustWidth() {
		if (!this.opened) {
			return;
		}
		for (let i = 0; i < this.bar.length; i++) {
			// The find bar has an absolute position and thus the browser extends
			// its width to the maximum possible width once the find bar does not fit
			// entirely within the window anymore (and its elements are automatically
			// wrapped). Here we detect and fix that.
			this.bar[i].classList.remove('wrapContainers');

			const findbarHeight = this.bar[i].clientHeight;
			const inputContainerHeight =
				this.bar[i].firstElementChild.clientHeight;

			if (findbarHeight > inputContainerHeight) {
				// The findbar is taller than the input container, which means that
				// the browser wrapped some of the elements. For a consistent look,
				// wrap all of them to adjust the width of the find bar.
				this.bar[i].classList.add('wrapContainers');
			}
		}
	}
}

export { PDFFindBar };
