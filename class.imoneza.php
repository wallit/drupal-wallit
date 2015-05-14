<?php
    
class iMoneza {
	private $options;
    public $doClientSideAuth = false;
    public $doServerSideAuth = false;
    public $doDynamic = false;

    public function __construct()
    {
        $this->options = variable_get("imoneza_options", array());

        if (isset($options["imoneza_ra_api_key_access"])){
            // If there's an Access API access key, and we're using client-side access control, create the JavaScript snippet
            if (isset($this->options['ra_api_key_access']) && $this->options['ra_api_key_access'] != '' && (!isset($this->options['access_control']) || $this->options['access_control'] == 'JS')) {
                $this->doClientSideAuth = true;
            }

            // If 'no_dynamic' isn't set, then make sure we add the dynamic resource creation block to every page
            if (!isset($this->options['no_dynamic']) || $this->options['no_dynamic'] != '1') {
                $this->doDynamic = true;
            }

            // Perform server-side access control
            if (isset($this->options['ra_api_key_secret']) && $this->options['ra_api_key_secret'] != '' && isset($this->options['access_control']) && $this->options['access_control'] == 'SS') {
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
        if ($resourceValues['key'] == '')
            return;

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
        $values['url'] = '';
        
//        if (is_page() || is_single()) {
//            $this_post = get_post();
//
//            $values['key'] = $this_post->ID;
//            $values['name'] = $this_post->post_title;
//            $values['title'] = $this_post->post_title;
//            $values['description'] = $this_post->post_excerpt;
//            $values['publicationDate'] = $this_post->post_date;
//            $values['url'] = get_permalink($this_post->ID);
//        } else if (is_category()) {
//            $cat = get_query_var('cat');
//            $this_category = get_category($cat);
//
//            $values['key'] = 'Category-' . $this_category->cat_ID;
//            $values['name'] = $this_category->cat_name;
//            $values['title'] = $this_category->cat_name;
//            $values['url'] = get_category_link($this_category->cat_ID);
//        } else if (is_front_page()) {
//            $values['key'] = 'FrontPage';
//            $values['name'] = 'Front Page';
//            $values['title'] = 'Front Page';
//            $values['url'] = get_home_url();
//        } else if (is_tag()) {
//            $tag = get_query_var('tag');
//            $this_tag = get_term_by('name', $tag, 'post_tag');
//
//            $values['key'] = 'Tag-' . $this_tag->term_id;
//            $values['name'] = $this_tag->name;
//            $values['title'] = $this_tag->name;
//            $values['description'] = $this_tag->description;
//            $values['url'] = get_term_link($this_tag->term_id);
//        }

        // Ignore archive pages
        // Ignore feeds

        return $values;
    }

    // Adds the iMoneza JavaScript snippet to the HTML head of a page
    public function create_snippet($node)
    {
        $public_api_key = $this->options['ra_api_key_access'];
        $resourceValues = $this->get_resource_values($node);

        if ($resourceValues['key'] != '') {
            $output = '
                <script src="' . IMONEZA__RA_UI_URL . '/assets/imoneza.js"></script>
                <script type="text/javascript">
                    iMoneza.ResourceAccess.init({
                        ApiKey: "' . $public_api_key . '",
                        ResourceKey: "' . $resourceValues['key'] . '"
                    });
                </script>
            ';

            return $output;
        }else{
            return '';
        }
    }

    // Adds the iMoneza JavaScript reference to the HTML head of a page
    public function create_reference()
    {
        echo '<script src="' . IMONEZA__RA_UI_URL . '/assets/imoneza.js"></script>';
    }

    // Adds the dynamic resource creation block to the HTML head of a page
    public function create_dynamic($node)
    {
        $resourceValues = $this->get_resource_values($node);

        if ($resourceValues['key'] != '') {
            echo '
                <script type="application/imoneza">
                    <Resource>
                        <Name>' . $resourceValues['name'] . '</Name>
                        <Title>' . $resourceValues['title'] . '</Title>' .
                        ($resourceValues['description'] == '' ? '' :  '<Description>' . $resourceValues['description'] . '</Description>') . 
                        ($resourceValues['publicationDate'] == '' ? '' : '<PublicationDate>' . $resourceValues['publicationDate'] . '</PublicationDate>') .
                    '</Resource>
                </script>
            ';
        }
    }
}