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
check_all(37);

if ( isset($_GET["type"])) $type=secure_input($dbc,$_GET["type"]);
else $type='ALL';

$section=$_SESSION['SES_SECTION'];
$mysection=get_highest_section_where_granted($_SESSION['id'],37);
if ( check_rights($_SESSION['id'], 24) ) $section='0';
else if ( $mysection <> '' ) {
     if ( is_children($section,$mysection)) 
         $section=$mysection;
}

writehead();
echo "
<script type='text/javascript' src='js/checkForm.js'></script>
</script>
";
echo "</head>";
echo "<body>";

//=====================================================================
// affiche la fiche entreprise
//=====================================================================

echo "<div align=center><table class='noBorder'>
      <tr><td>
      <font size=4><b>Nouvelle entreprise cliente</b></font></td></tr></table>";


echo "<form name='company' action='save_company.php'>";
echo "<input type='hidden' name='operation' value='insert'>";

//=====================================================================
// ligne 1
//=====================================================================

echo "<table cellspacing=0 border=0>";
echo "<tr class=TabHeader>
            <td class=TabHeader colspan=2>informations entreprise</td>
      </tr>";


//=====================================================================
// ligne type
//=====================================================================

$query="select TC_CODE,TC_LIBELLE from type_company order by TC_LIBELLE";
$result=mysqli_query($dbc,$query);

echo "<tr>
            <td bgcolor=$mylightcolor width=200><b>Type</b> $asterisk</td>
            <td bgcolor=$mylightcolor width=250 align=left>
          <select name='TC_CODE'>";
               while ($row=@mysqli_fetch_array($result)) {
                  if ( $row["TC_CODE"] == $type ) $selected='selected';
                  else $selected='';
                  echo "<option value='".$row["TC_CODE"]."' $selected>".$row["TC_LIBELLE"]."</option>";
              }
 echo "</select>";
 echo "</td>
      </tr>";

//=====================================================================
// ligne code
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Nom</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='C_NAME' size='20' value=''>";
echo " </td>
      </tr>";
      
//=====================================================================
// ligne section
//=====================================================================

if (  $nbsections == 0 ) {
    echo "<tr>
            <td bgcolor=$mylightcolor ><b>Section de rattachement</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left>";
     echo "<select id='groupe' name='groupe' class=smallcontrol2>";
     
     $level=get_level($section);
     if ( $level == 0 ) $mycolor=$myothercolor;
    elseif ( $level == 1 ) $mycolor=$my2darkcolor;
    elseif ( $level == 2 ) $mycolor=$my2lightcolor;
    elseif ( $level == 3 ) $mycolor=$mylightcolor;
    else $mycolor='white';
    $class="style='background: $mycolor;'";
    if ( isset($_SESSION['filter']) ) $defaultsection=$_SESSION['filter'];
    else $defaultsection=$_SESSION['SES_SECTION'];
    
    if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
    else $sectionorder=$defaultsectionorder;
    
    if ( check_rights($_SESSION['id'], 24))
         display_children2(-1, 0, $defaultsection, $nbmaxlevels, $sectionorder);
    else { 
        echo "<option value='$section' $class >".get_section_code($section)." - ".get_section_name($section)."</option>";
        display_children2($section, $level +1, $defaultsection, $nbmaxlevels);
    }
     
    echo "</select></td> ";
    echo "</tr>";      
}
else echo "<input type='hidden' name='groupe' value='0'>";

//=====================================================================
// parent company 
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Etablissement secondaire de</b></font></td>
            <td bgcolor=$mylightcolor align=left>";
echo "<select id='parent' name='parent' class=smallcontrol2>";
echo "<option value='null'>aucun</option>";

$query="select C_ID, C_NAME, C_DESCRIPTION from company where S_ID=0 and C_ID > 0 order by C_NAME";
$result=mysqli_query($dbc,$query);
while ( $row=@mysqli_fetch_array($result)) {
     $code=$row["C_NAME"];
     if ( $row["C_DESCRIPTION"] <> "" ) $code .=" - ".$row["C_DESCRIPTION"];
    echo "<option value='".$row["C_ID"]."'>".$code."</option>";
}
echo "</select></td> ";
echo "</tr>";      

//=====================================================================
// ligne description
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Description</b></td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='C_DESCRIPTION' size='40' value=''>";        
echo " </td>
      </tr>";
      
//=====================================================================
// ligne siret
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor ><b>N° SIRET</b></td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='C_SIRET' size='30' value='' onchange='checkNumber(form.C_SIRET,\"\")'>";        
echo " </td>
      </tr>";


//=====================================================================
// ligne address
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor align=right>Adresse</td>
            <td bgcolor=$mylightcolor align=left>
            <textarea name='address' cols='20' rows='3' value='' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' ></textarea></td>";
echo "</tr>";

echo "<tr>
            <td bgcolor=$mylightcolor align=right>Code postal</td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='zipcode' size='10' value=''></td>";
echo "</tr>";

echo "<tr>
            <td bgcolor=$mylightcolor align=right>Ville</td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='city' size='20' value=''></td>";
echo "</tr>";

//=====================================================================
// ligne contact
//=====================================================================

echo "<tr id=uRow2>
            <td bgcolor=$mylightcolor align=right>Nom du contact</td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='relation_nom' size='20' value=''></td>";
echo "</tr>";

//=====================================================================
// ligne phone
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor align=right>Téléphone</td>
            <td bgcolor=$mylightcolor align=left>
            <input type='text' name='phone' size='20' value='' onchange='checkPhone(form.phone,\"\",\"".$min_numbers_in_phone."\")'>";        
echo "</tr>";

echo "<tr>
            <td bgcolor=$mylightcolor align=right>Fax</td>
            <td bgcolor=$mylightcolor align=left>
            <input type='text' name='fax' size='20' value='' onchange='checkPhone(form.fax,\"\",\"".$min_numbers_in_phone."\")'>";        
echo "</tr>";

//=====================================================================
// ligne email
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor align=right>E-Mail</td>
            <td bgcolor=$mylightcolor align=left>
                <input type='text' name='email' size='25'
            value='' onchange='mailCheck(form.email,\"\")'></td>";    
echo "</tr>";
      
echo "</table>";
echo "<p><input type='submit' class='btn btn-default' value='sauver'> ";

echo "<input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">";
echo "</form>";
echo "</div>";

writefoot();
?>
