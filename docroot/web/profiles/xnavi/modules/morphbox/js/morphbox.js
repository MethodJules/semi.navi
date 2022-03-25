/**
 * @file
 * Morphologischer Kasten behaviors.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Behavior description.
   */
  Drupal.behaviors.morphbox = {
    attach: function (context, settings) {

      console.log('It works!');
      console.log(settings);
      var App = new Vue({
        el: '#morphbox-vue',
        data: {
          table: '',
          attribute: '',
          dimensions: [],
          attributes: [],
          showDimensionInput: true,
          showAttributeInput: false,
          showDimensionSaveButton: false,
          addDimension: true,
          addAttribute: false,
          morphboxData: { 
            dimensions: [{
              name: 'Dimension 1', 
              attributes: [
                {name: 'Attribute 1'}, 
                {name: 'Attribute 2'}
              ]
            },
            {
                name: 'Dimension 2', 
                attributes: [
                  {name: 'Attribute 1'}, 
                  {name: 'Attribute 2'}
                ]
              },
              {
                name: 'Dimension 3', 
                attributes: [
                  {name: 'Attribute 1'}, 
                  {name: 'Attribute 2'}
                ]
              }
            ]
          }
        },
        methods: {
          buttonClick(e) {
            console.log('kilck');
            e.preventDefault();
            this.dimensions.push(this.table);
            console.log(this.dimensions);
            this.showDimensionInput = false;
            this.addDimension = false;
            this.showAttributeInput = true;
            this.addAttribute = true;
          },
          saveAttribute(e) {
            e.preventDefault();
            this.attributes.push({name: this.attribute})
            console.log(this.attributes);
            this.attribute = '';
            this.showDimensionSaveButton = true;
          },
          saveDimension(e) {
            e.preventDefault();
            let morphboxDimension = {
              name: this.table,
              attributes: this.attributes
            }
            console.log(morphboxDimension);
            this.morphboxData.dimensions.push(morphboxDimension);
            axios.post(settings.baseUrl + '/morphbox/data', {
              morphboxDimension
            }).then((response) => {
              console.log(response);
            });
          }
        }
      })

    }
  };

} (jQuery, Drupal));
