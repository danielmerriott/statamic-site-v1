# The Statarkers Theme

Born from the idea of a blank canvas for starting your Statamic sites this theme is loosely based on the popular [Starkers](http://viewportindustries.com/products/starkers/) theme for Wordpress. The idea being that you can load the theme into your Statamic themes folder, add the dummy content and fields to your Statamic install and get designing and building your Statamic sites in even less time than you were already.

Normalize is used to reset browser defaults and SASS is used to make your CSS life easy. Each template is marked up with some helpful user notes. A development version of modernizr is included but we recommend that you tailor this to your site's requirements. To help with older browser support [DOMAssistant](http://www.domassistant.com/) and [Selectivir](http://selectivizr.com/) are also conditionally loaded for IE8. To finish off there are plenty of html5 boilerplate best practices in the document head and jQuery is included as standard.

Out of the box Statarkers supports IE8 and all modern browsers. To provide a basic layout for older browsers (IE7 & below) we use Andy Clarke's [universal stylesheet](http://stuffandnonsense.co.uk/blog/about/universal_internet_explorer_6_css). The scripts in the default layout template use conditional comments to stop them loading below IE8. If you would like to support IE7 & below you can remove these elements. 

You can use Statarkers in any way you wish, please feel free to fork it on github. As the theme is offered for free, it’s also offered with limited support. If you find a bug, please let me know and I’ll fix it as soon as possible. Any improvements and feature requests can be asked through github.

Happy Statamic Themeing!

##Installing

To install the theme follow the instructions below.

1. [Install Statamic:](http://statamic.com/learn/digging-in/installing) **Don't forget to set up your .htaccess file**.
2. Copy the statarkers theme folder to your _themes folder. Please note if you have downloaded the statarkers-theme-master folder **the statarkers theme is located inside the _themes folder**.
3. Rename the Statarkers theme folder to the name of your theme. From now on we will still refer to the Statarkers theme folder but this will now be whatever you rename it to.
4. Change the name of the statarkers.js file (located in the js folder) to the name of your theme.
5. In the Statarkers theme folder go to the css folder. Rename the statarkers.css file to the name of your theme then find the sass folder and rename the statarkers.scss file to the name of your theme.
6. In the Statarkers theme folder open the config.rb file and change the http path to /themes/yourThemeName instead of /themes/statarkers.
7. Delete the contents of your Statamic install fieldsets folder then copy the new fieldsets from the Statarkers folder (located in _config/fieldsets).
8. If you would like to use the dummy content provided with the Statarkers theme (recommended) then delete the contents of your existing _content folder and copy the files and folders from the Statarkers _content folder.
9. In your Statamic install go to _config settings YAML file and change the _theme name from denali to the name you have given to your theme. If this is a clean install you should also change the site name and url to whatever you are using. We suggest also changing the _taxonomy_slugify to true as this gives you cleaner url's.
10. Add the gitignore file to the root of your project/site, in here there are some handy ignores for Statamic.
11. Run a compass watch on the statarkers theme folder using the terminal, alternatively you can use something like [Codekit](http://incident57.com/codekit/) to preprocess the SASS files.

##More Information

You can visit [Statamic Themes](http://www.statamicthem.es/themes).
