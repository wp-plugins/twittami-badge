/*
 * Facebox (for jQuery)
 * version: 1.2 (05/05/2008)
 * @requires jQuery v1.2 or later
 *
 * Examples at http://famspam.com/twittamibox/
 *
 * Licensed under the MIT:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright 2007, 2008 Chris Wanstrath [ chris@ozmm.org ]
 */
(function($) {
  $.twittamibox = function(data, klass) {
    $.twittamibox.loading()

    if (data.ajax) fillTwittamiboxFromAjax(data.ajax)
    else if (data.image) fillTwittamiboxFromImage(data.image)
    else if (data.div) fillTwittamiboxFromHref(data.div)
    else if ($.isFunction(data)) data.call($)
    else $.twittamibox.reveal(data, klass)
  }

  /*
   * Public, $.twittamibox methods
   */

  $.extend($.twittamibox, {
    settings: {
      opacity      : 0,
      overlay      : true,
      loadingImage : '/e/facebox/loading.gif',
      closeImage   : '/twittamibox/closelabel.gif',
      imageTypes   : [ 'png', 'jpg', 'jpeg', 'gif' ],
      twittamiboxHtml  : '\
    <div id="twittamibox" style="display:none;"> \
      <div class="popup"> \
        <table> \
          <tbody> \
            <tr> \
              <td class="tl"/><td class="b"/><td class="tr"/> \
            </tr> \
            <tr> \
              <td class="b"/> \
              <td class="body"> \
                <div class="content"> \
                </div> \
              </td> \
              <td class="b"/> \
            </tr> \
            <tr> \
              <td class="bl"/><td class="b"/><td class="br"/> \
            </tr> \
          </tbody> \
        </table> \
      </div> \
    </div>'
    },

    loading: function() {
      init()
      if ($('#twittamibox .loading').length == 1) return true
      showOverlay()

      $('#twittamibox .content').empty()
      $('#twittamibox .body').children().hide().end().
        append('<div class="loading"><img src="/e'+$.twittamibox.settings.loadingImage+'"/></div>')

      $('#twittamibox').css({
        top:	getPageScroll()[1] + (getPageHeight() / 10),
        left:	385.5
      }).show()

      $(document).bind('keydown.twittamibox', function(e) {
        if (e.keyCode == 27) $.twittamibox.close()
        return true
      })
      $(document).trigger('loading.twittamibox')
    },

    reveal: function(data, klass) {
      $(document).trigger('beforeReveal.twittamibox')
      if (klass) $('#twittamibox .content').addClass(klass)
      $('#twittamibox .content').append(data)
      $('#twittamibox .loading').remove()
      $('#twittamibox .body').children().fadeIn('normal')
      $('#twittamibox').css('left', $(window).width() / 2 - ($('#twittamibox table').width() / 2))
      $(document).trigger('reveal.twittamibox').trigger('afterReveal.twittamibox')
    },

    close: function() {
      $(document).trigger('close.twittamibox')
      return false
    }
  })

  /*
   * Public, $.fn methods
   */

  $.fn.twittamibox = function(settings) {
    init(settings)

    function clickHandler() {
      $.twittamibox.loading(true)

      // support for rel="twittamibox.inline_popup" syntax, to add a class
      // also supports deprecated "twittamibox[.inline_popup]" syntax
      var klass = this.rel.match(/twittamibox\[?\.(\w+)\]?/)
      if (klass) klass = klass[1]

      fillTwittamiboxFromHref(this.href, klass)
      return false
    }

    return this.click(clickHandler)
  }

  /*
   * Private methods
   */

  // called one time to setup twittamibox on this page
  function init(settings) {
    if ($.twittamibox.settings.inited) return true
    else $.twittamibox.settings.inited = true

    $(document).trigger('init.twittamibox')
    makeCompatible()

    var imageTypes = $.twittamibox.settings.imageTypes.join('|')
    $.twittamibox.settings.imageTypesRegexp = new RegExp('\.' + imageTypes + '$', 'i')

    if (settings) $.extend($.twittamibox.settings, settings)
    $('body').append($.twittamibox.settings.twittamiboxHtml)

    var preload = [ new Image(), new Image() ]
    preload[0].src = $.twittamibox.settings.closeImage
    preload[1].src = $.twittamibox.settings.loadingImage

    $('#twittamibox').find('.b:first, .bl, .br, .tl, .tr').each(function() {
      preload.push(new Image())
      preload.slice(-1).src = $(this).css('background-image').replace(/url\((.+)\)/, '$1')
    })

    $('#twittamibox .close').click($.twittamibox.close)
    $('#twittamibox .close_image').attr('src', $.twittamibox.settings.closeImage)
  }
  
  // getPageScroll() by quirksmode.com
  function getPageScroll() {
    var xScroll, yScroll;
    if (self.pageYOffset) {
      yScroll = self.pageYOffset;
      xScroll = self.pageXOffset;
    } else if (document.documentElement && document.documentElement.scrollTop) {	 // Explorer 6 Strict
      yScroll = document.documentElement.scrollTop;
      xScroll = document.documentElement.scrollLeft;
    } else if (document.body) {// all other Explorers
      yScroll = document.body.scrollTop;
      xScroll = document.body.scrollLeft;	
    }
    return new Array(xScroll,yScroll) 
  }

  // Adapted from getPageSize() by quirksmode.com
  function getPageHeight() {
    var windowHeight
    if (self.innerHeight) {	// all except Explorer
      windowHeight = self.innerHeight;
    } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
      windowHeight = document.documentElement.clientHeight;
    } else if (document.body) { // other Explorers
      windowHeight = document.body.clientHeight;
    }	
    return windowHeight
  }

  // Backwards compatibility
  function makeCompatible() {
    var $s = $.twittamibox.settings

    $s.loadingImage = $s.loading_image || $s.loadingImage
    $s.closeImage = $s.close_image || $s.closeImage
    $s.imageTypes = $s.image_types || $s.imageTypes
    $s.twittamiboxHtml = $s.twittamibox_html || $s.twittamiboxHtml
  }

  // Figures out what you want to display and displays it
  // formats are:
  //     div: #id
  //   image: blah.extension
  //    ajax: anything else
  function fillTwittamiboxFromHref(href, klass) {
    // div
    if (href.match(/#/)) {
      var url    = window.location.href.split('#')[0]
      var target = href.replace(url,'')
      $.twittamibox.reveal($(target).clone().show(), klass)

    // image
    } else if (href.match($.twittamibox.settings.imageTypesRegexp)) {
      fillTwittamiboxFromImage(href, klass)
    // ajax
    } else {
      fillTwittamiboxFromAjax(href, klass)
    }
  }

  function fillTwittamiboxFromImage(href, klass) {
    var image = new Image()
    image.onload = function() {
      $.twittamibox.reveal('<div class="image"><img src="' + image.src + '" /></div>', klass)
    }
    image.src = href
  }

  function fillTwittamiboxFromAjax(href, klass) {
    $.get(href, function(data) { $.twittamibox.reveal(data, klass) })
  }

  function skipOverlay() {
    return $.twittamibox.settings.overlay == false || $.twittamibox.settings.opacity === null 
  }

  function showOverlay() {
    if (skipOverlay()) return

    if ($('twittamibox_overlay').length == 0) 
      $("body").append('<div id="twittamibox_overlay" class="twittamibox_hide"></div>')

    $('#twittamibox_overlay').hide().addClass("twittamibox_overlayBG")
      .css('opacity', $.twittamibox.settings.opacity)
      .click(function() { $(document).trigger('close.twittamibox') })
      .fadeIn(200)
    return false
  }

  function hideOverlay() {
    if (skipOverlay()) return

    $('#twittamibox_overlay').fadeOut(200, function(){
      $("#twittamibox_overlay").removeClass("twittamibox_overlayBG")
      $("#twittamibox_overlay").addClass("twittamibox_hide") 
      $("#twittamibox_overlay").remove()
    })
    
    return false
  }

  /*
   * Bindings
   */

  $(document).bind('close.twittamibox', function() {
    $(document).unbind('keydown.twittamibox')
    $('#twittamibox').fadeOut(function() {
      $('#twittamibox .content').removeClass().addClass('content')
      hideOverlay()
      $('#twittamibox .loading').remove()
    })
  })

})(jQuery);