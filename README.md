![Checkfront](https://media.checkfront.com/images/brand/Checkfront-Logo-Tag-60.png)
Checkfront Joomla Booking Plugin
==========================

The [Checkfront Joomla Booking Plugin](http://www.checkfront.com/joomla/) seamlessly 
integrates Checkfront into your Joomla powered Website.  This combines the robust publishing capabilities
of Joomla with the power of Checkfront.

This plugin is for Joomla 1.6 or greater (tested to 3.0).  

Except as otherwise noted, the Checkfront Joomla Plugin is licensed under the [GNU General Public License](http://www.gnu.org/copyleft/gpl.html)

Usage
-----

Once installed and configured, you can render a booking window anywhere in your site by creating a new article, and 
supplying the checkfront shortcode: [checkfront] 

```html
<h2>Booking Online!</h2>

[checkfront]
```

You can further customize how the booking portal renders by supplying options to the short code.

```html

<!-- Auto select a category -->
[checkfront category_id=1]

<!-- Filter a category-->
[checkfront filter_category_id=1,3]

<!-- Display the tabbed interface in a compact layout-->
[checkfront options=tabs,compact]

<!-- Use a custom background and font-->
[checkfront style="background-color: #000;color:#fff;font-family:Tahoma; width:800"]
```
For a full list of of available options please the setup guide: [Online Bookings with Joomla and Checkfront](http://www.checkfront.com/joomla/)

