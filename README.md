# boardinternes Lexikon
Das Plugin erweitert das Board um ein eigenes Lexikon. Das Lexikon bietet eine praktische Möglichkeit, um ein umfassendes und benutzerfreundliches Nachschlagewerk für die spezifischen Informationen zu seinem Forum an einem Ort zu sammeln. Bestimmte Usergruppen, welche im ACP festgelegt werden, können Einträge für das Lexikon erstellen und in verschiedene Kategorien einsortieren. Kategorien können ebenfalls individuell erstellt werden - von ausgewählten Gruppen. Eingereichte Einträge werden, wenn sie vom Team sind, sofort freigeschaltet. Bei Usern, die kein Zugriff auf das Mod-CP haben, kann im ACP ausgesucht werden, ob Einreichungen von ihnen vorher kontrolliert werden sollen. Der entsprechende User wird dann entweder per PN oder MyAlert über die Annahme bzw. Ablehnung informiert. Sollte das Plugin MyAlerts nicht installiert sein, werden private Nachrichten entsprechend verschickt. Bei einer Installation kann das Team in den Einstellungen aussuchen, ob ein Alert oder ein PN geschickt an den User geschickt werden soll. Genauso kann eingestellt werden, ob User ihre selbst eingereichten Einträge selbstständig bearbeiten und/oder löschen können.<br>
<br>
Das Lexikon beinhaltet auf Wunsch ein Inhaltsverzeichnis oder besser gesagt ein Glossar. Es handelt sich dabei um eine alphabetische Übersicht aller Einträge. Kategorien und Einträge können entweder nach dem angezeigten Titel im Menü oder nach einer manuellen Sortierung geordnet werden. Manche Einträge sind zu umfangreich, um sie in einen bestehenden Eintrag einzufügen, weswegen es auch möglich ist, Einträge als Untereinträge zu kennzeichnen. Man kann in das Lexikon nicht nur klassische Einträge einfügen, sondern das Menü um ein externen Link erweitern. Ein Beispiel wäre, wenn das Board eine Seite besitzt, wo User das Abschlussjahr ihrer Charaktere berechnen können, dann kann ohne groß zu suchen innerhalb der Einträge solche Links in das Menü eingefügt werden.

# Datenbank-Änderungen
hinzugefügte Tabelle:
- PRÄFIX_lexicon_categories
- PRÄFIX_lexicon_entries

# Einstellungen
- Gruppen für Kategorien
- Gruppen für Einträge
- Überprüfung von Einträgen
- Bearbeitung von Einträgen
- Löschen von Einträgen
- Benachrichtigungsystem
- Sortierung der Kategorien
- Sortierung der Einträge
- Inhaltsverzeichnis
- Untereinträge

# Neues Templatess (nicht global!) 
- lexicon_add_category
- lexicon_add_entry
- lexicon_add_sort
- lexicon_add_subentry
- lexicon_contents
- lexicon_contents_bit
- lexicon_contents_entries
- lexicon_edit_category
- lexicon_edit_entry
- lexicon_entry
- lexicon_entry_option
- lexicon_header_banner
- lexicon_header_link
- lexicon_mainpage
- lexicon_menu
- lexicon_menu_add_cat
- lexicon_menu_add_entry
- lexicon_menu_cat
- lexicon_menu_cat_option
- lexicon_menu_entries
- lexicon_menu_subentries
- lexicon_modcp
- lexicon_modcp_bit
- lexicon_modcp_edit
- lexicon_modcp_nav

# Neue Variable
- header: {$lexikon_newentry} und {$menu_lexicon}
- modcp_nav_users: {$nav_lexicon}

# Neues CSS - lexicon.css
Es wird automatisch in jedes bestehende und neue Design hinzugefügt. Man sollte es einfach einmal abspeichern - auch im Default. Sonst kann es passieren, dass es bei einem Update von MyBB entfernt wird.

# Importieren von Daten aus Ales Wiki Plugin:
1. Lexikon installieren. Wiki von Ales <b>NICHT</b> deinstallieren
2. phpmyadmin (Datenbank) öffnen und auf den Reiter "SQL" klicken
3. Query ausführen: <b>ACHTUNG evt. Tableprefix anpassen!</b>
<blockquote>
INSERT INTO `mybb_lexicon_categories` (cid, categoryname, sort) SELECT cid,category,sort FROM `mybb_wiki_categories`
INSERT INTO `mybb_lexicon_entries` (eid, cid, linktitle, link, externallink, title, entrytext, sort, parentlist, uid, accepted) SELECT wid,cid,linktitle,link,"",title,wikitext,sort,0,uid,accepted FROM `mybb_wiki_entries`
</blockquote>

# Demo
<img src="https://stormborn.at/plugins/lexikon_mainpage.png">
<img src="https://stormborn.at/plugins/lexikon_inhaltsverzeichnis.png">
<img src="https://stormborn.at/plugins/lexikon_entry.png">
<img src="https://stormborn.at/plugins/lexikon_catadd.png">
<img src="https://stormborn.at/plugins/lexikon_entryadd.png">
<img src="https://stormborn.at/plugins/lexikon_search.png">
<img src="https://stormborn.at/plugins/lexikon_modcp.png">
