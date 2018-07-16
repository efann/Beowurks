<?php

namespace Drupal\custom\Controller;

use Symfony\Component\HttpFoundation\Response;
use Drupal\taxonomy\Entity\Term;

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
class AjaxController
{
  private $fcCategory = '';
  // The controller method receives these parameters as arguments.
  // The parameters are mapped to the arguments with the same name.
  // So in this case, the page method of the NodeController has one argument: $tcCustomCategory. There may be multiple parameters in a
  // route, but their names should be unique.
  //-------------------------------------------------------------------------------------------------
  public function getContent($tcType, $tnNodeID)
  {
    $lcGeneratedContent = '';
    $lcContentType = '';

    try
    {
      if (($tcType === 'node') && (isset($tnNodeID)))
      {
        $loNode = $this->getNode($tnNodeID);
        $lcNodeType = $loNode->getType();

        if ($lcNodeType === 'project')
        {
          $lcContentType = 'application/xml';
          $lcGeneratedContent = $this->generateNodeProjectXML($loNode);
        }
        else if ($lcNodeType === 'applicationparts')
        {
          $lcContentType = 'text/html; utf-8';
          $lcGeneratedContent = $this->generateNodeApplicationPartsText($loNode);
        }
      }
    }
    catch (\Exception $loErr)
    {
      $lcContentType = 'text/html; utf-8';
      $lcGeneratedContent = $loErr->getMessage();
    }

    /*
       Awesome!!!!
       From https://drupal.stackexchange.com/questions/182022/how-to-output-from-custom-module-without-rest-of-theme
    */
    $loResponse = new Response();
    // From https://symfony.com/doc/2.1/components/http_foundation/introduction.html
    $loResponse->headers->set('Content-Type', $lcContentType);
    $loResponse->setContent($lcGeneratedContent);

    return ($loResponse);
  }

  //-------------------------------------------------------------------------------------------------
  private function generateNodeApplicationPartsText($toNode)
  {
    $lcTitle = $toNode->get('title')->value;

    $lcContent = '';

    $lcContent .= "<div>\n";

    if (strcmp($lcTitle, "ChangeLog") == 0)
    {
      $lcContent .= $this->getChangeLog($toNode);
    }
    else
    {
      $lcContent .= $toNode->get('body')->value;
    }

    $lcContent .= "</div>\n";

    return ($lcContent);
  }

  //-------------------------------------------------------------------------------------------------
  private function generateNodeProjectXML($toNode)
  {
    $lcTitle = $toNode->get('title')->value;
    $lcBody = $toNode->get('body')->value;
    $lcURL = $toNode->get('field_project_url')->value;

    $lcContent = '';

    $lcContent .= '<div>';

    $lcContent .= "<h4>$lcTitle</h4>";
    $lcContent .= "<p><a href='$lcURL'>$lcURL</a></p>";
    $lcContent .= $lcBody;

    $lcContent .= '</div>';

    $loWriter = new \XMLWriter();
    // Let's store our XML into the memory so we can output it later
    $loWriter->openMemory();
    // Let's also set the indent so its a very clean and formatted XML
    $loWriter->setIndent(2);
    $loWriter->setIndentString("  ");

    $loWriter->startDocument("1.0", "UTF-8");

    $loWriter->startElement("Data");

    $loWriter->startElement("nodeinfo");
    $loWriter->writeElement("title", $lcTitle);
    $loWriter->writeElement("body", $lcContent);
    $loWriter->endElement(); // nodeinfo

    $loWriter->endElement(); // Data

    $loWriter->endDocument();

    return ($loWriter->outputMemory());
  }

  //-------------------------------------------------------------------------------------------------

  private function getNode($tnNodeID)
  {
    // From https://drupal.stackexchange.com/questions/225209/load-term-by-name
    $loNode = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->load($tnNodeID);

    return ($loNode);
  }


  //-------------------------------------------------------------------------------------------------
  private function getChangeLog($toNode)
  {
    $lcContent = '';
    $lcXMLFile = '';
    $lcTermName = '';

    // From https://stackoverflow.com/questions/37122908/drupal-8-get-taxonomy-term-value-in-node
    // Geesh.
    $lnTermID = $toNode->get('field_application')->target_id;

    try
    {
      $loTerm = Term::load($lnTermID);
      $lcTermName = $loTerm->get('name')->value;
    }
    catch (\Exception $loErr)
    {
      return ($loErr->getMessage());
    }

    if ($lcTermName === 'JEquity')
    {
      return ($this->getJSONChangeLog($lcTermName));
    }

    switch ($lcTermName)
    {
      case 'BeoBasis':
        $lcXMLFile = "http://efann.users.sourceforge.net/svn/xml/beobasis.xml";
        break;

      case 'BeoZip':
        $lcXMLFile = "http://efann.users.sourceforge.net/svn/xml/beozip.xml";
        break;

      case 'JAS Carousel':
        $lcXMLFile = "http://efann.users.sourceforge.net/svn/xml/jasca.xml";
        break;

      case 'PolyJen':
        $lcXMLFile = "http://efann.users.sourceforge.net/svn/xml/polyjen.xml";
        break;

      case 'Trash Wizard':
        $lcXMLFile = "http://efann.users.sourceforge.net/svn/xml/trashwizard.xml";
        break;
    }

    if (strlen($lcXMLFile) == 0)
    {
      return ($lcContent);
    }

    $lcContent .= "<p>From the Apache Subversion log files. . . .</p>";

    $lcContent .= '<div><ul>';
    $loXML = simplexml_load_file($lcXMLFile);

    $lcVersionTag1 = 'Created tag';
    $lcVersionTag2 = 'Create tag';

    foreach ($loXML->children() as $loChild)
    {
      $lcContent .= '<li>';
      $lcDate = substr($loChild->date, 0, 10);
      $lcMessage = $loChild->msg;

      $lcVersionTag = "";
      if (stripos($lcMessage, $lcVersionTag1) !== FALSE)
      {
        $lcVersionTag = $lcVersionTag1;
      }
      else
      {
        if (stripos($lcMessage, $lcVersionTag2) !== FALSE)
        {
          $lcVersionTag = $lcVersionTag2;
        }
      }

      $lnPos = stripos($lcMessage, $lcVersionTag);

      $llVersion = ($lnPos !== FALSE);
      if ($llVersion)
      {
        $lcSubStr = trim(substr($lcMessage, $lnPos + strlen($lcVersionTag)));
        if (substr($lcSubStr, -1) == ' . ')
        {
          $lcSubStr = substr($lcSubStr, 0, strlen($lcSubStr) - 1);
        }

        $lcMessage = ' < b>Version ' . $lcSubStr . ' released .</b > ';
      }

      $lcMessage = str_replace("\n", "<br />", $lcMessage);
      // Now get rid of any double line spaces.
      $lcMessage = str_replace("<br /><br />", "<br />", $lcMessage);

      $lcContent .= '<em>' . $lcDate . '</em> ' . ($llVersion ? ' - ' : '<br />') . $lcMessage;
      $lcContent .= '</li> ';
    }
    $lcContent .= '</ul></div> ';

    return ($lcContent);
  }

  //-------------------------------------------------------------------------------------------------
  private function getJSONChangeLog($tcName)
  {
    $lcContent = '';
    $lcURL = "";

    switch ($tcName)
    {
      case 'JEquity':
        $lcURL = "https://api.github.com/repos/efann/JEquity/commits";
        break;
    }

    if (strlen($lcURL) == 0)
    {
      return ($lcContent);
    }

    $loCurl = curl_init($lcURL);

    // Return into a variable. Otherwise, it just outputs to the screen.
    curl_setopt($loCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($loCurl, CURLOPT_HEADER, 0);
    // From https://developer.github.com/v3/#user-agent-required
    curl_setopt($loCurl, CURLOPT_USERAGENT, "efann-JEquity");

    $loResponse = curl_exec($loCurl);
    $laResults = json_decode($loResponse, true);
    curl_close($loCurl);

    $lcContent .= '<ul>';
    foreach ($laResults as $lnKey => $laSubArray)
    {
      $lcDate = substr($laResults[$lnKey]['commit']['author']['date'], 0, 10);
      $lcName = $laResults[$lnKey]['commit']['author']['name'];
      if ($lcName === 'Eddie Fann')
      {
        $lcName = 'Beowurks';
      }

      $lcMessage = str_replace("\n\n", "\n", $laResults[$lnKey]['commit']['message']);
      $lcMessage = str_replace("\n", "<br>", $lcMessage);

      $lcContent .= "<li><em>$lcMessage</em><br>";
      $lcContent .= "<b>Commit by</b>: $lcName<br>";
      $lcContent .= "<b>Commit on</b>: $lcDate</li>";
    }
    $lcContent .= ' </ul> ';

    return ($lcContent);
  }

  //-------------------------------------------------------------------------------------------------

}

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------


