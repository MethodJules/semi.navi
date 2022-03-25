// globals
var myimgmap = myimgmap || {};
$ = jQuery;
var ViewMode = true;

/* Don't show remove image button in edit mode.*/
$('#edit-field-wk-bild-0-remove-button').hide();
Indeko.ImageMap.hideElements();

// wait for images to be fully loaded
$(window).on("load", function() {
    initView(ViewMode);
});
