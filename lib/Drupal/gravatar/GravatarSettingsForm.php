<?php

/**
 * @file
 * Defines Drupal\gravatar\GravatarSettingsForm
 */

namespace Drupal\gravatar;

use Drupal\system\SystemConfigFormBase;

/**
 * Gravatar module URL endpoints
 */
class GravatarSettingsForm extends SystemConfigFormBase {
  
  /**
   * Gravatar config object
   * 
   * @var Drupal\Core\Config\Config 
   */
  protected $config;
  
  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormID() {
    return 'gravatar_settings';
  }

  
 /** 
  * Administration settings form.
  *
  * Implements \Drupal\Core\Form\FormInterface::buildForm().
  */
  public function buildForm(array $form, array &$form_state) {
    
    $config = $this->getConfig();
    
    // Display settings.
    $form['display'] = array(
      '#type'        => 'fieldset',
      '#title'       => t('Display'),
    );
    $form['display']['gravatar_size'] = array(
      '#type'          => 'item',
      '#title'         => t('Image size'),
      '#description'   => t('The preferred image size (maximum @max pixels). This setting can be adjusted in the <a href="@user-picture-link">user pictures settings</a>.', array('@max' => GRAVATAR_SIZE_MAX, '@user-picture-link' => url('admin/config/people/accounts', array('fragment' => 'edit-user-picture-default')))),
      '#value'         => t('@sizex@size', array('@size' => Gravatar::getSize($config))),
    );
    $form['display']['gravatar_rating'] = array(
      '#type'          => 'select',
      '#title'         => t('Image maturity filter'),
      '#description' => theme('item_list', array('items' => array(
        t('G: Suitable for display on all websites with any audience type.'),
        t('PG: May contain rude gestures, provocatively dressed individuals, the lesser swear words, or mild violence.'),
        t('R: May contain such things as harsh profanity, intense violence, nudity, or hard drug use.'),
        t('X: May contain hardcore sexual imagery or extremely disturbing violence.'),
      ))),
      '#options'       => drupal_map_assoc(array('G', 'PG', 'R', 'X')),
      '#default_value' => $config->get('gravatar_rating'),
    );
    $form['display']['gravatar_default'] = array(
      '#type'          => 'radios',
      '#title'         => t('Default image'),
      '#description'   => t('Specifies an image that should be returned if either the requested e-mail address has no associated gravatar, or that gravatar has a rating higher than is allowed by the maturity filter.'),
      '#options'       => array(
        GRAVATAR_DEFAULT_GLOBAL => t('Global default user image'),
        GRAVATAR_DEFAULT_MODULE => t('Module default image (white background)'),
        GRAVATAR_DEFAULT_MODULE_CLEAR => t('Module default image (transparent background)'),
        GRAVATAR_DEFAULT_IDENTICON => t('Gravatar.com identicon (generated)'),
        GRAVATAR_DEFAULT_WAVATAR => t('Gravatar.com wavatar (generated)'),
        GRAVATAR_DEFAULT_MONSTERID => t('Gravatar.com monsterid (generated)'),
        GRAVATAR_DEFAULT_LOGO => t('Gravatar.com logo'),
      ),
      '#default_value' => $config->get('gravatar_default'),
      '#field_prefix' => '<div class="picture js-show">' . theme('image', array('path' => '', 'alt' => t('Default picture example'), 'title' => t('Default picture example'), 'attributes' => array('id' => 'gravatar-imagepreview'), 'getsize' => FALSE)) . '</div>',
      '#process' => array('form_process_radios', array($this,'gravatar_process_default_setting')),
    );

    // Add JavaScript and CSS to swap the default image previews.
    $form['#attached']['js'][] = drupal_get_path('module', 'gravatar') . '/gravatar.js';
    $form['#attached']['css'][] = drupal_get_path('module', 'gravatar') . '/gravatar.css';

    // Advanced settings.
    $form['advanced'] = array(
      '#type' => 'fieldset',
      '#title' => t('Advanced'),
      '#description' => t('Do not modify these options unless you know what you are doing!'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $form['advanced']['gravatar_url'] = array(
      '#type' => 'textfield',
      '#title' => t('Gravatar URL'),
      '#default_value' => $config->get('gravatar_url'),
    );
    $form['advanced']['gravatar_url_ssl'] = array(
      '#type' => 'textfield',
      '#title' => t('Gravatar secure URL'),
      '#default_value' => $config->get('gravatar_url_ssl'),
    );

    return parent::buildForm($form, $form_state);
  }

  
  public function gravatar_process_default_setting($element) {
    
    $config = $this->getConfig();
    
    $element[GRAVATAR_DEFAULT_GLOBAL]['#description'] = t('This setting can be adjusted in the <a href="@user-picture-link">user pictures settings</a>.', array('@user-picture-link' => url('admin/config/people/accounts', array('fragment' => 'edit-user-picture-default'))));
    // If the global user picture is empty, disable the respective option.
    if (!variable_get('user_picture_default', '')) {
      $element[GRAVATAR_DEFAULT_GLOBAL]['#disabled'] = TRUE;
      $element[GRAVATAR_DEFAULT_GLOBAL]['#description'] = t('There currently is not a global default user picture specified.') . ' ' . $element[GRAVATAR_DEFAULT_GLOBAL]['#description'];
    }

    foreach ($element['#options'] as $key => $choice) {
      // Add an image to preview this default image option.
      $options = array(
        'path' =>  Gravatar::getPath(mt_rand(), $config, array('default' => Gravatar::getDefaultImage($key, $config), 'size' => 80)),
        'alt' => $choice,
        'title' => $choice,
        'attributes' => array(
          'id' => 'gravatar-imagepreview-'. $key,
          // Hide the image if the respective option is disabled.
          'class' => isset($element[$key]['#disabled']) && $element[$key]['#disabled'] ? 'hide' : 'js-hide',
        ),
        'getsize' => FALSE,
      );
      $element[$key]['#suffix'] = theme('image', $options);
    }

    return $element;
  }
  
  /**
   * Implements \Drupal\Core\Form\FormInterface::validateForm().
   */
  public function validateForm(array &$form, array &$form_state) {
    
    // @todo

    parent::validateForm($form, $form_state);
  }
  
  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, array &$form_state) {
    $values = $form_state['values'];
    
    $this->getConfig()
      ->set('gravatar_size', $values['gravatar_size'])
      ->set('gravatar_rating', $values['gravatar_rating'])
      ->set('gravatar_default', $values['gravatar_default'])
      ->set('gravatar_url', $values['gravatar_url'])
      ->set('gravatar_url_ssl', $values['gravatar_url_ssl'])
      ->save();
    
    parent::submitForm($form, $form_state);
  }
  
  /**
   * Lazy load and return the gravatar config object
   * 
   * @return Drupal\Core\Config\Config
   */
  protected function getConfig() {
    if (null === $this->config) {
      $this->config = $this->configFactory->get('gravatar.settings');
    }
    return $this->config;
  }
}
