# GitBareCLOC
*Simple PHP script to count lines of code from a Git bare repository* 

![Screenshot](https://cloud.githubusercontent.com/assets/15333057/19784463/e40df008-9c63-11e6-896d-908bd001feff.png)

Introduction
------------
I was looking for something like this out there, but found nothing that I like or actually works on my git server, where only bare repositories are, so I start coding and end up with this little script.

This small program is intended to be used on bare repositories and uses a configuration file, reads the contents of different repositories to display a document in HTML with the lines of code and other information.

It also allows the execution of external programs to generate more information in HTML.
I'm using as example gitinspector to generate extra statistical information from the repositories, but others can be setup on the setting files.

Because this program is for my internal use and have no external access, I'm using the PHP standalone server, that does the job extremely well, my server is a Debian Jessie Virtual Machine that only has 1 CPU and 512mb of ram. But you can use any other server that you want, as long as that can execute PHP and has access to the repositories/file system.

Requeriments
------------

 - I running this script with an atom CPU, 512mb of RAM, but less memory may be also possible because it never bypass 90mb of use.
 - Runs on PHP cli 5.6 or better.
 - (optional) <a href="https://github.com/ejwa/gitinspector">gitinspector</a> (tested with 0.4.4)

Installation
------------

 1. **Download or clone the files into an empty folder** on the desired system.
I only tested this with bare repositories, but in theory, normal repositories are easier to use, so it might be able to work there too but I never tested it.
The directory needs to have read and write access to the process that executes the script.
 2. (optional) If you want to use the PHP standalone server, **edit runServer.sh**, this file initializes the server in the correct directory, you can use that or create your own.
 3. **Edit settings.conf**
This file has some required values used by the program and can (or needs to) be modified to reflect the running environment and the desired options, every element is requested and missing ones will error out with undesired behavior.
The Values are easy to set in pairs separated by a double colon and terminated by a linefeed
ex:
key1: value
key2: value2
any line beginning with # will be ignored.
The settings are self explanatory:
	 - **ignoreFiles** comma separated list of files that will be ignored when counting the lines of code, example reference or data files.
	 - **ignoreExtensions** comma separated list of extensions that will be ignored when counting the lines of code, example binary files and other non code files.
	 - **repositories** a comma separated list of directories where the repositories are located, if you have a directory with many repositories you can specify /directory/with/repos/* to add all of them at once, and as many as you need, example: /dir1/\*,/some/repo/,/more/repos/here/\*
	 - **additionalInformation** command line to gather extra HTML information, you can change this with other you wish, the *[repo]* string will be replaced with the repository directory that is getting parse when the program is running.
The command line will be executed inside every repository.
	 - **refresh** The program is set to refresh the information every one hour (3600 seconds) you can change this with any amount of seconds you wish.

Usage
-----

When you get the server running, just simply open a browser and go to the server URL, the first time (and every refresh) will take a little while depending on the number of repositories, and the system speed.
If you wish to force a refresh just pass ?force=1 in the command line.
If no additional information command is set, the program will just display the count of lines of code only wihtout the link to the extra information.
