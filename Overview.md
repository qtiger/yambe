# Yet Another MediaWiki Breadcrumb Extension #
Given there are several breadcrumb extensions, why did I bother creating Yet Another MediaWiki Breadcrumb Extension (YAMBE)?

  1. I haven't seen any extensions which work exactly how I want them to.
  1. It served as an exercise to help me learn about extending MediaWiki

# The YAMBE Approach #
YAMBE is designed to allow a hierarchical breadcrumb where each page has a defined parent. This is not the wiki style and thus is probably not useful for a true wiki. However, where MediaWiki is being used more as a CMS than a wiki YAMBE comes into its own. Also, because of the way that YAMBE works it should be straightforward to implement YAMBE in parts of a wiki which need it and another breadcrumb approach in parts which do not.

# How YAMBE Works #
YAMBE does what it does in two parts.

## On creating a new page ##
Whenever a new page is created beneath a page which already uses YAMBE a piece of markup is inserted at the start of the page to set up YAMBE and identify the page's parent.

If you want a breadcrumb, and you create the new page from its parent you don't need to do anything extra. The preloaded markup is already complete. If you create it from another leaf, or want later to change the parent you can edit the markup.

If you don't want a breadcrumb just delete the markup.

By way of example, here is the markup which is at the head of this page
```
<yambe:breadcrumb>MediaWiki|MediaWiki</yambe:breadcrumb>
```
The root page of the hierachy can be set up with a blank tag as it has no parent.

```
<yambe:breadcrumb />
```

## On displaying a page ##
The second part of what YAMBE does is a parser hook. When the page is displayed this reads the markup to locate the parent, and then loops through each parent until it gets to the top of the tree. There is a variable set up to stop the script in case the hierarchy is too deep and it automatically stops if it detects an error or reaches a page which has no parent.

# The Tag #
```
<yambe:breadcrumb self="Self Text">Parent Page|Parent Text</yambe:breadcrumb>
```

  * Self Text is the text to display at the end of the breadcrumb for the current page. Defaults to page name if blank
  * Parent Page is the name of the parent page
  * Parent Text is the text to display in the breadcrumb for the parent

# How existing breadcrumb extensions work #
There are a number of approaches to building a MediaWiki Breadcrumb.

## Track User Path ##
The first approach tracks the users path through the wiki showing recently visited pages. This is a very nice solution for a wiki, but it differs from how a breadcrumb works on a more conventional website. For example if I have a page called Leaf with a breadcrumb which looks like:

> Trunk > Branch > Twig > Leaf

and I follow a link from it to a page called Fruit which is also parented off the Twig page a conventional breadcrumb would now read:

> Trunk > Branch > Twig > Fruit

In contrast the wiki type breadcrumb would read:

> Trunk > Branch > Twig > Leaf > Fruit.

**Extensions Using this Approach**
  * [Breadcrumbs](http://www.mediawiki.org/wiki/Extension:BreadCrumbs)
  * [BreadCrumbs (Kimon)](http://www.mediawiki.org/wiki/Extension:BreadCrumbs_(Kimon))
  * [GISWiki kwBreadCrumbs](http://www.mediawiki.org/wiki/Extension:GISWiki_kwBreadCrumbs)

## URL level-based breadcrumb ##
The second type of breadcrumb parses the URI to turn the directory structure of the URL into the breadcrumb. Fine if that is what you want - but I aim to not build the site hierarchy into the URL as this can result in very long URLs and also means that you have to change the URL if you change the site structure which is a bad idea.

**Extensions Using this Approach**
  * [BrettCrumbs](http://www.mediawiki.org/wiki/Extension:BrettCrumbs)

## Category Based Breadcrumb ##
Category-based breadcrumbs build the breadcrumb from the page name and the containing category.

**Extensions Using this Approach**
  * [CategoryBreadcrumb](http://www.mediawiki.org/wiki/Extension:CategoryBreadcrumb)

## Markup-based Breadcrumb ##
There is another kind which takes some kind of markup and parses it into a breadcrumb. The thing is the markup you need to use has to be typed in manually and is nearly as long-winded as writing the breadcrumb manually anyway. So there is not much saving.