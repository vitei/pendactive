## Description

Pendactive is a recursive task management tool that allows individuals or
teams organize and assign tasks hierarchically. A live demonstration can be
seen at [pendactive.com](http://pendactive.com)

## Requirements

* PHP4+
* MySQL

## Installation

Copy the entire directory into your httpdocs folder and create a config file:

```
$ cp php/config-default.php php/config.php
```

You will need to define the following strings in php/config.php:

```
define(CONFIG_DB_NAME,          '');    // database name
define(CONFIG_DB_HOSTNAME,      '');    // database hostname
define(CONFIG_DB_USER,          '');    // database username
define(CONFIG_DB_PASSWORD,      '');    // database password
define(CONFIG_DATADIR,          '');    // path to read/writable data directory 
```

Most of that is quite self explanatory, basically you just need an empty MySQL
database sitting somewhere on a server. The last string, CONFIG_DATADIR is the
full path of a read/writable directory used for storing image files. This is
can be anywhere but it is recommended that it sits outside of your httpdocs
directory. You may also have to edit PHP's default open_basedir in order to
allow it access to that directory.

Next, in a web browser open the following page to initialize the database:

```
setup/install.php
```

And thats it! 

