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
check_all(17);
$id=$_SESSION['id'];
$section=$_SESSION['SES_SECTION'];
get_session_parameters();

$suggestedsection=$section;
if ( check_rights($id, 17, $filter)) $suggestedsection=$filter;
if (isset ($_GET["section"])) $suggestedsection=$_GET["section"];

$mysection=get_highest_section_where_granted($id,17);

if ( check_rights($id, 24) ) $section='0';
else if ( $mysection <> '' ) {
    if ( is_children($section,$mysection)) 
        $section=$mysection;
}

//=====================================================================
// affiche la fiche vehicule
//=====================================================================

writehead();
echo "
<script type='text/javascript' src='js/checkForm.js'></script>
</script>
";
echo "</head>";
echo "<body>";

echo "<div align=center><font size=4><b>Ajout d'un nouveau véhicule<br></b></font>";

echo "<p><table cellspacing=0 border=0>";
echo "<form name='vehicule' action='save_vehicule.php'>";
echo "<input type='hidden' name='V_ID' value=''>";
echo "<input type='hidden' name='groupe' value=''>";
echo "<input type='hidden' name='EQ_ID' value='1'>";
echo "<input type='hidden' name='TV_CODE' value=''>";
echo "<input type='hidden' name='V_IMMATRICULATION' value=''>";
echo "<input type='hidden' name='V_COMMENT' value=''>";
echo "<input type='hidden' name='VP_ID' value=''>";
echo "<input type='hidden' name='V_ANNEE' value=''>";
echo "<input type='hidden' name='V_ASS_DATE' value=''>";
echo "<input type='hidden' name='V_CT_DATE' value=''>";
echo "<input type='hidden' name='V_REV_DATE' value=''>";
echo "<input type='hidden' name='V_INVENTAIRE' value=''>";
echo "<input type='hidden' name='V_INDICATIF' value=''>";
echo "<input type='hidden' name='operation' value='insert'>";
echo "<input type='hidden' name='from' value=''>";
for ( $i = 1 ; $i <= 8 ; $i++) {
    echo "<input type='hidden' name='P".$i."' value=''>";
}
//=====================================================================
// ligne 1
//=====================================================================

echo "<tr class=TabHeader>
             <td bgcolor=$mydarkcolor align=right></td>
            <td bgcolor=$mydarkcolor align=right><b>informations véhicule</b></td>
      </tr>";


//=====================================================================
// ligne type
//=====================================================================

$query2="select distinct TV_CODE, TV_LIBELLE from type_vehicule
         order by TV_CODE";
$result2=mysqli_query($dbc,$query2);

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Type</b>$asterisk</td>
            <td bgcolor=$mylightcolor align=left >
        <select name='TV_CODE' class=smalldropdown>";
while (custom_fetch_array($result2)) {
    echo "<option value='$TV_CODE' class=smalldropdown>$TV_CODE - $TV_LIBELLE</option>";
}
echo "</select>";
echo "</td>
      </tr>";


//=====================================================================
// ligne immatriculation
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Immatriculation</b></td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='V_IMMATRICULATION' size='20' value=''>";        
echo "</tr>";

//=====================================================================
// numéro d'indicatif
//=====================================================================

echo "<tr >
            <td bgcolor=$mylightcolor ><b>Indicatif</b></td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='V_INDICATIF' size='30' value=''>";        
echo " </td>
      </tr>";
//=====================================================================
// ligne année
//=====================================================================

$curyear=date("Y");
$year=$curyear - 30; 
echo "<tr>
            <td bgcolor=$mylightcolor ><b>Année</b></td>
            <td bgcolor=$mylightcolor align=left>
            <select name='V_ANNEE'>";
while ( $year <= $curyear + 1 ) {
    if ( $year == $curyear ) $selected = 'selected';
    else $selected = '';
    echo "<option value='$year' $selected>$year</option>";
    $year++;
}        
echo "</select></tr>";


//=====================================================================
// ligne kilometrage
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Kilométrage</b></td>
            <td bgcolor=$mylightcolor align=left>
            <input type='text' name='V_KM' size='5' value='0' onchange='checkNumber(this,\"0\")'>";
echo "</tr>";


echo "<tr>
            <td bgcolor=$mylightcolor ><b>Prochaine révision</b></td>
            <td bgcolor=$mylightcolor align=left>
            <input type='text' name='V_KM_REVISION' size='5' value='0' onchange='checkNumber(this,\"0\")'>";
echo "</tr>";

//=====================================================================
// ligne modèle
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Modèle</b></td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='V_MODELE' size='25' value=''>";
echo "</tr>";

//=====================================================================
// ligne section
//=====================================================================

if (  $nbsections == 0 ) {
    echo "<tr>
            <td bgcolor=$mylightcolor ><b>Section</b>$asterisk</td>
            <td bgcolor=$mylightcolor align=left>";
    echo "<select id='groupe' name='groupe' class='smalldropdown'>";
     
    $level=get_level($section);
    if ( $level == 0 ) $mycolor=$myothercolor;
    elseif ( $level == 1 ) $mycolor=$my2darkcolor;
    elseif ( $level == 2 ) $mycolor=$my2lightcolor;
    elseif ( $level == 3 ) $mycolor=$mylightcolor;
    else $mycolor='white';
    $class="style='background: $mycolor;'";
    
    if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
    else $sectionorder=$defaultsectionorder;
        
    if ( check_rights($id, 24))
        display_children2(-1, 0, $suggestedsection, $nbmaxlevels, $sectionorder);
    else { 
        echo "<option value='$suggestedsection' $class >".get_section_code($suggestedsection)." - ".get_section_name($suggestedsection)."</option>";
        display_children2($section, $level +1, $suggestedsection, $nbmaxlevels);
    }
    
    echo "</select></td> ";
    echo "</tr>";
}
else echo "<input type='hidden' name='groupe' value='0'>";

//=====================================================================
// ligne type
//=====================================================================
if ( $gardes == 1 ) {
    
    if ( isset($_SESSION['filter']) ) $defaultsection=$_SESSION['filter'];
    else $defaultsection=$_SESSION['SES_SECTION'];
    
    $query2 ="select EQ_ID, EQ_NOM from type_garde ";
    if ( $sdis == 1 ) $query2 .=" where S_ID = ".$defaultsection;
    $query2 .=" order by EQ_ID";
    $result2=mysqli_query($dbc,$query2);

    echo "<tr>
            <td bgcolor=$mylightcolor >
            <b>Usage principal</b></td>
            <td bgcolor=$mylightcolor align=left>
        <select name='EQ_ID'>
        <option value='0' $selected>Aucun</option>";
    while ($row2=@mysqli_fetch_array($result2)) {
        $EQ_ID=$row2["EQ_ID"];
        $EQ_NOM=$row2["EQ_NOM"];
        echo "<option value='$EQ_ID'>$EQ_NOM</option>";
    }
    echo "</select>";
    echo "</tr>";
}

//=====================================================================
// dates d'assurance de contrôle technique et de révision
//=====================================================================

echo "<input type='hidden' name='dc0' value='".getnow()."'>";


// assurance
echo "<tr>
            <td bgcolor=$mylightcolor ><b>Fin assurance</b></td>
            <td bgcolor=$mylightcolor align=left>
            <input type='text' size='10' name='dc1' value='' class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA' autocomplete='off'
            style='width:100px;'>";


// contrôle technique
echo "<tr>
            <td bgcolor=$mylightcolor ><b>Contrôle technique</b></td>
            <td bgcolor=$mylightcolor align=left>
            <input type='text' size='10' name='dc2' value='' class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA' autocomplete='off'
            style='width:100px;'>";

// révision
echo "<tr>
            <td bgcolor=$mylightcolor ><b>Prochaine révision</b></td>
            <td bgcolor=$mylightcolor align=left>
            <input type='text' size='10' name='dc3' value='' class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA' autocomplete='off'
            style='width:100px;'>";

//=====================================================================
// numéro d'inventaire
//=====================================================================

echo "<tr >
            <td bgcolor=$mylightcolor ><b>N°d'inventaire</b></td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='V_INVENTAIRE' size='30' value=''>";
echo " </td>
      </tr>";

      
//=====================================================================
// ligne commentaire
//=====================================================================

echo "<tr bgcolor=$mylightcolor>
          <td ><b>Commentaire</b></td>
          <td ><textarea name='V_COMMENT' cols='33' rows='3' 
            style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;'
            ></textarea></td>";
echo "</tr>";

     
//=====================================================================
// vehicule externe
//=====================================================================

if (check_rights($_SESSION['id'], 24) and ($nbsections ==  0 )) {
echo "<tr>
            <td bgcolor=$mylightcolor ><b>véhicule $cisname</b></td>
            <td bgcolor=$mylightcolor align=left>
            <input type='checkbox' name='V_EXTERNE' value='1'>
            <font size=1><i>mis à disposition (utilisable, non modifiable)<i></font>";
echo " </td>
      </tr>";
}            

echo "</table>";
echo "<p><input type='submit' class='btn btn-default' value='sauver'></form>";
echo " <input type='button' class='btn btn-default' value='Annuler' name='annuler' onclick=\"javascript:history.back(1);\"></div>";

writefoot();
?>
