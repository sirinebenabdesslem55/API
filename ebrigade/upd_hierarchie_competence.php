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
if (isset($_GET["hierarchie"])) $hierarchie=secure_input($dbc,$_GET["hierarchie"]);
else $hierarchie="";
writehead();

?>

<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/competence.js'></script>
<?php

echo "
</head>
<body>";

//=====================================================================
// affiche la hierarchie
//=====================================================================
if ( $hierarchie <> "" ) {
    $query="select PH_CODE, PH_NAME, PH_HIDE_LOWER, PH_UPDATE_LOWER_EXPIRY, PH_UPDATE_MANDATORY from poste_hierarchie
         where PH_CODE='".$hierarchie."'";    
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    $title="Hi�rarchie de Comp�tence - $PH_CODE";
    $operation='update';
}
else {
    $PH_CODE="";
    $PH_NAME="";
    $PH_HIDE_LOWER=1;
    $PH_UPDATE_LOWER_EXPIRY=1;
    $PH_UPDATE_MANDATORY=1;
    $title="Ajout nouvelle Hi�rarchie de Comp�tence";
    $operation='insert';
}

echo "<div align=center class='table-responsive'><font size=4><b>".$title."</b></font>";
echo "<form name='hierarchie' action='save_hierarchie_competence.php'>";

echo "<p><table cellspacing=0 border=0>";
echo "<input type='hidden' name='OLD_PH_CODE' value='$PH_CODE'>";
echo "<input type='hidden' name='operation' value='$operation'>";

//=====================================================================
// ligne 1
//=====================================================================

echo "<tr>
    <td colspan=2 class=TabHeader>infos</td>
      </tr>";

//=====================================================================
// ligne description
//=====================================================================

echo "<tr bgcolor=$mylightcolor>
            <td ><b>Code</b>$asterisk</td>
            <td align=left>
            <input type='text' name='PH_CODE' size='15' maxlength='15' value=\"$PH_CODE\"  onchange=\"isValid2(form.PH_CODE,'$PH_CODE');\">
            </td>";
echo "</tr>";      

echo "<tr bgcolor=$mylightcolor>
            <td ><b>Description</b> $asterisk</td>
            <td align=left>
            <input type='text' name='PH_NAME' size='30' maxlength='30' value=\"$PH_NAME\" onchange=\"isValid4(form.PH_NAME, '');\">";        
echo "</tr>";


$t1="\"Montrer seulement la comp�tence la plus haute de la hi�rarchie pour une personne sur les �v�nements, masquer les autres\"";
$t2="\"En cas de mise � jour de la date d'expiration sur une comp�tence de la hi�rarchie, la mise � jour automatique des dates des comp�tences inf�rieures est possible.\"";
$t3="\"Rendre obligatoire la validation des comp�tences inf�rieures, si non coch�e elle reste facultative sur les �v�nements formations.\"";

if ( $PH_HIDE_LOWER == 1 ) $checked='checked';
else $checked='';
echo "<tr bgcolor=$mylightcolor>
            <td ><b>Masquer les comp�tences inf�rieures</b></td>
            <td align=left>
            <input type='checkbox' name='PH_HIDE_LOWER' value='1' $checked title=".$t1.">
            <span class=small2> sur les �v�nements</span>";        
echo "</tr>";

if ( $PH_UPDATE_LOWER_EXPIRY == 1 ) $checked='checked';
else $checked='';
echo "<tr bgcolor=$mylightcolor>
            <td ><b>Prolonger les comp�tences inf�rieures</b></td>
            <td align=left>
            <input type='checkbox' name='PH_UPDATE_LOWER_EXPIRY' id='PH_UPDATE_LOWER_EXPIRY' value='1' $checked title=".$t2." onchange='checkProlonge();'>
            <span class=small2>les comp�tences inf�rieures peuvent �tre prolong�es</span>";        
echo "</tr>";

if ( $PH_UPDATE_LOWER_EXPIRY == 1 ) $disabled='';
else $disabled='disabled';
if ( $PH_UPDATE_MANDATORY == 1 ) $checked='checked';
else $checked='';
echo "<tr bgcolor=$mylightcolor>
            <td align=right ><i>Obligatoire</i></td>
            <td align=left>
            <input type='checkbox' name='PH_UPDATE_MANDATORY' id='PH_UPDATE_MANDATORY' value='1' $disabled $checked title=".$t3.">
            <span class=small2>les comp�tences inf�rieures sont obligatoirement prolong�es</span>";        
echo "</tr>";
 
// afficher les comp�tences de cette hi�rarchie
$queryp="select PS_ID, TYPE, DESCRIPTION, PH_LEVEL
        from  poste p
        where PH_CODE='".$PH_CODE."'
        order by PH_LEVEL asc";
$resultp=mysqli_query($dbc,$queryp);

if ( @mysqli_num_rows($resultp) > 0 ) {
    echo "<tr>
            <td colspan=2 class=TabHeader>
            Comp�tences faisant partie de cette hi�rarchie</td>
        </tr>";
        
    while (custom_fetch_array($resultp)) {
        $DESCRIPTION=strip_tags($DESCRIPTION);
        echo "<tr bgcolor=$mylightcolor>
            <td><b>Niveau ".$PH_LEVEL." - $TYPE</b></td>
            <td align=left><a href=upd_poste.php?from=hierarchie&pid=$PS_ID>$DESCRIPTION</a>";        
        echo "</tr>";
    }
}

//=====================================================================
// bas de tableau
//=====================================================================
echo "</table>";
echo "<p><input type='submit' class='btn btn-default' value='sauver'> <input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"redirect();\">";
echo "</form>";
if ( $hierarchie <> "0" ) {
    echo "<form name='equipe2' action='save_hierarchie_competence.php' >";
    echo "<input type='hidden' name='OLD_PH_CODE' value='$PH_CODE'>";
    echo "<input type='hidden' name='PH_CODE' value='$PH_CODE'>";
    echo "<input type='hidden' name='operation' value='delete'>";
    echo "<input type='hidden' name='PH_NAME' value='$PH_NAME'>";
    echo "<input type='submit' class='btn btn-default' value='supprimer'> ";
    echo "</form>";
}

echo "</div>";
writefoot();
?>
