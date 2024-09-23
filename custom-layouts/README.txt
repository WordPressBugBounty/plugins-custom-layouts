=== Custom Layouts - Post + Product grids made easy ===
Contributors: CodeAmp
Tags: posts, products, post grid, product grid, recent posts
Requires at least: 5.9
Tested up to: 6.5
Requires PHP: 7.2
Stable tag: 1.4.12
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Build a list or grid layout of any post type (posts, products, pages + more).

== Description ==

Build a list or grid layout of any post type (products, pages, posts + more). 

The visual editing experience makes creating unique designs a breeze.

* Design post templates (cards) using our drag and drop builder
* Built for both Gutenberg users and non Gutenberg users (blocks + shortcodes available)
* Query builder - select from multiple post types or restrict by tags, categories, taxonomies, authors and more
* Works with any theme
* No coding required
* Responsive options

**Links**

* [Website](https://customlayouts.com)
* [Examples](https://customlayouts.com/examples/)
* [Documentation](https://customlayouts.com/documentation/)
* [Follow us](https://twitter.com/wpcustomlayouts) for updates and tips

== What can you make with this plugin? ==
* Popular posts widget
* Recent posts widget
* A shop layout for listing products (*when combined WooCommerce, Easy Digital Downloads and other product plugins)
* A post grid
* A portfolio grid
* Masonry layouts
* Custom post type layouts
And much more...

== Layout Editor ==

Build single column or grid based layouts using the visual editor.

* Supports Masonry out of the box
* Tons of design options to build the layout you want
* Choose to add numbered Pagination ( 'Load more' and 'Infinite scroll' options are planned )
* Powerful query builder
* Responsive columns (choose how many columns to show for each device size - and set your own breakpoints)

== Query Builder ==

Options include:

* Multiple post type selection
* Tag, category and custom taxonomy restriction
* Single author or multiple author restriction
* Ordering by - post title, published date (for recent posts), modified date, post ID, comment count (for popular posts), author
* Posts per page
* Offset
* Ignore sticky posts

== Template Editor ==

* Design individual templates (cards) using our drag and drop editor - creativity is your only limit
* Link them with your layouts to build beautiful designs
* Hundreds of design options
* Unique blocks for building templates + template editor
  * granular control over the blocks you add to your design - choose from a growing list
  * to re-arrange blocks, just drag and drop
  * each block has it's own sidebar full of options such as spacing, colors (including gradients + alpha transparencies), font settings, borders, and more.
* Currently available blocks: **Post Title**, **Post Type**, **Post Excerpt**, **Post Author**, **Published + Modified Date**, **Custom Field**, **Taxonomies**, **Link**, **Basic Text** (with shortcode support).
[Find out more about our template blocks](https://customlayouts.com/documentation/template-elements/).

== Gutenberg integration ==

* Custom blocks for building layouts
* Seamlessly integrates with our Template Editor
* UI built using 90% Gutenberg components 👍
* Experimental FSE Query block support (drop our Post Template block into the Query block...)
* Works with the version of Gutenberg shipped in WP core, and the Gutenberg plugin (using the latest stable build)

== Built for speed ==

* At its base - loads only 1 css file and 1 javascript file on the frontend - total bundle size approx 15kb.
* CSS file dynamically created, saving resources and requests per page load (+ avoiding inline CSS)
* Caches posts + queries for optimum performance
* Not jQuery dependant

== Includes 7 sample templates ==
We've included a pack of templates ready for you to customise - check the installation tab for how to import these to your site.

== Multilingual ready ==
Custom Layouts should support most multilingual plugins - we use a custom post type to store content that is not stored in the block editor.
* We've also added specific support for WPML's Advanced Translation Editor.

== Screenshots ==

1. Using the layout block
2. The Template Editor - full customization over each item
3. Frontend result
4. Short demo animation
5. Responsive controls


== Installation ==

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `custom-layouts.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `custom-layouts.zip`
2. Extract the `custom-layouts` directory to your computer
3. Upload the `custom-layouts` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

= Importing the sample templates =

To import the sample template data, follow these steps:

1. Log in to your site as an administrator.
2. Go to Tools: "Import" in the WordPress admin panel.
3. Install the "WordPress" importer from the list.
4. Activate & Run Importer.
5. Upload the file from our plugin folder - `custom-layouts/sample-data/sample-templates.xml` - using the form provided on that page.
6. You will first be asked to map the authors in this export file to users on the site. For each author, you may choose to map to an existing user on the site or to create a new user.
7. WordPress will then import each of the templates into your site.
8. Access your new templates via wp-admin -> "Custom Layouts" -> "Templates"


== Frequently Asked Questions ==

= How do I add a layout to a page?  (Block editor way) =

Then you can use the `Custom Layouts` block.

All of the layout options are available from within the block inspector, or you can load a saved layout.

= How do I add a layout to a page? (Shortcode method) =

You can find this shortcode in the sidebar of your Layout admin page. 

It will look something like: `[custom-layouts id="123"]`

Where `123` is the ID of your layout

= How do I add a template to a layout? =

* First create a template by heading to "Templates" in the "Custom Layouts" admin menu or by clicking "add new" in the layout block.
* Edit your template and when finished you can choose it from  the "Post Template" dropdown in your layout.


== Known Issues ==

* Admin Template Editor only works on desktop computers (tablets + mobiles not supported)
* Admin Template Editor does not work on IE, all other major browsers supported - ** the frontend of this plugin supports IE 11+, no sweat


== Roadmap ==

* Improve performance and load speed by providing an option to use CSS Grid for layouts + masonry
* Support more CSS unit types in more places
* Blocks for WooCommerce
* Blocks for ACF
* More template blocks (possibly use the block library)
* Additional hover options
* Additional font options - either Google fonts or the Gutenberg api which looks like it's coming soon
* Social/sharing blocks

**[Reviews](https://wordpress.org/support/plugin/custom-layouts/reviews/#new-post) help us improve (and climb the plugin rankings)**

== Changelog ==


= 1.4.12 =
* Security improvements.

= 1.4.11 =
* Improvement - add post title to the featured image anchor tag.
* Fix - issues with the font panel throwing an error.
* Fix - issues with inheriting block editor styles in our admin screens.

= 1.4.10 =
* Fix - issues with WP 6.4 and our admin scripts not loading (lodash was deprecated).
* Fix - CSS issues in our modals in the block editor.

= 1.4.9 = 
* Update - use wp_kses_post instead of esc_html to allow html tags in post titles.
* Fix - issues with WP 6.2 and our admin scripts not loading.
* Fix - a UI issue with our admin multiselect fields not displaying correctly
* New - support shortcodes in the "no results" message
* New - support layouts and templates in FSE.

= 1.4.8 =
* Fix - issue with search form selection being reset
* Fix - Add i18n to date picker option
* Fix - An issue with save button being disabled after a single change in our modals
* New - Grid gap layout setting which allows for more granular control of spacing (replaces item spacing)
* New - Spacing controls to add margin and padding to your layouts
* New - Background color + gradient option for your Layouts
* Improvement - use CSS var for setting dynamic padding

= 1.4.7 =
* Fix - a bug with our posts layout block not saving the connected Search & Filter query correctly
* Fix - use css vars to dynamically add layout spacing (prevents a validation error when using style tags)
* Fix - an issue with taxonomy terms being double encoded, causing admin previews to fail when taxonomy terms have special characters
* Fix - an issue when using the font size selector field refreshing our admin screens (they were causing an unwanted page submit).
* Fix - an issue where templates wouldn't render if elements had not been added - thanks @mchelles for reporting
* Fix - an issue with our layout css not being loaded when CSS mode is set to inline
* Fix - various CSS issues in admin
* Compatibility - an issue when the using the filesystem before it was ready - thanks @freddela for reporting and @ssh-sftp-updater-support team for helping
* Improvement - switch over color pickers to the new color picker dropdown from Gutenberg
* Version - bump minimum required WordPress version to 5.9

= 1.4.6 =
* Fix - stop triggering enqueue_block_editor_assets multiple times (fixes an issue with AIOSEO)
* Fix - an issue where (restrict by) taxonomies was not saving in admin post templates
* Fix - an issue with the gradient picker in the template editor

= 1.4.5 =
* Fix - support `%` to position the comment number in the comment field (when the result is 1 comment)
* Fix - an issue where the featured image expanded past the width of a column
* Fix - reset query cache when using WooCommerce manual ordering
* Fix - a visual issue when dragging template elements
* Fix - an issue when using the default post template block
* Fix - an issue with the `openInNewWindow` value for featured media not being init correctly
* Fix - a PHP warning when accessing a custom field which is not scalar
* Improvement - update block.json in order to show additional info in the plugins repo
* Improvement - better support for editor styles across different setups (when using FSE, with the GB plugin + without)
* New - add default and manual sort orders to layouts

= 1.4.4 =
* Fix - an regression introduced in 1.4.3 where our date elements would display todays date rather than the correct date

= 1.4.3 =
* Fix - an issue with color palette support defined via `add_theme_support`
* Fix - an issue with editor styles not being loaded since WP 5.8
* Fix - an i18n issue with formatting dates on the frontend
* Fix - various CSS issues since WP 5.8
* Fix - an issue when opening a layout or template modal and the canvas width was being calculated incorrectly
* Fix - a WPML issue when not using the ATE, our templates wouldn't load in admin
* Fix - a WPML issue where our CSS was not being regenerated on the frontend for the correct language (after saving a template via admin)
* Improvement - register our blocks via a json file so they are discoverable in the block library
* Improvement - speed enhancements including preloading of some of our rest api requests
* New - add option to open links in new window for Featured Media and Post Title blocks

= 1.4.2 =
* Fix - an issue with shortcodes not working in our text block
* Fix - template editor - an issue where clearing a color setting (to make it unset / transparent) was causing defaults to be loaded instead
* Fix - a PHP warning on activation
* New - support for WPML + Advanced Translation Editor

= 1.4.1 =
* Fix - Templates - add alt text to featured images
* New - Templates - comment count element
* New - Templates - excerpt options - limit the manual excerpt length and hide the "read more" text
* New - Templates + Layouts admin pages - integration with editor styles (for themes that support it) - only available with the latest Gutenberg plugin v10.6 (coming soon in WP core)

= 1.4.0 =
* Fix - Layouts - ensure layouts are not affected by global pagination when pagination is set to "none"
* Fix - Templates - default settings had some spacing issues
* Fix - Templates - an issue where the "image source" option was not displaying if the post didn't have a featured image
* Fix - Templates - save the last preview content source when saving the template editor via the admin page
* Fix - Templates - link block - when a background had a gradient set it wasn't applied to the hover / focus styles
* Fix - Templates - Taxonomy block - a php warning when no taxonomy was selected
* Improvement - added a specific class to the results when used with Search & Filter to allow for multiple search forms + results on a page
* Improvement - modals are now larger and more responsive
* New - Modal feature - sync preview width - modals editors now inherit the width of the layout they were launched from (for better previews)
* New - Layouts - access saved layouts directly via the layout block - edit and create new layouts via the layout editor modal or use the block inspector.
* New - Layouts - add template information to posts edit screen
* New - Templates - added line height to the Typography panel
* New - Templates - added new shortcode `[custom-template]` for displaying an individual post template with a single post (matches behaviour of the post template block)
* Upgrade notice - we have changed the name of our layout shortcode - it is now `[custom-layout]` - your previous shortcodes will continue to work for some time.

= 1.3.1 = 
* Fix - layout issue with excerpt display (when the excerpt automatically adds the read more link)
* Fix - an issue with transients not being used correctly when there are multiple layouts on the same page
* Fix - an issue with tax_query not being set properly
* Fix - an issue with pagination not working correctly on the front page
* Fix - issues with before/after text in the custom field block
* Fix - an issue with the gradient sample template css
* Improvement - increase template editor max preview width to 1000px
* New - support html as a custom field type
* New - offset query paramater (works with pagination)
* New - post type block

= 1.3.0 =
* Release notes - https://codeamp.com/update-custom-layouts-1-3-0/
* Fix - some query issues when taxonomy query was disabled (but the settings were still being applied)
* Fix - some instances where our assets were not being supplied the correct version number, and consequently remained cached after an update
* Fix - author gravatar was not scaling with font size
* Fix - various issues to do with popovers and tooltip alignment
* Fix - regenerate CSS feature was not fully regenerating the CSS
* Fix - by default, select display name in author block rather than full name (full name can be empty)
* Fix - actions were not firing (filters were ok) - we were mixing back slash and forward - now all filters + actions use forward slashes only
* Update - required WP version to 5.7
* Update - sample templates - addition of taxonomies + new spacing controls
* Update - admin icons for some template blocks
* New - Filters + actions for frontend blocks
* New - change container size of a block to be full width or wrap to content (new toolbar button)
* New - "fill last row" layout option - you can now stretch the last row of a grid if results do not fill all of the columns
* New - Custom Field block - display the value of a custom field in your templates as a string (restrict by words/characters) or formatted number (with decimals), or a formatted date (custom formatting options)
* New - Taxonomies block - list any combination of taxonomy terms in your templates
* New - allow unit types for margins, padding and border radius - upgrade components to use BoxControl

= 1.2.1 =
* Fix - an issue when using the query restrictions the first time (in a new layout)
* Fix - an issue with the S&F integration and Polylang
* Fix - issues with custom classes on the layout block and layout shortcode
* Fix - issues with the font size selector
* Fix - a layout glitch when dragging the first block into a template
* Fix - prevent saving of a new template via modal, until a title has been set
* Improvement - when creating a new template via the modal, auto select it in the layout after saving
* New - no results message - add your own message to display when the query settings return no results

= 1.2.0 =
* Improvement - Change the label of the "Custom Layout" block to "Posts Layout"
* New - "Post Template" Block - Drop into an FSE Query block or use the post selector to display any post with any template
* New - Query options for Layouts - select multiple post types and restrict by tag/category/taxonomy

= 1.1.7 =
* Fix - a JS error when launching the template editor modal
* New - the template editor modal now uses the query from the layout to display the set of posts for previews
* New - responsive columns - choose how many columns to show for Mobile, Tablet and Desktop
* New - settings page to set responsive breakpoints + regenerate CSS

= 1.1.6 =
* Fix - missing files from bad commit

= 1.1.5 =
* Fix - issue with the order paramters on the frontend
* Fix - issue with the sticky post paramater on the frontend
* Fix - issue with block align not working on the frontend
* Fix - add loaders/placeholders to layout block + admin page
* Fix - an issue with CSS not loading due to a https bug in wp_uploads_url()

= 1.1.4 =
* New - Add visual editing to the layout admin page

= 1.1.3 =
* Fix - an issue with Post Title not being displayed correctly

= 1.1.2 =
* Fix - an issue with CSS generation
* Fix - Rename "Custom Layouts" block to "Custom Layout"
* New - Show a default image icon when no image is present + allow color to be changed

= 1.1.1 =
* Fix - Enable link on featured image was not working
* Fix - Modal template title not showing up correctly
* Fix - Grid spacing not working
* Fix - Border color not being set properly
* Fix - Update CSS file when templates are drafted / trashed / deleted
* Improvement - store queried IDs in transients (they will get complex eventually)
* Improvement - update icon (still room for improvement)
* Improvement - Theme colours + gradients now available in template editor admin page with WP 5.7 or Gutenberg 9.7+
* Improvement - Update all frontend class names to use BEM naming conventions for predictable and reliable naming (we do increase the specificity, though)
* New - Gradient picker for background colors
* New - Alpha transparency for all color pickers
* New - Create new templates directly from the layout block
* New - Equal height rows (when not using masonry)
* New - Featured Image options - set image size, ratio and fit mode
* New - Add support for layout block alignment (supports wide and full width)
* New - Demo templates

= 1.1.0 =
* New - Gutenberg Integration - New block + modal editor
  * Layout Block - build layouts using the new layout block
  * Modal Template Editor - get the full template editor as a modal from within the layout block
* Fix - update version number whenever templates are saved to prevent caching of frontend CSS
* Fix - remove admin-ajax dynamic CSS and opt to use inline CSS as a fallback instead
* Fix - store generated CSS in post meta for faster loading, and only regenerate the updated templates when needed
* General UI fixes

= 1.0.1 =
* Fix - issue with imagesLoaded & re-initialising the layout on frontend
* Fix - issue with frontend date formatting when "custom" was chosen (thanks @paaljoachim)
* Fix - issues with the padlock unsetting in our dimension controls
* Fix - allow for multiple layouts on a single page + support for multiple masonry instances

= 1.0.0 =
* Initial version.

= 0.0.1 =
* Init project setup

== Upgrade Notice ==

= 1.4.8 =
We have changed the frontend markup of our layouts and template containers. This should only affect users who have custom CSS applied to layouts - test before upgrading.

= 1.4.0 =
We have changed the name of our layout shortcode - it is now `[custom-layout]` - your previous shortcodes will continue to work for some time.

= 1.1.7 =
When upgrading, CSS should be automatically regenerated.  If it is not, head to the new settings page and choose "Regenerate CSS".


