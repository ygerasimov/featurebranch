[![Build Status](https://travis-ci.org/podarok/featurebranch.png?branch=master)](https://github.com/podarok/featurebranch)

Featurebranch
=========================

When using git branches for separate features of web applications it is
always handy to have possibility to spin virtual hosts from branches in order
to do testing before merging into main branch

This Symfony based project is aimed to be middleware between git repo and
Continuous integration server that will build hosts from git branches
and update them automatically when detects commits to those branches.

This app has an url that can be triggered in post-receive hook of git. All will
fetch these commits and check what branch has been updated.

Admin credentials for the application are admin / propeople. If you would like
to change them please see app/config/config.yml file.

Installation
========================

As this is symfony app you need to run composer install in order to pull all
dependencies to vendor folder.

We expect this app, jenkins and server where we deploy all sites to be the same
physical server.

Additionally you need to install curl php extension and install phing 
(http://www.phing.info/).

## Install VM with this tool and real life Drupal project

### Vagrant with scripts from puphpet.com

Go to puphpet.com and upload config.yaml file from puphpet folder. Download
the vagrant configuration files and start the VM. If generating vagrant files
after uploading config.yaml file doesn't work, generate any configuration but
then replace puphpet/config.yaml in vagrant config files with config file from
this repo. If you do not use vagrant, use manual installation steps to set up
the server.

### Manual installation

Install apache and enable vhost_alias mod

```no-highlight
apt-get install apache
a2enmod vhost_alias
service apache2 restart
```

Set up one virtual host for control application (this symfony app). Replace with
your host name value of ServerName option.

```no-highlight
<VirtualHost *:80>
  ServerName 192.168.56.101.xip.io

  ## Vhost docroot
  DocumentRoot "/var/www/control/web"
  <Directory "/var/www/control/web">
    Options Indexes FollowSymLinks MultiViews
    AllowOverride All
    Order allow,deny
    Allow from all
  </Directory>

  ## Logging
  ErrorLog "/var/log/apache2/control_error.log"
  ServerSignature Off
  CustomLog "/var/log/apache2/control_access.log" combined
</VirtualHost>
```

Next set up wildcard virtual host (it uses vhost_alias mod). All projects will
be deployed under /var/www/project/* folder.

```no-highlight
<VirtualHost *:80>
  ServerName 192.168.56.101.xip.io
  ServerAlias *.192.168.56.101.xip.io
  VirtualDocumentRoot /var/www/project/%1
  LogLevel warn
  <Directory "/var/www/project/*">
    AllowOverride All
    Options -Indexes
    Order allow,deny
    Allow from all
  </Directory>
</VirtualHost>
```

Install php with all required modules.

```no-highlight
apt-get install php5 php5-curl php5-mysql php5-gd
```

Install mysql

```no-highlight
apt-get install mysql-server
```

### Drush

You can install drush via apt

```no-highlight
apt-get install drush
```

### Phing

From http://www.phing.info/trac/wiki/Users/Installation

```no-highlight
pear channel-discover pear.phing.info
pear install --alldeps phing/phing
```

### Jenkins

From https://wiki.jenkins-ci.org/display/JENKINS/Installing+Jenkins+on+Ubuntu

```no-highlight
wget -q -O - http://pkg.jenkins-ci.org/debian/jenkins-ci.org.key | sudo apt-key add -
sudo sh -c 'echo deb http://pkg.jenkins-ci.org/debian binary/ > /etc/apt/sources.list.d/jenkins.list'
sudo apt-get update
sudo apt-get install jenkins
```

Enable phing plugin in Jenkins through UI.

You can configure users and security for Jenkins

From https://wiki.jenkins-ci.org/display/JENKINS/Standard+Security+Setup

![Working example for admin/Anonymous users](https://raw.github.com/ygerasimov/featurebranch/master/jenkins_security.png)
This example use no authentification for featurebranch jobs creation process via Anonymous jenkins user.

After adding admin user permissions you can create the user itself via ***login*** link at top-right of the screen.

### Set up bare git repository

Our control application will need main repository to pull our drupal project
from. You can use either internal repo (created on the server itself) or some
external one. In both cases you need to install git.

Instructions below are for creating internal bare repo. In example below we
set it up in /var/git/repo.git

```no-highlight
apt-get install git
mkdir -p /var/git/repo.git
cd /var/git/repo.git
git --bare init
```

Main thing about repository -- it should have post-receive hook customized. In
this hook we need to do get request to http://192.168.56.101.xip.io/gitupdate
(host to be adjusted according to your settings). You can set this hook for
external repositories like on github.

For the bare repo we need to add executable file in hooks folder.

```no-highlight
cd /var/git/repo.git/hooks
echo '#!/bin/sh' > post-receive
echo 'wget -O - -q -t 1 http://192.168.56.101.xip.io/gitupdate' >> post-receive
chmod a+x post-receive
```

### Deploy the application

Puppet scripts set up to have virtual host for the application in
/var/www/control so you can clone this repo there. Next step is to download
composer. We should be in /var/www/control folder.

```no-highlight
curl -sS https://getcomposer.org/installer | php
```

And run composer install in the root of the clone of this repo

```no-highlight
php composer.phar install
```

Create folders / set up permissions

```no-highlight
mkdir app/data
chmod a+w app/data
chmod a+w app/logs
chmod a+w app/cache
```

Now we need to configure application. Edit app/conf/config.yml file.
Adjust feature_branch_main section (in the bottom).

```no-highlight
feature_branch_main:
    apache_root: "/var/www/project"
    repo_origin: "/var/git/repo"
    work_filepath: "/var/www/control/app/data"
    ci_url: "http://192.168.56.101.xip.io:8080"
    mysql_root_login: "root"
    mysql_root_pass: "123"
```

apache_root -- where all hosts should be created. We have set up wildcard
virtual host to check this folder. So you probably don't need to change this
option.

repo_origin -- if you use external repository, change this. Make sure your
jenkins user can checkout from that repo.

work_filepath -- this can be any folder that apache can write to. We have set up
data folder with write permissions.

ci_url -- url of the Jenkins. Change it accordingly to your host and port setup.

mysql_root_login, mysql_root_pass -- credentials of the user who will create
databases for hosts.


### Windows users Cygwin installation

Before going to cygwin console You should install EasyPHP Devserver. http://www.easyphp.org/easyphp-devserver.php
We are using EasyPHP-DevServer-13.1VC9 here

Get cygwin installation program from http://www.cygwin.com/

Install basic packages and *curl, git* binaries or use *GIT bash* for a *git* from http://msysgit.github.io/

After that clone this repo into Easyphp **data/localweb/projects** folder

go to *featurebranch* folder using cygwin console and type

> *curl -sS https://getcomposer.org/installer | php*

Next - update packages for a application

> *php composer.phar update*
