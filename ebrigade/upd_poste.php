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
writehead();
forceReloadJS('js/poste.js');

$PS_ID=intval($_GET["pid"]);
if (isset ($_GET["from"])) $from=$_GET["from"];
else $from ="default";


//=====================================================================
// affiche la fiche poste
//=====================================================================

$query="select p.PS_ID, p.EQ_ID, p.TYPE, p.DESCRIPTION,
        e.EQ_NOM, p.PS_EXPIRABLE, p.PS_AUDIT, p.PS_DIPLOMA, p.PS_NUMERO, p.PS_NATIONAL, p.PS_FORMATION, 
        p.PS_RECYCLE, p.PS_USER_MODIFIABLE, p.PS_PRINTABLE, p.PS_PRINT_IMAGE, p.PS_SECOURISME, p.F_ID,
        p.PH_CODE, p.PH_LEVEL, p.PS_ORDER
        from equipe e, poste p
        where p.EQ_ID=e.EQ_ID
        and p.PS_ID=".$PS_ID;    
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);
$PH_LEVEL=intval($PH_LEVEL);
$PS_ORDER=intval($PS_ORDER);

if ( $PS_FORMATION  == 0 ) $disabled1='disabled';
else $disabled1='';

if ( $PS_DIPLOMA  == 0 ) $disabled2='disabled';
else $disabled2='';

if ( $PS_PRINTABLE  == 0 ) $disabled3='disabled';
else $disabled3='';

echo "<div align=center class='table-responsive'><font size=4><b>Compétence n° $PS_ID </b></font>(type  ".$EQ_NOM.")<br>";

echo "<p><table cellspacing=0 border=0>";
echo "<form name='poste' action='save_poste.php'>";
echo "<input type='hidden' name='PS_ID' value='$PS_ID'>";
echo "<input type='hidden' name='operation' value='update'>";
echo "<input type='hidden' name='TYPE' value=\"$TYPE\">";
echo "<input type='hidden' name='DESCRIPTION' value=\"$DESCRIPTION\">";
echo "<input type='hidden' name='PS_EXPIRABLE' value='0'>";
echo "<input type='hidden' name='PS_AUDIT' value='0'>";
echo "<input type='hidden' name='PS_DIPLOMA' value='0'>";
echo "<input type='hidden' name='PS_NUMERO' value='0'>";
echo "<input type='hidden' name='PS_SECOURISME' value='0'>";
echo "<input type='hidden' name='PS_NATIONAL' value='0'>";
echo "<input type='hidden' name='PS_PRINTABLE' value='0'>";
echo "<input type='hidden' name='PS_PRINT_IMAGE' value='0'>";
echo "<input type='hidden' name='PS_FORMATION' value='0'>";
echo "<input type='hidden' name='PS_RECYCLE' value='0'>";
echo "<input type='hidden' name='PS_USER_MODIFIABLE' value='0'>";
echo "<input type='hidden' name='F_ID' value='4'>";
echo "<input type='hidden' name='PH_CODE' value=''>";
echo "<input type='hidden' name='PH_LEVEL' value='0'>";

//=====================================================================
// ligne 1
//=====================================================================

echo "<tr>
            <td colspan=2 class=TabHeader>$title</td>
      </tr>";

//=====================================================================
// ligne type
//=====================================================================


$query2="select EQ_ID 'NEWEQ_ID', EQ_NOM 'NEWEQ_NOM' from equipe order by EQ_ORDER, EQ_NOM ";

echo "<tr>
          <td bgcolor=$mylightcolor width=200><b>Type</b> $asterisk</td>
          <td bgcolor=$mylightcolor align=left width=250>
        <select id ='EQ_ID' name='EQ_ID' >";
$result2=mysqli_query($dbc,$query2);
while (custom_fetch_array($result2)) {
     if ( $NEWEQ_ID == $EQ_ID ) $selected='selected';
     else $selected='';
    echo "<option value='".$NEWEQ_ID."' $selected style='background-color:#FFFFFF'>".$NEWEQ_NOM."</option>";
}

echo "</select>";
echo "</tr>";

//=====================================================================
// ligne numero
//=====================================================================
echo "<tr>
            <td bgcolor=$mylightcolor ><b>Ordre affichage</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left>
          <select name='PS_ORDER'>";
             for ($i=1 ; $i<=200 ; $i++) {
                if ($i == $PS_ORDER) $selected="selected";
                      else $selected="";
                 echo "<option value='$i' $selected>$i</option>";
            }
             echo "</select>";
echo "</tr>";

//=====================================================================
// ligne description
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor >
            <b>Description</b> $asterisk</td>
            <td bgcolor=$mylightcolor  align=left >
            <input type='text' name='DESCRIPTION' size='35' value=\"".$DESCRIPTION."\">
            ";        
echo "</tr>";
      

//=====================================================================
// ligne nom court
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Nom court</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='TYPE' size='5' value=\"$TYPE\">";        
echo "</tr>";


//=====================================================================
// ligne hierarchie
//=====================================================================
$query2="select distinct ph.PH_CODE, ph.PH_NAME from poste_hierarchie ph order by ph.PH_CODE ";
$result2=mysqli_query($dbc,$query2);
echo "<tr>
    <td bgcolor=$mylightcolor ><b>Hiérarchie</b> $asterisk</td>
    <td bgcolor=$mylightcolor align=left>
    <select name='PH_CODE' id='PH_CODE' title=\"Si cette compétence fait partie d'une hiérarchie\" onchange=\"changedType();\">";
    echo "<option value='' $selected>Ne fait pas partie d'une hiérarchie</option>";
    while ($row2=@mysqli_fetch_array($result2)) {
        $string = "";
        if ( $row2[0] == $PH_CODE ) $selected='selected';
        else $selected='';
        $query3="select TYPE from poste where PH_CODE='".$row2[0]."' order by PH_LEVEL asc";
        $result3=mysqli_query($dbc,$query3);
        while ($row3=@mysqli_fetch_array($result3)) {
            $string .= " ".$row3[0].",";
        }
        if ( $string <> "" ) $string = " (". rtrim($string,',')." )";  
        echo "<option value='".$row2[0]."' $selected title=\"".$row2[1]."\">".$row2[0].$string."</option>";
    }
    echo "</select>";    
echo "</tr>";

if ( $PH_CODE == '' ) $style="style='display:none'";
else  $style="";

echo "<tr id='rowOrder' $style>
    <td bgcolor=$mylightcolor align=right><i>Ordre dans la hiérarchie</i> $asterisk</td>
    <td bgcolor=$mylightcolor align=left>
    <select name='PH_LEVEL' title=\"Ordre dans la hiérarchie\">";
    for ( $i=0; $i < 10; $i++ ) {
        if ( $i == $PH_LEVEL ) $selected='selected';
        else $selected='';
        echo "<option value='".$i."' $selected>".$i."</option>";
    }
    echo "</select>";    
echo "</tr>";

//=====================================================================
// ligne habilitation requise
//=====================================================================

$query2="select distinct F_ID, F_LIBELLE from fonctionnalite
         where F_ID in (2,4,9,12,13,18,22,24,25,26,29,30,31,37,46,55)";
$result2=mysqli_query($dbc,$query2);
echo "<tr>
          <td bgcolor=$mylightcolor ><b>Habilitation</b> $asterisk</td>
          <td bgcolor=$mylightcolor align=left>
        <select name='F_ID' title='Choisir la permission requise pour pouvoir modifier cette compétence'>";
        while ($row2=@mysqli_fetch_array($result2)) {
            if ( $row2[0] == $F_ID ) $selected='selected';
            else $selected='';
            echo "<option value='".$row2[0]."' $selected>".$row2[0]." - ".$row2[1]."</option>";
        }
         echo "</select>";
echo "</tr>";

//=====================================================================
// ligne secourisme
//=====================================================================
if ( $PS_SECOURISME == 1 ) $checked="checked";
else $checked="";
echo "<tr>
      <td bgcolor=$mylightcolor >
            <b>Secourisme</b></font></td>
      <td bgcolor=$mylightcolor align=left>
            <input type='checkbox' name='PS_SECOURISME' id='PS_SECOURISME' value='1' $checked >
            <small>Compétence officielle de secourisme</small>
            </td>";        
echo "</tr>";

//=====================================================================
// ligne formation
//=====================================================================

if ( $PS_FORMATION == 1 ) $checked="checked";
else $checked="";
echo "<tr>
            <td bgcolor=$mylightcolor >
                <b>Formation possible</b></font></td>
            <td bgcolor=$mylightcolor align=left>
                <input type='checkbox' name='PS_FORMATION' id='PS_FORMATION' value='1' $checked  onchange='changedDiplome();'>
                <small>On peut organiser des formations pour cette compétence</small>
                </td>";        
echo "</tr>";

//=====================================================================
// ligne recycle
//=====================================================================

if ( $PS_RECYCLE == 1 ) $checked="checked";
else $checked="";
echo "<tr>
            <td bgcolor=$mylightcolor >
                <b>Formation continue</b></font></td>
            <td bgcolor=$mylightcolor align=left>
                <input type='checkbox' name='PS_RECYCLE' id='PS_RECYCLE' value='1' $checked  $disabled1>
                <small>Une formation continue régulière est nécessaire</small>
                </td>";        
echo "</tr>";

//=====================================================================
// ligne expirable
//=====================================================================
if ( $PS_EXPIRABLE == 1 ) $checked="checked";
else $checked="";
echo "<tr>
            <td bgcolor=$mylightcolor >
                <b>Date d'expiration</b></font></td>
            <td bgcolor=$mylightcolor align=left>
                <input type='checkbox' name='PS_EXPIRABLE' id='PS_EXPIRABLE' value='1' $checked >
                <small>On peut définir une date d'expiration sur cette compétence</small>
                </td>";        
echo "</tr>";

//=====================================================================
// ligne diplome
//=====================================================================
if ( $PS_DIPLOMA == 1 ) $checked="checked";
else $checked="";
echo "<tr>
      <td bgcolor=$mylightcolor >
            <b>Diplôme délivré</b></font></td>
      <td bgcolor=$mylightcolor align=left>
            <input type='checkbox' name='PS_DIPLOMA' id='PS_DIPLOMA' value='1' $checked onchange='changedDiplome();'>
            <small>Un diplôme est délivré après formation</small>
            </td>";        
echo "</tr>";

if ( $PS_NUMERO == 1 ) $checked="checked";
else $checked="";
echo "<tr>
      <td bgcolor=$mylightcolor >
            <b>Diplôme numéroté</b></font></td>
      <td bgcolor=$mylightcolor align=left>
            <input type='checkbox' name='PS_NUMERO' id='PS_NUMERO' value='1' $checked  $disabled2 >
            <small>Chaque diplôme a un numéro unique</small>
            </td>";        
echo "</tr>";

if ( $PS_NATIONAL == 1 ) $checked="checked";
else $checked="";
echo "<tr>
      <td bgcolor=$mylightcolor >
            <b>Diplôme national</b></font></td>
      <td bgcolor=$mylightcolor align=left>
            <input type='checkbox' name='PS_NATIONAL' id='PS_NATIONAL'  value='1' $checked  $disabled2>
            <small>Diplôme délivré au niveau national seulement</small>
            </td>";        
echo "</tr>";
if ( $PS_PRINTABLE == 1 ) $checked="checked";
else $checked="";
echo "<tr>
      <td bgcolor=$mylightcolor >
            <b>Diplôme imprimable</b></font></td>
      <td bgcolor=$mylightcolor align=left>
            <input type='checkbox' name='PS_PRINTABLE' id='PS_PRINTABLE' value='1' $checked  $disabled2 onchange='changedDiplome();'>
            <small>Possibilité d'imprimer un diplôme</small>
            </td>";        
echo "</tr>";
if ( $PS_PRINT_IMAGE == 1 ) $checked="checked";
else $checked="";
echo "<tr>
      <td bgcolor=$mylightcolor >
            <b>Imprimer image</b></font></td>
      <td bgcolor=$mylightcolor align=left>
            <input type='checkbox' name='PS_PRINT_IMAGE' id='PS_PRINT_IMAGE' value='1' $checked  $disabled2 $disabled3>
            <small>L'image est obligatoirement imprimée</small>
            </td>";        
echo "</tr>";


//=====================================================================
// ligne modifiable
//=====================================================================

if ( $PS_USER_MODIFIABLE == 1 ) $checked="checked";
else $checked="";
echo "<tr>
            <td bgcolor=$mylightcolor >
                <b>Modifiable</b></font></td>
            <td bgcolor=$mylightcolor align=left>
                <input type='checkbox' name='PS_USER_MODIFIABLE'  value='1' $checked >
                <small>Modifiable par chaque utilisateur</small>
                </td>";        
echo "</tr>";

//=====================================================================
// ligne audit
//=====================================================================
if ( $PS_AUDIT == 1 ) $checked="checked";
else $checked="";
echo "<tr>
            <td bgcolor=$mylightcolor >
                <b>Alerter si modifications</b></font></td>
            <td bgcolor=$mylightcolor align=left>
                <input type='checkbox' name='PS_AUDIT' id='PS_AUDIT' value='1' $checked  >
                <small>Un mail est envoyé au secrétariat en cas de modification</small>
                </td>";        
echo "</tr>";

//=====================================================================
// bas de tableau
//=====================================================================
echo "</table>"; 

echo "<p><input type='submit' class='btn btn-default' value='sauver'> ";

echo "</form><form name='poste2' action='save_poste.php'>";
echo "<input type='hidden' name='PS_ID' value='$PS_ID'>";
echo "<input type='hidden' name='operation' value='delete'>";
echo "<input type='submit' class='btn btn-default' value='supprimer'> ";

if ( $from == "hierarchie" )
    echo "<input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"redirect3('upd_hierarchie_competence.php?hierarchie=".$PH_CODE."');\">";
else
    echo "<input type='button' class='btn btn-default' value='Retour' name='annuler' onclick='redirect();'\">";
echo "</form>";
echo "</div>";
writefoot();

?>
