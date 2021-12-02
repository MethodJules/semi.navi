(function ($, Drupal, drupalSettings) {
  let initialized = false;

  function init() {
    if(!initialized) {
      initialized = true;

      $('.meetup-link').one('click', function(e) {
          e.preventDefault();
          $.ajax({url: this.href, success: function(result) {
              window.location.reload(true);
/*            $('#meetup-status').load(document.URL +  ' #meetup-status > p');
              $('#meetup-links').load(document.URL +  ' #meetup-links > li');*/
            },
            error: function (xhr, ajaxOptions, thrownError) {
            },
          });
          return false;
        }
      );
    }
  }


  Drupal.behaviors.meetup = {
    attach: function (context, settings) {
      init();
    }
  };
})(jQuery, Drupal, drupalSettings);
