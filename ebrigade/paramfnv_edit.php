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
?>
<script type="text/javascript" src="js/paramfn.js"></script>
<?php

$title="Fonction";
if (isset($_GET["TFV_ID"])) $TFV_ID=intval($_GET["TFV_ID"]);
else $TFV_ID=0;


echo "<form name='paramfnv' action='paramfnv_save.php'>";
//=====================================================================
// affiche la fiche
//=====================================================================

if ( $TFV_ID > 0 ) {
$query="select TFV_ID,TFV_ORDER,TFV_NAME,TFV_DESCRIPTION from type_fonction_vehicule
        where TFV_ID=".$TFV_ID;    
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$TFV_ID=$row["TFV_ID"];
$TFV_ORDER=$row["TFV_ORDER"];
$TFV_NAME=$row["TFV_NAME"];
$TFV_DESCRIPTION=$row["TFV_DESCRIPTION"];

echo "<div align=center>
<table class='noBorder'><tr>
<td><font size=4><b>Fonction de véhicule </b></font></td>
<td>
<i class='fa fa-truck fa-3x' style='color:#e64d00;'></i></td>
</tr></table>";
echo "<input type='hidden' name='operation' value='update'>";
echo "<input type='hidden' name='TFV_ID' value='".$TFV_ID."'>";
}
else {
$TFV_NAME='';
$TFV_ORDER='';
$TFV_DESCRIPTION='';
echo "<div align=center><font size=4><b>Nouvelle fonction </b></font> <i class='fa fa-truck fa-3x' style='color:#e64d00;'></i>";
echo "<input type='hidden' name='operation' value='insert'>";
echo "<input type='hidden' name='TFV_ID' value='0'>";
}

echo "<p><table cellspacing=0 border=0>";

//=====================================================================
// ligne 1
//=====================================================================

echo "<tr>
            <td colspan=2 class=TabHeader>Fonction</td>
      </tr>";


//=====================================================================
// ligne nom
//=====================================================================

echo "<tr bgcolor=$mylightcolor >
            <td>
            <b>Nom</b> $asterisk</td>
            <td align=left>
            <input type='text' name='TFV_NAME' value=\"".$TFV_NAME."\"
            title=\"Choisir la description de la fonction, maximum 40 caractères\" >";        
echo "</tr>";


//=====================================================================
// ligne ordre
//=====================================================================

echo "<tr bgcolor=$mylightcolor >
            <td ><b>Ordre dans la liste</b>$asterisk</td>
            <td align=left>
          <select name='TFV_ORDER'
          title=\"Choisir l'ordre de la fonction dans la liste déroulante listant les fonctions applicables pour les véhicules\">";
          for ($i=1 ; $i<=20 ; $i++) {
            if ($TFV_ORDER == $i) $selected="selected";
            else $selected="";
            echo "<option value='$i' $selected>$i</option>";
          }
           echo "</select>";
echo "</tr>";


//=====================================================================
// ligne description
//=====================================================================

echo "<tr bgcolor=$mylightcolor >
            <td>
            <b>Description </b></font></td>
            <td align=left>
            <input type='text' name='TFV_DESCRIPTION' value=\"".$TFV_DESCRIPTION."\"
            title=\"Choisir la description de la fonction, maximum 200 caractères\" >";        
echo "</tr>";

//=====================================================================
// bas de tableau
//=====================================================================

echo "</table>";

echo "<p><input type='submit' class='btn btn-default' id='sauver' value='sauver'> ";
echo "</form>";
if ( $TFV_ID > 0 ) {
    echo "<form name='paramfnv' action='paramfnv_save.php'>";
    echo "<input type='hidden' name='TFV_ID' value='$TFV_ID'>";
    echo "<input type='hidden' name='operation' value='delete'>";
    echo "<input type='submit' class='btn btn-default' value='supprimer'> ";
}
echo "<input type=button class='btn btn-default' value=Retour name=annuler onclick=\"redirect2();\">";
echo "</form>";
echo "</div>";
writefoot();
?>
