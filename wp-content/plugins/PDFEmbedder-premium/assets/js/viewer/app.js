const pdfjsLib = require('pdfjs-dist');
import {
	animationStarted,
	apiPageLayoutToViewerModes,
	AutoPrintRegExp,
	DEFAULT_SCALE_VALUE,
	getActiveOrFocusedElement,
	isValidRotation,
	isValidScrollMode,
	isValidSpreadMode,
	noContextMenuHandler,
	normalizeWheelEventDirection,
	parseQueryString,
	ProgressBar,
	RendererType,
	RenderingStates,
	ScrollMode,
	SpreadMode,
	TextLayerMode,
} from './ui_utils.js';
import { Toolbar } from './toolbar.js';
import { PDFFindController } from './pdf_find_controller.js';
import { PDFFindBar } from './pdf_find_bar.js';
import { OverlayManager } from './overlay_manager.js';
import { AppOptions, OptionKind } from './app_options.js';

import { CursorTool, PDFCursorTools } from './pdf_cursor_tools.js';

const pdfjsViewer = require('pdfjs-dist/web/pdf_viewer.js');

const DISABLE_AUTO_FETCH_LOADING_BAR_TIMEOUT = 5000; // ms
const FORCE_PAGES_LOADED_TIMEOUT = 10000; // ms
const WHEEL_ZOOM_DISABLED_TIMEOUT = 1000; // ms

const ViewOnLoad = {
	UNKNOWN: -1,
	PREVIOUS: 0, // Default value.
	INITIAL: 1,
};

const ViewerCssTheme = {
	AUTOMATIC: 0, // Default value.
	LIGHT: 1,
	DARK: 2,
};

// Keep these in sync with mozilla-central's Histograms.json.
const KNOWN_VERSIONS = [
	'1.0',
	'1.1',
	'1.2',
	'1.3',
	'1.4',
	'1.5',
	'1.6',
	'1.7',
	'1.8',
	'1.9',
	'2.0',
	'2.1',
	'2.2',
	'2.3',
];
// Keep these in sync with mozilla-central's Histograms.json.
const KNOWN_GENERATORS = [
	'acrobat distiller',
	'acrobat pdfwriter',
	'adobe livecycle',
	'adobe pdf library',
	'adobe photoshop',
	'ghostscript',
	'tcpdf',
	'cairo',
	'dvipdfm',
	'dvips',
	'pdftex',
	'pdfkit',
	'itext',
	'prince',
	'quarkxpress',
	'mac os x',
	'microsoft',
	'openoffice',
	'oracle',
	'luradocument',
	'pdf-xchange',
	'antenna house',
	'aspose.cells',
	'fpdf',
];
pdfjsLib.GlobalWorkerOptions.workerSrc = pdfemb_trans.worker_src;
const USE_ONLY_CSS_ZOOM = true;

const DEFAULT_SCALE_DELTA = 1.1;
const MIN_SCALE = 0.25;
const MAX_SCALE = 10.0;

class DefaultExternalServices {
	constructor() {
		throw new Error('Cannot initialize DefaultExternalServices.');
	}

	static updateFindControlState(data) {}

	static updateFindMatchesCount(data) {}

	static initPassiveLoading(callbacks) {}

	static reportTelemetry(data) {}

	static createDownloadManager(options) {
		throw new Error('Not implemented: createDownloadManager');
	}

	static createPreferences() {
		throw new Error('Not implemented: createPreferences');
	}

	static createL10n(options) {
		throw new Error('Not implemented: createL10n');
	}

	static createScripting(options) {
		throw new Error('Not implemented: createScripting');
	}

	static get supportsIntegratedFind() {
		return shadow(this, 'supportsIntegratedFind', false);
	}

	static get supportsDocumentFonts() {
		return shadow(this, 'supportsDocumentFonts', true);
	}

	static get supportedMouseWheelZoomModifierKeys() {
		return shadow(this, 'supportedMouseWheelZoomModifierKeys', {
			ctrlKey: true,
			metaKey: true,
		});
	}

	static get isInAutomation() {
		return shadow(this, 'isInAutomation', false);
	}

	static updateEditorStates(data) {
		throw new Error('Not implemented: updateEditorStates');
	}
}
const PDFViewerApplication = {
	initialBookmark: document.location.hash.substring(1),
	appConfig: null,
	pdfLoadingTask: null,
	pdfDocument: null,
	pdfViewer: null,
	pdfHistory: null,
	pdfLinkService: null,
	eventBus: null,
	downloadManager: null,
	store: null,
	/** @type {Toolbar} */
	toolbar: null,
	_boundEvents: Object.create(null),
	/** @type {SecondaryToolbar} */
	secondaryToolbar: null,
	url: '',
	baseUrl: '',
	_downloadUrl: '',
	externalServices: DefaultExternalServices,
	isInitialViewSet: false,
	downloadComplete: false,
	async initalize(appConfig) {
		this.appConfig = appConfig;
		await this.intializeWebView();

		this.bindEvents();
		this.bindWindowEvents();
	},
	async intializeWebView() {
		const { appConfig } = this;
		let self = this;
		const eventBus = new pdfjsViewer.EventBus();
		this.eventBus = eventBus;

		this.overlayManager = new OverlayManager();

		const linkService = new pdfjsViewer.PDFLinkService({
			eventBus,
			externalLinkTarget: AppOptions.get('externalLinkTarget'),
			externalLinkRel: AppOptions.get('externalLinkRel'),
			ignoreDestinationZoom: AppOptions.get('ignoreDestinationZoom'),
		});
		this.pdfLinkService = linkService;

		const findController = new PDFFindController({
			linkService: linkService,
			eventBus,
		});
		this.findController = findController;
		this.l10n = pdfjsViewer.NullL10n;

		const container = document.getElementById('viewerContainer');
		const pdfViewer = new pdfjsViewer.PDFViewer({
			container,
			eventBus,
			linkService,
			findController,
			l10n: this.l10n,
			useOnlyCssZoom: USE_ONLY_CSS_ZOOM,
			textLayerMode: AppOptions.get('textLayerMode'),
			_currentScaleValue: 'page-fit',
		});

		this.pdfViewer = pdfViewer;

		linkService.setViewer(pdfViewer);

		this.pdfHistory = new pdfjsViewer.PDFHistory({
			eventBus,
			linkService,
		});
		linkService.setHistory(this.pdfHistory);

		if (!this.supportsIntegratedFind) {
			this.findBar = new PDFFindBar(
				appConfig.findBar,
				eventBus,
				this.l10n,
			);
		}

		this.pdfCursorTools = new PDFCursorTools({
			container,
			eventBus,
			cursorToolOnLoad: CursorTool.HAND,
		});

		this.toolbar = new Toolbar(appConfig.toolbar, eventBus, false);

		//Set the user settings.
		eventBus.on('pagesinit', function () {
			let toolbarBottom = document.getElementById('toolbar-bottom');

			//Set the PDF SCALE
			self.pdfViewer.currentScaleValue = DEFAULT_SCALE_VALUE;
			self.pdfViewer.scrollMode = pdfemb_trans.continousscroll
				? ScrollMode.VERTICAL
				: ScrollMode.PAGE;
			//Number of pages
			document.getElementById('numPages').innerHTML = self.pagesCount;
			if (typeof toolbarBottom != 'undefined' && toolbarBottom != null) {
				document.getElementById('numPages-bottom').innerHTML =
					self.pagesCount;
			}
		});
		eventBus.on(
			'pagechanging',
			function (evt) {
				const page = evt.pageNumber;
				const numPages = self.pagesCount;
				let toolbarBottom = document.getElementById('toolbar-bottom');
				document.getElementById('pageNumber').value = page;
				document.getElementById('numPages').innerHTML = numPages;
				document.getElementById('previous').disabled = page <= 1;
				document.getElementById('next').disabled = page >= numPages;

				page >= numPages;

				document.getElementById('previous').disabled = page <= 1;
				document.getElementById('next').disabled = page >= numPages;

				if (
					typeof toolbarBottom != 'undefined' &&
					toolbarBottom != null
				) {
					document.getElementById('previous-bottom').disabled =
						page <= 1;
					document.getElementById('next-bottom').disabled =
						document.getElementById('pageNumber-bottom').value =
							page;
					document.getElementById('numPages-bottom').innerHTML =
						numPages;
					document.getElementById('previous-bottom').disabled =
						page <= 1;
					document.getElementById('next-bottom').disabled =
						page >= numPages;
				}
			},
			true,
		);
	},
	/**
	 * @private
	 */
	async _initializeMetadata(pdfDocument) {
		const { info, metadata, contentDispositionFilename, contentLength } =
			await pdfDocument.getMetadata();

		if (pdfDocument !== this.pdfDocument) {
			return; // The document was closed while the metadata resolved.
		}
		this.documentInfo = info;
		this.metadata = metadata;
		this._contentDispositionFilename ??= contentDispositionFilename;
		this._contentLength ??= contentLength; // See `getDownloadInfo`-call above.

		// Provides some basic debug information
		console.log(
			`PDF ${pdfDocument.fingerprints[0]} [${info.PDFFormatVersion} ` +
				`${(info.Producer || '-').trim()} / ${(
					info.Creator || '-'
				).trim()}] ` +
				`(PDF.js: ${version || '-'})`,
		);
		let pdfTitle = info.Title;

		const metadataTitle = metadata?.get('dc:title');
		if (metadataTitle) {
			// Ghostscript can produce invalid 'dc:title' Metadata entries:
			//  - The title may be "Untitled" (fixes bug 1031612).
			//  - The title may contain incorrectly encoded characters, which thus
			//    looks broken, hence we ignore the Metadata entry when it contains
			//    characters from the Specials Unicode block (fixes bug 1605526).
			if (
				metadataTitle !== 'Untitled' &&
				!/[\uFFF0-\uFFFF]/g.test(metadataTitle)
			) {
				pdfTitle = metadataTitle;
			}
		}
		if (pdfTitle) {
			this.setTitle(
				`${pdfTitle} - ${
					this._contentDispositionFilename || this._title
				}`,
			);
		} else if (this._contentDispositionFilename) {
			this.setTitle(this._contentDispositionFilename);
		}

		if (
			info.IsXFAPresent &&
			!info.IsAcroFormPresent &&
			!pdfDocument.isPureXfa
		) {
			if (pdfDocument.loadingParams.enableXfa) {
				console.warn(
					'Warning: XFA Foreground documents are not supported',
				);
			} else {
				console.warn('Warning: XFA support is not enabled');
			}
			this.fallback(UNSUPPORTED_FEATURES.forms);
		} else if (
			(info.IsAcroFormPresent || info.IsXFAPresent) &&
			!this.pdfViewer.renderForms
		) {
			console.warn('Warning: Interactive form support is not enabled');
			this.fallback(UNSUPPORTED_FEATURES.forms);
		}

		if (info.IsSignaturesPresent) {
			console.warn(
				'Warning: Digital signatures validation is not supported',
			);
			this.fallback(UNSUPPORTED_FEATURES.signatures);
		}

		// Telemetry labels must be C++ variable friendly.
		let versionId = 'other';
		if (KNOWN_VERSIONS.includes(info.PDFFormatVersion)) {
			versionId = `v${info.PDFFormatVersion.replace('.', '_')}`;
		}
		let generatorId = 'other';
		if (info.Producer) {
			const producer = info.Producer.toLowerCase();
			KNOWN_GENERATORS.some(function (generator) {
				if (!producer.includes(generator)) {
					return false;
				}
				generatorId = generator.replace(/[ .-]/g, '_');
				return true;
			});
		}
		let formType = null;
		if (info.IsXFAPresent) {
			formType = 'xfa';
		} else if (info.IsAcroFormPresent) {
			formType = 'acroform';
		}
		this.externalServices.reportTelemetry({
			type: 'documentInfo',
			version: versionId,
			generator: generatorId,
			formType,
		});

		this.eventBus.dispatch('metadataloaded', { source: this });
	},

	/**
	 * @private
	 */
	async _initializePageLabels(pdfDocument) {
		const labels = await pdfDocument.getPageLabels();

		if (pdfDocument !== this.pdfDocument) {
			return; // The document was closed while the page labels resolved.
		}
		if (!labels || AppOptions.get('disablePageLabels')) {
			return;
		}
		const numLabels = labels.length;
		// Ignore page labels that correspond to standard page numbering,
		// or page labels that are all empty.
		let standardLabels = 0,
			emptyLabels = 0;
		for (let i = 0; i < numLabels; i++) {
			const label = labels[i];
			if (label === (i + 1).toString()) {
				standardLabels++;
			} else if (label === '') {
				emptyLabels++;
			} else {
				break;
			}
		}
		if (standardLabels >= numLabels || emptyLabels >= numLabels) {
			return;
		}
		const { pdfViewer, pdfThumbnailViewer, toolbar } = this;

		pdfViewer.setPageLabels(labels);
		pdfThumbnailViewer.setPageLabels(labels);

		// Changing toolbar page display to use labels and we need to set
		// the label of the current page.
		toolbar.setPagesCount(numLabels, true);
		toolbar.setPageNumber(
			pdfViewer.currentPageNumber,
			pdfViewer.currentPageLabel,
		);
	},

	run(config) {
		this.initalize(config).then(WebViewInitialized);
	},
	/**
	 * Opens PDF document specified by URL.
	 * @returns {Promise} - Returns the promise, which is resolved when document
	 *                      is opened.
	 */
	async open(file, args) {
		const self = this;

		if (this.pdfLoadingTask) {
			// We need to destroy already opened document.
			await this.close();
		}

		const parameters = Object.create(null);
		if (typeof file === 'string') {
			// URL
			this.setTitleUsingUrl(file, /* downloadUrl = */ file);
			parameters.url = file;
		} else if (file && 'byteLength' in file) {
			// ArrayBuffer
			parameters.data = file;
		} else if (file.url && file.originalUrl) {
			this.setTitleUsingUrl(
				file.originalUrl,
				/* downloadUrl = */ file.url,
			);
			parameters.url = file.url;
		}

		// Finally, update the API parameters with the arguments (if they exist).
		if (args) {
			for (const key in args) {
				parameters[key] = args[key];
			}
		}
		let url = parameters.url;
		if (parameters.secure) {
			this._downloadUrl = parameters.download_url;
		} else {
			this._downloadUrl = url;
		}
		this._docFilename = parameters.file;
		parameters.cMapPacked = true;
		parameters.cMapUrl = pdfemb_trans.cmap_url;

		// Loading document.
		const loadingTask = pdfjsLib.getDocument(parameters);

		this.pdfLoadingTask = loadingTask;
		loadingTask.onPassword = (updateCallback, reason) => {
			this.pdfLinkService.externalLinkEnabled = false;
			this.passwordPrompt.setUpdateCallback(updateCallback, reason);
			this.passwordPrompt.open();
		};
		loadingTask.onProgress = function (progressData) {
			self.progress(progressData.loaded / progressData.total);
		};

		return loadingTask.promise.then(
			function (pdfDocument) {
				// Document loaded, specifying document for the viewer.
				self.pdfDocument = pdfDocument;
				self.pdfViewer.setDocument(pdfDocument);
				self.pdfLinkService.setDocument(pdfDocument);
				self.pdfHistory.initialize({
					fingerprint: pdfDocument.fingerprints[0],
				});
				webViewerZoomReset();
				self.loadingBar.hide();
				self.setTitleUsingMetadata(pdfDocument);
				self._initializePageLabels(pdfDocument);
			},
			function (exception) {
				const message = exception && exception.message;
				const l10n = self.l10n;
				let loadingErrorMessage;

				if (exception instanceof pdfjsLib.InvalidPDFException) {
					// change error message also for other builds
					loadingErrorMessage = l10n.get(
						'invalid_file_error',
						null,
						'Invalid or corrupted PDF file.',
					);
				} else if (exception instanceof pdfjsLib.MissingPDFException) {
					// special message for missing PDFs
					loadingErrorMessage = l10n.get(
						'missing_file_error',
						null,
						'Missing PDF file.',
					);
				} else if (
					exception instanceof pdfjsLib.UnexpectedResponseException
				) {
					loadingErrorMessage = l10n.get(
						'unexpected_response_error',
						null,
						'Unexpected server response.',
					);
				} else {
					loadingErrorMessage = l10n.get(
						'loading_error',
						null,
						'An error occurred while loading the PDF.',
					);
				}

				loadingErrorMessage.then(function (msg) {
					self.error(msg, { message });
				});
				self.loadingBar.hide();
			},
		);
	},

	/**
	 * Closes opened PDF document.
	 * @returns {Promise} - Returns the promise, which is resolved when all
	 *                      destruction is completed.
	 */
	close() {
		const errorWrapper = document.getElementById('errorWrapper');
		errorWrapper.hidden = true;

		if (!this.pdfLoadingTask) {
			return Promise.resolve();
		}

		const promise = this.pdfLoadingTask.destroy();
		this.pdfLoadingTask = null;

		if (this.pdfDocument) {
			this.pdfDocument = null;

			this.pdfViewer.setDocument(null);
			this.pdfLinkService.setDocument(null, null);

			if (this.pdfHistory) {
				this.pdfHistory.reset();
			}
		}

		return promise;
	},

	get loadingBar() {
		const bar = new pdfjsViewer.ProgressBar('loadingBar');

		return pdfjsLib.shadow(this, 'loadingBar', bar);
	},

	setTitleUsingUrl: function pdfViewSetTitleUsingUrl(url) {
		this.url = url;
		let title = pdfjsLib.getFilenameFromUrl(url) || url;
		try {
			title = decodeURIComponent(title);
		} catch (e) {
			// decodeURIComponent may throw URIError,
			// fall back to using the unprocessed url in that case
		}
		this.setTitle(title);
	},

	setTitleUsingMetadata(pdfDocument) {
		const self = this;
		pdfDocument.getMetadata().then(function (data) {
			const info = data.info,
				metadata = data.metadata;
			self.documentInfo = info;
			self.metadata = metadata;

			let pdfTitle;
			if (metadata && metadata.has('dc:title')) {
				const title = metadata.get('dc:title');
				// Ghostscript sometimes returns 'Untitled', so prevent setting the
				// title to 'Untitled.
				if (title !== 'Untitled') {
					pdfTitle = title;
				}
			}

			if (!pdfTitle && info && info.Title) {
				pdfTitle = info.Title;
			}

			if (pdfTitle) {
				self.setTitle(pdfTitle + ' - ' + document.title);
			}
		});
	},

	setTitle: function pdfViewSetTitle(title) {
		document.title = title;
	},

	error: function pdfViewError(message, moreInfo) {
		const l10n = this.l10n;
		const moreInfoText = [
			l10n.get(
				'error_version_info',
				{
					version: pdfjsLib.version || '?',
					build: pdfjsLib.build || '?',
				},
				'PDF.js v{{version}} (build: {{build}})',
			),
		];

		if (moreInfo) {
			moreInfoText.push(
				l10n.get(
					'error_message',
					{ message: moreInfo.message },
					'Message: {{message}}',
				),
			);
			if (moreInfo.stack) {
				moreInfoText.push(
					l10n.get(
						'error_stack',
						{ stack: moreInfo.stack },
						'Stack: {{stack}}',
					),
				);
			} else {
				if (moreInfo.filename) {
					moreInfoText.push(
						l10n.get(
							'error_file',
							{ file: moreInfo.filename },
							'File: {{file}}',
						),
					);
				}
				if (moreInfo.lineNumber) {
					moreInfoText.push(
						l10n.get(
							'error_line',
							{ line: moreInfo.lineNumber },
							'Line: {{line}}',
						),
					);
				}
			}
		}

		const errorWrapper = document.getElementById('errorWrapper');
		errorWrapper.hidden = false;

		const errorMessage = document.getElementById('errorMessage');
		errorMessage.textContent = message;

		const closeButton = document.getElementById('errorClose');
		closeButton.onclick = function () {
			errorWrapper.hidden = true;
		};

		const errorMoreInfo = document.getElementById('errorMoreInfo');
		const moreInfoButton = document.getElementById('errorShowMore');
		const lessInfoButton = document.getElementById('errorShowLess');
		moreInfoButton.onclick = function () {
			errorMoreInfo.hidden = false;
			moreInfoButton.hidden = true;
			lessInfoButton.hidden = false;
			errorMoreInfo.style.height = errorMoreInfo.scrollHeight + 'px';
		};
		lessInfoButton.onclick = function () {
			errorMoreInfo.hidden = true;
			moreInfoButton.hidden = false;
			lessInfoButton.hidden = true;
		};
		moreInfoButton.hidden = false;
		lessInfoButton.hidden = true;
		Promise.all(moreInfoText).then(function (parts) {
			errorMoreInfo.value = parts.join('\n');
		});
	},
	progress: function pdfViewProgress(level) {
		const percent = Math.round(level * 100);
		// Updating the bar if value increases.
		if (percent > this.loadingBar.percent || isNaN(percent)) {
			this.loadingBar.percent = percent;
		}
	},

	get pagesCount() {
		return this.pdfDocument.numPages;
	},

	get page() {
		return this.pdfViewer.currentPageNumber;
	},

	set page(val) {
		this.pdfViewer.currentPageNumber = val;
	},

	bindEvents() {
		const { eventBus, _boundEvents } = this;

		_boundEvents.beforePrint = this.beforePrint.bind(this);
		_boundEvents.afterPrint = this.afterPrint.bind(this);
		eventBus._on('resize', webViewerResize);
		eventBus._on('hashchange', webViewerHashchange);
		eventBus._on('beforeprint', _boundEvents.beforePrint);
		eventBus._on('afterprint', _boundEvents.afterPrint);
		eventBus._on('pagerendered', webViewerPageRendered);
		eventBus._on('updateviewarea', webViewerUpdateViewarea);
		eventBus._on('pagechanging', webViewerPageChanging);
		eventBus._on('scalechanging', webViewerScaleChanging);
		eventBus._on('rotationchanging', webViewerRotationChanging);
		eventBus._on('namedaction', webViewerNamedAction);
		eventBus._on(
			'presentationmodechanged',
			webViewerPresentationModeChanged,
		);
		eventBus._on('presentationmode', webViewerPresentationMode);
		eventBus._on(
			'switchannotationeditormode',
			webViewerSwitchAnnotationEditorMode,
		);
		eventBus._on(
			'switchannotationeditorparams',
			webViewerSwitchAnnotationEditorParams,
		);
		eventBus._on('print', webViewerPrint);
		eventBus._on('download', webViewerDownload);
		eventBus._on('fullscreen', webViewerFullscreen);
		eventBus._on('firstpage', webViewerFirstPage);
		eventBus._on('lastpage', webViewerLastPage);
		eventBus._on('nextpage', webViewerNextPage);
		eventBus._on('previouspage', webViewerPreviousPage);
		eventBus._on('zoomin', webViewerZoomIn);
		eventBus._on('zoomout', webViewerZoomOut);
		eventBus._on('zoomreset', webViewerZoomReset);
		eventBus._on('pagenumberchanged', webViewerPageNumberChanged);
		eventBus._on('scalechanged', webViewerScaleChanged);
		eventBus._on('rotatecw', webViewerRotateCw);
		eventBus._on('rotateccw', webViewerRotateCcw);
		eventBus._on('optionalcontentconfig', webViewerOptionalContentConfig);
		eventBus._on('switchscrollmode', webViewerSwitchScrollMode);
		eventBus._on('scrollmodechanged', webViewerScrollModeChanged);
		eventBus._on('spreadmodechanged', webViewerSpreadModeChanged);
		eventBus._on('documentproperties', webViewerDocumentProperties);
		eventBus._on('findfromurlhash', webViewerFindFromUrlHash);
		eventBus._on('updatefindmatchescount', webViewerUpdateFindMatchesCount);
		eventBus._on('updatefindcontrolstate', webViewerUpdateFindControlState);
	},
	bindWindowEvents() {
		const { eventBus, _boundEvents } = this;

		function addWindowResolutionChange(evt = null) {
			if (evt) {
				webViewerResolutionChange(evt);
			}
			const mediaQueryList = window.matchMedia(
				`(resolution: ${window.devicePixelRatio || 1}dppx)`,
			);
			mediaQueryList.addEventListener(
				'change',
				addWindowResolutionChange,
				{
					once: true,
				},
			);

			if (
				typeof PDFJSDev !== 'undefined' &&
				PDFJSDev.test('MOZCENTRAL')
			) {
				return;
			}
			_boundEvents.removeWindowResolutionChange ||= function () {
				mediaQueryList.removeEventListener(
					'change',
					addWindowResolutionChange,
				);
				_boundEvents.removeWindowResolutionChange = null;
			};
		}
		addWindowResolutionChange();

		_boundEvents.windowResize = () => {
			eventBus.dispatch('resize', { source: window });
		};
		_boundEvents.windowHashChange = () => {
			eventBus.dispatch('hashchange', {
				source: window,
				hash: document.location.hash.substring(1),
			});
		};
		_boundEvents.windowBeforePrint = () => {
			eventBus.dispatch('beforeprint', { source: window });
		};
		_boundEvents.windowAfterPrint = () => {
			eventBus.dispatch('afterprint', { source: window });
		};
		_boundEvents.windowUpdateFromSandbox = (event) => {
			eventBus.dispatch('updatefromsandbox', {
				source: window,
				detail: event.detail,
			});
		};

		window.addEventListener('visibilitychange', webViewerVisibilityChange);
		window.addEventListener('wheel', webViewerWheel, { passive: false });
		window.addEventListener('touchstart', webViewerTouchStart, {
			passive: false,
		});
		window.addEventListener('keydown', webViewerKeyDown);
		window.addEventListener('resize', _boundEvents.windowResize);
		window.addEventListener('hashchange', _boundEvents.windowHashChange);
		window.addEventListener('beforeprint', _boundEvents.windowBeforePrint);
		window.addEventListener('afterprint', _boundEvents.windowAfterPrint);
		window.addEventListener(
			'updatefromsandbox',
			_boundEvents.windowUpdateFromSandbox,
		);
	},
	unbindEvents() {
		if (typeof PDFJSDev !== 'undefined' && PDFJSDev.test('MOZCENTRAL')) {
			throw new Error('Not implemented: unbindEvents');
		}
		const { eventBus, _boundEvents } = this;

		eventBus._off('resize', webViewerResize);
		eventBus._off('hashchange', webViewerHashchange);
		eventBus._off('beforeprint', _boundEvents.beforePrint);
		eventBus._off('afterprint', _boundEvents.afterPrint);
		eventBus._off('pagerendered', webViewerPageRendered);
		eventBus._off('updateviewarea', webViewerUpdateViewarea);
		eventBus._off('pagechanging', webViewerPageChanging);
		eventBus._off('scalechanging', webViewerScaleChanging);
		eventBus._off('rotationchanging', webViewerRotationChanging);
		eventBus._off('namedaction', webViewerNamedAction);
		eventBus._off(
			'presentationmodechanged',
			webViewerPresentationModeChanged,
		);
		eventBus._off('presentationmode', webViewerPresentationMode);
		eventBus._off('print', webViewerPrint);
		eventBus._off('download', webViewerDownload);
		eventBus._off('fullscreen', webViewerFullscreen);
		eventBus._off('firstpage', webViewerFirstPage);
		eventBus._off('lastpage', webViewerLastPage);
		eventBus._off('nextpage', webViewerNextPage);
		eventBus._off('previouspage', webViewerPreviousPage);
		eventBus._off('zoomin', webViewerZoomIn);
		eventBus._off('zoomout', webViewerZoomOut);
		eventBus._off('zoomreset', webViewerZoomReset);
		eventBus._off('pagenumberchanged', webViewerPageNumberChanged);
		eventBus._off('scalechanged', webViewerScaleChanged);
		eventBus._off('rotatecw', webViewerRotateCw);
		eventBus._off('rotateccw', webViewerRotateCcw);
		eventBus._off('optionalcontentconfig', webViewerOptionalContentConfig);
		eventBus._off('switchscrollmode', webViewerSwitchScrollMode);
		eventBus._off('scrollmodechanged', webViewerScrollModeChanged);
		eventBus._off('switchspreadmode', webViewerSwitchSpreadMode);
		eventBus._off('spreadmodechanged', webViewerSpreadModeChanged);
		eventBus._off('documentproperties', webViewerDocumentProperties);
		eventBus._off('findfromurlhash', webViewerFindFromUrlHash);
		eventBus._off(
			'updatefindmatchescount',
			webViewerUpdateFindMatchesCount,
		);
		eventBus._off(
			'updatefindcontrolstate',
			webViewerUpdateFindControlState,
		);

		if (_boundEvents.reportPageStatsPDFBug) {
			eventBus._off('pagerendered', _boundEvents.reportPageStatsPDFBug);
			eventBus._off('pagechanging', _boundEvents.reportPageStatsPDFBug);

			_boundEvents.reportPageStatsPDFBug = null;
		}
		if (typeof PDFJSDev === 'undefined' || PDFJSDev.test('GENERIC')) {
			eventBus._off('fileinputchange', webViewerFileInputChange);
			eventBus._off('openfile', webViewerOpenFile);
		}

		_boundEvents.beforePrint = null;
		_boundEvents.afterPrint = null;
	},
	unbindWindowEvents() {
		if (typeof PDFJSDev !== 'undefined' && PDFJSDev.test('MOZCENTRAL')) {
			throw new Error('Not implemented: unbindWindowEvents');
		}
		const { _boundEvents } = this;

		window.removeEventListener(
			'visibilitychange',
			webViewerVisibilityChange,
		);
		window.removeEventListener('wheel', webViewerWheel, { passive: false });
		window.removeEventListener('touchstart', webViewerTouchStart, {
			passive: false,
		});
		window.removeEventListener('keydown', webViewerKeyDown);
		window.removeEventListener('resize', _boundEvents.windowResize);
		window.removeEventListener('hashchange', _boundEvents.windowHashChange);
		window.removeEventListener(
			'beforeprint',
			_boundEvents.windowBeforePrint,
		);
		window.removeEventListener('afterprint', _boundEvents.windowAfterPrint);
		window.removeEventListener(
			'updatefromsandbox',
			_boundEvents.windowUpdateFromSandbox,
		);

		_boundEvents.removeWindowResolutionChange?.();
		_boundEvents.windowResize = null;
		_boundEvents.windowHashChange = null;
		_boundEvents.windowBeforePrint = null;
		_boundEvents.windowAfterPrint = null;
		_boundEvents.windowUpdateFromSandbox = null;
	},

	/**
	 * @ignore
	 */
	_reportDocumentStatsTelemetry() {
		const { stats } = this.pdfDocument;
		if (stats !== this._docStats) {
			this._docStats = stats;

			this.externalServices.reportTelemetry({
				type: 'documentStats',
				stats,
			});
		}
	},
	beforePrint() {
		this._printAnnotationStoragePromise = this.pdfScriptingManager
			.dispatchWillPrint()
			.catch(() => {
				/* Avoid breaking printing; ignoring errors. */
			})
			.then(() => {
				return this.pdfDocument?.annotationStorage.print;
			});

		if (this.printService) {
			// There is no way to suppress beforePrint/afterPrint events,
			// but PDFPrintService may generate double events -- this will ignore
			// the second event that will be coming from native window.print().
			return;
		}

		if (!this.supportsPrinting) {
			this.l10n.get('printing_not_supported').then((msg) => {
				this._otherError(msg);
			});
			return;
		}

		// The beforePrint is a sync method and we need to know layout before
		// returning from this method. Ensure that we can get sizes of the pages.
		if (!this.pdfViewer.pageViewsReady) {
			this.l10n.get('printing_not_ready').then((msg) => {
				// eslint-disable-next-line no-alert
				window.alert(msg);
			});
			return;
		}

		const pagesOverview = this.pdfViewer.getPagesOverview();
		const printContainer = this.appConfig.printContainer;
		const printResolution = AppOptions.get('printResolution');
		const optionalContentConfigPromise =
			this.pdfViewer.optionalContentConfigPromise;

		const printService = PDFPrintServiceFactory.instance.createPrintService(
			this.pdfDocument,
			pagesOverview,
			printContainer,
			printResolution,
			optionalContentConfigPromise,
			this._printAnnotationStoragePromise,
			this.l10n,
		);
		this.printService = printService;
		this.forceRendering();

		printService.layout();

		this.externalServices.reportTelemetry({
			type: 'print',
		});

		if (this.pdfDocument?.annotationStorage.hasAnnotationEditors) {
			this.externalServices.reportTelemetry({
				type: 'editing',
				data: {
					type: 'print',
				},
			});
		}
	},
	/**
	 * @private
	 */
	_ensureDownloadComplete() {
		if (this.pdfDocument && this.downloadComplete) {
			return;
		}
		throw new Error('PDF document not downloaded.');
	},
	goFullscreen() {},
	async download() {
		const url = this._downloadUrl,
			filename = new URL(url).pathname.split('/').pop();

		let link = document.createElement('a');
		link.href = url;
		link.download = filename;
		link.dispatchEvent(new MouseEvent('click'));
	},

	afterPrint() {
		if (this._printAnnotationStoragePromise) {
			this._printAnnotationStoragePromise.then(() => {
				this.pdfScriptingManager.dispatchDidPrint();
			});
			this._printAnnotationStoragePromise = null;
		}

		if (this.printService) {
			this.printService.destroy();
			this.printService = null;

			this.pdfDocument?.annotationStorage.resetModified();
		}
		this.forceRendering();
	},

	zoomIn: function pdfViewZoomIn(ticks) {
		let newScale = this.pdfViewer.currentScale;
		do {
			newScale = (newScale * DEFAULT_SCALE_DELTA).toFixed(2);
			newScale = Math.ceil(newScale * 10) / 10;
			newScale = Math.min(MAX_SCALE, newScale);
		} while (--ticks && newScale < MAX_SCALE);
		this.pdfViewer.currentScaleValue = newScale;
	},

	zoomOut: function pdfViewZoomOut(ticks) {
		let newScale = this.pdfViewer.currentScale;
		do {
			newScale = (newScale / DEFAULT_SCALE_DELTA).toFixed(2);
			newScale = Math.floor(newScale * 10) / 10;
			newScale = Math.max(MIN_SCALE, newScale);
		} while (--ticks && newScale > MIN_SCALE);
		this.pdfViewer.currentScaleValue = newScale;
	},
	zoomReset() {
		if (this.pdfViewer.isInPresentationMode) {
			return;
		}
		this.pdfViewer.currentScaleValue = DEFAULT_SCALE_VALUE;
	},
	get pagesCount() {
		return this.pdfDocument ? this.pdfDocument.numPages : 0;
	},

	get page() {
		return this.pdfViewer.currentPageNumber;
	},

	set page(val) {
		this.pdfViewer.currentPageNumber = val;
	},

	setInitialView(
		storedHash,
		{ rotation, sidebarView, scrollMode, spreadMode } = {},
	) {
		const setRotation = (angle) => {
			if (isValidRotation(angle)) {
				this.pdfViewer.pagesRotation = angle;
			}
		};
		const setViewerModes = (scroll, spread) => {
			if (isValidScrollMode(scroll)) {
				this.pdfViewer.scrollMode = scroll;
			}
			if (isValidSpreadMode(spread)) {
				this.pdfViewer.spreadMode = spread;
			}
		};
		this.isInitialViewSet = true;
		error_log(ScrollMode.PAGE);
		setViewerModes(ScrollMode.PAGE, spreadMode);

		if (this.initialBookmark) {
			setRotation(this.initialRotation);
			delete this.initialRotation;

			this.pdfLinkService.setHash(this.initialBookmark);
			this.initialBookmark = null;
		} else if (storedHash) {
			setRotation(rotation);

			this.pdfLinkService.setHash(storedHash);
		}

		// Ensure that the correct page number is displayed in the UI,
		// even if the active page didn't change during document load.
		this.toolbar.setPageNumber(
			this.pdfViewer.currentPageNumber,
			this.pdfViewer.currentPageLabel,
		);

		if (!this.pdfViewer.currentScaleValue) {
			// Scale was not initialized: invalid bookmark or scale was not specified.
			// Setting the default one.
			this.pdfViewer.currentScaleValue = DEFAULT_SCALE_VALUE;
		}
	},
};
function webViewerHashchange(evt) {
	const hash = evt.hash;
	if (!hash) {
		return;
	}
	if (!PDFViewerApplication.isInitialViewSet) {
		PDFViewerApplication.initialBookmark = hash;
	} else if (!PDFViewerApplication.pdfHistory?.popStateInProgress) {
		PDFViewerApplication.pdfLinkService.setHash(hash);
	}
}
function webViewerPageRendered({ pageNumber, error }) {
	// If the page is still visible when it has finished rendering,
	// ensure that the page number input loading indicator is hidden.
	if (pageNumber === PDFViewerApplication.page) {
		PDFViewerApplication.toolbar.updateLoadingIndicatorState(false);
	}

	if (error) {
		PDFViewerApplication.l10n.get('rendering_error').then((msg) => {
			PDFViewerApplication._otherError(msg, error);
		});
	}

	// It is a good time to report stream and font types.
	PDFViewerApplication._reportDocumentStatsTelemetry();
}
function webViewerUpdateViewarea({ location }) {
	if (PDFViewerApplication.isInitialViewSet) {
		// Only update the storage when the document has been loaded *and* rendered.
		PDFViewerApplication.store
			?.setMultiple({
				page: location.pageNumber,
				zoom: location.scale,
				scrollLeft: location.left,
				scrollTop: location.top,
				rotation: location.rotation,
			})
			.catch(() => {
				// Unable to write to storage.
			});
	}

	// Show/hide the loading indicator in the page number input element.
	const currentPage = PDFViewerApplication.pdfViewer.getPageView(
		/* index = */ PDFViewerApplication.page - 1,
	);
	const loading = currentPage?.renderingState !== RenderingStates.FINISHED;
	PDFViewerApplication.toolbar.updateLoadingIndicatorState(loading);
}

function webViewerResize() {
	const { pdfDocument, pdfViewer } = PDFViewerApplication;

	pdfViewer.updateContainerHeightCss();

	if (!pdfDocument) {
		return;
	}
	const currentScaleValue = pdfViewer.currentScaleValue;
	if (
		currentScaleValue === 'auto' ||
		currentScaleValue === 'page-fit' ||
		currentScaleValue === 'page-width'
	) {
		// Note: the scale is constant for 'page-actual'.
		pdfViewer.currentScaleValue = currentScaleValue;
	}
	pdfViewer.update();
}
const WebViewInitialized = () => {
	const { appConfig, eventBus } = PDFViewerApplication;
	let file;
	const queryString = document.location.search.substring(1);
	const params = parseQueryString(queryString);
	file = params.get('url') ?? AppOptions.get('defaultUrl');

	if (file.search('/?pdfemb-serveurl=') == -1) {
		PDFViewerApplication.open(file);
	} else {
		document.oncontextmenu = function () {
			return false;
		};
		pdfembGetPDF(file);
	}
};

function webViewerScaleChanging(evt) {
	PDFViewerApplication.toolbar.setPageScale(evt.presetValue, evt.scale);

	PDFViewerApplication.pdfViewer.update();
}

function webViewerRotationChanging(evt) {
	PDFViewerApplication.pdfThumbnailViewer.pagesRotation = evt.pagesRotation;

	PDFViewerApplication.forceRendering();
	// Ensure that the active page doesn't change during rotation.
	PDFViewerApplication.pdfViewer.currentPageNumber = evt.pageNumber;
}

function webViewerPageChanging({ pageNumber, pageLabel }) {
	PDFViewerApplication.toolbar.setPageNumber(pageNumber, pageLabel);
}

function webViewerResolutionChange(evt) {
	PDFViewerApplication.pdfViewer.refresh();
}
function webViewerPresentationModeChanged(evt) {
	PDFViewerApplication.pdfViewer.presentationModeState = evt.state;
}
function webViewerSwitchAnnotationEditorMode(evt) {
	PDFViewerApplication.pdfViewer.annotationEditorMode = evt.mode;
}
function webViewerPrint() {
	PDFViewerApplication.triggerPrinting();
}
function webViewerFullscreen() {
	PDFViewerApplication.goFullscreen();
}
function webViewerDownload() {
	PDFViewerApplication.download();
}
function webViewerFirstPage() {
	if (PDFViewerApplication.pdfDocument) {
		PDFViewerApplication.page = 1;
	}
}
function webViewerLastPage() {
	if (PDFViewerApplication.pdfDocument) {
		PDFViewerApplication.page = PDFViewerApplication.pagesCount;
	}
}
function webViewerSwitchAnnotationEditorParams(evt) {
	PDFViewerApplication.pdfViewer.annotationEditorParams = evt;
}
function webViewerNextPage() {
	PDFViewerApplication.pdfViewer.nextPage();
}
function webViewerPreviousPage() {
	PDFViewerApplication.pdfViewer.previousPage();
}
function webViewerZoomIn() {
	PDFViewerApplication.zoomIn();
}
function webViewerZoomOut() {
	PDFViewerApplication.zoomOut();
}
function webViewerZoomReset() {
	PDFViewerApplication.zoomReset();
}
function webViewerNamedAction(evt) {
	// Processing a couple of named actions that might be useful, see also
	// `PDFLinkService.executeNamedAction`.
	switch (evt.action) {
		case 'GoToPage':
			PDFViewerApplication.appConfig.toolbar.pageNumber.select();
			break;

		case 'Find':
			if (!PDFViewerApplication.supportsIntegratedFind) {
				PDFViewerApplication.findBar.toggle();
			}
			break;

		case 'Print':
			PDFViewerApplication.triggerPrinting();
			break;

		case 'SaveAs':
			PDFViewerApplication.downloadOrSave();
			break;
	}
}
function webViewerPresentationMode() {
	PDFViewerApplication.requestPresentationMode();
}

let zoomDisabledTimeout = null;
function setZoomDisabledTimeout() {
	if (zoomDisabledTimeout) {
		clearTimeout(zoomDisabledTimeout);
	}
	zoomDisabledTimeout = setTimeout(function () {
		zoomDisabledTimeout = null;
	}, WHEEL_ZOOM_DISABLED_TIMEOUT);
}

function webViewerPageNumberChanged(evt) {
	const pdfViewer = PDFViewerApplication.pdfViewer;
	// Note that for `<input type="number">` HTML elements, an empty string will
	// be returned for non-number inputs; hence we simply do nothing in that case.
	if (evt.value !== '') {
		PDFViewerApplication.pdfLinkService.goToPage(evt.value);
	}

	// Ensure that the page number input displays the correct value, even if the
	// value entered by the user was invalid (e.g. a floating point number).
	if (
		evt.value !== pdfViewer.currentPageNumber.toString() &&
		evt.value !== pdfViewer.currentPageLabel
	) {
		PDFViewerApplication.toolbar.setPageNumber(
			pdfViewer.currentPageNumber,
			pdfViewer.currentPageLabel,
		);
	}
}
function webViewerScrollModeChanged(evt) {
	if (PDFViewerApplication.isInitialViewSet) {
		// Only update the storage when the document has been loaded *and* rendered.
		PDFViewerApplication.store?.set('scrollMode', evt.mode).catch(() => {
			// Unable to write to storage.
		});
	}
}
function webViewerUpdateFindMatchesCount({ matchesCount }) {
	if (PDFViewerApplication.supportsIntegratedFind) {
		PDFViewerApplication.externalServices.updateFindMatchesCount(
			matchesCount,
		);
	} else {
		PDFViewerApplication.findBar.updateResultsCount(matchesCount);
	}
}

function webViewerWheel(evt) {
	const { pdfViewer, supportedMouseWheelZoomModifierKeys } =
		PDFViewerApplication;

	if (pdfViewer.isInPresentationMode) {
		return;
	}

	if (
		(evt.ctrlKey && supportedMouseWheelZoomModifierKeys.ctrlKey) ||
		(evt.metaKey && supportedMouseWheelZoomModifierKeys.metaKey)
	) {
		// Only zoom the pages, not the entire viewer.
		evt.preventDefault();
		// NOTE: this check must be placed *after* preventDefault.
		if (zoomDisabledTimeout || document.visibilityState === 'hidden') {
			return;
		}

		// It is important that we query deltaMode before delta{X,Y}, so that
		// Firefox doesn't switch to DOM_DELTA_PIXEL mode for compat with other
		// browsers, see https://bugzilla.mozilla.org/show_bug.cgi?id=1392460.
		const deltaMode = evt.deltaMode;
		const delta = normalizeWheelEventDirection(evt);
		const previousScale = pdfViewer.currentScale;

		let ticks = 0;
		if (
			deltaMode === WheelEvent.DOM_DELTA_LINE ||
			deltaMode === WheelEvent.DOM_DELTA_PAGE
		) {
			// For line-based devices, use one tick per event, because different
			// OSs have different defaults for the number lines. But we generally
			// want one "clicky" roll of the wheel (which produces one event) to
			// adjust the zoom by one step.
			if (Math.abs(delta) >= 1) {
				ticks = Math.sign(delta);
			} else {
				// If we're getting fractional lines (I can't think of a scenario
				// this might actually happen), be safe and use the accumulator.
				ticks = PDFViewerApplication.accumulateWheelTicks(delta);
			}
		} else {
			// pixel-based devices
			const PIXELS_PER_LINE_SCALE = 30;
			ticks = PDFViewerApplication.accumulateWheelTicks(
				delta / PIXELS_PER_LINE_SCALE,
			);
		}

		if (ticks < 0) {
			PDFViewerApplication.zoomOut(-ticks);
		} else if (ticks > 0) {
			PDFViewerApplication.zoomIn(ticks);
		}

		const currentScale = pdfViewer.currentScale;
		if (previousScale !== currentScale) {
			// After scaling the page via zoomIn/zoomOut, the position of the upper-
			// left corner is restored. When the mouse wheel is used, the position
			// under the cursor should be restored instead.
			const scaleCorrectionFactor = currentScale / previousScale - 1;
			const rect = pdfViewer.container.getBoundingClientRect();
			const dx = evt.clientX - rect.left;
			const dy = evt.clientY - rect.top;
			pdfViewer.container.scrollLeft += dx * scaleCorrectionFactor;
			pdfViewer.container.scrollTop += dy * scaleCorrectionFactor;
		}
	} else {
		setZoomDisabledTimeout();
	}
}

function webViewerTouchStart(evt) {
	if (evt.touches.length > 1) {
		// Disable touch-based zooming, because the entire UI bits gets zoomed and
		// that doesn't look great. If we do want to have a good touch-based
		// zooming experience, we need to implement smooth zoom capability (probably
		// using a CSS transform for faster visual response, followed by async
		// re-rendering at the final zoom level) and do gesture detection on the
		// touchmove events to drive it. Or if we want to settle for a less good
		// experience we can make the touchmove events drive the existing step-zoom
		// behaviour that the ctrl+mousewheel path takes.
		evt.preventDefault();
	}
}

function webViewerKeyDown(evt) {
	if (PDFViewerApplication.overlayManager.active) {
		return;
	}
	const { eventBus, pdfViewer } = PDFViewerApplication;
	const isViewerInPresentationMode = pdfViewer.isInPresentationMode;

	let handled = false,
		ensureViewerFocused = false;
	const cmd =
		(evt.ctrlKey ? 1 : 0) |
		(evt.altKey ? 2 : 0) |
		(evt.shiftKey ? 4 : 0) |
		(evt.metaKey ? 8 : 0);

	// First, handle the key bindings that are independent whether an input
	// control is selected or not.
	if (cmd === 1 || cmd === 8 || cmd === 5 || cmd === 12) {
		// either CTRL or META key with optional SHIFT.
		switch (evt.keyCode) {
			case 70: // f
				if (
					!PDFViewerApplication.supportsIntegratedFind &&
					!evt.shiftKey
				) {
					PDFViewerApplication.findBar.open();
					handled = true;
				}
				break;
			case 71: // g
				if (!PDFViewerApplication.supportsIntegratedFind) {
					const { state } = PDFViewerApplication.findController;
					if (state) {
						const eventState = Object.assign(
							Object.create(null),
							state,
							{
								source: window,
								type: 'again',
								findPrevious: cmd === 5 || cmd === 12,
							},
						);
						eventBus.dispatch('find', eventState);
					}
					handled = true;
				}
				break;
			case 61: // FF/Mac '='
			case 107: // FF '+' and '='
			case 187: // Chrome '+'
			case 171: // FF with German keyboard
				if (!isViewerInPresentationMode) {
					PDFViewerApplication.zoomIn();
				}
				handled = true;
				break;
			case 173: // FF/Mac '-'
			case 109: // FF '-'
			case 189: // Chrome '-'
				if (!isViewerInPresentationMode) {
					PDFViewerApplication.zoomOut();
				}
				handled = true;
				break;
			case 48: // '0'
			case 96: // '0' on Numpad of Swedish keyboard
				if (!isViewerInPresentationMode) {
					// keeping it unhandled (to restore page zoom to 100%)
					setTimeout(function () {
						// ... and resetting the scale after browser adjusts its scale
						PDFViewerApplication.zoomReset();
					});
					handled = false;
				}
				break;

			case 38: // up arrow
				if (
					isViewerInPresentationMode ||
					PDFViewerApplication.page > 1
				) {
					PDFViewerApplication.page = 1;
					handled = true;
					ensureViewerFocused = true;
				}
				break;
			case 40: // down arrow
				if (
					isViewerInPresentationMode ||
					PDFViewerApplication.page < PDFViewerApplication.pagesCount
				) {
					PDFViewerApplication.page = PDFViewerApplication.pagesCount;
					handled = true;
					ensureViewerFocused = true;
				}
				break;
		}
	}

	if (typeof PDFJSDev === 'undefined' || PDFJSDev.test('GENERIC || CHROME')) {
		// CTRL or META without shift
		if (cmd === 1 || cmd === 8) {
			switch (evt.keyCode) {
				case 83: // s
					eventBus.dispatch('download', { source: window });
					handled = true;
					break;

				case 79: // o
					if (
						typeof PDFJSDev === 'undefined' ||
						PDFJSDev.test('GENERIC')
					) {
						eventBus.dispatch('openfile', { source: window });
						handled = true;
					}
					break;
			}
		}
	}

	// CTRL+ALT or Option+Command
	if (cmd === 3 || cmd === 10) {
		switch (evt.keyCode) {
			case 80: // p
				PDFViewerApplication.requestPresentationMode();
				handled = true;
				break;
			case 71: // g
				// focuses input#pageNumber field
				PDFViewerApplication.appConfig.toolbar.pageNumber.select();
				handled = true;
				break;
		}
	}

	if (handled) {
		if (ensureViewerFocused && !isViewerInPresentationMode) {
			pdfViewer.focus();
		}
		evt.preventDefault();
		return;
	}

	// Some shortcuts should not get handled if a control/input element
	// is selected.
	const curElement = getActiveOrFocusedElement();
	const curElementTagName = curElement?.tagName.toUpperCase();
	if (
		curElementTagName === 'INPUT' ||
		curElementTagName === 'TEXTAREA' ||
		curElementTagName === 'SELECT' ||
		curElement?.isContentEditable
	) {
		// Make sure that the secondary toolbar is closed when Escape is pressed.
		if (evt.keyCode !== /* Esc = */ 27) {
			return;
		}
	}

	// No control key pressed at all.
	if (cmd === 0) {
		let turnPage = 0,
			turnOnlyIfPageFit = false;
		switch (evt.keyCode) {
			case 38: // up arrow
			case 33: // pg up
				// vertical scrolling using arrow/pg keys
				if (pdfViewer.isVerticalScrollbarEnabled) {
					turnOnlyIfPageFit = true;
				}
				turnPage = -1;
				break;
			case 8: // backspace
				if (!isViewerInPresentationMode) {
					turnOnlyIfPageFit = true;
				}
				turnPage = -1;
				break;
			case 37: // left arrow
				// horizontal scrolling using arrow keys
				if (pdfViewer.isHorizontalScrollbarEnabled) {
					turnOnlyIfPageFit = true;
				}
			/* falls through */
			case 75: // 'k'
			case 80: // 'p'
				turnPage = -1;
				break;
			case 27: // esc key
				if (
					!PDFViewerApplication.supportsIntegratedFind &&
					PDFViewerApplication.findBar.opened
				) {
					PDFViewerApplication.findBar.close();
					handled = true;
				}
				break;
			case 40: // down arrow
			case 34: // pg down
				// vertical scrolling using arrow/pg keys
				if (pdfViewer.isVerticalScrollbarEnabled) {
					turnOnlyIfPageFit = true;
				}
				turnPage = 1;
				break;
			case 13: // enter key
			case 32: // spacebar
				if (!isViewerInPresentationMode) {
					turnOnlyIfPageFit = true;
				}
				turnPage = 1;
				break;
			case 39: // right arrow
				// horizontal scrolling using arrow keys
				if (pdfViewer.isHorizontalScrollbarEnabled) {
					turnOnlyIfPageFit = true;
				}
			/* falls through */
			case 74: // 'j'
			case 78: // 'n'
				turnPage = 1;
				break;

			case 36: // home
				if (
					isViewerInPresentationMode ||
					PDFViewerApplication.page > 1
				) {
					PDFViewerApplication.page = 1;
					handled = true;
					ensureViewerFocused = true;
				}
				break;
			case 35: // end
				if (
					isViewerInPresentationMode ||
					PDFViewerApplication.page < PDFViewerApplication.pagesCount
				) {
					PDFViewerApplication.page = PDFViewerApplication.pagesCount;
					handled = true;
					ensureViewerFocused = true;
				}
				break;

			case 83: // 's'
				PDFViewerApplication.pdfCursorTools.switchTool(
					CursorTool.SELECT,
				);
				break;
			case 72: // 'h'
				PDFViewerApplication.pdfCursorTools.switchTool(CursorTool.HAND);
				break;

			case 82: // 'r'
				PDFViewerApplication.rotatePages(90);
				break;
		}

		if (
			turnPage !== 0 &&
			(!turnOnlyIfPageFit || pdfViewer.currentScaleValue === 'page-fit')
		) {
			if (turnPage > 0) {
				pdfViewer.nextPage();
			} else {
				pdfViewer.previousPage();
			}
			handled = true;
		}
	}

	// shift-key
	if (cmd === 4) {
		switch (evt.keyCode) {
			case 13: // enter key
			case 32: // spacebar
				if (
					!isViewerInPresentationMode &&
					pdfViewer.currentScaleValue !== 'page-fit'
				) {
					break;
				}
				pdfViewer.previousPage();

				handled = true;
				break;

			case 82: // 'r'
				PDFViewerApplication.rotatePages(-90);
				break;
		}
	}

	if (!handled && !isViewerInPresentationMode) {
		// 33=Page Up  34=Page Down  35=End    36=Home
		// 37=Left     38=Up         39=Right  40=Down
		// 32=Spacebar
		if (
			(evt.keyCode >= 33 && evt.keyCode <= 40) ||
			(evt.keyCode === 32 && curElementTagName !== 'BUTTON')
		) {
			ensureViewerFocused = true;
		}
	}

	if (ensureViewerFocused && !pdfViewer.containsElement(curElement)) {
		// The page container is not focused, but a page navigation key has been
		// pressed. Change the focus to the viewer container to make sure that
		// navigation by keyboard works as expected.
		pdfViewer.focus();
	}

	if (handled) {
		evt.preventDefault();
	}
}

function webViewerUpdateFindControlState({
	state,
	previous,
	matchesCount,
	rawQuery,
}) {
	if (PDFViewerApplication.supportsIntegratedFind) {
		PDFViewerApplication.externalServices.updateFindControlState({
			result: state,
			findPrevious: previous,
			matchesCount,
			rawQuery,
		});
	} else {
		PDFViewerApplication.findBar.updateUIState(
			state,
			previous,
			matchesCount,
		);
	}
}

function webViewerFindFromUrlHash(evt) {
	PDFViewerApplication.eventBus.dispatch('find', {
		source: evt.source,
		type: '',
		query: evt.query,
		phraseSearch: evt.phraseSearch,
		caseSensitive: false,
		entireWord: false,
		highlightAll: true,
		findPrevious: false,
		matchDiacritics: true,
	});
}
function webViewerSpreadModeChanged(evt) {
	if (PDFViewerApplication.isInitialViewSet) {
		// Only update the storage when the document has been loaded *and* rendered.
		PDFViewerApplication.store?.set('spreadMode', evt.mode).catch(() => {
			// Unable to write to storage.
		});
	}
}
function webViewerScaleChanged(evt) {
	PDFViewerApplication.pdfViewer.currentScaleValue = evt.value;
}
function webViewerRotateCw() {
	PDFViewerApplication.rotatePages(90);
}
function webViewerRotateCcw() {
	PDFViewerApplication.rotatePages(-90);
}
function webViewerOptionalContentConfig(evt) {
	PDFViewerApplication.pdfViewer.optionalContentConfigPromise = evt.promise;
}
function webViewerSwitchScrollMode(evt) {
	PDFViewerApplication.pdfViewer.scrollMode = evt.mode;
}
function webViewerSwitchSpreadMode(evt) {
	PDFViewerApplication.pdfViewer.spreadMode = evt.mode;
}
function webViewerDocumentProperties() {
	PDFViewerApplication.pdfDocumentProperties.open();
}
function webViewerVisibilityChange(evt) {
	if (document.visibilityState === 'visible') {
		// Ignore mouse wheel zooming during tab switches (bug 1503412).
		setZoomDisabledTimeout();
	}
}
window.PDFViewerApplication = PDFViewerApplication;

function pdfembGetPDF(url, callback) {
	//   Get PDF directly
	if (url.search('/?pdfemb-serveurl=') == -1) {
		return false;
	}
	let download_url = url;
	pdfembAddAjaxBufferTransport();
	jQuery
		.ajax({
			dataType: 'arraybuffer',
			type: 'POST',
			url: url,
		})
		.done(function (blob) {
			let uia = new Uint8Array(blob);

			let args = {
				secure: true,
				download_url: window.location.origin + download_url,
			};
			PDFViewerApplication.open(uia, args);
		})
		.fail(function (jqXHR, textStatus, errorThrown) {
			return false;
		});
}

function pdfembAddAjaxBufferTransport() {
	let pdfembAddAjaxBufferTransport_added = false;

	if (pdfembAddAjaxBufferTransport_added) {
		return;
	}
	pdfembAddAjaxBufferTransport_added = true;

	// http://www.artandlogic.com/blog/2013/11/jquery-ajax-blobs-and-array-buffers/
	/**
	 * Register ajax transports for blob send/recieve and array buffer send/receive via XMLHttpRequest Level 2
	 * within the comfortable framework of the jquery ajax request, with full support for promises.
	 *
	 * Notice the +* in the dataType string? The + indicates we want this transport to be prepended to the list
	 * of potential transports (so it gets first dibs if the request passes the conditions within to provide the
	 * ajax transport, preventing the standard transport from hogging the request), and the * indicates that
	 * potentially any request with any dataType might want to use the transports provided herein.
	 *
	 * Remember to specify 'processData:false' in the ajax options when attempting to send a blob or arraybuffer -
	 * otherwise jquery will try (and fail) to convert the blob or buffer into a query string.
	 */
	jQuery.ajaxTransport('+*', function (options, originalOptions, jqXHR) {
		// Test for the conditions that mean we can/want to send/receive blobs or arraybuffers - we need XMLHttpRequest
		// level 2 (so feature-detect against window.FormData), feature detect against window.Blob or window.ArrayBuffer,
		// and then check to see if the dataType is blob/arraybuffer or the data itself is a Blob/ArrayBuffer
		if (
			window.FormData &&
			((options.dataType &&
				(options.dataType == 'blob' ||
					options.dataType == 'arraybuffer')) ||
				(options.data &&
					((window.Blob && options.data instanceof Blob) ||
						(window.ArrayBuffer &&
							options.data instanceof ArrayBuffer))))
		) {
			return {
				/**
				 * Return a transport capable of sending and/or receiving blobs - in this case, we instantiate
				 * a new XMLHttpRequest and use it to actually perform the request, and funnel the result back
				 * into the jquery complete callback (such as the success function, done blocks, etc.)
				 *
				 * @param headers
				 * @param completeCallback
				 */
				send: function (headers, completeCallback) {
					var xhr = new XMLHttpRequest(),
						url = options.url || window.location.href,
						type = options.type || 'GET',
						dataType = options.dataType || 'text',
						data = options.data || null,
						async = options.async || true;

					xhr.addEventListener('load', function () {
						var res = {};

						res[dataType] = xhr.response;
						completeCallback(
							xhr.status,
							xhr.statusText,
							res,
							xhr.getAllResponseHeaders(),
						);
					});

					xhr.open(type, url, async);
					xhr.responseType = dataType;
					xhr.send(data);
				},
				abort: function () {
					jqXHR.abort();
				},
			};
		}
	});
}

function pdfemb_rc4ab(key, ab) {
	var s = [],
		j = 0,
		x,
		res = '',
		input,
		output;
	var dv = new DataView(ab);

	// Check for Unicode BOM and skip it
	var starty = 0;
	if (
		dv.getUint8(0) == 0xef &&
		dv.getUint8(1) == 0xbb &&
		dv.getUint8(2) == 0xbf
	) {
		starty = 3;
	}

	// Decrypt
	for (var i = 0; i < 256; i++) {
		s[i] = i;
	}
	for (i = 0; i < 256; i++) {
		j = (j + s[i] + key.charCodeAt(i % key.length)) % 256;
		x = s[i];
		s[i] = s[j];
		s[j] = x;
	}
	i = 0;
	j = 0;
	for (var y = starty; y < ab.byteLength; y++) {
		i = (i + 1) % 256;
		j = (j + s[i]) % 256;
		x = s[i];
		s[i] = s[j];
		s[j] = x;
		input = dv.getUint8(y);
		output = input ^ s[(s[i] + s[j]) % 256];
		dv.setUint8(y, output);
	}
}

export { PDFViewerApplication };
