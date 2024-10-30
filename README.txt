=== Plugin Name ===
Contributors: chvillanuevap
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=chvillanuevap%40gmail%2ecom&lc=US&item_name=Better%20Comments%20Reply%20Manager&item_number=better%2dcomments%2dreply%2dmanager&amount=5%2e00&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: comments
Requires at least: 3.1
Tested up to: 4.6.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Take control of your comments! Track which comments need a reply from the blog post's author and which do not.

== Description ==

With this plugin, you can easily check which comments require a reply from each blog post's author. This can be very beneficial if you have a blog with a significant amount of comments. The plugin allows you to manually mark if a comment does not need a reply from the author, and also, through filters, to choose which types of comments need a reply.

== Installation ==

1. Upload the `better-comments-reply-manager` folder to the `/wp-content/plugins/` directory.
2. Activate the Better Comments Reply Manager plugin through the Plugins menu in WordPress.
3. Once the plugin has been installed and activated, it will start working. There are no settings or options to configure.

== Frequently Asked Questions ==

= When is a comment marked as needing a reply? =

Every single time a new comment is written by a reader and posted to the blog, it will be marked as needing a reply (see Screenshot 1). The reply status of the comment will be displayed in a new column titled "Reply Status".

If the comment was written by the author, it will be marked as not needing a reply (see Screenshot 2).

Older comments, written before the installation of this plugin, will not have a reply status (see Screenshot 3).

However, the administrator can still manually mark those comments as needing a reply using the comment row actions (see Screenshot 4).

For convenience, if a user other than the post's author replies to a comment, it will be displayed in the Reply Status column (see Screenshot 12).

= Can I manually mark a comment as needing or not needing a reply? =

Yes! (See question below)

= How to mark a comment as needing or not needing a reply =

Once the blog post's author responds to a comment that needs a reply, the reply status of the comment is updated to not needing a reply (see Screenshot 11).

The plugin also offers the following additional ways to mark a comment as needing or not a reply:

* Through the Comments page

Anyone with the capacity to `moderate_comments` (i.e. administrators, authors) can mark a comment as not needing a reply through the comment row actions (see Screenshot 10). This capacity can be modified through the `better_comments_reply_manager_pro_cap` filter as:

    add_filter( 'better_comments_reply_manager_pro_cap', function( $capacity ) {
        // $capacity = 'moderate_comments' by default.
        return 'enter_your_permission_level_here';
    } );

The same action can be performed in bulk through the comment bulk actions (see Screenshot 8). Note that this bulk option requires that JavaScript is active in the browser.

* Through the Edit Comment page

The option is also available in the Edit Comment page, under the new Reply Status menu (see Screenshot 11).

* Through the Edit Post page

And through the Edit Post page, with the comment row actions (see Screenshot 10).

= How to see which comments need a reply =

You can display only the comments that need a reply by clicking on the "Approved & Need Reply" link in the status links bar (see Screenshot 11).

As the name indicates, it will only display those comments that have been approved and that are marked as needing a reply by the post's author (see Screenshot 12).

= Will this plugin mark comments as needing a reply retroactively? =

No. The author plans to add the capability to retroactively analyze the reply status of the comments in the future.

== Screenshots ==

1.  Comment needs a reply.
2.  Comment is by author.
3.  Comment reply status is not marked.
4.  Comment row actions.
5.  A user has replied status.
6.  Comment replied by author.
7.  The Mark as Does Not Need Reply action in the comment row actions bar.
8.  Comment bulk actions.
9.  Edit Comment page.
10. Edit Post page.
11. The new "Approved & Need Reply" status in the comment status links.
12. The new "Approved & Need Reply" comments page.

== Changelog ==

= 1.0.0 =

Release Date: September 16th, 2016

* Initial commit.
