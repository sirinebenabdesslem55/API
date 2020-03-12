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

$C_ID=intval($_GET["C_ID"]);
if ( $C_ID == 0 ) $C_ID=intval($_SESSION['SES_COMPANY']);

if ( $C_ID == 0 ) check_all(37);
else if ($C_ID == $_SESSION['SES_COMPANY'] and ! check_rights($_SESSION['id'], 45)) check_all(45);
else if (! check_rights($_SESSION['id'], 45) or $C_ID <> $_SESSION['SES_COMPANY'] )
check_all(37);

if ( check_rights($_SESSION['id'], 24)) $section='0';
else $section=$_SESSION['SES_SECTION'];

if ( isset($_GET["from"]))$from=$_GET["from"];
else $from="default";

$query="select TC_CODE, C_NAME, S_ID, C_DESCRIPTION, C_CREATED_BY, DATE_FORMAT(C_CREATE_DATE, '%d-%m-%Y') C_CREATE_DATE,
        C_ADDRESS, C_ZIP_CODE, C_CITY, C_EMAIL, C_PHONE, C_FAX, C_CONTACT_NAME, C_PARENT, C_SIRET
        FROM company
        where C_ID =".$C_ID;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

if ( check_rights($_SESSION['id'], 37, "$S_ID")) {
    $granted=true;
    $disabled='';
}
else {
    $granted=false;
    $disabled='disabled';
}
writehead();
echo "
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/company.js'></script>
";
echo "</head>";
echo "<body>";

//=====================================================================
// affiche la fiche entreprise
//=====================================================================

echo "<div align=center><table class='noBorder'>
      <tr><td>
      <font size=4><b>".$C_NAME."</b></font></td></tr></table>";

$query1="select count(*) as NB from pompier where P_STATUT='EXT' and C_ID = ".$C_ID;
$result1=mysqli_query($dbc,$query1);
$row1=@mysqli_fetch_array($result1);

$query2="select count(*) as NB from pompier where P_STATUT <> 'EXT' and P_OLD_MEMBER=0 and C_ID = ".$C_ID;
$result2=mysqli_query($dbc,$query2);
$row2=@mysqli_fetch_array($result2);

$query3="select count(*) as NB from company where C_PARENT = ".$C_ID;
$result3=mysqli_query($dbc,$query3);
$row3=@mysqli_fetch_array($result3);

echo "<table class='noBorder'><tr>";
echo "<td align=right>Nombre de personnes: </td>
<td align =left><a href=personnel.php?order=P_NOM&filter=0&subsections=1&position=actif&category=EXT&company=".$C_ID." 
title='voir le personnel externe'><span class='badge' style='background-color:purple;'>".$row1["NB"]."</span></a>";
if ( $row2["NB"] > 0 ) 
    echo " externes <a href=personnel.php?order=P_NOM&filter=0&subsections=1&position=actif&category=interne&company=".$C_ID." 
    title='voir le personnel membre $cisname'><span class='badge' style='background-color:purple;'>".$row2["NB"]."</span></a> en interne";
echo "</td></tr><tr>";
echo "<td align=right>Etablissements secondaires:</td><td align=left><span class='badge'>".$row3["NB"]."</span></td></tr></table>";
echo "<form name='company' action='save_company.php'>";
echo "<input type='hidden' name='operation' value='update'>";
echo "<input type='hidden' name='C_ID' value='$C_ID'>";

//=====================================================================
// ligne 1
//=====================================================================

if ( $C_CREATED_BY <> '' ) 
    $author = "<font size=1><i> - créée par ".ucfirst(get_prenom($C_CREATED_BY))." ".strtoupper(get_nom($C_CREATED_BY))."
               le ". $C_CREATE_DATE."
                </i></font>";
else 
    $author='';

echo "<table cellspacing=0 border=0>";
echo "<tr class=TabHeader>
            <td class=TabHeader colspan=2>informations entreprise".$author."</td>
      </tr>";


//=====================================================================
// ligne type
//=====================================================================

$query="select TC_CODE,TC_LIBELLE from type_company order by TC_LIBELLE";
$result=mysqli_query($dbc,$query);

echo "<tr>
            <td bgcolor=$mylightcolor width=200><b>Type</b> $asterisk</td>
            <td bgcolor=$mylightcolor width=250 align=left>
          <select name='TC_CODE' $disabled>";
               while ($row=@mysqli_fetch_array($result)) {
                  if ( $row["TC_CODE"] == $TC_CODE ) $selected='selected';
                  else $selected='';
                  echo "<option value='".$row["TC_CODE"]."' $selected>".$row["TC_LIBELLE"]."</option>";
              }
 echo "</select>";
 echo "</td>
      </tr>";

//=====================================================================
// ligne nom
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Nom</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='C_NAME' size='40' value=\"$C_NAME\" $disabled>";
echo " </td>
      </tr>";

//=====================================================================
// ligne code ebrigade
//=====================================================================
echo "<tr>
            <td bgcolor=$mylightcolor ><b>Code $application_title</b></td>
            <td bgcolor=$mylightcolor align=left>".$C_ID."</td>
      </tr>";
      

//=====================================================================
// ligne section
//=====================================================================

if (  $nbsections == 0 ) {
    echo "<tr>
            <td bgcolor=$mylightcolor ><b>Section de rattachement</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left>";
     echo "<select id='groupe' name='groupe' $disabled class=smallcontrol2>";
     
    if ( $granted ) {
        $mysection=get_highest_section_where_granted($_SESSION['id'],37);
        if ( check_rights($_SESSION['id'], 24) ) $section='0';
        else if ( $mysection <> '' ) {
            if ( is_children($section,$mysection)) $section=$mysection;
        }
    }
    else $mysection=$S_ID;
    
    $level=get_level($mysection);
    if ( $level == 0 ) $mycolor=$myothercolor;
    elseif ( $level == 1 ) $mycolor=$my2darkcolor;
    elseif ( $level == 2 ) $mycolor=$my2lightcolor;
    elseif ( $level == 3 ) $mycolor=$mylightcolor;
    else $mycolor='white';
    $class="style='background: $mycolor;'";
    
    if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
    else $sectionorder=$defaultsectionorder;
    
    if ( check_rights($_SESSION['id'], 24))
         display_children2(-1, 0, $S_ID, $nbmaxlevels, $sectionorder);
    else { 
        echo "<option value='$mysection' $class >".get_section_code($mysection)." - ".get_section_name($mysection)."</option>";
        display_children2($mysection, $level +1, $S_ID, $nbmaxlevels);
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
echo "<select id='parent' name='parent' $disabled class=smallcontrol2>";

if ( $C_PARENT == '' ) $selected ='selected';
else $selected ='';
echo "<option value='null' $selected>aucun</option>";

$query="select C_ID, C_NAME, C_DESCRIPTION from company 
        where S_ID=0 
        and C_ID > 0 
        and C_ID <> ".$C_ID;
        
if ( intval($C_PARENT) > 0 ) 
$query .=" UNION select C_ID, C_NAME, C_DESCRIPTION from company 
        where C_ID = ".$C_PARENT;
$query .=" order by C_NAME";
$result=mysqli_query($dbc,$query);

while ( $row=@mysqli_fetch_array($result)) {
    if ( $C_PARENT == $row["C_ID"] ) $selected ='selected';
    else $selected ='';
    $code=$row["C_NAME"];
    if ( $row["C_DESCRIPTION"] <> "" ) $code .=" - ".substr($row["C_DESCRIPTION"],0,20);
    echo "<option value='".$row["C_ID"]."' $selected>".$code."</option>";
}
echo "</select></td> ";
echo "</tr>";

//=====================================================================
// ligne description
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Description</b></td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='C_DESCRIPTION' size='40' value=\"$C_DESCRIPTION\" $disabled>";        
echo " </td>
      </tr>";

//=====================================================================
// ligne siret
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor ><b>N° SIRET</b></td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='C_SIRET' size='30' value=\"$C_SIRET\" onchange='checkNumber(form.C_SIRET,\"$C_SIRET\")' $disabled>";        
echo " </td>
      </tr>";
      
//=====================================================================
// ligne address
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor align=right>Adresse</td>
            <td bgcolor=$mylightcolor align=left>
            <textarea name='address' cols='30' rows='3' value='' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' $disabled >".$C_ADDRESS."</textarea></td>";
echo "</tr>";

echo "<tr>
            <td bgcolor=$mylightcolor align=right>Code postal</td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='zipcode' size='10' value=\"$C_ZIP_CODE\" $disabled></td>";
echo "</tr>";

echo "<tr>
            <td bgcolor=$mylightcolor align=right>Ville</td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='city' size='30' value=\"$C_CITY\" $disabled></td>";
echo "</tr>";

//=====================================================================
// ligne contact
//=====================================================================

echo "<tr id=uRow2>
            <td bgcolor=$mylightcolor align=right>Nom du contact</td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='relation_nom' size='30' value=\"$C_CONTACT_NAME\" $disabled></td>";
echo "</tr>";

//=====================================================================
// ligne phone
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor align=right>Téléphone</td>
            <td bgcolor=$mylightcolor align=left>
            <input type='text' name='phone' size='20' value=\"$C_PHONE\" onchange='checkPhone(form.phone,\"\",\"".$min_numbers_in_phone."\");' $disabled>";        
echo "</tr>";

echo "<tr>
            <td bgcolor=$mylightcolor align=right>Fax</td>
            <td bgcolor=$mylightcolor align=left>
            <input type='text' name='fax' size='20' value=\"$C_FAX\" onchange='checkPhone(form.fax,\"\",\"".$min_numbers_in_phone."\");' $disabled>";        
echo "</tr>";

//=====================================================================
// ligne email
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor align=right>E-Mail</td>
            <td bgcolor=$mylightcolor align=left>
                <input type='text' name='email' size='40'
            value=\"$C_EMAIL\" onchange='mailCheck(form.email,\"\");' $disabled></td>";    
echo "</tr>";
      
//=====================================================================
// rôles
//=====================================================================

echo "<tr>
            <td colspan=2 height=45 bgcolor=$mylightcolor align=left><b>Responsables pour l'entreprise</b></td>    
</tr>";

$query="SELECT r.P_ID, r.P_NOM, r.P_PRENOM, r.P_SECTION, tcr.TCR_CODE, tcr.TCR_DESCRIPTION, r.S_CODE
        from type_company_role tcr
        left join (
        select p.P_ID, p.P_NOM, p.P_PRENOM, p.P_SECTION, s.S_CODE, cr.TCR_CODE
        from pompier p, company_role cr, section s
        where cr.P_ID = p.P_ID
        and s.S_ID = p.P_SECTION
        and cr.C_ID = ".$C_ID."
        ) as r
        on r.TCR_CODE = tcr.TCR_CODE";

if ( $S_ID > 0 ) $query .=" where tcr.TCR_FLAG is null";    
        
$query .=" order by tcr.TCR_CODE asc";

$result=mysqli_query($dbc,$query);
     
$i=0;
while ($row=@mysqli_fetch_array($result)) {
    $c=$row["TCR_CODE"];
    $TCR_DESCRIPTION=$row["TCR_DESCRIPTION"];
      
    $CURPID=$row["P_ID"];
    $CURPNOM=$row["P_NOM"];
    $CURPPRENOM=$row["P_PRENOM"];
    $CURPSECTION=$row["P_SECTION"];
    $CURSECTIONCODE=$row["S_CODE"];
   
    echo "<tr>
            <td bgcolor=$mylightcolor align=right>".$TCR_DESCRIPTION."</td>";
    echo "<td bgcolor=$mylightcolor align=left>";
    if (( $disabled == "") ){
        print write_modal("upd_company_role.php?C_ID=".$C_ID."&TCR_CODE=".$c, "personne_".$c, "<i class='fa fa-user fa-lg' title='choisir une personne pour ce rôle'></i>");
    }           
        
    echo "<a href=upd_personnel.php?pompier=".$CURPID.">".strtoupper($CURPNOM)." ".ucfirst($CURPPRENOM)."</a>"; 
    if ( $CURSECTIONCODE <> "" ) echo " <font size=1>(".$CURSECTIONCODE.")</font>";

    echo "</td></tr>";
}

echo "</table><p>";
if ( check_rights($_SESSION['id'], 37))
    echo "<p><input type='submit' class='btn btn-default' value='sauver' $disabled> ";
echo "</form>";

if ( check_rights($_SESSION['id'], 19) and $C_ID > 0 ) {
    echo "<form name='delcompany' action='save_company.php'>";
    echo "<input type='hidden' name='C_ID' value='$C_ID'>";
    echo "<input type='hidden' name='operation' value='delete'>";
    echo "<input type='submit' class='btn btn-default' value='supprimer'> ";
}
if ( $from == 'export' ) {
    echo " <input type=submit class='btn btn-default' value='fermer' onclick='fermerfenetre();'> ";
}
else
    echo "<input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">";
echo "</form>";
echo "</div>";
writefoot();
?>
