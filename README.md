# iMoneza Drupal Plugin
This plugin provides integration with the [iMoneza](http://www.imoneza.com "iMoneza") service for sites running Drupal 7.x.

## Getting started
Too install the plugin, either clone the repository or download a zip and drop into `/sites/all/modules` and activate the
module.

## Configuration
Once installed, you must configure the plugin with iMoneza credentials including the Resource Access Key/Secret and the 
Resource Management Key/Secret available from the API Keys section of the iMoneza website. To access the configuration
scree, click the `iMoneza` link in the admin menu bar.

The plugin is also designed to work with a variety of node types. In order to use the iMoneza service with your site, 
select at least one node type on the iMoneza configuration page.

## Using the plugin
### Resource creation
The iMoneza plugin automatically creates resources in the iMoneza service for ever new node you create that has iMoneza
enabled. You can also optionally enable dynamic resource creation that will allow you to create resources for previously
created content.

### Custom pricing
When creating a resource, the new resource automatically receives default pricing parameters. These parameters can be 
overridden on a resource-by-resource basis using the iMoneza menu on the `Edit` page for the paritcular node. More information
about the supported pricing structures can be found at the [iMoneza Merchants page](https://www.imoneza.com/merchants/).

## Additional resources
More information about the iMoneza service and the Drupal plugin can be found at [iMoneza.com](http://www.imoneza.com "iMoneza").
