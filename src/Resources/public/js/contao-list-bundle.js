!function(e){function t(t){for(var n,i,r=t[0],o=t[1],s=0,u=[];s<r.length;s++)i=r[s],Object.prototype.hasOwnProperty.call(a,i)&&a[i]&&u.push(a[i][0]),a[i]=0;for(n in o)Object.prototype.hasOwnProperty.call(o,n)&&(e[n]=o[n]);for(l&&l(t);u.length;)u.shift()()}var n={},a={"contao-list-bundle":0};function i(t){if(n[t])return n[t].exports;var a=n[t]={i:t,l:!1,exports:{}};return e[t].call(a.exports,a,a.exports,i),a.l=!0,a.exports}i.e=function(e){var t=[],n=a[e];if(0!==n)if(n)t.push(n[2]);else{var r=new Promise((function(t,i){n=a[e]=[t,i]}));t.push(n[2]=r);var o,s=document.createElement("script");s.charset="utf-8",s.timeout=120,i.nc&&s.setAttribute("nonce",i.nc),s.src=function(e){return i.p+""+({imagesloaded:"imagesloaded","masonry-layout":"masonry-layout"}[e]||e)+".js"}(e);var l=new Error;o=function(t){s.onerror=s.onload=null,clearTimeout(u);var n=a[e];if(0!==n){if(n){var i=t&&("load"===t.type?"missing":t.type),r=t&&t.target&&t.target.src;l.message="Loading chunk "+e+" failed.\n("+i+": "+r+")",l.name="ChunkLoadError",l.type=i,l.request=r,n[1](l)}a[e]=void 0}};var u=setTimeout((function(){o({type:"timeout",target:s})}),12e4);s.onerror=s.onload=o,document.head.appendChild(s)}return Promise.all(t)},i.m=e,i.c=n,i.d=function(e,t,n){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(e,t){if(1&t&&(e=i(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(i.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var a in e)i.d(n,a,function(t){return e[t]}.bind(null,a));return n},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p="/bundles/heimrichhannotlistbundle/",i.oe=function(e){throw console.error(e),e};var r=window.webpackJsonp=window.webpackJsonp||[],o=r.push.bind(r);r.push=t,r=r.slice();for(var s=0;s<r.length;s++)t(r[s]);var l=o;i(i.s="nEhL")}({"5r56":function(e,t){e.exports=utilsBundle},nEhL:function(e,t,n){"use strict";n.r(t);n("5r56");function a(e,t){for(var n=0;n<t.length;n++){var a=t[n];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}var i=function(){function e(){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e)}var t,i,r;return t=e,r=[{key:"init",value:function(){e.initPagination(),e.initMasonry(),e.initEvents(),e.initModal(),e.initVideo()}},{key:"initVideo",value:function(){document.querySelectorAll(".video-player").forEach((function(e){var t=e.querySelector(".play-button"),n=e.querySelector(".poster"),a=e.querySelector("video");t&&t.addEventListener("click",(function(){a.play(),n.classList.add("d-none")}))}))}},{key:"initEvents",value:function(){document.addEventListener("filterAjaxComplete",(function(t){e.updateList(t.detail)}))}},{key:"initModal",value:function(){if(void 0!==window.jQuery){var e=location.href;document.querySelectorAll('.huh-list .items[data-open-list-items-in-modal="1"]').length<1||(document.querySelectorAll('.huh-list .items[data-open-list-items-in-modal="1"]').forEach((function(t){var n="modal-"+t.closest(".wrapper").getAttribute("id");window.jQuery("#"+n).on("hidden.bs.modal",(function(t){history.pushState({modalId:n},"",e)}))})),addEventListener("popstate",(function(e){window.jQuery("#"+e.state.modalId).modal("hide")})),utilsBundle.event.addDynamicEventListener("click",'.huh-list .items[data-open-list-items-in-modal="1"] .item .details.modal-link',(function(e,t){t.preventDefault(),utilsBundle.ajax.get(e.getAttribute("href"),{},{onSuccess:function(t){var n=document.createElement("div"),a=e.closest(".items"),i=a.getAttribute("data-list-modal-reader-type"),r=a.getAttribute("data-list-modal-reader-css-selector"),o=a.getAttribute("data-list-modal-reader-module"),s="modal-"+a.closest(".wrapper").getAttribute("id"),l=null;switch(n.innerHTML=t.response.trim(),i){case"huh_reader":if(null===(l=n.querySelector("#huh-reader-"+o)))return void console.warn("Reader not found with selector: #huh-reader-"+o);break;case"css_selector":if(null===(l=n.querySelector(r)))return void console.warn("Reader not found with selector: "+r)}null!==l&&(document.getElementById(s).querySelector(".modal-content .modal-body").innerHTML=l.outerHTML,window.jQuery("#"+s).modal("show"),history.pushState({modalId:s},"",e.getAttribute("href")),history.pushState({modalId:s},"",e.getAttribute("href")))}})})))}}},{key:"isAtBottom",value:function(e){return e.getBoundingClientRect().bottom<=(window.innerHeight||document.getElementById("main").clientHeight)}},{key:"initPagination",value:function(){document.querySelectorAll(".huh-list .ajax-pagination").forEach((function(t){var a=t.closest(".huh-list"),i=a.querySelector(".items");if(a&&i){var r={loadingHtml:'<div class="loading"><span class="text">Lade...</span></div>',enableScreenReaderMessage:!0,screenReaderMessage:"Es wurden neue Einträge zur Liste hinzugefügt.",disableLiveRegion:!1};!t.hasAttribute("data-disable-live-region")||!0!==t.getAttribute("data-disable-live-region")&&"1"!==t.getAttribute("data-disable-live-region")&&"true"!==t.getAttribute("data-disable-live-region")||(r.disableLiveRegion=!0),r.disableLiveRegion||(i.setAttribute("aria-busy","false"),i.setAttribute("aria-live","polite"),i.setAttribute("aria-relevant","additions text"),i.setAttribute("aria-atomic","false"),!t.hasAttribute("data-enable-screen-reader-message")||!0!==t.getAttribute("data-enable-screen-reader-message")&&"1"!==t.getAttribute("data-enable-screen-reader-message")&&"true"!==t.getAttribute("data-enable-screen-reader-message")||(r.enableScreenReaderMessage=!0),t.hasAttribute("data-screen-reader-message")&&(r.screenReaderMessage=t.getAttribute("data-screen-reader-message"))),document.addEventListener("scroll",(function(t){i.hasAttribute("data-add-infinite-scroll")&&"1"===i.dataset.addInfiniteScroll&&e.isAtBottom(a)&&o(t)}),{passive:!0}),t.querySelector("a.next").addEventListener("click",(function(e){e.stopPropagation(),e.preventDefault(),o(e)}));var o=function o(s){var l=new XMLHttpRequest;!i.classList.contains("loading")&&t.querySelector(".huh-list .ajax-pagination a.next")&&(l.onreadystatechange=function(){if(1===l.readyState){if(a.dispatchEvent(new CustomEvent("huh.list.ajax-pagination-loading",{bubbles:!0,detail:{wrapper:a.querySelector(".wrapper"),pagination:t,items:i}})),t.innerHTML=r.loadingHtml,!r.disableLiveRegion){i.setAttribute("aria-busy","true");var s=i.querySelector("span.sr-only");s&&i.removeChild(s)}i.classList.add("loading")}if(4===l.readyState&&200===l.status){var u=l.responseText,d=(new DOMParser).parseFromString(u,"text/html"),c=d.querySelectorAll(".huh-list .items .item");n.e("imagesloaded").then(n.t.bind(null,"vX6Q",7)).then((function(s){if((0,s.default)(c,(function(e){if(!0===r.enableScreenReaderMessage){var t=document.createElement("span");t.classList.add("sr-only"),t.textContent=r.screenReaderMessage,i.appendChild(t)}c.forEach((function(e){i.appendChild(e)}))})),t.innerHTML="",d.querySelector(".huh-list .ajax-pagination a.next")){var l=d.querySelector(".huh-list .ajax-pagination a.next");l.addEventListener("click",(function(e){e.preventDefault(),e.stopPropagation(),o()})),t.appendChild(l)}"1"!==i.dataset.addMasonry?(i.querySelectorAll(".item").forEach((function(e){e.classList.forEach((function(t){t.match(/item_\d+/g)&&e.classList.remove(t)}))})),i.querySelectorAll(".item").forEach((function(e,t,n){var a=t+1;e.classList.remove("odd","even","first","last"),e.classList.add("item_"+a),a%2==0?e.classList.add("even"):e.classList.add("odd"),1===a&&e.classList.add("first"),a===n.length&&e.classList.add("last")})),a.dispatchEvent(new CustomEvent("huh.list.ajax-pagination-loaded",{bubbles:!0,detail:{wrapper:a.querySelector(".wrapper"),pagination:t,items:i}})),r.disableLiveRegion||i.setAttribute("aria-busy","false"),i.classList.remove("loading")):n.e("masonry-layout").then(n.t.bind(null,"hNNL",7)).then((function(){e.initMasonry()}))}))}},l.open("GET",t.querySelector(".huh-list .ajax-pagination a.next").href,!0),l.send())}}else console.warn("Ajax pagination do not contain list or items containers.")}))}},{key:"initMasonry",value:function(){document.querySelectorAll('.huh-list .items[data-add-masonry="1"]').length<1||n.e("masonry-layout").then(n.t.bind(null,"hNNL",7)).then((function(e){var t=e.default;n.e("imagesloaded").then(n.t.bind(null,"vX6Q",7)).then((function(e){var n=e.default;document.querySelectorAll('.huh-list .items[data-add-masonry="1"]').forEach((function(e,a,i){var r={itemSelector:".item",stamp:".stamp-item"},o=e.getAttribute("data-masonry");null!==o&&""!==o&&(r=Object.assign({},r,JSON.parse(o))),n(e,(function(n){new t(e,r).layout()}))}))}))}))}},{key:"updateList",value:function(e){var t=JSON.parse(e.getAttribute("data-response")),n=e.getAttribute("data-list"),a=document.querySelector(n).parentNode;a.outerHTML=t.list,document.dispatchEvent(new CustomEvent("huh.list.list_update_complete",{detail:{list:a,listId:n,filter:e},bubbles:!0,cancelable:!0}))}}],(i=null)&&a(t.prototype,i),r&&a(t,r),e}();document.addEventListener("DOMContentLoaded",i.init)}});