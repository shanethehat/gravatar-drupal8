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
use Drupal\gravatar\Gravatar;

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
  
  /**
   * Returns a image field item array
   * 
   * @param \Drupal\user\Plugin\Core\Entity\User $user
   *   User entity
   * @return array
   *   Image field item definition
   */
  protected function getGravatarItem(User $user) {
    
    $config = config('gravatar.settings');
    $filename = $this->getEmailHash($user) . '.jpg';
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
      'uri' => Gravatar::getPath($filename, $config),
    );
    
    return $item;
  }
  
  /**
   * returns a hsh of an email address to be used is the gravatar request
   * 
   * @param \Drupal\user\Plugin\Core\Entity\User $user
   *   User entity
   * @return string
   *   MD5 hash of the email address
   */
  protected function getEmailHash(User $user) {
    return md5($user->mail);
  }
  
  
  
  

    
  
  
    
}
