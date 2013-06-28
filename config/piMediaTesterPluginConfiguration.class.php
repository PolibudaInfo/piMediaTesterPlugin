<?php

/**
 * piMediaTesterPlugin configuration.
 * 
 * @package     piMediaTesterPlugin
 * @subpackage  config
 * @author      Jarek Rencz <jrencz@polibuda.info>
 */
class piMediaTesterPluginConfiguration extends sfPluginConfiguration
{
  const VERSION = '1.0.0-DEV';

  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
  }
  
  public function configure()
  {
    $this->dispatcher->connect('dm.media_library.control_menu', array($this, 'listenToMediaLibraryControlMenuEvent'));
  }
  
  public static function listenToMediaLibraryControlMenuEvent(sfEvent $event)
  {
    $media_library_menu = $event->getSubject();
    
    $media_library_menu->addChild(
      $media_library_menu->getI18n()->__('Missing media'),
      $media_library_menu->getHelper()->link('+/dmMediaLibrary/missingMedia')
        ->set('.s16.s16_folder_clear')
    )->end();
    
  }
}
