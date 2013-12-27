Punch!
======
		
Punch is a server-based timer. A digital punch clock, if you will. Its PHP/MySQL backend ensure you never lose your timings in case of a crash or accidental closing of your browser. I've been running it on my localhost via a Chrome application shortcut and using it for over three years to record my billable hours.

![Punch in action](http://i.imgur.com/K7o1n.jpg)

This script is by no means perfect and not even close to elegant. I like to put it in the "Hey, it works!" category.

Features
--------

* It works.
* Pause/continue.
* Time notation: start / stop / elapsed / decimal.
* Since it's server based, the browser window doesn't need to be open for the timer to run.

Installation
------------

1. Fire up phpMyAdmin (or whatever client you use), create a database and import punch.sql.
2. Open index.php and supply the database credentials on top.
3. Open a browser and go to wherever your script resides.

To do
-----

1. Add/remove tabs button.
1. Housecleaning.
2. Needs a little lovin' in the icon dep.
