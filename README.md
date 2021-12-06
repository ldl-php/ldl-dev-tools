#LDL docker developer tool chain

## Requirements

First and foremost, you *must* have docker and docker-compose installed in your system

To install docker please follow the instructions for your platform stated in the following link

[Docker install instructions](https://docs.docker.com/engine/install/)

## Why docker?

We use docker in order to simplify our development process and to allow a clear communication between 
all collaborators of the project. 

Every single platform has it's quirks and we can't spend time understanding which is a particular problem you might face 
in your platform. 

This docker setup allows all collaborators to communicate in a consistent way, for example, we all know 
that the projects inside the docker setup are all located in:
 
```text
/home/ldl/projects
```

Each and every single collaborator can use whatever platform they decide to develop LDL (Linux, *BSD, Windows or OSX), 
as long as they use docker we will all be able to have a common language when we any of us faces problems.
  
**NOTE:** *If you decide to develop for LDL without this docker setup you will be on your own. This is not because we are not 
willing to help you, it's only due to the fact that particular problems of each collaborator will drain time that we 
should be using to develop the framework.*

## Starting the install

First we need to clone this project, then we start the docker setup with ldl-start:

```
cd ~
git clone https://github.com/ldl-php/ldl-dev-tools
cd ~/ldl-dev-tools
sudo ./docker/bin/ldl-start
```

**IMPORTANT**: *For running the setup you'll need super user privileges*

## Docker setup details

The setup is interactive, some questions will be prompted and you must enter correct values for it to work

**IMPORTANT:** *Failing to enter the correct values during this stage will result on a broken setup!
Please read the following sections which will explain exactly what each required input means.*

### File permissions 

Docker creates all files by default as the root user, the LDL docker setup has some tweaks in order to use a regular
user to write the project files, when installing, you will be prompted to enter a non-privileged user which is able 
to write the cloned LDL repositories. At this step, please enter the user you usually use to write code.

**IMPORTANT:** *If you enter an invalid user, you will face permission problems and will most likely not be able to write code 
for LDL.*

### Github token

To be able to clone and fork all LDL repositories into your github account, the docker setup needs a github token.

[Follow these instructions to create a GitHub token](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/creating-a-personal-access-token)

The token will be stored at the following location

```text
~/ldl-dev-tools/docker/github/token
```
IMPORTANT: The token is EXCLUDED from all git commits (set in .gitignore), it will only live in your computer.

### Recommended settings for GitHub token

- Token expiration time:

It is recommended that you set the expiration date the token to "No expiration", this is not the safest setting but it's 
a set and forget setting, else you will have problems in the future after the token expires.

**IMPORTANT**: *Be extremely careful with this token DO NOT share it with anyone, not even with main collaborators from 
the LDL project.*

### Mandatory settings for GitHub token

The docker setup requires that your token has the following permissions:

- repo
   - repo:status
   - repo_deployment
   - public_repo
   - repo:invite
   - security_events

- admin:org
   - read:org

**IMPORTANT:** Failing to set any of these permissions will most likely result on a broken setup

## Making the environment available from anywhere in your system

Open up an xterm, gitbash or whatever terminal you use, then run:

```
export SHELL_NAME=$(echo $SHELL | cut -d '/' -f $(echo $SHELL | awk -F"/" '{print NF}'))
export RC_FILE=".${SHELL_NAME}rc"
nano  "~/${RC_FILE}"
```

Add at the bottom of this file the following line:

```text
export ldl-start="sudo ~/ldl-dev-tools/bin/ldl-start $@"
```

Then run:

```
. ~/$RC_FILE

```

Finally, any time you reboot your computer and wish to collaborate, you will only need to open a terminal and run:

```text
ldl-start
```

## Commands / Utilities included in docker container

- ldl-clone

Will clone and fork all ldl projects in folder ~/ldl/projects

- ccc 

Deletes composer cache, deletes composer.lock and pulls in composer dependencies through composer install
Use it when installing PHP dependencies, or when an update has made into another LDL library.

- git-ls-files-in-commit

Lists which files have changed on a current commit, optionally takes an argument which lets you specify a commit hash to 
see which files have changed on a particular commit. This is useful when you don't remember which files you have 
modified. 

This command also takes in an argument, the first argument must be a git hash, in this case, the command
will give you a list of files modified in that particular git hash.

- git-update-branch

Use it when you want to discard all local changes and start fresh or when someone has push forced a branch. 

DETAILS: The command creates a temporary branch from the current branch, deletes current branch, and pulls in latest changes

This command is used to make sure that you are up to date with the latest changes

- git-rebase 

Use it when you need to pull in changes from master

DETAILS: Stashes changes if any, resets current branch to HEAD, checks out master, pulls master, checks out previous branch, applies stashed changes


## TODO

- Add documentation about our git branching model 
