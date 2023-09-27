import { PDFViewerApplication } from './viewer/app';

const getViewerConfiguration = () => {
	return {
		appContainer: document.body,
		mainContainer: document.getElementById('viewerContainer'),
		viewerContainer: document.getElementById('viewer'),
		toolbar: {
			container: document.getElementById('toolbarViewer'),
			numPages: document.getElementsByClassName('numPages'),
			pageNumber: document.getElementsByClassName('pageNumber'),
			scaleSelect: document.getElementsByClassName('scaleSelect'),
			customScaleOption: document.getElementById('customScaleOption'),
			previous: document.getElementsByClassName('previousButton'),
			next: document.getElementsByClassName('nextButton'),
			zoomIn: document.getElementsByClassName('zoomIn'),
			zoomOut: document.getElementsByClassName('zoomOut'),
			viewFind: document.getElementsByClassName('viewFind-test'),
			print: document.getElementById('print'),
			download: document.getElementsByClassName('download'),
			fullscreen: document.getElementsByClassName(
				'wppdf-fullscreen-button',
			),
		},
		findBar: {
			bar: document.getElementsByClassName('findbar'),
			toggleButton: document.getElementsByClassName('viewFind'),
			findField: document.getElementsByClassName('findInput'),
			highlightAllCheckbox:
				document.getElementsByClassName('findHighlightAll'),
			caseSensitiveCheckbox:
				document.getElementsByClassName('findMatchCase'),
			matchDiacriticsCheckbox: document.getElementsByClassName(
				'findMatchDiacritics',
			),
			entireWordCheckbox:
				document.getElementsByClassName('findEntireWord'),
			findMsg: document.getElementsByClassName('findMsg'),
			findResultsCount:
				document.getElementsByClassName('findResultsCount'),
			findPreviousButton: document.getElementsByClassName('findPrevious'),
			findNextButton: document.getElementsByClassName('findNext'),
		},
	};
};
const WebViewLoad = () => {
	const config = getViewerConfiguration();
	PDFViewerApplication.run(config);
};

document.addEventListener('DOMContentLoaded', WebViewLoad, true);
