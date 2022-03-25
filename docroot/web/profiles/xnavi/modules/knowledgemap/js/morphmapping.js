$ = jQuery;
//Create a Namespace for Indeko javascript objects (no objects in global namespace)
var Indeko = Indeko || {};
var props = []; // GUI element rows above knowledge map

var ValidationResult = function() {
	var l_oValidationResult = {
		isTitelValid: false,
		messageTitel: Drupal.t("Add the shape's title."),
		isAreaValid: false,
		messageArea: Drupal.t("Draw your shape on the image first."),
		isMorphboxValid: false,
		messageMorphbox: Drupal.t("Bitte weisen Sie der Kontur Inhalte aus dem Portal zu."),

		/**
		 * @returns {boolean} TRUE if all validations are true, otherwiese FALSE.
		 */
		isValid: function() {
			if (this.isTitelValid && this.isAreaValid && this.isMorphboxValid) {
				return true;
			} else {
				return false;
			}
		},

		l_oInputTitel: {}
	};

	return l_oValidationResult;
};

/**
 * Variables and functions namespace of the image map.
 */
Indeko.ImageMap = Indeko.ImageMap || {
		scalingFactor: 1,
		contentBlockLabel:	$('#block-zfdw-b4-hervorgehobenesformularportalsuchepage-1-wissenskarte').find('label[for=edit-search-api-fulltext]'),
		elemTags: $('#edit-field-tag-combined-und'),
		buttonSave: $('#edit-submit'),
		elemTitle: $("#edit-title-0-value"),
		elemDescription: $("#edit-field-beschreibung-0-value"),
		elemButtonHighlight: $("#button_hightlight"),
    idImageMapCode: '#edit-field-markierte-bereiche-0-value',
	};

/**
 * Variables and functions namespace of the morphological box.
 */
Indeko.MorphBox = {
	// DOM element that contains the representation of the morphological box.
	//element : $('#morphological-box'),
	wholeSearchBox : $('#block-zfdw-b4-hervorgehobenesformularportalsuchepage-1'),
	searchTypeBlock : $('.form-type-select'),
	element : $('#block-zfdw-b4-hervorgehobenesformularportalsuchepage-1'),
	selects : $('#views-exposed-form-search-page-1 .js-form-type-select').find('select'),
  searchJson : '',       // search blocks values before editing knowledge map
  elemFulltext: $('[data-drupal-selector=edit-search-api-fulltext]'),
  elemType: $('[data-drupal-selector=edit-type]'),
  elemInternalUrl: $('#edit-field-internal-reference-0-target-id'),
  elemSidebar: $('.region--sidebar'),
  elemBlockSearchresults: $('#block-zfdw-b4-views-block-knowledgemap-search-results'),
};

/**
 * Initializes knowledge map editor in add/edit modes and attaches map areas to image in view mode.
 *
 * @param {boolean} ViewMode TRUE for viewing a knowledge map, FALSE for adding or editing a knowledge map.
 * @returns {boolean} TRUE if image is found and imagemap can be initialized, otherwise FALSE.
 */
function  initView(ViewMode) {
  var result = false;
  var imageClassName = "";

  if ($(Indeko.ImageMap.idImageMapCode).length > 0) {
    ViewMode = false;
    imageClassName = 'image-style-wissenkarte';
    $('[data-drupal-selector=edit-field-wk-bild-0-preview]').addClass('image-style-wissenkarte');
  }

  if ($('.field--type-knowledgemap-field-type').length > 0) {
    ViewMode = true;
    imageClassName = 'field--type-knowledgemap-field-type';
  }

  if (document.getElementsByClassName(imageClassName).length > 0){
    result = true;
    var l_oImageEdit = document.getElementsByClassName('image-style-wissenkarte');
    var l_oImageView = document.getElementsByClassName('field--type-knowledgemap-field-type');

    if (l_oImageEdit.length > 0){
      // Edit and Add Mode
      myimgmap = {};
      var loadedValue = $(Indeko.ImageMap.idImageMapCode).val();

      instanciate_maschek_image(l_oImageEdit[0]);				// instantiate image map object
      instanciateAreaDescription();							// load GUI
      myimgmap.setMapHTML(loadedValue);						// load image map areas
      Indeko.ImageMap.hookSaveButton(); 						// attach client side validation to save button

      // converts the standard portal search block to be usable to link content to knowledge maps
      if ($(Indeko.MorphBox.element).length > 0) {
        Indeko.MorphBox.convertMorphsearch();
      }

      myimgmap.loadStrings(imgmapStrings);					// load status messages
      makeUnselectable($('#edit-field-wk-bild').find('img'));
    } else if (l_oImageView.length > 0) {
      // ViewMode
      myimgmap = {};



      // read map id and attach to image
      var l_oPicContainer = $('.field--type-knowledgemap-field-type');
      var imageMapHtml = $.parseHTML($('.field--name-field-markierte-bereiche').text());
      l_oPicContainer.append(imageMapHtml);

      var l_sId = $(imageMapHtml).attr('id');
      l_oPicContainer.find('img').attr('USEMAP', '#' + l_sId);


      // initial search block message
      // $('#block-views-block-knowledgemap-search-results').find('.view-empty').text(Drupal.t('Click on an area of the knowledge map.'));

      Indeko.ImageMap.hookMapAreas();

      var guiArea = $('.node--type-wissenskarte.node--view-mode-full');
      var textHideAreas = Drupal.t("Hide areas");
      var textShowAreas = Drupal.t("Show areas");

      guiArea.prepend('<div id="button-hide" class="areashow btn">' + textShowAreas + '</div></span>');

      // hook button to show hide map areas, if enabled [ID 103]
      //if(Indeko.ImageMap.elemButtonHighlight.length) {
        Indeko.ImageMap.hookButtonHighlighting();
      //}

      Indeko.ImageMap.addTooltip();
      //$('map').imageMapResize();
    }
  }

  return result;
}

/**
 * Instanciates the imagemap object to draw areas and interact with the GUI.
 *
 * @param p_oPic The DOM image element.
 */
function instanciate_maschek_image(p_oPic){
	myimgmap = new imgmap({
		mode : "editor",
		custom_callbacks : {
			'onAddArea'       : function(id)  {gui_addArea(id);},//to add new form element on gui
			'onRemoveArea'    : function(id)  {gui_removeArea(id);},//to remove form elements from gui
			'onAreaChanged'   : function(obj) {gui_areaChanged(obj);},// update form elements with selected area values
			'onSelectArea'    : function(obj) {gui_selectArea(obj);},//to select form element when an area is clicked
			'onHtmlChanged'   : function(str) {gui_htmlChanged(str);},// to update "markierte Bereiche"
			'onDrawArea'      : function(id)  {gui_updateArea(id);}, // to update drawn area
			'onStatusMessage' : function(str) {gui_statusMessage(str);},// to display status messages on gui
			'onLoadImage'     : function(pic) {Indeko.ImageMap.scale(pic);} // scale image map areas to current image display size
		},
		pic_container: p_oPic, // element containing the image
		bounding_box : false,
		label : "%t",
		hint: "%t %h",
		label_style: 'font-family: sans-serif; font-size: 87.5%; color: #444',
		draw_opacity: '50',
		CL_NORM_SHAPE: '#0000FF',
		CL_DRAW_SHAPE: '#0000FF',
		CL_HIGHLIGHT_SHAPE: '#0000FF'
	});

	myimgmap.useImage(p_oPic);
}

function instanciateAreaDescription(){

	// TODO: edit content type wissenskarte to add div wrapper around image for clear identification
	var guiArea = $('#edit-field-wk-bild-wrapper');
	var textHideAreas = Drupal.t("Hide areas");
	var textShowAreas = Drupal.t("Show areas");
  guiArea.prepend('<div id="maparea-desciption"><textarea class="form-textarea form-control" id="img_description" name="img_description" cols="160" rows="1" placeholder="Beschreibung für gezeichneten Bereich"></textarea></div>');
  $('#img_description').keyup(Indeko.MorphBox.getSelectedValuesFromMorphBox);

	guiArea.prepend('<span class="inline-block"><div id="addAreaButton" class="addAreaButton" value="" ></div>' +
    				'<div id="button-hide" class="area-show">' + textHideAreas + '</div></span>');
	guiArea.prepend('<label id="addAreaError" class="labelAreaErrorText" />');
	guiArea.prepend('<div id="areadescription"></div>');

	//clickevent an addAreaButton
	$('#addAreaButton').click(function () {


		var l_oResult = validateLastArea();
		validateHighlight(l_oResult);

		if (l_oResult.isValid()) {
			Indeko.ImageMap.addNewArea();      // add new area on validation success...
			Indeko.MorphBox.reset();
		}
	});

    // Hide/show drawn canvas areas on click.
	var btnHide = $('#button-hide');
    btnHide.click(function () {
      Indeko.ImageMap.scale($('img.image-style-wissenkarte')[0]);

    	// Hide
        if(btnHide.hasClass("area-show")) {
            btnHide.toggleClass("area-show");
            btnHide.text(textShowAreas);
            Indeko.ImageMap.setCanvasVisibility(true);

        // Show
        } else {
            btnHide.toggleClass("area-show");
            btnHide.text(textHideAreas);
            Indeko.ImageMap.setCanvasVisibility(false);
        }
    });
}

/**
 * Sets the visibility of drawn knowledge map areas.
 *
 * @param {boolean} hide TRUE if areas should be hidden, otherwise FALSE.
 */
Indeko.ImageMap.setCanvasVisibility = function (hide) {
    var image = $('.pic_container');

    if (hide === true) {
        image.find('canvas').addClass('canvas-hidden');
        image.find('.imgmap_label').addClass('canvas-hidden');
	} else if (hide === false) {
        image.find('canvas').removeClass('canvas-hidden');
        image.find('.imgmap_label').removeClass('canvas-hidden');
	}
};

/**
 * Checks the status of the hide area button and sets canvas visibility accordingly.
 */
Indeko.ImageMap.updateCanvasVisibility = function () {
    // Hide all previously drawn areas if user has chosen to hide all (but the currently active) areas.
    if (!$('#button-hide').is(".area-show")) {
        Indeko.ImageMap.setCanvasVisibility(true);
    }
};

/*
 * Highlights knowledge map form elements that failed the validation and displays warning messages.
 * @param l_oResult validation result object
 */
function validateHighlight(l_oResult) {
	$('#addAreaError').text("");

	if (l_oResult.isAreaValid === false){
		$('#addAreaError').append("<br>").append(l_oResult.messageArea);
		$('.image-style-wissenkarte').addClass('addAreaError');
	} else {
		$('.image-style-wissenkarte').removeClass('addAreaError');
	}

	if (l_oResult.isTitelValid === false){
		$('#addAreaError').append("<br>").append(l_oResult.messageTitel);
		$(l_oResult.l_oInputTitel).addClass('addAreaError');
	} else {
		$('input').removeClass('addAreaError');
	}

	if (l_oResult.isMorphboxValid === false) {
		$('#addAreaError').append("<br>").append(l_oResult.messageMorphbox);
		Indeko.MorphBox.element.addClass('addAreaError');
	} else {
		Indeko.MorphBox.element.removeClass('addAreaError');
	}
}

function validateLastArea(){
	var l_oValidationResult = new ValidationResult();

	if (myimgmap.currentid > -1){
		var l_oArea = $('#img_area_'+myimgmap.currentid);
		if (l_oArea.length > 0) {
			var l_oInputTitel = $('#img_area_'+myimgmap.currentid).find('input[name=img_alt]');
			if (l_oInputTitel.val().trim() != "") {
				l_oValidationResult.isTitelValid = true;
				l_oValidationResult.message = "";
			} else {
				l_oValidationResult.l_oInputTitel = l_oInputTitel;
			}
		} else {
			l_oValidationResult.isTitelValid = true;
			l_oValidationResult.message = "";
		}
	}

	if (getValidAreaCount() == $('input[name=img_alt]').length){
		l_oValidationResult.isAreaValid = true;
	} else {
		l_oValidationResult.isAreaValid = false;
	}

  if ($(Indeko.MorphBox.element).length > 0) {
    /* Check if the drawn area is linked to content on the website. */
    var searchObject = Indeko.MorphBox.toArray();
    if ($.isEmptyObject(searchObject) && myimgmap.areas[0] !== null) {
      l_oValidationResult.isMorphboxValid = false;
    } else {
      l_oValidationResult.isMorphboxValid = true;
    }
  } else {
    l_oValidationResult.isMorphboxValid = true;
  }


	return l_oValidationResult;
}

/* validate all ares */
function validateAllAreas(){
	var l_bIsValid = true;

	// all titels inputs from areas
	var l_aInputTitelfromAreas = $('input[name=img_alt]');

	/* validate all titels */
	for (var i = 0; i < l_aInputTitelfromAreas.length; i++) {
		if ($(l_aInputTitelfromAreas[i]).val().trim() == ""){
			$(l_aInputTitelfromAreas[i]).addClass('addAreaError');
			l_bIsValid = false;
		} else {
			$(l_aInputTitelfromAreas[i]).removeClass('addAreaError');
		}
	}

	/* Validate linked content.	Shouldn't be possible to fail after validateLastArea() check since a user cannot
	 * empty the morphological box through the GUI (always at least '*' search returned) */
	var allAreas = myimgmap.areas;
	$.each(allAreas, function(index, area) {
		if(area == null) {
			return;
		}
		if (area.ahref === '' || area.ahref === 'undefined') {
			$($(myimgmap.pic_container).find('canvas')[index]).addClass('canvasError');
			l_bIsValid = false;
		} else {
			$($(myimgmap.pic_container).find('canvas')[index]).removeClass('canvasError');
		}
	});

	if (l_bIsValid === true){
		$('#addAreaError').text("");
		$('input').removeClass('addAreaError');
	} else {
		var l_oValidationResult = new ValidationResult();
		$('#addAreaError').text(l_oValidationResult.messageTitel);
		return false;
	}

	/* validate gui areas */
	if (getValidAreaCount() !== $('input[name=img_alt]').length){
		var l_oValidationResult = new ValidationResult();
		$('#addAreaError').text(l_oValidationResult.messageArea);
		return false;
	}

	return true;
}

/* returns the valid/real count of areas */
function getValidAreaCount(){
	var l_nCount = 0;
	for (var i = 0; i < myimgmap.areas.length; i++) {
		if (myimgmap.areas[i] != null && typeof myimgmap.areas[i] != 'undefined') {
			if (myimgmap.areas[i].tagName == "CANVAS") {
				l_nCount++;
			}
		}
	}

	return l_nCount;
}

function gui_addArea(id) {
	//var id = props.length;
	//id = 1;
	props[id] = document.createElement('DIV');
	$('#areadescription').append(props[id]);

	props[id].id        = 'img_area_' + id;
	props[id].aid       = id;
	props[id].className = 'img_area';
	//hook ROW event handlers
	myimgmap.addEvent(props[id], 'mouseover', gui_row_mouseover);
	myimgmap.addEvent(props[id], 'mouseout',  gui_row_mouseout);
	myimgmap.addEvent(props[id], 'click',     gui_row_click);

	$('<input type="text"  name="img_id" class="img_id" value="' + id + '" readonly="1"/>').appendTo(props[id]);
	$('<input type="radio" name="img_active" class="img_active" id="img_active_'+id+'" value="'+id+'" >').appendTo(props[id]);
	$('.img_active').hide();

	var l_oSelect = $('<select name="img_shape" class="img_shape form-control">').appendTo(props[id]);
	$('<option value="rect">' + "⬛" + '</option>').appendTo(l_oSelect);
	$('<option value="circle">' + "⚫" + '</option>').appendTo(l_oSelect);
	$('<option value="poly">' + "<i>⯂</i>" + '</option>').appendTo(l_oSelect);
	l_oSelect.val("rect");
	if (jQuery.chosen) {
    l_oSelect.chosen({disable_search: true}); // transform to chosen select box
  }

	$('<Label class="img_label">' + Drupal.t("Title") + ':</Label>').appendTo(props[id]);
	$('<input type="text" name="img_alt" class="img_alt form-control" value="">').appendTo(props[id]);
	$('<input type="text" name="img_coords" class="img_coords" value="" style="display: none;">').appendTo(props[id]);

	var removeAreaButton = $('<div class="removeAreaButton" value="" />').appendTo(props[id]);
	removeAreaButton.click(function () {
		// eventuell auslagern
		myimgmap.removeArea(myimgmap.currentid);

		/*
		 * Deletes the polygon, if the user did not finish drawing the polygon with "SHIFT"
		 * and tries to delete the figure via delete button.
		 * This fixes the issue described in CR 1.
		 * Link: "https://trello.com/c/UxwE6Ftb/191-cr-1-als-registrierter-benutzer-mochte-
		 * ich-die-konturen-des-bereichs-sowohl-als-kreise-als-auch-als-rechtecke-und-polygone-zeich"
		 */
		myimgmap.is_drawing = 0;

		validateAllAreas();
		var l_nPropsPosition = props.length > 0 ? props.length - 1 : 0;
		enableDeleteButtonOnSelectedGuiArea(props[l_nPropsPosition]); // enable deletebutton of last area
	});
	enableDeleteButtonOnSelectedGuiArea(props[id]);

	//hook more event handlers to individual inputs
	myimgmap.addEvent($(props[id]).find('input[name=img_alt]')[0],  'change', gui_input_change);
  myimgmap.addEvent($('img_description'),  'change', gui_input_change);

  l_oSelect.change(function(event) {gui_input_change(event)});
	/*if (myimgmap.isSafari) {
	 //need these for safari
	 myimgmap.addEvent(props[id].getElementsByTagName('select')[0], 'change', gui_row_click);
	 }*/

	//set shape as nextshape if set
	if (myimgmap.nextShape) {
		l_oSelect.val(myimgmap.nextShape);
		l_oSelect.trigger('chosen:updated');
	}
	//alert(this.props[id].parentNode.innerHTML);*/


	gui_row_select(id, true);
}

/**
 *	Called from imgmap when an area was removed.
 */
function gui_removeArea(id) {
	if (props[id]) {
		//shall we leave the last one?
		var pprops = props[id].parentNode;
		if (pprops) {
			pprops.removeChild(props[id]);
			props[id] = null;
			try {
				var lastid = pprops.lastChild.aid;
				gui_row_select(lastid, true);
				myimgmap.currentid = lastid;
				Indeko.MorphBox.update(myimgmap.currentid); // update values of morphological box
			}
			catch (err) {
				//alert('noparent');
			}
		}
	}
}

/**
 *	Handles click on props row.
 */
function gui_row_click(e) {
	if (myimgmap.viewmode === 1) {return;}//exit if preview mode
	var obj = (myimgmap.isMSIE) ? window.event.srcElement : e.currentTarget;
	//var multiple = (e.originalTarget.name == 'img_active');
	//myimgmap.log(e.originalTarget);
	if (typeof obj.aid == 'undefined') {obj = obj.parentNode;}
	//gui_row_select(obj.aid, false, multiple);
	gui_row_select(obj.aid, false, false);
	myimgmap.currentid = obj.aid;

	Indeko.MorphBox.update(obj.aid); // Update selected morphological box items

	enableDeleteButtonOnSelectedGuiArea(e.currentTarget)
}

function enableDeleteButtonOnSelectedGuiArea(selectedArea){
	/* check if  area to select exist */
	var l_aAreaToSelect = $('#areadescription').find("#" + selectedArea.id);
	if (l_aAreaToSelect.length === 1) {
		$(props).find('.removeAreaButton').hide();
		$(selectedArea).find('.removeAreaButton').show();
	}
	return false;
}

/**
 *	Handles click on a property row.
 *	@author	Adam Maschek (adam.maschek(at)gmail.com)
 *	@date	2006-06-06 16:55:29
 */
function gui_row_select(id, setfocus, multiple) {
	if (myimgmap.is_drawing) {return;}//exit if in drawing state
	if (myimgmap.viewmode === 1) {return;}//exit if preview mode
	if (!document.getElementById('img_active_'+id)) {return;}
	//if (!multiple)
	gui_cb_unselect_all();
	document.getElementById('img_active_'+id).checked = 1;
	if (setfocus) {
		document.getElementById('img_active_'+id).focus();
	}
	//remove all background styles
	for (var i = 0; i < props.length; i++) {
		if (props[i]) {
			props[i].style.background = '';
		}
	}
	//put highlight on actual props row
	props[id].style.background = '#e7e7e7';

	// Reset areas
    $('.pic_container').find('canvas').removeClass('canvas-top');

    Indeko.ImageMap.updateCanvasVisibility();

	// Bring selected area to top
	var cssId = myimgmap.getMapName() + 'area' + id;
	$('#' + cssId).removeClass('canvas-hidden').addClass('canvas-top');
}

/**
 *	Handles mouseover on props row.
 */
function gui_row_mouseover(e) {
	if (myimgmap.is_drawing) {return;}//exit if in drawing state
	if (myimgmap.viewmode === 1) {return;}//exit if preview mode
	var obj = (myimgmap.isMSIE) ? window.event.srcElement : e.currentTarget;
	if (typeof obj.aid == 'undefined') {obj = obj.parentNode;}
	//console.log(obj.aid);
	myimgmap.highlightArea(obj.aid);
}

/**
 *	Handles mouseout on props row.
 */
function gui_row_mouseout(e) {
	if (myimgmap.is_drawing) {return;}//exit if in drawing state
	if (myimgmap.viewmode === 1) {return;}//exit if preview mode
	var obj = (myimgmap.isMSIE) ? window.event.srcElement : e.currentTarget;
	if (typeof obj.aid == 'undefined') {obj = obj.parentNode;}
	myimgmap.blurArea(obj.aid);
}

/**
 *	Unchecks all checboxes/radios.
 */
function gui_cb_unselect_all() {
	for (var i = 0; i < props.length; i++) {
		if (props[i] && document.getElementById('img_active_'+i)) {
			document.getElementById('img_active_'+i).checked = false;
		}
	}
}

/**
 *	Called when one of the properties change, and the recalculate function
 *	must be called.
 *	@date	2006.10.24. 22:42:02
 *	@author	Adam Maschek (adam.maschek(at)gmail.com)
 */
function gui_input_change(e) {
	// validation first if title field changes
	if (e.target.name === "img_alt") {
		var l_oId = myimgmap.currentid;
		var l_oResult = validateLastArea();

		if (l_oResult.isTitelValid === false){
			$('#addAreaError').append("<br>").append(l_oResult.messageTitel);
			$(l_oResult.l_oInputTitel).addClass('addAreaError');
		} else {
			//$('input').removeClass('addAreaError');
			$('#img_area_'+myimgmap.currentid).find('input[name=img_alt]').removeClass('addAreaError');
			$(myimgmap.pic_container).find('canvas[id*="area' + myimgmap.currentid +'"]').removeClass('canvasError');
			$('#addAreaError').text("");
		}
	}


	if (myimgmap.viewmode === 1) {return;}//exit if preview mode
	if (myimgmap.is_drawing) {return;}//exit if drawing
	//console.log('blur');
	var obj = (myimgmap.isMSIE) ? window.event.srcElement : e.currentTarget;
	//console.log(obj);
	var id = obj.parentNode.aid;
	//console.log(this.areas[id]);
	if (obj.name == 'img_href')        {myimgmap.areas[id].ahref   = obj.value;}
	else if (obj.name == 'img_alt')    {myimgmap.areas[id].aalt    = obj.value; myimgmap.areas[id].atitle  = obj.value;}
	else if (obj.name == 'img_title')  {myimgmap.areas[id].atitle  = obj.value;}
  else if (obj.name == 'img_description')  {myimgmap.areas[id].description  = obj.value;}
  else if (obj.name == 'img_target') {myimgmap.areas[id].atarget = obj.value;}
	else if (obj.name == 'img_shape') {
		if (myimgmap.areas[id].shape != obj.value && myimgmap.areas[id].shape != 'undefined') {
			//shape changed, adjust coords intelligently inside _normCoords
			var coords = '';
			if (props[id]) {
				coords  =  $(props[id]).find('input[name=img_coords]').val();
			}
			else {
				coords = myimgmap.areas[id].lastInput || '' ;
			}
			coords = myimgmap._normCoords(coords, obj.value, 'from'+myimgmap.areas[id].shape);
			if (props[id]) {
				$(props[id]).find('input[name=img_coords]').val(coords);
			}
			myimgmap.areas[id].shape = obj.value;
			myimgmap.nextShape =  obj.value;
			myimgmap._recalculate(id, coords);
			myimgmap.areas[id].lastInput = coords;
		}
		else if (myimgmap.areas[id].shape == 'undefined') {
			myimgmap.nextShape = obj.value;
		}
	}
	if (myimgmap.areas[id] && myimgmap.areas[id].shape != 'undefined') {
		myimgmap._recalculate(id, $(props[id]).find('input[name=img_coords]').val());
		myimgmap.fireEvent('onHtmlChanged', myimgmap.getMapHTML());//temp ## shouldnt be here
	}
}

function gui_areaChanged(area) {
	var id = area.aid;
	if (props[id]) {
		if (area.shape)  {
			$(props[id]).find('select[name=img_shape]').val(area.shape);
			$(props[id]).find('select[name=img_shape]').trigger('chosen:updated');
		}
		if (area.lastInput) {$(props[id]).find('input[name=img_coords]').val(area.lastInput);}
		if (area.aalt)    {$(props[id]).find('input[name=img_alt]').val(area.aalt);}
	}
}

function gui_selectArea(obj) {
	gui_row_select(obj.aid, false, false);

	Indeko.MorphBox.update(obj.aid); // Update selected morphological box items
}

/**
 * Called from imgmap "onHtmlChanged" event with the new html code when changes occur.
 *
 * @param str	html image map code in string format.
 */
function gui_htmlChanged(str) {
	if ($(Indeko.ImageMap.idImageMapCode).length > 0) {
    $(Indeko.ImageMap.idImageMapCode).val(str);
	}
}

/**
 * Adds title and morpholigical box content to the current area if this info was set prior to drawing an area.
 * Called from imgmap "onDrawArea" event.
 *
 * @param id    ID of the area being drawn by user.
 */
// todo testing janzen 18.10
function gui_updateArea(id) {
	// add href and json to area if user already selected values from the morphological box
	Indeko.MorphBox.getSelectedValuesFromMorphBox();

	// add title to area if the user already entered a title prior to drawing an area
	if (props[id]) {
		var areaTitle = $(props[id]).find('input[name=img_alt]').val();
		var areaDescription = $('img_description').val();

		if (!$.isEmptyObject(areaTitle)) {
			myimgmap.areas[id].aalt    = areaTitle;
			myimgmap.areas[id].atitle  = areaTitle;
		}

    if (!$.isEmptyObject(areaDescription)) {
      myimgmap.areas[id].description    = areaDescription;
    }
	}

	$('.image-style-wissenkarte').removeClass('addAreaError');

	myimgmap.fireEvent('onHtmlChanged', myimgmap.getMapHTML());
}

/**
 * Displays status messages on GUI.
 * Called from imgmap "onStatusMessage" event.
 *
 * @param str	Status message in string format.
 */
function gui_statusMessage(str) {
	var statusArea = $('.form-item-field-wk-bild-und-0').find('label');

	// add class to morphbox block after drawing ends
	if (str === myimgmap.strings.READY) {
        Indeko.MorphBox.element.addClass('drawfinished');
	} else {
        Indeko.MorphBox.element.removeClass('drawfinished');
    }

	// status strings not loaded properly
	if (typeof str == 'undefined') {
		myimgmap.loadStrings(imgmapStrings);
		return;
	}

	$('.knowledgemapStatusMessage').remove();
	if (str.toLowerCase().indexOf("shift") >= 0) {
		if (statusArea) {
			statusArea.append('<span class="knowledgemapStatusMessage"> ' + str + '</span>');
		}
	}
}

/*
 * Updates morphological box display after selecting a new knowledge map area.
 *
 * @paran id 	ID of the selected area.
 */
// todo testing janzen 18.10
Indeko.MorphBox.update = function(id) {
	Indeko.MorphBox.reset();

	if (myimgmap.areas[myimgmap.currentid] == null) {
    $('#img_description').val("");
  } else {
    $('#img_description').val(myimgmap.areas[myimgmap.currentid].description);
  }

  if (myimgmap.areas[id] === null || typeof myimgmap.areas[id].json === "undefined") { // TODO
		// areas is not valid
		return false;
	}

  if ($(Indeko.MorphBox.element).length > 0) {
    var jsonString = myimgmap.areas[id].json;
    //jsonString = decodeURI(jsonString);

    if (jsonString !== 'undefined') {
      var searchObject = JSON.parse(jsonString);
      Indeko.MorphBox.toSearchblock(searchObject);
    }
  }

	Indeko.MorphBox.selectItems();
};

/*
 * Select items in the morphologocal box that match the data array.
 * !!! Has to be changed depending on the representation of the morphological box !!!
 */
Indeko.MorphBox.selectItems = function() {

  if ($(Indeko.MorphBox.element).length > 0) {
    // TODO direct url prototype
    // check if href is a search string or direct url
    var href = myimgmap.areas[myimgmap.currentid].ahref;
    // TODO 2020if (href.indexOf(Drupal.settings.morphsearch.searchPath) === -1) {
    $('#direct-url').val(myimgmap.areas[myimgmap.currentid].ahref);
  } else {
    // todo testing 18.10
    var jsonString = myimgmap.areas[myimgmap.currentid].json;
    //jsonString = decodeURI(jsonString);
    var searchObject = JSON.parse(jsonString);
    Indeko.MorphBox.toSearchblock(searchObject);
  }
};



/*
 * Reset the morphological box to initial display state.
 * !!! Has to be changed depending on the representation of the morphological box !!!
 */
Indeko.MorphBox.reset = function() {

  if ($(Indeko.MorphBox.element).length > 0) {
    //Indeko.Morphsearch.reset();
    Indeko.MorphBox.elemFulltext.val(''); // ID 34 do not reset fulltext field on reset, so have to do it here
    Indeko.MorphBox.elemType.val('All')
  }

	// TODO direct url prototype
	// clear the url textfields
	$('#direct-url').val('');
  Indeko.MorphBox.elemInternalUrl.val('');

  $('#img_description').val('');
	// Remove class on morphbox block
    Indeko.MorphBox.element.removeClass('drawfinished');
};

/**
 * Show the Morphological Box
 */
Indeko.MorphBox.show = function() {
	Indeko.MorphBox.element.show();
};

/**
 * Hide the Morphological Box
 */
Indeko.MorphBox.hide = function() {
	Indeko.MorphBox.element.hide();
};

/**
 * Converts the standard portal search block to be used to link content to knowledge maps.
 */
Indeko.MorphBox.convertMorphsearch = function() {
  Indeko.MorphBox.searchJson = JSON.stringify(Indeko.MorphBox.toArray());                      // save search block state to restore it later
  Indeko.MorphBox.reset();
  Indeko.MorphBox.element.addClass('knowledgemap');											// add class for knowledgemap block styling
  Indeko.ImageMap.contentBlockLabel.text(Drupal.t("Inhalt des Bereichs"));						// change label of the search block
  Indeko.MorphBox.elemSidebar.find('.block:not(#block-hervorgehobenesformularsearchpage-1-wissenskarte)').hide(); // hide others blocks in first sidebar
  $('.morphblocktable').remove();                                                 				// remove standard search block search / reset / save elements
  Indeko.MorphBox.selects.change(Indeko.MorphBox.getSelectedValuesFromMorphBox);  				// changelistener for comboboxes in MorpBox
  Indeko.MorphBox.searchTypeBlock.click(Indeko.MorphBox.getSelectedValuesFromMorphBox);			// clickevent for Inhaltstypen
  Indeko.MorphBox.elemFulltext.unbind().keyup(Indeko.MorphBox.getSelectedValuesFromMorphBox);  // keyuplistener for fulltext field
  $('#views-exposed-form-search-page-1 .js-form-type-select').show();
	// TODO direct url prototype
	// add a textfield for map area direct links
  Indeko.MorphBox.element.find('label:first').after('<h4>' + Drupal.t('Direktlink') + '</h4>');
	var htmlDirectUrl = '<div class="form-type-textfield direct-url">' +
		'<input type="text" id="direct-url" class="direct-url form-text form-element--type-text  form-control" value="" placeholder="' + Drupal.t('Add a link') + '"></div>'
		 + '<h4>' + Drupal.t("oder Portalsuche") + '</h4>';
  Indeko.MorphBox.elemFulltext.before(htmlDirectUrl);
  Indeko.MorphBox.elemFulltext.attr('placeholder', Drupal.t('Add internal search'))
  $('#direct-url').keyup(Indeko.MorphBox.getSelectedValuesFromMorphBox);
  Indeko.MorphBox.element.find('label[for=edit-type]').hide();

  // TODO internal url prototype
  /*var elemInternalUrl = Indeko.MorphBox.elemInternalUrl;
  if (elemInternalUrl.length > 0) {
    elemInternalUrl.detach();
    $('.form-type-textfield.direct-url').append(elemInternalUrl);
    $('#edit-field-internal-reference-und-add-more').val(Drupal.t('Add internal url'));
    elemInternalUrl.find('.clearfix').show();

    Drupal.behaviors.morphmapping = {
      attach: function(context, settings) {
        elemInternalUrl.ajaxSuccess(function(event, xhr, settings, data) {

          var ajaxInsert = $.parseHTML(data[1].data);
          var nodeId = $(ajaxInsert).find('.entityreference-view-widget-checkbox').val();
          var nodeTitle = $(ajaxInsert).find('label').text();

          // update url if user selected content
          if (nodeTitle.length > 0) {
            $('#direct-url').val(Drupal.settings.basePath + 'node/' + nodeId);
            Indeko.MorphBox.getSelectedValuesFromMorphBox();
          }
        });
      }
    };
	}*/
	Indeko.MorphBox.update(myimgmap.currentid);														// show selected morphological box items of current map area
};

// todo testing janzen 18.10
Indeko.MorphBox.getSelectedValuesFromMorphBox = function(){

	var areaDescription = $('#img_description').val();
  myimgmap.areas[myimgmap.currentid].description = areaDescription;
  myimgmap.fireEvent('onHtmlChanged', myimgmap.getMapHTML());

  if ($(Indeko.MorphBox.element).length > 0) {
    // TODO direct url prototype
    // if direct url given ignore search box parameters
    var directUrl = $('#direct-url').val();
    if (directUrl.length > 0) {

      if (directUrl.indexOf('http') === -1 && directUrl.indexOf(drupalSettings.path.baseUrl) === -1) {
        directUrl = 'http://' + directUrl;
      }

      myimgmap.areas[myimgmap.currentid].ahref = encodeURI(directUrl);
      myimgmap.fireEvent('onHtmlChanged', myimgmap.getMapHTML());
    }
    else {
      var searchObject = Indeko.MorphBox.elemFulltext;
      if (!$.isEmptyObject(searchObject)) {
        var jsonString = JSON.stringify(searchObject);
        //jsonString = encodeURI(jsonString);

        // TODO 2020 myimgmap.areas[myimgmap.currentid].ahref = encodeURI(Indeko.Morphsearch.toUrl(searchObject));
        myimgmap.areas[myimgmap.currentid].ahref = encodeURI(directUrl);
        myimgmap.areas[myimgmap.currentid].json = jsonString;
        Indeko.MorphBox.element.removeClass('addAreaError');
        myimgmap.fireEvent('onHtmlChanged', myimgmap.getMapHTML());

        var searchObject = Indeko.MorphBox.toArray();
        if (!$.isEmptyObject(searchObject)) {
          var jsonString = JSON.stringify(searchObject);
          //jsonString = encodeURI(jsonString);

          // TODO 2020 myimgmap.areas[myimgmap.currentid].ahref = encodeURI(Indeko.MorphBox.toUrl(searchObject));
          myimgmap.areas[myimgmap.currentid].ahref = encodeURI(directUrl);
          myimgmap.areas[myimgmap.currentid].json = jsonString;
          Indeko.MorphBox.element.removeClass('addAreaError');
          myimgmap.fireEvent('onHtmlChanged', myimgmap.getMapHTML());
        }
      }
    }
  }
}

/*
 * Scales image map area coordinates in add and edit mode to the current displayed width in the browser if the image
 * width differs from it's original width.
 * (Drupal automatically scales image width depending on browser width and page (image width in view node can be
 * different from width in add node can be different from width in edit node).
 *
 * @param domImage DOM element containing the image.
 */
Indeko.ImageMap.scale = function (domImage) {

	var parentContainer = $(domImage.parentNode);

	/* Wait until the image is resized after it was put in parentContainer. */
	var timer = window.setTimeout(function() {

		if(domImage.width <= domImage.naturalWidth &&
			domImage.height <= domImage.naturalHeight) {
			Indeko.ImageMap.scalingFactor = domImage.width / domImage.naturalWidth;
			myimgmap.scaleAllAreas(Indeko.ImageMap.scalingFactor);

			window.clearTimeout(timer);
		}
	}, 100);
};

/**
 * Adds client side validation to save / submit button.
 */
Indeko.ImageMap.hookSaveButton = function () {
	Indeko.ImageMap.buttonSave.click(function () {
		var l_bIsValid = true;

		// Error if title is empty
		var titleElement = Indeko.ImageMap.elemTitle;
		if ($.isEmptyObject(titleElement.val())) {
			titleElement.addClass('error');
			if ($('.errorTitle').length === 0) {
				titleElement.after('<p class="errorTitle labelAreaErrorText"><label>' + Drupal.t("Title field is required.") + '</label></p>');
			}
			titleElement.focus();
			l_bIsValid = false;
		} else {
			titleElement.removeClass('error');
			$('.errorTitle').remove();
		}

		// Error if description is empty
		var descriptionElement = Indeko.ImageMap.elemDescription;
		if ($.isEmptyObject(descriptionElement.val())) {
			descriptionElement.addClass('error');
			if ($('.errorDescription').length === 0) {
				descriptionElement.after('<p class="errorDescription labelAreaErrorText"><label>' + Drupal.t("Description field is required.") + '</label></p>');
			}
			descriptionElement.focus();
			l_bIsValid = false;
		} else {
			descriptionElement.removeClass('error');
			$('.errorDescription').remove();
		}

		// guarantee that last drawn area was saved properly
		gui_updateArea(myimgmap.currentid);


		var l_oResult = validateLastArea();
		if (!l_oResult.isValid()) {
			validateHighlight(l_oResult);
			l_bIsValid = false;
		}

		// Validate all drawn areas
		var allAreas = myimgmap.areas;
		var allCanvasAreas = $(myimgmap.pic_container).find('canvas');
		$.each(allAreas, function (index, area) {
			var currentCanvasArea = $(allCanvasAreas[index]);
			if (area == null) {
				return;
			}

			// validate linked content through MorphBox
      if ($(Indeko.MorphBox.element).length > 0) {
        if ($.isEmptyObject(area.ahref) && $.isEmptyObject(area.json)) {
          currentCanvasArea.addClass('canvasError');
          Indeko.MorphBox.element.addClass('addAreaError');
          l_bIsValid = false;
        }
      }

			// validate area titles
			if ($.isEmptyObject(area.atitle)) {
				currentCanvasArea.addClass('canvasError');
				$('#img_area_' + index).find("input[name=img_alt]").addClass("addAreaError");
				l_bIsValid = false;
			}
		});

		// if all knowledge map values are valid
		if (l_bIsValid) {

			// update map areas before saving
			myimgmap.fireEvent('onHtmlChanged', myimgmap.getMapHTML());
			Indeko.MorphBox.reset();

			// restore the search block to the state prior to editing / creating the knowledge map
			localStorage["searchValues"] = Indeko.MorphBox.searchJson;

			// add selected morphological elements of each drawn knowledge map area as tags
			var jsonString = '';
			Indeko.ImageMap.elemTags.val(-1); // clear tags field
			$.each(allAreas, function (index, area) {
				if (!$.isEmptyObject(area.json) && area.json != "undefined") {
					jsonString = area.json;
					var searchObject = JSON.parse(jsonString);

					// add tags to select field
					$.each(searchObject.morphological, function (index, value) {
						Indeko.ImageMap.elemTags.find('option[value=' + value + ']').attr('selected', 'selected');
					});
				}
			});
		}

		return l_bIsValid;

	});
};


/**
 * Updates search block on click on map areas.
 */
Indeko.ImageMap.hookMapAreas = function () {
    $("map area").click(function () {

      // If a link is attached to the map area, follow it
      var href = $(this).attr('href');

      if (href !== '') {
        if(href.indexOf('http') === -1 && href.indexOf(drupalSettings.path.baseUrl) === -1) {
          window.location = 'http://' + href;
        } else {
          return true;
        }

        // If search results should be displayed in the AJAX block view besides the knowledge map
      } else if (Indeko.MorphBox.elemBlockSearchresults.length) {
        var jsonString = $(this).attr('data-json');

        // Get search parameters and execute the AJAX call.
        var searchObject = JSON.parse(jsonString);
        searchObject.fulltext = decodeURI(searchObject.fulltext);
        //Indeko.MorphBox.elemFulltext.val(searchObject.fulltext);
        //Indeko.MorphBox.elemType.val(searchObject.type);

        const resultBlock = Indeko.MorphBox.elemBlockSearchresults;
        resultBlock.find('[data-drupal-selector=edit-search-api-fulltext]').val(searchObject.fulltext);
        resultBlock.find('[data-drupal-selector=edit-type]').val(searchObject.type);
        resultBlock.find('[id^=edit-submit-knowledgemap-search-results]').click();

        // Update block title with area title.
        // Use "alt" instead of "title" to prevent problems with qTip2 library editing "title" attribute.
        var areaTitle = decodeURI($(this).attr('alt'));
        Indeko.MorphBox.elemBlockSearchresults.find('h2').text(areaTitle.replace(/(.{60})..+/, "$1…"));

        // Update morphsearch block search.
        /*        Indeko.MorphBox.reset();
                Indeko.MorphBox.toSearchblock(searchObject);*/

        // Don't follow the clicked href link.
        return false;
      }

      // knowledgemap with mouseover tooltips only (no links or search)
      return false;
    })
};


/**
 * Adds a new area to the image map.
 */
Indeko.ImageMap.addNewArea = function () {
    myimgmap.addNewArea();

    Indeko.ImageMap.updateCanvasVisibility();
};

/**
 * Hide image map text section (marked areas text field).
 */
Indeko.ImageMap.hideElements = function() {
	$(Indeko.ImageMap.idImageMapCode).hide();
};

/**
 * Adds the tooltip to knowledge map areas.
 */
Indeko.ImageMap.addTooltip = function() {

  tippy('map area', {
    content(reference) {
      let tooltip = '';
      const title = reference.getAttribute('title');
      reference.removeAttribute('title');
      const description = reference.getAttribute('data-description');
      tooltip += title;

      if (description) {
        tooltip += '</br>' + description
      }

      return tooltip;
    },
    allowHTML: true,
    followCursor: 'initial',
    duration: [250,250],
    delay: [500,0],
    offset: [0,49]
  });

};


/**
 * Toggles knowledge map areas highlighting.
 * Styling of highlighting is set in module jq_maphilight (ATLAS version). [ID 103]
 * TODO admin menu to set button highlighting options. Current settings: areas get filled and outlined.
 */
Indeko.ImageMap.hookButtonHighlighting = function() {
  var btn = $("#button-hide");
  var mapAreas = $('map area');
  var options = mapAreas.data('maphilight') || {};

  // Toggles button text and class
  function buttonToggle() {
    if(btn.hasClass("areashow")) {
      btn.removeClass("areashow").addClass("areahide");
      btn.text(drupalSettings.jq_maphilight.stringAreaShow);
    } else if(btn.hasClass("areahide")) {
      btn.removeClass("areahide").addClass("areashow");
      btn.text(drupalSettings.jq_maphilight.stringAreaHide);
    }
  }

  // Adjust the button if jq_maphilight (ATLAS version) module is set to always display map areas
  if(drupalSettings.jq_maphilight !== undefined && drupalSettings.jq_maphilight !== null) {
    if (drupalSettings.jq_maphilight.alwaysOn === "true") {
      buttonToggle();
    }
  }

  btn.click(function() {

    // enable highlighting
    if(btn.hasClass("areashow")) {
      buttonToggle();
      drupalSettings.jq_maphilight.alwaysOn = "true";
      options.alwaysOn = true;
      options.fill = true;
      options.stroke = true;

      mapAreas.data('maphilight', options).trigger('alwaysOn.maphilight');

      // attach additonal hover effect to map areas
      //Drupal.behaviors.jq_maphilight.attachHover(mapAreas);

      // disable highlighting
    } else if(btn.hasClass("areahide")) {
      buttonToggle();
      drupalSettings.jq_maphilight.alwaysOn = "false";
      options.alwaysOn = false;
      options.stroke = false;
      options.fill = false;
      mapAreas.data('maphilight', options).trigger('alwaysOn.maphilight');

      mapAreas.unbind(".maphilight");
    }
  });
};

function escapeHtml(string) {
    var entityMap = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': '&quot;',
        "'": '&#39;',
        "/": '&#x2F;'
    };

    // Escape only quotation marks as they could break the client side generated map html code.
    return String(string).replace(/["']/g, function (s) {
		return entityMap[s];
	});
}

/**
 * Prevents an element from being (accidentially) selected or dragged.
 * (http://stackoverflow.com/questions/12906789/preventing-an-image-from-being-draggable-or-selectable-without-using-js)
 *
 * @param $target DOM target to make unselectable.
 */
function makeUnselectable($target) {
    $target
        .addClass( 'unselectable' ) // All these attributes are inheritable
        .attr( 'unselectable', 'on' ) // For IE9 - This property is not inherited, needs to be placed onto everything
        .attr( 'draggable', 'false' ) // For moz and webkit, although Firefox 16 ignores this when -moz-user-select: none; is set, it's like these properties are mutually exclusive, seems to be a bug.
        .on( 'dragstart', function() { return false; } );  // Needed since Firefox 16 seems to ingore the 'draggable' attribute we just applied above when '-moz-user-select: none' is applied to the CSS

    $target // Apply non-inheritable properties to the child elements
        .find( '*' )
        .attr( 'draggable', 'false' )
        .attr( 'unselectable', 'on' );
}

/* todo com, function copied from imgmap */
imgmap.prototype.getMapInnerHTML = function(flags) {
	var html, coords;
	html = '';
	//foreach area properties
	for (var i=0, le = this.areas.length; i<le; i++) {
		if (this.areas[i]) {
			if (this.areas[i].shape && this.areas[i].shape != 'undefined') {
				coords = this.areas[i].lastInput;
				if (flags && flags.match(/noscale/)) {
					//for preview use real coordinates, not scaled
					var cs = coords.split(',');
					for (var j=0, le2 = cs.length; j<le2; j++) {
						cs[j] = Math.round(cs[j] * this.globalscale);
					}
					coords = cs.join(',');
				}
				html+= '<area shape="' + this.areas[i].shape + '"' +
                    // Escape user input title. It may containt characters that break qTip2 tooltip module (e.g. quotation marks).
                    ' alt="' + escapeHtml(this.areas[i].aalt) + '"' +
					' title="' + escapeHtml(this.areas[i].atitle) + '"' +
					' id="' + this.areas[i].id + '"' +
					' coords="' + coords + '"' +
					' href="' +	this.areas[i].ahref + '"' +
					' data-json="' + escapeHtml(this.areas[i].json) + '"' +
					' data-description="' + escapeHtml(this.areas[i].description) + '"' +
					' target="' + this.areas[i].atarget + '" />';
			}
		}
	}
	//alert(html);
	return html;
};

// todo check comment
/**
 *	Sets the coordinates according to the given HTML map code or DOM object.
 *	@author	Adam Maschek (adam.maschek(at)gmail.com)
 *	@date	2006-06-07 11:47:16
 *	@param	map	DOM object or string of a map you want to apply.
 *	@return	True on success
 */
imgmap.prototype.setMapHTML = function(map) {
	if (this.viewmode === 1) {return;}//exit if preview mode

	this.fireEvent('onSetMap', map);
	//this.log(map);
	//remove all areas
	this.removeAllAreas();
	//console.log(this.areas);

	var oMap;
	if (typeof map == 'string') {
		var oHolder = document.createElement('DIV');
		oHolder.innerHTML = map;
		oMap = oHolder.firstChild;
	}
	else if (typeof map == 'object') {
		oMap = map;
	}
	if (!oMap || oMap.nodeName.toLowerCase() !== 'map') {return false;}
	this.mapname = oMap.name;
	this.mapid   = oMap.id;
	var newareas = oMap.getElementsByTagName('area');
	var shape, coords, href, alt, title, target, id, json, desc;
	for (var i=0, le = newareas.length; i<le; i++) {
		shape = coords = href = alt = title = target = '';

		id = this.addNewArea();//btw id == this.currentid, just this form is a bit clearer

		shape = this._normShape(newareas[i].getAttribute('shape', 2));

		this.initArea(id, shape);

		if (newareas[i].getAttribute('coords', 2)) {
			//normalize coords
			coords = this._normCoords(newareas[i].getAttribute('coords', 2), shape);
			this.areas[id].lastInput = coords;
			//for area this one will be set in recalculate
		}

		href = newareas[i].getAttribute('href', 2);
		// FCKeditor stored url to prevent mangling from the browser.
		var sSavedUrl = newareas[i].getAttribute( '_fcksavedurl' );
		if (sSavedUrl) {
			href = sSavedUrl;
		}
		if (href) {
			this.areas[id].ahref = href;
		}

		alt = newareas[i].getAttribute('alt');
		if (alt) {
			this.areas[id].aalt = alt;
		}

		title = newareas[i].getAttribute('title');
		if (!title) {title = alt;}
		if (title) {
			this.areas[id].atitle = title;
		}

		json = newareas[i].getAttribute('data-json');
		if (json) {
			this.areas[id].json = json;
		}

    desc = newareas[i].getAttribute('data-description');
    if (desc) {
      this.areas[id].description = desc;
    }

		target = newareas[i].getAttribute('target');
		if (target) {target = target.toLowerCase();}
//		if (target == '') target = '_self';
		this.areas[id].atarget = target;

		this._recalculate(id, coords);//contains repaint
		this.relaxArea(id);

		this.fireEvent('onAreaChanged', this.areas[id]);

	}//end for areas
	this.fireEvent('onHtmlChanged', this.getMapHTML());
	return true;
};

/**
 * Converts selected search items to an object.
 *
 * @returns object Object that contains all search items.
 */
Indeko.MorphBox.toArray = function() {

  // search object structure that can be easily converted to and from JSON
  let searchObj = {
    'fulltext' : '',
    'type' : '' ,
    'morphological' : ''};

  // save and parse fulltext search string
  searchObj.fulltext = encodeURI(Indeko.MorphBox.elemFulltext.val());


  // save content type search
  searchObj.type = Indeko.MorphBox.elemType.val();

  // save morphological search by iterating over all select elements in the morphological block

  return searchObj;
};


/**
 * Load search block with values from the searchObj.
 *
 * @param object Object that contains all search items.
 * @see toArray for searchObj structure definition.
 */
Indeko.MorphBox.toSearchblock = function(searchObj) {

  // fill fulltext field
  if (searchObj.fulltext === '*') {
    Indeko.MorphBox.elemFulltext.val('');
  } else {
    Indeko.MorphBox.elemFulltext.val((decodeURI(searchObj.fulltext).replace(/^\(\(\(|\)\)\)$/g, '')));
  }

  // select type search elements
  Indeko.MorphBox.elemType.val(searchObj.type);
};
