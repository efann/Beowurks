<?php

namespace Drupal\custom\Controller;

use DOMDocument;
use DOMXPath;
use Drupal;
use Drupal\taxonomy\Entity\Term;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use XMLWriter;
use function simplexml_load_file;

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
class AjaxController
{
  const TRASH_WIZARD = 1;
  const JEQUITY = 2;

  const JEQUITY_TITLE = 'JEquity';

  // The controller method receives these parameters as arguments.
  // The parameters are mapped to the arguments with the same name.
  // So in this case, the page method of the NodeController has one argument: $tcCustomCategory. There may be multiple parameters in a
  // route, but their names should be unique.
  //-------------------------------------------------------------------------------------------------
  public function getContent($tcType, $tnID)
  {
    $loResponse = new Response();

    $lcGeneratedContent = '<p>Unfortunately, nothing could be found.</p>';
    $lcContentType = 'text/html; utf-8';

    if (is_null($tnID))
    {
      $loResponse->headers->set('Content-Type', $lcContentType);
      $loResponse->setContent($lcGeneratedContent);

      return ($loResponse);
    }

    try
    {
      switch ($tcType)
      {
        // Returning node content.
        case 'node':
          $loNode = $this->getNode($tnID);
          // PHP try/catch doesn't catch NULL exceptions. Lovely. . . .
          if ($loNode == null)
          {
            throw new Exception("Unfortunately, the node of $tnID could not be found.");
          }
          $lcNodeType = $loNode->getType();

          switch ($lcNodeType)
          {
            case 'project':
              $lcContentType = 'application/xml';
              $lcGeneratedContent = $this->generateNodeProjectXML($loNode);
              break;

            case 'applicationparts':
              $lcContentType = 'text/html; utf-8';
              $lcGeneratedContent = $this->generateNodeApplicationPartsText($loNode);
              break;

            default;
              $lcContentType = 'text/html; utf-8';
              $lcGeneratedContent = $loNode->get('body')->value;
              break;
          }
          break;

        // Returning version information
        case 'version':
          switch ($tnID)
          {
            case self::TRASH_WIZARD:
              $lcVersion = $this->getTrashWizardVersion();

              if (isset($_GET['skipjavascript']))
              {
                $lcContentType = 'text/html; utf-8';
                $lcGeneratedContent = $lcVersion;
              }
              else
              {
                $lcContentType = 'application/x-javascript';
                $lcGeneratedContent = "document.write(\"" . $lcVersion . "\")";
              }
              break;

            case self::JEQUITY:
              $lcContentType = 'text/html; utf-8';
              $lcGeneratedContent = $this->getJEquityVersion();
              break;
          }
          break;

        // Returning JSON information from Book content
        case 'chaptertree':
          switch ($tnID)
          {
            case self::JEQUITY:
              $lcContentType = 'text/plain; utf-8';
              $lcGeneratedContent = $this->buildJSONBookChapters(self::JEQUITY_TITLE);
              break;
          }
          break;
      }
    }
    catch (Exception $loErr)
    {
      $lcContentType = 'text/html; utf-8';
      $lcGeneratedContent = "<h2 style='text-align: center;'>" . $loErr->getMessage() . "</h2><p style='text-align: center;'><a href='/'>Home</a></p>";
    }

    /*
       Awesome!!!!
       From https://drupal.stackexchange.com/questions/182022/how-to-output-from-custom-module-without-rest-of-theme
    */
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

    $loWriter = new XMLWriter();
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

  private function getNode($tnNodeID): ?Drupal\Core\Entity\EntityInterface
  {
    try
    {
      // From https://drupal.stackexchange.com/questions/225209/load-term-by-name
      $loNode = Drupal::entityTypeManager()
          ->getStorage('node')
          ->load($tnNodeID);
    }
    catch (Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException $loErr)
    {
      $loNode = null;
    }
    catch (Drupal\Component\Plugin\Exception\PluginNotFoundException $loErr)
    {
      $loNode = null;
    }

    return ($loNode);
  }


  //-------------------------------------------------------------------------------------------------
  private function getChangeLog($toNode)
  {
    $lcContent = '';
    $lcXMLFile = '';

    // From https://stackoverflow.com/questions/37122908/drupal-8-get-taxonomy-term-value-in-node
    // Geesh.
    $lnTermID = $toNode->get('field_application')->target_id;

    try
    {
      $loTerm = Term::load($lnTermID);
      $lcTermName = $loTerm->get('name')->value;
    }
    catch (Exception $loErr)
    {
      return ($loErr->getMessage());
    }

    if (($lcTermName === 'JEquity') || ($lcTermName === 'PolyJen') || ($lcTermName === 'BeoZip')
        || ($lcTermName === 'Trash Wizard') || ($lcTermName === 'BeoBasis'))
    {
      return ($this->getJSONChangeLog($lcTermName));
    }

    switch ($lcTermName)
    {
      case 'JAS Carousel':
        $lcXMLFile = "http://efann.users.sourceforge.net/svn/xml/jasca.xml";
        break;
    }

    if (strlen($lcXMLFile) == 0)
    {
      return ($lcContent);
    }

    $lcContent .= "<p>From the Apache Subversion log files. . . .</p>";

    $lcContent .= '<div><ul>';
    // Geesh, don't forget the \.
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

        $lcMessage = ' <b>Version ' . $lcSubStr . ' released.</b> ';
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
      case 'BeoBasis':
        $lcURL = "https://api.github.com/repos/efann/BeoBasis/commits";
        break;

      case 'JEquity':
        $lcURL = "https://api.github.com/repos/efann/JEquity/commits";
        break;

      case 'PolyJen':
        $lcURL = "https://api.github.com/repos/efann/PolyJen/commits";
        break;

      case 'BeoZip':
        $lcURL = "https://api.github.com/repos/efann/BeoZip/commits";
        break;

      case 'Trash Wizard':
        $lcURL = "https://api.github.com/repos/efann/TrashWizard/commits";
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

    $lcContent .= "<p>From the Git log files. . . .</p>";
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
  private function getTextStrippedOfTags($tcFileName)
  {
    /* Read an HTML file */
    $lcRawText = file_get_contents($tcFileName);

    /* Get the file's character encoding from a <meta> tag */
    preg_match('@<meta\s+http-equiv="Content-Type"\s+content="([\w/]+)(;\s+charset=([^\s"]+))?@i',
        $lcRawText, $laMatches);
    $lcEncoding = $laMatches[3];

    /* Convert to UTF-8 before doing anything else */
    $lcUTF8Text = iconv($lcEncoding, "utf-8", $lcRawText);

    /* Strip HTML tags and invisible text */
    $lcUTF8Text = $this->strip_html_tags($lcUTF8Text);

    /* Decode HTML entities */
    $lcUTF8Text = html_entity_decode($lcUTF8Text, ENT_QUOTES, "UTF-8");

    return ($lcUTF8Text);
  }

  //-------------------------------------------------------------------------------------------------

  /** From http://nadeausoftware.com/articles/2007/09/php_tip_how_strip_html_tags_web_page
   * Remove HTML tags, including invisible text such as style and
   * script code, and embedded objects.  Add line breaks around
   * block-level tags to prevent word joining after tag removal.
   */
  private function strip_html_tags($tcText)
  {
    $tcText = preg_replace(
        array(
          // Remove invisible content
            '@<head[^>]*?>.*?</head>@siu',
            '@<style[^>]*?>.*?</style>@siu',
            '@<script[^>]*?.*?</script>@siu',
            '@<object[^>]*?.*?</object>@siu',
            '@<embed[^>]*?.*?</embed>@siu',
            '@<applet[^>]*?.*?</applet>@siu',
            '@<noframes[^>]*?.*?</noframes>@siu',
            '@<noscript[^>]*?.*?</noscript>@siu',
            '@<noembed[^>]*?.*?</noembed>@siu',
          // Add line breaks before and after blocks
            '@</?((address)|(blockquote)|(center)|(del))@iu',
            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
            '@</?((table)|(th)|(td)|(caption))@iu',
            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
            '@</?((frameset)|(frame)|(iframe))@iu',
        ),
        array(
            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
            "\n\$0", "\n\$0",
        ),
        $tcText);

    return (strip_tags($tcText));
  }


//-------------------------------------------------------------------------------------------------
  private function getTrashWizardVersion()
  {
    $lcText = $this->getTextStrippedOfTags($_SERVER["DOCUMENT_ROOT"] . "/Software/NET/TrashWizard.WPF/publish.htm");
    $laWords = explode("\n", $lcText);

    $lcVersion = "";
    $llMarkerFound = false;
    foreach ($laWords as &$lcWord)
    {
      $lcWord = trim($lcWord);
      if (($llMarkerFound) && (strlen($lcWord) > 0))
      {
        $lcVersion = $lcWord;
        break;
      }

      if ($lcWord == "Version:")
      {
        $llMarkerFound = true;
      }
    }

    return ($lcVersion);
  }

  //-------------------------------------------------------------------------------------------------
  private function getJEquityVersion()
  {
    $lcMainURL = "https://sourceforge.net";
    $lcURL = $lcMainURL . "/projects/jequity/files/JEquity/";
    $lcMarker = 'jequity-';
    $lnIncrement = 1000;

    // Use the Curl extension to query.
    $loCurl = curl_init();
    $lnTimeout = 10;
    curl_setopt($loCurl, CURLOPT_URL, $lcURL);
    curl_setopt($loCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($loCurl, CURLOPT_CONNECTTIMEOUT, $lnTimeout);

    $loHTML = curl_exec($loCurl);
    curl_close($loCurl);

    // Create a DOM parser object
    $loDOM = new DOMDocument();

    // The @ before the method call suppresses any warnings that
    // loadHTML might throw because of invalid HTML in the page.
    @$loDOM->loadHTML($loHTML);

    $lcContent = '';

    $lcContent .= "<html lang='en'>\n";
    $lcContent .= "<head><title>Version Info</title></head>\n";
    $lcContent .= "<body>\n";
    $lcContent .= "<div id='jequity-version'>\n";

    // Iterate over all the table <a> tags
    $loXPath = new DOMXPath($loDOM);
    $loNodes = $loXPath->query('//table//a/@href');
    $lnMax = 0;
    $lcAppFolder = '';
    $lcVersion = '';
    foreach ($loNodes as $loHref)
    {
      $lcPath = $loHref->nodeValue;
      $lnPathLen = strlen($lcPath);
      // Contains the marker and the end of the path has a number for the last or second to last character.
      if ((stripos($lcPath, $lcMarker)) && (ctype_digit(substr($lcPath, $lnPathLen - 1, 1)) || (ctype_digit(substr($lcPath, $lnPathLen - 2, 1)))))
      {
        $lcContent .= $lcPath;
        $lcContent .= "<br />\n";
        $lcPartial = substr($lcPath, strlen($lcMarker));
        $lcNumber = preg_replace("/[^0-9.]/", "", $lcPartial);
        $lcContent .= $lcNumber;
        $lcContent .= "<br />\n";

        $laLines = explode('.', $lcNumber);
        $lnLines = count($laLines);
        $lnStart = 1;
        $lnNumber = 0;
        // Skip the last element: it will be the fraction.
        for ($i = $lnLines - 2; $i >= 0; $i--)
        {
          $lcFragment = $laLines[$i];
          $lnNumber += doubleval($lcFragment) * $lnStart;
          $lnStart *= $lnIncrement;
        }

        $lnNumber += doubleval('0.' . $laLines[$lnLines - 1]);

        $lcContent .= sprintf('%.8f', $lnNumber);
        $lcContent .= "<br />\n";

        if ($lnMax < $lnNumber)
        {
          $lnMax = $lnNumber;
          $lcVersion = $lcNumber;
          $lcAppFolder = $lcPath;
        }
      }
    }

    $lcContent .= "<br />\n";
    $lcContent .= "=================================\n";
    $lcContent .= "<br />\n";
    $lcContent .= '<div id="app_folder">' . $lcMainURL . $lcAppFolder . "</div>\n";
    $lcContent .= '<div id="app_version">' . $lcVersion . "</div>\n";
    $lcContent .= "=================================\n";

    $lcContent .= "</div>\n";
    $lcContent .= "</body>\n";
    $lcContent .= "</html>\n";

    return ($lcContent);
  }

  //-------------------------------------------------------------------------------------------------
  private function buildJSONBookChapters($tcProject)
  {
    $loBookManger = Drupal::service('book.manager');

    $laBooks = $loBookManger->getAllBooks();
    $lnBookID = null;
    foreach ($laBooks as $loBook)
    {
      if (strpos($loBook['title'], $tcProject) !== false)
      {
        $lnBookID = $loBook['nid'] + 0;
        break;
      }
    }

    if ($lnBookID)
    {
      $laTOC = $loBookManger->getTableOfContents($lnBookID, 20);

      $laKeys = array_keys($laTOC);
      $lnTrack = 0;

      $laJSON = $this->buildTree($laTOC, $laKeys, $lnTrack, 0);
    }

    return (json_encode($laJSON));
  }

//-------------------------------------------------------------------------------------------------
  private function buildTree($taTOC, $taKeys, &$tnTrack, $tnLevel)
  {
    $laResult = array();
    $lnCount = count($taTOC);

    while ($tnTrack < $lnCount)
    {
      $lnID = $taKeys[$tnTrack];
      $lcTitle = $taTOC[$lnID];

      $lcTitleStrip = ltrim($lcTitle, " -");
      $lnPos = strpos($lcTitle, $lcTitleStrip);
      $lcDashes = trim(substr($lcTitle, 0, $lnPos));
      $lnDepth = strlen($lcDashes);

      $laItem = ["name" => $lcTitleStrip, "id" => $lnID];

      // Next level indicated.
      if ($tnLevel < $lnDepth)
      {
        // Add to the previous element.
        $lnResultCount = count($laResult);
        if ($lnResultCount > 0)
        {
          $laResult[$lnResultCount - 1]['children'] = $this->buildTree($taTOC, $taKeys, $tnTrack, $lnDepth);
        }
        else
        {
          // This really should never happen. Only if the first element has some dashes for some reason.
          $laItem['children'] = $this->buildTree($taTOC, $taKeys, $tnTrack, $lnDepth);
          $laResult[] = $laItem;
          $tnTrack++;
        }
      }
      else if ($tnLevel == $lnDepth)
      {
        $laResult[] = $laItem;
        $tnTrack++;
      }
      else if ($tnLevel > $lnDepth)
      {
        break;
      }
    }

    if (count($laResult) > 0)
    {
      return ($laResult);
    }

    return (null);
  }
  //-------------------------------------------------------------------------------------------------

}

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------


