<?php


namespace Drupal\custom\Controller;

use Drupal\taxonomy\Entity\Term;
use Drupal\views\Views;

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
class ApplicationPartsController
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

    $lcPageTitle = $this->getTitle();

    $lnProjectID = $this->getTermID($lcPageTitle);
    $laArgs = [$lnProjectID];

    $loViewExecutable->setArguments($laArgs);
    $loViewExecutable->execute(Self::APP_LIST_BLOCK);

    $lcContent = '';

    $lcContent .= "<h4 class='application_description'>" . $lcPageTitle . "&copy;" . "</h4>\n";

    try
    {
      $loTerm = Term::load($lnProjectID);
      $lcContent .= "<div  class='application_description'>" . $loTerm->get('description')->value . "</div>\n";
    }
    catch (\Exception $loErr)
    {
      $lcContent .= "<p class='application_description'>Unknown description. . . .</p>\n";
    }

    $lcContent .= "<div id='TaxonomyContentAndListforTabs'>\n";
    $lcContent .= "<ul>\n";
    foreach ($loViewExecutable->result as $lnIndex => $loRow)
    {
      $loNode = $loRow->_entity;

      $lnID = $loNode->id();
      $lcTitle = $loNode->get('title')->value;

      $lcHref = "/ajax/node/" . $lnID;
      $lcContent .= "<li><a href='$lcHref'>$lcTitle</a></li>\n";
    }
    $lcContent .= "</div>\n";
    $lcContent .= "</ul>\n";

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
