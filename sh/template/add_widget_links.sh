cd [[wppath]]

# ajout de la widget vers les sous domaines
cmdwp widget delete custom_html-1
cmdwp widget delete text-2
cmdwp widget add custom_html colormag_right_sidebar 1 --title="Partenaires" --content='[[links]]'
