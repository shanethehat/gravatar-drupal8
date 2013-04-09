<?php

/**
 * @file
 * Contains \Drupal\image\Plugin\field\formatter\ImageFormatter.
 */

namespace Drupal\gravatar\Plugin\field\formatter;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Drupal\image\Plugin\field\formatter\ImageFormatter;
use Drupal\user\Plugin\Core\Entity\User;
use Drupal\Core\Config\Config;

/**
 * Plugin implementation of the 'gravatar' formatter.
 *
 * @Plugin(
 *   id = "gravatar",
 *   module = "gravatar",
 *   label = @Translation("Gravatar"),
 *   field_types = {
 *     "image"
 *   },
 *   settings = {
 *     "image_style" = "",
 *     "image_link" = ""
 *   }
 * )
 */
class GravatarFormatter extends ImageFormatter {
  
  /**
   * Implements \Drupal\field\Plugin\Type\Formatter\FormatterInterface::prepareView().
   */
  public function prepareView(array $entities, $langcode, array &$items) {
    parent::prepareView($entities, $langcode, $items);
    
    //echo '<pre>'; var_dump($items); exit;
    
    /*
     * @todo implement gravatar logic:
     * 
     * if the user has has permission to disable their gravatar and has done so
     *     do not change the item
     * else if the user does not have the use gravatar permission
     *     replace the item with one that uses the default gravatar image
     * else
     *     replace the item with the loaded gravatar
     */
    
    foreach ($entities as $uid => $entity) {
      if (empty($items[$uid])) {
        $items[$uid][] = $this->getGravatarItem($entity);
        continue;
      }
      foreach ($items[$uid] as &$item) {
        $item = $this->getGravaterItem($entity);
      }
    }
    
    //echo '<pre>'; var_dump($items); exit;
  }
  
  protected function getGravatarItem(User $user) {
    
    $filename = $this->getEmailHash($user) . '.jpg';
    
    $config = config('gravatar.settings');
    
    $size = explode('x', $config->get('user_picture_dimensions'));
    
    $item = array(
      'alt' => 'Gravatar',
      'title' => '',
      'width' => $size[0],
      'height' => $size[1],
      'filemime' => 'image/jpeg',
      'status' => 1,
      'uid' => $user->uid,
      'filename' => $filename,
      'uri' => $this->getGravatarPath($filename, $config),
    );
    
    return $item;
  }
  
  protected function getEmailHash(User $user) {
    return md5($user->mail);
  }
  
  protected function getGravatarPath($filename, Config $config) {
    global $is_https;
    
    // default options.
    $options = array(
      'default' => $this->getDefaultImage($config->get('gravatar_default'), $config),
      'size' => $this->getSize($config),
      'rating' => $config->get('gravatar_rating'),
      'cache' => (bool) $config->get('gravatar_cache'),
    );

    // @todo Implement cache fetching.
    //if ($options['cache'] && gravatar_var('cache') && valid_email_address($mail)) {
    //  if ($cached = cache_get($hash, 'gravatar')) {
    //    return $cached;
    //  }
    //  elseif ($data = _gravatar_get_gravatar_image($mail)) {
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
   * @param $config
   *   Config object 
   * @return
   *   The default image for use in a Gravatar avatar URL.
   */
  protected function getDefaultImage($index, Config $config) {
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
   * @return
   *   An integer representing a square image size in pixels.
   */
  protected function getSize($config) {
    static $size = NULL;
    if (!isset($size)) {
      $size = min(explode('x', $config->get('user_picture_dimensions') . 'x' . GRAVATAR_SIZE_MAX));
    }
    return $size;
  }
    
}
