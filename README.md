Featurebranch
========================

When using git branches for separate features of web applications it is
always handy to have possibility to spin virtual hosts from branches in order
to do testing before merging into main branch.

This Symfony based project is aimed to be middleware between git repo and
Continuous integration server that will build hosts from git branches
and update them automatically when detects commits to those branches.

This app has an url that can be triggered in post-receive hook of git. All will
fetch these commits and check what branch has been updated.

Installation
========================

As this is symfony app you need to run composer install in order to pull all
dependencies to vendor folder.

We expect this app, jenkins and server where we deploy all sites to be the same
physical server.

Additionally you need to install curl php extension and install phing 
(http://www.phing.info/).

pear install VersionControl_Git-alpha
