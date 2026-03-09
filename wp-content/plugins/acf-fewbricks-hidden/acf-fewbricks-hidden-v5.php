<?php

class acf_field_fewbricks_hidden extends acf_field
{


  /*
    *  __construct
    *
    *  This function will setup the field type data
    *
    *  @type	function
    *  @date	5/03/2014
    *  @since	5.0.0
    *
    *  @param	n/a
    *  @return	n/a
    */

  function __construct()
  {

    /*
        *  name (string) Single word, no spaces. Underscores allowed
        */

    $this->name = 'fewbricks_hidden';


    /*
        *  label (string) Multiple words, can include spaces, visible when selecting a field type
        */

    $this->label = __('Fewbricks Hidden', 'acf-fewbricks-hidden');


    /*
        *  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
        */

    $this->category = 'basic';


    /*
        *  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
        */

    $this->defaults = array(
        'default_value' => '',
        'return_default_value' => 'yes'
    );


    /*
        *  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
        *  var message = acf._e('hidden', 'error');
        */

    $this->l10n = array(
        'error' => __('Error! Please enter a higher value', 'acf-fewbricks-hidden'),
    );


    // do not delete!
    parent::__construct();

  }


  /*
    *  render_field_settings()
    *
    *  Create extra settings for your field. These are visible when editing a field
    *
    *  @type	action
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	$field (array) the $field being edited
    *  @return	n/a
    */

  function render_field_settings($field)
  {

    /*
        *  acf_render_field_setting
        *
        *  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
        *  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
        *
        *  More than one setting can be added by copy/paste the above code.
        *  Please note that you must also have a matching $defaults value for the field name (font_size)
        */
    acf_render_field_setting($field, array(
        'label' => __('Default Value', 'acf-fewbricks-hidden'),
        'instructions' => __('Set a default value for the field', 'acf-fewbricks-hidden'),
        'type' => 'string',
        'name' => 'default_value',
        'prepend' => '',
    ));

    acf_render_field_setting($field, array(
        'label' => __('Adhere to default value changes', 'acf-fewbricks-hidden'),
        'instructions' => __('If the default value should always be used.', 'acf-fewbricks-hidden'),
        'type' => 'radio',
        'name' => 'return_default_value',
        'choices' => array(
            'yes' => 'Yes',
            'no' => 'No',
        ),
        'default_value' => 'yes',
        'prepend' => '',
    ));

  }


  /*
    *  render_field()
    *
    *  Create the HTML interface for your field
    *
    *  @param	$field (array) the $field being rendered
    *
    *  @type	action
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	$field (array) the $field being edited
    *  @return	n/a
    */

  function render_field($field)
  {
    // CSS to hide the field and its wrapper
    ?>
    <style
        type="text/css">.field_key-<?php echo $field['key']; ?>, .acf-<?php echo str_replace('_', '-', $field['key']); ?>, .acf-field-<?php echo str_replace('_', '-', $field['key']); ?>, [data-key="<?php echo $field['key']; ?>"] {
        display: none;
      }</style>
    <input type="hidden" name="<?php echo esc_attr($field['name']) ?>" value="<?php echo esc_attr($field['value']) ?>"
           style="display: none"/>
    <?php
  }

  /*
    *  load_value()
    *
    *  This filter is applied to the $value after it is loaded from the db
    *
    *  @type	filter
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	$value (mixed) the value found in the database
    *  @param	$post_id (mixed) the $post_id from which the value was loaded
    *  @param	$field (array) the field array holding all the field options
    *  @return	$value
    */
  function load_value($value, $post_id, $field)
  {

    // apply setting
    if ($field['return_default_value'] == 'yes') {

      $value = $field['default_value'];

    }

    return $value;

  }

  /*
    *  format_value()
    *
    *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
    *
    *  @type	filter
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	$value (mixed) the value which was loaded from the database
    *  @param	$post_id (mixed) the $post_id from which the value was loaded
    *  @param	$field (array) the field array holding all the field options
    *
    *  @return	$value (mixed) the modified value
    */
  function format_value($value, $post_id, $field)
  {

    // apply setting
    if ($field['return_default_value'] == 'yes') {

      $value = $field['default_value'];

    }

    // return
    return $value;

  }


}


// create field
new acf_field_fewbricks_hidden();
