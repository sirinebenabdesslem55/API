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
  
include_once ("config.php");
check_all(18);
$section=$_SESSION['SES_SECTION'];
?>

<html>
<head>
<script type='text/javascript' src='js/type_vehicule.js'></script>
</head>
<?php

include_once ("config.php");
$OLD_TV_CODE=secure_input($dbc,$_POST["OLD_TV_CODE"]);
$TV_CODE=secure_input($dbc,$_POST["TV_CODE"]);
$TV_NB=intval($_POST["TV_NB"]);
$TV_USAGE=secure_input($dbc,$_POST["TV_USAGE"]);
$TV_LIBELLE=secure_input($dbc,$_POST["TV_LIBELLE"]);
$TV_ICON=secure_input($dbc,$_POST["icon"]);
$operation=$_POST["operation"];

$TV_CODE=STR_replace("\"","",$TV_CODE);
$TV_LIBELLE=STR_replace("\"","",$TV_LIBELLE);

if (isset ($_POST["from"])) $from=$_POST["from"];
else $from=0;

//=====================================================================
// check data
//=====================================================================

if ( $TV_CODE <> $OLD_TV_CODE ) {
    $query="select count(1) from type_vehicule where TV_CODE='".$TV_CODE."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    if ( $row[0] > 0 ) {
        write_msgbox("erreur", $error_pic, "Le type de v�hicule choisi ( ".$TV_CODE." ) existe d�j� dans la base de donn�es.<br> Il doit �tre unique.<p align=center><input type=submit class='btn btn-default' value='retour' onclick=\"javascript:goback('".$operation."','".$OLD_TV_CODE."');\">",10,0);
        exit;
    }
    
    if (  $TV_CODE == '' ) {
        write_msgbox("erreur", $error_pic, "Le code pour le type de v�hicule choisi doit �tre renseign�.<br> Et il doit �tre unique.<p align=center><input type=submit class='btn btn-default' value='retour' onclick=\"javascript:goback('".$operation."','".$OLD_TV_CODE."');\">",10,0);
        exit;
    }
}

//=====================================================================
// update la fiche
//=====================================================================

if ( $operation == 'update' ) {
    $query="update type_vehicule set
           TV_CODE=\"".$TV_CODE."\",
           TV_USAGE=\"".$TV_USAGE."\",
           TV_LIBELLE=\"".$TV_LIBELLE."\",
           TV_ICON=\"".$TV_ICON."\",
           TV_NB=".$TV_NB."
           where TV_CODE ='".$OLD_TV_CODE."'";
    $result=mysqli_query($dbc,$query);
    
    if ( $TV_CODE <> $OLD_TV_CODE ) {
        $query="update vehicule set TV_CODE='".$TV_CODE."' where TV_CODE='".$OLD_TV_CODE."'";
        $result=mysqli_query($dbc,$query);
        
        $query="update type_vehicule_role set TV_CODE='".$TV_CODE."' where TV_CODE='".$OLD_TV_CODE."'";
        $result=mysqli_query($dbc,$query);
    }
}

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert' ) {
   $query="insert into type_vehicule (TV_CODE, TV_LIBELLE, TV_USAGE,TV_NB, TV_ICON)
   values (\"$TV_CODE\",\"$TV_LIBELLE\",\"$TV_USAGE\",$TV_NB,\"$TV_ICON\")";
   $result=mysqli_query($dbc,$query);
}

//=====================================================================
// update roles
//=====================================================================

$query="delete from type_vehicule_role where TV_CODE='".$TV_CODE."'";
$result=mysqli_query($dbc,$query);

for ( $i = 1 ; $i <= $TV_NB ; $i++ ) {
    if ( isset ($_POST["ROLE_$i"])) {
        $ROLE_NAME = secure_input($dbc, $_POST["ROLE_$i"]);
        if ( isset($_POST["PS_$i"])) $PS_ID = intval($_POST["PS_$i"]);
        else $PS_ID = 0;
        $query="insert into type_vehicule_role (TV_CODE, ROLE_ID, ROLE_NAME, PS_ID)
                values ('".$TV_CODE."',".$i.", \"".$ROLE_NAME."\", ".$PS_ID.")";
        $result=mysqli_query($dbc,$query);
    }
}

if ($operation == 'delete' )
   echo "<body onload=suppress('".$TV_CODE."') />";
else 
   echo "<body onload=redirect('type_vehicule.php') />";
   
 echo "<html>";
?>
