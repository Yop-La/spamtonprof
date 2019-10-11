# admin email
email="[[email]]"

# local url login
url="[[url]]"

# admin login
admin="[[username]]"

# path to install your WPs
pathtoinstall="[[pathtoinstall]]"

# path to plugins.txt
pluginfilepath="~/path/to/wippy/plugins.txt"

dbname="[[dbname]]"
dbpass="[[dbpass]]"

wptitle="[[wptitle]]"

host="[[host]]"

alias proj="cd $pathtoinstall"



cmdwp core download --locale=fr_FR --force
cmdwp core version
cmdwp core config --dbhost=$host --dbname=$dbname --dbuser=$dbname --dbpass=$dbpass --skip-check 

# install
cmdwp core install --url=$url --title=$wptitle --admin_user=$admin --admin_email=$email --admin_password=$dbpass
