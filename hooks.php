<?php
function imoneza_help($path, $arg){
	switch($path){
		case "admin/help#imoneza":
			return "<p>".t("This plugin integrates your Drupal site with the iMoneza service")."</p>";
			break;

	}
}
 
function imoneza_form_alter(&$form, $form_state, $form_id){
	
	$form['imoneza'] = array(
    '#type' => 'fieldset',
    '#title' => t('iMoneza'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#group' => 'additional_settings'
  );
  $form['imoneza']['node_options'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Default options'),
    '#default_value' => variable_get('node_options_' . $type->type, array('status', 'promote')),
    '#options' => array(
      'status' => t('Published'),
      'promote' => t('Promoted to front page'),
      'sticky' => t('Sticky at top of lists'),
      'revision' => t('Create new revision'),
    ),
  '#description' => t('Users with the <em>Administer content</em> permission will be able to override these options.')
  );
	
}