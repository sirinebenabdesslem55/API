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
check_all(55);
$id = $_SESSION['id'];
get_session_parameters();

if ( check_rights($id , 24)) $highestsection=0;
else {
    $highestsection=get_highest_section_where_granted($id , 55);
    if ( $highestsection == '' ) $highestsection=$_SESSION['SES_SECTION'];
}

$list = preg_split('/,/' , get_family("$highestsection"));
if ( in_array($filter,$list)) $cursection=$filter;
else $cursection=$mysection;

writehead();

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<?php
if ( zipcodes_populated() ) {
    forceReloadJS('js/zipcode.js');
}

echo "</head>";
//=====================================================================
// affiche la fiche personnel
//=====================================================================
echo "<body><div align=center class='table-responsive'><font size=4><b>Ajouter une section<br></b></font>";
echo "<p><table cellspacing=0 border=0>";
echo "<form name='personnel' action='save_section.php' method='POST' >";
print insert_csrf('section');
echo "<input type='hidden' name='S_ID' value='100'>";
echo "<input type='hidden' name='chef' value=''>";
echo "<input type='hidden' name='adjoint' value=''>";
echo "<input type='hidden' name='cadre' value=''>";
echo "<input type='hidden' name='description' value=''>";
echo "<input type='hidden' name='operation' value='insert'>";
//=====================================================================
// ligne 1
//=====================================================================

echo "<tr height=10>
             <td width=300 colspan=2 class=TabHeader>Informations obligatoires</td>
      </tr>";
      
echo "<tr height=5>
            <td bgcolor=$mylightcolor width=300 colspan=2></td>";        
echo "</tr>";

//=====================================================================
// code
//=====================================================================
echo "<tr>
            <td bgcolor=$mylightcolor width=150 ><b>Nom</b> $asterisk</td>
            <td bgcolor=$mylightcolor width=150 align=left height=25><input type='text' name='code' size='25' maxlength='25' value=''>";        
echo "</tr>";

//=====================================================================
// name
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor width=150 >Description$asterisk</td>
            <td bgcolor=$mylightcolor width=150 align=left height=25><input type='text' name='nom' size='40' maxlength='50' value='' >";        
echo "</tr>";

//=====================================================================
// ordre garde
//=====================================================================
if ( $gardes == 1 ) {
    echo "<tr bgcolor=$mylightcolor >
            <td >Ordre garde</td>
            <td align=left ><select name='ordre' >";
    echo "<option value='0' selected>Non défini</option>";
    for ( $i=1; $i < 10; $i++ ) {
        echo "<option value='".$i."'>".$i."</option>";
    }      
    echo "</select></tr>";
}

//=====================================================================
// parent section 
//=====================================================================
if ( $nbsections == 0 ) {
    echo "<tr>
            <td bgcolor=$mylightcolor width=150><b>Sous ".choice_section_order('ins_section.php')." de</b> $asterisk</td>
            <td bgcolor=$mylightcolor width=150 align=left>";
    echo "<select id='parent' name='parent'>";

    if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
    else $sectionorder=$defaultsectionorder;

    if ( $highestsection <> 0 ){
        $level=get_level($highestsection);
        display_children2($highestsection, $level +1, $cursection, $nbmaxlevels - 1, $sectionorder);
    }
    else {
        $mycolor=$myothercolor;
        $class="style='background: $mycolor;'";
        echo "<option value='0' $class >".get_section_code('0')." - ".get_section_name('0')."</option>";
        display_children2(0, 1, $cursection, $nbmaxlevels - 1, $sectionorder);
    }     
    echo "</select></td> ";
    echo "</tr>";

    //=====================================================================
    // intercalaire
    //=====================================================================
    echo "<tr>
            <td width=300 colspan=2 class=TabHeader>
                <i>Informations facultatives</i>
            </td>
          </tr>";

    //=====================================================================
    // ligne address
    //=====================================================================

    echo "<tr bgcolor=$mylightcolor>
                <td width=150 align=left>Adresse</font></td>
                <td width=150 align=left><textarea name='address' cols='30' rows='3' value=''></textarea></td>";
    echo "</tr>";

    echo "<tr bgcolor=$mylightcolor >
                <td align=left><i>Complément d'adresse</i></td>
                <td align=left><input type='text' name='address_complement' size='33' value=''></td>";
    echo "</tr>";

    echo "<tr bgcolor=$mylightcolor >
                <td align=left>Code postal</font></td>
                <td align=left><input type='text' name='zipcode' id='zipcode' maxlength='5' size='5'  value=''></td>";
    echo "</tr>";

    echo "<tr bgcolor=$mylightcolor >
                <td align=left>Ville</font></td>
                <td align=left><input type='text' name='city' id='city' size='30' maxlength='30' value=''>";

    echo  "<div id='divzipcode' 
                style='display: none;
                position: absolute; 
                border-style: solid;
                border-width: 2px;
                background-color: $mylightcolor; 
                border-color: $mydarkcolor;
                width: 450px;
                height: 130px;
                padding: 5px;
                overflow-y: auto'>
                </div>";

    echo "</td>
     </tr>";
    //=====================================================================
    // ligne phone, email
    //=====================================================================

    echo "<tr bgcolor=$mylightcolor >
                <td >Téléphone</td>
                <td align=left>
                <input type='text' name='phone' size='16' maxlength=16 value='' onchange='checkPhone(form.phone,\"\",\"".$min_numbers_in_phone."\")'>";        
    echo "</tr>";

    if ( $assoc ) {
        echo "<tr bgcolor=$mylightcolor >
                <td >TPH veille opérationnelle</td>
                <td align=left>
                <input type='text' name='phone2' size='16' maxlength=16 value='' onchange='checkPhone(form.phone2,\"\",\"".$min_numbers_in_phone."\")'>";
        echo "</tr>";
        echo "<tr bgcolor=$mylightcolor >
                <td >Téléphone formation</td>
                <td align=left>
                <input type='text' name='phone3' size='16' maxlength=16 value='' onchange='checkPhone(form.phone3,\"\",\"".$min_numbers_in_phone."\")'>";
        echo "</tr>";
    }
    echo "<tr bgcolor=$mylightcolor >
                <td >Fax</td>
                <td align=left>
                <input type='text' name='fax' size='16' maxlength=16 value='' onchange='checkPhone(form.fax,\"\",\"".$min_numbers_in_phone."\")'>";
    echo "</tr>";

    if ( $syndicate ) $e="Email président";
    else $e="Email opérationnel";

    echo "<tr bgcolor=$mylightcolor>
                <td >".$e."</font></td>
                <td align=left>
                <input type='text' name='email' size='33' value='' onchange='mailCheck(form.email,\"\")'
                title='Cette adresse est utilisée pour les besoins de la veille opérationnelle.'>";
    echo "</tr>";
    echo "<tr bgcolor=$mylightcolor>
                <td >Email secrétariat</font></td>
                <td align=left>
                <input type='text' name='email2' size='33' value='' onchange='mailCheck(form.email2,\"\")'
                title='Cette adresse email utilisée dans les documents PDF générés, et reçoit toutes les notifications relatives aux événements et au personnel.'>";
    echo "</tr>";

    if ( $syndicate == 0 ) {
        echo "<tr bgcolor=$mylightcolor>
                    <td >Email formation</font></td>
                    <td align=left>
                    <input type='text' name='email3' size='33' value='' onchange='mailCheck(form.email3,\"\")'
                    title='Adresse email utilisée pour les contacts liés aux formations.'>";
        echo "</tr>";
    }
    echo "<tr bgcolor=$mylightcolor>
                <td >Site web</td>
                <td align=left>
                <input type='text' name='url' size='33' value=''>";
    echo "</tr>";
    
    if ( $assoc ) {
        echo "<tr bgcolor=$mylightcolor >
                  <td >SIRET</td>
                  <td align=left><input type='text' name='siret' size='20' title=\"Code SIRET de l'organisation\" autocomplete='off'
                    value=''></td>";
        echo "</tr>";
        echo "<tr bgcolor=$mylightcolor >
                  <td >N° Affiliation</td>
                  <td align=left><input type='text' name='affiliation' size='20' title=\"Numéro d'affiliation l'organisation\" autocomplete='off'
                    value='' ></td>";
        echo "</tr>";
    }
}
else echo "<input type='hidden' id='parent' name='parent' value='0'>";
echo "</table>";
echo "<p><input type='submit' class='btn btn-default' value='sauver'></form>";
echo " <input type='button' class='btn btn-default' value='Annuler' name='annuler' onclick=\"javascript:history.back(1);\"></div>";
writefoot();
?>
