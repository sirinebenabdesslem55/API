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
get_session_parameters();

writehead();
echo "
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/type_consommable.js'></script>";
echo "</head>";
echo "<body>";

$TC_ID=$_GET["id"];
if (isset ($_GET["from"])) $from=$_GET["from"];
else $from=0;

//=====================================================================
// affiche la fiche type de consommable
//=====================================================================
$query="select tc.TC_ID, tc.CC_CODE, tc.TC_DESCRIPTION, tc.TC_CONDITIONNEMENT, tc.TC_UNITE_MESURE,
        cc.CC_NAME, cc.CC_DESCRIPTION, cc.CC_IMAGE, cc.CC_ORDER,
        tco.TCO_CODE, tco.TCO_DESCRIPTION, 
        tc.TC_QUANTITE_PAR_UNITE, tc.TC_UNITE_MESURE,
        tum.TUM_CODE, tum.TUM_DESCRIPTION, tc.TC_PEREMPTION
        from type_consommable tc, categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum
        where tc.CC_CODE=cc.CC_CODE
        and tum.TUM_CODE = tc.TC_UNITE_MESURE
        and tco.TCO_CODE = tc.TC_CONDITIONNEMENT
        and tc.TC_ID='".$TC_ID."'";
        
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);
$TC_DESCRIPTION=ucfirst($TC_DESCRIPTION);

echo "<div class='table-responsive' align=center>
<form name='consommable' action='save_type_consommable.php' method='POST'>";

// update
if ( $TC_ID > 0 ) {
    echo "<input type='hidden' name='operation' value='update'>
          <input type='hidden' name='TC_ID' value='$TC_ID'>";

    $query="select sum(C_NOMBRE) from consommable where TC_ID='".$TC_ID."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $nombre=intval($row[0]);
    echo "<table class='noBorder'>
      <tr><td width='50'><i class='fa fa-".$CC_IMAGE." fa-3x' style='color:saddlebrown;'></i></td><td>
      <font size=4><b>".$TC_DESCRIPTION."</b></font><br><span class='badge'>".$nombre."</span> articles de ce type</td></tr></table>";
}
// insert
else {

    echo "<input type='hidden' name='operation' value='insert'>
          <input type='hidden' name='TC_ID' value='0'>";

    $query="select CC_CODE, CC_NAME , CC_IMAGE from categorie_consommable";
    if ( $catconso <> 'ALL' ) 
        $query .= " where CC_CODE='".$catconso."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $CC_CODE=$row["CC_CODE"];
    $CC_NAME=$row["CC_NAME"];
    $CC_IMAGE=$row["CC_IMAGE"];
    $cmt="Nouveau type de consommable";
    echo "<div align=center><table class='noBorder'>
      <tr><td width='70'><i class='fa fa-".$CC_IMAGE." fa-3x' style='color:saddlebrown;'></i></td><td>
      <font size=4><b>".$cmt."</b></font></td></tr></table>";
}

//=====================================================================
// ligne 1
//=====================================================================

echo "<p><table cellspacing=0 border=0>";
echo "<tr height=10>
            <td class=TabHeader colspan=2>informations type de consommable</td>
      </tr>";


//=====================================================================
// ligne cat�gorie
//=====================================================================

$query="select CC_CODE, CC_NAME from categorie_consommable
         where CC_CODE<>'ALL' order by CC_ORDER asc";
$result=mysqli_query($dbc,$query);

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Cat�gorie</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left>
          <select name='CC_CODE' >";
               while ($row=@mysqli_fetch_array($result)) {
                  if ( $row["CC_CODE"] == $CC_CODE ) $selected='selected';
                  else $selected='';
                  echo "<option value=\"".$row["CC_CODE"]."\" $selected>".$row["CC_CODE"]." - ".$row["CC_NAME"]."</option>";
              }
 echo "</select>";
 echo "</td>
      </tr>";

//=====================================================================
// ligne code
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Type</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='TC_DESCRIPTION' size='40' value=\"$TC_DESCRIPTION\">";        
echo " </td>
      </tr>";
      
//=====================================================================
// ligne conditionnement
//=====================================================================

$query="select TCO_CODE, TCO_DESCRIPTION from type_conditionnement
        order by TCO_ORDER asc";
$result=mysqli_query($dbc,$query);

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Conditionnement</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left>
          <select name='TCO_CODE'>";
               while ($row=@mysqli_fetch_array($result)) {
                  if ( $row["TCO_CODE"] == $TCO_CODE ) $selected='selected';
                  else $selected='';
                  echo "<option value=\"".$row["TCO_CODE"]."\" $selected>".$row["TCO_DESCRIPTION"]."</option>";
              }
echo "</select>";
echo "</td>
      </tr>";
     
//=====================================================================
// ligne quantit� par conditionnement et unit� de mesure
//=====================================================================

$query="select TUM_CODE, TUM_DESCRIPTION from type_unite_mesure
        order by TUM_ORDER asc";
$result=mysqli_query($dbc,$query);

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Contenance </b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left>
          <input type='text' size='3' maxlength='4' name='TC_QUANTITE_PAR_UNITE' 
            value='".$TC_QUANTITE_PAR_UNITE."' title='Pr�cisez la quantit� ou le nombre pour une unit� de conditionnement'
            onchange=\"checkFloat(this,'".$TC_QUANTITE_PAR_UNITE."')\";>
          <select name='TUM_CODE' title='pr�cisez l'unit� de mesure'>";
               while ($row=@mysqli_fetch_array($result)) {
                  if ( $row["TUM_CODE"] == $TUM_CODE ) $selected='selected';
                  else $selected='';
                  echo "<option value=\"".$row["TUM_CODE"]."\" $selected>".$row["TUM_DESCRIPTION"]."s</option>";
              }
echo "</select>";
echo "</td>
      </tr>";
  
//=====================================================================
// lot de mat�riel
//=====================================================================
if ( $TC_PEREMPTION == 1 ) $checked='checked';
else $checked='';

echo "<tr>
            <td bgcolor=$mylightcolor><b>P�rissable</b></td>
            <td bgcolor=$mylightcolor align=left>
            <input type='checkbox' name='TC_PEREMPTION' value='1' $checked
            title=\"Cochez la case si ce type de consommable est p�rissable\">
            <font size=1><i>denr�e p�rissable, avec une date limite<i></font>";
echo " </td>
      </tr>";

echo "</table>";
echo "<p><input type='submit' class='btn btn-default' value='sauver'> ";
if ( $TC_ID <> 0 ) {
    echo "</form><form name='consommable2' action='save_type_consommable.php' method='POST'>";
    echo "<input type='hidden' name='operation' value='delete'>";
    echo "<input type='hidden' name='TC_ID' value='$TC_ID'>";
    echo "<input type='submit' class='btn btn-default' value='supprimer'> ";
}
echo "<input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">";
echo "</form>";
echo "</div>";
writefoot();
?>
