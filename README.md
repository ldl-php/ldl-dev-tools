#ldl-dev-tools

Utilities for LDL developers

For these tools to work correctly you must add the src/bin folder from this project to your system path

## Cloning repo

```
cd ~
git clone https://github.com/pthreat/ldl-dev-tools
```

## Important!

This project must *not* be included inside the ~/ldl-project folder

## Adding path for Linux and other \*nix os's

Open up an xterm or gnome console, or whatever terminal you have available, then run:

```
export SHELL_NAME=$(echo $SHELL | cut -d '/' -f $(echo $SHELL | awk -F"/" '{print NF}'))
export RC_FILE=".${SHELL_NAME}rc"
nano  "~/${RC_FILE}"
```

Add at the bottom of this file the following line:

Please replace /path/to/this/project/src/bin with the corresponding path to this project.

```
export PATH=$PATH:/path/to/this/project/src/bin

```

Then run:

```
. ~/$RC_FILE

```

If you are using zsh, don't forget to run rehash after you have done this.

#Commands / Utilities included

- ldl-clone-all

Will clone all ldl projects in folder ~/ldl-project

- de 

Short name for docker execute, executes commands on a docker container

- ccc 

Clears composer dependencies, deletes composer cache, deletes composer.lock and pulls in composer dependencies through composer install

Use it when installing PHP dependencies, or when an update has made into another LDL library

- git-ls-files-in-commit

Lists which files have changed on a current commit, optionally takes an argument which lets you specify a commit hash to see which
files have changed on a particular commit.

Use it when you have modified lots of files and you have commited your changes and want to list which files you have commited on the current
commit or in a particular commit

- git-update-branch

Use it when you want to discard all local changes and start fresh

Creates a temporary branch from the current branch, deletes current branch, and pulls in latest changes
This command is used to make sure that you are up to date with the latest changes

- git-rebase 

Use it when you need to pull in changes from master
Stashes changes if any, resets current branch to HEAD, checks out master, pulls master, checks out previous branch, applies stashed changes
