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
<script type="text/javascript" src="js/poste.js"></script>
<?php

$type='ALL';
if ( isset($_GET["EQ_ID"])) $MYEQ_ID=intval($_GET["EQ_ID"]);
else if (isset($_SESSION['typequalif'])) $MYEQ_ID=intval($_SESSION['typequalif']);
else $MYEQ_ID=0;

$title="Compétence";
echo "<div align=center class='table-responsive'><font size=4><b>Ajout $title<br></b></font>";
echo "<form name='poste' action='save_poste.php'>";
echo "<input type='hidden' name='operation' value='insert'>";
echo "<input type='hidden' name='TYPE' value=''>";
echo "<input type='hidden' name='DESCRIPTION' value=''>";
echo "<input type='hidden' name='PS_EXPIRABLE' value='0'>";
echo "<input type='hidden' name='PS_AUDIT' value='0'>";
echo "<input type='hidden' name='PS_DIPLOMA' value='0'>";
echo "<input type='hidden' name='PS_NUMERO' value='0'>";
echo "<input type='hidden' name='PS_SECOURISME' value='0'>";
echo "<input type='hidden' name='PS_NATIONAL' value='0'>";
echo "<input type='hidden' name='PS_FORMATION' value='0'>";
echo "<input type='hidden' name='PS_RECYCLE' value='0'>";
echo "<input type='hidden' name='PS_USER_MODIFIABLE' value='0'>";
echo "<input type='hidden' name='PS_PRINTABLE' value='0'>";
echo "<input type='hidden' name='PS_PRINT_IMAGE' value='0'>";
echo "<input type='hidden' name='F_ID' value='4'>";
echo "<input type='hidden' name='PH_CODE' value=''>";
echo "<input type='hidden' name='PH_LEVEL' value='0'>";
echo "<input type='hidden' name='PS_ORDER' value='10'>";

echo "<p><table cellspacing=0 border=0>";

//=====================================================================
// ligne 1
//=====================================================================
echo "<tr>
       <td colspan=2 class=TabHeader>$title</td>
      </tr>";
//=====================================================================
// ligne type
//=====================================================================

$query="select EQ_ID, EQ_NOM from equipe order by EQ_ORDER, EQ_NOM";
echo "<tr>
      <td bgcolor=$mylightcolor width=200 ><b>Type </b>$asterisk</td>
      <td bgcolor=$mylightcolor align=left width=250>
        <select id ='EQ_ID' name='EQ_ID' 
        onchange=\"displaymanager2(document.getElementById('EQ_ID').value)\">";
if ( $type == 'ALL') echo "<option value='ALL'>Choisissez un type</option>";
$result=mysqli_query($dbc,$query);
while (custom_fetch_array($result)) {
    if ( $EQ_ID == $MYEQ_ID ) $selected='selected';
    else $selected='';
    echo "<option value='".$EQ_ID."' ".$selected." style='background-color:#FFFFFF'>".$EQ_NOM."</option>";
}

echo "</select>";
echo "</tr>";

//=====================================================================
// ligne numero
//=====================================================================
if ( $MYEQ_ID > 0 ) {
    echo "<tr>
              <td bgcolor=$mylightcolor ><b>Ordre affichage</b> $asterisk</td>
              <td bgcolor=$mylightcolor align=left>
              <select name='PS_ORDER'>";
    for ($i=1 ; $i<=200 ; $i++) {
        echo "<option value='$i'>$i</option>";
    }
    echo "</select>";
    echo "</tr>";

    //=====================================================================
    // ligne description
    //=====================================================================

    echo "<tr>
              <td bgcolor=$mylightcolor ><b>Description $asterisk</b></font></td>
              <td bgcolor=$mylightcolor align=left height=25><input type='text' name='DESCRIPTION' size='25' value=''>";
    echo "</tr>";
          

    //=====================================================================
    // ligne nom court
    //=====================================================================

    echo "<tr>
              <td bgcolor=$mylightcolor ><b>Nom court </b>$asterisk</td>
              <td bgcolor=$mylightcolor align=left height=25><input type='text' name='TYPE' size='5' value=''>";
    echo "</tr>";



    //=====================================================================
    // ligne hierarchie
    //=====================================================================
    $query2="select distinct ph.PH_CODE, ph.PH_NAME from poste_hierarchie ph order by ph.PH_CODE ";
    $result2=mysqli_query($dbc,$query2);
    echo "<tr>
        <td bgcolor=$mylightcolor ><b>Hiérarchie </b>$asterisk</td>
        <td bgcolor=$mylightcolor align=left>
        <select name='PH_CODE' id='PH_CODE' title=\"Si cette compétence fait partie d'une hiérarchie\" onchange=\"changedType();\">";
        echo "<option value=''>Ne fait pas partie d'une hiérarchie</option>";
        while ($row2=@mysqli_fetch_array($result2)) {
            $string = "";
            $query3="select TYPE from poste where PH_CODE='".$row2[0]."' order by PH_LEVEL asc";
            $result3=mysqli_query($dbc,$query3);
            while ($row3=@mysqli_fetch_array($result3)) {
                $string .= " ".$row3[0].",";
            }
            if ( $string <> "" ) $string = " (". rtrim($string,',')." )";  
            echo "<option value='".$row2[0]."' title=\"".$row2[1]."\">".$row2[0].$string."</option>";
        }
        echo "</select>";    
    echo "</tr>";

    $style="style='display:none'";

    echo "<tr id='rowOrder' $style>
        <td bgcolor=$mylightcolor align=right><i>Ordre dans la hiérarchie </i>$asterisk</td>
        <td bgcolor=$mylightcolor align=left>
        <select name='PH_LEVEL' title=\"Ordre dans la hiérarchie\">";
        for ( $i=0; $i < 10; $i++ ) {
            echo "<option value='".$i."' >".$i."</option>";
        }
        echo "</select>";    
    echo "</tr>";


    //=====================================================================
    // ligne habilitation requise
    //=====================================================================

    $query2="select distinct F_ID, F_LIBELLE from fonctionnalite
             where F_ID in (2,4,9,12,13,22,24,25,26,29,30,31,37,46)";
    $result2=mysqli_query($dbc,$query2);
    echo "<tr>
            <td bgcolor=$mylightcolor ><b>Habilitation </b>$asterisk</td>
            <td bgcolor=$mylightcolor align=left>
            <select name='F_ID' title='Choisir la permission requise pour pouvoir modifier cette compétence'>";
            while ($row2=@mysqli_fetch_array($result2)) {
                if ( $row2[0] == 4 ) $selected='selected';
                else $selected='';
                echo "<option value='".$row2[0]."' $selected>".$row2[0]." - ".$row2[1]."</option>";
            }
            echo "</select>";
    echo "</tr>";
    
    //=====================================================================
    // ligne secourisme
    //=====================================================================
    echo "<tr>
              <td bgcolor=$mylightcolor >
                    <b>Secourisme</b></td>
              <td bgcolor=$mylightcolor align=left>
                    <input type='checkbox' name='PS_SECOURISME' id='PS_SECOURISME' value='1'>
                    <small>Compétence officielle de secourisme</small>
                    </td>";        
    echo "</tr>";
    //=====================================================================
    // ligne formation
    //=====================================================================
    echo "<tr>
              <td bgcolor=$mylightcolor >
                    <b>Formation possible</b></font></td>
              <td bgcolor=$mylightcolor align=left>
                    <input type='checkbox' name='PS_FORMATION' id='PS_FORMATION'  value='1' onchange='changedDiplome();'>
                    <small>On peut organiser des formations pour cette compétence</small>
                    </td>";        
    echo "</tr>";
    
    //=====================================================================
    // ligne recycle
    //=====================================================================
    echo "<tr>
              <td bgcolor=$mylightcolor >
                    <b>Formation continue</b></td>
              <td bgcolor=$mylightcolor align=left>
                    <input type='checkbox' name='PS_RECYCLE' id='PS_RECYCLE' value='1' disabled>
                    <small>Une formation continue régulière est nécessaire</small>
                    </td>";        
    echo "</tr>";

    //=====================================================================
    // ligne expirable
    //=====================================================================
    echo "<tr>
              <td bgcolor=$mylightcolor >
                    <b>Date d'expiration</b></font></td>
              <td bgcolor=$mylightcolor align=left>
                    <input type='checkbox' name='PS_EXPIRABLE' id='PS_EXPIRABLE' value='1'>
                    <small>On peut définir une date d'expiration sur cette compétence</small>
                    </td>";        
    echo "</tr>";

    //=====================================================================
    // ligne diplome
    //=====================================================================
    echo "<tr>
              <td bgcolor=$mylightcolor >
                    <b>Diplôme délivré</b></td>
              <td bgcolor=$mylightcolor align=left>
                    <input type='checkbox' name='PS_DIPLOMA' id='PS_DIPLOMA'  value='1' onchange='changedDiplome();'>
                    <small>Un diplôme est délivré après formation</small>
                    </td>";        
    echo "</tr>";
    
    echo "<tr>
              <td bgcolor=$mylightcolor >
                    <b>Diplôme numéroté</b></td>
              <td bgcolor=$mylightcolor align=left>
                    <input type='checkbox' name='PS_NUMERO' id='PS_NUMERO'  value='1' disabled>
                    <small>Chaque diplôme a un numéro unique</small>
                    </td>";        
    echo "</tr>";

    echo "<tr>
              <td bgcolor=$mylightcolor >
                    <b>Diplôme national</b></td>
              <td bgcolor=$mylightcolor align=left>
                    <input type='checkbox' name='PS_NATIONAL' id='PS_NATIONAL' value='1' disabled>
                    <small>Diplôme délivré au niveau national seulement</small>
                    </td>";        
    echo "</tr>";
    echo "<tr>
              <td bgcolor=$mylightcolor >
                    <b>Diplôme imprimable</b></td>
              <td bgcolor=$mylightcolor align=left>
                    <input type='checkbox' name='PS_PRINTABLE' id='PS_PRINTABLE' value='1' onchange='changedDiplome();' disabled>
                    <small>Possibilité d'imprimer un diplôme</small>
                    </td>";        
    echo "</tr>";
    echo "<tr>
              <td bgcolor=$mylightcolor >
                    <b>Imprimer image</b></td>
              <td bgcolor=$mylightcolor align=left>
                    <input type='checkbox' name='PS_PRINT_IMAGE' id='PS_PRINT_IMAGE' value='1' disabled>
                    <small>L'image est obligatoirement imprimée</small>
                    </td>";        
    echo "</tr>";

    //=====================================================================
    // ligne user modif
    //=====================================================================
    echo "<tr>
              <td bgcolor=$mylightcolor>
                    <b>Modifiable</b></td>
              <td bgcolor=$mylightcolor align=left>
                    <input type='checkbox' name='PS_USER_MODIFIABLE' id='PS_USER_MODIFIABLE' value='1'>
                    <small>Modifiable par chaque utilisateur</small>
                    </td>";        
    echo "</tr>";
    
    //=====================================================================
    // ligne audit
    //=====================================================================
    echo "<tr>
              <td bgcolor=$mylightcolor >
                    <b>Alerter si modifications</b></font></td>
              <td bgcolor=$mylightcolor align=left>
                    <input type='checkbox' name='PS_AUDIT' id='PS_AUDIT' value='1'>
                    <small>Un mail est envoyé au secrétariat en cas de modification</small>
                    </td>";        
    echo "</tr>";
}
echo "</table>"; 
echo "<p><input type='submit' class='btn btn-default' value='sauver'></form>";
echo "<input type='button' class='btn btn-default' value='Annuler' name='annuler' onclick='redirect2();'\"></div>";
writefoot();
?>
