/*
 * ATTENTION: An "eval-source-map" devtool has been used.
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file with attached SourceMaps in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/js/metabox/index.js":
/*!*********************************!*\
  !*** ./src/js/metabox/index.js ***!
  \*********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var scss_metabox_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! scss/metabox.scss */ \"./src/scss/metabox.scss\");\n\n\n/**\n * WordPress dependencies\n */\nconst {\n  __\n} = wp.i18n;\nwindow.addEventListener('load', () => {\n  let $delete = document.querySelectorAll('.fillablepdfs-metabox__documents-delete');\n  if ($delete.length === 0) {\n    return;\n  }\n  $delete.forEach($del => {\n    $del.addEventListener('click', async e => {\n      e.preventDefault();\n      if (!confirm(__('Are you sure you want to delete this PDF?', 'forgravity_fillablepdfs'))) {\n        return false;\n      }\n      let formData = new FormData();\n      formData.append('action', 'fg_fillablepdfs_metabox_delete');\n      formData.append('pdfId', e.target.dataset.pdfId);\n      formData.append('nonce', e.target.dataset.nonce);\n      const fetchParams = {\n        method: 'POST',\n        body: formData\n      };\n      console.log(fetchParams);\n      await fetch(ajaxurl, fetchParams).then(response => response.json()).then(response => {\n        if (response.success) {\n          e.target.parentNode.parentNode.remove();\n        } else {\n          alert(response.data);\n        }\n      });\n    });\n  });\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9zcmMvanMvbWV0YWJveC9pbmRleC5qcy5qcyIsIm1hcHBpbmdzIjoiOztBQUFBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQUE7QUFBQTtBQUVBO0FBRUE7QUFFQTtBQUNBO0FBQ0E7QUFFQTtBQUVBO0FBRUE7QUFFQTtBQUNBO0FBQ0E7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBRUE7QUFFQTtBQUlBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFFQTtBQUVBO0FBRUE7QUFFQSIsInNvdXJjZXMiOlsid2VicGFjazovL2ZvcmdyYXZpdHktZmlsbGFibGVwZGZzLy4vc3JjL2pzL21ldGFib3gvaW5kZXguanM/M2VhZCJdLCJzb3VyY2VzQ29udGVudCI6WyJpbXBvcnQgJ3Njc3MvbWV0YWJveC5zY3NzJztcblxuLyoqXG4gKiBXb3JkUHJlc3MgZGVwZW5kZW5jaWVzXG4gKi9cbmNvbnN0IHsgX18gfSA9IHdwLmkxOG47XG5cbndpbmRvdy5hZGRFdmVudExpc3RlbmVyKCAnbG9hZCcsICgpID0+IHtcblxuXHRsZXQgJGRlbGV0ZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoICcuZmlsbGFibGVwZGZzLW1ldGFib3hfX2RvY3VtZW50cy1kZWxldGUnICk7XG5cblx0aWYgKCAkZGVsZXRlLmxlbmd0aCA9PT0gMCApIHtcblx0XHRyZXR1cm47XG5cdH1cblxuXHQkZGVsZXRlLmZvckVhY2goICggJGRlbCApID0+IHtcblxuXHRcdCRkZWwuYWRkRXZlbnRMaXN0ZW5lciggJ2NsaWNrJywgYXN5bmMgKCBlICkgPT4ge1xuXG5cdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0XHRcdGlmICggISBjb25maXJtKCBfXyggJ0FyZSB5b3Ugc3VyZSB5b3Ugd2FudCB0byBkZWxldGUgdGhpcyBQREY/JywgJ2ZvcmdyYXZpdHlfZmlsbGFibGVwZGZzJyApICkgKSB7XG5cdFx0XHRcdHJldHVybiBmYWxzZTtcblx0XHRcdH1cblxuXHRcdFx0bGV0IGZvcm1EYXRhID0gbmV3IEZvcm1EYXRhKCk7XG5cdFx0XHRmb3JtRGF0YS5hcHBlbmQoICdhY3Rpb24nLCAnZmdfZmlsbGFibGVwZGZzX21ldGFib3hfZGVsZXRlJyApO1xuXHRcdFx0Zm9ybURhdGEuYXBwZW5kKCAncGRmSWQnLCBlLnRhcmdldC5kYXRhc2V0LnBkZklkICk7XG5cdFx0XHRmb3JtRGF0YS5hcHBlbmQoICdub25jZScsIGUudGFyZ2V0LmRhdGFzZXQubm9uY2UgKTtcblxuXHRcdFx0Y29uc3QgZmV0Y2hQYXJhbXMgPSB7XG5cdFx0XHRcdG1ldGhvZDogJ1BPU1QnLFxuXHRcdFx0XHRib2R5OiAgIGZvcm1EYXRhXG5cdFx0XHR9O1xuXG5cdFx0XHRjb25zb2xlLmxvZyggZmV0Y2hQYXJhbXMgKTtcblxuXHRcdFx0YXdhaXQgZmV0Y2goIGFqYXh1cmwsIGZldGNoUGFyYW1zIClcblx0XHRcdFx0LnRoZW4oICggcmVzcG9uc2UgKSA9PiByZXNwb25zZS5qc29uKCkgKVxuXHRcdFx0XHQudGhlbiggKCByZXNwb25zZSApID0+IHtcblxuXHRcdFx0XHRcdGlmICggcmVzcG9uc2Uuc3VjY2VzcyApIHtcblx0XHRcdFx0XHRcdGUudGFyZ2V0LnBhcmVudE5vZGUucGFyZW50Tm9kZS5yZW1vdmUoKTtcblx0XHRcdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRcdFx0YWxlcnQoIHJlc3BvbnNlLmRhdGEgKTtcblx0XHRcdFx0XHR9XG5cblx0XHRcdFx0fSApO1xuXG5cdFx0fSApO1xuXG5cdH0gKTtcblxufSApO1xuIl0sIm5hbWVzIjpbXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./src/js/metabox/index.js\n");

/***/ }),

/***/ "./src/scss/metabox.scss":
/*!*******************************!*\
  !*** ./src/scss/metabox.scss ***!
  \*******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9zcmMvc2Nzcy9tZXRhYm94LnNjc3MuanMiLCJtYXBwaW5ncyI6IjtBQUFBIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vZm9yZ3Jhdml0eS1maWxsYWJsZXBkZnMvLi9zcmMvc2Nzcy9tZXRhYm94LnNjc3M/ZTc1MSJdLCJzb3VyY2VzQ29udGVudCI6WyIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiXSwibmFtZXMiOltdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./src/scss/metabox.scss\n");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval-source-map devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./src/js/metabox/index.js");
/******/ 	
/******/ })()
;