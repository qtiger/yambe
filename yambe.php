<?php
/**
 * Yet Another MediaWiki Breadcrumb Extension (Yambe)
 * For documentation, please see https://github.com/qtiger/yambe/blob/master/README.md
 *
 * @ingroup Extensions
 * @author Ian Coleman
 * @version 0.2.5
 */
define('YAMBE_VERSION','0.2.5, 2017-06-08');
 
//Extension credits that show up on Special:Version
$wgExtensionCredits['parserhook'][] = array(
 'name' => 'YAMBE Hierarchical Breadcrumb',
 'url' => 'https://github.com/qtiger/yambe',
 'version' => YAMBE_VERSION,
 'author' => 'Ian Coleman',
 'description' => 'Parser hook to show Breadcumb on MediaWiki.'
);
 
//Some global config declarations
// where to split the URL to find page name. Usually either /index.php/ or /wiki/
$URLSplit = "/index.php/"; 
$bcDelim = " &gt; "; // Character to mark the stages of the breadcrumb?
$maxCountBack = 5; // Maximum number of links in breadcrumb 
$overflowPre = "[...]"; // Prefix if breadcrumb is longer than $maxCountBack links
$selfLink = false;
 
//Set up the hooks
$wgHooks['EditFormPreloadText'][] = array('yambeSetParent');
 
// Avoid unstubbing $parser on setHook() too early on modern (1.12+) MW versions, as 
// per r35980
if ( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
 $wgHooks['ParserFirstCallInit'][] = 'yambeInit';
} else {
 $wgExtensionFunctions[] = 'yambeInit';
}
 
function yambeInit($parser){
 $parser->setHook ( 'yambe:breadcrumb', 'yambeBreadcrumb' );
 return true;
}
 
// Function to build the breadcrumb
function yambeBreadcrumb ($data, $args, $parser)
{
global $bcDelim, $maxCountBack, $overflowPre, $selfLink;

$parser->disableCache();
$pgTitle = $parser->getTitle();
 
// Grab the self argument if it exists
if (isset($args['self'])) $yambeSelf = $args['self'];
else $yambeSelf = $pgTitle->getText();
 
// Breadcrumb is built in reverse and ends with this rather gratuitous self-link
if ($selfLink) $breadcrumb = linkFromText($pgTitle->getText(),$yambeSelf,$pgTitle->getNamespace());
else $breadcrumb = $yambeSelf;
 
$cur = str_replace(" ", "_", ($pgTitle->getText()));
 
// Store the current link details to prevent circular references
if ($pgTitle->getNsText() == "Main") $bcList[$cur] = "";
else $bcList[$cur] = $pgTitle->getNsText();
 
if ($data!="")
  {
  $cont = true;
  $count = 2; // because by first check, breadcrumb will have 2 elements!
 
  do
    {
    // Grab the parent information from the tag
    $parent = explode("|",$data);
    $page   = splitName(trim($parent[0]));
 
    // Allow for use of only the parent page, no display text
    if(count($parent) < 2) $parent[1] = "";
 
    // Check link not already in stored in list to prevent circular references
    if (array_key_exists($page['title'],$bcList))
      if ($bcList[$page['title']] == $page['namespace']) $cont = false;
 
    if ($cont) 
      {
      // Store the current link details to prevent circular references
      $bcList[str_replace(" ", "_",$page['title'])] = $page['namespace'];
 
      // make a url from the parent
      $url = yambeMakeURL($page, trim($parent[1]));
 
      // And if valid add to the front of the breadcrumb
      if ($url != "")
        {
        $breadcrumb = $url . $bcDelim . $breadcrumb;
 
         // Get the next parent from the database
        $par = getTagFromParent($page['title'], $page['namespaceid']);
 
        // Check to see if we've tracked back too far
        if ($count >= $maxCountBack)
          {
          $cont = false;
          if ($par['data'] != "")
            $breadcrumb = $overflowPre . $bcDelim . $breadcrumb;
          }
        else
          {
          $page['title'] = str_replace(" ", "_", $page['title']); 
 
          $data = $par['data'];
          if ($data == "")
            $cont = false;
          }
        }
      }
    $count++;
    }
    while ($cont); // Loop back to get next parent
  }
 
// Encapsulate the final breadcrumb in its div and send it back to the parser
return "<div id='yambe' class='noprint'>$breadcrumb</div>\n";
}
 
// Function to get namespace id from name
function getNamespaceID($namespace)
{
if ($namespace == "") return 0;
else
  {
  $ns = new MWNamespace();
  return $ns->getCanonicalIndex(trim(strtolower($namespace)));
  }
}
 
// Function to build a url from text
function linkFromText($page, $displayText, $nsID=0)
{
global $wgVersion;

$title = Title::newFromText (trim($page), $nsID);
$oldVersion = version_compare( $wgVersion, '1.28', '<=' );

if ( $oldVersion ) {
  global $wgUser;
   
  $skin = $wgUser->getSkin();   
  if (!is_null($title)) return $skin->makeKnownLinkObj($title, $displayText, "");
  } else {
    if (!is_null($title)) return MediaWiki\MediaWikiServices::getInstance()->getLinkRenderer()->makeKnownLink($title, $displayText);
  }

return "";
}
 
function pageExists($page, $nsID=0)
{
$page = str_replace(" ", "_", $page);
 
$dbr = wfGetDB( DB_SLAVE );
 
if ($dbr->selectField( 'page', 'page_id', 
    array("page_title" => $page, "page_namespace" => $nsID), 
   __METHOD__ ) == "") return false;
else return true;
}
 
// Function checks that the parent page exists and if so builds a link to it
function yambeMakeURL($page, $display)
{
if (pageExists($page['title'],$page['namespaceid']))
  return linkFromText($page['title'],$display,$page['namespaceid']);
else return "";
}
 
// Get the parents tag
function getTagFromParent($pgName, $ns = 0)
{
$par['data']= "";
$par['exists']= false;
$par['self']="";
 
$dbr = wfGetDB( DB_SLAVE );
 
$pgName = str_replace(" ", "_", $pgName);
 
$res = $dbr->select(array ("revision", "text", "page",),
                    "old_text", 
                     array( "page_title" =>$pgName, "page_namespace" => $ns),
                     __METHOD__, 
                     array ("ORDER BY"=>"rev_id desc limit 1"),
                     array("text" => array ("LEFT JOIN", "old_id=rev_text_id" ), 
                           "revision" => array( 'LEFT JOIN', 'rev_page=page_id')));
 
// Check to see if the query worked
if ($res)
  {
  if ($dbr->numRows( $res) > 0)
    {
    // We've got the parent text. Now locate it's parent tag
    $row = $dbr->fetchRow( $res );
    $text = $row["old_text"];
 
    $dbr->freeResult( $res ); 
    $par = yambeUnpackTag ($text);
    }
  }
return $par;
}
 
function splitName($in)
{
if (substr_count($in,":"))
  {
  // Parent name includes Namespace - grab the page name out for display element
  $fullName  = explode (":", $in);
  $page['title']     = str_replace(" ", "_", $fullName[1]);
  $page['namespace'] = $fullName[0];
  $page['namespaceid'] = getNamespaceID($fullName[0]);
  }
else
  {
  $page['title']     = str_replace(" ", "_", $in);
  $page['namespace'] = "";
  $page['namespaceid'] = 0;
  }
return $page;
}
 
// Set up the breadcrumb link in a new page
function yambeSetParent(&$textbox, &$title)
{
global $URLSplit;
 
if ($URLSplit == "/") {
  $url = parse_url($_SERVER['HTTP_REFERER']);
  $parent = substr($url["path"],1);
}
else {
  $arr = explode($URLSplit,$_SERVER['HTTP_REFERER']);
  $parent = $arr[1];
}
 
// If the code breaks on this line check declaration of $URLSplit on line 23 matches your wiki 
$page = splitName($parent); 
$par = getTagFromParent($page['title'],$page['namespaceid']);
 
if ($par['exists'])
  {  
  if ($par['self'] != "") $display = $par['self'];
  else $display = str_replace("_", " ", $page['title']);
 
  $textbox = "<yambe:breadcrumb>$parent|$display</yambe:breadcrumb>";
  }
 
return true;
}
 
// Bit of a kludge to get data and arguments from a yambe tag
function yambeUnpackTag ($text)
{
$ret['exists']=false;
$ret['data']= "";
$ret['self']= "";
$end = false;
 
// Find the opening tag in the supplied text
$start = strpos($text,"<yambe:breadcrumb");
 
// Find the end of the tag
// Grab it and convert <yambe_breadcrumb> because simplexml doesn't like <yambe:breadcrumb>
if ($start !== false)
  {
  $end = strpos($text,"</yambe:breadcrumb>", $start);
  if ($end !== false) $tag = substr($text, $start, $end-$start+19);
  else
    {
    $end = strpos($text,"/>", $start);
    if ($end !== false) $tag = substr($text, $start, $end-$start+2);
    }
 
  if ($end !== false)
    {
    $tag = str_replace("yambe:breadcrumb", "yambe_breadcrumb", $tag);
 
    // encapsulate in standalone XML doc
    $xmlstr = "<?xml version='1.0' standalone='yes'?><root>$tag</root>";
 
    $xml = new SimpleXMLElement($xmlstr);
 
    // And read the data out of it
    $ret['self'] = $xml->yambe_breadcrumb['self'];
    $ret['data'] = $xml->yambe_breadcrumb[0];
    $ret['exists'] = true;
    }
  }
 
return $ret;
}
?>
