$ = jQuery;
/*
* Create a Namespace for Indeko javascript objects (no objects in global namespace)
*/
var Indeko = Indeko || {};

Indeko.AddForm = (function() {

  var module = {};
  var imageDiv = null;


  /*
   * Show the Submit Button
   */
  function showSubmitButton() {
    $("#edit-submit").show();
  }

  /*
   * Hide the Submit Button
   */
  function hideSubmitButton() {
    $("#edit-submit").hide();
  }

  /**
   * Removes the GUI elements to draw areas.
   */
  function removeGuiElements() {
    $('#areadescription').remove();
    $('#addAreaError').remove();
    $('#addAreaButton').remove();
  }

  /*
   * Check and return true if the JS Object is a DOM element
   */
  function isElement(object){
    return (
      typeof HTMLElement === "object" ? object instanceof HTMLElement : //DOM2
        object && typeof object === "object" && object !== null && object.nodeType === 1 &&
        typeof object.nodeName==="string"
    );
  }

  /*
   * Add Maschek Editor to the image via initView
   */
  function addEditor() {

    $("#edit-field-markierte-bereiche-0-value").val('');
    initView(true);
    Indeko.ImageMap.addNewArea();
    // if no node title is set use the filename as title
    var filename = $(".file").find("a").text();
    if (!$("#edit-title-0-value").val()) {
      $("#edit-title-0-value").val(filename);
    }
  }

  /*
   * If the image gets removed,
   * hide the morphological box and the submit button and wrap up the upload button
   */
  function imageRemoved() {

    Indeko.MorphBox.hide();
    hideSubmitButton();
    removeGuiElements();

  }

  // If the image gets uploaded, show the morphological box and attach Maschek Editor
  function imageAddedEvent() {

    showSubmitButton();
    Indeko.MorphBox.show();
    $(".form-item-field-wk-bild-0").find('label').hide();
    addEditor();
  }

  /*
   * Initialize the create form in Knowledge Map (first time)
   */
  module.init = function() {

    Indeko.ImageMap.hideElements();
    Indeko.MorphBox.hide();
    hideSubmitButton();

    /*
     * The observer looks for modification inside the drupal
     * standard image field. If drupal modifies the html via
     * AJAX/JS, the observer fires and provides more information
     * about the DOM manipulation in the mutations parameter.
     */
    var observer = new MutationObserver(function(mutations) {

      var addedNode = null;

      /*
       * Drupal modifies the DOM multiple times, so it
       * checks each time if the image was added...
       */
      if(mutations[mutations.length-1].addedNodes.length > 0) {
        addedNode = mutations[mutations.length - 1].addedNodes[0];
      }

      if (isElement(addedNode)) {
        //check if the user uploaded an image
        if(addedNode.getElementsByClassName('image-widget').length > 0) {
          var imageAdded = addedNode.getElementsByClassName('image-widget');

          /*
           * If the image tag was added, attach a load function.
           * This function is called after the image tag has loaded
           * the actual image data.
           *
           * Todo: the observer fires 3 times, the last two times the mutations
           * Object contains the img tag, hence this if branch is called twice.
           * This is no problem, because the onload function is attached two times,
           * but the image tag calls it only one time (when the image is loaded).
           */
          $(imageAdded).find('img')[0].onload = function () {
            imageAddedEvent();
          };
        }
        //check if the user clicked the "delete" button
        else if($('#edit-field-wk-bild-0-remove-button', addedNode).length > 0 &&
          $('#edit-field-wk-bild-0-remove-button', addedNode).length > 0) {
          imageRemoved();
        }
      }
    });

    var config = { subtree: true, childList: true };
    observer.observe(document.getElementById('edit-field-wk-bild-wrapper'), config);
  };

  return module;

})();



jQuery(document).ready(function() {

  Indeko.AddForm.init();

});
