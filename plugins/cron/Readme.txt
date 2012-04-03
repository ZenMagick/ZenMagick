A cron package for ZenMagick, based on code and ideas from
pseudo-cron (v1.3) by Kai Blankenhorn http://www.bitfolge.de/pseudocron


Introduction
============
This plugin allows to execute scheduled commands. The frequencey can be configured
in various ways. 
cron is a system daeomon on *nix systems. For more information about the cron daemon
and the crontab configuration file see:
http://man.cx/cron(8)
http://man.cx/crontab(5)
 

Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Configure as required using the Plugin Manager and the plugin admin page.


Configuration
=============
a) plugin
The plugin itself can be configured either from the Plugin Manager or via the plugins
own admin page (some options might only be available there).
Possible options:
* trigger
  This configured if/how execution of cron jobs is triggered.
  Available options are:
  + hidden image:
    This option will inject a img tag in storefront pages (all or selected). Requesting
    the image (transparent 1x1 px) will then trigger execution.
    The advantage here is that this will not affect the performance of the original requested
    page as execution is handled by a separate request.

* trigger pages
  For the 'hidden image' option a comma separated list of pages can be configured that should be used.
  If empty, all pages will be used.

* missed run policy
  This controls what happens if, for some reason, the execution of jobs has been missed. (This might happen when
  using the image trigger and no activity on the storefront, or when only manually runnning jobs).
  Options are ignore or catch-up. If ignored, jobs are only run when the are actually ready at the time the
  plugin is triggered. If catch-up is selected, jobs that are not ready, but have missed at least one run, will
  be run. 
  Please note that the 'catch-up' option will result in all jobs being run once after being configured (as no 
  history exists).


The crontab.txt file
====================
As explained above, the file to configure jobs is called crontab. The actual file is ../cron/etc/crontab.txt.
In contrast to a *proper* crontab file there is no user column. Also, the actual job/task is assumed to be the name
of a class that implements the included ZMCronJob interface.

If scheduled, an instance of that class will be created and the execute() method called.


cronhistory.txt
===============
To keep track of previous executions a file ../cron/etc/cronhistory.txt will be created by the plugin. Please make
sure that the etc folder is writeable by your webserver.


Creating new jobs
=================
A cron job as defined by this plugin is the name of a class (accessible via class loader) that implements the included
interface ZMCronJob.
There is a sample class included in the jobs folder to illustrate this.


Configuring a system cron job to use PHP CLI
============================================
On *nix OS the systems cron job may be used to run ZenMagick cron jobs. This actually creates a 
situation where a cron process is executing another (the plugin).
The advantage here is that this doesn't affect storefront performance at all. Also, only a single
system cron job is required. All actual ZenMagick cron jobs can then be configured in the plugins
crontab.
You can also execute cron jobs manually on the command line:
[path-to-php]/php [path-to-zen-cart]/zenmagick/plugins/request/cron/cron.cli

NOTE: The PHP CLI file does *not* have a .php extension to hide it from the class loader.

There should be only one cron job required that triggers the plugin once every minute.

Example (you'll have to adjust both the php executable path and the actual file path):

* * * * * /usr/bin/php -f /home/htdocs/zen-cart/zenmagick/plugins/request/cron/cron.cli


Birthday email job
==================
This job, when run once a day, will send an email to every customer with a dob of today's date.
Furthermore, there are two settings to tweak things:

'plugins.cron.jobs.birthday.offset'
This may be used to generate emails before/after the actual birthday. Valid values are [+|-][0-9]* or 
an empty string for the actual date.

Examples:
// send emails one day early
storefront,admin:
  settings:
    plugins:
      jobs:
        birthday:
          offset: -1

// send emails two days late
storefront,admin:
  settings:
    plugins:
      jobs:
        birthday:
          template: birthday.php
          offset: +2

NOTE: The sign (+/-) is mandatory.


'plugins.cron.jobs.birthday.template'
The name of the email template used for the email. The template is expected to be in the
active theme's views/emails folder like any other email.
Default value is 'birthday'.

crontab:
One possible crontab entry to run the job once a day could be:

0    5    0    *    *        ZMBirthdayEmailCronJob  # every day at 5 am

email template
The template will have access to a variable $account as the customer to process.

Example template:
Congratulations, <?php echo $account->getFullName() ?>,
it's your birthday and <?php echo $settingsService->get('storeName') ?> would like to wish you all the best!
Have fun,
The team at <?php echo $settingsService->get('storeName') ?>
