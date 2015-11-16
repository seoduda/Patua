;(function ($) {
"use strict";

   // Create QTY Buttons to product pages
  $('.product-info, table.cart').addQty();

  // Update product images
  function updateColors(element, target){
     var attribute_name =  $(element).attr('name');
     var attribute_value = $(element).val();

     // Color Swatches
     if($(element).hasClass('select-option')){
       attribute_name = $(element).parent().data('attribute-name');
       attribute_value = $(element).data('name');
     }
     
     var attribute_data = $('.variations_form').data('product_variations');
       setTimeout(function(){
         $(attribute_data).each(function(index, element){
            if(element.attributes[attribute_name] === attribute_value){
               if(element.image_link){
                  $(target+' .slide.first img, '+target+' .product-thumbnails .first img').attr('src',element.image_src);
                  $(target+' .slide.first a, '+target+' .product-thumbnails .first a').attr('href',element.image_link);
                  $(target+' .ux-product-zoom-gallery .slide.first img').attr('src',element.image_link);
                  $(target+' .product-gallery-slider').flickity( 'select', 0);
                }
            }
        });
      }, 1);
  }

  // Update to correct colors
  setTimeout(function(){
    $('.select-option').click(function(){
       updateColors($(this),'.product-gallery');
    });
    
    $('.variations_form select[name*="attribute"]').change(function(){
       updateColors($(this),'.product-gallery');
    });
  }, 300);


  $('.quick-view, .open-quickview').click(function(e){
     /* add loader  */
     $(this).after('<div class="ux-loading dark"></div>');

     var product_id = $(this).attr('data-prod');
     var data = { action: 'ux_quickview', product: product_id};
      $.post(ajaxURL.ajaxurl, data, function(response) {
       $.magnificPopup.open({
          removalDelay: 300,
          items: {
            src: '<div class="product-lightbox">'+response+'</div>',
            type: 'inline'
          }
        });
         $('.ux-loading').remove();
         $('.product-lightbox .product-gallery-slider').flickity({
                  cellAlign: "center",
                  wrapAround: true,
                  autoPlay: false,
                  prevNextButtons:true,
                  percentPosition: true,
                  imagesLoaded: true,
                  lazyLoad: 1,
                  pageDots: false,
                  rightToLeft: false
         });
         setTimeout(function() {

              // Run Variations Form Scripts
              if ($('.product-lightbox form').hasClass('variations_form')) {
                $('.product-lightbox form.variations_form').wc_variation_form();
              }
              
              // Create QTY Buttons
              $('.product-lightbox').addQty();

              $('.select-option').click(function(){
                  updateColors($(this),'.product-lightbox');
              });

              $('.variations_form select[name*="attribute"]').change(function(){
                  updateColors($(this),'.product-lightbox');
              });
              
      
        }, 600);
      });

      e.preventDefault();
  }); // product lightbox



  // Product gallery slider
  $('.product-gallery-slider').on( 'cellSelect', function() {
    // Set slider heights
    var height = $(this).find('.is-selected').outerHeight();
    if(height) {$(this).find('.flickity-viewport').css('height',height);}
  });


  // Activate product Zoom for non-touch
  if(!/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {

       /* Start Zoom images */
       var $easyzoom = $('.product-zoom .easyzoom').easyZoom({ loadingNotice: '' });

       /* Swap Zoom Images */
       var firstZoomItem = $easyzoom.filter('.product-zoom .easyzoom.first').data('easyZoom');

       if(firstZoomItem) {
         setTimeout(function(){
           $('select[name*="attribute"]').change(function(){
                firstZoomItem.swap($('.easyzoom.first img').attr('src'), $('.easyzoom.first a').attr('href'));
           });
         }, 300);
       }

  }



  /* Product Gallery Popup */
   $('.product-gallery-slider').magnificPopup({
      delegate: 'a',
      type: 'image',
      tLoading: '<div class="ux-loading dark"></div>',
      removalDelay: 300,
      closeOnContentClick: true,
      gallery: {
          enabled: true,
          navigateByImgClick: false,
          preload: [0,1] // Will preload 0 - before current, and 1 after the current image
      },
      image: {
          verticalFit: false,
          tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
      },
      callbacks: {
       beforeOpen: function() {
         this.st.mainClass = 'has-product-video';
        },
        open: function () {
            var magnificPopup = $.magnificPopup.instance;

            // Add product video to gallery popup
            var productVideo = $('.product-video-popup').attr('href');

            if(productVideo){
              magnificPopup.items.push({
                  src: productVideo,
                  type: 'iframe'
              });

              magnificPopup.updateItemHTML();
            }

            // Touch slide popup
            var slidePan = $('.mfp-wrap')[0];
            var mc = new Hammer(slidePan);

            mc.on("panleft", function(ev) {
              if(ev.isFinal){ magnificPopup.prev(); }
            });

            mc.on("panright", function(ev) {
              if(ev.isFinal){ magnificPopup.next(); }
            });
        },
    }
  });

  /* Product Video Popup */
  $("a.product-video-popup").click(function(e){
       $('.product-gallery-slider').find('.first a').click();

       // Go to Video Slide
       setTimeout(function(){
          var magnificPopup = $.magnificPopup.instance;
          magnificPopup.prev();
        }, 10);
       e.preventDefault();
  });

  /* Open product gallery slider */
  $('.zoom-button').click(function(e){
      $('.product-gallery-slider').find('.is-selected a').click();
      e.preventDefault();
  });

  /* Ajax add to cart */
  $('body').on('added_to_cart', function(){
      jQuery('.mini-cart').addClass('active cart-active');
      jQuery('.mini-cart').hover(function(){jQuery('.cart-active').removeClass('cart-active');});
      setTimeout(function(){jQuery('.cart-active').removeClass('active');}, 5000);
  });

  /* Product thumbnails link fix */
  $('.product-thumbnails a').on("click", function (e) {
        e.preventDefault();
  });


  /* reviews link */
  $('.scroll-to-reviews').click(function(e){
      $('.product-details .tabs-nav li').removeClass('current-menu-item');
      $('.product-details .tabs-nav').find('a[href=#panelreviews]').parent().addClass('current-menu-item');
      $('.tabs li, .tabs-inner,.panel.entry-content').removeClass('active');
      $('.tabs li.reviews_tab, #panelreviews, #tab-reviews').addClass('active');
      $('.panel.entry-content').css('display','none');
      $('#tab-reviews').css('display','block');
      $.scrollTo('#panelreviews',300,{offset:-90});
      $.scrollTo('.reviews_tab',300,{offset:-90});
      $.scrollTo('#section-reviews',300,{offset:-90});
      e.preventDefault();
  });
}(jQuery));