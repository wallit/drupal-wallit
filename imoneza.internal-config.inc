<?php
/**
 * @file The internal configuration options for this plugin
 *
 * @author Aaron Saray
 */

use iMoneza\Drupal\Model;

/**
 * Creates internal configuration form
 * 
 * @see imoneza_menu()
 * @param $form
 * @param $form_state
 * @return mixed
 */
function imoneza_internal_config_form($form, &$form_state) {
    $form['#theme'] = 'imoneza_internal_config_form';
    $form['#attached']['css'] = array(
        drupal_get_path('module', 'imoneza') . '/assets/admin.css',
    );

    $form['manage_api_url'] = array(
        '#type' =>  'textfield',
        '#title'  =>  t('Resource Management API URL:'),
        '#attributes' =>  array(
            'placeholder'   =>  Model\Options::DEFAULT_MANAGE_API_URL
        )
    );
    $form['access_api_url'] = array(
        '#type' =>  'textfield',
        '#title'  =>  t('Resource Access API URL:'),
    );
    $form['javascript_cdn_url'] = array(
        '#type' =>  'textfield',
        '#title'  =>  t('Javascript CDN URL:'),
    );
    $form['manage_ui_url'] = array(
        '#type' =>  'textfield',
        '#title'  =>  t('Manage UI URL:'),
    );

    $form['actions']['submit'] = array(
        '#type' =>  'submit',
        '#value'  =>  t('Save')
    );

    return $form;
}

/**
 * Implements hook_theme().
 *
 * This is the theme for the internal configuration form
 *
 * @param $vars
 * @return string
 */
function theme_imoneza_internal_config_form($vars) {
    $form = $vars['form'];
    $output = "<h3>" . t("Internal Configuration") . "</h3>";
    $output .= "<p>" . t("Here you can override the URLs built into the plugin.  Do not use this section unless you know what you're doing.");
    $output .= " <strong>" . t("Leave them blank") . "</strong> " . t("to use the default production URLs.") . "</p>";

    $output .= drupal_render_children($form);
    return $output;
}

