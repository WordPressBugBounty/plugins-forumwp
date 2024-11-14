=== ForumWP – Forum & Discussion Board ===
Author URI: https://forumwpplugin.com/
Plugin URI: https://wordpress.org/plugins/forumwp/
Contributors: ultimatemember, nsinelnikov
Tags: forum, topic, reply, user-profile, user-registration
Requires PHP: 7.0
Requires at least: 5.5
Tested up to: 6.7
Stable tag: 2.1.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Add a forum to your website with ForumWP.

== Description ==

ForumWP is a forum plugin which adds an online forum to your website. With ForumWP you can easily create forums and allow users to create topics and write replies.

= Features of the plugin include: =

* Forum visibility
* Forum styling
* Topics list
* Topics search
* Replies list
* Sub-replies and sorting
* User login & registration
* Profile page
* Easy settings
* Manage forums, topics and replies
* Manage modules and email settings

Read about all of the plugin's features at [ForumWP](https://forumwpplugin.com)

= Documentation & Support =

Got a problem or need help with ForumWP? Head over to our [documentation](http://docs.forumwpplugin.com/) and perform a search of the knowledge base. If you can’t find a solution to your issue then you can create a topic on the [support forum](https://forumwpplugin.com/support).

== Installation ==

1. Activate the plugin
2. That's it. Go to ForumWP > Settings to customize plugin options
3. For more details, please visit the official [Documentation](http://docs.forumwpplugin.com/) page.

== Screenshots ==

1. Screenshot 1
2. Screenshot 2
3. Screenshot 3
4. Screenshot 4
5. Screenshot 5
6. Screenshot 6
7. Screenshot 7
8. Screenshot 8
9. Screenshot 9
10. Screenshot 10
11. Screenshot 11
12. Screenshot 12
13. Screenshot 13
14. Screenshot 14

== Changelog ==

= 2.1.2: November 14, 2024 =

* Fixed: "Load textdomain just in time" issue

= 2.1.1: November 05, 2024 =

* Fixed: Displaying trashed topics and replies
* Fixed: Topic's views counting using $_COOKIES. It's cookieless for now for better security
* Fixed: Get upload dir PHP notice
* Fixed: Loading user profile subtabs
* Fixed: PHP fatal error on the single reply link
* Fixed: Displaying links for restore/delete permanently topic on the frontend
* Fixed: Compatibility with modules
* Fixed: blueprint.json file

* Templates required update:
  - js/forum-category-list.php
  - js/forum-row.php
  - js/reply-row.php
  - js/single-topic-tags.php
  - js/topic-row.php
  - profile/main-mobile.php
  - profile/main.php
  - topic.php

* Cached and optimized/minified assets(JS/CSS) must be flushed/re-generated after upgrade

= 2.1.0: October 02, 2024 =

* Added: Email notifications tab to the user Account page
* Added: Template versioning
* Added: `wp_safe_redirect()` function using
* Fixed: CVE-2024-8428 security vulnerability
* Fixed: Supporting WordPress Code Standards
* Fixed: Typo e-mail to email
* Fixed: UI styling, fixed for attribute for label, Changed field label text
* Fixed: Shortcode issues
* Fixed: Compatibility with block themes
* Fixed: Uninstall process
* Fixed: Admin settings and notices
* Fixed: Topic views cookies reset after logout
* Fixed: Migration module when there aren't any proper data for migration

= 2.0.2: June 14, 2021 =

* Added: `fmwp_user_display_name` filter for user display name and 3rd-party integrations
* Added: `fmwp_before_restore_reply` action hook for 3rd-party integrations
* Fixed: Default topics order on the individual forum page (fixed issue #24)
* Fixed: Uninstall process
* Fixed: Excluding spam topics from the latest topic label (forums list)
* Fixed: Posts privacy in the query posts counter
* Fixed: CSS issues with lists' header padding
* Fixed: Changed last update date for old and new forum when change topic's forum
* Fixed: Modified date for the forum, topic, reply
* Fixed: Displaying topics and replies profile tab for the spectator user role

= 2.0.1: April 28, 2021 =

* Added: Breadcrumbs to the topics and forums pages
* Added: Option `Logout redirect`
* Added: @all and @everyone mention users from Administrator role when reply is created. For submitting email notification to the topic author and all replies' authors in the topic thread
* Fixed: Modules class `is_active()` function to avoid PHP fatal errors
* Tweak: Spam status has been moved from post status to the postmeta

= 2.0: March 2, 2021 =

* Added: bbPress migration module
* Added: Sanitize, escape functions
* Added: PHP and JS hooks for better 3rd-party integration
* Fixed: Security and CPT privacy based on privacy settings (WP native and CPT meta)
* Fixed: Email notifications sending
* Fixed: User permalinks generate process
* Fixed: Dropdown.js scripts
* Fixed: Tipsy.js initialization
* Fixed: Conflicts when change ForumWP CPT statuses
* Fixed: Hide topics from the trashed forums
* Fixed: Post deletion dependencies. Delete permanently sub posts (forum>topic>reply) on post deletion
* Fixed: Shortcode blocks responsibility
* Fixed: Small changes in CSS and layout classes
* Fixed: Uninstall scripts
* Tweak: wp.org release
* Tweak: There is only Migration module in ForumWP plugin

= 1.0.8: February 2, 2021 =

* Added: Migration module to migrate bbPress to ForumWP
* Added: Hooks for customize forums list columns' title
* Added: *.pot file for the translationsin JS files
* Added: Compatibility with UM:Social Activity and creating activity post on reply created
* Fixed: Displaying embed content in Forum page
* Fixed: Admin forms CSS
* Fixed: Compatibility with themes without page.php template
* Fixed: Admin bar classes

= 1.0.7: November 13, 2019 =

* Fixed dropdown JS closing
* Fixed changing forum status (locked/unlocked) via wp-admin
* Fixed small notice with likes

= 1.0.6: October 24, 2019 =

* Added settings for "Photos" module: Max image size and Image quality (for jpg only)
* Added ForumWP - Plus modules compatibility
* Added Forum category, Topic tag IDs column
* Added hidden styles for the topic, forum, reply actions when they aren't possible
* Added edit topics/replies capabilities for ForumWP Participant user (enable menu view at wp-admin if it's visible)
* Added with_sub="1|0" attribute for [fmwp_forums /] shortcode to include/exclude subcategories forums when you display forums of the selected category
* Fixed AJAX preloader styles when scripts are pre-loaded before footer
* Fixed UI elements styles
* Fixed Profile page notices when there is the wrong user in a query
* Deprecated reply permalink setting, because it's unusable
* Changed templates: archive-topic.php, forum.php, profile/edit.php, topic-status-tags.php, topic.php

= 1.0.5: August 30, 2019 =

* Fixed minification of the modules' scripts

= 1.0.4: August 30, 2019 =

* Added "Photos" module for the attaching image files(png, jpg, jpeg, gif) to the topics and replies
* Added AJAX preloaders to avoid double posting or any other double actions
* Fixed double posting
* Fixed search and pagination for the forums list
* Small CSS changes

= 1.0.3: August 1, 2019 =

* Changed the logic for user mentions
* Fixed issues with single topic/forum templates
* Fixed issues with Forum Category and Topic Tag pages' templates
* Small CSS changes

= 1.0.2: July 24, 2019 =

* Added settings for order topics and forums in the lists
* Added global forum/topic settings for set custom template, stored in theme with predefined comment
* Added individual forum/topic settings for set custom template, stored in theme with predefined comment
* Fixed forums/topics privacy settings (private, hidden)
* Fixed templates loading for individual forum/topic
* Fixed FontAwesome integration
* Small CSS changes

= 1.0.1: July 22, 2019 =

* Added shortcode attribute for the sorting Forums
* Added ability to create topic tags from frontend when new topic create or edit
* Fixed User Profile page edit on BeaverBuilder mode
* Changed templates structure and the directory in theme
* Small CSS changes

= 1.0.0: July 15, 2019 =

* Initial Release

== Upgrade Notice ==

= 2.1.1 =
This version fixes a security related bug. Upgrade immediately.

= 2.1.0 =
This version fixes a security related bug. Upgrade immediately.
