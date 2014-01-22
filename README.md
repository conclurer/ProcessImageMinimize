# ProcessImageMinimize

Module for compressing images on ProcessWire using the "minimize.pw" web service.

## Installation

In order to install ProcessImageMinimize, copy the .module file in the directory "/site/modules/".

Login to your ProcessWire admin backend. In the "Modules" section, click on "Check for New Modules" on the top right. 

After the page has reloaded, scroll down to the "Process" section and click on the "Install" button next to "ProcessImageMinimize".

Enter your license key, which was given to you, and click save. You are now set up.

If you don't have a license key, you may receive a free license key at [https://minimize.pw/free/](https://minimize.pw/free/).

### Changing your License Key

If you need to change your license key, click on the cog icon in the "Modules" section of your ProcessWire admin backend.

In the following screen, replace the former license code with your new license key.

## Usage

To embed a minimized image into your web page, simply call the method mz() or minimize() on your Pageimage-object.


```
$img = $page->image;
$img->minimize();
```

Or put in context:


```
$img = $page->image;
echo "<img alt='A minimized image' src='{$img->minimize()->url}'>";
```

You can also use it combined with other Pageimage methods:

```
$image->size(300,400)->mz()->url;
```

Is it really that simple? Yep.

## Handling Errors

ProcessImageMinimize is able to handle errors without impact on the rest of your website.

If ProcessImageMinimize is unable to fetch a minimized image, the full-size, locally stored image will be used instead.

ProcessImageMinimize will cache all minimized images locally on your server. We won't send additional requests, once an image has been compressed.

## Server Requirements

For a properly working configuration, it is necessary that your server / ProcessWire configurations includes these settings:

* ProcessWire 2.3.0 or higher