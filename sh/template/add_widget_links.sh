cd [[wppath]]
pwd
# ajout de la widget vers les sous domaines
cmdwp widget delete custom_html-1
cmdwp widget delete text-2
cmdwp widget add custom_html sidebar-right 1 --title="Partenaires" --content='[[links]]'
