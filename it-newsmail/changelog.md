#IT-NewsMail

Wordpress plugin for automatically mailing newsposts to registered users who subscribe to categories.
Author: Max Witt digIT 13/14
Licence: MIT

Legend:	+ Added
		- Removed
		x Known bug
		! Bugfix
		* Modified

##Version 2.1
Plugin becomes aware of changes to the wordpress domain. 
Category and User modifications won't break the plugin

	+ Users subscribing to all news automatically subscribe to new cats.
	+ Removed users or categories are also removed from Newsmail DB

---

##Version 2.0
Heavy ideas implemented. This was the original list of features suggested
for the newsmail plugin.

	+ Users may subscribe to any subset of categories
	* "All news" means that each user really subscribes to all categories
	! Subscription function only modifies DB when real changes are made
	! Post--mail delay - Author has 60 minutes from last edit until the
	  post is sent to the recipients. Editing resets timer
	+ Cron and post queue that runs regardless of site trafic
	+ Global response times a little lower thanks to separated cron
	+ Visually responsive JS aid for showing when the user subscribes
	  to all news.
	+ Users may subscribe to each category from their respective page.
	* Conforms to a unified category-subscription model per user across 
	  the plugin.

---

##Version 1.1
Bugfix - when users were batched in the total count was 102. 
Our recipient limit was 99 which broke the whole thing. 
A feature from 2.0 was scheduled and launched earlier.

	! Added a churn to the mailing function - 90 recipients maximum per email.

---

##Version 1.0
First working version of the plugin. 

	+ User and category IDs are stored in a table belonging to the plugin
	+ A simple widget lets the user choose "all news" or nothing.
	+ Two methods are hooked into WPs stack: One serving the subscriptions 
	  and one posting the emails.
	+ Emails are formatted with HTML, currently without stylesheet.
	x Each time the user hits save in the widget - one DB query per
	  subscription is performed, even if nothing is changed.
	x Each post is emailed to all subscribers in the same email
	  May be a problem if there are limits on recipients.

---