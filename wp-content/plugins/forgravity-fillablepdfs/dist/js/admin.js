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

/***/ "./src/js/admin.js":
/*!*************************!*\
  !*** ./src/js/admin.js ***!
  \*************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var scss_admin_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! scss/admin.scss */ \"./src/scss/admin.scss\");\n\nwindow.addEventListener('load', () => {\n  const $dropzone = document.querySelector('.fillablepdfs-dropzone'),\n    $templateInfo = document.querySelector('.fillablepdfs-template-info'),\n    $name = document.getElementById('name');\n\n  // Handle file uploads.\n  if ($dropzone) {\n    const $file = document.getElementById($dropzone.dataset.file),\n      strings = fg_fillablepdfs_admin_strings;\n    $dropzone.ondragover = e => {\n      $dropzone.classList.add('fillablepdfs-dropzone--dropping');\n      e.preventDefault();\n    };\n    $dropzone.ondragstart = e => {\n      $dropzone.classList.add('fillablepdfs-dropzone--dropping');\n      e.preventDefault();\n    };\n    $dropzone.ondragend = e => {\n      $dropzone.classList.remove('fillablepdfs-dropzone--dropping');\n      e.preventDefault();\n    };\n    $dropzone.ondrop = e => {\n      e.preventDefault();\n      $dropzone.classList.remove('fillablepdfs-dropzone--dropping');\n\n      // Attach file to file input.\n      $file.files = e.dataTransfer.files;\n      $file.dispatchEvent(new Event('change', e));\n    };\n    $dropzone.addEventListener('click', e => {\n      $file.click();\n    });\n    $file.addEventListener('change', e => {\n      // If more than one file was dropped, display error.\n      if (e.target.files.length > 1) {\n        alert(strings.too_many_files);\n        e.target.value = null;\n        return false;\n      }\n\n      // If file is not a PDF, display error.\n      if (e.target.files[0].type !== 'application/pdf') {\n        alert(strings.illegal_file_type);\n        e.target.value = null;\n        return false;\n      }\n\n      // Set changed flag.\n      document.querySelector('.fillablepdfs-dropzone__changed').value = '1';\n\n      // If this is the import form, submit form.\n      if ($dropzone.dataset.import) {\n        $dropzone.parentElement.submit();\n      }\n      if ($templateInfo) {\n        // Set file name in template info.\n        $templateInfo.querySelector('.fillablepdfs-template-info__file-name').innerHTML = e.target.files[0].name;\n\n        // Hide drop zone, show template info.\n        $dropzone.style.display = 'none';\n        $templateInfo.style.display = 'flex';\n      }\n\n      // Replace template name.\n      if ($name && $name.value.length === 0) {\n        $name.value = e.target.files[0].name.replace(/\\.[^/.]+$/, '');\n        $name.dispatchEvent(new Event('keyup'));\n      }\n    });\n  }\n\n  // Update template name.\n  if ($name && document.querySelector('.fillablepdfs-template-info__name')) {\n    $name.addEventListener('keyup', e => {\n      document.querySelector('.fillablepdfs-template-info__name').innerHTML = e.target.value;\n    });\n  }\n\n  // Reset template file.\n  if (document.querySelector('.fillablepdfs-template-info__action--replace')) {\n    document.querySelector('.fillablepdfs-template-info__action--replace').addEventListener('click', e => {\n      e.preventDefault();\n\n      // Reset file input.\n      document.getElementById($dropzone.dataset.file).value = null;\n\n      // Set changed flag.\n      document.querySelector('.fillablepdfs-dropzone__changed').value = '1';\n\n      // Hide template info, show drop zone.\n      $templateInfo.style.display = 'none';\n      $dropzone.style.display = 'block';\n    });\n  }\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9zcmMvanMvYWRtaW4uanMuanMiLCJtYXBwaW5ncyI6Ijs7QUFBQTtBQUVBO0FBRUE7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFFQTtBQUNBO0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBRUE7QUFFQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUVBO0FBRUE7QUFFQTtBQUVBO0FBRUE7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFFQTtBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBRUE7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUVBO0FBRUE7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUVBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFFQTtBQUNBO0FBRUEiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9mb3JncmF2aXR5LWZpbGxhYmxlcGRmcy8uL3NyYy9qcy9hZG1pbi5qcz8yMzFhIl0sInNvdXJjZXNDb250ZW50IjpbImltcG9ydCAnc2Nzcy9hZG1pbi5zY3NzJztcblxud2luZG93LmFkZEV2ZW50TGlzdGVuZXIoICdsb2FkJywgKCkgPT4ge1xuXG5cdGNvbnN0ICRkcm9wem9uZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcuZmlsbGFibGVwZGZzLWRyb3B6b25lJyApLFxuXHRcdCR0ZW1wbGF0ZUluZm8gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLmZpbGxhYmxlcGRmcy10ZW1wbGF0ZS1pbmZvJyApLFxuXHRcdCRuYW1lID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoICduYW1lJyApO1xuXG5cdC8vIEhhbmRsZSBmaWxlIHVwbG9hZHMuXG5cdGlmICggJGRyb3B6b25lICkge1xuXG5cdFx0Y29uc3QgJGZpbGUgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCggJGRyb3B6b25lLmRhdGFzZXQuZmlsZSApLFxuXHRcdCAgICAgIHN0cmluZ3MgPSBmZ19maWxsYWJsZXBkZnNfYWRtaW5fc3RyaW5ncztcblxuXHRcdCRkcm9wem9uZS5vbmRyYWdvdmVyID0gKCBlICkgPT4ge1xuXHRcdFx0JGRyb3B6b25lLmNsYXNzTGlzdC5hZGQoICdmaWxsYWJsZXBkZnMtZHJvcHpvbmUtLWRyb3BwaW5nJyApO1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdH07XG5cblx0XHQkZHJvcHpvbmUub25kcmFnc3RhcnQgPSAoIGUgKSA9PiB7XG5cdFx0XHQkZHJvcHpvbmUuY2xhc3NMaXN0LmFkZCggJ2ZpbGxhYmxlcGRmcy1kcm9wem9uZS0tZHJvcHBpbmcnICk7XG5cdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0fTtcblxuXHRcdCRkcm9wem9uZS5vbmRyYWdlbmQgPSAoIGUgKSA9PiB7XG5cdFx0XHQkZHJvcHpvbmUuY2xhc3NMaXN0LnJlbW92ZSggJ2ZpbGxhYmxlcGRmcy1kcm9wem9uZS0tZHJvcHBpbmcnICk7XG5cdFx0XHRlLnByZXZlbnREZWZhdWx0KCk7XG5cdFx0fTtcblxuXHRcdCRkcm9wem9uZS5vbmRyb3AgPSAoIGUgKSA9PiB7XG5cblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdCRkcm9wem9uZS5jbGFzc0xpc3QucmVtb3ZlKCAnZmlsbGFibGVwZGZzLWRyb3B6b25lLS1kcm9wcGluZycgKTtcblxuXHRcdFx0Ly8gQXR0YWNoIGZpbGUgdG8gZmlsZSBpbnB1dC5cblx0XHRcdCRmaWxlLmZpbGVzID0gZS5kYXRhVHJhbnNmZXIuZmlsZXM7XG5cdFx0XHQkZmlsZS5kaXNwYXRjaEV2ZW50KCBuZXcgRXZlbnQoICdjaGFuZ2UnLCBlICkgKTtcblxuXHRcdH07XG5cblx0XHQkZHJvcHpvbmUuYWRkRXZlbnRMaXN0ZW5lciggJ2NsaWNrJywgKCBlICkgPT4ge1xuXG5cdFx0XHQkZmlsZS5jbGljaygpO1xuXG5cdFx0fSApO1xuXG5cdFx0JGZpbGUuYWRkRXZlbnRMaXN0ZW5lciggJ2NoYW5nZScsICggZSApID0+IHtcblxuXHRcdFx0Ly8gSWYgbW9yZSB0aGFuIG9uZSBmaWxlIHdhcyBkcm9wcGVkLCBkaXNwbGF5IGVycm9yLlxuXHRcdFx0aWYgKCBlLnRhcmdldC5maWxlcy5sZW5ndGggPiAxICkge1xuXHRcdFx0XHRhbGVydCggc3RyaW5ncy50b29fbWFueV9maWxlcyApO1xuXHRcdFx0XHRlLnRhcmdldC52YWx1ZSA9IG51bGw7XG5cdFx0XHRcdHJldHVybiBmYWxzZTtcblx0XHRcdH1cblxuXHRcdFx0Ly8gSWYgZmlsZSBpcyBub3QgYSBQREYsIGRpc3BsYXkgZXJyb3IuXG5cdFx0XHRpZiAoIGUudGFyZ2V0LmZpbGVzWzBdLnR5cGUgIT09ICdhcHBsaWNhdGlvbi9wZGYnICkge1xuXHRcdFx0XHRhbGVydCggc3RyaW5ncy5pbGxlZ2FsX2ZpbGVfdHlwZSApO1xuXHRcdFx0XHRlLnRhcmdldC52YWx1ZSA9IG51bGw7XG5cdFx0XHRcdHJldHVybiBmYWxzZTtcblx0XHRcdH1cblxuXHRcdFx0Ly8gU2V0IGNoYW5nZWQgZmxhZy5cblx0XHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcuZmlsbGFibGVwZGZzLWRyb3B6b25lX19jaGFuZ2VkJyApLnZhbHVlID0gJzEnO1xuXG5cdFx0XHQvLyBJZiB0aGlzIGlzIHRoZSBpbXBvcnQgZm9ybSwgc3VibWl0IGZvcm0uXG5cdFx0XHRpZiAoICRkcm9wem9uZS5kYXRhc2V0LmltcG9ydCApIHtcblx0XHRcdFx0JGRyb3B6b25lLnBhcmVudEVsZW1lbnQuc3VibWl0KCk7XG5cdFx0XHR9XG5cblx0XHRcdGlmICggJHRlbXBsYXRlSW5mbyApIHtcblxuXHRcdFx0XHQvLyBTZXQgZmlsZSBuYW1lIGluIHRlbXBsYXRlIGluZm8uXG5cdFx0XHRcdCR0ZW1wbGF0ZUluZm8ucXVlcnlTZWxlY3RvciggJy5maWxsYWJsZXBkZnMtdGVtcGxhdGUtaW5mb19fZmlsZS1uYW1lJyApLmlubmVySFRNTCA9IGUudGFyZ2V0LmZpbGVzWyAwIF0ubmFtZTtcblxuXHRcdFx0XHQvLyBIaWRlIGRyb3Agem9uZSwgc2hvdyB0ZW1wbGF0ZSBpbmZvLlxuXHRcdFx0XHQkZHJvcHpvbmUuc3R5bGUuZGlzcGxheSA9ICdub25lJztcblx0XHRcdFx0JHRlbXBsYXRlSW5mby5zdHlsZS5kaXNwbGF5ID0gJ2ZsZXgnO1xuXG5cdFx0XHR9XG5cblx0XHRcdC8vIFJlcGxhY2UgdGVtcGxhdGUgbmFtZS5cblx0XHRcdGlmICggJG5hbWUgJiYgJG5hbWUudmFsdWUubGVuZ3RoID09PSAwICkge1xuXHRcdFx0XHQkbmFtZS52YWx1ZSA9IGUudGFyZ2V0LmZpbGVzWzBdLm5hbWUucmVwbGFjZSggL1xcLlteLy5dKyQvLCAnJyApO1xuXHRcdFx0XHQkbmFtZS5kaXNwYXRjaEV2ZW50KCBuZXcgRXZlbnQoICdrZXl1cCcgKSApO1xuXHRcdFx0fVxuXG5cdFx0fSApO1xuXG5cdH1cblxuXHQvLyBVcGRhdGUgdGVtcGxhdGUgbmFtZS5cblx0aWYgKCAkbmFtZSAmJiBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLmZpbGxhYmxlcGRmcy10ZW1wbGF0ZS1pbmZvX19uYW1lJyApICkge1xuXHRcdCRuYW1lLmFkZEV2ZW50TGlzdGVuZXIoICdrZXl1cCcsICggZSApID0+IHtcblx0XHRcdGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcuZmlsbGFibGVwZGZzLXRlbXBsYXRlLWluZm9fX25hbWUnICkuaW5uZXJIVE1MID0gZS50YXJnZXQudmFsdWU7XG5cdFx0fSApO1xuXHR9XG5cblx0Ly8gUmVzZXQgdGVtcGxhdGUgZmlsZS5cblx0aWYgKCBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLmZpbGxhYmxlcGRmcy10ZW1wbGF0ZS1pbmZvX19hY3Rpb24tLXJlcGxhY2UnICkgKSB7XG5cdFx0ZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy5maWxsYWJsZXBkZnMtdGVtcGxhdGUtaW5mb19fYWN0aW9uLS1yZXBsYWNlJyApLmFkZEV2ZW50TGlzdGVuZXIoICdjbGljaycsICggZSApID0+IHtcblxuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXG5cdFx0XHQvLyBSZXNldCBmaWxlIGlucHV0LlxuXHRcdFx0ZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoICRkcm9wem9uZS5kYXRhc2V0LmZpbGUgKS52YWx1ZSA9IG51bGw7XG5cblx0XHRcdC8vIFNldCBjaGFuZ2VkIGZsYWcuXG5cdFx0XHRkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLmZpbGxhYmxlcGRmcy1kcm9wem9uZV9fY2hhbmdlZCcgKS52YWx1ZSA9ICcxJztcblxuXHRcdFx0Ly8gSGlkZSB0ZW1wbGF0ZSBpbmZvLCBzaG93IGRyb3Agem9uZS5cblx0XHRcdCR0ZW1wbGF0ZUluZm8uc3R5bGUuZGlzcGxheSA9ICdub25lJztcblx0XHRcdCRkcm9wem9uZS5zdHlsZS5kaXNwbGF5ID0gJ2Jsb2NrJztcblxuXHRcdH0gKTtcblx0fVxuXG59ICk7XG4iXSwibmFtZXMiOltdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./src/js/admin.js\n");

/***/ }),

/***/ "./src/scss/admin.scss":
/*!*****************************!*\
  !*** ./src/scss/admin.scss ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9zcmMvc2Nzcy9hZG1pbi5zY3NzLmpzIiwibWFwcGluZ3MiOiI7QUFBQSIsInNvdXJjZXMiOlsid2VicGFjazovL2ZvcmdyYXZpdHktZmlsbGFibGVwZGZzLy4vc3JjL3Njc3MvYWRtaW4uc2Nzcz9kZDY4Il0sInNvdXJjZXNDb250ZW50IjpbIi8vIGV4dHJhY3RlZCBieSBtaW5pLWNzcy1leHRyYWN0LXBsdWdpblxuZXhwb3J0IHt9OyJdLCJuYW1lcyI6W10sInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///./src/scss/admin.scss\n");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./src/js/admin.js");
/******/ 	
/******/ })()
;