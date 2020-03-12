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
check_all(0);
writehead();
check_all(71);
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);
$section=$_SESSION['SES_SECTION'];
echo "<script type='text/javascript' src='js/consommable.js'></script>";

if (isset ($_GET["C_ID"])) $C_ID = intval($_GET["C_ID"]);
else $C_ID=0;
if (isset ($_GET["TC_ID"])) $TC_ID = intval($_GET["TC_ID"]);
else $TC_ID=0;
if (isset ($_GET["S_ID"])) $S_ID = intval($_GET["S_ID"]);
else $S_ID=0;
if (isset ($_GET["quantity"])) $C_NOMBRE = intval($_GET["quantity"]);
else $C_NOMBRE=0;
if (isset ($_GET["minimum"])) $C_MINIMUM = intval($_GET["minimum"]);
else $C_MINIMUM=0;
if (isset ($_GET["C_DATE_ACHAT"])) $C_DATE_ACHAT = secure_input($dbc,$_GET["C_DATE_ACHAT"]);
else $C_DATE_ACHAT='';
if (isset ($_GET["C_DATE_PEREMPTION"])) $C_DATE_PEREMPTION  = secure_input($dbc,$_GET["C_DATE_PEREMPTION"]);
else $C_DATE_PEREMPTION ='';
if (isset ($_GET["C_DESCRIPTION"])) $C_DESCRIPTION = secure_input($dbc,$_GET["C_DESCRIPTION"]);
else $C_DESCRIPTION='';
if (isset ($_GET["C_LIEU_STOCKAGE"])) $C_LIEU_STOCKAGE = secure_input($dbc,$_GET["C_LIEU_STOCKAGE"]);
else $C_LIEU_STOCKAGE='';

if (isset ($_GET["operation"])) $operation=$_GET["operation"];
else $operation='insert';

// verifier les permissions de modification
if (! check_rights($id, 71,"$S_ID")) {
 check_all(24);
}

if (isset ($_GET["from"])) $from=$_GET["from"];
else $from=0;

if ( $C_DATE_ACHAT <> '') {
    $tmp=explode ("-",$C_DATE_ACHAT); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
    $C_DATE_ACHAT = "\"".$year1."-".$month1."-".$day1."\"";
}
else  $C_DATE_ACHAT = 'null';

if ( $C_DATE_PEREMPTION <> '') {
    $tmp=explode ("-",$C_DATE_PEREMPTION); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
    $C_DATE_PEREMPTION = "\"".$year1."-".$month1."-".$day1."\"";
}
else  $C_DATE_PEREMPTION = 'null';

$query="select TC_PEREMPTION from type_consommable where TC_ID=".$TC_ID;
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
if ($row[0] == '0') $C_DATE_PEREMPTION = 'null';

//=====================================================================
// update la fiche
//=====================================================================

if ( $operation == 'update' ) {
    $query="update consommable set
           TC_ID=\"".$TC_ID."\",
           C_DESCRIPTION=\"".$C_DESCRIPTION."\",
           C_NOMBRE=\"".$C_NOMBRE."\",
           C_MINIMUM=\"".$C_MINIMUM."\",
           C_DATE_ACHAT=".$C_DATE_ACHAT.",
           C_DATE_PEREMPTION=".$C_DATE_PEREMPTION.",
           S_ID=\"".$S_ID."\",
           C_LIEU_STOCKAGE=\"".$C_LIEU_STOCKAGE."\"
           where C_ID =".$C_ID;
    $result=mysqli_query($dbc,$query);
    
    $query="update evenement_consommable set TC_ID=\"".$TC_ID."\"  where C_ID =".$C_ID;
    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert' ) {
   $query="insert into consommable 
   (S_ID, TC_ID, C_DESCRIPTION, C_NOMBRE, C_MINIMUM,C_DATE_ACHAT, C_DATE_PEREMPTION, C_LIEU_STOCKAGE)
   values
   (\"$S_ID\",\"$TC_ID\",\"$C_DESCRIPTION\",$C_NOMBRE, $C_MINIMUM, $C_DATE_ACHAT,$C_DATE_PEREMPTION,\"$C_LIEU_STOCKAGE\")";
   $result=mysqli_query($dbc,$query);
   $_SESSION['filter'] = $S_ID;
}

if ($operation == 'delete' ) {
   echo "<body onload=suppress('".$C_ID."')>";
}
else {
   echo "<body onload=redirect('consommable.php')>";
}
?>
