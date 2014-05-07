# Introduction
**Minimize.pw** is an online service for compressing images without visual effects. It is able to reduce the file sizes of supported image types by up to 80%.Minimize.pw can handle both JPEG and PNG files.The online service is integrated into the ProcessWire CMF via the **ProcessImageMinimize** module.Beside the fee-based plans there is also a [free usage plan](https://minimize.pw/free) available. We encourage all developers to try out minimize.pw for free under the premise of fair-use. If you use minimize.pw for a commercial project, you shall buy additional volume instead of requesting additional free license keys.
# Getting started
To embed a minimized image into your web page, simply call the method `mz()` or `minimize()` on your Pageimage-object. Please note, that is has to be a single Pageimage (not a WireArray).

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

You can also minimize a cropped image by Apeisa's CropImage module by calling `getMinimizedThumb($type)` instead of `getThumb($type)`.

In context:

```
$url = $image->getMinimizedThumb('small');
echo "<img alt='A minimized thumbnail' src='$url'>";
```

**Additional ressources:**

- [Learn more about minimize.pw](https://minimize.pw)
- [Play minimize.pw - the game](https://demo.minimize.pw/game/)
- [Get a free license key](https://minimize.pw/free)

# In more Detail
## Scheme of the Web Service
The web service minimize.pw is made out of three parts:- The visual web site of the service, that is used as an accounting platform- An queuing endpoint which also serves as the main API node- One or more singular working services, that are going to be turned on or off dynamically The services themselves are written in Ruby. Furthermore we use a SQL server for master data management and Redis for the queuing service.For image compression we use the open-source tools PNGQuant and JPGoptim.Transmitting the user’s unique license key will authenticate him to the system. The license key is static and won’t be changed.### API Nodes**/queue/push**
pushes n image URLs to the queue returning references for each image requiring a license key
**/queue/state** returns the current state for each reference passed by### API Call SchemeEvery image URL pushed to the API node is added to the queue. At the time, one image URL is added, the user’s account will be debited with one credit.
Based on the principle “first in, last out”, the queue pushes the individual tasks to idle working servers.
The working servers fetch the image from the given URL and compressing it, if possible.
After having finished the individual tasks, the working servers are storing the compressed images on their built-in webserver with a cryptic URL and report the state of the task to the API node.
The client ProcessWire instance pulls the compressed image from each working server.
After seven days, the compressed images are going to be automatically deleted from each working server’s disk, whether or not the client ProcessWire instance has pulled the image or not.
## ProcessImageMinimize Module
The ProcessImageMinimize module is the connection of one ProcessWire site to the minimize.pw web service. It handles all HTTP connections to the minimize.pw servers and manages the local image files.
It is also engineered in a fail-save way, so one ProcessWire site’s images won’t prick if the service is unavailable.After one image file has been minimized by minimize.pw, the minimized image file is stored as *.mz.jpg or *.mz.png file on your server, after it has been pulled by ProcessImageMinimize.**Example:**

File | File Name--- | ---Original image | summer-2014.jpgMinimized image | summer-2014.mz.jpgThe ProcessImageMinimize module is only functional if you enter a license key in its configuration page in the module section in the ProcessWire admin panel.
###Standard API Methods
Developers can call the `minimize()` method on any Pageimage object. This will send the current image file to the local minimize.pw queue and return a Pageimage, representing the current *(compressed)* image or the minimized image after the image has been pulled from minimize.pw’s web servers.
A short alias for the `minimize()` method is `mz()`.
By default, ProcessImageMinimize will distribute a locally compressed image file while the minimized image is queued locally or in minimize.pw’s queue. 
If the module CropImage  by Apeisa is installed on one ProcessWire site, ProcessImageMinimize let you minimize the images created by CropImage by calling `getMinimizedThumb($type)` instead of CropImage’s default `getThumb($type)`.
A short alias for `getMinimizedThumb()` is `getMzThumb()`.
###Configuration Options
ProcessImageMinimize offers multiple configuration options. Developers are encouraged to configure the ProcessImageMinimize module in a way, which accelerates the individual site’s performance.
Following options are configurable via the ProcessWire module page of ProcessImageMinimize:
- **Automatically minimize all images being uploaded.** This will push every image directly to the minimize.pw queue. This will shorten the time for the minimized image to be available on one ProcessWire site. You are still able to invoke the process of minimization manually by calling the `minimize()` method.- **Replace original image files.** If enabled, ProcessImageMinimize will overwrite the original image file with the minimized version. Keep in mind, that your original image files will be deleted!- **Disable local compressing.** If set, ProcessImageMinimize won’t create any locally compressed versions of your images while the original image is being processed.
If Apeisa’s CropImage module is installed, you are additionally able to configure:
- **Automatically replace CropImages.** If set, every image created by CropImage’s `getThumb()` method are passed over to ProcessImageMinimize. Keep in mind, that all passed images will be overwritten with a minimized version. If set, it is not necessary anymore to pass images manually to ProcessImageMinimize by calling the `getMinimizedThumb()` method.
###Local Workflow
The ProcessImageMinimize module is built in a load time efficient way. All processes happen in the background, while preserving site load performance.
There are several different steps for the ProcessImageMinimize module to complete. On every page load, only one step is done at one time. These steps are:
1. When added to the local queue, ProcessImageMinimize will add the particular image file to the `process_image_minimize` database table with a unique id and `state = 0`. Furthermore, a 0 byte `.mz-processing` file is created to reduce the number of database query of future database queries.2. If available, ProcessImageMinimize will generate and push all URLs to the images with `state = 0` to the minimize.pw queue (at a max of 50 images per request). The returning process references will be saved to the database with `state = 1`.3. ProcessImageMinimize will fetch the task states for all images queued in minimize.pw’s queue for more than 100 seconds (at a max of 50 images). For each image, ProcessImageMinimize will analyze the state. If the task failed, the state will be set to 4, the error message of the server will be saved and a `.mz-excluded` file created, to prevent further pushes to the queue. If the task is completed, the state will be set to 2 and the URL to pull the minimized image will be saved.4. If not having 0, ProcessImageMinimize will fetch the minimized image files of all images with `state = 2` (at a max of 5 images per request, since this is the most time intensive task). The images are saved to disk as *.mz. version or – if set – written over the original image file. In the last case, a `.mz-replaced` file is created to prevent the image file to be pushed again. The database record will be updated with `state = 3`.5. ProcessImageMinimize will delete all created temporary images created for images with `state = 3` in the database (with a limit of 10 images per request). After that, the process for one image is completed. The state will be set to 5.
### Best Practices
- **Optimizing for fast site loading.** If you want to ensure that your site loads quickly, turn off local compressing. This will speed up your site, especially by handling larger image galleries.
- **Optimizing for reduced disk usage.** Turn on both replacement settings, so that all large original image files are overwritten with the minimized version.
- **Optimizing for image quality.** [Configure ProcessWire](https://processwire.com/api/fieldtypes/images/) to not compress images (`quality = 100` in config.php). Ensure that the `minimize()` method is always called as last method for an image output. Avoid image operations afterwards.<br>Don't do this: `$image->mz()->size(300,400)`<br>Do this instead: `$image->size(300,400)->mz()`

- **Optimizing for low traffic.** Keep local compression turned on and enable both replacement settings.# For Module Developers
Module Developers are able to call the `minimize()` method on every Pageimage to invoke the process of minimizing.
If generating a custom image file at a certain path, developers can push the image manually to minimize.pw by using the method `pushImagePath($path)` of the ProcessImageMinimize class. This will replace the image at the given path with a minimized version of the image. We encourage all module developers to include minimize.pw into their module. 
**Please feel free to contact us for free additional testing volume or custom APIs at [developers@minimize.pw](mailto:developers@minimize.pw).**