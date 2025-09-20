Transliteration - Laravel 4, 5, 6, 7, 8, 9 & 10 text cleaning Package
=====================

Transliteration provides one-way string transliteration (romanization) and cleans text by replacing unwanted characters.

> ...it takes Unicode text and tries to represent it in US-ASCII characters (universally displayable, unaccented characters) by attempting to transliterate the pronunciation expressed by the text in some other writing system to Roman letters.

This adapts the module from https://drupal.org/project/transliteration for use with Laravel.

## Features

* Transliterate text to US-ASCII characters

## Why use this?

* I use this for filename on uploads.   See this image:


![](https://drupal.org/files/styles/grid-3/public/images/translit_0.png?itok=CwKAPBtB)*

* I use this with [Andrew Elkins's Cabinet](https://github.com/andrewelkins/cabinet)

## Quick start

Install the package via Composer:

    composer require that0n3guy/transliteration

Depending on your version of Laravel, you should install a different version of the package.

| Laravel Version | Package Version |
|:---------------:|:---------------:|
|      10.0       |      ^2.0       |
|       9.0       |      ^2.0       |
|       8.0       |      ^2.0       |
|       7.0       |      ^2.0       |
|       6.0       |      ^2.0       |
|       5.0       |      ^2.0       |
|       4.0       |      ^1.0       |

In your `config/app.php` add `'That0n3guy\Transliteration\TransliterationServiceProvider'` to the end of the `$providers` array

```php
'providers' => array(

    'Illuminate\Foundation\Providers\ArtisanServiceProvider',
    'Illuminate\Auth\AuthServiceProvider',
    ...
    'That0n3guy\Transliteration\TransliterationServiceProvider',

),
```

### How to use

Simply call the Transliteration class:

```php
Route::get('/test', function(){
  echo Transliteration::clean_filename('test& ® is true');
});
```

This would return `test_r_is_true`

### Set a language

You can optionally set a [Optional ISO 639 language code](http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes).  Do it like so:

```php
Route::get('/test', function(){
  echo Transliteration::clean_filename('testing Japanese 日本語', 'jpn');
});
```

This would return `testing_Japanese_Ri_Ben_Yu_`.


## How to use to rename file uploads (sanitize them)
This is an old example, but still relevant.   It uses [Cabinet](https://github.com/andrewelkins/cabinet) which I don't really recommend using anymore since there are better options.

Add something like:

```php
// if using transliteration
if (class_exists( 'That0n3guy\Transliteration\Transliteration' )) {
  $file->fileSystemName = Transliteration::clean_filename($file->getClientOriginalName());  // You can see I am cleaning the filename
}
```

To your Upload controller.  For example.  I added it to my UploadController.php and my store() method looks like so:

```php
/**
 * Stores new upload
 *
 */
public function store()
{
    $file = Input::file('file');

    // if using transliteration
    if (class_exists( 'That0n3guy\Transliteration\Transliteration' )) {
      $file->fileSystemName = Transliteration::clean_filename($file->getClientOriginalName());
    }

    $upload = new Upload;

    try {
        $upload->process($file);
    } catch(Exception $exception){
        // Something went wrong. Log it.
        Log::error($exception);
        $error = array(
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'error' => $exception->getMessage(),
        );
        // Return error
        return Response::json($error, 400);
    }

    // If it now has an id, it should have been successful.
    if ( $upload->id ) {
      ...
```

# Example how to use with octobercms

* Create a plugin, for this example I'll call it `that0n3guy.drivers`.  You can see documentation here: https://octobercms.com/docs/console/scaffolding#scaffold-create-plugin

* Add a bootPackages method to your `Plugin.php` as per the instructions here (copy/paste it straight from that page):
https://luketowers.ca/blog/how-to-use-laravel-packages-in-october-cms-plugins/

* Add `that0n3guy/transliteration` to your plugins composer.json file:
```
    "require": {
        "that0n3guy/transliteration": "^2.0"
    }
```

Create a config file in your plugins config folder (create this folder) just like https://luketowers.ca/blog/how-to-use-laravel-packages-in-october-cms-plugins/.   File structure example:

```
that0n3guy
    drivers
        config
            config.php
        Plugin.php
```

The config file should contain:
```
<?php
use Illuminate\Support\Facades\Config;

$config = [
    // This contains the Laravel Packages that you want this plugin to utilize listed under their package identifiers
    'packages' => [
        'that0n3guy/transliteration'  => [
            'providers' => [
                '\That0n3guy\Transliteration\TransliterationServiceProvider',
            ],
            'aliases' => [
                'Transliteration' => '\That0n3guy\Transliteration\Facades\Transliteration',
            ],
        ],

    ],
];

return $config;
```

Now you can use transliteration facade anywhere in your octobercms php code (like in the docs above).
