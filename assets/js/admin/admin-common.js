(()=>{"use strict";var a={n:t=>{var e=t&&t.__esModule?()=>t.default:()=>t;return a.d(e,{a:e}),e},d:(t,e)=>{for(var n in e)a.o(e,n)&&!a.o(t,n)&&Object.defineProperty(t,n,{enumerable:!0,get:e[n]})},o:(a,t)=>Object.prototype.hasOwnProperty.call(a,t)};const t=jQuery;var e=a.n(t);e()((function(){!function(){var a=e()(".advads-tab-menu",".advads-tab-container");a.on("click","a",(function(a){a.preventDefault();var t=e()(this),n=t.closest(".advads-tab-container"),i=e()(t.attr("href"));n.find("a.is-active").removeClass("is-active"),t.addClass("is-active"),n.find(".advads-tab-target").hide(),i.show()})),a.each((function(){var a=e()(this),t=window.location.hash,n=void 0!==t&&t,i=a.find("a:first");n&&a.find("a[href="+n+"]").length>0&&(i=a.find("a[href="+n+"]")),i.trigger("click")}))}()}))})();