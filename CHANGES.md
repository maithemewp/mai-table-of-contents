# Changelog

## 1.6.2 (1/18/24)
* Fixed: Remove unnecessary encoding in PHP's DOMDocument which was unintentionally encoding some special characters from non-English languages.

## 1.6.1 (1/10/24)
* Fixed: PHP warning when during sanitization when label/hide/show text fields are empty.

## 1.6.0 (1/4/24)
* Added: New settings for TOC label and hide/show text.
* Fixed: Wrong font-weight name for the TOC toggle (now using normal instead of regular).

## 1.5.5 (11/27/23)
* Changed: Updated the updater.
* Changed: Updated character encoding function for PHP 8.2 compatibility.

## 1.5.4 (10/18/23)
* Fixed: Styles missing in the editor when using TOC block.

## 1.5.3 (10/17/23)
* Added: [Developers] New `mai_table_of_contents_has_custom` filter to declare when you're rendering a TOC via custom display methods.
* Changed: Register TOC block with block.json.
* Changed: Update the updater.

## 1.5.2 (6/28/23)
* Changed: Update the updater.
* Fixed: TOC block and shortcode not respecting the minimum headings count.

## 1.5.1 (5/5/23)
* Fixed: Missing content on some post types when a TOC is not present.

## 1.5.0 (5/5/23)
* Changed: More efficient building of TOC and structuring the headings/markup.
* Fixed: Invalid markup in some scenarios.

## 1.4.3 (2/6/23)
* Fixed: Headings inside other elements/blocks were breaking hierarchy in TOC.

## 1.4.2 (3/14/22)
* Fixed: Post type restriction not working in some scenarios.

## 1.4.1 (3/11/22)
* Fixed: Missing default HTML class changing default TOC styles unexpectedly.

## 1.4.0 (3/10/22)
* Changed: Refactored internal classes.
* Fixed: Edge-case issues when TOC block is used and/or heading id attribute exists but is empty.

## 1.3.5 (3/2/22)
* Added: Support for Rank Math TOC feature.

## 1.3.4 (3/1/22)
* Fixed: CSS not loading on some posts.

## 1.3.3 (2/17/22)
* Fixed: TOC data showing in Mai Post Grid in some scenarios.

## 1.3.2 (12/17/21)
* Fixed: CSS loading if blocks parsed early.
* Fixed: Settings link in plugin list.

## 1.3.1 (12/7/21)
* Fixed: Editor vs front end CSS loading.

## 1.3.0 (12/6/21)
* Changed: CSS is now loaded on demand right before the table of contents.
* Changed: Settings now appear under Mai Theme menu in Dashboard.

## 1.2.0 (9/15/21)
* Added: New style setting with "Minimum" option.
* Added: [Developers] New `mai_table_of_contents_labels` filter to change the open/close and heading text.
* Changed: Allow html in title/label.
* Changed: Settings and block fields are now registered via PHP.

## 1.1.3 (4/14/21)
* Fixed: TOC heading hover color now inherits primary color in Mai Theme v2.

## 1.1.2 (2/25/21)
* Fixed: TOC list spacing in the editor.

## 1.1.1 (2/24/21)
* Fixed: Custom property order to allow easier overrides for toc colors.

## 1.1.0 (2/22/21)
* Added: Support for Mai Theme v2 colors and spacing.

## 1.0.1 (2/13/21)
* Added: Mai logo icon to updater.

## 1.0.0 (12/11/20)
* Official release.

## 0.2.5 (6/2/20)
* Fixed: Settings page not loading in some instances.

## 0.2.4 (3/3/20)
* Fixed: No longer runs on front page.
* Fixed: Empty content error when attempting to calculate headings when there is no content in a post.

## 0.2.3
* Fixed: Undefined function.

## 0.2.2
* Fixed: Marker arrow was still displaying in Firefox.

## 0.2.1
* Changed: "Click to Show" text to just "Show" for better mobile experience.
* Changed: Made more strings translation ready.

## 0.2.0
* Added: Settings page.
* Added: Link to settings page from plugin list.
* Added: Block preview now shows an example table of contents.

## 0.1.2
* Added: Add support for align wide.

## 0.1.1
* Fixed: ACF's load_json now works correctly to load the field groups.

## 0.1.0 (10/18/19)
* Initial release.
