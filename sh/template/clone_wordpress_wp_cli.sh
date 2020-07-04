
# path to install your WPs
pathtoinstall="[[pathtoinstall]]"


#wp path to clone
pathtoclone="[[pathtoclone]]"

# admin email
email="[[email]]"

# local url login
url="[[url]]"

# local url login
cloneurl="[[cloneurl]]"


# admin login
admin="[[username]]"

# path to install your WPs
pathtoinstall="[[pathtoinstall]]"

mkdir $pathtoinstall;

dbname="[[dbname]]"
dbpass="[[dbpass]]"

wptitle="[[wptitle]]"

host="[[host]]"



#export db
cd $pathtoclone
cmdwp db export ~/db/production.sql

# clone wp directory
cd ~/tempo
pwd
cd $pathtoinstall
pwd
rm -R ./*

pwd
ls
cp -Rp $pathtoclone/* . 

# fill wp-config.php
cmdwp config set DB_USER "$dbname"
cmdwp config set DB_NAME "$dbname"
cmdwp config set DB_PASSWORD "$dbpass"
cmdwp config set WP_CACHE true


#import database
cmdwp db import ~/db/production.sql

cmdwp search-replace $url $cloneurl 

cmdwp user update 1 --user_pass=$dbpass 
cmdwp user update 1 --user_login=$admin
cmdwp user update 1 --display_name=$admin
cmdwp user update 1 --user_email=$email
 



#htaccess pour éviter les 404
cp ~/wp_template/.htaccess .


