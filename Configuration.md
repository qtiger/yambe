# Notes on Stability #
YAMBE works and has been tested and is stable on MediaWiki 1.13.1, 1.14.0 and 1.15.0 on installations using the traditional wiki URLs and short URLs.

If you do encounter any problems please log them in the issues list and I will attempt to address them.

# Configuration #
There are five variables in the YAMBE code which can be set to configure the way that YAMBE behaves on your system. These are shown below:
```
//Some global config declarations
// where to split the URL to find page name. Usually either /index.php/ or /wiki/
$URLSplit = "/index.php/"; 
$bcDelim = " &gt; "; // Character to mark the stages of the breadcrumb?
$maxCountBack = 5; // Maximum number of links in breadcrumb 
$overflowPre = "[...]"; // Prefix if breadcrumb is longer than $maxCountBack links
$selfLink = false; // Set true if last page in breadcrumb should be a link
```

## $URLSplit ##
This is the most important of the four. It should be set to the element which divides your site URL from your wiki page name. In the following example $URLSplit should be set to "/thisbit/"

```
http://www.mysite.com/thisbit/MyPage
```

In the default wiki installation the URLSplit is /index.php/. Where you are using friendly URLs this is often changed to /wiki/

## $bcDelim ##
This is the string used to break up elements of the breadcrumb. It allows you to configure what the breadcrumb looks like. The default is to use the greater than chevron, but this can be changed to whatever you need.

## $maxCountBack ##
This is how many elements the breadcrumb should show. It stops YAMBE from looping back over too many pages. Once YAMBE reaches this limit it stops building the breadcrumb and shows the overflow prefix.

## $overflowPre ##
The overflow prefix that YAMBE puts in front of the breadcrumb when the number of pages in the breadcrumb exceeds $maxCountBack.

## $selfLink ##
Set this to true if the last item on the breadcrumb should be a link, otherwise false.

## CSS Styles ##
Yambe encloses the breadcrumb in a div with the id yambe. This can be used to configure the breadcrumb style in the CSS.

# The Code #
Add  the following to LocalSettings.php
```
require_once ('extensions/yambe.php');
```
Save yambe.php (from the source list above) in MediaWiki extensions directory and edit the global config declarations to fit your wiki.

To kick things off enter a yambe tag in the root page of your hierarchy. Because this is the root page it has no parent so the data element of the tag is blank
```
<yambe:breadcrumb />
```

The normal form of the yambe tags is:
```
<yambe:breadcrumb>Page Title|Display Text for Breadcrumb</yambe:breadcrumb>
```

As of 0.2.1 the Display Text for Breadcrumb can be omitted (Thanks to Chris Cauthen for suggesting this) eg

```
<yambe:breadcrumb>Page Title</yambe:breadcrumb>
```

Tags are automatically produced when creating pages under pages that are already tagged, and the display text defaults to the page title.