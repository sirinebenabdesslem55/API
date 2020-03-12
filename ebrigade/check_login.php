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
  
session_start();
include_once ("config.php");
include_once ("fonctions_sql.php");
require_once('browscap.php');
$b=get_browser_ebrigade();
$nomenu=1;
writehead();

?>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<SCRIPT language=JavaScript>
function redirect(url) {
     self.location.href=url;
}
</SCRIPT>

<?php
$name=str_replace("_","",strtolower($cisname));
$name=str_replace(".","",$name);
$name=str_replace(" ","",$name);
$name=str_replace("/","",$name);
$dbversion=get_conf(1);
$filesdir=get_conf(21);
if (!is_dir($filesdir) and $filesdir <> "") mkdir($filesdir, 0777);
$maintenance_mode=get_conf(37);
if ( $filesdir == "" ) $filesdir=".";
$url=$identpage;

if ( isset($_POST["id"])) $id=secure_input($dbc,$_POST["id"]); 
else $id="";
if ( isset($_POST["pwd"])) $pwd=secure_input($dbc,$_POST["pwd"]);
else $pwd="";

$path=$filesdir."/save";
echo "<body><div align=center>";

// ==================================
// upgrade database if needed
// ==================================
if ( check_ebrigade() == 1  and  $version <> $dbversion ) {
    echo "<p><table class='noBorder'><tr><td width=60><b>ATTENDEZ</b></td></tr></table><p>";
    write_msgbox("upgrade en cours", "", "Une mise à jour de votre base de données est en cours de $dbversion vers $version. Cette opération peut prendre quelques minutes. Merci de patienter ...",10,0);
    echo "</div><script>
    window.onload=redirect('upgrade.php') ;
    </script>";
    exit;
}
else if ( check_ebrigade() == 0 ) {
    // load reference schema if needed
    create_sql_functions();
    load_reference_schema();
    load_zipcodes();
    echo "<p>";
    exit;
}

// ==================================
// check parameters: try to connect
// ==================================
if ($id == "" ){
   write_msgbox("erreur connexion", $error_pic, $error_1."<br><p align=center><input type='button' class='btn btn-default' value='retour' onclick=\"redirect('".$url."');\"> ",10,0);
}
elseif ($pwd == "" ){
   write_msgbox("erreur connexion", $error_pic, $error_2."<br><p align=center><input type='button' class='btn btn-default' value='retour' onclick=\"redirect('".$url."');\"> ",10,0);
}
else {
     
    $dbc=connect();
    
    // ================================================
    // vérifier qu'un utilisateur avec ce mot de passe
    // ================================================
    $query="select P_ID, P_MDP, LENGTH(P_MDP) 'MDP_SIZE', P_PASSWORD_FAILURE, P_LICENCE, P_LICENCE_EXPIRY, datediff(P_LICENCE_EXPIRY,NOW()) as DAYS from pompier where P_CODE=\"".$id."\"";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $P_PASSWORD_FAILURE=intval($row["P_PASSWORD_FAILURE"]);
    $P_LICENCE=$row["P_LICENCE"];
    $P_ID=$row["P_ID"];
    $P_LICENCE_EXPIRY=$row["P_LICENCE_EXPIRY"];
    if ( $P_LICENCE_EXPIRY <> '' ) $DAYS=$row["DAYS"];
    else $DAYS=1;
    $MDP_SIZE=$row["MDP_SIZE"];
    $valid =  my_validate_password($pwd, $row["P_MDP"]);

    if ( ! $valid ) {
        if ( $password_failure > 0 ) {
            if ( $P_PASSWORD_FAILURE > 0 ) 
                $query="update pompier set P_PASSWORD_FAILURE=P_PASSWORD_FAILURE + 1, P_LAST_CONNECT=NOW() where P_CODE='".$id."'";
            else
                $query="update pompier set P_PASSWORD_FAILURE=1, P_LAST_CONNECT=NOW() where P_CODE='".$id."'";
            $result=mysqli_query($dbc,$query);
        }
        write_msgbox("erreur connexion", $error_pic, $error_3."<p align=center><input type='button' class='btn btn-default' value='retour' onclick=\"redirect('".$url."');\"> ",10,0);
    }
    else
    {
        // create session
        create_session($P_ID);
        
        // case new encryption bcrypt
        if ( $encryption_method == 'bcrypt' ) {
            if ( $MDP_SIZE < 50 ) rehash_password ($P_ID, $pwd);
        }
        // case obsolete pbkdf2 encryption method has been used, revert to md5 encryption
        else if ( $MDP_SIZE > 50 ) {
            rehash_password ($P_ID, $pwd);
        }
    
        // trigger processes if needed
        if ( $auto_optimize == 1 ) {
            $query=" select P_ID from audit where TO_DAYS(NOW()) = TO_DAYS(A_DEBUT)";
            $result=mysqli_query($dbc,$query);
            if ( mysqli_num_rows($result) == 1 ) {
                @set_time_limit($mytimelimit);
                cleanup_ics("$basedir");
                database_cleanup();
                database_optimize();
                specific_maintenance();
            }
        }
        if ( $auto_backup == 1 ) {
            //  backup de la base
            if (!is_dir($path)) mkdir($path, 0777);
            $cur_datetime=date("Y-m-d");
            $backupfile=$path."/".$name."_".$cur_datetime."_".$dbversion.".sql";
            if (! is_file($backupfile)) {
                include_once ("backup.php");
            }
        }
    
        // si pas de licence valide, connexion refusee
        if ( $licences == 1 and $block_personnel == 1 and ! check_rights($P_ID, 14) and ( $P_LICENCE == '' or $DAYS < 0 )) {
            if ( $P_LICENCE == '' ) $m= " n'est pas valide";
            if ( $DAYS < 0 ) $m= " est périmée";
            write_msgbox("Pas de licence valide",$warning_pic,"Votre licence ".$m.", vous ne pouvez pas utiliser $application_title.<p><input type='button' class='btn btn-default' value='retour' 
                            onclick=\"redirect('".$url."');\">",30,30);
            session_destroy();
        }
        else if ( $maintenance_mode == 1 and ! check_rights($P_ID, 14)) {
            write_msgbox("Maintenance en cours",$warning_pic,$maintenance_text."<p><input type='button' class='btn btn-default' value='retour' onclick=\"redirect('".$url."');\">",30,30);
            session_destroy();
        }
        else  {
            // now redirect to the right page
            $accept_date=get_accept_date ($P_ID);
            if ( $accept_date == '' and $charte_active ) $target='charte.php';
            else $target='index.php';
            echo "<body onload=redirect('".$target."')></body>";
        }
    }
}

writefoot($loadjs=false);
?>

