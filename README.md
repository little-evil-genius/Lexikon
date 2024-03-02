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
- lexicon_edit_externallink
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
- lexicon_menu_externallink_option
- lexicon_menu_subentries
- lexicon_modcp
- lexicon_modcp_bit
- lexicon_modcp_edit
- lexicon_modcp_nav
- lexicon_search_results
- lexicon_search_results_bit

# Neue Variable
- header: {$lexikon_newentry} und {$menu_lexicon}
- modcp_nav_users: {$nav_lexicon}

# Neues CSS - lexicon.css
Es wird automatisch in jedes bestehende und neue Design hinzugefügt. Man sollte es einfach einmal abspeichern - auch im Default. Sonst kann es passieren, dass es bei einem Update von MyBB entfernt wird.
<blockquote>#lexicon {
	width: 100%;
    display: flex;
    gap: 20px;
    justify-content: space-between;
    align-items: flex-start;    
}

#lexicon #navigation {
	width: 20%;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    background: #fff;
    border: 1px solid #ccc;
    padding: 1px;
    -moz-border-radius: 7px;
    -webkit-border-radius: 7px;
    border-radius: 7px;   
}

#lexicon #navigation .navigation-headline {
	min-height: 50px;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    text-transform: uppercase;
    text-align: center;
    padding: 0 5px;
    box-sizing: border-box;
    background: #0066a2 url(../../../images/thead.png) top left repeat-x;
    color: #ffffff;
}

#lexicon #navigation .navigation-headline:first-child {
    -moz-border-radius-topleft: 6px;
    -moz-border-radius-topright: 6px;
    -webkit-border-top-left-radius: 6px;
    -webkit-border-top-right-radius: 6px;
    border-top-left-radius: 6px;
    border-top-right-radius: 6px; 
}

#lexicon #navigation .navigation-headline:first-child a:link,
#lexicon #navigation .navigation-headline:first-child a:visited,
#lexicon #navigation .navigation-headline:first-child a:active,
#lexicon #navigation .navigation-headline:first-child a:hover {
    margin-left: 0;
}

#lexicon #navigation .navigation-headline a:link,
#lexicon #navigation .navigation-headline a:visited,
#lexicon #navigation .navigation-headline a:active,
#lexicon #navigation .navigation-headline a:hover {
	color: #ffffff;
	margin-left: 5px;
}

#lexicon #navigation .navigation-item {
    min-height: 25px;
    width: 100%;
    margin: 0 auto;
    padding: 5px 20px;
    display: flex;
    align-items: center;
    box-sizing: border-box;
    border-bottom: 1px solid #ddd;
    background: #f5f5f5;
}

#lexicon #navigation .navigation-item:last-child {
    -moz-border-radius-bottomright: 6px;
    -webkit-border-bottom-right-radius: 6px;
    border-bottom-right-radius: 6px;
    -moz-border-radius-bottomleft: 6px;
    -webkit-border-bottom-left-radius: 6px;
    border-bottom-left-radius: 6px;
}

#lexicon #navigation .navigation-subitem {
	min-height: 25px;
	width: 100%;
	margin: 0 auto;
	padding: 0 20px 0px 20px;
	display: flex;
	align-items: center;
	box-sizing: border-box;
	border-bottom: 1px solid #ddd;
	background: #f5f5f5;
}

#lexicon #navigation .navigation-subitem i {
	font-size: 11px;
	padding-top: 1px;
}

#lexicon #navigation .navigation-externallink-option {
    width: 100%;
    text-align: right;
}

#lexicon #navigation .navigation-search {
    width: 100%;
    margin: 0 auto;
    padding: 10px 0;
    display: flex;
    align-items: center;
    box-sizing: border-box;
    border-bottom: 1px solid #ddd;
    background: #f5f5f5;
    justify-content: center;
}

#lexicon #navigation .navigation-search input.textbox {
    width: 68%;
}

#lexicon .lexicon-entry {
	width: 80%;
    box-sizing: border-box;
    background: #fff;
    border: 1px solid #ccc;
    padding: 1px;
    -moz-border-radius: 7px;
    -webkit-border-radius: 7px;
    border-radius: 7px;    
}

#lexicon .lexicon-entry .entry-headline {
	height: 50px;
    width: 100%;
    font-size: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    text-transform: uppercase;
    background: #0066a2 url(../../../images/thead.png) top left repeat-x;
    color: #ffffff;
    -moz-border-radius-topleft: 6px;
    -moz-border-radius-topright: 6px;
    -webkit-border-top-left-radius: 6px;
    -webkit-border-top-right-radius: 6px;
    border-top-left-radius: 6px;
    border-top-right-radius: 6px; 
}


#lexicon .lexicon-entry .entry-subline {
	text-align: right;
	padding-right: 10px;
	padding-top: 5px;
	background: #f5f5f5;
}

#lexicon .lexicon-entry .entry {
    background: #f5f5f5;
	padding: 20px 40px;
	text-align: justify;
	line-height: 180%;   
    -moz-border-radius-bottomright: 6px;
    -webkit-border-bottom-right-radius: 6px;
    border-bottom-right-radius: 6px;
    -moz-border-radius-bottomleft: 6px;
    -webkit-border-bottom-left-radius: 6px;
    border-bottom-left-radius: 6px; 
}

#lexicon .lexicon-entry .entry.content {
    -moz-border-radius-bottomright: 0;
    -webkit-border-bottom-right-radius: 0;
    border-bottom-right-radius: 0;
    -moz-border-radius-bottomleft: 0;
    -webkit-border-bottom-left-radius: 0;
    border-bottom-left-radius: 0;
}

#lexicon .lexicon-entry .content-bit {
	padding: 0 40px 40px 40px;
	display: flex;
	flex-wrap: wrap;
	justify-content: space-between;
	gap: 20px;
	background:#f5f5f5;
    -moz-border-radius-bottomright: 6px;
    -webkit-border-bottom-right-radius: 6px;
    border-bottom-right-radius: 6px;
    -moz-border-radius-bottomleft: 6px;
    -webkit-border-bottom-left-radius: 6px;
    border-bottom-left-radius: 6px; 
}

#lexicon .lexicon-entry .content-bit .content-letter {
	width: 45%;     
}

#lexicon .lexicon-entry .content-bit .content-letter .content-item {
	margin-bottom: 5px;    
}

#lexicon .lexicon-entry .content-bit .content-letter .content-item .content-item-cat {
	font-size:0.7em;
}

#lexicon .lexicon-entry .lexicon_search_results {
    margin-bottom: 10px;
}</blockquote>

# Importieren von Daten aus Ales Wiki Plugin:
1. Lexikon installieren. Wiki von Ales <b>NICHT</b> deinstallieren
2. phpmyadmin (Datenbank) öffnen und auf den Reiter "SQL" klicken
3. Query für die Kategorien ausführen: <b>ACHTUNG evt. Tableprefix anpassen!</b>
<blockquote>
INSERT INTO `mybb_lexicon_categories` (cid, categoryname, sort) SELECT cid,category,sort FROM `mybb_wiki_categories`
</blockquote>
4. Query für die Einträge ausführen: <b>ACHTUNG evt. Tableprefix anpassen!</b>
<blockquote>
INSERT INTO `mybb_lexicon_entries` (eid, cid, linktitle, link, externallink, title, entrytext, sort, parentlist, uid, accepted) SELECT wid,cid,linktitle,link,"",title,wikitext,sort,0,uid,accepted FROM `mybb_wiki_entries`
</blockquote>


# Demo
<img src="https://stormborn.at/plugins/lexikon_mainpage2.png">
<img src="https://stormborn.at/plugins/lexikon_inhaltsverzeichnis2.png">
<img src="https://stormborn.at/plugins/lexikon_entry2.png">
<img src="https://stormborn.at/plugins/lexikon_catadd2.png">
<img src="https://stormborn.at/plugins/lexikon_entryadd2.png">
<img src="https://stormborn.at/plugins/lexikon_search2.png">
<img src="https://stormborn.at/plugins/lexikon_modcp2.png">
