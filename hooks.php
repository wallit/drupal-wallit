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
        $admin = variable_get("imoneza_admin", new iMoneza_Admin());
        $admin->render_imoneza_meta_box($form, $form_state);
    }
}

function imoneza_menu(){
  $items = array();
  $items["admin/settings/imoneza"] = array(
      "title" => "iMoneza",
      "description" => "TBD description",
      "page callback" => "drupal_get_form",
      "page arguments" => array("imoneza_admin"),
      "access arguments" => array("administer imoneza settings"),
      "type" => MENU_NORMAL_ITEM,
    );
  return $items;
}


function imoneza_admin(){
    $imoneza_admin = variable_get("imoneza_admin", new iMoneza_Admin());
    return $imoneza_admin->create_admin_page();

}