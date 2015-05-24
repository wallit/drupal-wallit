<?php
    
class iMoneza {
	private $options;
    public $doClientSideAuth = false;
    public $doServerSideAuth = false;
    public $doDynamic = false;

    public function __construct()
    {
        $this->options = variable_get("imoneza_options", array());

        if (isset($this->options["imoneza_ra_api_key_access"])){
            // If there's an Access API access key, and we're using client-side access control, create the JavaScript snippet
            if (isset($this->options['imoneza_ra_api_key_access']) && $this->options['imoneza_ra_api_key_access'] != '' && (!isset($this->options['imoneza_access_control']) || $this->options['imoneza_access_control'] == 1)) {
                $this->doClientSideAuth = true;
            }

            // If 'no_dynamic' isn't set, then make sure we add the dynamic resource creation block to every page
            if (!isset($this->options['imoneza_nodynamic']) || $this->options['imoneza_nodynamic'] != '1') {
                $this->doDynamic = true;
            }

            // Perform server-side access control
            if (isset($this->options['imoneza_ra_api_key_secret']) && $this->options['imoneza_ra_api_key_secret'] != '' && isset($this->options['imoneza_access_control']) && $this->options['imoneza_access_control'] == 2) {
                $this->doServerSideAuth = true;
            }
        }
    }

    public function is_imoneza_managed_node($node){

        if (is_array($this->options['imoneza_node_types'])){
            return in_array($node->type, $this->options['imoneza_node_types']);
        }else{
            return $node->type == $this->options['imoneza_node_types'];
        }

    }

    public function imoneza_template_redirect($node)
    {
        $resourceValues = $this->get_resource_values($node);
        if ($resourceValues['key'] == ''){
            echo "key was empty";
            return;

        }


        $resourceAccess = new iMoneza_ResourceAccess();
        $response = $resourceAccess->getResourceAccess($resourceValues['key'], $resourceValues['url']);
    }

    private function get_resource_values($node)
    {

        $values = array();
        $values['key'] = $node->nid;
        $values['name'] = $node->title;
        $values['title'] = $node->title;
        $values['description'] = '';
        $values['publicationDate'] = '';
        $values['url'] = url("/node/".$node->nid, array("absolute"=>true));
        
        return $values;
    }

    // Adds the iMoneza JavaScript snippet to the HTML head of a page
    public function create_snippet($node)
    {
        $public_api_key = $this->options['imoneza_ra_api_key_access'];
        $resourceValues = $this->get_resource_values($node);

        if ($resourceValues['key'] != '') {
            $output = '
                iMoneza.ResourceAccess.init({
                    ApiKey: "' . $public_api_key . '",
                    ResourceKey: "' . $resourceValues['key'] . '"
                });
            ';

            return $output;
        }else{
            return '';
        }
    }


    // Adds the dynamic resource creation block to the HTML head of a page
    public function create_dynamic($node)
    {
        $resourceValues = $this->get_resource_values($node);


        if ($resourceValues['key'] != '') {

            $output = '<script type="application/imoneza"><Resource><Name>' . $resourceValues['name'] . '</Name><Title>' . $resourceValues['title'] . '</Title>' .($resourceValues['description'] == '' ? '' :  '<Description>' . $resourceValues['description'] . '</Description>') .($resourceValues['publicationDate'] == '' ? '' : '<PublicationDate>' . $resourceValues['publicationDate'] . '</PublicationDate>') .'</Resource> </script>';

            $imoneza_head = array(
                "#tag" => "script",
                "#type" => "markup",
                "#markup" => $output
            );

            drupal_add_html_head($imoneza_head, "imoneza-dynamic-header");
        }
    }
}