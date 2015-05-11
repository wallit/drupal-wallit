<?php

function imoneza_help($path, $arg){
	switch($path){
		case "admin/help#imoneza":
			return "<p>".t("This plugin integrates your Drupal site with the iMoneza service")."</p>";
			break;

	}
}
 
function imoneza_form_alter(&$form, $form_state, $form_id){
	if (strcmp($form_id, "article_node_form") == 0){
    $form['imoneza'] = array(
      '#type' => 'fieldset',
      '#title' => t('iMoneza'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#group' => 'additional_settings'
    );
    $form['imoneza']['imoneza_options'] = array(
      '#type' => 'textfield',
      '#title' => t('Default options'),
      '#default_value' => t("test"),
      
    '#description' => t('Users with the <em>Administer content</em> permission will be able to override these options.')
    );
  }
	
	
}

function imoneza_menu(){
  $items = array();
  $items["admin/settings/imoneza"] = array(
      "title" => "iMoneza module settings",
      "description" => "TBD description",
      "page callback" => "drupal_get_form",
      "page arguments" => array("imoneza_admin"),
      "access arguments" => array("administer imoneza settings"),
      "type" => MENU_NORMAL_ITEM,
    );
  return $items;
}


function imoneza_admin(){
  return $imoneza_admin->create_admin_page();

}