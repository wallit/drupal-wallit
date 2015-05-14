<?php

function imoneza_help($path, $arg){
	switch($path){
		case "admin/help#imoneza":
			return "<p>".t("This plugin integrates your Drupal site with the iMoneza service")."</p>";
			break;

	}
}
 
function imoneza_form_alter(&$form, $form_state, $form_id){

	if (strcmp($form_id, "article_node_form") == 0 || strcmp($form_id, "page_node_form") == 0){
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

function imoneza_node_load($nodes, $types){
    //check for resource access
    //TODO do we want to run this if more than one node is displayed on the page?

    if (count($nodes) > 1 || count($nodes) < 1){
        return;
        //For now, not executing on pages that contain multiple nodes
    }
    $node = array_pop($nodes);

    if (user_access("administer") || user_access("edit any $node->type content")){
        //allow admins to access anything
        return;
    }

    $imoneza = variable_get("imoneza", new iMoneza());

    drupal_add_js(IMONEZA__RA_UI_URL . "/assets/imoneza.js", "file");

    if ($imoneza->doDynamic){
        drupal_add_js($imoneza->create_dynamic($node), "inline");
    }

    if ($imoneza->is_imoneza_managed_node($node) && $imoneza->doServerSideAuth){
        $imoneza->imoneza_template_redirect($node);
    }else if ($imoneza->doClientSideAuth){
        drupal_add_js($imoneza->create_dynamic($node), "inline");
    }else{
        //ignore it
        return;
    }

}