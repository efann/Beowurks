<?php


namespace Drupal\custom\Controller;

use Drupal\views\Views;

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
class HelpSystemController
  //extends BookNavigationBlock
{
  const NO_DATA = 'Not much data to show here. . . .';
  const APP_LIST_VIEW = 'application_list';
  const APP_LIST_BLOCK = 'block_application_list';

  private $fcProject;
  // The controller method receives these parameters as arguments.
  // The parameters are mapped to the arguments with the same name.
  // So in this case, the page method of the NodeController has one argument: $tcCustomCategory. There may be multiple parameters in a
  // route, but their names should be unique.
  //-------------------------------------------------------------------------------------------------
  public function getContent($tcProject)
  {
    $loViewExecutable = Views::getView(self::APP_LIST_VIEW);
    if (!is_object($loViewExecutable))
    {
      return array(
          '#type' => 'markup',
          '#markup' => t(self::NO_DATA),
      );
    }

    $this->fcProject = $tcProject;

    $lcContent = '';

    $lcContent .= "<h4 class='documentation'>" . $this->fcProject . "&copy;" . "</h4>\n";

    $loBookManger = \Drupal::service('book.manager');

    $laBooks = $loBookManger->getAllBooks();
    $lnBookID = null;
    foreach ($laBooks as $loBook)
    {
      $lcContent .= "<p>" . $loBook['title'] . "   " . $this->fcProject . "</p>";
      if (strpos($loBook['title'], $this->fcProject) !== false)
      {
        $lnBookID = $loBook['nid'] + 0;
        break;
      }
    }

    if ($lnBookID)
    {

      $laTOC = $loBookManger->getTableOfContents($lnBookID, 20);

      $lcContent .= '<ul>';
      $lnLevel = 0;
      foreach ($laTOC as $lnID => $lcTitle)
      {
        $lcMask = ltrim($lcTitle, " -");
        $lnPos = strpos($lcTitle, $lcMask);
        $lcDashes = trim(substr($lcTitle, 0, $lnPos));
        $lnDepth = strlen($lcDashes);

        if ($lnLevel < $lnDepth)
        {
          $lcContent .= '<ul>';
        }
        else if ($lnLevel > $lnDepth)
        {
          $lcContent .= '</ul>';
        }
        $lcContent .= "<li id='$lnID'>$lcMask</li>";

        $lnLevel = $lnDepth;

      }
      $lcContent .= '</ul>';

    }


    return array(
        '#type' => 'markup',
        '#markup' => t($lcContent),
    );


  }

  //-------------------------------------------------------------------------------------------------

  public function getTitle()
  {
    $lcValue = trim($this->fcProject);
    $lcValue = str_replace('-', ' ', $lcValue);
    $lcValue = str_replace('_', ' ', $lcValue);

    return (ucwords($lcValue, " "));
  }

  //-------------------------------------------------------------------------------------------------

  private function getTermID($tcCategory)
  {
    $lnID = -1;

    // From https://drupal.stackexchange.com/questions/225209/load-term-by-name
    $laTerms = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadByProperties(['name' => $tcCategory]);

    if (!$laTerms)
    {
      return ($lnID);
    }

    // reset() rewinds array's internal pointer to the first element and returns the
    // value of the first array element, or FALSE if the array is empty.
    $lnID = (int)reset($laTerms)->id();

    return ($lnID);
  }

  //-------------------------------------------------------------------------------------------------

}

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
