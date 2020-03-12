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
<script type='text/javascript' src='js/type_materiel.js'></script>
</script>
";
echo "</head>";
echo "<body>";

//=====================================================================
// affiche la fiche type de matériel
//=====================================================================

$query="select CM_DESCRIPTION,PICTURE from categorie_materiel
        where TM_USAGE='ALL'";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$cmt=$row["CM_DESCRIPTION"];
$picture=$row["PICTURE"];

echo "<div align=center class='table-responsive'><table class='noBorder'>
      <tr><td width = 40 ><i class='fa fa-".$picture." fa-2x' style='color:purple;'></i></td><td>
      <font size=4><b>Nouveau type de matériel</b></font></td></tr></table>";


echo "<form name='materiel' action='save_type_materiel.php'>";
echo "<input type='hidden' name='operation' value='insert'>";
echo "<input type='hidden' name='TM_ID' value='0'>";
echo "<input type='hidden' name='TM_LOT' value='0'>";
    
//=====================================================================
// ligne 1
//=====================================================================

echo "<p><table cellspacing=0 border=0>";
echo "<tr height=10>
            <td class=TabHeader colspan=2>informations type de matériel</td>
      </tr>";


//=====================================================================
// ligne catégorie
//=====================================================================

$query="select TM_USAGE, CM_DESCRIPTION from categorie_materiel
         where TM_USAGE<>'ALL' order by TM_USAGE asc";
$result=mysqli_query($dbc,$query);

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Catégorie</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left>
          <select name='TM_USAGE'  id='TM_USAGE' onchange='change_type();'>";
               while ($row=@mysqli_fetch_array($result)) {
                  if ( $row["TM_USAGE"] == $catmateriel ) $selected='selected';
                  else $selected='';
                  echo "<option value=\"".$row["TM_USAGE"]."\" $selected>".$row["TM_USAGE"]." - ".$row["CM_DESCRIPTION"]."</option>";
              }
 echo "</select>";
 echo "</td>
      </tr>";

//=====================================================================
// ligne code
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor><b>type</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='TM_CODE' size='20' value=''>";        
echo " </td>
      </tr>";
  
      
//=====================================================================
// ligne description
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor><b>Description</b></td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='TM_DESCRIPTION' size='40' value=''>";        
echo " </td>
      </tr>";


//=====================================================================
// vetement taille
//=====================================================================
      
$query="select TT_CODE, TT_NAME, TT_DESCRIPTION from type_taille order by TT_ORDER";
$result=mysqli_query($dbc,$query);

if ( $catmateriel == 'Habillement' ) $style="";
else $style="style='display:none'";

echo "<tr id=row_tt $style>
            <td bgcolor=$mylightcolor ><b>Mesure Taille</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left>
          <select name='TT_CODE' title='choisir le type de mesure pour les tailles de ce vetement'>";
               while ($row=@mysqli_fetch_array($result)) {
                  echo "<option value=\"".$row["TT_CODE"]."\" title=\"".$row["TT_DESCRIPTION"]."\" >".$row["TT_NAME"].": ".$row["TT_DESCRIPTION"]."</option>";
              }
 echo "</select>";
            
      
//=====================================================================
// lot de matériel
//=====================================================================
echo "<tr>
            <td bgcolor=$mylightcolor><b>Lot de matériel</b></td>
            <td bgcolor=$mylightcolor align=left>
            <input type='checkbox' name='TM_LOT' value='1'
            title=\"Cochez la case si ce type définit un lot de matériel\">
            <font size=1><i>des pièces de matériel peuvent être intégrées dans un lot<i></font>";        
echo " </td>
      </tr>";    

echo "</table>";
echo "<p><input type='submit' class='btn btn-default' value='sauver'> ";

echo "<input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">";
echo "</form>";
echo "</div>";
writefoot();
?>
