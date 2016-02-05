# Walker, Texas Scraper.

This is a tool I built to make it easy to scrape content from websites that
are paginated. Think along the lines of webcomics. I mostly wanted to scrape
some to read offline on my phone since most Tumblr themes seem to blow chunks
in mobile browsers.

This is a self contained app that sits in its own directory, not something
you install to /usr or whatever. It is also not intended to be used from
a web SAPI but feel free to do what you want honeybadger don't care.

## Requirements

* PHP 7.0.0 or newer
* Composer

## Installation

1. Download and and extract the repo from the zip here on github or clone it
locally.

2. `$ composer install` from within the project directory.

## Usage

* `$ php bin\walker.php help`
