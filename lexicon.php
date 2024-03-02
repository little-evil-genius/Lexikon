<?php
define('IN_MYBB', 1);
require_once './global.php';

global $db, $cache, $mybb, $lang, $templates, $theme, $header, $headerinclude, $footer, $text_options, $parser;

// PARSER - HTML und CO erlauben
require_once MYBB_ROOT."inc/class_parser.php";;
$parser = new postParser;
$text_options = array(
    "allow_html" => 1,
    "allow_mycode" => 1,
    "allow_smilies" => 1,
    "allow_imgcode" => 1,
    "filter_badwords" => 0,
    "nl2br" => 1,
    "allow_videocode" => 0
);

// SPRACHDATEI
$lang->load('lexicon');

// user is visiting the site and plugin isn't installed
if (!$db->table_exists("lexicon_entries")) {
    redirect('index.php', $lang->lexicon_redirect_uninstall);
}

// EINSTELLUNGEN
$lexicon_groups_cat_setting = $mybb->settings['lexicon_groups_cat'];
$lexicon_groups_entry_setting = $mybb->settings['lexicon_groups_entry'];
$lexicon_sort_cat_setting = $mybb->settings['lexicon_sort_cat'];
$lexicon_sort_entry_setting = $mybb->settings['lexicon_sort_entry'];
$lexicon_contents_setting = $mybb->settings['lexicon_contents'];
$lexicon_sub_setting = $mybb->settings['lexicon_sub'];
$user_edit_setting = $mybb->settings['lexicon_user_edit'];
$user_delete_setting = $mybb->settings['lexicon_user_delete'];
$user_accepted_setting = $mybb->settings['lexicon_user_accepted'];

// ACCOUNTSWITCHER
$user_id = $mybb->user['uid'];
if ($user_id != 0 AND !function_exists('accountswitcher_is_installed')) {
    // Haupt-UID
    $mainID = $db->fetch_field($db->simple_select("users", "as_uid", "uid = '".$user_id."'"), "as_uid");
    if(empty($mainID)) {
        $mainID = $user_id;
    }
    // Zusatzfunktion - CharakterUID-string
    $charas = lexicon_get_allchars($user_id);
    //hier den string bauen ich hänge hinten und vorne noch ein komma dran um so was wie 1 udn 100 abzufangen
    $charastring = ",".implode(",", array_keys($charas)).",";
} else {
    $charastring = "";
}
 
add_breadcrumb($lang->lexicon_nav_main, "lexicon.php");

// Manuelle Sortierung - Kategorien
if ($lexicon_sort_cat_setting == 1) {
    $sort_cat = "sort ASC";
} else  {
    $sort_cat = "categoryname ASC";
}

// Manuelle Sortierung - Einträge
if ($lexicon_sort_entry_setting == 1) {
    $sort_entry = "sort ASC, linktitle ASC";
} else  {
    $sort_entry = "linktitle ASC";
}

if ($lexicon_sub_setting == 1) {
    $sub_options = "AND parentlist = '0'";
} else {
    $sub_options = "";
}

//Generieren wir uns mal das Menü, welches sich Automatisch erweitert, wenn neue Einträge in der Datenbank erscheinen.
$query_menu = $db->query("SELECT * FROM ".TABLE_PREFIX."lexicon_categories
ORDER BY ".$sort_cat."
");

$menu_cat = "";
while($cat = $db->fetch_array($query_menu)){

    // Leer laufen lassen
    $category = "";
    $cid = "";

    // Mit Infos füllen
    $category = $cat['categoryname'];
    $cid = $cat['cid'];

    // Einträge der Kategorie auslesen
    $entries_query = $db->query("SELECT * FROM ".TABLE_PREFIX."lexicon_entries
    WHERE cid = '".$cid."'
    AND accepted = '1'
    $sub_options
    ORDER BY $sort_entry
    ");

    $entries = "";
    $option_buttons_cat = "";
    while($entry = $db->fetch_array($entries_query)){

        // Leer laufen lassen
        $link = "";
        $linktitle = "";
        $eid = "";
        $externallink = "";

        // Mit Infos füllen
        $link = $entry['link'];
        $linktitle = $entry['linktitle'];
        $eid = $entry['eid'];
        $externallink = $entry['externallink'];

        if ($lexicon_sub_setting == 1) {
            $subentries_query = $db->query("SELECT * FROM ".TABLE_PREFIX."lexicon_entries
            WHERE accepted = 1
            AND parentlist = '".$eid."'
            ORDER BY $sort_entry
            ");
        
            $subentries = "";
            while($subentry = $db->fetch_array($subentries_query)){

                // Leer laufen lassen
                $eid = "";
                $sublink = "";   
                $sublinktitle = "";
                $subexternallink = "";
                $option_menu_subentries = "";
    
                // Mit Infos füllen
                $eid = $subentry['eid'];   
                $sublink = $subentry['link'];       
                $sublinktitle = $subentry['linktitle'];
                $subexternallink = $subentry['externallink'];

                if($subexternallink != "") {
                    $subfulllink = $subexternallink;
                    if ($mybb->usergroup['canmodcp'] == '1') {
                        eval("\$option_menu_externallink = \"".$templates->get("lexicon_menu_externallink_option")."\";");
                    } else {
                        $option_menu_externallink = "";
                    }
                } else {
                    $subfulllink = "lexicon.php?page=".$sublink;
                    $option_menu_externallink = "";
                }

                eval("\$subentries .= \"".$templates->get("lexicon_menu_subentries")."\";");
            }
        }

        if($externallink != "") {
            $fulllink = $externallink;
            if ($mybb->usergroup['canmodcp'] == '1') {
                eval("\$option_menu_externallink = \"".$templates->get("lexicon_menu_externallink_option")."\";");
            } else {
                $option_menu_externallink = "";
            }
        } else {
            $fulllink = "lexicon.php?page=".$link;
            $option_menu_externallink = "";
        }
        
        eval("\$entries .= \"".$templates->get("lexicon_menu_entries")."\";");
    }

    // Team kann die Option Buttons sehen
    if ($mybb->usergroup['canmodcp'] == '1') {
        eval("\$option_buttons_cat = \"".$templates->get("lexicon_menu_cat_option")."\";");
    } else {
        $option_buttons_cat = "";
    }

    eval("\$menu_cat .= \"".$templates->get("lexicon_menu_cat")."\";");
}

// Inhaltsverzeichnis anzeigen im Menü
if ($lexicon_contents_setting == 1) {
    $menu_contents = "<div class=\"navigation-item\"><a href=\"lexicon.php?action=contents\">{$lang->lexicon_contents}</a></div> ";
} else  {
    $menu_contents = "";
}

// Erlaubte User/Gruppen Button für das Hinzufügen Kategorien anzeigen
if(is_member($lexicon_groups_cat_setting)) { 
    eval("\$add_cat = \"".$templates->get("lexicon_menu_add_cat")."\";");
} else {
    $add_cat = "";
}

// Erlaubte User/Gruppen Button für das Hinzufügen Einträge anzeigen => nur anzeigen, wenn es mindestens eine Kategorie gibt
$count_cat = $db->num_rows($db->query("SELECT cid FROM ".TABLE_PREFIX."lexicon_categories"));
if(is_member($lexicon_groups_entry_setting) AND $count_cat > 0) { 
    eval("\$add_entry = \"".$templates->get("lexicon_menu_add_entry")."\";");
} else {
    $add_entry = "";
}

// lade das Template für die Navigation
eval("\$menu = \"".$templates->get("lexicon_menu")."\";");
 
// DIE HAUPTSEITE VOM LEXIKON - kein Aktion
if(!$mybb->get_input('action') AND !$mybb->get_input('search') AND !$mybb->get_input('page') AND !$mybb->get_input('edit') AND !$mybb->get_input('delete_entry') AND !$mybb->get_input('delete_category') AND !$mybb->get_input('delete_externallink')) {
    
    eval("\$page = \"".$templates->get("lexicon_mainpage")."\";");
    output_page($page);
    die();
}

// INHALTSVERZEICHNIS
if($mybb->get_input('action') == "contents") {

    add_breadcrumb($lang->lexicon_contents);

    // user is visiting the site and plugin isn't installed
    if ($lexicon_contents_setting == 0) {
        redirect('lexicon.php', $lang->lexicon_redirect_contents_deaktiv);
        return;
    }

    $alphabet = range('A','Z');

    $contents_bit = "";
    foreach ($alphabet as $buchstabe){  

        // Einträge nach dem Buchstaben auslesen
        $entries_query = $db->query("SELECT * FROM ".TABLE_PREFIX."lexicon_entries
        WHERE linktitle LIKE '".$buchstabe."%'
        AND accepted = 1
        ORDER BY linktitle ASC    
        ");

        $entries = "";    
        while($entry = $db->fetch_array($entries_query)){

            // Leer laufen lassen
            $link = "";    
            $linktitle = "";
            $externallink = "";
            $cid = "";
            $categoryname = "";
            $fulllink = "";

            // Mit Infos füllen
            $link = $entry['link'];    
            $linktitle = $entry['linktitle'];
            $externallink = $entry['externallink'];
            $cid = $entry['cid']; 

            if($externallink != "") {
                $fulllink = $externallink;
            } else {
                $fulllink = "lexicon.php?page=".$link;
            }

            $categoryname = $db->fetch_field($db->simple_select("lexicon_categories", "categoryname", "cid = '".$cid."'"), "categoryname");

            eval("\$entries .= \"".$templates->get("lexicon_contents_entries")."\";");
        }
    
        eval("\$contents_bit .= \"".$templates->get("lexicon_contents_bit")."\";");
    }
    
    eval("\$page = \"".$templates->get("lexicon_contents")."\";");
    output_page($page);
    die();
}

// SUCHERGEBNIS
if($mybb->get_input('search') == "results") {

    $keyword = $db->escape_string($mybb->get_input('keyword'));

    $lexicon_nav_search = $lang->sprintf($lang->lexicon_nav_search, $keyword);

    add_breadcrumb($lexicon_nav_search);

    $search_query = $db->query("SELECT * FROM ".TABLE_PREFIX."lexicon_entries 
    WHERE (title REGEXP '[[:<:]]".$keyword."[[:>:]]' OR entrytext REGEXP '[[:<:]]".$keyword."[[:>:]]')
    AND accepted = 1 
    ORDER BY linktitle ASC
    ");

    $results_bit = "";
    $results_none = $lang->lexicon_search_none;
    while($result = $db->fetch_array($search_query)){
        $results_none = "";

        // Leer laufen lassen
        $link = "";    
        $title = "";
        $cid = "";
        $categoryname = "";
        $fulllink = "";
        $entry = "";
        $previw_entry = "";

        // Mit Infos füllen
        $link = $result['link'];    
        $title = $result['title'];
        $cid = $result['cid']; 
        $entry = $result['entrytext']; 
        $categoryname = $db->fetch_field($db->simple_select("lexicon_categories", "categoryname", "cid = '".$cid."'"), "categoryname");
        $fulllink = "lexicon.php?page=".$link;
        $previw_entry = my_substr($entry, 0, 400)." [...]";

        eval("\$results_bit .= \"".$templates->get("lexicon_search_results_bit")."\";");
    }

    eval("\$page = \"".$templates->get("lexicon_search_results")."\";");
    output_page($page);
    die();
}

// KATEGORIE HINZUFÜGEN - SEITE
if($mybb->get_input('action') == "add_category") {

    add_breadcrumb($lang->lexicon_nav_add_category);

    // Nicht erlaubte User/Gruppen wieder auf die Hauptseite weiterleiten
    if(!is_member($lexicon_groups_cat_setting)) { 
        redirect('lexicon.php', $lang->lexicon_redirect_add_error_cat);
        return;
    }

    if($lexicon_sort_cat_setting == 1) { 
        $sort = 0;
        eval("\$sort_option = \"".$templates->get("lexicon_add_sort")."\";");
    } else {
        $sort_option = "";
    }
    
    eval("\$page = \"".$templates->get("lexicon_add_category")."\";");
    output_page($page);
    die();
}

// KATEGORIE HINZUFÜGEN - SPEICHERN
if($mybb->get_input('action') == "do_category") {
 
    $new_cat = [
       "categoryname" => $db->escape_string($mybb->get_input('categoryname')),
       "sort" => (int)$mybb->get_input('sort'),
    ];
 
    $db->insert_query("lexicon_categories", $new_cat);
 
    redirect("lexicon.php", $lang->lexicon_redirect_add_cat);  
} 

// EINTRAG HINZUFÜGEN - SEITE
if($mybb->get_input('action') == "add_entry") {

    add_breadcrumb($lang->lexicon_nav_add_entry);

    // Nicht erlaubte User/Gruppen wieder auf die Hauptseite weiterleiten
    if(!is_member($lexicon_groups_entry_setting)) { 
        redirect('lexicon.php', $lang->lexicon_redirect_add_error_entry);
        return;
    }

    if($lexicon_sort_entry_setting == 1) { 
        $sort = 0;
        eval("\$sort_option = \"".$templates->get("lexicon_add_sort")."\";");
    } else {
        $sort_option = "";
    }

    // KATEGORIEN DROPBOX GENERIEREN
    $categories_query = $db->query("SELECT * FROM ".TABLE_PREFIX."lexicon_categories ORDER by categoryname ASC");
          
    $cat_select = ""; 
    while($category = $db->fetch_array($categories_query)) {
    
        $cat_select .= "<option value=\"{$category['cid']}\">{$category['categoryname']}</option>";
    }

    // UNTEREINTRÄGE - Einträge sammeln
    if($lexicon_sub_setting == 1) {
        
        $entries_query = $db->query("SELECT * FROM ".TABLE_PREFIX."lexicon_entries
        WHERE accepted = '1'
        AND parentlist = '0'
        ORDER by linktitle ASC");
    
        $entries_select = ""; 
        while($entry = $db->fetch_array($entries_query)) {
    
            $entries_select .= "<option value=\"{$entry['eid']}\">{$entry['linktitle']}</option>";
        }

        eval("\$sub_option = \"".$templates->get("lexicon_add_subentry")."\";");
    } else {
        $sub_option = "";
    }
    
    eval("\$page = \"".$templates->get("lexicon_add_entry")."\";");
    output_page($page);
    die();
}

// EINTRAG HINZUFÜGEN - SPEICHERN
if($mybb->get_input('action') == "do_entry") {

    // Team Einträge werden gleich angenommen      
    if($mybb->usergroup['canmodcp'] == '1' || $user_accepted_setting == 0){
        $accepted = 1;
    } else {
        $accepted = 0;
    }
 
    $new_entry = [
       "cid" => (int)$mybb->get_input('category'),
       "linktitle" => $db->escape_string($mybb->get_input('linktitle')),
       "link" => $db->escape_string($mybb->get_input('link')),
       "externallink" => $db->escape_string($mybb->get_input('externallink')),
       "title" => $db->escape_string($mybb->get_input('title')),
       "entrytext" => $db->escape_string($mybb->get_input('entrytext')),
       "sort" => (int)$mybb->get_input('sort'),
       "parentlist" => (int)$mybb->get_input('parentlist'),
       "uid" => (int)$mybb->user['uid'],
       "accepted" => (int)$accepted,
    ];
 
    $db->insert_query("lexicon_entries", $new_entry);
 
    redirect("lexicon.php", $lang->lexicon_redirect_add_entry);  
} 

// DIE SEITEN
$lexicon_entry = $mybb->get_input('page');
if ($lexicon_entry) {

    // Eintrag nach Link input ausgeben
    $entries_query = $db->query("SELECT * FROM ".TABLE_PREFIX."lexicon_entries
    WHERE link = '".$lexicon_entry."'
    ");
    
    $option_buttons_entry = "";
    while($entry = $db->fetch_array($entries_query)){

        add_breadcrumb($entry['title']);

        // Leer laufen lassen  
        $eid = "";
        $cid = "";
        $linktitle = "";
        $link = "";
        $title = "";
        $entrytext = "";
        $pos = "";

        // Mit Infos füllen   
        $eid = $entry['eid'];
        $cid = $entry['cid'];
        $linktitle = $entry['linktitle'];
        $link = $entry['link'];
        $title = $entry['title'];
        $entrytext = $parser->parse_message($entry['entrytext'], $text_options);

        // Team und der entsprechende User evtl kann die Option Buttons sehen
        $pos = strpos($charastring, ",".$entry['uid'].",");
        if ($mybb->usergroup['canmodcp'] == '1' || $pos !== false) {
            // Team kann immer 
            if ($mybb->usergroup['canmodcp'] == '1') {
                $edit_button = "<a href=\"lexicon.php?edit=entry&eid=".$eid."\">".$lang->lexicon_button_edit."</a>";
                $delete_button = "<a href=\"lexicon.php?delete_entry=".$eid."\" onClick=\"return confirm('".$lang->lexicon_entry_delet_notice."');\">".$lang->lexicon_button_delete."</a>";
                eval("\$option_buttons_entry = \"".$templates->get("lexicon_entry_option")."\";");
            } else {
                if ($user_edit_setting == 1 OR $user_delete_setting == 1) {
                    // Bearbeiten
                    if ($user_edit_setting == 1) {
                        $edit_button = "<a href=\"lexicon.php?edit=entry&eid=".$eid."\">".$lang->lexicon_button_edit."</a>";
                    } else {
                        $edit_button = "";
                    }
                    // Löschen
                    if ($user_delete_setting == 1) {
                        $delete_button = "<a href=\"lexicon.php?delete_entry=".$eid."\" onClick=\"return confirm('".$lang->lexicon_entry_delet_notice."');\">".$lang->lexicon_button_delete."</a>";
                    } else {
                        $delete_button = "";
                    }
                    eval("\$option_buttons_entry = \"".$templates->get("lexicon_entry_option")."\";");
                } else {
                    $option_buttons_entry = "";
                }
            }
        } else {
            $option_buttons_entry = "";
        }

        eval("\$page = \"".$templates->get("lexicon_entry")."\";");
        output_page($page);
        die();
    }

}

// KATEGORIE BEARBEITEN - SEITE
if($mybb->get_input('edit') == "category") {

    add_breadcrumb($lang->lexicon_nav_edit_category);

    // Nicht erlaubte User/Gruppen wieder auf die Hauptseite weiterleiten
    if($mybb->usergroup['canmodcp'] != '1') { 
        redirect('lexicon.php', $lang->lexicon_redirect_edit_error_cat);
        return;
    }

    $cid = $mybb->get_input('cid');

    // Kategorie auslesen
    $category_query = $db->query("SELECT * FROM ".TABLE_PREFIX."lexicon_categories
    WHERE cid = '".$cid."'
    ");

    while($edit = $db->fetch_array($category_query)){

        // Leer laufen lassen
        $categoryname = "";
        $sort = "";

        // Mit Infos füllen
        $categoryname = $edit['categoryname'];
        $sort = $edit['sort'];

    }

    if($lexicon_sort_cat_setting == 1) { 
        eval("\$sort_option = \"".$templates->get("lexicon_add_sort")."\";");
    } else {
        $sort_option = "";
    }
    
    eval("\$page = \"".$templates->get("lexicon_edit_category")."\";");
    output_page($page);
    die();
}

// KATEGORIE BEARBEITEN - SPEICHERN
if($mybb->get_input('edit') == "do_category") {

    $cid = $mybb->get_input('cid');
 
    $edit_cat = [
       "categoryname" => $db->escape_string($mybb->get_input('categoryname')),
       "sort" => (int)$mybb->get_input('sort'),
    ];
 
    $db->update_query("lexicon_categories", $edit_cat, "cid = '".$cid."'");
 
    redirect("lexicon.php", $lang->lexicon_redirect_edit_cat);  
} 

// EINTRAG BEARBEITEN - SEITE
if($mybb->get_input('edit') == "entry") {

    $eid = $mybb->get_input('eid');

    add_breadcrumb($lang->lexicon_nav_edit_entry);

    // Nur Teamies und der entsprechende User können die Seite sehen   
    $sendedby = $db->fetch_field($db->simple_select("lexicon_entries", "uid", "eid = '".$eid."'"), "uid");
    $check = strpos($charastring, ",".$sendedby.",");

    if($mybb->usergroup['canmodcp'] != '1' AND ($check === false AND $user_edit_setting == 1)) { 
        redirect('lexicon.php', $lang->lexicon_redirect_edit_error_entry);
        return;
    }

    // Eintrag auslesen
    $entry_query = $db->query("SELECT * FROM ".TABLE_PREFIX."lexicon_entries
    WHERE eid = '".$eid."'
    ");

    while($edit = $db->fetch_array($entry_query)){

        // Leer laufen lassen
        $cid = "";
        $linktitle = "";
        $link = "";
        $title = "";
        $entrytext = "";
        $sort = "";
        $externallink = "";

        // Mit Infos füllen
        $cid = $edit['cid'];
        $linktitle = $edit['linktitle'];
        $link = $edit['link'];
        $title = $edit['title'];
        $entrytext = $edit['entrytext'];
        $sort = $edit['sort'];
        $externallink = $edit['externallink'];
 
        // KATEGORIEN DROPBOX GENERIEREN
        $categories_query = $db->query("SELECT * FROM ".TABLE_PREFIX."lexicon_categories ORDER by categoryname ASC");

        $cat_select = "";
        while($category = $db->fetch_array($categories_query)) {
    
            // die bisherige Kategorie als ausgewählt anzeigen lassen
            if($category['cid'] == $cid) {
                $checked_cat = "selected";
            } else {
                $checked_cat = "";
            }
    
            $cat_select .= "<option value=\"{$category['cid']}\" {$checked_cat}>{$category['categoryname']}</option>";    
        }

        if($lexicon_sub_setting == 1) { 
        
            $entries_query = $db->query("SELECT * FROM ".TABLE_PREFIX."lexicon_entries  
            WHERE accepted = '1'
            AND parentlist = '0'
            ORDER by linktitle ASC
            ");
   
            $entries_select = "";    
            while($entry = $db->fetch_array($entries_query)) {

                // die bisherige Kategorie als ausgewählt anzeigen lassen
                if($entry['eid'] == $edit['parentlist']) {
                    $checked_sub = "selected";
                } else {
                    $checked_sub = "";   
                }

                $entries_select .= "<option value=\"{$entry['eid']}\" {$checked_sub}>{$entry['linktitle']}</option>";   
            }
   
            eval("\$sub_option = \"".$templates->get("lexicon_add_subentry")."\";");

        } else {
            $sub_option = "";
        }

    }

    if($lexicon_sort_entry_setting == 1) { 
        eval("\$sort_option = \"".$templates->get("lexicon_add_sort")."\";");
    } else {
        $sort_option = "";
    }
    
    eval("\$page = \"".$templates->get("lexicon_edit_entry")."\";");
    output_page($page);
    die();
}

// EINTRAG BEARBEITEN - SPEICHERN
if($mybb->get_input('edit') == "do_entry") {

    $eid = $mybb->get_input('eid');
 
    $edit_entry = [
       "cid" => (int)$mybb->get_input('category'),
       "linktitle" => $db->escape_string($mybb->get_input('linktitle')),
       "link" => $db->escape_string($mybb->get_input('link')),
       "externallink" => $db->escape_string($mybb->get_input('externallink')),
       "title" => $db->escape_string($mybb->get_input('title')),
       "sort" => (int)$mybb->get_input('sort'),
       "parentlist" => (int)$mybb->get_input('parentlist'),
       "entrytext" => $db->escape_string($mybb->get_input('entrytext')),
    ];

    $db->update_query("lexicon_entries", $edit_entry, "eid = '".$eid."'");
 
    redirect("lexicon.php", $lang->lexicon_redirect_edit_entry);  
}

// EXTERNENLINK BEARBEITEN - SEITE
if($mybb->get_input('edit') == "externallink") {

    $eid = $mybb->get_input('eid');

    add_breadcrumb($lang->lexicon_nav_edit_externallink);

    if($mybb->usergroup['canmodcp'] != '1') { 
        redirect('lexicon.php', $lang->lexicon_redirect_edit_error_entry);
        return;
    }

    // Eintrag auslesen
    $externallink_query = $db->query("SELECT * FROM ".TABLE_PREFIX."lexicon_entries
    WHERE eid = '".$eid."'
    ");

    while($edit = $db->fetch_array($externallink_query)){

        // Leer laufen lassen
        $cid = "";
        $linktitle = "";
        $externallink = "";
        $sort = "";

        // Mit Infos füllen
        $cid = $edit['cid'];
        $linktitle = $edit['linktitle'];
        $externallink = $edit['externallink'];
        $sort = $edit['sort'];
 
        // KATEGORIEN DROPBOX GENERIEREN
        $categories_query = $db->query("SELECT * FROM ".TABLE_PREFIX."lexicon_categories ORDER by categoryname ASC");

        $cat_select = "";
        while($category = $db->fetch_array($categories_query)) {
    
            // die bisherige Kategorie als ausgewählt anzeigen lassen
            if($category['cid'] == $cid) {
                $checked_cat = "selected";
            } else {
                $checked_cat = "";
            }
    
            $cat_select .= "<option value=\"{$category['cid']}\" {$checked_cat}>{$category['categoryname']}</option>";    
        }

        if($lexicon_sub_setting == 1) { 
        
            $entries_query = $db->query("SELECT * FROM ".TABLE_PREFIX."lexicon_entries  
            WHERE accepted = '1'
            AND parentlist = '0'
            ORDER by linktitle ASC
            ");
   
            $entries_select = "";    
            while($entry = $db->fetch_array($entries_query)) {

                // die bisherige Kategorie als ausgewählt anzeigen lassen
                if($entry['eid'] == $edit['parentlist']) {
                    $checked_sub = "selected";
                } else {
                    $checked_sub = "";   
                }

                $entries_select .= "<option value=\"{$entry['eid']}\" {$checked_sub}>{$entry['linktitle']}</option>";   
            }
   
            eval("\$sub_option = \"".$templates->get("lexicon_add_subentry")."\";");

        } else {
            $sub_option = "";
        }

    }

    if($lexicon_sort_entry_setting == 1) { 
        eval("\$sort_option = \"".$templates->get("lexicon_add_sort")."\";");
    } else {
        $sort_option = "";
    }
    
    eval("\$page = \"".$templates->get("lexicon_edit_externallink")."\";");
    output_page($page);
    die();
}

// EXTERNENLINK BEARBEITEN - SPEICHERN
if($mybb->get_input('edit') == "do_externallink") {

    $eid = $mybb->get_input('eid');
 
    $edit_externallink = [
       "cid" => (int)$mybb->get_input('category'),
       "linktitle" => $db->escape_string($mybb->get_input('linktitle')),
       "externallink" => $db->escape_string($mybb->get_input('externallink')),
       "sort" => (int)$mybb->get_input('sort'),
       "parentlist" => (int)$mybb->get_input('parentlist'),
    ];

    $db->update_query("lexicon_entries", $edit_externallink, "eid = '".$eid."'");
 
    redirect("lexicon.php", $lang->lexicon_redirect_edit_externallink);  
}

// KATEGORIE LÖSCHEN
$delete_cat = $mybb->get_input('delete_category');
if($delete_cat) {

    // in Kategorie löschen
    $db->delete_query("lexicon_categories", "cid = '".$delete_cat."'");
    // Einträge der Kategorie löschen
    $db->delete_query("lexicon_entries", "cid = '".$delete_cat."'");

    redirect("lexicon.php", $lang->lexicon_redirect_delete_cat);
}

// EINTRAG LÖSCHEN
$delete_entry = $mybb->get_input('delete_entry');
if($delete_entry) {

    // in Eintrag löschen
    $db->delete_query("lexicon_entries", "eid = '".$delete_entry."'");
    // in Untereinträge von diesem Eintrag löschen
    $db->delete_query("lexicon_entries", "parentlist = '".$delete_entry."'");

    redirect("lexicon.php", $lang->lexicon_redirect_delete_entry);
}

// EXTERNENLINK LÖSCHEN
$delete_externallink = $mybb->get_input('delete_externallink');
if($delete_externallink) {

    // in Eintrag löschen
    $db->delete_query("lexicon_entries", "eid = '".$delete_externallink."'");
    // in Untereinträge von diesem Eintrag löschen
    $db->delete_query("lexicon_entries", "parentlist = '".$delete_externallink."'");

    redirect("lexicon.php", $lang->lexicon_redirect_delete_externallink);
}

// ACCOUNTSWITCHER HILFSFUNKTION
function lexicon_get_allchars($user_id) {
	global $db, $cache, $mybb, $lang, $templates, $theme, $header, $headerinclude, $footer;

	//für den fall nicht mit hauptaccount online
	if (isset($mybb->user['as_uid'])) {
        $as_uid = intval($mybb->user['as_uid']);
    } else {
        $as_uid = 0;
    }

	$charas = array();
	if ($as_uid == 0) {
	  // as_uid = 0 wenn hauptaccount oder keiner angehangen
	  $get_all_users = $db->query("SELECT uid,username FROM ".TABLE_PREFIX."users WHERE (as_uid = ".$user_id.") OR (uid = ".$user_id.") ORDER BY username");
	} else if ($as_uid != 0) {
	  //id des users holen wo alle an gehangen sind 
	  $get_all_users = $db->query("SELECT uid,username FROM ".TABLE_PREFIX."users WHERE (as_uid = ".$as_uid.") OR (uid = ".$user_id.") OR (uid = ".$as_uid.") ORDER BY username");
	}
	while ($users = $db->fetch_array($get_all_users)) {
	  $uid = $users['uid'];
	  $charas[$uid] = $users['username'];
	}
	return $charas;  
}
