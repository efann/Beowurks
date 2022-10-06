<?php

// From http://valuebound.com/resources/blog/drupal-8-how-to-create-a-custom-block-programatically

namespace Drupal\custom\Plugin\Block;

use Drupal;
use Drupal\Core\Block\BlockBase;
use Drupal\file\Entity\File;
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

    $loViewExecutable->execute(self::VIEW_PROJECTS_BLOCK);

    $lcContent = '';
    $lcContent .= "<h4>Projects</h4>\n";

    $lcContent .= "<div id='carousel_projects_block' class='carousel slide row' data-ride='carousel'>\n";

    $lcContent .= "<ol class='carousel-indicators'>\n";
    foreach ($loViewExecutable->result as $lnIndex => $loRow)
    {
      $lcContent .= "<li data-target='#carousel_projects_block' data-slide-to='$lnIndex'" . ($lnIndex == 0 ? " class='active'" : "") . "></li>\n";
    }
    $lcContent .= "</ol>\n";

    $lcContent .= "<div class='carousel-inner'>\n";

    $lcActive = " active";
    foreach ($loViewExecutable->result as $lnIndex => $loRow)
    {
      $loNode = $loRow->_entity;

      $lnID = $loNode->id();
      $lcID = $lnID . '_project';
      $lcTitle = $loNode->get('title')->value;
      $lcURL = $this->getNodeField($loNode, 'field_project_url');
      $lcImage = $this->getNodeField($loNode, 'field_screen_shot');
      $lcAlt = "$lcTitle ($lcURL)";

      $lcContent .= "<div class='col-xs-12 carousel-item$lcActive'>";
      $lcContent .= "<a class='dialogbox-image' href><img class='responsive-image-large' id='$lcID' src='$lcImage' alt='$lcAlt' title='$lcAlt' /></a>\n";
      $lcContent .= "</div>\n";

      $lcActive = "";
    }

    $lcContent .= "</div>\n";

    $lcContent .= "<a class='carousel-control-prev' href='#carousel_projects_block' role='button' data-slide='prev'>\n";
    $lcContent .= "<span class='carousel-control-prev-icon' aria-hidden='true'></span>\n";
    $lcContent .= "<span class='sr-only'>Previous</span>\n";
    $lcContent .= "</a>\n";
    $lcContent .= "<a class='carousel-control-next' href='#carousel_projects_block' role='button' data-slide='next'>\n";
    $lcContent .= "<span class='carousel-control-next-icon' aria-hidden='true'></span>\n";
    $lcContent .= "<span class='sr-only'>Next</span>\n";
    $lcContent .= "</a>\n";

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

      if ($loField->entity instanceof File)
      {
        $lcPublicValue = $loField->entity->uri->value;
        $lcURL = Drupal::service('stream_wrapper_manager')->getViaUri($lcPublicValue)->getExternalUrl();

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
