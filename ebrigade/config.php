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

// version principale ( = database version)
$version="5.1";
// patch version
$patch_version="5.1.0";

$php_minimum_version='5.2.0';
$php_maximum_version='7.4.99';

// colors, do not touch
$mydarkcolor="#191970";
$my2darkcolor="#FFCC33";
$my2lightcolor="#FFFF99";
$myothercolor="#FFC0C0";

//grey
$mygreycolor="#C3C3C3";
$mydarkgreycolor="#808080";
$mylightgreycolor="#FAFAFA";

//green
$mygreencolor="#A5E7A5";
$mygreencolor2="#00FF00";
$mygreencolor3="#CCFF66";

// Déclaration des variables de configuration
$background = "#FFFFFF" ;                                 // Couleur de fond
$textcolor = "#00006B" ;                                  // Couleur du texte
$fontfamily = "Arial" ;                                   // Police d'écran

// colors
$purple="purple";
$red="red";
$green="green";
$brown="#996633";
$orange="orange";
$blue="#0000CC";
$white="#FFFFFF";
$yellow="#FFFF99";

//identification repertoire  
$basedir = dirname(__FILE__);
$curdir = getcwd();
if ( $basedir <> $curdir ) {
$reldir = str_replace ( $basedir,'',$curdir);
$reldir = str_replace ( "\\","/",$reldir );
$subcnt = substr_count($reldir, "/"); 
if ( $subcnt == 1 ) $basedir = '..';
if ( $subcnt == 2 ) $basedir = '../..';
}
else $basedir='.';

//messages d'erreur
$error_pic="<i class='fa fa-exclamation-circle fa-lg' style='color:red'></i>";
$question_pic="<i class='fa fa-question-circle fa-lg' ></i>";
$warning_pic="<i class='fa fa-exclamation-triangle fa-lg' style='color:orange'></i>";
$star_pic="<i class='fa fa-star fa-lg' style='color:green'></i>";
$miniquestion_pic="<i class='fa fa-info-circle' title='voir les habilitations des groupes'></i>";
$asterisk=" <i class='fa fa-asterisk' style='color:red' title='information obligatoire'></i>";

$error_1="Vous devez saisir l'identifiant ou matricule";
$error_2="Vous devez saisir le mot de passe";
$error_3="Les identifiants saisis ne sont pas reconnus.";
$error_4="Votre session a expiré, vous devez vous identifier.";
$error_5="Identification obligatoire";
$error_6="Vous n'êtes pas habilités à utiliser cette fonctionnalité de l'application";
$error_7="L'identifiant ou l'adresse email saisis ne sont pas reconnus.";
$error_8="Les paramètres fournis à la page sont incorrects.";
$question_1="Etes vous certain de vouloir supprimer ";

$mois=array("janvier","février","mars","avril","mai","juin","juillet","août","septembre","octobre","novembre","décembre");
$jours=array("dimanche","lundi","mardi","mercredi","jeudi","vendredi","samedi");

// nombre maximum de backups.
$sql=$basedir."/sql/";
$nbfiles=15;

// Upload files, supported extensions
$supported_ext = array(".doc",".docx",".zip",".pps",".ppt",".pptx",".xls",".xlsx",".pdf",".jpg",".jpeg",".png",".odt", ".mp3"); 

// Allowable file Mime Types. Add more mime types if you want
$supported_mimes = array('image/jpeg','image/jpg','image/gif','image/png','application/msword','application/octet-stream','application/oct-stream',
                        'application/pdf','application/vnd.ms-excel','application/force-download','application/forcedownload',
                        'application/download','application/x-download','application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation','application/vnd.oasis.opendocument.text',
                        'application/vnd.ms-powerpoint','audio/mp3');

// config
$config_file=$basedir."/conf/sql.php";

// optional user guides
$userguide=$basedir."/doc/userguide.doc";
$adminguide=$basedir."/doc/adminguide.pdf";

// photos identite
$trombidir=$basedir."/images/user-specific/trombi";

// doc templates
$userdocdir=$basedir."/images/user-specific/documents";

// maximum size of uploaded docs (take php.ini value)
$MAX_POST_SIZE_MB = intval(str_replace("M", "", ini_get('post_max_size')));
$MAX_FILE_SIZE_MB = intval(str_replace("M", "", ini_get('upload_max_filesize')));
if ( $MAX_POST_SIZE_MB <= $MAX_FILE_SIZE_MB ) $MAX_FILE_SIZE_MB = $MAX_POST_SIZE_MB - 1;
if ( $MAX_FILE_SIZE_MB == 0 ) $MAX_FILE_SIZE_MB = 5;
$MAX_SIZE =  intval(str_replace("M", "", $MAX_FILE_SIZE_MB)) * 1024 * 1024;
$MAX_SIZE_ERROR="La taille du fichier attaché ne doit pas dépasser ".$MAX_FILE_SIZE_MB." MB.";

// durée de conservation des données en purge glissante.
$days_smslog=1825;
$days_disponibilite=60;

// nombre maxi de personnes
$nbmaxpersonnes=500000;
$nbmaxpersonnesparsection=500;

// nombre maxi de compétences 
$nbmaxpostes=300;

// nombre maxi de sections
$nbmaxsections=1000; 

// nombre maxi de niveaux hiérarchiques 
$nbmaxlevels=5;

// nombre maxi de groupes utilisateurs ou rôles dans organigramme
$nbmaxgroupes=50;

// extended time limit (used in backup / restore), tableau garde
$mytimelimit=300;

// nb max de parties 
$nbmaxsessionsparevenement=14;

// output max number of rows
$maxnumrows=500;

// nombre max de destinataires mail dans la page message
$maxdestmessage=400;

// nombre max de destinataires SMS dans la page message 
$maxdestsms=200;
$maxdestsmsgateway=30;

// nombre maxi de messages dans le chat
$maxchatmessages=20;

// password failed block time in minutes (default 30 minutes)
$passwordblocktime=30;

// nombre maxi de lignes sur une note de frais
$maxlignesnotedefrais=15;

// bad mail domains
// email address appear in red with a warning, because we know that mail delivery might not work
$bad_mail_domains=array("wanadoo.fr","orange.fr","hotmail.fr","gmail.fr");

// impression diplomes
$numfields_org=12;

// permettre impression totale du diplôme y compris le fond, sur du papier blanc.
$printfulldiplome=false;

// periode en secondes rfaraichissement auto SITAC
$autorefresh_period=15;

// persistence de la geoloc GPS en minutes
$gps_persistence=60; 

// skype, activé par defaut
$skype_enabled=true;

// nombre de jours pour mettre la photo si photo_obligatoire=1
$limit_days_photo=30;
$limit_start_date='2020-01-23';

// wiki doc page
$wikiurl="http://ebrigade.sourceforge.net/wiki/index.php";

// taille max des mails ou SMS
$maxchar_sms=160;
$maxchar_mail=1000;

// colors report
$colors = array(0xff0000, 0x3300CC, 0x00cc00,
              0xff9900, 0xFF99FF, 0x00CC99, 
              0x996699, 0xFFCC33, 0x666666,  
              0xa0bdc4, 0x999966, 0x333366, 
              0xc3c3e6, 0xc3c3e5, 0xc3c3e3, 
              0xFF3366, 0x5c88c4, 0xf488c4,
              0xba4a4a, 0x97ba99, 0x972399,
              0x653851, 0x133851, 0x51fa13,
              0xfa1337, 0x1e0207, 0xd1df07,
              0xd1dfb9
              );

include_once ($basedir."/fonctions.php");
include_once ($basedir."/fonctions_menu.php");
include_once ($basedir."/fonctions_parameters.php");
include_once ($basedir."/fonctions_mail.php");
include_once ($basedir."/fonctions_gardes.php");
include_once ($basedir."/fonctions_bank.php");
include_once ($basedir."/fonctions_specific.php");

if ( file_exists($config_file)) {
    include_once ($config_file);
    $password = simple_crypto($password, 'd');
}

global $noconnect;
if ( isset($noconnect)) {
    $mylightcolor="#B7D8FB";
    $error_reporting=1;
    $api_key="";
    $army=0;
}
else {
    $dbc=connect();
    $conf_array=extract_conf();
    $already_configured=@$conf_array[-1];
    $dbversion=@$conf_array[1];
    $nbsections=@$conf_array[2];
    $gardes=@$conf_array[3];
    $vehicules=@$conf_array[4];
    $grades=@$conf_array[5];
    $cisname=@$conf_array[6];
    $cisurl=@$conf_array[7];
    $admin_email=@$conf_array[8];
    $sms_provider=@$conf_array[9];
    $sms_user=@$conf_array[10];
    $sms_password=@$conf_array[11];
    $sms_api_id=@$conf_array[12];
    $auto_backup=@$conf_array[13];
    $auto_optimize=@$conf_array[14];
    $password_quality=@$conf_array[15];
    $password_length=@$conf_array[16];
    $password_failure=@$conf_array[17];
    $materiel=@$conf_array[18];
    $chat=@$conf_array[19];
    $identpage=@$conf_array[20];
    $filesdir=@$conf_array[21];
    $evenements=@$conf_array[22];
    $competences=@$conf_array[23];
    $disponibilites=@$conf_array[24];
    $log_actions=@$conf_array[25];
    $cron_allowed=@$conf_array[26];
    $theme=@$conf_array[27];
    $mail_allowed=@$conf_array[28];
    $syndicate=@$conf_array[29];
    $externes=@$conf_array[30];
    $cotisations=@$conf_array[31];
    $bank_accounts=@$conf_array[32];
    $store_confidential_data=@$conf_array[33];
    $days_audit=intval(@$conf_array[34]);
    $geolocalize_enabled=@$conf_array[35];
    $days_log=intval(@$conf_array[36]);
    $maintenance_mode=intval(@$conf_array[37]);
    $application_title=@$conf_array[38];
    $organisation_name=@$conf_array[39];
    $association_dept_name=@$conf_array[40];
    $maintenance_text=@$conf_array[41];
    $document_security=intval(@$conf_array[42]);
    $defaultsectionorder=(@$conf_array[43]);
    $encryption_method=(@$conf_array[44]);
    $consommables=intval(@$conf_array[45]);
    $dispo_periodes=intval(@$conf_array[47]);
    $charte_active=intval(@$conf_array[48]);
    $session_expiration=intval(@$conf_array[49]);
    $webservice_key=@$conf_array[50];
    $deconnect_redirect=@$conf_array[51];
    $sdis=intval(@$conf_array[52]);
    $donotreply=intval(@$conf_array[53]);
    $error_reporting=intval(@$conf_array[54]);
    $snow=intval(@$conf_array[55]);
    $assoc=intval(@$conf_array[56]);
    $api_key=@$conf_array[57];
    $remplacements=intval(@$conf_array[58]);
    $army=intval(@$conf_array[59]);
    $api_provider=@$conf_array[60];
    $notes=@$conf_array[61];
    $licences=@$conf_array[62];
    $block_personnel=intval(@$conf_array[63]);
    $import_api=intval(@$conf_array[64]);
    $import_api_url=@$conf_array[65];
    $import_api_token=@$conf_array[66];
    $lock_mailer=@$conf_array[67];
    $photo_obligatoire=@$conf_array[68];
    $themecolors = extract_colors($theme);
    $mylightcolor  = "#".$themecolors[0];
    $mylightcolor2 = "#".$themecolors[1];
    $mylightcolor3 = "#".$themecolors[2];
    $mylightcolor_hexa = $themecolors[0];
    
    if ( $nbsections > 0 ) $casernesp=1;
    else $casernesp=0;
    if ( $sdis or $casernesp ) $pompiers=1;
    else $pompiers=0;

    if ( $army ) $grades_imgdir='images/grades_army';
    else $grades_imgdir='images/grades_sp';
}

if ( isset ($cisname)) $title="$cisname";
else $title="eBrigade";

// geolocalisation - sur Google une clé est requises pour les installations récentes après juin 2016
$google_maps_url="https://maps.google.com/maps/api/js?a=1";
if ( $api_key <> '' ) $google_maps_url .="&key=".$api_key;
// géolocalisation Google, payante
$google_geocode_api="https://maps.googleapis.com/maps/api/geocode";
// par contre osm est gratuit
$osm_geocode_api="http://api-adresse.data.gouv.fr";
// cartes waze 
$waze_url="https://www.waze.com/ul?navigate=yes&zoom=17";

$debug = false;
if ( $error_reporting == 0 ) error_reporting(0);
else if ( $error_reporting == 1 ) error_reporting(E_ERROR);
else {
    error_reporting(E_ALL);
    if ( $error_reporting == 3 ) 
        $debug = true;
}

// dénominations. departement = $levels[3]; antenne = $levels[4]; 
$levels = array("national","zone","région","département","antenne");
$sous_sections="sous sections";

// dénomination particulières
if ( isset ($nbsections )) {
if ( $nbsections > 0 ) {
    $levels = array("centre","section","section","section","section");
}
if ( $syndicate ) {
    $levels[4]="centre";
    $sous_sections="centres";
}
else if ( $sdis ) {
    $levels = array("SDIS","arrondissement","compagnie","centre de secours","section");
}
else if ( $army ) {
    $levels = array("armée","division","régiment","escadron","section");
}
if ( $syndicate == 1 ) $renfort_label="participation";
else $renfort_label="renfort";
}

// temporarily disabled, needs more testing
$allow_sdis_mode = true;

//-----------------------------------------------------
// Regional settings
//-----------------------------------------------------
// pays par défaut (victimes), 65 = France (table pays)
$default_pays_id=65;
// geolocalisation, pays par defaut
$geolocalize_default_country='FRANCE';
// money
$default_money="Euro";
$default_money_symbol="€";
// Phone prefix
$phone_prefix="+33";
// nombre minimum de numéros requis si commence par zéro
$min_numbers_in_phone="10";
// timezone
date_default_timezone_set( 'Europe/Paris' );

//-----------------------------------------------------
// Optional configuration, can override defaults
//-----------------------------------------------------
$config_file_optional=$basedir."/conf/optional.php";
if ( file_exists($config_file_optional)) {
    include_once ($config_file_optional);
}


$starttime = get_time();

