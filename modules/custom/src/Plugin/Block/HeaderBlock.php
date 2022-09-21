<?php

// From http://valuebound.com/resources/blog/drupal-8-how-to-create-a-custom-block-programatically

namespace Drupal\custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------

/**
 * Provides a header block.
 *
 * @Block(
 *   id = "header_block",
 *   admin_label = @Translation("Header Block"),
 *   category = @Translation("Custom block for displaying the header.")
 * )
 */
class HeaderBlock extends BlockBase
{
  //-------------------------------------------------------------------------------------------------

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $lcContent = "";

    // From https://drupal.stackexchange.com/questions/187400/how-do-i-show-the-site-slogan

    $lcContent .= "<div>\n";

    $lcContent .= "<div style='float: left;'>\n";

    $lcContent .= "<div class='title'><a href='/'>" . \Drupal::config('system.site')->get('name') . "</a></div>\n";
    $lcContent .= "<div class='slogan'>" . \Drupal::config('system.site')->get('slogan') . "</div>\n";

    $lcContent .= "</div>\n";
    // canvas must have the width & height declared here and not in the style.
    $lcContent .= "<canvas class='fractal' width='130' height='130'></canvas>\n";

    $lcContent .= "</div>\n";

    // From https://drupal.stackexchange.com/questions/199527/how-do-i-correctly-setup-caching-for-my-custom-block-showing-content-depending-o
    return (array(
        '#type' => 'markup',
        '#cache' => array('max-age' => 0),
      // From https://drupal.stackexchange.com/questions/184963/pass-raw-html-to-markup
      // Otherwise, convas tag was being stripped.
        '#markup' => \Drupal\Core\Render\Markup::create($lcContent)
    ));

  }

  //-------------------------------------------------------------------------------------------------

}

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
