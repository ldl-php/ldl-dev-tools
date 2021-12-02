#!/bin/bash

export SCRIPT_DIR="$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )";
export LOG_DIR="${SCRIPT_DIR}/log"
export GITHUB_TOKEN=${GITHUB_TOKEN}
export ENV_FILE="${SCRIPT_DIR}/../.env"

cd $SCRIPT_DIR;

source ./usr/share/ldl_bash_utils

[[ ${UID} -ne "0" ]] && stdout_error 'You must be root to run this command! Please run it again as root or through sudo' && exit 1;

export ROOT_DIR=$(pwd -P)

cd $SCRIPT_DIR;

export LOCK_FILE=".docker-compose-installer"
export GITHUB_TOKEN_FILE="${SCRIPT_DIR}/github/token"
export EDIT_USER=0
export EDIT_GROUP=0

if [[ -f ${LOCK_FILE} ]]
then
    stdout_warning "Seems like a previous install took place, do you wish to continue? (y/n)"
    read opt
    [[ "$opt" != "y" ]] && echo "Ok, bailing out!" && exit 1
fi

which docker &>/dev/null
if [[ $? -gt 0 ]] ; then
   stdout_error "Please install docker first!"
   exit 1
else
   stdout_ok "Docker is installed ..."
fi

which docker-compose &>/dev/null
if [[ $? -gt 0 ]] ; then
   stdout_error "Please install docker-compose first!"
    exit 1
else
   stdout_ok "Docker compose is installed ..."
fi

if [[ ! -f "${GITHUB_TOKEN_FILE}" ]]; then
   stdout_warning "In order for me to clone LDL repositories I need a github token"

   echo -e "\n########################################################";
   echo -e "This token needs to have the following permissions:"
   echo -e "########################################################\n";

   stdout_ok "repo:status"
   stdout_ok "repo_deployment"
   stdout_ok "public_repo"
   stdout_ok "repo:invite"
   stdout_ok "security_events"
   stdout_ok "read:org\n"

   echo -e "If you don't know how to do this, open the following link:\n"
   echo -e "https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/creating-a-personal-access-token\n"

   while [ -z "$GITHUB_TOKEN" ]; do
      read -p "Please enter your github token here:" GITHUB_TOKEN
   done

   echo "${GITHUB_TOKEN}" > ${GITHUB_TOKEN_FILE}
fi

if [[ ! -f ${ENV_FILE} ]]; then
   stdout_warning "I need to know which is the user you are going to use to edit files"
   stdout_warning "This is so you can edit files from within your machine"
   export EDIT_USER=$(getValidUser)
   export EDIT_GROUP=$(getUserGroup ${EDIT_USER})

   echo "EDIT_USER=${EDIT_USER}" > ${ENV_FILE}
   echo "EDIT_GROUP=${EDIT_GROUP}" >> ${ENV_FILE}
else
   source ${ENV_FILE}
fi

if [[ -z $(docker ps -q -f "name=${DOCKER_CONTAINER_NAME}") ]]; then
   docker-compose stop &> /dev/null&
   spinner "Stopping docker container ..."
   echo ""
fi

docker container inspect ${DOCKER_CONTAINER_NAME} &> /dev/null
if [[ $? -eq 0 ]]; then 
   docker rm ldl-dev &> /dev/null&
   spinner "Removing docker container ..."
   echo ""
fi

[[ -d "${SCRIPT_DIR}/${REPO_FOLDER}" ]] && rm -rf ${SCRIPT_DIR}/${REPO_FOLDER} &> /dev/null

mkdir ${SCRIPT_DIR}/${REPO_FOLDER}
chown -R ${EDIT_USER}:${EDIT_GROUP} ${SCRIPT_DIR}/${REPO_FOLDER}

docker-compose build --no-cache --pull &> ${LOG_DIR}/docker-build.log&
spinner "Building docker container, please wait (this takes time!) ..."
echo ""

stdout_ok "Creating lock file ..."
touch ${LOCK_FILE}

stdout_ok "Running docker-compose up ..."
cd ..
docker-compose up
