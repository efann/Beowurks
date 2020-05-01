<?php


namespace Drupal\custom\Controller;

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
class HelpSystemController
{
  private $fcProject;
  // The controller method receives these parameters as arguments.
  // The parameters are mapped to the arguments with the same name.
  // So in this case, the page method of the NodeController has one argument: $tcCustomCategory. There may be multiple parameters in a
  // route, but their names should be unique.
  //-------------------------------------------------------------------------------------------------
  public function getContent($tcProject)
  {
    $lcContent = '';

    $this->fcProject = $tcProject;

    $lcContent .= "<div class='row'>\n";
    $lcContent .= "<div class='controller-display-id-help-documentation'>\n";
    $lcContent .= "<h4 class='documentation'>" . $this->fcProject . "&copy; fred" . "</h4>\n";

    $lcContent .= "<div id='jqtree_list' class='col-sm-4'></div>\n";
    $lcContent .= "<div id='jqtree_content' class='col-sm-8'></div>\n";

    $lcContent .= "</div>\n";
    $lcContent .= "</div>\n";

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

    $lcValue .= " Documentation";

    return (ucwords($lcValue, " "));
  }
  //-------------------------------------------------------------------------------------------------

}

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
