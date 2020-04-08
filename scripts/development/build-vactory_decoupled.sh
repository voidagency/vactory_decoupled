#!/usr/bin/env bash
# Move up 3 levels since we are in vactory_decoupled/script/development.
BASE_DIR="$(dirname $(dirname $(cd ${0%/*} && pwd)))"

COMPOSER="$(which composer)"
COMPOSER_BIN_DIR="$(composer config bin-dir)"
DOCROOT="web"

# Define the color scheme.
FG_C='\033[1;37m'
BG_C='\033[42m'
WBG_C='\033[43m'
EBG_C='\033[41m'
NO_C='\033[0m'

echo -e "\n"
if [ $1 ] ; then
  DEST_DIR="$1"
  echo $1
else
  DEST_DIR="$( dirname $BASE_DIR )/test_vactory_decoupled"
  echo -e "${FG_C}${WBG_C} WARNING ${NO_C} No installation path provided.\nVactory will be installed in $DEST_DIR."
  echo -e "${FG_C}${BG_C} USAGE ${NO_C} ${0} [install_path] # to install in a different directory."
fi
DRUSH="$DEST_DIR/$COMPOSER_BIN_DIR/drush"

echo -e "\n\n\n"
echo -e "\t********************************"
echo -e "\t*   Installing Vactory Decoupled    *"
echo -e "\t********************************"
echo -e "\n\n\n"
echo -e "Installing to: $DEST_DIR\n"

if [ -d "$DEST_DIR" ]; then
  echo -e "${FG_C}${WBG_C} WARNING ${NO_C} You are about to delete $DEST_DIR to install Vactory Decoupled in that location."
  rm -Rf $DEST_DIR
  if [ $? -ne 0 ]; then
    echo -e "${FG_C}${EBG_C} ERROR ${NO_C} Sometimes drush adds some files with permissions that are not deletable by the current user."
    echo -e "${FG_C}${BG_C} EXECUTING ${NO_C} sudo rm -Rf $DEST_DIR"
    sudo rm -Rf $DEST_DIR
  fi
fi

# update composer
$COMPOSER self-update

echo "-----------------------------------------------"
echo " Downloading Vactory Decoupled using composer "
echo "-----------------------------------------------"
echo -e "${FG_C}${BG_C} EXECUTING ${NO_C} $COMPOSER create-project voidagency/vactory-decoupled-project ${DEST_DIR} --stability dev --no-interaction\n\n"
php -d memory_limit=-1 $COMPOSER create-project voidagency/vactory-decoupled-project ${DEST_DIR} --stability dev --no-interaction --no-install
if [ $? -ne 0 ]; then
  echo -e "${FG_C}${EBG_C} ERROR ${NO_C} There was a problem setting up Vactory Decoupled using composer."
  echo "Please check your composer configuration and try again."
  exit 2
fi

cd ${DEST_DIR}
$COMPOSER config repositories.vactory_decoupled path ${BASE_DIR}
$COMPOSER require "void/vactory_decoupled:*" "phpunit/phpunit:^6" --no-progress

cd $DOCROOT
echo "-----------------------------------------------"
echo " Installing Vactory Decoupled for local usage "
echo "-----------------------------------------------"
echo -e "${FG_C}${BG_C} EXECUTING ${NO_C} $DRUSH site-install --verbose --yes --db-url=$SIMPLETEST_DB --site-mail=admin@localhost --account-mail=admin@localhost --site-name='Vactory Decoupled Demo' --account-name=admin --account-pass=admin\n\n"
$DRUSH site-install --verbose --yes --db-url=$SIMPLETEST_DB --site-mail=admin@localhost --account-mail=admin@localhost --site-name='Vactory Decoupled Demo' --account-name=admin --account-pass=admin;

if [ $? -ne 0 ]; then
  echo -e "${FG_C}${EBG_C} ERROR ${NO_C} The Drupal installer failed to install Vactory Decoupled."
  exit 3
fi

echo -e "${FG_C}${BG_C} EXECUTING ${NO_C} $DRUSH en -y recipes_magazin contentajs\n\n"
//$DRUSH en -y recipes_magazin contentajs

echo -e "\n\n\n"
echo -e "\t********************************"
echo -e "\t*    Installation finished     *"
echo -e "\t********************************"
echo -e "\n\n\n"