#!/bin/sh

# this is needed to run python in headless process.
export PYTHONIOENCODING=utf-8

# Script to start the PHP standalone server (you can use others like Apache if you wish)
php -S 0.0.0.0:8000 -t /home/administrator/GitBareCLOC/
