# ProcessImageMinimize

Module for compressing images on ProcessWire using the "minimize.pw" web service.

## Installation

Install ProcessImageMinimize like every other ProcessWire module. 

After installation, enter your license key in the modules settings and click save. 


### Changing your License Key

In order to change your license key, click on the cog icon next to the modules names in the "Modules" section of your ProcessWire admin backend.


In the following screen, replace the former license code with your new license key.

If you don't have a license key, you may receive a free license key at [https://minimize.pw/free/](https://minimize.pw/free/).

## Usage

To embed a minimized image into your web page, simply call the method mz() or minimize() on your Pageimage-object. Please note, that is has to be a single Pageimage.


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

## Best practice
Only use the module before you output the image for best results. Example:

```
//Bad
$image->mz()->size(300,400)->url;
//Good
$image->size(300,400)->mz()->url;
```
Set ProcessWire to save images with JPEG 99 as a quality setting. Example:

```
//Create a thumbnail and minimize it
$options = array(
  'quality' => 99,
);  
$thumbnail = $image->size(100, 100, $options)->mz(); 
```



## Handling Errors and limitations

ProcessImageMinimize is able to handle errors without impact on the rest of your website.

If ProcessImageMinimize is unable to fetch a minimized image, the full-size, locally stored image will be used instead.

ProcessImageMinimize will cache all minimized images locally on your server. We won't send additional requests, once an image has been compressed.

### Limits
Beside your monthly limits (according to your license key) there are some limitations:
- Images over 10M won't be uploaded. It takes too long to process them.
- Your PHP max_execution time might be over, if you have too many images to compress. Simply refresh the page to compress to try it again and minimize all remaining images.
- Especially large (> 5MB) PNG files can take some time to compress.



## Server Requirements

For a properly working configuration, it is necessary that your server / ProcessWire configurations includes these settings:

* ProcessWire 2.3.0 or higher
* Fast connection (as it uploads the images to our service)
