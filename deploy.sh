#! /bin/bash
set -o errexit -o nounset

echo "Go to git directory"
pushd `dirname $0`

DEFAULT_BRANCH=release
branch=${1:-$DEFAULT_BRANCH}

echo "git pull origin $branch"
git pull origin $branch

echo "ant quick-build"
ant quick-build

echo "Delete theme ida"
rm -r /usr/local/vufind2/themes/ida
echo "Delete theme genderbib"
rm -r /usr/local/vufind2/themes/genderbib
echo "Delete theme meta"
rm -r /usr/local/vufind2/themes/meta
echo "Delete theme meta-bootstrap3"
rm -r /usr/local/vufind2/themes/meta-bootstrap3
echo "Delete module Ida"
rm -r /usr/local/vufind2/module/Ida/

echo "Copy all to vufind"
cp -R themes module ida languages solr /usr/local/vufind2/

echo "Clear language cache"
rm -r /usr/local/vufind2/local/cache/languages/*

#echo "Restart Solr"
#pushd $VUFIND_HOME
#./vufind.sh restart
echo "Deployment successful. Remember to restart Solr, if schema.xml was modified!"

popd
