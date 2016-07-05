# iMoneza Drupal Plugin

Integrate your site with your iMoneza account and begin monetizing your content.

## Description

iMoneza is a digital micro-transaction paywall service. This Drupal plugin allows you to quickly and easily integrate iMoneza with your site. 
It will add iMoneza's paywall to your site and allow you to manage your iMoneza resources from within Drupal. 
Please note - an iMoneza account is **required**.

Visit [www.imoneza.com](https://imoneza.com) for more information about iMoneza.

## Installation

1. Set up an iMoneza account, create an iMoneza property, and generate a set of API keys.
2. Visit the [latest release link](https://github.com/iMoneza/drupal-imoneza/releases/latest) and download imoneza.zip
3. Upload imoneza.zip through the module installer in Drupal
4. Follow setup wizard

## Developer Notes

If you see a need, please put in a ticket.  Or better yet, fork this and submit your own pull request.

### Development

If you need to work against test or qa, please visit the slug of `/admin/settings/imoneza/config`, a hidden menu item.

### Release Methodology

1. Merge all changes back into master for the drupal version this change is for.
2. Pick the new release number (ex: 7.x-2.3)
3. Change the `imoneza.info` to the new release number
4. Create a new tag with that new release number locally and push to remote.
5. Update branch `release-history` xml file to have the most recent update in it, commit, and push.