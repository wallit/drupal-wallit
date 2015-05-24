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
      "description" => "iMoneza Settings",
      "page callback" => "drupal_get_form",
      "page arguments" => array("imoneza_admin"),
      "access arguments" => array("administer imoneza settings"),
      "type" => MENU_NORMAL_ITEM,
    );
  return $items;
}

function imoneza_permission(){
    return array(
        "administer imoneza settings" => array(
            "title" => t("Administer iMoneza Global Settings"),
            "description" => t("Configure iMoneza, including API keys and site-wide defaults")
        )
    );
}

/**
 * <p>Hook that displays the admin configuration page for the iMoneza module.
 *  Delegates to the iMoneza_Admin::create_admin_page() method.
 * </p>
 * @return $form corresponding to the admin form for configuring iMoneza
 */
function imoneza_admin(){
    $imoneza_admin = variable_get("imoneza_admin", new iMoneza_Admin());
    return $imoneza_admin->create_admin_page();

}

/**
 * <p>Primary hook for controlling whether iMoneza displays the paywall.</p>
 *
 * <p>Logic works like this:
 * <ol>
 *  <li>Determine how many nodes are being loaded that iMoneza manages</li>
 *  <li>If there are more than one managed node on this page, or no managed
 *      nodes on this page, default to open. The reason for this is because
 *      the home page, searches, section home pages, etc. all load more than
 *      one node. It will be up to the theme to make sure it only displays
 *      content the publisher is comfortable giving away for free.</li>
 *  <li>If the user requesting the node has the 'administer' role or has
 *      privileges such that the user can edit the content in the node,
 *      we allow them to view the content with the paywall</li>
 *</ol>
 * <p>
 *  At this point, if we're going to show the paywall, we determine
 *  whether to display the javascript paywall, or handle it server-side.
 * </p>
 * @param $nodes the nodes being loaded
 * @param $types the types of the nodes being loaded, not used.
 */
function imoneza_node_load($nodes, $types){
    //check for resource access

    $numManagedNodes = 0;

    $imoneza = variable_get("imoneza", new iMoneza());


    foreach ($nodes as $node){
        if ($imoneza->is_imoneza_managed_node($node)){
            $numManagedNodes++;
            $managedNode = $node;
        }

    }
    if ($numManagedNodes > 1 || $numManagedNodes < 1){
        return;
        //For now, not executing on pages that contain multiple nodes
    }
    $node = $managedNode;

    if (user_access("administer") || user_access("edit any $node->type content")){
        //allow admins to access anything
        return;
    }

    drupal_add_js(IMONEZA__RA_UI_URL . "/assets/imoneza.js", "file");

    if ($imoneza->doDynamic){
        $imoneza->create_dynamic($node);
    }

    if ($imoneza->doServerSideAuth){
        $imoneza->imoneza_template_redirect($node);
    }else if ($imoneza->doClientSideAuth){
        drupal_add_js($imoneza->create_snippet($node), "inline");
    }else{
        //ignore it
        return;
    }

}
