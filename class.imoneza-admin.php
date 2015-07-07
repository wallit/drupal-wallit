<?php
/**
 * @file
 * Contains the iMoneza Admin class.
 */

/**
 * Class iMonezaAdmin.
 *
 * Provides an interface for admin functionality, like creating resources
 * etc.
 */
class IMonezaAdmin {
  private $options;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->options = variable_get('imoneza_options');
    if (isset($this->options['imoneza_rm_api_key_access'])
      && $this->options['imoneza_rm_api_key_access'] != ''
      && isset($this->options['imoneza_rm_api_key_secret'])
      && $this->options['imoneza_rm_api_key_secret'] != ''
    ) {
      $this->ready = TRUE;
    }
  }

  /**
   * Returns contents of the Form Javascript.
   *
   * @return string
   *    Contents of the form javascript to be rendered.
   */
  public function renderFormJavascript() {
    return read_file_contents("static/post_form_js.html");
  }

  /**
   * Renders the iMoneza box on a node.
   *
   * @param mixed $form
   *    Form to render the box on.
   * @param mixed $form_state
   *    Form state for the form.
   */
  public function renderImonezaMetaBox(&$form, $form_state) {

    try {

      $form['imoneza'] = array(
        '#type' => 'fieldset',
        '#title' => t('iMoneza'),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#group' => 'additional_settings',
      );

      $post = $form_state["node"];

      // Needed for multival.
      $form["#tree"] = TRUE;

      $resource_management = new IMonezaResourceManagement();
      if (isset($post) && isset($post->nid)) {
        $resource = $resource_management->getResource($post->nid, TRUE);
      }
      else {
        $resource = array("IsManaged" => 0);
      }

      $is_managed = $resource['IsManaged'] == 1
        && $resource['Active'] == 1;

      if (!$is_managed) {
        $property = $resource_management->getProperty();
        $form["imoneza"]["#description"] = t("Not managed by iMoneza");
      }
      else {
        $property = $resource['Property'];
        $form["imoneza"]["#description"] = t("Managed by iMoneza");
      }

      // If there are no pricing tiers, set a default zero
      // tier in case we need it.
      if (!isset($resource['ResourcePricingTiers']) ||
        count($resource['ResourcePricingTiers']) == 0
      ) {
        $resource['ResourcePricingTiers'] = array(
          array('Tier' => 0, 'Price' => '0.00'),
        );
      }

      $form['imoneza']['imoneza_js_area'] = array(
        "#markup" => $this->renderFormJavascript(),
      );

      $form['imoneza']["imoneza_isManaged"] = array(
        "#type" => "checkbox",
        '#title' => t('Use iMoneza to manage access to this resource'),
        '#default_value' => $is_managed,
        '#required' => FALSE,
        "#attributes" => array(
          "onclick" => "imoneza_update_display()",
          "id" => "imoneza_isManaged",
        ),
      );

      $form['imoneza']["imoneza_isManaged_original"] = array(
        "#type" => "hidden",
        "#id" => "imoneza_isManaged_original",
        "#default_value" => $is_managed,

      );

      $form["imoneza"]["imoneza_meta_container"] = array(
        "#type" => "container",
        "#attributes" => array(
          "class" => array(
            "imoneza_row",
          ),
        ),
      );

      $imoneza_container = &$form["imoneza"]["imoneza_meta_container"];

      $imoneza_container["imoneza_metadata"] = array(
        "#markup" => "<strong>Metadata</strong>",
        "#attributes" => array(
          "class" => array("imoneza_row"),
        ),
      );

      $imoneza_container["imoneza_name"] = array(
        "#type" => "textfield",
        "#size" => "25",
        "#default_value" => t(check_plain(isset($resource['Name'])
          ? $resource['Name'] : "")),
        "#title" => t("Name"),
        "#description" => t("A friendly name for the resource to help you identify it. This name is never displayed publicly to consumers. Defaults to the article title."),
      );

      $imoneza_container["imoneza_title"] = array(
        "#type" => "textfield",
        "#size" => 25,
        "#default_value" => t(check_plain(isset($resource['Title'])
          ? $resource['Title'] : "")),
        "#title" => t("Title"),
        "#description" => t("The title of the resource which gets displayed to consumers. Defaults to the article title."),
      );

      $imoneza_container["imoneza_byline"] = array(
        "#type" => "textfield",
        "#size" => 25,
        "#default_value" => t(check_plain(isset($resource['Byline'])
          ? $resource['Byline'] : "")),
        "#title" => t("Byline"),
        "#description" => t("For instance, the author of the post."),
      );

      $imoneza_container["imoneza_description"] = array(
        "#type" => "textarea",
        "#default_value" => t(check_plain(isset(
          $resource['Description']) ? $resource['Description'] : "")),
        "#title" => "Description",
        "#description" => t("A short description of the post. Defaults to the first 100 words."),
      );

      $imoneza_container["imoneza_pricing"] = array(
        "#markup" => "<strong>Pricing</strong>",
      );

      $pricing_options = array(
        "Inherit" => "Inherit",
        "Free" => "Free",
        "FixedPrice" => "Fixed Price",
        "VariablePrice" => "Variable Price",
        "TimeTiered" => "Time Tiered",
        "ViewTiered" => "View Tiered",
        "SubscriptionOnly" => "Subscription Only",
      );

      $pricing_groups = array();
      $pricing_groups_list = $is_managed ?
        $resource['Property']['PricingGroups'] :
        $property['PricingGroups'];

      $default_group = 0;
      foreach ($pricing_groups_list as $pricing_group) {
        $default_group = $pricing_group['IsDefault'];
        $pricing_groups[$pricing_group['PricingGroupID']]
          = $pricing_group['Name'];
      }

      $selected_group = $is_managed ?
        $resource['PricingGroup']['PricingGroupID'] : $default_group;

      $imoneza_container["imoneza_pricingGroup"] = array(
        "#type" => "select",
        "#options" => $pricing_groups,
        "#title" => t("Pricing Group"),
        "#default_value" => $selected_group,

      );

      if (!isset($resource['PricingModel'])) {
        $resource['PricingModel'] = "Inherit";
      }

      $imoneza_container["imoneza_pricingModel"] = array(
        "#type" => "select",
        "#options" => $pricing_options,
        "#default_value" => $resource['PricingModel'],
        "#title" => t("Pricing Model"),
        "#attributes" => array(
          "onchange" => "imoneza_update_display()",
          "id" => "edit-imoneza-pricingmodel",
        ),

      );

      $imoneza_container["imoneza_custom_pricing_container"] = array(
        "#type" => "container",
        "#attributes" => array(
          "class" => array(
            "imoneza_row_price",
          ),
        ),
      );

      $custom_pricing_container
        = &$imoneza_container["imoneza_custom_pricing_container"];

      $custom_pricing_container["imoneza_custom_pricing"] = array(
        "#markup" => "<strong>Custom Pricing</strong>",
      );

      $custom_pricing_container["imoneza_price"] = array(
        "#type" => "textfield",
        "#size" => 25,
        "#title" => t("Pricing"),
        "#default_value" => isset($resource["Price"]) ? $resource["Price"] : t("0.0"),
      );

      $expiration_options = array(
        "Never" => "Never",
        "Years" => "Years",
        "Months" => "Months",
        "Weeks" => "Weeks",
        "Days" => "Days",
      );

      if (!isset($resource['ExpirationPeriodUnit'])) {
        $resource['ExpirationPeriodUnit'] = "Never";
      }

      $custom_pricing_container["imoneza_expirationPeriodUnit"] = array(
        "#type" => "select",
        "#options" => $expiration_options,
        "#title" => t("Expiration Period"),
        "#attributes" => array(
          "onchange" => "imoneza_update_display()",
          "id" => "edit-imoneza-expirationperiodunit",
        ),
        "#default_value" => $resource['ExpirationPeriodUnit'] = "Never",
      );

      $custom_pricing_container["imoneza_expirationPeriodValue"] = array(
        "#type" => "textfield",
        "#title" => "Expiration Duration",
        "#size" => 25,
      );

      $imoneza_container["imoneza_tiered_pricing_container"] = array(
        "#type" => "container",
        "#attributes" => array(
          "class" => array(
            "imoneza_row_price_tier",
          ),
        ),
      );

      $tiered_pricing_container
        = &$imoneza_container["imoneza_tiered_pricing_container"];

      $tiered_pricing_container["imoneza_tiered_fieldset"] = array(
        "#type" => "fieldset",
        "#title" => "Pricing Tiers",
        "#description" => t("You must have at least one tier, and there must be one tier of 0 minutes or 0 views."),
        "#prefix" => '<div id="tiers-fieldset-wrapper">',
        "#suffix" => "</div>",
      );

      $fieldset = &$tiered_pricing_container["imoneza_tiered_fieldset"];

      if (isset($resource['ResourcePricingTiers'])
        && is_array($resource['ResourcePricingTiers'])
      ) {

        $form_state["imoneza_num_tiers"]
          = count($resource['ResourcePricingTiers']);
        for ($i = 0;
             $i < count($resource['ResourcePricingTiers']); $i++) {
          $fieldset[$i] = array(
            "#type" => "fieldset",

            "#prefix" => '<div class="tier-wrapper">',
            "#suffix" => "</div>",
            "#attributes" => array(
              "class" => array('container-inline'),
            ),
          );

          $wrapper = &$fieldset[$i];

          $scaling_factor = "minutes";
          $tier_val = $resource['ResourcePricingTiers'][$i]["Tier"];
          if ($resource['ResourcePricingTiers'][$i]["Tier"] > 60) {
            $scaling_factor = "hours";
            $tier_val
              = $resource['ResourcePricingTiers'][$i]["Tier"]
              / 60.0;
          }

          if ($resource['ResourcePricingTiers'][$i]["Tier"] > 1440) {
            $scaling_factor = "days";
            $tier_val
              = $resource['ResourcePricingTiers'][$i]["Tier"]
              / 1440.0;
          }
          $wrapper["tier"] = array(
            "#type" => "textfield",
            "#default_value" => $tier_val,
          );
          $options = array(
            "minutes" => "minutes",
            "hours" => "hours",
            "days" => "days",
          );
          $wrapper["scale_val"] = array(
            "#type" => "select",
            "#options" => $options,
            "#attributes" => array(
              "class" => array(
                "time_scale_selector",
              ),
            ),
            "#default_value" => $scaling_factor,
            "#prefix" => '<div class="tier-selector-wrapper">',
            "#suffix" => "</div>",
          );
          $wrapper["view_text"] = array(
            "#markup" => '<div class="view_text">views</div>'
          );
          $wrapper["price"] = array(
            "#type" => "textfield",
            "#default_value" => $resource['ResourcePricingTiers'][$i]["Price"],
            "#prefix" => "<br />",
          );

          $wrapper["tier"]["#title"] = t("Tier &nbsp;");
          $wrapper["price"]["#title"] = t("Price");

          $wrapper['remove'] = array(
            "#type" => "button",
            "#value" => t("Remove"),
            "#attributes" => array(
              "class" => array(
                "remove_button",
              ),
            ),

          );

        }
      }
      else {
        if (!isset($form_state["imoneza_num_tiers"])) {
          $form_state["imoneza_num_tiers"] = 1;
        }

        for ($i = 0; $i < $form_state["imoneza_num_tiers"]; $i++) {
          $fieldset[$i] = array();
          $fieldset[$i]["tier"] = array(
            "#type" => "textfield",
            "#title" => t("Value"),
          );
          $fieldset[$i]["price"] = array(
            "#type" => "textfield",
            "#title" => t("Price"),
          );
        }
      }

      $fieldset["imoneza_add_tier"] = array(
        "#type" => "button",
        "#value" => t("Add Tier"),
        "#attributes" => array(
          "class" => array(
            "add_tier_btn",
          ),
        ),

      );

      $form["actions"]["submit"]["#submit"][] = array($this, "saveMetaBoxData");

    }
    catch (Exception $e) {
      $form['imoneza']["imoneza_error"] = array(
        "#markup" => t("An error has occurred: @error", array("@errro" => check_plain($e->getMessage()))),
      );
    }

  }

  /**
   * Handler to save form data.
   *
   * @param mixed $form
   *    Form being saved.
   * @param mixed $form_state
   *    Form state for the form.
   *
   * @throws Exception
   *    Throws an exception on I/O issues.
   */
  public function saveMetaBoxData($form, $form_state) {
    // Check the user's permissions.
    if (!user_access("edit any " . $form["#node"]->type . " content")) {
      // No permission to be here.
      return;
    }

    $post_id = $form_state["nid"];

    $values = &$form_state["values"]["imoneza"];

    if ($values['imoneza_isManaged'] != '1') {
      if ($values['imoneza_isManaged_original'] == '1') {
        // User unchecked the box for iMoneza to manage the resource.
        $resource_management = new IMonezaResourceManagement();
        $data = array(
          'ExternalKey' => $post_id,
          'Active' => 0,
        );
        $resource_management->putResource($post_id, $data);

        $this->setUpdatedNotice('iMoneza settings for the resource were successfully updated.');
      }

      return;
    }

    $values = &$values["imoneza_meta_container"];

    // OK, it's safe for us to save the data now.
    $name = check_plain($values['imoneza_name']) == ""
      ? $form_state["values"]["title"]
      : check_plain($values['imoneza_name']);
    $title = check_plain($values['imoneza_title']) == ""
      ? $form_state["values"]["title"]
      : check_plain($values['imoneza_title']);

    $description = check_plain($values['imoneza_description']);
    if ($description == "") {
      $description = implode(" ", array_slice(
          explode(" ",
            $form_state["values"]["body"][LANGUAGE_NONE]["0"]["value"]), 0, 100)) . "...";
    }

    $data = array(
      'ExternalKey' => $post_id,
      'Active' => 1,
      'Name' => $name,
      'Title' => $title,
      'Byline' => check_plain($values['imoneza_byline']),
      'Description' => $description,
      'URL' => url("/node/" . $post_id, array("absolute" => TRUE)),
      'PublicationDate' => $form_state["values"]["created"] == "" ? date(DATE_ATOM) : date(DATE_ATOM, $form_state["values"]["created"]),
      'PricingGroup' => array('PricingGroupID' => check_plain($values['imoneza_pricingGroup'])),
      'PricingModel' => check_plain($values['imoneza_pricingModel']),
    );

    if ($values['imoneza_pricingModel'] == 'FixedPrice'
      || $values['imoneza_pricingModel'] == 'VariablePrice'
    ) {

      $data['Price'] = check_plain(
        $values["imoneza_custom_pricing_container"]['imoneza_price']);

      $data['ExpirationPeriodUnit'] = check_plain(
        $values["imoneza_custom_pricing_container"]['imoneza_expirationPeriodUnit']);

      if ($values["imoneza_custom_pricing_container"]['imoneza_expirationPeriodUnit'] != 'Never') {
        $data['ExpirationPeriodValue'] = check_plain(
          $values["imoneza_custom_pricing_container"]['imoneza_expirationPeriodValue']);
      }
    }

    if ($values['imoneza_pricingModel'] == 'ViewTiered'
      || $values['imoneza_pricingModel'] == 'TimeTiered'
    ) {
      $tiers = $_POST["imoneza"]["imoneza_meta_container"]["imoneza_tiered_pricing_container"]["imoneza_tiered_fieldset"];

      $do_multiply = $values['imoneza_pricingModel'] == 'TimeTiered';

      $vals = array();
      for ($i = 0; $i < count($tiers); ++$i) {
        $multiplier = 1;
        if ($do_multiply) {
          if ($tiers[$i]["scale_val"] == "hours") {
            $multiplier = 60;
          }
          else {
            if ($tiers[$i]["scale_val"] == "days") {
              $multiplier = 1440;
            }
          }
        }
        $vals[] = array(
          'Tier' => $tiers[$i]["tier"] * ($multiplier),
          'Price' => $tiers[$i]["price"],
        );
      }

      $data['ResourcePricingTiers'] = $vals;
    }

    $resource_management = new IMonezaResourceManagement();
    try {
      $resource_management->putResource($post_id, $data);
      $this->setUpdatedNotice('iMoneza settings for the resource "
                . "were successfully updated.');
    }
    catch (Exception $e) {
      $this->setErrorNotice($e->getMessage());
    }
  }

  /**
   * Options page callback.
   */
  public function createAdminPage() {
    $form = array();
    $options = $this->options;
    if (!isset($options["imoneza_node_types"])
      || $options["imoneza_node_types"] == "0"
    ) {

      $options["imoneza_node_types"] = array();
    }

    if (!isset($options['imoneza_access_control_excluded_user_agents'])) {
      $options['imoneza_access_control_excluded_user_agents'] = "";
    }

    if (count($options) == 0) {
      $options['imoneza_ra_api_key_access'] = "";
      $options['imoneza_ra_api_key_secret'] = "";
      $options['imoneza_rm_api_key_access'] = "";
      $options['imoneza_rm_api_key_secret'] = "";
      $options['imoneza_nodynamic'] = "0";
      $options['imoneza_access_control'] = 0;
      $options['imoneza_node_types'] = array();
    }

    $form['imoneza_ra_header'] = array(
      "#markup" => read_file_contents("static/resource_access_api_header.html"),
    );

    $form['imoneza_ra_api_key_access'] = array(
      '#type' => 'textfield',
      '#title' => t('Access Key'),
      '#default_value' => isset($options['imoneza_ra_api_key_access']) ? $options['imoneza_ra_api_key_access'] : "",
      '#size' => 36,
      '#maxlength' => 100,
      '#description' => t("Resource Access API Access Key"),
      '#required' => FALSE,
    );

    $form['imoneza_ra_api_key_secret'] = array(
      '#type' => 'textfield',
      '#title' => t('Secret Key'),
      '#default_value' => isset($options['imoneza_ra_api_key_secret'])
        ? $options['imoneza_ra_api_key_secret'] : "",
      '#size' => 65,
      '#maxlength' => 100,
      '#description' => t("Resource Access API Secret Key"),
      '#required' => FALSE,
    );

    $form['imoneza_rm_api_header'] = array(
      "#markup" => read_file_contents("static/resource_management_api_header.html"),
    );

    $form['imoneza_rm_api_key_access'] = array(
      '#type' => 'textfield',
      '#title' => t('Access Key'),
      '#default_value' => isset($options['imoneza_rm_api_key_access']) ? $options['imoneza_rm_api_key_access'] : "",
      '#size' => 36,
      '#maxlength' => 100,
      '#description' => t("Resource Management API Access Key"),
      '#required' => FALSE,
    );

    $form['imoneza_rm_api_key_secret'] = array(
      '#type' => 'textfield',
      '#title' => t('Secret Key'),
      '#default_value' => isset($options['imoneza_rm_api_key_secret']) ? $options['imoneza_rm_api_key_secret'] : "",
      '#size' => 65,
      '#maxlength' => 100,
      '#description' => t("Resource Management API Secret Key"),
      '#required' => FALSE,
    );

    $form['imoneza_dynamic_resources_header'] = array(
      "#markup" => read_file_contents("static/dynamic_resource_header.html"),
    );

    $form['imoneza_nodynamic'] = array(
      '#type' => 'checkbox',
      '#title' => t('Disable Dynamic Resource Creation'),
      '#default_value' => isset($options['imoneza_nodynamic']) ? $options['imoneza_nodynamic'] : FALSE,
      '#description' => t("Do not include dynamic resource creation block on every page"),
      '#required' => FALSE,
    );

    $radio_options = array(
      IMONEZA_NO_ACCESS_CONTROL => t("None"),
      IMONEZA_CLIENT_SIDE_ACCESS_CONTROL => t("Client-side (JavaScript)"),
      IMONEZA_SERVER_SIDE_ACCESS_CONTROL => t("Server-side"),
    );

    $form['imoneza_access_control_description'] = array(
      "#markup" => read_file_contents("static/access_control_description.html"),
    );

    $form['imoneza_access_control'] = array(
      '#type' => 'radios',
      '#title' => t('Access Control Method'),
      '#default_value' => isset($options['imoneza_access_control']) ? $options['imoneza_access_control'] : 0,
      '#options' => $radio_options,
      '#required' => FALSE,
    );

    $form['imoneza_access_control_excluded_user_agents'] = array(
      "#type" => "textarea",
      "#title" => "Excluded User Agents",
      "#description" => t("Comma-separated list of user agents to allow unlimited access to your resources"),
      "#default_value" => isset($options['imoneza_access_control_excluded_user_agents']) ? $options['imoneza_access_control_excluded_user_agents'] : ""
    );

    $node_types = node_type_get_types();

    $node_options = array();

    foreach ($node_types as $type) {
      $node_options[$type->type] = $type->name;
    }

    $form['imoneza_node_types'] = array(
      "#type" => "checkboxes",
      "#options" => $node_options,
      "#default_value" => $options['imoneza_node_types'],
      "#title" => "Node Types",
      "#description" => t("Use this to select which node types you want iMoneza to control"),
    );

    $form['imoneza_config_submit'] = array(
      "#type" => "submit",
      "#value" => "Save",
    );

    $form['imoneza_admin_help'] = array(
      "#markup" => read_file_contents("static/admin_help.html"),
    );

    $form["#submit"][] = array($this, "imonezaSaveConfig");

    return $form;

  }

  /**
   * Handler to save configuration.
   *
   * @param mixed $form
   *    The original form displayed to the user.
   * @param mixed $form_state
   *    Form state corresponding to the form.
   */
  public function imonezaSaveConfig($form, &$form_state) {

    $options = array();

    $sanitized_input = $this->sanitize($form_state['values']);

    $options['imoneza_ra_api_key_access']
      = $sanitized_input['imoneza_ra_api_key_access'];
    $options['imoneza_ra_api_key_secret']
      = $sanitized_input["imoneza_ra_api_key_secret"];
    $options['imoneza_rm_api_key_access']
      = $sanitized_input["imoneza_rm_api_key_access"];
    $options['imoneza_rm_api_key_secret']
      = $sanitized_input["imoneza_rm_api_key_secret"];
    $options['imoneza_nodynamic']
      = $sanitized_input["imoneza_nodynamic"];
    $options['imoneza_access_control']
      = $sanitized_input["imoneza_access_control"];

    $options['imoneza_node_types'] = $sanitized_input['imoneza_node_types'];
    $options['imoneza_access_control_excluded_user_agents']
      = $sanitized_input['imoneza_access_control_excluded_user_agents'];

    variable_set("imoneza_options", $options);

  }

  /**
   * Sanitize each setting field as needed.
   *
   * @param  mixed $input
   *    Array containing all settings fields as array keys.
   *
   * @return array
   *    Similar to $input with sanitized values.
   */
  public function sanitize($input) {

    $new_input = array();

    if (isset($input['imoneza_rm_api_key_access'])) {
      $new_input['imoneza_rm_api_key_access'] =
        check_plain($input['imoneza_rm_api_key_access']);
    }
    if (isset($input['imoneza_rm_api_key_secret'])) {
      $new_input['imoneza_rm_api_key_secret'] =
        check_plain($input['imoneza_rm_api_key_secret']);
    }

    if (isset($input['imoneza_ra_api_key_access'])) {
      $new_input['imoneza_ra_api_key_access'] =
        check_plain($input['imoneza_ra_api_key_access']);
    }
    if (isset($input['imoneza_ra_api_key_secret'])) {
      $new_input['imoneza_ra_api_key_secret'] =
        check_plain($input['imoneza_ra_api_key_secret']);
    }

    if (isset($input['imoneza_nodynamic'])
      && $input['imoneza_nodynamic'] == '1'
    ) {
      $new_input['imoneza_nodynamic'] = '1';
    }
    else {
      $new_input['imoneza_nodynamic'] = '0';
    }

    if (isset($input['imoneza_use_access_control'])
      && $input['imoneza_use_access_control'] == '1'
    ) {
      $new_input['imoneza_use_access_control'] = '1';
    }
    else {
      $new_input['imoneza_use_access_control'] = '0';
    }

    if (isset($input['imoneza_access_control'])) {
      $new_input['imoneza_access_control'] =
        check_plain($input['imoneza_access_control']);
    }

    if (isset($input['imoneza_node_types'])) {

      $types = array();
      foreach ($input['imoneza_node_types'] as $key => $val) {
        $types[check_plain($key)] = check_plain($val);
      }

      $new_input['imoneza_node_types'] = $types;
    }

    if (isset($input['imoneza_access_control_excluded_user_agents'])) {
      $new_input['imoneza_access_control_excluded_user_agents']
        = implode("\n",
        array_map('check_plain',
          str_replace("\r", "",
            explode("\n",
              $input['imoneza_access_control_excluded_user_agents']
            )
          )
        )
      );
    }

    return $new_input;
  }

  /**
   * Displays a notice to the user.
   *
   * @param string $notice
   *    Notice to be displayed.
   */
  public function setUpdatedNotice($notice) {

    drupal_set_message($notice, "status");
  }

  /**
   * Displays an error notice to the user.
   *
   * @param string $notice
   *    Error to be displayed.
   */
  public function setErrorNotice($notice) {

    drupal_set_message($notice, "error");
  }

}
