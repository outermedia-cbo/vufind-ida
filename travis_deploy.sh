#!/bin/bash

set -o errexit -o nounset

echo "This commit was made against the $TRAVIS_BRANCH."
if [ "$TRAVIS_BRANCH" == "stage" ]
then
  echo "Deploying to Stage"
  export DEPLOY_PORT=2223
else
  echo "Deploying to Dev"
  export DEPLOY_PORT=2222
fi

# Add Server to known_hosts manually because variables are not evaluated in addons phase.
echo Add server to known hosts
ssh-keyscan -t $TRAVIS_SSH_KEY_TYPES -p $DEPLOY_PORT -H $DEPLOY_HOST 2>&1 | tee -a $HOME/.ssh/known_hosts

## Fetch VuFind and build package
#curl http://heanet.dl.sourceforge.net/project/vufind/VuFind/2.3.1/vufind-$VUFIND_VERSION.tar.gz > vufind-$VUFIND_VERSION.tar.gz
#tar xzf vufind-$VUFIND_VERSION.tar.gz
#cp -R themes module ida languages solr vufind-$VUFIND_VERSION/

# SCP to Server
echo Prepare key
chmod 0600 ssh_ida_travis_rsa
#scp -C -r -i ssh_ida_travis_rsa -P 2222 -p vufind-$VUFIND_VERSION/* $DEPLOY_USER@$DEPLOY_HOST:$DEPLOY_PATH
#scp -C -q -r -i ssh_ida_travis_rsa -P 2222 -p themes module ida languages solr $DEPLOY_USER@$DEPLOY_HOST:$DEPLOY_PATH
echo RSYNC to server
rsync -az -e "ssh -p $DEPLOY_PORT -i ssh_ida_travis_rsa" themes module ida languages solr $DEPLOY_USER@$DEPLOY_HOST:$DEPLOY_PATH