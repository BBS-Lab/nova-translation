/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 2);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

/* globals __VUE_SSR_CONTEXT__ */

// IMPORTANT: Do NOT use ES2015 features in this file.
// This module is a runtime utility for cleaner component module output and will
// be included in the final webpack user bundle.

module.exports = function normalizeComponent (
  rawScriptExports,
  compiledTemplate,
  functionalTemplate,
  injectStyles,
  scopeId,
  moduleIdentifier /* server only */
) {
  var esModule
  var scriptExports = rawScriptExports = rawScriptExports || {}

  // ES6 modules interop
  var type = typeof rawScriptExports.default
  if (type === 'object' || type === 'function') {
    esModule = rawScriptExports
    scriptExports = rawScriptExports.default
  }

  // Vue.extend constructor export interop
  var options = typeof scriptExports === 'function'
    ? scriptExports.options
    : scriptExports

  // render functions
  if (compiledTemplate) {
    options.render = compiledTemplate.render
    options.staticRenderFns = compiledTemplate.staticRenderFns
    options._compiled = true
  }

  // functional template
  if (functionalTemplate) {
    options.functional = true
  }

  // scopedId
  if (scopeId) {
    options._scopeId = scopeId
  }

  var hook
  if (moduleIdentifier) { // server build
    hook = function (context) {
      // 2.3 injection
      context =
        context || // cached call
        (this.$vnode && this.$vnode.ssrContext) || // stateful
        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional
      // 2.2 with runInNewContext: true
      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
        context = __VUE_SSR_CONTEXT__
      }
      // inject component styles
      if (injectStyles) {
        injectStyles.call(this, context)
      }
      // register component module identifier for async chunk inferrence
      if (context && context._registeredComponents) {
        context._registeredComponents.add(moduleIdentifier)
      }
    }
    // used by ssr in case component is cached and beforeCreate
    // never gets called
    options._ssrRegister = hook
  } else if (injectStyles) {
    hook = injectStyles
  }

  if (hook) {
    var functional = options.functional
    var existing = functional
      ? options.render
      : options.beforeCreate

    if (!functional) {
      // inject component registration as beforeCreate hook
      options.beforeCreate = existing
        ? [].concat(existing, hook)
        : [hook]
    } else {
      // for template-only hot-reload because in that case the render fn doesn't
      // go through the normalizer
      options._injectStyles = hook
      // register for functioal component in vue file
      options.render = function renderWithStyleInjection (h, context) {
        hook.call(context)
        return existing(h, context)
      }
    }
  }

  return {
    esModule: esModule,
    exports: scriptExports,
    options: options
  }
}


/***/ }),
/* 1 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony default export */ __webpack_exports__["a"] = ({
  methods: {
    trans: function trans(key, replace) {
      return window.config.translations["nova-translation::" + key] ? this.__("nova-translation::" + key, replace) : key;
    }
  }
});

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(3);
module.exports = __webpack_require__(16);


/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

Nova.booting(function (Vue, router, store) {
  router.addRoutes([{
    name: 'nova-translation',
    path: '/nova-translation',
    component: __webpack_require__(4)
  }]);
});

/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(5)
}
var normalizeComponent = __webpack_require__(0)
/* script */
var __vue_script__ = __webpack_require__(10)
/* template */
var __vue_template__ = __webpack_require__(15)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/js/components/TranslationMatrix.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-142b81f7", Component.options)
  } else {
    hotAPI.reload("data-v-142b81f7", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(6);
if(typeof content === 'string') content = [[module.i, content, '']];
if(content.locals) module.exports = content.locals;
// add the styles to the DOM
var update = __webpack_require__(8)("3ee7c6cf", content, false, {});
// Hot Module Replacement
if(false) {
 // When the styles change, update the <style> tags
 if(!content.locals) {
   module.hot.accept("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"vue\":true,\"id\":\"data-v-142b81f7\",\"scoped\":false,\"hasInlineConfig\":true}!../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./TranslationMatrix.vue", function() {
     var newContent = require("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"vue\":true,\"id\":\"data-v-142b81f7\",\"scoped\":false,\"hasInlineConfig\":true}!../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./TranslationMatrix.vue");
     if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
     update(newContent);
   });
 }
 // When the module is disposed, remove the <style> tags
 module.hot.dispose(function() { update(); });
}

/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(7)(false);
// imports


// module
exports.push([module.i, "\n.modal {\n  background-color: rgba(0, 0, 0, 0.6);\n}\n.table tfoot tr:hover td {\n  background-color: transparent;\n}\n", ""]);

// exports


/***/ }),
/* 7 */
/***/ (function(module, exports) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
module.exports = function(useSourceMap) {
	var list = [];

	// return the list of modules as css string
	list.toString = function toString() {
		return this.map(function (item) {
			var content = cssWithMappingToString(item, useSourceMap);
			if(item[2]) {
				return "@media " + item[2] + "{" + content + "}";
			} else {
				return content;
			}
		}).join("");
	};

	// import a list of modules into the list
	list.i = function(modules, mediaQuery) {
		if(typeof modules === "string")
			modules = [[null, modules, ""]];
		var alreadyImportedModules = {};
		for(var i = 0; i < this.length; i++) {
			var id = this[i][0];
			if(typeof id === "number")
				alreadyImportedModules[id] = true;
		}
		for(i = 0; i < modules.length; i++) {
			var item = modules[i];
			// skip already imported module
			// this implementation is not 100% perfect for weird media query combinations
			//  when a module is imported multiple times with different media queries.
			//  I hope this will never occur (Hey this way we have smaller bundles)
			if(typeof item[0] !== "number" || !alreadyImportedModules[item[0]]) {
				if(mediaQuery && !item[2]) {
					item[2] = mediaQuery;
				} else if(mediaQuery) {
					item[2] = "(" + item[2] + ") and (" + mediaQuery + ")";
				}
				list.push(item);
			}
		}
	};
	return list;
};

function cssWithMappingToString(item, useSourceMap) {
	var content = item[1] || '';
	var cssMapping = item[3];
	if (!cssMapping) {
		return content;
	}

	if (useSourceMap && typeof btoa === 'function') {
		var sourceMapping = toComment(cssMapping);
		var sourceURLs = cssMapping.sources.map(function (source) {
			return '/*# sourceURL=' + cssMapping.sourceRoot + source + ' */'
		});

		return [content].concat(sourceURLs).concat([sourceMapping]).join('\n');
	}

	return [content].join('\n');
}

// Adapted from convert-source-map (MIT)
function toComment(sourceMap) {
	// eslint-disable-next-line no-undef
	var base64 = btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap))));
	var data = 'sourceMappingURL=data:application/json;charset=utf-8;base64,' + base64;

	return '/*# ' + data + ' */';
}


/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

/*
  MIT License http://www.opensource.org/licenses/mit-license.php
  Author Tobias Koppers @sokra
  Modified by Evan You @yyx990803
*/

var hasDocument = typeof document !== 'undefined'

if (typeof DEBUG !== 'undefined' && DEBUG) {
  if (!hasDocument) {
    throw new Error(
    'vue-style-loader cannot be used in a non-browser environment. ' +
    "Use { target: 'node' } in your Webpack config to indicate a server-rendering environment."
  ) }
}

var listToStyles = __webpack_require__(9)

/*
type StyleObject = {
  id: number;
  parts: Array<StyleObjectPart>
}

type StyleObjectPart = {
  css: string;
  media: string;
  sourceMap: ?string
}
*/

var stylesInDom = {/*
  [id: number]: {
    id: number,
    refs: number,
    parts: Array<(obj?: StyleObjectPart) => void>
  }
*/}

var head = hasDocument && (document.head || document.getElementsByTagName('head')[0])
var singletonElement = null
var singletonCounter = 0
var isProduction = false
var noop = function () {}
var options = null
var ssrIdKey = 'data-vue-ssr-id'

// Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
// tags it will allow on a page
var isOldIE = typeof navigator !== 'undefined' && /msie [6-9]\b/.test(navigator.userAgent.toLowerCase())

module.exports = function (parentId, list, _isProduction, _options) {
  isProduction = _isProduction

  options = _options || {}

  var styles = listToStyles(parentId, list)
  addStylesToDom(styles)

  return function update (newList) {
    var mayRemove = []
    for (var i = 0; i < styles.length; i++) {
      var item = styles[i]
      var domStyle = stylesInDom[item.id]
      domStyle.refs--
      mayRemove.push(domStyle)
    }
    if (newList) {
      styles = listToStyles(parentId, newList)
      addStylesToDom(styles)
    } else {
      styles = []
    }
    for (var i = 0; i < mayRemove.length; i++) {
      var domStyle = mayRemove[i]
      if (domStyle.refs === 0) {
        for (var j = 0; j < domStyle.parts.length; j++) {
          domStyle.parts[j]()
        }
        delete stylesInDom[domStyle.id]
      }
    }
  }
}

function addStylesToDom (styles /* Array<StyleObject> */) {
  for (var i = 0; i < styles.length; i++) {
    var item = styles[i]
    var domStyle = stylesInDom[item.id]
    if (domStyle) {
      domStyle.refs++
      for (var j = 0; j < domStyle.parts.length; j++) {
        domStyle.parts[j](item.parts[j])
      }
      for (; j < item.parts.length; j++) {
        domStyle.parts.push(addStyle(item.parts[j]))
      }
      if (domStyle.parts.length > item.parts.length) {
        domStyle.parts.length = item.parts.length
      }
    } else {
      var parts = []
      for (var j = 0; j < item.parts.length; j++) {
        parts.push(addStyle(item.parts[j]))
      }
      stylesInDom[item.id] = { id: item.id, refs: 1, parts: parts }
    }
  }
}

function createStyleElement () {
  var styleElement = document.createElement('style')
  styleElement.type = 'text/css'
  head.appendChild(styleElement)
  return styleElement
}

function addStyle (obj /* StyleObjectPart */) {
  var update, remove
  var styleElement = document.querySelector('style[' + ssrIdKey + '~="' + obj.id + '"]')

  if (styleElement) {
    if (isProduction) {
      // has SSR styles and in production mode.
      // simply do nothing.
      return noop
    } else {
      // has SSR styles but in dev mode.
      // for some reason Chrome can't handle source map in server-rendered
      // style tags - source maps in <style> only works if the style tag is
      // created and inserted dynamically. So we remove the server rendered
      // styles and inject new ones.
      styleElement.parentNode.removeChild(styleElement)
    }
  }

  if (isOldIE) {
    // use singleton mode for IE9.
    var styleIndex = singletonCounter++
    styleElement = singletonElement || (singletonElement = createStyleElement())
    update = applyToSingletonTag.bind(null, styleElement, styleIndex, false)
    remove = applyToSingletonTag.bind(null, styleElement, styleIndex, true)
  } else {
    // use multi-style-tag mode in all other cases
    styleElement = createStyleElement()
    update = applyToTag.bind(null, styleElement)
    remove = function () {
      styleElement.parentNode.removeChild(styleElement)
    }
  }

  update(obj)

  return function updateStyle (newObj /* StyleObjectPart */) {
    if (newObj) {
      if (newObj.css === obj.css &&
          newObj.media === obj.media &&
          newObj.sourceMap === obj.sourceMap) {
        return
      }
      update(obj = newObj)
    } else {
      remove()
    }
  }
}

var replaceText = (function () {
  var textStore = []

  return function (index, replacement) {
    textStore[index] = replacement
    return textStore.filter(Boolean).join('\n')
  }
})()

function applyToSingletonTag (styleElement, index, remove, obj) {
  var css = remove ? '' : obj.css

  if (styleElement.styleSheet) {
    styleElement.styleSheet.cssText = replaceText(index, css)
  } else {
    var cssNode = document.createTextNode(css)
    var childNodes = styleElement.childNodes
    if (childNodes[index]) styleElement.removeChild(childNodes[index])
    if (childNodes.length) {
      styleElement.insertBefore(cssNode, childNodes[index])
    } else {
      styleElement.appendChild(cssNode)
    }
  }
}

function applyToTag (styleElement, obj) {
  var css = obj.css
  var media = obj.media
  var sourceMap = obj.sourceMap

  if (media) {
    styleElement.setAttribute('media', media)
  }
  if (options.ssrId) {
    styleElement.setAttribute(ssrIdKey, obj.id)
  }

  if (sourceMap) {
    // https://developer.chrome.com/devtools/docs/javascript-debugging
    // this makes source maps inside style tags work properly in Chrome
    css += '\n/*# sourceURL=' + sourceMap.sources[0] + ' */'
    // http://stackoverflow.com/a/26603875
    css += '\n/*# sourceMappingURL=data:application/json;base64,' + btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))) + ' */'
  }

  if (styleElement.styleSheet) {
    styleElement.styleSheet.cssText = css
  } else {
    while (styleElement.firstChild) {
      styleElement.removeChild(styleElement.firstChild)
    }
    styleElement.appendChild(document.createTextNode(css))
  }
}


/***/ }),
/* 9 */
/***/ (function(module, exports) {

/**
 * Translates the list format produced by css-loader into something
 * easier to manipulate.
 */
module.exports = function listToStyles (parentId, list) {
  var styles = []
  var newStyles = {}
  for (var i = 0; i < list.length; i++) {
    var item = list[i]
    var id = item[0]
    var css = item[1]
    var media = item[2]
    var sourceMap = item[3]
    var part = {
      id: parentId + ':' + i,
      css: css,
      media: media,
      sourceMap: sourceMap
    }
    if (!newStyles[id]) {
      styles.push(newStyles[id] = { id: id, parts: [part] })
    } else {
      newStyles[id].parts.push(part)
    }
  }
  return styles
}


/***/ }),
/* 10 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__mixins_I18n__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_animated_scroll_to__ = __webpack_require__(11);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_animated_scroll_to___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_animated_scroll_to__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ __webpack_exports__["default"] = ({
  mixins: [__WEBPACK_IMPORTED_MODULE_0__mixins_I18n__["a" /* default */]],

  components: {
    PromptKeyModal: __webpack_require__(12)
  },

  data: function data() {
    return {
      labels: [],
      locales: [],
      loading: true,
      promptKeyModalOpened: false
    };
  },
  mounted: function mounted() {
    this.hydrate();
  },


  methods: {
    hydrate: function hydrate() {
      var _this = this;

      Nova.request().get('/nova-vendor/nova-translation/labels').then(function (response) {
        _this.labels = response.data.labels;
        _this.locales = response.data.locales;
        _this.loading = false;
      }).catch(function (error) {
        console.error(error);
      });
    },
    addKey: function addKey(key) {
      this.promptKeyModalOpened = false;

      if (!this.keyExists(key)) {
        this.addI18nKey(key);
      } else {
        this.$toasted.show(this.trans('The key you try to add already exists!'), { type: 'error' });
        __WEBPACK_IMPORTED_MODULE_1_animated_scroll_to___default()(document.querySelector('#tr__' + key), {
          speed: 500
        });
      }
    },
    keyExists: function keyExists(key) {
      for (var i = 0; i < this.labels.length; i++) {
        if (this.labels[i].key === key) {
          return true;
        }
      }

      return false;
    },
    addI18nKey: function addI18nKey(key) {
      for (var i = 0; i < this.locales.length; i++) {
        this.labels.push({
          key: key,
          value: '',
          locale_id: this.locales[i].id
        });
      }
    },
    updateLabel: function updateLabel(key, localeId, value) {
      for (var i = 0; i < this.labels.length; i++) {
        if (this.labels[i].key === key && this.labels[i].locale_id === localeId) {
          this.labels[i].value = value;
          break;
        }
      }
    },
    saveLabels: function saveLabels() {
      var _this2 = this;

      this.loading = true;

      Nova.request().post('/nova-vendor/nova-translation/labels', { labels: this.labels }).then(function (response) {
        _this2.labels = response.data.labels;
        _this2.$toasted.show(_this2.trans('The translations have been successfully saved!'), { type: 'success' });
      }).catch(function (error) {
        _this2.$toasted.show(_this2.trans('An error occurred while saving the translations!'), { type: 'error' });
      }).finally(function () {
        _this2.loading = false;
      });
    }
  },

  computed: {
    matrix: function matrix() {
      var matrix = {};

      for (var i = 0, label; i < this.labels.length; i++) {
        label = this.labels[i];
        if (typeof matrix[label.key] === 'undefined') {
          matrix[label.key] = {};
        }
        matrix[label.key][label.locale_id] = label.value;
      }

      return matrix;
    }
  }
});

/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
// --------- SCROLL INTERFACES
// ScrollDomElement and ScrollWindow have identical interfaces
var ScrollDomElement = /** @class */ (function () {
    function ScrollDomElement(element) {
        this.element = element;
    }
    ScrollDomElement.prototype.getHorizontalScroll = function () {
        return this.element.scrollLeft;
    };
    ScrollDomElement.prototype.getVerticalScroll = function () {
        return this.element.scrollTop;
    };
    ScrollDomElement.prototype.getMaxHorizontalScroll = function () {
        return this.element.scrollWidth - this.element.clientWidth;
    };
    ScrollDomElement.prototype.getMaxVerticalScroll = function () {
        return this.element.scrollHeight - this.element.clientHeight;
    };
    ScrollDomElement.prototype.getHorizontalElementScrollOffset = function (elementToScrollTo) {
        return elementToScrollTo.getBoundingClientRect().left + this.element.scrollLeft - this.element.getBoundingClientRect().left;
    };
    ScrollDomElement.prototype.getVerticalElementScrollOffset = function (elementToScrollTo) {
        return elementToScrollTo.getBoundingClientRect().top + this.element.scrollTop - this.element.getBoundingClientRect().top;
    };
    ScrollDomElement.prototype.scrollTo = function (x, y) {
        this.element.scrollLeft = x;
        this.element.scrollTop = y;
    };
    return ScrollDomElement;
}());
var ScrollWindow = /** @class */ (function () {
    function ScrollWindow() {
    }
    ScrollWindow.prototype.getHorizontalScroll = function () {
        return window.scrollX || document.documentElement.scrollLeft;
    };
    ScrollWindow.prototype.getVerticalScroll = function () {
        return window.scrollY || document.documentElement.scrollTop;
    };
    ScrollWindow.prototype.getMaxHorizontalScroll = function () {
        return Math.max(document.body.scrollWidth, document.documentElement.scrollWidth, document.body.offsetWidth, document.documentElement.offsetWidth, document.body.clientWidth, document.documentElement.clientWidth) - window.innerWidth;
    };
    ScrollWindow.prototype.getMaxVerticalScroll = function () {
        return Math.max(document.body.scrollHeight, document.documentElement.scrollHeight, document.body.offsetHeight, document.documentElement.offsetHeight, document.body.clientHeight, document.documentElement.clientHeight) - window.innerHeight;
    };
    ScrollWindow.prototype.getHorizontalElementScrollOffset = function (elementToScrollTo) {
        var scrollLeft = window.scrollX || document.documentElement.scrollLeft;
        return scrollLeft + elementToScrollTo.getBoundingClientRect().left;
    };
    ScrollWindow.prototype.getVerticalElementScrollOffset = function (elementToScrollTo) {
        var scrollTop = window.scrollY || document.documentElement.scrollTop;
        return scrollTop + elementToScrollTo.getBoundingClientRect().top;
    };
    ScrollWindow.prototype.scrollTo = function (x, y) {
        window.scrollTo(x, y);
    };
    return ScrollWindow;
}());
// --------- KEEPING TRACK OF ACTIVE ANIMATIONS
var activeAnimations = {
    elements: [],
    cancelMethods: [],
    add: function (element, cancelAnimation) {
        activeAnimations.elements.push(element);
        activeAnimations.cancelMethods.push(cancelAnimation);
    },
    stop: function (element) {
        var index = activeAnimations.elements.indexOf(element);
        if (index > -1) {
            // Stop animation
            activeAnimations.cancelMethods[index]();
            // Remove it
            activeAnimations.elements.splice(index, 1);
            activeAnimations.cancelMethods.splice(index, 1);
        }
    }
};
// --------- CHECK IF CODE IS RUNNING IN A BROWSER
var WINDOW_EXISTS = typeof window !== 'undefined';
// --------- ANIMATE SCROLL TO
var defaultOptions = {
    cancelOnUserAction: true,
    easing: function (t) { return (--t) * t * t + 1; },
    elementToScroll: WINDOW_EXISTS ? window : null,
    horizontalOffset: 0,
    maxDuration: 3000,
    minDuration: 250,
    speed: 500,
    verticalOffset: 0,
};
function animateScrollTo(numberOrCoordsOrElement, userOptions) {
    if (userOptions === void 0) { userOptions = {}; }
    // Check for server rendering
    if (!WINDOW_EXISTS) {
        // @ts-ignore
        // If it still gets called on server, return Promise for API consistency
        return new Promise(function (resolve) {
            resolve(false); // Returning false on server
        });
    }
    else if (!window.Promise) {
        throw ('Browser doesn\'t support Promises, and animated-scroll-to depends on it, please provide a polyfill.');
    }
    var x;
    var y;
    var scrollToElement;
    var options = __assign(__assign({}, defaultOptions), userOptions);
    var isWindow = options.elementToScroll === window;
    var isElement = !!options.elementToScroll.nodeName;
    if (!isWindow && !isElement) {
        throw ('Element to scroll needs to be either window or DOM element.');
    }
    var elementToScroll = isWindow ?
        new ScrollWindow() :
        new ScrollDomElement(options.elementToScroll);
    if (numberOrCoordsOrElement instanceof Element) {
        scrollToElement = numberOrCoordsOrElement;
        x = elementToScroll.getHorizontalElementScrollOffset(scrollToElement);
        y = elementToScroll.getVerticalElementScrollOffset(scrollToElement);
    }
    else if (typeof numberOrCoordsOrElement === 'number') {
        x = elementToScroll.getHorizontalScroll();
        y = numberOrCoordsOrElement;
    }
    else if (Array.isArray(numberOrCoordsOrElement) && numberOrCoordsOrElement.length === 2) {
        x = numberOrCoordsOrElement[0] === null ? elementToScroll.getHorizontalScroll() : numberOrCoordsOrElement[0];
        y = numberOrCoordsOrElement[1] === null ? elementToScroll.getVerticalScroll() : numberOrCoordsOrElement[1];
    }
    else {
        // ERROR
        throw ('Wrong function signature. Check documentation.\n' +
            'Available method signatures are:\n' +
            '  animateScrollTo(y:number, options)\n' +
            '  animateScrollTo([x:number | null, y:number | null], options)\n' +
            '  animateScrollTo(scrollToElement:Element, options)');
    }
    // Add offsets
    x += options.horizontalOffset;
    y += options.verticalOffset;
    // Horizontal scroll distance
    var maxHorizontalScroll = elementToScroll.getMaxHorizontalScroll();
    var initialHorizontalScroll = elementToScroll.getHorizontalScroll();
    // If user specified scroll position is greater than maximum available scroll
    if (x > maxHorizontalScroll) {
        x = maxHorizontalScroll;
    }
    // Calculate distance to scroll
    var horizontalDistanceToScroll = x - initialHorizontalScroll;
    // Vertical scroll distance distance
    var maxVerticalScroll = elementToScroll.getMaxVerticalScroll();
    var initialVerticalScroll = elementToScroll.getVerticalScroll();
    // If user specified scroll position is greater than maximum available scroll
    if (y > maxVerticalScroll) {
        y = maxVerticalScroll;
    }
    // Calculate distance to scroll
    var verticalDistanceToScroll = y - initialVerticalScroll;
    // Calculate duration of the scroll
    var horizontalDuration = Math.abs(Math.round((horizontalDistanceToScroll / 1000) * options.speed));
    var verticalDuration = Math.abs(Math.round((verticalDistanceToScroll / 1000) * options.speed));
    var duration = horizontalDuration > verticalDuration ? horizontalDuration : verticalDuration;
    // Set minimum and maximum duration
    if (duration < options.minDuration) {
        duration = options.minDuration;
    }
    else if (duration > options.maxDuration) {
        duration = options.maxDuration;
    }
    // @ts-ignore
    return new Promise(function (resolve, reject) {
        // Scroll is already in place, nothing to do
        if (horizontalDistanceToScroll === 0 && verticalDistanceToScroll === 0) {
            // Resolve promise with a boolean hasScrolledToPosition set to true
            resolve(true);
        }
        // Cancel existing animation if it is already running on the same element
        activeAnimations.stop(options.elementToScroll);
        // To cancel animation we have to store request animation frame ID 
        var requestID;
        // Cancel animation handler
        var cancelAnimation = function () {
            removeListeners();
            cancelAnimationFrame(requestID);
            // Resolve promise with a boolean hasScrolledToPosition set to false
            resolve(false);
        };
        // Registering animation so it can be canceled if function
        // gets called again on the same element
        activeAnimations.add(options.elementToScroll, cancelAnimation);
        // Prevent user actions handler
        var preventDefaultHandler = function (e) { return e.preventDefault(); };
        var handler = options.cancelOnUserAction ?
            cancelAnimation :
            preventDefaultHandler;
        // If animation is not cancelable by the user, we can't use passive events
        var eventOptions = options.cancelOnUserAction ?
            { passive: true } :
            { passive: false };
        var events = [
            'wheel',
            'touchstart',
            'keydown',
            'mousedown',
        ];
        // Function to remove listeners after animation is finished
        var removeListeners = function () {
            events.forEach(function (eventName) {
                options.elementToScroll.removeEventListener(eventName, handler);
            });
        };
        // Add listeners
        events.forEach(function (eventName) {
            options.elementToScroll.addEventListener(eventName, handler, eventOptions);
        });
        // Animation
        var startingTime = Date.now();
        var step = function () {
            var timeDiff = Date.now() - startingTime;
            var t = timeDiff / duration;
            var horizontalScrollPosition = Math.round(initialHorizontalScroll + (horizontalDistanceToScroll * options.easing(t)));
            var verticalScrollPosition = Math.round(initialVerticalScroll + (verticalDistanceToScroll * options.easing(t)));
            if (timeDiff < duration && (horizontalScrollPosition !== x || verticalScrollPosition !== y)) {
                // If scroll didn't reach desired position or time is not elapsed
                // Scroll to a new position
                elementToScroll.scrollTo(horizontalScrollPosition, verticalScrollPosition);
                // And request a new step
                requestID = requestAnimationFrame(step);
            }
            else {
                // If the time elapsed or we reached the desired offset
                // Set scroll to the desired offset (when rounding made it to be off a pixel or two)
                // Clear animation frame to be sure
                elementToScroll.scrollTo(x, y);
                cancelAnimationFrame(requestID);
                // Remove listeners
                removeListeners();
                // Resolve promise with a boolean hasScrolledToPosition set to true
                resolve(true);
            }
        };
        // Start animating scroll
        requestID = requestAnimationFrame(step);
    });
}
exports.default = animateScrollTo;
// Support for direct usage in browsers
// This is mostly to keep it similar to v1
// Don't forget to include Promise polyfill for IE
// <script src="https://unpkg.com/es6-promise/dist/es6-promise.auto.min.js"></script>
// https://github.com/stefanpenner/es6-promise
if (WINDOW_EXISTS) {
    window.animateScrollTo = animateScrollTo;
}


/***/ }),
/* 12 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
var normalizeComponent = __webpack_require__(0)
/* script */
var __vue_script__ = __webpack_require__(13)
/* template */
var __vue_template__ = __webpack_require__(14)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/js/components/PromptKeyModal.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-75ee3b3d", Component.options)
  } else {
    hotAPI.reload("data-v-75ee3b3d", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 13 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__mixins_I18n__ = __webpack_require__(1);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ __webpack_exports__["default"] = ({
  mixins: [__WEBPACK_IMPORTED_MODULE_0__mixins_I18n__["a" /* default */]],

  data: function data() {
    return {
      newKey: ''
    };
  },
  mounted: function mounted() {
    document.querySelectorAll('.modal input')[0].focus();
  },


  methods: {
    handleKeydown: function handleKeydown(e) {
      if (['Escape', 'Enter'].indexOf(e.key) !== -1) {
        return;
      }

      e.stopPropagation();
    },
    handleConfirm: function handleConfirm() {
      this.$emit('confirm', this.newKey);
    },
    handleClose: function handleClose() {
      this.$emit('close');
    }
  }
});

/***/ }),
/* 14 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "modal",
    { attrs: { role: "dialog" }, on: { "modal-close": _vm.handleClose } },
    [
      _c(
        "form",
        {
          staticClass:
            "bg-white rounded-lg shadow-lg overflow-hidden w-action-fields",
          attrs: { autocomplete: "off" },
          on: {
            keydown: _vm.handleKeydown,
            submit: function($event) {
              $event.preventDefault()
              $event.stopPropagation()
              return _vm.handleConfirm($event)
            }
          }
        },
        [
          _c(
            "div",
            [
              _c(
                "heading",
                {
                  staticClass: "border-b border-40 py-8 px-8",
                  attrs: { level: 2 }
                },
                [_vm._v(_vm._s(_vm.trans("Add a translation key")))]
              ),
              _vm._v(" "),
              _c("div", { staticClass: "m-8" }, [
                _c("div", { staticClass: "action" }, [
                  _c("input", {
                    directives: [
                      {
                        name: "model",
                        rawName: "v-model",
                        value: _vm.newKey,
                        expression: "newKey"
                      }
                    ],
                    staticClass:
                      "w-full form-control form-input form-input-bordered",
                    attrs: { type: "text" },
                    domProps: { value: _vm.newKey },
                    on: {
                      input: function($event) {
                        if ($event.target.composing) {
                          return
                        }
                        _vm.newKey = $event.target.value
                      }
                    }
                  })
                ])
              ])
            ],
            1
          ),
          _vm._v(" "),
          _c("div", { staticClass: "bg-30 px-6 py-3 flex" }, [
            _c("div", { staticClass: "flex items-center ml-auto" }, [
              _c(
                "button",
                {
                  staticClass:
                    "btn btn-link dim cursor-pointer text-80 ml-auto mr-6",
                  attrs: { type: "button" },
                  on: {
                    click: function($event) {
                      $event.preventDefault()
                      return _vm.handleClose($event)
                    }
                  }
                },
                [
                  _vm._v(
                    "\n          " + _vm._s(_vm.trans("Close")) + "\n        "
                  )
                ]
              ),
              _vm._v(" "),
              _c(
                "button",
                {
                  staticClass: "btn btn-default btn-primary",
                  attrs: { type: "submit" }
                },
                [
                  _vm._v(
                    "\n          " + _vm._s(_vm.trans("Confirm")) + "\n        "
                  )
                ]
              )
            ])
          ])
        ]
      )
    ]
  )
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-75ee3b3d", module.exports)
  }
}

/***/ }),
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    [
      _c("heading", [_vm._v(_vm._s(_vm.trans("Translations Matrix")))]),
      _vm._v(" "),
      _vm.loading
        ? _c("loading-view")
        : _c(
            "div",
            [
              _c("div", { staticClass: "mb-6 text-right" }, [
                _c(
                  "button",
                  {
                    staticClass: "btn btn-link dim cursor-pointer text-80",
                    on: {
                      click: function($event) {
                        $event.preventDefault()
                        _vm.promptKeyModalOpened = true
                      }
                    }
                  },
                  [_vm._v(_vm._s(_vm.trans("Add key")))]
                ),
                _vm._v(" "),
                _c(
                  "button",
                  {
                    staticClass:
                      "ml-3 btn btn-default btn-primary text-white cursor-pointer text-80",
                    on: { click: _vm.saveLabels }
                  },
                  [_vm._v("Save")]
                )
              ]),
              _vm._v(" "),
              _c("card", [
                _c(
                  "table",
                  {
                    staticClass: "my-4 table w-full",
                    attrs: { cellpadding: "0", cellspacing: "0" }
                  },
                  [
                    _c("thead", [
                      _c(
                        "tr",
                        { staticClass: "p-3" },
                        [
                          _c("th"),
                          _vm._v(" "),
                          _vm._l(_vm.locales, function(locale) {
                            return _c("th", { key: locale.id }, [
                              _vm._v(_vm._s(locale.label))
                            ])
                          })
                        ],
                        2
                      )
                    ]),
                    _vm._v(" "),
                    _c(
                      "tbody",
                      _vm._l(_vm.matrix, function(keyI18n, key) {
                        return _c(
                          "tr",
                          {
                            key: key,
                            staticClass: "p-3",
                            attrs: { id: "tr__" + key }
                          },
                          [
                            _c("td", [_vm._v(_vm._s(key))]),
                            _vm._v(" "),
                            _vm._l(_vm.locales, function(locale) {
                              return _c("td", { key: key + "__" + locale.id }, [
                                _c(
                                  "textarea",
                                  {
                                    staticClass:
                                      "w-full form-control form-input form-input-bordered py-3 h-auto",
                                    attrs: { rows: "1" },
                                    on: {
                                      input: function($event) {
                                        return _vm.updateLabel(
                                          key,
                                          locale.id,
                                          $event.target.value
                                        )
                                      }
                                    }
                                  },
                                  [
                                    _vm._v(
                                      _vm._s(
                                        keyI18n[locale.id]
                                          ? keyI18n[locale.id]
                                          : ""
                                      )
                                    )
                                  ]
                                )
                              ])
                            })
                          ],
                          2
                        )
                      }),
                      0
                    )
                  ]
                )
              ]),
              _vm._v(" "),
              _c("div", { staticClass: "mt-6 text-right" }, [
                _c(
                  "button",
                  {
                    staticClass: "btn btn-link dim cursor-pointer text-80",
                    on: {
                      click: function($event) {
                        $event.preventDefault()
                        _vm.promptKeyModalOpened = true
                      }
                    }
                  },
                  [_vm._v(_vm._s(_vm.trans("Add key")))]
                ),
                _vm._v(" "),
                _c(
                  "button",
                  {
                    staticClass:
                      "ml-3 btn btn-default btn-primary text-white cursor-pointer text-80",
                    on: { click: _vm.saveLabels }
                  },
                  [_vm._v("Save")]
                )
              ])
            ],
            1
          ),
      _vm._v(" "),
      _c(
        "portal",
        { attrs: { to: "modals", transition: "fade-transition" } },
        [
          _vm.promptKeyModalOpened
            ? _c("PromptKeyModal", {
                on: {
                  confirm: _vm.addKey,
                  close: function($event) {
                    _vm.promptKeyModalOpened = false
                  }
                }
              })
            : _vm._e()
        ],
        1
      )
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-142b81f7", module.exports)
  }
}

/***/ }),
/* 16 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ })
/******/ ]);