!function($){"use strict";function t(t,e){var i=$(t).attr("name"),a=$(t).val();$(t).hasClass("select-option")&&(i=$(t).parent().data("attribute-name"),a=$(t).data("name"));var o=$(".variations_form").data("product_variations");setTimeout(function(){$(o).each(function(t,o){o.attributes[i]===a&&o.image_link&&($(e+" .slide.first img, "+e+" .product-thumbnails .first img").attr("src",o.image_src),$(e+" .slide.first a, "+e+" .product-thumbnails .first a").attr("href",o.image_link),$(e+" .ux-product-zoom-gallery .slide.first img").attr("src",o.image_link),$(e+" .product-gallery-slider").flickity("select",0))})},1)}if($(".product-info, table.cart").addQty(),setTimeout(function(){$(".select-option").click(function(){t($(this),".product-gallery")}),$('.variations_form select[name*="attribute"]').change(function(){t($(this),".product-gallery")})},300),$(".quick-view, .open-quickview").click(function(e){$(this).after('<div class="ux-loading dark"></div>');var i=$(this).attr("data-prod"),a={action:"ux_quickview",product:i};$.post(ajaxURL.ajaxurl,a,function(e){$.magnificPopup.open({removalDelay:300,items:{src:'<div class="product-lightbox">'+e+"</div>",type:"inline"}}),$(".ux-loading").remove(),$(".product-lightbox .product-gallery-slider").flickity({cellAlign:"center",wrapAround:!0,autoPlay:!1,prevNextButtons:!0,percentPosition:!0,imagesLoaded:!0,lazyLoad:1,pageDots:!1,rightToLeft:!1}),setTimeout(function(){$(".product-lightbox form").hasClass("variations_form")&&$(".product-lightbox form.variations_form").wc_variation_form(),$(".product-lightbox").addQty(),$(".select-option").click(function(){t($(this),".product-lightbox")}),$('.variations_form select[name*="attribute"]').change(function(){t($(this),".product-lightbox")})},600)}),e.preventDefault()}),$(".product-gallery-slider").on("cellSelect",function(){var t=$(this).find(".is-selected").outerHeight();t&&$(this).find(".flickity-viewport").css("height",t)}),!/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent)){var e=$(".product-zoom .easyzoom").easyZoom({loadingNotice:""}),i=e.filter(".product-zoom .easyzoom.first").data("easyZoom");i&&setTimeout(function(){$('select[name*="attribute"]').change(function(){i.swap($(".easyzoom.first img").attr("src"),$(".easyzoom.first a").attr("href"))})},300)}$(".product-gallery-slider").magnificPopup({delegate:"a",type:"image",tLoading:'<div class="ux-loading dark"></div>',removalDelay:300,closeOnContentClick:!0,gallery:{enabled:!0,navigateByImgClick:!1,preload:[0,1]},image:{verticalFit:!1,tError:'<a href="%url%">The image #%curr%</a> could not be loaded.'},callbacks:{beforeOpen:function(){this.st.mainClass="has-product-video"},open:function(){var t=$.magnificPopup.instance,e=$(".product-video-popup").attr("href");e&&(t.items.push({src:e,type:"iframe"}),t.updateItemHTML());var i=$(".mfp-wrap")[0],a=new Hammer(i);a.on("panleft",function(e){e.isFinal&&t.prev()}),a.on("panright",function(e){e.isFinal&&t.next()})}}}),$("a.product-video-popup").click(function(t){$(".product-gallery-slider").find(".first a").click(),setTimeout(function(){var t=$.magnificPopup.instance;t.prev()},10),t.preventDefault()}),$(".zoom-button").click(function(t){$(".product-gallery-slider").find(".is-selected a").click(),t.preventDefault()}),$("body").on("added_to_cart",function(){jQuery(".mini-cart").addClass("active cart-active"),jQuery(".mini-cart").hover(function(){jQuery(".cart-active").removeClass("cart-active")}),setTimeout(function(){jQuery(".cart-active").removeClass("active")},5e3)}),$(".product-thumbnails a").on("click",function(t){t.preventDefault()}),$(".scroll-to-reviews").click(function(t){$(".product-details .tabs-nav li").removeClass("current-menu-item"),$(".product-details .tabs-nav").find("a[href=#panelreviews]").parent().addClass("current-menu-item"),$(".tabs li, .tabs-inner,.panel.entry-content").removeClass("active"),$(".tabs li.reviews_tab, #panelreviews, #tab-reviews").addClass("active"),$(".panel.entry-content").css("display","none"),$("#tab-reviews").css("display","block"),$.scrollTo("#panelreviews",300,{offset:-90}),$.scrollTo(".reviews_tab",300,{offset:-90}),$.scrollTo("#section-reviews",300,{offset:-90}),t.preventDefault()})}(jQuery);