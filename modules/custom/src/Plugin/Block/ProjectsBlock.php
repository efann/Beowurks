<?php

// From http://valuebound.com/resources/blog/drupal-8-how-to-create-a-custom-block-programatically

namespace Drupal\custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\views\Views;

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------

/**
 * Provides a 'projects' block.
 *
 * @Block(
 *   id = "projects_block",
 *   admin_label = @Translation("Projects Block"),
 *   category = @Translation("Custom block for displaying the projects of Beowurks.")
 * )
 */
class ProjectsBlock extends BlockBase
{

  const VIEW_PROJECTS = 'project_list';
  const VIEW_PROJECTS_BLOCK = 'block_projects_list';
  const NO_DATA = 'Not much data to show here. . . .';

  //-------------------------------------------------------------------------------------------------

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $loViewExecutable = Views::getView(self::VIEW_PROJECTS);
    if (!is_object($loViewExecutable))
    {
      return array(
          '#type' => 'markup',
          '#markup' => t(self::NO_DATA),
      );
    }


    $loViewExecutable->execute(Self::VIEW_PROJECTS_BLOCK);

    $lcContent = '';

    $lcContent .= "<div id='projects_block' style='overflow: hidden; clear: both;'>\n";
    $lcContent .= "<h4>Projects</h4>\n";


    $lcContent .= "<div class='flexslider'>\n";
    $lcContent .= "<ul class='slides'>\n";

    foreach ($loViewExecutable->result as $lnIndex => $loRow)
    {
      $lcContent .= "<li>\n";

      $loNode = $loRow->_entity;

      $lnID = $loNode->id();
      $lcID = $lnID . '_project';
      $lcTitle = $loNode->get('title')->value;
      $lcImage = $this->getNodeField($loNode, 'field_screen_shot');

      $lcContent .= "<img class='responsive-image-large' id='$lcID' src='$lcImage' alt='$lcTitle' title='$lcTitle' />" . "\n";

      $lcContent .= "</li>\n";
    }

    $lcContent .= "</ul>\n";
    $lcContent .= "</div>\n";

    $lcContent .= "</div>\n";

    // From https://drupal.stackexchange.com/questions/199527/how-do-i-correctly-setup-caching-for-my-custom-block-showing-content-depending-o
    return (array(
        '#type' => 'markup',
        '#cache' => array('max-age' => 0),
        '#markup' => $lcContent,
    ));

  }

  //-------------------------------------------------------------------------------------------------

  private function getNodeField($toNode, $tcField)
  {
    $lcValue = '';
    if ($toNode->hasField($tcField))
    {
      $loField = $toNode->get($tcField);

      if ($loField->entity instanceof \Drupal\file\Entity\File)
      {
        $lcPublicValue = $loField->entity->uri->value;
        $lcURL = \Drupal::service('stream_wrapper_manager')->getViaUri($lcPublicValue)->getExternalUrl();

        $laURL = parse_url($lcURL);
        $lcValue = $laURL['path'];
      }
      else
      {
        $lcValue = $loField->value;
      }
    }

    return ($lcValue);
  }

  //-------------------------------------------------------------------------------------------------

}

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
