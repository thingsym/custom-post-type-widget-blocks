# Custom Post Type Widget Blocks

Custom Post Type Widgets for the Block Editor (Gutenberg).

This WordPress plugin adds default Custom Post Type Widgets to the Block Editor.
You can filter by registered Custom Post Type or Taxonomy on the Block Editor.

## Block category `Custom Post Type Widget Blocks`

![Custom Post Type Widget Blocks](screenshot-1.png "Custom Post Type Widget Blocks")


## Installation

1. Download and unzip files. Or install Custom Post Type Widget Blocks plugin using the WordPress plugin installer. In that case, skip 2.
2. Upload "custom-post-type-widget-blocks" to the "/wp-content/plugins/" directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Add a widget block in block category 'Custom Post Type Widget Blocks' to the Block Editor.
5. Have fun!

**IMPORTANT**: By default, WordPress will not work Date-based permalinks of custom post type. Recommend that you install the plugin in order to edit the permalink, if you are using a Date-based permalinks.

And try the following: [Custom Post Type Rewrite](https://wordpress.org/plugins/custom-post-type-rewrite/)

## Compatibility

- WordPress version 5.4 or later
- Gutenberg version 7.5 or later

## Descriptions of Widget Blocks

### Latest Posts (Custom Post Type)

display a list of your most recent custom posts.

### Archives (Custom Post Type)

display a monthly archive of your custom posts.

### Categories (Custom Post Type)

display a list of categories that has custom posts.

### Calendar (Custom Post Type)

display a calendar of your site’s posts.

### Latest Comments (Custom Post Type)

display a list of your most recent comments.

### Tag Cloud (Custom Post Type)

display a list of the your most used tags in a tag cloud.

### Search (Custom Post Type)

A search form for your site.

## Hooks

Custom Post Type Widget Blocks has its own hooks.

### Filter hooks

- custom_post_type_widget_blocks/archives/widget_archives_dropdown_args
- custom_post_type_widget_blocks/archives/widget_archives_args
- custom_post_type_widget_blocks/calendar/get_custom_post_type_calendar
- custom_post_type_widget_blocks/latest-comments/widget_comments_args
- custom_post_type_widget_blocks/categories/widget_categories_dropdown_args
- custom_post_type_widget_blocks/categories/widget_categories_args
- custom_post_type_widget_blocks/latest_posts/widget_posts_args
- custom_post_type_widget_blocks/tag_cloud/widget_tag_cloud_args

## WordPress Plugin Directory

Custom Post Type Widget Blocks is hosted on the WordPress Plugin Directory.

[https://wordpress.org/plugins/custom-post-type-widget-blocks/](https://wordpress.org/plugins/custom-post-type-widget-blocks/)

## Test Matrix

For operation compatibility between PHP version and WordPress version, see below [Travis CI](https://travis-ci.com/thingsym/custom-post-type-widget-blocks).

## Build development environment

```console
cd /path/to/custom-post-type-widget-blocks

# Install package
npm intall

# Show tasks list
npm run

# Build plugin
npm run build
```

### PHP unit testing with PHPUnit

```console
cd /path/to/custom-post-type-widget-blocks

# Install package
composer intall

# Show tasks list
composer run --list

# Run test
composer run phpunit
```

## Contribution

### Patches and Bug Fixes

Small patches and bug reports can be submitted a issue tracker in Github. Forking on Github is another good way. You can send a pull request.

1. Fork [Custom Post Type Widget Blocks](https://github.com/thingsym/custom-post-type-widget-blocks) from GitHub repository
2. Create a feature branch: git checkout -b my-new-feature
3. Commit your changes: git commit -am 'Add some feature'
4. Push to the branch: git push origin my-new-feature
5. Create new Pull Request

## Changelog

### [1.1.1] - 2020.09.15

- check class exists
- imporve code with phpcs, phpmd and phpstan
- reformat
- add strict mode

### [1.1.0] - 2020.08.18

- update japanese translation
- update pot
- imporve code with phpcs, phpmd and phpstan
- update testunit configuration
- add Disabled to latest posts block
- add unstable__bootstrapServerSideBlockDefinitions
- fix test case
- change wp cache name
- add hooks
- change hook tags
- add loading asset to register_block_type argument
- change asset loading function from wp_enqueue_* to wp_register_*
- add checking register_block_type function
- change wp_enqueue_script dependency setting to use asset file
- add CUSTOM_POST_TYPE_WIDGET_BLOCKS_PATH constant

### [1.0.1] - 2020.05.06 - for plugin review

- remove prefix `__` with define name
- add LICENSE file

### [1.0.0] - 2020.05.04

- initial release

## License

Licensed under [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).
