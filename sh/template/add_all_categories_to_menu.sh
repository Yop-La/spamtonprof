cd [[dir]]
pwd
cmdwp term list category --field=term_id | awk '!/^1$/' | xargs -n1 cmdwp menu item add-term [[slug_menu]] category

