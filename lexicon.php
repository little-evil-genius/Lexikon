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
ORDER BY $sort_cat
");

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
                $sublink = "";   
                $sublinktitle = "";
                $subexternallink = "";
    
                // Mit Infos füllen
                $sublink = $subentry['link'];       
                $sublinktitle = $subentry['linktitle'];
                $subexternallink = $subentry['externallink'];

                if($subexternallink != "") {
                    $subfulllink = $subexternallink;
                } else {
                    $subfulllink = "lexicon.php?page=".$sublink;
                }

                eval("\$subentries .= \"".$templates->get("lexicon_menu_subentries")."\";");
            }
        }

        if($externallink != "") {
            $fulllink = $externallink;
        } else {
            $fulllink = "lexicon.php?page=".$link;
        }
        
        eval("\$entries .= \"".$templates->get("lexicon_menu_entries")."\";");
    }

    // Team kann die Option Buttons sehen
    if ($mybb->usergroup['canmodcp'] == '1') {
        $option_buttons_cat = "<a href=\"lexicon.php?edit=category&cid={$cid}\">E</a> 
        <a href=\"lexicon.php?delete_category={$cid}\" onClick=\"return confirm('{$lang->lexicon_cat_delet_notice}');\">X</a>";
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

// Erlaubte User/Gruppen Button für das Hinzufügen Einträge anzeigen
if(is_member($lexicon_groups_entry_setting)) { 
    eval("\$add_entry = \"".$templates->get("lexicon_menu_add_entry")."\";");
} else {
    $add_entry = "";
}

// lade das Template für die Navigation
eval("\$menu = \"".$templates->get("lexicon_menu")."\";");
 
// DIE HAUPTSEITE VOM LEXIKON - kein Aktion
if(!$mybb->input['action'] AND !$mybb->input['page'] AND !$mybb->input['edit'] AND !$mybb->input['delete_entry'] AND !$mybb->input['delete_category']) {
    
    eval("\$page = \"".$templates->get("lexicon_mainpage")."\";");
    output_page($page);
    die();
}

// INHALTSVERZEICHNIS
if($mybb->input['action'] == "contents") {

    add_breadcrumb($lang->lexicon_contents);

    // user is visiting the site and plugin isn't installed
    if ($lexicon_contents_setting == 0) {
        redirect('lexicon.php', $lang->lexicon_redirect_contents_deaktiv);
        return;
    }

    $alphabet = range('A','Z');

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

            // Mit Infos füllen
            $link = $entry['link'];    
            $linktitle = $entry['linktitle'];
            $externallink = $entry['externallink'];

            if($externallink != "") {
                $fulllink = $externallink;
            } else {
                $fulllink = "lexicon.php?page=".$link;
            }

            eval("\$entries .= \"".$templates->get("lexicon_contents_entries")."\";");
        }
    
        eval("\$contents_bit .= \"".$templates->get("lexicon_contents_bit")."\";");
    }
    
    eval("\$page = \"".$templates->get("lexicon_contents")."\";");
    output_page($page);
    die();
}

// KATEGORIE HINZUFÜGEN - SEITE
if($mybb->input['action'] == "add_category") {

    add_breadcrumb($lang->lexicon_nav_add_category);

    // Nicht erlaubte User/Gruppen wieder auf die Hauptseite weiterleiten
    if(!is_member($lexicon_groups_cat_setting)) { 
        redirect('lexicon.php', $lang->lexicon_redirect_add_error_cat);
        return;
    }

    if($lexicon_sort_cat_setting == 1) { 
        eval("\$sort_option = \"".$templates->get("lexicon_add_sort")."\";");
    } else {
        $sort_option = "";
    }
    
    eval("\$page = \"".$templates->get("lexicon_add_category")."\";");
    output_page($page);
    die();
}

// KATEGORIE HINZUFÜGEN - SPEICHERN
if($mybb->input['action'] == "do_category") {
 
    $new_cat = [
       "categoryname" => $db->escape_string($mybb->get_input('categoryname')),
       "sort" => $db->escape_string($mybb->get_input('sort')),
    ];
 
    $db->insert_query("lexicon_categories", $new_cat);
 
    redirect("lexicon.php", $lang->lexicon_redirect_add_cat);  
} 

// EINTRAG HINZUFÜGEN - SEITE
if($mybb->input['action'] == "add_entry") {

    add_breadcrumb($lang->lexicon_nav_add_entry);

    // Nicht erlaubte User/Gruppen wieder auf die Hauptseite weiterleiten
    if(!is_member($lexicon_groups_entry_setting)) { 
        redirect('lexicon.php', $lang->lexicon_redirect_add_error_entry);
        return;
    }

    if($lexicon_sort_entry_setting == 1) { 
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
if($mybb->input['action'] == "do_entry") {

    // Team Einträge werden gleich angenommen      
    if($mybb->usergroup['canmodcp'] == '1'){
        $accepted = 1;
    } else {
        $accepted = 0;
    }
 
    $new_entry = [
       "cid" => $db->escape_string($mybb->get_input('category')),
       "linktitle" => $db->escape_string($mybb->get_input('linktitle')),
       "link" => $db->escape_string($mybb->get_input('link')),
       "externallink" => $db->escape_string($mybb->get_input('externallink')),
       "title" => $db->escape_string($mybb->get_input('title')),
       "entrytext" => $db->escape_string($mybb->get_input('entrytext')),
       "sort" => $db->escape_string($mybb->get_input('sort')),
       "parentlist" => $db->escape_string($mybb->get_input('parentlist')),
       "uid" => (int)$mybb->user['uid'],
       "accepted" => $accepted,
    ];
 
    $db->insert_query("lexicon_entries", $new_entry);
 
    redirect("lexicon.php", $lang->lexicon_redirect_add_entry);  
} 

// DIE SEITEN
$lexicon_entry = $mybb->input['page'];
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

        // Mit Infos füllen   
        $eid = $entry['eid'];
        $cid = $entry['cid'];
        $linktitle = $entry['linktitle'];
        $link = $entry['link'];
        $title = $entry['title'];
        $entrytext = $parser->parse_message($entry['entrytext'], $text_options);

        // Team kann die Option Buttons sehen
        if ($mybb->usergroup['canmodcp'] == '1') {
            $option_buttons_entry = "<a href=\"lexicon.php?edit=entry&eid={$eid}\">Bearbeiten</a> | <a href=\"lexicon.php?delete_entry={$eid}\">Löschen</a>";
        } else {
            $option_buttons_entry = "";
        }

        eval("\$page = \"".$templates->get("lexicon_entry")."\";");
        output_page($page);
        die();
    }

}

// KATEGORIE BEARBEITEN - SEITE
if($mybb->input['edit'] == "category") {

    add_breadcrumb($lang->lexicon_nav_edit_category);

    // Nicht erlaubte User/Gruppen wieder auf die Hauptseite weiterleiten
    if($mybb->usergroup['canmodcp'] != '1') { 
        redirect('lexicon.php', $lang->lexicon_redirect_edit_error_cat);
        return;
    }

    $cid = $mybb->input['cid'];

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
if($mybb->input['edit'] == "do_category") {

    $cid = $mybb->input['cid'];
 
    $edit_cat = [
       "categoryname" => $db->escape_string($mybb->get_input('categoryname')),
       "sort" => $db->escape_string($mybb->get_input('sort')),
    ];
 
    $db->update_query("lexicon_categories", $edit_cat, "cid = '".$cid."'");
 
    redirect("lexicon.php", $lang->lexicon_redirect_edit_cat);  
} 

// EINTRAG BEARBEITEN - SEITE
if($mybb->input['edit'] == "entry") {

    add_breadcrumb($lang->lexicon_nav_edit_entry);

    // Nur Teamies können die Seite sehen
    if($mybb->usergroup['canmodcp'] != '1') { 
        redirect('lexicon.php', $lang->lexicon_redirect_edit_error_entry);
        return;
    }

    $eid = $mybb->input['eid'];

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
if($mybb->input['edit'] == "do_entry") {

    $eid = $mybb->input['eid'];
 
    $edit_entry = [
       "cid" => $db->escape_string($mybb->get_input('category')),
       "linktitle" => $db->escape_string($mybb->get_input('linktitle')),
       "link" => $db->escape_string($mybb->get_input('link')),
       "externallink" => $db->escape_string($mybb->get_input('externallink')),
       "title" => $db->escape_string($mybb->get_input('title')),
       "sort" => $db->escape_string($mybb->get_input('sort')),
       "parentlist" => $db->escape_string($mybb->get_input('parentlist')),
       "entrytext" => $db->escape_string($mybb->get_input('entrytext')),
    ];

    $db->update_query("lexicon_entries", $edit_entry, "eid = '".$eid."'");
 
    redirect("lexicon.php", $lang->lexicon_redirect_edit_entry);  
}

// KATEGORIE LÖSCHEN
$delete_cat = $mybb->input['delete_category'];
if($delete_cat) {

    // in Kategorie löschen
    $db->delete_query("lexicon_categories", "cid = '".$delete_cat."'");
    // Einträge der Kategorie löschen
    $db->delete_query("lexicon_entries", "cid = '".$delete_cat."'");

    redirect("lexicon.php", $lang->lexicon_redirect_delete_cat);
}

// EINTRAG LÖSCHEN
$delete_entry = $mybb->input['delete_entry'];
if($delete_entry) {

    // in Eintrag löschen
    $db->delete_query("lexicon_entries", "eid = '".$delete_entry."'");

    redirect("lexicon.php", $lang->lexicon_redirect_delete_entry);
}

?>
