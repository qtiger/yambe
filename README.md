# yambe
A hierarchical breadcrumb extension for MediaWiki

## Yet Another MediaWiki Breadcrumb Extension
Given there are several breadcrumb extensions, why did I bother creating Yet Another MediaWiki? Breadcrumb Extension (YAMBE)?

* I haven't seen any extensions which work exactly how I want them to.
* It served as an exercise to help me learn about extending MediaWiki

## The YAMBE Approach
YAMBE is designed to allow a hierarchical breadcrumb where each page has a defined parent. This is not the wiki style and thus is probably not useful for a true wiki. However, where MediaWiki is being used more as a CMS than a wiki YAMBE comes into its own. Also, because of the way that YAMBE works it should be straightforward to implement YAMBE in parts of a wiki which need it and another breadcrumb approach in parts which do not.

## How YAMBE Works
YAMBE does what it does in two parts.

### On creating a new page
Whenever a new page is created beneath a page which already uses YAMBE a piece of markup is inserted at the start of the page to set up YAMBE and identify the page's parent.

If you want a breadcrumb, and you create the new page from its parent you don't need to do anything extra. The preloaded markup is already complete. If you create it from another leaf, or want later to change the parent you can edit the markup.

If you don't want a breadcrumb just delete the markup.

An example of the tag on a page called Kestrel parented from a page called Birds of Prey

```
<yambe:breadcrumb self="Lesser Kestrel">Birds_of_prey|Birds of Prey</yambe:breadcrumb>
```

The root page of the hierachy can be set up with a blank tag as it has no parent.

```
<yambe:breadcrumb />
```

### On displaying a page
The second part of what YAMBE does is a parser hook. When the page is displayed this reads the markup to locate the parent, and then loops through each parent until it gets to the top of the tree. There is a variable set up to stop the script in case the hierarchy is too deep and it automatically stops if it detects an error or reaches a page which has no parent.

## The Tag
```
<yambe:breadcrumb self="Self Text">Parent Page|Parent Text</yambe:breadcrumb>
```

* Self Text is the text to display at the end of the breadcrumb for the current page. Defaults to page name if blank
* Parent Page is the name of the parent page
* Parent Text is the text to display in the breadcrumb for the parent

## Notes on Stability
YAMBE works and has been tested and is stable on MediaWiki 1.13.1, 1.14.0, 1.15.0 1.24.1 and 1.25.2 on installations using the traditional wiki URLs and short URLs.

If you do encounter any problems please log them in the issues list and I will attempt to address them.

## Configuration
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

### $URLSplit

This is the most important of the four. It should be set to the element which divides your site URL from your wiki page name. In the following example $URLSplit should be set to "/thisbit/"

```
http://www.mysite.com/thisbit/MyPage
```

In the default wiki installation the URLSplit is /index.php/. Where you are using friendly URLs this is often changed to /wiki/

### $bcDelim
This is the string used to break up elements of the breadcrumb. It allows you to configure what the breadcrumb looks like. The default is to use the greater than chevron, but this can be changed to whatever you need.

### $maxCountBack
This is how many elements the breadcrumb should show. It stops YAMBE from looping back over too many pages. Once YAMBE reaches this limit it stops building the breadcrumb and shows the overflow prefix.

### $overflowPre
The overflow prefix that YAMBE puts in front of the breadcrumb when the number of pages in the breadcrumb exceeds $maxCountBack.

### $selfLink
Set this to true if the last item on the breadcrumb should be a link, otherwise false.

## CSS Styles
Yambe encloses the breadcrumb in a div with the id yambe. This can be used to configure the breadcrumb style in the CSS.

## The Code
Add the following to LocalSettings?.php

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
<yambe:breadcrumb>Page Title</yambe:breadcrumb>`
```

Tags are automatically produced when creating pages under pages that are already tagged, and the display text defaults to the page title.
