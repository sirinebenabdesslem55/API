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

?>
<script type="text/javascript" src="js/poste.js"></script>
<?php

if (isset($_GET["PS_ID"])) $PS_ID=intval($_GET["PS_ID"]);
else $PS_ID=0;
if (isset ($_GET["PS_ORDER"])) $PS_ORDER=intval($_GET["PS_ORDER"]);
$operation=$_GET["operation"];
if (isset ($_GET["TYPE"])) $TYPE=secure_input($dbc,$_GET["TYPE"]);
if (isset ($_GET["DESCRIPTION"])) $DESCRIPTION=secure_input($dbc,$_GET["DESCRIPTION"]);
if (isset ($_GET["PS_EXPIRABLE"])) $PS_EXPIRABLE=intval($_GET["PS_EXPIRABLE"]);
if (isset ($_GET["PS_AUDIT"])) $PS_AUDIT=intval($_GET["PS_AUDIT"]);
if (isset ($_GET["PS_DIPLOMA"])) $PS_DIPLOMA=intval($_GET["PS_DIPLOMA"]);
else $PS_DIPLOMA=0;
if (isset ($_GET["PS_NUMERO"])) $PS_NUMERO=intval($_GET["PS_NUMERO"]);
if (isset ($_GET["PS_FORMATION"])) $PS_FORMATION=intval($_GET["PS_FORMATION"]);
if (isset ($_GET["PS_SECOURISME"])) $PS_SECOURISME=intval($_GET["PS_SECOURISME"]);
if (isset ($_GET["PS_NATIONAL"])) $PS_NATIONAL=intval($_GET["PS_NATIONAL"]);
if (isset ($_GET["PS_PRINTABLE"])) $PS_PRINTABLE=intval($_GET["PS_PRINTABLE"]);
else $PS_PRINTABLE=0;
if (isset ($_GET["PS_PRINT_IMAGE"])) $PS_PRINT_IMAGE=intval($_GET["PS_PRINT_IMAGE"]);
else $PS_PRINT_IMAGE=0;
if (isset ($_GET["PS_RECYCLE"])) $PS_RECYCLE=intval($_GET["PS_RECYCLE"]);
if (isset ($_GET["PS_USER_MODIFIABLE"])) $PS_USER_MODIFIABLE=intval($_GET["PS_USER_MODIFIABLE"]);
if (isset ($_GET["EQ_ID"])) $EQ_ID=intval($_GET["EQ_ID"]);
if (isset ($_GET["F_ID"])) $F_ID=intval($_GET["F_ID"]);
if (isset ($_GET["PH_CODE"])) $PH_CODE=secure_input($dbc,$_GET["PH_CODE"]);
else $PH_CODE = '';
if (isset ($_GET["PH_LEVEL"])) $PH_LEVEL=intval($_GET["PH_LEVEL"]);
if ( $PH_CODE == '' ) {
    $PH_CODE="null";
    $PH_LEVEL="null";
}
else $PH_CODE = "\"".$PH_CODE."\"";
if ( $PS_DIPLOMA == 0 ) $PS_PRINTABLE = 0;
if ( $PS_DIPLOMA == 0 ) $PS_NUMERO = 0;
if ( $PS_PRINTABLE == 0 ) $PS_PRINT_IMAGE=0;
//=====================================================================
// update la fiche
//=====================================================================

if ( $operation == 'update' ) {
   $query="update poste set
           PS_ORDER=".$PS_ORDER.",
           TYPE=\"".$TYPE."\",
           DESCRIPTION=\"".$DESCRIPTION."\",
           F_ID=".$F_ID.",
           EQ_ID=".$EQ_ID.",
           PS_FORMATION=".$PS_FORMATION.",
           PS_EXPIRABLE=".$PS_EXPIRABLE.",
           PS_AUDIT=".$PS_AUDIT.",
           PS_DIPLOMA=".$PS_DIPLOMA.",
           PS_NUMERO=".$PS_NUMERO.",
           PS_SECOURISME=".$PS_SECOURISME.",
           PS_NATIONAL=".$PS_NATIONAL.",
           PS_PRINTABLE=".$PS_PRINTABLE.",
           PS_PRINT_IMAGE=".$PS_PRINT_IMAGE.",
           PS_RECYCLE=".$PS_RECYCLE.",
           PS_USER_MODIFIABLE=".$PS_USER_MODIFIABLE.",
           PH_CODE=".$PH_CODE.",
           PH_LEVEL=".$PH_LEVEL."
    where PS_ID=".$PS_ID ;
    $result=mysqli_query($dbc,$query);
   
    if ( $PS_EXPIRABLE == 0 ) {
        $query1="update qualification set
           Q_EXPIRATION=null
           where PS_ID=".$PS_ID ;
           $result1=mysqli_query($dbc,$query1);
   
    }
}

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert' ) {
   $query="select max(PS_ID) from poste";
   $result=mysqli_query($dbc,$query);
   $row=@mysqli_fetch_array($result);
   $NEXTPS_ID=intval($row[0]) + 1;
    
   $query="insert into poste
   (PS_ID, EQ_ID, TYPE, DESCRIPTION, PS_EXPIRABLE, PS_AUDIT, PS_FORMATION, 
       PS_DIPLOMA, PS_NUMERO, PS_SECOURISME, PS_NATIONAL, PS_PRINTABLE, PS_PRINT_IMAGE,  PS_RECYCLE, PS_USER_MODIFIABLE, F_ID, PH_CODE, PH_LEVEL)
   values
   ($NEXTPS_ID, $EQ_ID,\"$TYPE\",\"$DESCRIPTION\", $PS_EXPIRABLE, $PS_AUDIT, $PS_FORMATION, 
   $PS_DIPLOMA, $PS_NUMERO, $PS_SECOURISME, $PS_NATIONAL, $PS_PRINTABLE, $PS_PRINT_IMAGE, $PS_RECYCLE, $PS_USER_MODIFIABLE, $F_ID, $PH_CODE, $PH_LEVEL)";
   $result=mysqli_query($dbc,$query);
}

if ($operation == 'delete' ) {
   echo "<body onload=suppress('$PS_ID')>";
}
else {
   echo "<body onload=redirect()>";
}
?>
