<?php
/**
 * @file The admin settings information
 *
 * @author Aaron Saray
 */

/**
 * Hook that displays the first time admin form
 *
 * @see imoneza_menu()
 * @param $form
 * @param $form_state
 * @return mixed
 */
//function imoneza_first_time_form($form, &$form_state) {
//    $form['#theme'] = 'imoneza_first_time_form';
//    $form['#attached']['css'] = array(
//        drupal_get_path('module', 'imoneza') . '/assets/admin.css',
//    );
//
//    $form['manage_api_key'] = array(
//        '#type' =>  'textfield',
//        '#title'  =>  t('Resource Management API Key:'),
//        '#required' => true
//    );
//    $form['manage_api_secret'] = array(
//        '#type' =>  'textfield',
//        '#title'  =>  t('Resource Management API Secret:'),
//        '#required' =>  true
//    );
//
//    $form['actions']['submit'] = array(
//        '#type' =>  'submit',
//        '#value'  =>  t('Verify Access')
//    );
//
//    return $form;
//}
//
///**
// * Implements hook_theme().
// *
// * This is the theme for the first time form
// *
// * @param $vars
// * @return string
// */
//function theme_imoneza_first_time_form($vars) {
//    $form = $vars['form'];
//    $output = '<p class="imz-welcome">' . t("Hey there!  It looks like it's your first time here.  Let's get started!") . "</p>";
//    $output .= "<h3>" . t("Time to do a little configuration") . "</h3>";
//    $output .= "<p>" . t("Right now, all we need is your Resource Management API key and secret.");
//    $output .= t("This will help us custom tailor the rest of the plugin options for you.") . "</p>";
//    $output .= drupal_render_children($form);
//    return $output;
//}
//
//
///**
// * Implements the validation for first time form
// *
// * @param $form
// * @param $form_state
// */
//function imoneza_first_time_form_validate($form, &$form_state) {
//    $apiKey = $form_state['values']['manage_api_key'];
//    $apiSecret = $form_state['values']['manage_api_secret'];
//
//    //form_set_error('manage_api_secret', 'this is a test error.');
//}


/**
 * Implements the form saving mechanism for first time form
 *
 * @param $form
 * @param $form_state
 */
//function imoneza_first_time_form_submit($form, &$form_state) {
//  variable_set('imoneza_show_adblock_notification', $form_state['values']['show_adblock_notification']);
//  variable_set('imoneza_adblock_notification', $form_state['values']['adblock_notification']);
//  drupal_set_message(t('Your form has been saved.'));
//}