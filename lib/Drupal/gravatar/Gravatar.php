<?php

/**
 * @file
 * Defines Drupal\gravatar\Gravatar
 */

namespace Drupal\gravatar;

use Drupal\Core\Config\Config;

/**
 * Gravatar object helpers
 */
class Gravatar {
  
  /**
   * Return a path to a gravatar image
   * 
   * @param string $filename 
   *   Image filename, usually a hash of an email address
   * @param \Drupal\Core\Config\Config $config
   *   Gravatar config object
   * @param array $options
   *   Override default options
   * @return string 
   *   Gravatar URL
   */
  public static function getPath($filename, Config $config, array $options = array()) {
    
    global $is_https;
    
    // default options.
    $options += array(
      'default' => self::getDefaultImage($config->get('gravatar_default'), $config),
      'size' => self::getSize($config),
      'rating' => $config->get('gravatar_rating'),
      'cache' => (bool) $config->get('gravatar_cache')
    );

    // @todo Implement cache fetching.
    //if ($options['cache']) {
    //  if ($cached = cache_get($hash, 'gravatar')) {
    //    return $cached;
    //  } elseif ($data = _gravatar_get_gravatar_image($mail)) {
    //    cache_set($hash, $data, 'gravatar');
    //    return $data;
    //  }
    //}

    $gravatar = $is_https ? $config->get('gravatar_url_ssl') : $config->get('gravatar_url');

    $gravatar .= $filename;
    $query = array(
      'd' => $options['default'],
      's' => $options['size'],
      'r' => $options['rating'],
    );
    return url($gravatar, array('query' => $query));
  }
  
  
  /**
   * Get the default gravatar image.
   *
   * @param $index
   *   An integer index for selection.
   * @param \Drupal\Core\Config\Config $config
   *   Gravatar config object
   * @return string
   *   The default image for use in a Gravatar avatar URL.
   */
  public static function getDefaultImage($index, Config $config) {
    global $base_url;
    static $defaults = array();
     if (!isset($defaults[$index])) {
      $default = NULL;
      switch ($index) {
        case GRAVATAR_DEFAULT_GLOBAL:
          $default = $config->get('user_picture_default');
          if ($default && !valid_url($default, TRUE)) {
            // Convert a relative global default picture URL to an absolute URL.
            $default = file_create_url($default);
          }
          break;
        case GRAVATAR_DEFAULT_MODULE:
          $default = $base_url . '/' . drupal_get_path('module', 'gravatar') . '/avatar.png';
          break;
        case GRAVATAR_DEFAULT_MODULE_CLEAR:
          $default = $base_url . '/' . drupal_get_path('module', 'gravatar') . '/avatar-clear.png';
          break;
        case GRAVATAR_DEFAULT_IDENTICON:
          $default = 'identicon';
          break;
        case GRAVATAR_DEFAULT_WAVATAR:
          $default = 'wavatar';
          break;
        case GRAVATAR_DEFAULT_MONSTERID:
          $default = 'monsterid';
          break;
        case GRAVATAR_DEFAULT_LOGO:
          $default = 'default';
          //$default = $base_url . '/' . drupal_get_path('module', 'gravatar') . '/gravatar.jpg';
          break;
        case 404:
          $default = '404';
          break;
      }
      $defaults[$index] = $default;
    }
    // @todo Remove when stable.
    if (!isset($defaults[$index])) {
      watchdog('gravatar', 'Hit unwanted condition in _gravatar_get_default_image.');
      return NULL;
    }

    return $defaults[$index];
  }
  
  /**
   * Get the size in pixels of the gravatar.
   *
   * @param \Drupal\Core\Config\Config $config
   *   Gravatar config object
   * @return
   *   An integer representing a square image size in pixels.
   */
  public static function getSize(Config $config) {
    static $size = NULL;
    if (!isset($size)) {
      $size = min(explode('x', $config->get('user_picture_dimensions') . 'x' . GRAVATAR_SIZE_MAX));
    }
    return $size;
  }
  
}
