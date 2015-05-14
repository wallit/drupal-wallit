<?php
    
class iMoneza_Admin {
	private $options;

    public function __construct()
    {
         $this->options = variable_get('imoneza_options');

         // If Management API public and private keys are set, then add the iMoneza metabox
         if (isset($this->options['rm_api_key_access']) && $this->options['rm_api_key_access'] != '' && isset($this->options['rm_api_key_secret']) && $this->options['rm_api_key_secret'] != '') {
             $this->ready = true;
         }
    }

    public function render_form_javascript(){
        return read_file_contents("post_form_js.html");
    }

    public function render_imoneza_meta_box(&$form, $form_state)
    {
        // Add an nonce field so we can check for it later.
        try {

            $form['imoneza'] = array(
                '#type' => 'fieldset',
                '#title' => t('iMoneza'),
                '#collapsible' => TRUE,
                '#collapsed' => TRUE,
                '#group' => 'additional_settings'
            );

            $post = $form_state["node"];



            //needed for multival
           // $form["#tree"] = TRUE;



            $resourceManagement = new iMoneza_ResourceManagement();
            if (isset($post) && isset($post->nid)){
                $resource = $resourceManagement->getResource($post->nid, true);
            }else{
                $resource = array("IsManaged" => 0);
            }

            if (IMONEZA__DEBUG) {

                $resourceDebug = '<p><a onclick="document.getElementById(\'imonezaDebugResource\').style.display = document.getElementById(\'imonezaDebugResource\').style.display == \'none\' ? \'block\' : \'none\';">Resource</a></p>';
                $resourceDebug .=  '<pre id="imonezaDebugResource" style="display:none;">';
                $resourceDebug .= print_r($resource, true);
                $resourceDebug .=  '</pre>';

                $form['imoneza']["imoneza_resource_debug"] = array(
                    '#markup' => $resourceDebug
                );

            }

            $isManaged = $resource['IsManaged'] == 1 && $resource['Active'] == 1;

            if (!$isManaged) {
                $property = $resourceManagement->getProperty();
            } else {
                $property = $resource['Property'];
            }

            if (IMONEZA__DEBUG) {

                $propertyDebug = '<p><a onclick="document.getElementById(\'imonezaDebugProperty\').style.display = document.getElementById(\'imonezaDebugProperty\').style.display == \'none\' ? \'block\' : \'none\';">Property</a></p>';
                $propertyDebug .=  '<pre id="imonezaDebugProperty" style="display:none;">';
                $propertyDebug .= print_r($resource, true);
                $propertyDebug .=  '</pre>';

                $form['imoneza']["imoneza_resource_debug"] = array(
                    '#markup' => $propertyDebug
                );
            }


            // If there are no pricing tiers, set a default zero tier in case we need it
            if (!isset($resource['ResourcePricingTiers']) || count($resource['ResourcePricingTiers']) == 0) {
                $resource['ResourcePricingTiers'] = array(
                    array('Tier' => 0, 'Price' => '0.00')
                );
            }

            $form['imoneza']['imoneza_js_area'] = array(
                "#markup" => $this->render_form_javascript()
            );

            $styleAttr = '';
            $priceStyleAttr = '';

            if (!$isManaged)
                $styleAttr .= ' style="display:none;"';

            if (!$isManaged || ($resource['PricingModel'] != 'FixedPrice' && $resource['PricingModel'] != 'VariablePrice'))
                $priceStyleAttr = ($styleAttr == '' ? ' style="display:none;"' : $styleAttr);

            if (!$isManaged || ($resource['PricingModel'] != 'FixedPrice' && $resource['PricingModel'] != 'VariablePrice') || $resource['ExpirationPeriodUnit'] == 'Never')
                $expirationStyleAttr = ($priceStyleAttr == '' ? ' style="display:none;"' : $priceStyleAttr);

            if (!$isManaged || ($resource['PricingModel'] != 'TimeTiered' && $resource['PricingModel'] != 'ViewTiered'))
                $priceTierStyleAttr = ($priceStyleAttr == '' ? ' style="display:none;"' : $priceStyleAttr);

            $form['imoneza']["imoneza_managed"] = array(
                "#type" => "checkbox",
                '#title' => t('Use iMoneza to manage access to this resource'),
                '#default_value' => $isManaged,
                '#required' => FALSE,
                "#attributes" => array(
                    "onclick" => "imoneza_update_display()",
                    "id" => "imoneza_isManaged",
                )
            );

            $form['imoneza']["imoneza_managed_hidden"] = array(
                "#type" => "hidden",
                "#id" => "imoneza_isManaged_original",
                "#default_value" => $isManaged,

            );

            $form["imoneza"]["imoneza_meta_container"] = array(
                "#type" => "container",
                "#attributes" => array(
                    "class" => array(
                        "imoneza_row"
                    )
                )
            );

            $imonezaContainer = &$form["imoneza"]["imoneza_meta_container"];

            $imonezaContainer["imoneza_metadata"] = array(
                "#markup" => "<strong>Metadata</strong>",
                "#attributes" => array(
                    "class" => array("imoneza_row")
                )
            );

            $imonezaContainer["imoneza_name"] = array(
                "#type" => "textfield",
                "#size" => "25",
                "#default_value" => t(check_plain(isset($resource['Name']) ? $resource['Name'] : "")),
                "#title" => t("Name"),
                "#description" => t("A friendly name for the resource to help you identify it. This name is never displayed publicly to consumers. Defaults to the article title."),

            );

            $imonezaContainer["imoneza_title"] = array(
                "#type" => "textfield",
                "#size" => 25,
                "#default_value" => t(check_plain(isset($resource['Title']) ? $resource['Title'] : "")),
                "#title" => t("Title"),
                "#description" => t("The title of the resource which gets displayed to consumers. Defaults to the article title."),

            );

            $imonezaContainer["imoneza_byline"] = array(
                "#type" => "textfield",
                "#size" => 25,
                "#default_value" => t(check_plain(isset($resource['Byline']) ? $resource['Byline'] : "")),
                "#title" => t("Byline"),
                "#description" => t("For instance, the author of the post."),

            );

            $imonezaContainer["imoneza_description"] = array(
                "#type" => "textarea",
                "#default_value" => t(check_plain(isset($resource['Description']) ? $resource['Description'] : "")),
                "#title" => "Description",
                "#description" => t("A short description of the post. Defaults to the post's excerpt."),

            );

            $imonezaContainer["imoneza_pricing"] = array(
                "#markup" => "<strong>Pricing</strong>",

            );

            $pricingOptions = array(
                "Inherit" => "Inherit",
                "Free" => "Free",
                "FixedPrice" => "Fixed Price",
                "VariablePrice" => "Variable Price",
                "TimeTiered" => "Time Tiered",
                "ViewTiered" => "View Tiered",
                "SubscriptionOnly" => "Subscription Only");



            $pricingGroups = array();
            $pricingGroupsList = $isManaged ? $resource['Property']['PricingGroups'] : $property['PricingGroups'];
            $defaultGroup = 0;
            foreach ($pricingGroupsList as $pricingGroup) {
                $defaultGroup = $pricingGroup['IsDefault'];
                $pricingGroups[$pricingGroup['PricingGroupID']] = $pricingGroup['Name'];
            }

            $selectedGroup = $isManaged ? $resource['PricingGroup']['PricingGroupID'] :$defaultGroup;

            $imonezaContainer["imoneza_pricingGroup"] = array(
                "#type" => "select",
                "#options" => $pricingGroups,
                "#title" => t("Pricing Group"),
                "#default_value" => $selectedGroup,

            );

            if (!isset($resource['PricingModel'])){
              $resource['PricingModel'] = "Inherit";
            }

            $imonezaContainer["imoneza_pricingModel"] = array(
                "#type" => "select",
                "#options" => $pricingOptions,
                "#default_value" => $resource['PricingModel'],
                "#title" => t("Pricing Model"),
                "#attributes" => array(
                    "onchange" => "imoneza_update_display()",
                ),

            );

            $imonezaContainer["imoneza_custom_pricing_container"] = array(
                "#type" => "container",
                "#attributes" => array(
                    "class" => array(
                        "imoneza_row_price"
                    )
                )
            );
            $customPricingContainer = &$imonezaContainer["imoneza_custom_pricing_container"];
            $customPricingContainer["imoneza_custom_pricing"] = array(
                "#markup" => "<strong>Custom Pricing</strong>",

            );

            $customPricingContainer["imoneza_price"] = array(
                "#type" => "textfield",
                "#size" => 25,
                "#title" => t("Pricing"),


            );

            $expirationOptions = array(
                "Never" => "Never",
                "Years" => "Years",
                "Months" => "Months",
                "Weeks" => "Weeks",
                "Days" => "Days"
            );

            if (!isset($resource['ExpirationPeriodUnit'])){
                $resource['ExpirationPeriodUnit'] = "Never";
            }

            $customPricingContainer["imoneza_expirationPeriodUnit"] = array(
                "#type" => "select",
                "#options" => $expirationOptions,
                "#title" => t("Expiration Period"),
                "#attributes" => array(
                    "onchange" => "imoneza_update_display()",

                ),
                "#default_value" => $resource['ExpirationPeriodUnit'] = "Never"
            );

            $customPricingContainer["imoneza_expirationPeriodValue"] = array(
                "#type" => "textfield",
                "#title" => "Expiration Duration",
                "#size" => 25,

            );

            $imonezaContainer["imoneza_tiered_pricing_container"] = array(
                "#type" => "container",
                "#attributes" => array(
                    "class" => array(
                        "imoneza_row_price_tier"
                    )
                )
            );

            $tieredPricingContainer = &$imonezaContainer["imoneza_tiered_pricing_container"];

            $tieredPricingContainer["imoneza_tierd_header"] = array(
                "#markup" => "<strong>Pricing Tiers</strong><br /><small>You must have at least one tier, and there must be one tier of 0 minutes or 0 views.</small>",

            );


            //idk what to do with this
            //$output .=  '<tr class="' . $rowClass . ' ' . $priceTierClass . '"' . $priceTierStyleAttr . '><td colspan="3"><table id="imoneza_tiers"><tbody><tr><th>Tier</th><th>Price</th></tr>';
//            if (isset($resource['ResourcePricingTiers'])){
//                foreach ($resource['ResourcePricingTiers'] as $tier) {
//                    $label = ' views';
//                    $value = $tier['Tier'];
//                    if ($resource['PricingModel'] == 'TimeTiered') {
//                        $label = 'minutes';
//                        if ($value > 0 && $value % 1440 == 0) {
//                            $label = 'days';
//                            $value = $value / 1440;
//                        } else if ($value > 0 && $value % 60 == 0) {
//                            $label = 'hours';
//                            $value = $value / 60;
//                        }
//
//                        $label = '<select name="imoneza_tier_price_multiplier[]"><option value="1"' . ($label == 'minutes' ? ' selected' : '') . '>minutes</option><option value="60"' . ($label == 'hours' ? ' selected' : '') . '>hours</option><option value="1440"' . ($label == 'days' ? ' selected' : '') . '>days</option></select>';
//                    }
//                    $output .=  '<tr><td><input type="text" value="' . $value . '" name="imoneza_tier[]" size="5" />' . $label . '</td><td><input type="text" value="' . number_format($tier['Price'], 2) . '" name="imoneza_tier_price[]" /></td><td>' . ($tier['Tier'] > 0 ? '<a href="#" onclick="return imoneza_remove_tier(this);">Remove Tier</a>' : '') . '</td></tr>';
//                }
//            }
//
//            $form['imoneza']["imoneza_addTier"] = array(
//                "#markup" => "<a href=\"#\" onclick=\"return imoneza_add_tier();\">Add Tier</a>"
//            );


            $form["#submit"][] = array($this, "save_meta_box_data");

        } catch (Exception $e) {
            $form['imoneza']["imoneza_error"] = array(
                "#markup" => t("An error has occurred: " . check_plain($e->getMessage()))
            );
        }
    }

    function save_meta_box_data($form, $form_state) {

        var_dump($form_state);

	    // Check the user's permissions.
	    if (user_access("edit any [content-type] content")){
            //good to go
        }

        if ($_POST['imoneza_isManaged'] != '1') {
            if ($_POST['imoneza_isManaged_original'] == '1') {
                // user unchecked the box for iMoneza to manage the resource
                $resourceManagement = new iMoneza_ResourceManagement();
                $data = array(
                    'ExternalKey' => $post_id, 
                    'Active' => 0
                );
                $resource = $resourceManagement->putResource($post_id, $data);

                $this->setUpdatedNotice('iMoneza settings for the resource were successfully updated.');
            }
            return;
        }

	    /* OK, it's safe for us to save the data now. */

        $data = array(
            'ExternalKey' => $post_id,
            'Active' => 1,
            'Name' => sanitize_text_field($_POST['imoneza_name']),
            'Title' => sanitize_text_field($_POST['imoneza_title']),
            'Byline' => sanitize_text_field($_POST['imoneza_byline']),
            'Description' => sanitize_text_field($_POST['imoneza_description']),
            'URL' => get_permalink($post_id),
            'PublicationDate' => get_the_time('c', $post_id),
            'PricingGroup' => array('PricingGroupID' => sanitize_text_field($_POST['imoneza_pricingGroup'])),
            'PricingModel' => sanitize_text_field($_POST['imoneza_pricingModel'])
        );

        if ($_POST['imoneza_pricingModel'] == 'FixedPrice' || $_POST['imoneza_pricingModel'] == 'VariablePrice') {
            $data['Price'] = sanitize_text_field($_POST['imoneza_price']);
            $data['ExpirationPeriodUnit'] = sanitize_text_field($_POST['imoneza_expirationPeriodUnit']);
            if ($_POST['imoneza_expirationPeriodUnit'] != 'Never')
                $data['ExpirationPeriodValue'] = sanitize_text_field($_POST['imoneza_expirationPeriodValue']);
        }

        if ($_POST['imoneza_pricingModel'] == 'ViewTiered' || $_POST['imoneza_pricingModel'] == 'TimeTiered') {
            $tiers = $_POST['imoneza_tier'];
            $prices = $_POST['imoneza_tier_price'];
            $multiplier = $_POST['imoneza_tier_price_multiplier'];
            $vals = array();
            for ($i = 0; $i < count($tiers); ++$i)
                $vals[] = array('Tier' => $tiers[$i] * (isset($multiplier) ? $multiplier[$i] : 1), 'Price' => $prices[$i]);

            $data['ResourcePricingTiers'] = $vals;
        }

        $resourceManagement = new iMoneza_ResourceManagement();
        try {
            $resource = $resourceManagement->putResource($post_id, $data);
            $this->setUpdatedNotice('iMoneza settings for the resource were successfully updated.');
        } catch (Exception $e) {
            $this->setErrorNotice($e->getMessage());
        }
    }

    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page('iMoneza', 'iMoneza', 'manage_options', 'imoneza-settings-admin', array( $this, 'create_admin_page' ));
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {


        $form = array();
        $options = variable_get("imoneza_options", array());
        if ($options["imoneza_node_types"] == "0"){
            $options["imoneza_node_types"] = array();
        }

        if (!isset($options['imoneza_access_control_excluded_user_agents'])){
            $options['imoneza_access_control_excluded_user_agents'] = "";
        }

        if (count($options) == 0){
            $options['imoneza_ra_api_key_access'] =  "";
            $options['imoneza_ra_api_key_secret'] = "";
            $options['imoneza_rm_api_key_access'] = "";
            $options['imoneza_rm_api_key_secret'] = "";
            $options['imoneza_nodynamic'] = "0";
            $options['imoneza_access_control'] = 0;
            $options['imoneza_node_types'] = array();
        }

        $form['imoneza_ra_header'] = array(
            "#markup" => read_file_contents("static/resource_access_api_header.html")
        );

        $form['imoneza_ra_api_key_access'] = array(
            '#type' => 'textfield',
            '#title' => t('Access Key'),
            '#default_value' => $options['imoneza_ra_api_key_access'] ?: "",
            '#size' => 36,
            '#maxlength' => 100,
            '#description' => t("Resource Access API Access Key"),
            '#required' => FALSE,
          );

        $form['imoneza_ra_api_key_secret'] = array(
            '#type' => 'textfield',
            '#title' => t('Secret Key'),
            '#default_value' => $options['imoneza_ra_api_key_secret'] ?: "",
            '#size' => 65,
            '#maxlength' => 100,
            '#description' => t("Resource Access API Secret Key"),
            '#required' => FALSE,
          );

        $form['imoneza_rm_api_header'] = array(
            "#markup" => read_file_contents("static/resource_management_api_header.html")
        );

        $form['imoneza_rm_api_key_access'] = array(
            '#type' => 'textfield',
            '#title' => t('Access Key'),
            '#default_value' => $options['imoneza_rm_api_key_access'] ?: "",
            '#size' => 36,
            '#maxlength' => 100,
            '#description' => t("Resource Management API Access Key"),
            '#required' => FALSE,
          );

        $form['imoneza_rm_api_key_secret'] = array(
            '#type' => 'textfield',
            '#title' => t('Secret Key'),
            '#default_value' => $options['imoneza_rm_api_key_secret'] ?: "",
            '#size' => 65,
            '#maxlength' => 100,
            '#description' => t("Resource Management API Secret Key"),
            '#required' => FALSE,
          );

        $form['imoneza_dynamic_resources_header'] = array(
            "#markup" => read_file_contents("static/dynamic_resource_header.html")
        );

        $form['imoneza_nodynamic'] = array(
            '#type' => 'checkbox',
            '#title' => t('Disable Dynamic Resource Creation'),
            '#default_value' => $options['imoneza_nodynamic'] ?: FALSE,
            '#description' => t("Do not include dynamic resource creation block on every page"),
            '#required' => FALSE,
          );

        $radioOptions = array(NO_ACCESS_CONTROL => t("None"),
            CLIENT_SIDE_ACCESS_CONTROL => t("Client-side (JavaScript)"), SERVER_SIDE_ACCESS_CONTROL => t("Server-side"));

        $form['imoneza_access_control_description'] = array(
            "#markup" => read_file_contents("static/access_control_description.html")
        );


        $form['imoneza_access_control'] = array(
            '#type' => 'radios',
            '#title' => t('Access Control Method'),
            '#default_value' => $options['imoneza_access_control'] ?: 0,
            '#options' => $radioOptions,
            '#required' => FALSE,
          );

        $form['imoneza_access_control_excluded_user_agents'] = array(
            "#type" => "textarea",
            "#title" => "Excluded User Agents",
            "#description" => "Comma-separated list of user agents to allow unlimited access to your resources",
            "#default_value" => $options['imoneza_access_control_excluded_user_agents'] ?: ""
        );

        $nodeTypes = node_type_get_types();

        $nodeOptions = array();

        foreach($nodeTypes as $type){
            $nodeOptions[$type->type] = $type->name;
        }

        $form['imoneza_node_types'] = array(
            "#type" => "checkboxes",
            "#options" => $nodeOptions,
            "#default_value" => $options['imoneza_node_types'],
            "#title" => "Node Types",
            "#description" => "Use this to select which node types you want iMoneza to control"
        );

        $form['imoneza_config_submit'] = array(
            "#type" => "submit",
            "#value" => "Save"
        );

        $form['imoneza_admin_help'] = array(
            "#markup" => read_file_contents("static/admin_help.html")
        );

        $form["#submit"][] = array($this, "imoneza_save_config");


        return $form;

    }

    public function imoneza_save_config($form, &$form_state){

        $options = array();


        $sanitizedInput = $this->sanitize($form_state['values']);

        $options['imoneza_ra_api_key_access'] =  $sanitizedInput['imoneza_ra_api_key_access'];
        $options['imoneza_ra_api_key_secret'] = $sanitizedInput["imoneza_ra_api_key_secret"];
        $options['imoneza_rm_api_key_access'] = $sanitizedInput["imoneza_rm_api_key_access"];
        $options['imoneza_rm_api_key_secret'] = $sanitizedInput["imoneza_rm_api_key_secret"];
        $options['imoneza_nodynamic'] = $sanitizedInput["imoneza_nodynamic"];
        $options['imoneza_access_control'] = $sanitizedInput["imoneza_access_control"];
        $options['imoneza_node_types'] = $sanitizedInput['imoneza_node_types'];
        $options['imoneza_access_control_excluded_user_agents'] = $sanitizedInput['imoneza_access_control_excluded_user_agents'];

        variable_set("imoneza_options", $options);

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     * @return array similar to $input with sanitized values
     */
    public function sanitize( $input )
    {

        $new_input = array();

        if (isset($input['imoneza_rm_api_key_access']))
            $new_input['imoneza_rm_api_key_access'] = check_plain($input['imoneza_rm_api_key_access']);
        if (isset($input['imoneza_rm_api_key_secret']))
            $new_input['imoneza_rm_api_key_secret'] = check_plain($input['imoneza_rm_api_key_secret']);

        if (isset($input['imoneza_ra_api_key_access']))
            $new_input['imoneza_ra_api_key_access'] = check_plain($input['imoneza_ra_api_key_access']);
        if (isset($input['imoneza_ra_api_key_secret']))
            $new_input['imoneza_ra_api_key_secret'] = check_plain($input['imoneza_ra_api_key_secret']);

        if (isset($input['imoneza_nodynamic']) && $input['imoneza_nodynamic'] == '1')
            $new_input['imoneza_nodynamic'] = '1';
        else
            $new_input['imoneza_nodynamic'] = '0';

        if (isset($input['imoneza_use_access_control']) && $input['imoneza_use_access_control'] == '1')
            $new_input['imoneza_use_access_control'] = '1';
        else
            $new_input['imoneza_use_access_control'] = '0';

        if (isset($input['imoneza_access_control']))
            $new_input['imoneza_access_control'] = check_plain($input['imoneza_access_control']);

        if (isset($input['imoneza_node_types'])){

            $types = array();
            foreach($input['imoneza_node_types'] as $key => $val){
                $types[check_plain($key)] = check_plain($val);
            }

            $new_input['imoneza_node_types'] = $types;
        }

        if (isset($input['imoneza_access_control_excluded_user_agents']))
            $new_input['imoneza_access_control_excluded_user_agents'] = implode("\n", array_map('check_plain', str_replace("\r", "", explode("\n", $input['imoneza_access_control_excluded_user_agents']))));

        return $new_input;
    }

    public function setUpdatedNotice($notice) {

        $_SESSION['iMoneza_UpdatedNotice'] = $notice;
    }

    public function setErrorNotice($notice) {

        $_SESSION['iMoneza_ErrorNotice'] = $notice;
    }

    public function admin_notices() {
        if (isset($_SESSION['iMoneza_UpdatedNotice']) && $_SESSION['iMoneza_UpdatedNotice'] != '') {
            ?>
            <div class="updated">
                <p><?= $_SESSION['iMoneza_UpdatedNotice'] ?></p>
            </div>
            <?php
            unset($_SESSION['iMoneza_UpdatedNotice']);
        }
        if (isset($_SESSION['iMoneza_ErrorNotice']) && $_SESSION['iMoneza_ErrorNotice'] != '') {
            ?>
            <div class="error">
                <p><?= $_SESSION['iMoneza_ErrorNotice'] ?></p>
            </div>
            <?php
            unset($_SESSION['iMoneza_ErrorNotice']);
        }
    }

    /** 
     * Print the Section text
     */
    public function print_section_info_ra_api()
    {
        print 'You must provide a Resource Access API access key and secret key.';
    }

    public function print_section_info_rm_api()
    {
        print 'You must provide a Resource Management API access key and secret key. Note that these two keys should be different from the Resource Access API access key and secret key.';
    }

    public function print_section_info_dynamic_resource_creation()
    {
        print 'Dynamic resource creation allows pages on your site to be added to iMoneza automatically when users first hit them. To use this feature, make sure the checkbox below is unchecked and that you\'ve checked "Dynamically Create Resources" on the <a href="https://manageui.imoneza.com/Property/Edit#tab_advanced">Property Settings</a> page.';
    }

    public function print_section_info_access_control()
    {
        print 'The access control method specifies how your site will communicate with iMoneza, determine if a user has access to the resource they\'re trying to access, and display the paywall if needed.' .
               '<ul>' .
               '<li><strong>None:</strong> No access control is enforced. Visitors to your site will not interact with iMoneza and will never see a paywall. Essentially, iMoneza won\'t be used on your site.</li>' .
               '<li><strong>Client-side:</strong> The JavaScript Library is run in your users\'s web browsers. You can set whether the paywall appears in a modal window or not on the <a href="https://manageui.imoneza.com/Property/Edit#tab_paywall">Property Settings</a> page.</li>' .
               '<li><strong>Server-side:</strong> User access is verified on your web server. This is more secure than the client-side approach. If you choose this option, you can also exclude certain user agents (like search engine bots) from being redirected to the paywall.</li>' .
               '</ul>' .
               '<p>If you specify user agents to exclude, you can specify one user agent per line.</p>';
    }

    public function print_section_info_help()
    {
        print 'The <a href="https://www.imoneza.com/wordpress-plugin/" target="_blank">iMoneza website</a> has additional information about these settings.';
        print '<script>' .
               'function imoneza_update_controls() {' .
               '  document.getElementById("access_control_excluded_user_agents").disabled = !document.getElementById("access_control_ss").checked; ' .
               '} ' .
               'imoneza_update_controls(); ' .
               '</script>';

    }

    public function ra_api_key_access_callback()
    {
        printf(
            '<input type="text" id="ra_api_key_access" name="imoneza_options[ra_api_key_access]" value="%s" />',
            isset($this->options['ra_api_key_access']) ? esc_attr( $this->options['ra_api_key_access']) : ''
        );
    }

    public function ra_api_key_secret_callback()
    {
        printf(
            '<input type="text" id="ra_api_key_secret" name="imoneza_options[ra_api_key_secret]" value="%s" />',
            isset($this->options['ra_api_key_secret']) ? esc_attr( $this->options['ra_api_key_secret']) : ''
        );
    }

    public function rm_api_key_access_callback()
    {
        printf(
            '<input type="text" id="rm_api_key_access" name="imoneza_options[rm_api_key_access]" value="%s" />',
            isset($this->options['rm_api_key_access']) ? esc_attr( $this->options['rm_api_key_access']) : ''
        );
    }

    public function rm_api_key_secret_callback()
    {
        printf(
            '<input type="text" id="rm_api_key_secret" name="imoneza_options[rm_api_key_secret]" value="%s" />',
            isset($this->options['rm_api_key_secret']) ? esc_attr( $this->options['rm_api_key_secret']) : ''
        );
    }

    public function no_dynamic_callback()
    {
        printf(
            '<input type="checkbox" id="no_dynamic" name="imoneza_options[no_dynamic]" value="1" %s/>',
            isset($this->options['no_dynamic']) && $this->options['no_dynamic'] == '1' ? ' checked' : ''
        );
    }

    public function access_control_callback()
    {
        printf(
            '<input type="radio" id="access_control_none" name="imoneza_options[access_control]" value="None" onclick="imoneza_update_controls();" %s/><label for="access_control_none">None</label><br />',
            isset($this->options['access_control']) && $this->options['access_control'] == 'None' ? ' checked' : ''
        );
        printf(
            '<input type="radio" id="access_control_js" name="imoneza_options[access_control]" value="JS" onclick="imoneza_update_controls();" %s/><label for="access_control_js">Client-side (JavaScript Library)</label><br />',
            isset($this->options['access_control']) && $this->options['access_control'] == 'JS' ? ' checked' : ''
        );
        printf(
            '<input type="radio" id="access_control_ss" name="imoneza_options[access_control]" value="SS" onclick="imoneza_update_controls();" %s/><label for="access_control_ss">Server-side</label><br />',
            isset($this->options['access_control']) && $this->options['access_control'] == 'SS' ? ' checked' : ''
        );
    }

    public function access_control_excluded_user_agents_callback()
    {
        printf(
            '<textarea id="access_control_excluded_user_agents" name="imoneza_options[access_control_excluded_user_agents]" rows="5" cols="80">%s</textarea>',
            isset($this->options['access_control_excluded_user_agents']) ? esc_attr($this->options['access_control_excluded_user_agents']) : ''
        );
    }
}
