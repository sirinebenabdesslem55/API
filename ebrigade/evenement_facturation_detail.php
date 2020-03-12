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
$id=$_SESSION['id'];
writehead();

$frmaction ="Enregistrer";
$msgerr ="";

if (isset($_POST['evenement'])) $evenement = intval($_POST['evenement']);
else if (isset($_GET['evenement'])) $evenement = intval($_GET['evenement']);
else $evenement = 0;

if (isset($_POST['type'])) $type = secure_input($dbc,$_POST['type']);
else if (isset($_GET['type'])) $type = secure_input($dbc,$_GET['type']);
else $type = 'devis';

if ( $type == 'devis') $tab = 1;
else $tab = 2;


if ( isset($_SESSION['evenement_facture'])) unset($_SESSION['evenement_facture']);

$organisateur = get_section_organisatrice($evenement);

// le chef, le cadre de l'événement ont toujours accès à cette fonctionnalité, les autres doivent avoir 29 et/ou 24
if ( ! check_rights($id, 29, $organisateur) and ! is_chef_evenement($id, $evenement)) {
    check_all(29);
    check_all(24);
}

$SUM=0;

?>

<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/dateFunctions.js'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<script type='text/javascript' src='js/facturation_detail.js'></script>
<?php
echo "</head>";

//=====================================================================
// Titre
//=====================================================================

echo "<body>";
echo "<div align=center>
<table class='noBorder'><tr><td width=30><i class='fas fa-receipt fa-2x'></td><td><b><font size=4>Détail ".$type."</font></b></td></tr></table>";

echo "<form name=facture_detail_form action='save_detail_facture.php' method='POST'>";
echo "<input type='hidden' name='evenement' id='evenement' value='".$evenement."'>";
echo "<input type='hidden' name='type' id='type' value='".$type."'>";
echo "<p><table id='FactureTable'  bgcolor=$mylightcolor cellspacing=0 border=0>";
echo "<thead><tr>
            <td class=TabHeader colspan=8>Détail ".$type."</td>
      </tr>";

//=====================================================================
// Header
//=====================================================================    

echo "<tr bgcolor=$mylightcolor ><td width=120 aligne = center>";
echo "    <button class='btn btn-default ajouter' id='ajouter' title='ajouter une ligne, maximum (100)' ><i class='fas fa-plus'></i> ligne</button>";

echo "</td>
    <td width=160 align=left><b>Type</b></td>
    <td width=200><b>Description</b></td>
    <td width=40><b>Quantité</b></td>
    <td width=80><b>PU ".$default_money_symbol."</b></td>
    <td width=80><b>Remise %</b></td>
    <td width=80><b>Total ligne</b></td>
    <td width=12></td>
    </tr></thead>";


$query="select e.ef_lig, e.ef_frais, e.ef_txt, e.ef_qte, e.ef_pu, e.ef_rem, e.ef_comment, t.TEF_CODE, t.TEF_NAME
        from evenement_facturation_detail e, type_element_facturable t
        where e.e_id = ".$evenement."
        and e.ef_frais = t.TEF_CODE
        and e.ef_type='".$type."'
        order by e.ef_lig";
$result=mysqli_query($dbc,$query);

$i=1;
$TotalDoc=0;
echo "<tbody>";
while ($row=@mysqli_fetch_array($result)) {
    $save_disabled='';
    $ef_lig = $row["ef_lig"];
    $ef_txt = $row["ef_txt"];
    $ef_qte = $row["ef_qte"];
    $ef_pu = round($row["ef_pu"],2);
    $ef_rem = $row["ef_rem"];
    $ef_comment = $row["ef_comment"];
    $TEF_CODE = $row["TEF_CODE"];
    $TEF_NAME = $row["TEF_NAME"];
    
    $subtotal = round($ef_qte * $ef_pu * (100 - $ef_rem ) / 100, 2);
    $SUM = $SUM + $subtotal;
    $typefield="<input type='button' class='labelx btn btn-default' style='padding: 0.2rem 0.3rem' id='label".$i."' name='label".$i."' value='".$TEF_NAME."'>
             <div id='t".$i."' name='t".$i."' 
                style='display: none;
                       position: absolute; 
                       border-style: solid;
                       border-width: 2px;
                       background-color: $mylightcolor; 
                       border-color: $mydarkcolor;
                       width:500px;
                       height:60px;
                       padding: 5px;
                       margin-top: 100px;'>
                <i class='fa fa-euro-sign'></i> <b>".$TEF_NAME."</b><br>
                ".write_select_type_form($i, $TEF_CODE, false)."
                  <p align=center>
             </div>"; 
    
    
    echo "<tr bgcolor=$mylightcolor>
            <td></td>
            <td>".$typefield."</td>
            <td><input type='text' class='commentaire' name='commentaire".$i."' id='commentaire".$i."' size='60' maxlength='66' value=\"".$ef_txt."\" 
                title='Saisissez le descriptif lié à cette ligne' ></td>
            <td><input type='text' class='quantite' name='quantite".$i."' id='quantite".$i."' size='3' value='".$ef_qte."'
                onchange=\"checkNumberNullAllowed(this,'');\" ></td>
            <td><input type='text' class='pu' name='pu".$i."' id='pu".$i."' size='5' value='".$ef_pu."' 
                onchange=\"checkFloat(this,'');\" ></td>
            <td><input type='text' class='remise' name='remise".$i."' id='remise".$i."' size='5' value=\"".$ef_rem."\" 
                title='Remise accordée sur cette ligne en %' ></td>
            <td><input type='text' class='subtotal' name='subtotal".$i."' id='subtotal".$i."' size='5' value=".$subtotal." readonly disabled
                title='Sous total' ></td>
            <td><i class='delete fa fa-trash fa-lg' title='Supprimer cette ligne'></i></td>
        </tr>";
    $i++;
}

// afficher la premieres ligne si rien n'a encore ete enregistre
if ( $i == 1 ) {
    $save_disabled='disabled';
    $TEF_NAME="Choisir type";
    $TEF_CODE="";
    $typefield="<input type='button' class='btn btn-default labelx' id='label".$i."' name='label".$i."' value='".$TEF_NAME."'>
             <div id='t".$i."' 
                style='display: none;
                       position: absolute; 
                       border-style: solid;
                       border-width: 2px;
                       background-color: $mylightcolor; 
                       border-color: $mydarkcolor;
                       width:500px;
                       height:60px;
                       padding: 5px;
                       margin-top: 100px;'>
                <i class='fa fa-euro-sign fa-lg'></i> <b>".$TEF_NAME."</b><br>
                ".write_select_type_form($i, $TEF_CODE, false)."
                  <p align=center>
             </div>"; 
    
    
    echo "<tr bgcolor=$mylightcolor>
            <td></td>
            <td>".$typefield."</td>
            <td><input type='text' class='commentaire' name='commentaire".$i."' id='commentaire".$i."' size='60' maxlength='66' value=\"\" 
                title='Saisissez le descriptif lié à cette ligne' ></td>
            <td><input type='text' class='quantite' name='quantite".$i."' id='quantite".$i."' size='3' value=''
                onchange=\"checkNumberNullAllowed(this,'');\" ></td>
            <td><input type='text' class='pu' name='pu".$i."' id='pu".$i."' size='5' value='' 
                onchange=\"checkFloat(this,'');\" ></td>
            <td><input type='text' class='remise' name='remise".$i."' id='remise".$i."' size='5' value=\"\" 
                title='Lieu où les frais ont été engagés' ></td>
            <td><input type='text' class='subtotal' name='subtotal".$i."' id='subtotal".$i."' size='5' value='0' readonly disabled
                title='Sous total' ></td>
            <td><i class='delete fa fa-trash fa-lg'  title='Supprimer cette ligne'></i></td>
        </tr>";
}
echo "</tbody>";
echo "</table>";

//=====================================================================
// boutons enregistrement
//=====================================================================

echo "<p><input type='button'  class='btn btn-default' value='Retour' name='retour' onclick=\"javascript:bouton_redirect('evenement_facturation.php?tab=".$tab."&evenement=".$evenement."');\">";
echo " <input id='save' name='save' type='submit' class='btn btn-default' value='Enregistrer' $save_disabled>";
if ( $type == 'facture' ) echo " <input type='submit' class='btn btn-default' name='btcopie' value='Copie du devis'>";
if ( check_rights($id, 29)) echo " <input type='button' class='btn btn-default' value='Configurer' name='annuler' onclick=\"javascript:bouton_redirect('element_facturable.php?evenement_facture=".$evenement."');\" title='Configurer les éléments facturables'>";
echo " <i>Total (".$default_money_symbol."):</i> <input name='sum' id='sum' readonly value='".$SUM."' size=5 style='border:0px;font-weight:bold;color:$mydarkcolor;'> ";
echo "</form></div>";

writefoot();
