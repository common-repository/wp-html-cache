=== Plugin Name ===
Tags: html, cache,performance,html Cache,WP Cache
Contributors: wpxue
Stable tags:trunk
Requires at least: 2.8
Tested up to: 3.0.1

WP html cache is a plugin to accelerate the speed of your wordpress, by generating real html cache file, WP html cache will automatically generate real html files for posts when they are loaded for the first time, when the post published and a comment posted automatically update the html cache file.it is Very fast

== Description ==
This is a plugin to accelerate the speed of your wordpress, by generating real html cache file, the plugin will automatically generate real html files for posts when they are loaded for the first time, when the post published and a comment posted automatically update the html cache file.it is Very fast



== Installation ==

1.Upload to your plugins folder, usually `wp-content/plugins/` and unzip the file, it will create a `wp-content/plugins/WP-html-cache/` directory.

2.Activate the plugin on the plugin screen.

3.Make your permalink looks like a real html file : http://www.wpxue.com/archives/1.html

**Attention**

`htm (*.htm)or html (*.html)or  directory (dir/)must be the end of the permalink.`

4.create a file named "index.bak" under the root of your web directory

5.done

== Uninstallation ==

1.go into admin->options->WP-html-cache

2.delete all cache files (very important)

3.go into admin->plugins ,disable WP-html-cache

4.done.

== Frequently Asked Questions ==

**Attention**

htm (*.htm)or html (*.html)or  directory (dir/)must be the end of the permalink.

In the Settings → Permalinks panel (Options → Permalinks before WordPress 2.5), you can choose one of the "common" structures or enter your own in the "Custom structure" field using the structure tags. 

For more help  see the Permalinks,

http://codex.wordpress.org/Using_Permalinks#Choosing_your_permalink_structure


** Do I really need to use this plugin? **

* If your site gets Slashdotted
* If you're on a very slow server
* If you've had a complaint from your host about performance
* If you just want to blog rather than testing new plugins and functions of wordpress

** How can I tell if it's working? **

WP-html-cache adds some stats to the very end of a page in the HTML, so you can view source to see if there any codes like "<!-- create at yyyy-mm-dd hh:mm:ss by WP-html-cache 1.0 -->"

** Do you cache other pages such as cat ? **

YES.this plugin can cache home ,  single,  tag,  cat,



== More Info ==

For more info, please visit http://www.wpxue.com/wp-html-cache

