(()=>{"use strict";var t={n:e=>{var a=e&&e.__esModule?()=>e.default:()=>e;return t.d(a,{a}),a},d:(e,a)=>{for(var n in a)t.o(a,n)&&!t.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:a[n]})},o:(t,e)=>Object.prototype.hasOwnProperty.call(t,e)};const e=jQuery;var a=t.n(e);a()((function(){var t,e,n,o,s;t=a()(".js-pubguru-connect"),e=t.next(".aa-spinner"),n=a()("#advads-m2-connect"),a()(".js-m2-show-consent").on("click",".button",(function(t){t.preventDefault();var e=a()(this).closest("tr");n.show(),e.addClass("hidden"),e.next().removeClass("hidden")})),a()(".js-pubguru-disconnect").on("click",".button",(function(t){t.preventDefault();var e=a()(this).closest("tr");n.hide(),e.addClass("hidden"),e.prev().removeClass("hidden"),a().ajax({type:"POST",url:ajaxurl,data:{action:"pubguru_disconnect",nonce:advadsglobal.ajax_nonce},dataType:"json"}).done((function(t){if(t.success){var n=a()('<div class="notice notice-success" />');n.html("<p>"+t.data.message+"</p>"),e.closest(".postbox").after(n),setTimeout((function(){n.fadeOut(500,(function(){n.remove()}))}),3e3)}}))})),a()("#m2-connect-consent").on("change",(function(){var e=a()(this);t.prop("disabled",!e.is(":checked"))})),a()("#advads-overview").on("click",".notice-dismiss",(function(t){t.preventDefault();var e=a()(this).parent();e.fadeOut(500,(function(){e.remove()}))})),t.on("click",(function(n){n.preventDefault(),e.addClass("show"),a().ajax({type:"POST",url:ajaxurl,data:{action:"pubguru_connect",nonce:advadsglobal.ajax_nonce},dataType:"json"}).done((function(t){t.success&&(a()(".pubguru-not-connected").hide(),a()(".pubguru-connected").removeClass("hidden"),a()(".pg-tc-trail").toggle(!t.data.hasTrafficCop),a()(".pg-tc-install").toggle(t.data.hasTrafficCop))})).fail((function(e){var n=e.responseJSON,o=a()('<div class="notice notice-error is-dismissible" />');o.html("<p>"+n.data+'</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>'),t.closest(".postbox").after(o)})).complete((function(){return e.removeClass("show")}))})),o=a()("#pubguru-modules"),s=a()("#pubguru-notices"),o.on("input","input:checkbox",(function(){var t=a()(this),e=t.attr("name"),n=t.is(":checked");a().ajax({url:o.attr("action"),method:"POST",data:{action:"pubguru_module_change",security:o.data("security"),module:e,status:n}}).done((function(t){var e=t.data.notice,a=void 0===e?"":e;s.html(""),""!==a&&s.html(a)}))})),s.on("click",".js-btn-backup-adstxt",(function(){var t=a()(this);t.prop("disabled",!0),t.html(t.data("loading")),a().ajax({url:o.attr("action"),method:"POST",data:{action:"pubguru_backup_ads_txt",security:t.data("security")}}).done((function(e){e.success?(t.html(t.data("done")),setTimeout((function(){s.fadeOut("slow",(function(){s.html("")}))}),4e3)):t.html(t.data("text"))})).fail((function(){t.html(t.data("text"))}))})),a()(document).on("click","#dismiss-welcome i",(function(){a().ajax(window.ajaxurl,{method:"POST",data:{action:"advads_dismiss_welcome"},success:function(){a()("#welcome").remove()}})}))}))})();