<?php
/****************************************************************************************\
**   Plugin 'JoomDisplayKunenaName' 1.5                                                 **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2010 - 2015 Patrick Alt; since 2019 JoomGallery::ProjectTeam         **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

jimport('joomla.plugin.plugin');

/**
 * JoomGallery Display Kunena Name Plugin
 *
 * @package     Joomla
 * @subpackage  JoomGallery
 * @since       1.5
 */
class plgJoomGalleryJoomDisplayKunenaName extends JPlugin
{
  /**
   * True if Kunena is available, false otherwise
   *
   * @var   boolean
   * @since 2.0
   */
  protected $kunena_available = true;

  /**
   * Constructor
   *
   * For php4 compatability we must not use the __constructor as a constructor for plugins
   * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
   * This causes problems with cross-referencing necessary for the observer design pattern.
   *
   * @access  protected
   * @param   object    $subject  The object to observe
   * @param   object    $params   The object that holds the plugin parameters
   * @return  void
   * @since   1.5
   */
  function __construct(&$subject, $params)
  {
    parent::__construct($subject, $params);

    if(!class_exists('KunenaForum') || !KunenaForum::isCompatible('4.0') || !KunenaForum::enabled())
    {
      $this->loadLanguage();
      JFactory::getApplication()->enqueueMessage(JText::_('PLG_JOOMDISPLAYKUNENANAME_KUNENA_SEEMS_NOT_TO_BE_INSTALLED'), 'notice');
      $this->kunena_available = false;
    }
  }

  /**
   * OnJoomDisplayUser method
   *
   * Method links a user name with the corresponding Community Builder profile.
   *
   * @access  public
   * @param   int     $userID   The ID of the user to display
   * @param   boolean $realname True, if the user's full name shall be displayed
   * @param   string  $context  The context in which the name will be displayed
   * @return  string  The HTML code created for displaying the user's name
   * @since   1.5
   */
  function onJoomDisplayUser($userId, $realname, $context = null)
  {
    if(!$this->kunena_available)
    {
      return null;
    }

    $user = KunenaFactory::getUser((int) $userId);

    if(!$user->get('userid') || JFactory::getApplication()->isAdmin())
    {
      return null;
    }

    $name   = $realname ? $user->get('name') : $user->get('username');
    $avatar = KunenaFactory::getAvatarIntegration()->getLink($user);
    $link   = KunenaFactory::getProfile()->getProfileUrl((int) $userId);

    $overlib  = htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8');

    if($context == 'comment')
    {
      $html = '<a href="'.$link.'">'.$name.'</a><br /><a href="'.$link.'">'.$avatar.'</a>';

      return $html;
    }

    JHTML::_('behavior.tooltip', '.hasHint');
    $html = '<a href="'.$link.'" title="'.$name.'" rel="'.$overlib.'" class="hasHint">'.$name.'</a>';

    return $html;
  }

  /**
   * Replaces the smileys of JoomGallery with the ones of Kunena, if enabled
   *
   * @access  public
   * @param   array   An array of smileys
   * @return  void
   * @since   1.5
   */
  function onJoomGetSmileys(&$smileys)
  {
    if(!$this->kunena_available || !$this->params->get('replace_smileys'))
    {
      return;
    }

    $smileys = KunenaHtmlParser::getEmoticons(false, true);
  }
}