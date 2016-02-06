# Walker, Texas Scraper.

This is a tool I built to make it easy to scrape content from websites that
are paginated. Think along the lines of webcomics. I mostly wanted to scrape
some to read offline on my phone since most Tumblr themes seem to blow chunks
in mobile browsers.

This is a self contained app that sits in its own directory, not something
you install to /usr or whatever. It is also not intended to be used from
a web SAPI but feel free to do what you want honeybadger don't care.

# Requirements

* PHP 7.0.0 or newer
* Composer
* An understanding of DOM Query strings. (aka CSS Selectors, jQuery(...), document.querySelector(...))

# Installation

1. Download and and extract the repo from the zip here on github or clone it
locally.

2. `$ composer install` from within the project directory.

# Usage

* `$ php bin\walker.php` will show all the help infos.

# Example

OK. Let's scrape XKCD.

## Step 1) Create a new project.

	bob@RARITY [C:\Users\bob\Projects\Walker]$ php bin\walker.php create xkcd
	writing default settings to C:\Users\bob\Projects\Walker\conf\xkcd.json

## Step 2) Fill in the rest of that JSON file.

The comic series starts on `http://xkcd.com/1/` and is paginated which is
what this was designed to deal with.

As of the time of this writing, the proper Query to find the image to save
is `#comic img` with its `src` attribute.

The proper Query to find the next page URL is `.comicNav a[rel=next]` with
with its `href` attribute.

I also decided I want to change the final filename from the original on-site
names to padded sequential files such as `xkcd-0001.png`.

So `xkcd.json` is going to look like this after editing QueryDownload,
QueryDownloadAttr, QueryNext, QueryNextAttr, SaveFile, StartURL, and
PadFileNums. I am also going to set Verbose to true so we can see all the
things it is doing.

	{
		"Delay": 3,
		"LastIter": 1,
		"LastURL": "",
		"QueryDownload": "#comic img",
		"QueryDownloadAttr": "src",
		"QueryNext": ".comicNav a[rel=next]",
		"QueryNextAttr": "href",
		"SaveDir": "C:\\Users\\bob\\Projects\\Walker\\save\\%CONFIGNAME%",
		"SaveFile": "xkcd-%FILENUM%.%EXT%",
		"StartURL": "http://xkcd.com/1/",
		"TransformDownload": [],
		"TransformNext": [],
		"UserAgent": "",
		"Verbose": true,
		"PadFileNums": 4
	}

## Step 3) Walk it.

Execute the project...

	bob@RARITY [C:\Users\bob\Projects\Walker]$ php bin\walker.php walk xkcd
	>> Save Location: C:\Users\bob\Projects\Walker\save\xkcd
	>> Fetching http://xkcd.com/1/
	>> Searching HTML for #comic img
	>> Downloading http://imgs.xkcd.com/comics/barrel_cropped_(1).jpg...
	>> Saved C:\Users\bob\Projects\Walker\save\xkcd\xkcd-0001.jpg (24848)
	>> Searching HTML for .comicNav a[rel=next]
	>> Waiting 3sec...
	>> Fetching http://xkcd.com/2/
	>> Searching HTML for #comic img
	>> Downloading http://imgs.xkcd.com/comics/tree_cropped_(1).jpg...
	>> Saved C:\Users\bob\Projects\Walker\save\xkcd\xkcd-0002.jpg (59052)
	>> Searching HTML for .comicNav a[rel=next]
	>> Waiting 3sec...
	>> Fetching http://xkcd.com/3/
	>> Searching HTML for #comic img
	>> Downloading http://imgs.xkcd.com/comics/island_color.jpg...
	>> Saved C:\Users\bob\Projects\Walker\save\xkcd\xkcd-0003.jpg (88284)
	>> Searching HTML for .comicNav a[rel=next]
	>> Waiting 3sec...

zzz... zzz... elsewhere...

	bob@RARITY [C:\Users\bob\Projects\Walker]$ ls -l save\xkcd
	-rw-r--r--    1 bob      Administ    24848 Feb  6 01:06 xkcd-0001.jpg
	-rw-r--r--    1 bob      Administ    59052 Feb  6 01:06 xkcd-0002.jpg
	-rw-r--r--    1 bob      Administ    88284 Feb  6 01:06 xkcd-0003.jpg
	-rw-r--r--    1 bob      Administ    66490 Feb  6 01:06 xkcd-0004.jpg
	-rw-r--r--    1 bob      Administ    39876 Feb  6 01:06 xkcd-0005.jpg

If interupted it will continue where it left off. This also includes being able
to restart the task at a later date to consume updates that may have been added
since you last did it.

## URL Transforms

You can write classes which are able to transform URLs before Walker commits
to actually using them. The best example I can think of is if you are scraping
Tumblr you might want to make sure you are getting the largest version of the
image they have (currently the _1280 version).

You can do this by writing your own class which implements the
`Walker\Proto\TransformURL` interface (a whole 2 static methods). See the
[TransformURL interface file](core/Walker/Proto/TransformURL.php) for
descriptions of what needs to be done there. Also check out the
[TumblrLargestImage class file](core/Walker/Transform/TumblrLargestImage.php)
to see a completed example.

Once you have created your transform classes check out your project's json file
again. You should see two properties one called `TransformDownload` and another
called `TransformNext` and you should notice that these properties are arrays.
List the FQCN of the transform classes you want to use in this project. If you
are specifying multiple classes then be sure to put them in the order you need
and/or that they have working `WillHandleTransform` methods to ensure that they
yield to other transforms if they do not apply to the current URL. If all the
transforms in the list report true from `WillHandleTransform` then all of them
will transform it in the order they are configured.
