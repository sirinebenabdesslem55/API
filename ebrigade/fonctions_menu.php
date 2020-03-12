<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade/
  # version: 5.1

  # Copyright (C) 2004, 2020 Nicolas MARCHE
  # This program is free software; you can redistribute it and/or modify
  # it under the terms of the GNU General Public License as published by
  # the Free Software Foundation; either version 2 of the License, or
  # (at your option) any later version.
  #
  # This program is distributed in the hope that it will be useful,
  # but WITHOUT ANY WARRANTY; without even the implied warranty of
  # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  # GNU General Public License for more details.
  # You should have received a copy of the GNU General Public License
  # along with this program; if not, write to the Free Software
  # Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

//=====================================================================
// write head 
//=====================================================================
function writehead($style='') {
    global $title,$basedir,$additional_header_info, $snow, $nomenu, $nodoctype, $mylightcolor,$version,$headerset;
    $favicon = get_favicon();
    $appleicon = get_iphone_logo();
    $lang='fr';
    
    $head = "<!doctype html>";
    $head .= "<html lang='".$lang."' >
    <head>
    <title>".$title."</title>
    <link rel='icon' type='image/png' href='".$favicon."' />
    <link rel='apple-touch-icon' href='".$appleicon."' />
    <link rel='stylesheet' href='".$basedir."/css/all.css?version=".$version."'>
    <meta http-equiv='Content-Type' content='text/html; charset=ISO-8859-1' /> 
    <meta name='theme-color' content='".$mylightcolor."'/>";
    
    if ( $snow == 1 and ! isset($nomenu)) {
        if (! $style == 'iphone' ) $head .= "<script src='js/snow.js' type='text/javascript'></script>";
    }
    if (isset($additional_header_info)) $head .= $additional_header_info;
    @header("Content-Type: text/html; charset=ISO-8859-1");
    @header("X-FRAME-OPTIONS: SAMEORIGIN");
    @header("X-Content-Type-Options: nosniff");
    //@header("x-xss-protection: 1; mode=block");
    @header_remove("X-Powered-By");
    //@header("Set-Cookie: secure; HttpOnly");
    //@header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
    ini_set( 'default_charset', 'ISO-8859-1' );
    $head .= "\n<link rel='stylesheet' href='".$basedir."/css/bootstrap.css?version=".$version."' media='screen'>";
    $head .= "\n<link rel='stylesheet' href='".$basedir."/css/bootstrap-datepicker.css?version=".$version."' media='screen'>";
    $head .= "\n<link rel='stylesheet' href='".$basedir."/css/main.css?version=".$version."&update=4'>";
    $head .= "\n<link rel='stylesheet' href='".$basedir."/css/print.css?version=".$version."' media='print'>";

    if (! isset($nomenu)) {
        $head .= write_menu();
        $nomenu=1;
        $head .= "<div style='position: relative; top:1px; margin: 100 auto;' align = center>";
    }

    $headerset=1;
    print $head;
}

//=====================================================================
// write head
//=====================================================================
function writefoot($loadjs=true) {
    global $basedir, $debug, $chat, $print;
    
    if ( $print ) {
        $debug=0;
        $loadjs=false;
    }
    if ( $debug == 1 )
        show_total_time();
    
    $foot='';
    if ($loadjs) {
        $foot = "<script type='text/javascript' src='".$basedir."/js/bootstrap-datepicker.js'></script>
            <script type='text/javascript' src='".$basedir."/js/bootstrap-datepicker.fr.min.js'></script>
            <script type='text/javascript' src='".$basedir."/js/bootstrap-datepicker-ebrigade.js'></script>";
        if ($chat)
            $foot .=  "<script type='text/javascript' src='js/visitors.js'></script>";
    }
    $foot .= "</body>
     </html>";


    print $foot;
}

//=====================================================================
// Build bootstrap menu
//=====================================================================
function writehead_print_event($evenement) {
    global $dbc;
     global $title,$basedir;
    $query="select E_LIBELLE, E_LIEU from evenement where E_CODE=".intval($evenement);
     $res = mysqli_query($dbc,$query);
     $row = mysqli_fetch_array($res);
     $css='css/main.css';
    $t=$row["E_LIBELLE"]." - ".$row["E_LIEU"];
    $head = "<head>
    <title>".$t."</title>
    <LINK TITLE=\"".$t."\" REL='STYLESHEET' TYPE='text/css' HREF='".$basedir."/".$css."'>
    <meta http-equiv=Content-Type content='text/html; charset=iso-8859-1'>";
    header('Content-Type: text/html; charset=ISO-8859-1');
    ini_set( 'default_charset', 'ISO-8859-1' );
    echo $head;
}
  
//=====================================================================
// Evaluate conditions
//=====================================================================
  
function evaluate_condition ($id, $current, $type, $value, $menu="") {
    // output value can be  
    // > 0 display
    // <= 0 do not display
    $configs= array('vehicules','materiel','consommables','competences','externes','disponibilites', 'evenements','notes',
                'cotisations','gardes','nbsections','sdis' ,'syndicate','cron_allowed','chat','auto_backup','bank_accounts',
                'assoc','geolocalize_enabled','remplacements','pompiers','block_personnel','import_api');

    if ( $type == "" ) $ret=1;
    else if ( in_array($type,$configs) ) {
        global $$type;
        if ( $$type == $value ) $ret = 0;
        else $ret = -10;
    }
    else if ( $type == 'permission' ) {
        if ( check_rights($id, $value )) $ret=1;
        else $ret=0;
    }
    else if ( $type == 'not_permission' ) {
        if ( check_rights($id, $value )) $ret=-10;
        else $ret=0;
    }
    else if ( $type == 'spgm') {
        if ( is_dir("./".$type) ) $ret=0;
        else $ret=-10;
    }
    else if ( $type == 'SES_COMPANY' ) {
        if (! isset($_SESSION['SES_COMPANY'])) $ret=-10;
        else if ( intval($_SESSION['SES_COMPANY']) == 0 ) $ret=-10;
        else $ret=1;
    }
    else if (  $type == 'iphone' ) {
        if ( is_iphone()) $iphone=1;
        else $iphone=-10;
        if ( $iphone == $value ) $ret=1;
        else $ret=0;
    }    
    else $ret=1;
    $current = $current + $ret;
    //echo $type." ".$$type." ".$value." ='".$ret."' : ".$menu."<br>";
    return $current;
}   

//=====================================================================
// Build bootstrap menu
//=====================================================================

function write_menu() {
    global $dbc, $cisname, $basedir, $nomenu, $chat, $syndicate, $gardes,$notes,$version;
    if ( isset($nomenu) ) return;
    $id=intval(@$_SESSION['id']);
    $mysection=intval(@$_SESSION['SES_SECTION']);
    $query = "select distinct menu_group.MG_CODE, MG_TITLE, MG_NAME, MG_ICON, MI_CODE, MI_NAME, MI_TITLE, MI_URL, MI_ICON, mc.MC_TYPE ITEM_TYPE, MG_ORDER, MI_ORDER, mc.MC_VALUE ITEM_VALUE
              from menu_group, menu_item left join menu_condition mc on mc.MC_CODE = MI_CODE
              where menu_item.MG_CODE = menu_group.MG_CODE
              order by MG_ORDER, MG_CODE, MI_ORDER, MI_CODE";

    $out = "<meta name='viewport' content='width=device-width, initial-scale=1'>
            <script src='".$basedir."/js/jquery.min.12.js'></script>
            <script src='".$basedir."/js/bootstrap.js?version=".$version."'></script>";
    // $out=""; to do later when writefoot is available everywhere
    $out .= "<div class='container'>
                <nav class='navbar navbar-expand-lg fixed-top noprint navbar-ebrigade' >
                <a class='navbar-brand' href='index_d.php' title=\"Aller a la page d'accueil de ".$cisname."\" ><i class='fa fa-home fa-lg' style='color: #d9d9d9;'></i></a>
                <button class='navbar-toggler custom-toggler' type='button' data-toggle='collapse' data-target='#myNavbar' aria-controls='myNavbar' aria-expanded='false' aria-label='Toggle navigation'>
                    <i class='fa fa-bars fa-lg py-1 text-white'></i>
                </button>
                <div class='collapse navbar-collapse' id='myNavbar'>
                <ul class='navbar-nav'>";

    $result = mysqli_query($dbc,$query);
    $prevgroup="none";
    $previtem="none";
    $menugroup="";
    $menuitem="";
    $display_item=0;
    $items=0;
    while ( $row=@mysqli_fetch_array($result)) {
        $MG_CODE=$row["MG_CODE"];
        $MG_NAME=$row["MG_NAME"];
        $MG_ICON=$row["MG_ICON"];
        $MG_TITLE=$row["MG_TITLE"];
        $MI_CODE=$row["MI_CODE"];
        $MI_NAME=$row["MI_NAME"];
        $MI_ICON=$row["MI_ICON"];
        $MI_TITLE=$row["MI_TITLE"];
        $MI_URL=$row["MI_URL"];
        $ITEM_TYPE=$row["ITEM_TYPE"];
        $ITEM_VALUE=$row["ITEM_VALUE"];
        
        if ( $MI_CODE <> $previtem  ) {
            // flush previous item
            if ( $display_item > 0 ) { //avoid 2 dividers
                // avoid this at the end of the group "<div role='separator' class='dropdown-divider'></div>";
                if (strpos( substr($menugroup,-30,30), 'dropdown-divider') == false or strpos( substr($menuitem,-30,30), 'dropdown-divider') == false ) 
                    $menugroup  .=  $menuitem;
                if ( $MI_NAME <> 'divider' ) $items++;
            }
            // prepare next item
            $display_item=0;
            if ( $MI_ICON <> '' and  $MI_ICON <> 'null' ) {
                if ( $MI_ICON == 'power-off' ) $MI_NAME = "<i class='fa fa-".$MI_ICON." fa-lg' style='color:red;' ></i> ".$MI_NAME;
                else $MI_NAME = "<i class='fa fa-".$MI_ICON." fa-lg'></i> ".$MI_NAME;
            }
            if ( $MI_CODE == 'MASECTION' )  $MI_URL .="?S_ID=". $mysection;
            if ( $MI_NAME == 'divider' and $items > 0 ) $menuitem = "\n<div role='separator' class='dropdown-divider'></div>";
            else if ( $MI_NAME <> 'divider' ) $menuitem = " \n<a class='dropdown-item' href='".$MI_URL."' title=\"".$MI_TITLE."\">".$MI_NAME."</a>"; 
            $previtem = $MI_CODE;
        }
        if ( $MI_NAME == 'divider' and $items == 0 ) 
            $display_item = 0;
        else
            $display_item = evaluate_condition ($id, $display_item, $ITEM_TYPE, $ITEM_VALUE, $MG_NAME." > ".$MI_NAME." ".$items." items");
        
        if ( $MG_CODE <> $prevgroup ) {
            // flush previous group
            if ( $items > 0 ) {
                // avoid this at the end of the group "<div role='separator' class='dropdown-divider'></div>";
                if (strpos( substr($menugroup,-30,30), 'dropdown-divider') !== false)
                    $menugroup = substr($menugroup,0,-53);
                $out  .=  $menugroup."</div></li> ";
            }
            // prepare next group
            if ( $MG_ICON <> '' ) $MG_NAME = "<i class='fa fa-".$MG_ICON." fa-lg'></i> ".$MG_NAME;
            if ($MG_CODE == 'ME' ) $MG_NAME .= " ".my_ucfirst(@$_SESSION['SES_PRENOM']);
            $menugroup ="<li class='nav-item dropdown '>
                              <a class='dropdown-toggle nav-link' data-toggle='dropdown' href='#' title=\"".$MG_TITLE."\">".$MG_NAME."<span class='caret'></span></a>
                                <div class='dropdown-menu'>";
            $prevgroup = $MG_CODE;
            $items=0;
        }

    }
    if ( $display_item > 0 ) {// flush last item of the group, avoid 2 dividers
        if ( substr($menuitem,-14,7) <> 'divider' or substr($menugroup,-14,7) <> 'divider')  $menugroup  .=  $menuitem;
    }
    
    // syndicate print specific download menu
    if ( $syndicate == 1 ) $out  .= write_specific_menu();
    
    if ( $items > 0 ) {
        $out  .=  $menugroup."</div></li>";
    }
    
    // search button
    if (check_rights($id, 56)) {
        $out .= "<li class='nav-item' >
                    <a href='search_personnel.php' title=\"Recherche sur les fiches personnel ou adherent\" >
                        <i class='fa fa-search fa-lg top_menu_item' style='color: #d9d9d9;margin-right:12px;'></i>
                    </a>
                </li>";
    }
    
    
    // personnel salarié, enregistrer heures
    $query="select P_STATUT from pompier where P_ID=".$id;
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $STATUT = $row["P_STATUT"];
    if ( $STATUT == 'SAL' or $STATUT == 'FONC') {
        $out .= "<li class='nav-item'>";
        $out .= write_pointage_links($id);
        $out .= "</li>";
    }

    $out .= "<li class='nav-item' >
                    <a href='deconnexion.php' title=\"Se déconnecter de l'application\" >
                        <i class='fa fa-power-off fa-lg top_menu_item' style='color:red;'
                            onMouseOver=\"this.style.color='#ff6666';\" onMouseOut=\"this.style.color='red';\"></i> 
                    </a>
                </li>";
                
    // chat link
    if ($chat and check_rights($id, 51)) {
        $out .= "<li class='nav-item ' style='padding-top:0.4rem; padding-bottom:0.5rem;'>
                        <a href='chat.php' title=\"Communication par messagerie instantanee avec les autres personnes connectees\">
                            <span class='badge top_menu_item' id='counter' ></span>
                        </a>
                    </li>";
    }
    
    $out .= "<li class='nav-item'><br></li>";
    $out .= "<li class='nav-item'><br></li>";
    $out .= "<li class='nav-item'><br></li>";
    $out .= "<li class='nav-item'><br></li>";
    $out .= "<li class='nav-item'><br></li>";
    $out .= "<li class='nav-item'><br></li>";
    $out .= "<li class='nav-item'><br></li>";
    $out .= "<li class='nav-item'><br></li>";
    $out .= "<li class='nav-item'><br></li>";
    $out .= "<li class='nav-item'><br></li>";
    
    $out .="</ul></div></nav></div>";

    return $out;
}    

//=====================================================================
// Menu special pour telechargements
//=====================================================================

function write_specific_menu() {
    global $dbc, $cisname, $syndicate;
    $id=intval($_SESSION['id']);
    if (! check_rights($id, 44 ))
        return "";
        
    $mysection=intval($_SESSION['SES_SECTION']);
    $parent = intval($_SESSION['SES_PARENT']);
    
    $spec ="<li class='dropdown'>
                        <a class='dropdown-toggle nav-link' data-toggle='dropdown' href='#' title=\"Voir ou télecharger des documents ".$cisname."\">Téléchargement <span class='caret'></span></a>
                        <div class='dropdown-menu'>"; 
        
    // afficher les documents nationaux pour chaque type
    $query="select td.TD_CODE, td.TD_LIBELLE, td.TD_SECURITY, count(*) as NB
            from type_document td left join document d on d.TD_CODE = td.TD_CODE
            where td.TD_SYNDICATE = ".$syndicate."
            group by td.TD_CODE, td.TD_LIBELLE, td.TD_SECURITY";
    $query .=" order by TD_LIBELLE";
    $result=mysqli_query($dbc,$query);
    while ($row=mysqli_fetch_array($result)) {
        $TD_CODE=$row["TD_CODE"];
        $TD_LIBELLE=$row["TD_LIBELLE"];
        $TD_SECURITY=intval($row["TD_SECURITY"]);
        $NB=$row["NB"];
        if ( check_rights($id, $TD_SECURITY)) {
            if ( $syndicate == 1 ) $s=1;
            else $s=0;
            $spec .=" \n<a class='dropdown-item' href='documents.php?filter=".$s."&td=".$TD_CODE."&page=1&yeardoc=all&dossier=0' title=\"".$TD_LIBELLE.": ".$NB." documents\">".substr($TD_LIBELLE,0,26)."</a>";
        }
    }
    // cas particulier afficher les documents d'un département
    if ( $parent == 30 or $mysection == 30 or $mysection == 0 or $mysection == 1 ) {
        $spec .="\n<div role='separator' class='dropdown-divider'></div>";
        $nb=count_document(30);
        $spec .= "\n<a class='dropdown-item' href='documents.php?dossier=0&filter=30&td=ALL#documents' target=\"droite\" class=s title=\"Documents 06: ".$nb." documents\">Documents 06</a>";
    }

    $spec .="</div></li>";
    return $spec;
}
  
  
//=====================================================================
// POINTAGE
//=====================================================================

function write_pointage_links($person) {
    global $dbc;
    
    // quel type de salarié?
    $query="select TS_CODE from pompier where P_ID=".$person;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( $row["TS_CODE"] == 'SNU' ) return;
    
    $link="horaires.php?person=$person";
    
    // est ce qu'il y a une periode commencée pour le matin
    $query="select 1 from horaires where P_ID=".$person." and H_DATE='".date('Y-m-d')."' 
            and H_DEBUT1 is not null";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( $row[0] == 1 ) $started1=true;
    else $started1=false;
    
    // est ce qu'il y a une periode terminée pour le matin
    $query="select 1 from horaires where P_ID=".$person." and H_DATE='".date('Y-m-d')."' 
            and H_DEBUT1 is not null and H_FIN1 is not null";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( $row[0] == 1 ) $finished1=true;
    else $finished1=false;
    
    // est ce qu'il y a une periode commencée pour après-midi
    $query="select 1 from horaires where P_ID=".$person." and H_DATE='".date('Y-m-d')."' 
            and H_DEBUT2 is not null";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( $row[0] == 1 ) $started2=true;
    else $started2=false;
    
    // est ce qu'il y a une periode terminée pour après-midi
    $query="select 1 from horaires where P_ID=".$person." and H_DATE='".date('Y-m-d')."' 
            and H_DEBUT2 is not null and H_FIN2 is not null";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( $row[0] == 1 ) $finished2=true;
    else $finished2=false;
    if ( $finished2 ) {
        $c="Depointer, enregistrer l'heure de fin de la periode de travail meme si elle a deja ete enregistree";
        $t='Depointer';
        $link .="&action=depointer";
        $color='orange';
    }
    else if (( ! $started1 and ! $finished1 and ! $started2 ) or (! $started2 and ! $finished2 and $finished1)) {
        $c="Pointer, enregistrer l'heure de debut de la periode de travail";
        $link .="&action=pointer";
        $t='Pointer';
        $color='green';
    }
    else {
        $c="Depointer, enregistrer l'heure de fin de la periode de travail";
        $link .="&action=depointer";
        $t='Depointer';
        $color='red';
    }
    return " <ul class='nav nav-item '>
            <a href='".$link."' class='btn btn-default' title=\"".$c."\" style='background-color:#d9d9d9;'
                onMouseOver=\"this.style.background='white';\" onMouseOut=\"this.style.background='#d9d9d9';\">
                <i class='fa fa-clock fa-lg' style='color:".$color.";' ></i> ".$t."
            </a>
        </ul>";
}

//=====================================================================
// write modal
//=====================================================================

function write_modal($url, $modal_id, $text_in_link) {
    return "<a data-toggle='modal' href='#modal_".$modal_id."' data-remote='".$url."' data-target='#modal_".$modal_id."'>".$text_in_link."</a> 
    <div class='modal fade' id='modal_".$modal_id."' tabindex='-1' role='dialog' aria-hidden='true' >
        <div class='modal-dialog modal-dialog-scrollable' role='document'>
            <div class='modal-content'> 
                <div class='modal-body'>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    $('#modal_".$modal_id."').on('show.bs.modal', function (e) {
        var button = $(e.relatedTarget);
        var modal = $(this);
        modal.find('.modal-body').load(button.data('remote'));
    });
    </script>";
}

function write_modal_header($label) {
   
    echo "<div class='modal-header'>
            <h4 class='modal-title' >".$label."</h4>
             <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                    <i class='fa fa-times' aria-hidden='true' title='Fermer cette fenêtre'></i>
             </button>
        </div>";
}
 ?>