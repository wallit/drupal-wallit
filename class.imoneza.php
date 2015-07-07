<?php
/**
 * @file
 * File containing the iMoneza class object used for resource access.
 */

/**
 * Class IMoneza.
 *
 * Contains general client-facing functionality for iMoneza.
 */
class IMoneza {

  private $options;
  public $doClientSideAuth = FALSE;
  public $doServerSideAuth = FALSE;
  public $doDynamic = FALSE;

  /**
   * Constructor.
   */
  public function __construct() {

    $this->options = variable_get("imoneza_options", array());
    if (isset($this->options["imoneza_ra_api_key_access"])) {
      // If there's an Access API access key, and we're using client-side
      // access control, create the JavaScript snippet.
      if (
        isset($this->options['imoneza_ra_api_key_access'])
        && $this->options['imoneza_ra_api_key_access'] != ''
        &&
        (!isset($this->options['imoneza_access_control'])
          || $this->options['imoneza_access_control'] == 1)
      ) {
        $this->doClientSideAuth = TRUE;
      }

      // If 'no_dynamic' isn't set, then make sure we add the dynamic
      // resource creation block to every page.
      if (!isset($this->options['imoneza_nodynamic'])
        || $this->options['imoneza_nodynamic'] != '1'
      ) {
        $this->doDynamic = TRUE;
      }

      // Perform server-side access control.
      if (isset($this->options['imoneza_ra_api_key_secret'])
        && $this->options['imoneza_ra_api_key_secret'] != ''
        && isset($this->options['imoneza_access_control'])
        && $this->options['imoneza_access_control'] == 2
      ) {
        $this->doServerSideAuth = TRUE;
      }
    }
  }

  /**
   * Returns boolean indicating whether node type is managed.
   *
   * @param object $node
   *    The node in question.
   *
   * @return bool
   *    Indicating whether this node is managed by iMoneza.
   */
  public function isImonezaManagedNode($node) {

    if (is_array($this->options['imoneza_node_types'])) {
      return in_array($node->type, $this->options['imoneza_node_types']);
    }
    else {
      return $node->type == $this->options['imoneza_node_types'];
    }

  }

  /**
   * Peforms a redirect based on whether the user has access.
   *
   * @param object $node
   *    The node being loaded.
   *
   * @throws Exception
   *    Exception thrown for I/O errors.
   */
  public function imonezaTemplateRedirect($node) {
    $resource_values = $this->getResourceValues($node);
    if ($resource_values['key'] == '') {
      return;

    }

    $resource_access = new iMonezaResourceAccess();
    $resource_access->getResourceAccess($resource_values['key'],
      $resource_values['url']);
  }

  /**
   * Returns values stored in iMoneza service.
   *
   * @param object $node
   *    The node to return resource values for.
   *
   * @return array
   *    Array of resource values.
   */
  private function getResourceValues($node) {

    $values = array();
    $values['key'] = $node->nid;
    $values['name'] = $node->title;
    $values['title'] = $node->title;
    $values['description'] = '';
    $values['publicationDate'] = '';
    $values['url'] = url("/node/" . $node->nid, array("absolute" => TRUE));

    return $values;
  }

  /**
   * Adds the iMoneza JavaScript snippet to the HTML head of a page.
   *
   * @param object $node
   *    Node to add JavaScript snippet to.
   *
   * @return string
   *    The JavaScript snippet.
   */
  public function createSnippet($node) {
    $public_api_key = $this->options['imoneza_ra_api_key_access'];
    $resource_values = $this->getResourceValues($node);

    if ($resource_values['key'] != '') {
      $output = '
                iMoneza.ResourceAccess.init({
                    ApiKey: "' . $public_api_key . '",
                    ResourceKey: "' . $resource_values['key'] . '"
                });
            ';

      return $output;
    }
    else {
      return '';
    }
  }

  /**
   * Adds the dynamic resource creation block to the HTML head of a page.
   *
   * @param object $node
   *    Node to add dynamic resource creation snipped to.
   */
  public function createDynamic($node) {
    $resource_values = $this->getResourceValues($node);

    if ($resource_values['key'] != '') {

      $output = '<script type="application/imoneza"><Resource><Name>' .
        $resource_values['name'] . '</Name><Title>' .
        $resource_values['title'] . '</Title>' .
        ($resource_values['description'] == '' ? '' : '<Description>' .
          $resource_values['description'] . '</Description>') .
        ($resource_values['publicationDate'] == '' ? '' :
          '<PublicationDate>' .
          $resource_values['publicationDate'] . '</PublicationDate>')
        . '</Resource> </script>';

      $imoneza_head = array(
        "#tag" => "script",
        "#type" => "markup",
        "#markup" => $output
      );

      drupal_add_html_head($imoneza_head, "imoneza-dynamic-header");
    }
  }
}
