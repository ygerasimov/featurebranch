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

Install VM with this tool and real life Drupal project
=======================================================

Go to puphpet.com and upload config.yaml file from puphpet folder. Download
the vagrant configuration files and start the VM. If generating vagrant files
after uploading config.yaml file doesn't work, generate any configuration but
then replace puphpet/config.yaml in vagrant config files with config file from
this repo.

Login to VM and install Jenkins.

From https://wiki.jenkins-ci.org/display/JENKINS/Installing+Jenkins+on+Ubuntu

wget -q -O - http://pkg.jenkins-ci.org/debian/jenkins-ci.org.key | sudo apt-key add -
sudo sh -c 'echo deb http://pkg.jenkins-ci.org/debian binary/ > /etc/apt/sources.list.d/jenkins.list'
sudo apt-get update
sudo apt-get install jenkins


