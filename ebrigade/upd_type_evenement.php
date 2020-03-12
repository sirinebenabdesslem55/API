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

if (isset ($_GET["TE_CODE"])) $TE_CODE=secure_input($dbc,$_GET["TE_CODE"]);
else $TE_CODE="";
if (isset ($_GET["operation"])) $operation=secure_input($dbc,$_GET["operation"]);
else $operation="";

writehead();
echo "
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/type_evenement.js'></script>
<script type='text/javascript' src='js/ddslick.js'></script>
";

// choix d'ic�nes pour le type d'�v�nement
$query="select TE_ICON from type_evenement where TE_CODE='".$TE_CODE."'";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$current="images/evenements/".$row[0];
if ( $TE_CODE == "" ) $current="images/evenements/WHAT.png";

echo "<script type='text/javascript'>
    var ddData = [";
$f = 0;
$file_arr = array();
    
$dir=opendir('images/evenements');

while ($file = readdir ($dir)) {
    if ($file != "." && $file != ".." &&  file_extension($file) == 'png' ){
        $file_arr[$f] = "images/evenements/".$file;
        $name_arr[$f] = $file;
        $f++;
    }
}
closedir($dir);
array_multisort( $file_arr, $name_arr );

for( $i=0 ; $i < count( $file_arr ); $i++ ) {
    echo "  {
        text: '".$name_arr[$i]."',
            value: '".$name_arr[$i]."',";   
        if ( $current == $file_arr[$i] ) echo "selected: true,";
        else echo "selected: false,";
        echo "description: \"\",
        imageSrc: \"".$file_arr[$i]."\"
        },";
}
echo "];";
echo "</script>
</head>";
echo "<body><div class='table-responsive'>";

//=====================================================================
// affiche la fiche type �v�nement
//=====================================================================

$query="select te.TE_CODE, te.TE_LIBELLE, te.CEV_CODE, cev.CEV_DESCRIPTION,
        te.TE_MAIN_COURANTE, te.TE_VICTIMES, te.TE_MULTI_DUPLI, te.TE_ICON,
        te.EVAL_PAR_STAGIAIRES, te.PROCES_VERBAL, te.FICHE_PRESENCE, te.ORDRE_MISSION,
        te.CONVENTION, te.EVAL_RISQUE, te.CONVOCATIONS, te.FACTURE_INDIV, te.ACCES_RESTREINT,
        te.TE_PERSONNEL, te.TE_VEHICULES, te.TE_MATERIEL, te.TE_CONSOMMABLES, te.COLONNE_RENFORT
        from type_evenement te, categorie_evenement cev
        where cev.CEV_CODE = te.CEV_CODE
        and te.TE_CODE = '".$TE_CODE."'";
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

if ( $operation == 'addstat' ) {
    $cnt=count_entities("type_bilan", "TE_CODE='".$TE_CODE."'");
    $newnum=intval($cnt) + 1;
    $query="insert into type_bilan (TE_CODE,TB_NUM,TB_LIBELLE)
            values('".$TE_CODE."',".$newnum.",'Statistique n�".$newnum."')";
    $result=mysqli_query($dbc,$query);
    
    $query="update type_evenement set TE_MAIN_COURANTE=1 where TE_CODE='".$TE_CODE."'";
    $result=mysqli_query($dbc,$query);
}

if ( $operation == 'insert' ) {
    $TE_CODE = "";
    $TE_LIBELLE = "";
    $CEV_CODE= "";
    $TE_ICON = "";
    $TE_PERSONNEL = "1";
    $TE_VEHICULES = "0";
    $TE_MATERIEL  = "0";
    $TE_CONSOMMABLES  = "0";
    $TE_MAIN_COURANTE = "0";
    $TE_MULTI_DUPLI  = "0";
    $COLONNE_RENFORT  = "0";
    $ACCES_RESTREINT  = "0";
    $EVAL_PAR_STAGIAIRES  = "0";
    $PROCES_VERBAL  = "0";
    $FICHE_PRESENCE  = "0";
    $ORDRE_MISSION  = "0";
    $CONVENTION  = "0";
    $EVAL_RISQUE  = "0";
    $CONVOCATIONS  = "0";
    $FACTURE_INDIV  = "0";
    $TE_VICTIMES  = "0";
}

echo "<form name='evenement' action='save_type_evenement.php' method='POST'>";
echo "<input type='hidden' name='OLD_TE_CODE' value='$TE_CODE'>";
echo "<input type='hidden' name='TE_LIBELLE' value=\"$TE_LIBELLE\">";
echo "<input type='hidden' name='CEV_CODE' value='$CEV_CODE'>";


if ( $TE_CODE == "" ) {
    $img="";
    $txt="Nouveau type d'�v�nement";
    $nbtxt="";
    echo "<input type='hidden' name='operation' value='insert'>";
    $NB=0;
}
else {
    $img="<img src=images/evenements/".$TE_ICON." class='img-max-40'>";
    $txt=$TE_CODE." - ".$TE_LIBELLE;
    echo "<input type='hidden' name='operation' value='update'>";
    $query2="select count(1) from evenement where TE_CODE='".$TE_CODE."'";
    $result2=mysqli_query($dbc,$query2);
    $row2=mysqli_fetch_array($result2);
    $NB=$row2[0];
    $nbtxt="<span class='badge'>".$NB."</span> �v�nements de ce type";
}

echo "<div align=center><table class='noBorder'>
      <tr><td width = 60>".$img."</td><td>
      <font size=4><b>".$txt."</b></font><br>".$nbtxt."</td></tr></table>";

//=====================================================================
// ligne 1
//=====================================================================

echo "<p><table cellspacing=0 border=0>";
echo "<tr >
          <td class=TabHeader colspan=2>informations type d'�v�nement</td>
      </tr>";

//=====================================================================
// lignes code et description
//=====================================================================

if ( $TE_CODE == 'GAR' or $TE_CODE == 'MC' ) {
    $disabled_code='disabled';
    echo "<input type='hidden' name='TE_CODE' value='".$TE_CODE."'>";
}
else $disabled_code='';

echo "<tr bgcolor=$mylightcolor>
          <td ><b>Code</b> $asterisk</td>
          <td  align=left><input type='text' name='TE_CODE' size='5' maxlength=5 value='".$TE_CODE."' $disabled_code
                title='code �v�nement, 5 caract�res maximum,  lettres majuscules et chiffres'
                onchange=\"isValid6(evenement.TE_CODE,'".$TE_CODE."');\">";
echo " </td>
      </tr>";
      

echo "<tr bgcolor=$mylightcolor>
          <td ><b>Description</b>$asterisk</td>
          <td align=left><input type='text' name='TE_LIBELLE' size='40' value=\"$TE_LIBELLE\">";
echo " </td>
      </tr>";
      
      
//=====================================================================
// ligne icone
//=====================================================================
    
echo "<tr bgcolor=$mylightcolor><td><b>Ic�ne</td>
    <td><div id='iconSelector'></div><input type=hidden name='icon' id='icon' value=\"".$TE_ICON."\">";
    
?>
<script type="text/javascript">

$('#iconSelector').ddslick({
    data:ddData,
    width:340,
    height:400,
    selectText: "Choisir une ic�ne pour ce type d'�v�nement",
    imagePosition:"left",
    onSelected: function(data){
        document.getElementById("icon").value = data.selectedData.imageSrc;
    }   
});
</script>
<?php
    echo "</td></tr>";
      
//=====================================================================
// ligne cat�gorie
//=====================================================================

$query="select CEV_CODE, CEV_DESCRIPTION from categorie_evenement
         order by CEV_CODE asc";
$result=mysqli_query($dbc,$query);

echo "<tr bgcolor=$mylightcolor>
          <td ><b>Cat�gorie</b> $asterisk</td>
          <td align=left>
          <select name='CEV_CODE' id='CEV_CODE' onchange='change_type();'>";
             while ($row=@mysqli_fetch_array($result)) {
                if ( $row["CEV_CODE"] == $CEV_CODE ) $selected='selected';
                else $selected='';
                echo "<option value=\"".$row["CEV_CODE"]."\" $selected>".$row["CEV_CODE"]." - ".$row["CEV_DESCRIPTION"]."</option>";
             }
 echo "</select>";
 echo "</td>
     </tr>";

      
//=====================================================================
// propri�t�s
//=====================================================================

echo "<tr class=TabHeader >
          <td colspan=2>Propri�t�s</td> 
    </td></tr>";
      
 
if ( $TE_PERSONNEL == 1 ) $checked='checked';
else $checked='';

echo "<tr bgcolor=$mylightcolor>
          <td ><b>Onglet Personnel</b></td>
          <td align=left><input type='checkbox' name='TE_PERSONNEL' value='1' $checked>
          <span class=small>Il est possible d'inscrire du personnel</span>";
echo " </td>
      </tr>";

if ( $vehicules == 1 ) {
    if ( $TE_VEHICULES == 1 ) $checked='checked';
    else $checked='';

    echo "<tr bgcolor=$mylightcolor>
          <td ><b>Onglet V�hicules</b></td>
          <td align=left><input type='checkbox' name='TE_VEHICULES' value='1' $checked>
          <span class=small>Il est possible d'engager des v�hicules</span>";
    echo " </td>
      </tr>";
}
if ( $materiel == 1 ) {
    if ( $TE_MATERIEL == 1 ) $checked='checked';
    else $checked='';

    echo "<tr bgcolor=$mylightcolor>
          <td ><b>Onglet Mat�riel</b></td>
          <td align=left><input type='checkbox' name='TE_MATERIEL' value='1' $checked>
          <span class=small>Il est possible d'engager du mat�riel</span>";
    echo " </td>
      </tr>";
}
if ( $consommables ) {
    if ( $TE_CONSOMMABLES == 1 ) $checked='checked';
    else $checked='';

    echo "<tr bgcolor=$mylightcolor>
          <td ><b>Onglet Consommables</b></td>
          <td align=left><input type='checkbox' name='TE_CONSOMMABLES' value='1' $checked>
          <span class=small>Il est possible de consommer des produits</span>";
    echo " </td>
      </tr>";
}     
if ( $TE_MAIN_COURANTE == 1 ) $checked='checked';
else $checked='';

echo "<tr bgcolor=$mylightcolor>
          <td ><b>Onglet Rapport/Statistiques</b></td>
          <td align=left><input type='checkbox' name='TE_MAIN_COURANTE'  id='TE_MAIN_COURANTE' value='1' $checked onchange=\"javascript:changedRapport();\">
          <span class=small>Un rapport ou main courante peut �tre cr��e sur ce type d'�v�nement </span>";
echo " </td>
      </tr>";
      
if ( $TE_VICTIMES == 1 and $TE_MAIN_COURANTE == 1) $checked='checked';
else $checked='';

if ( $TE_MAIN_COURANTE == 1 ) $disabled = "";
else $disabled = "disabled";

echo "<tr bgcolor=$mylightcolor>
          <td ><b>Victimes</b></td>
          <td align=left><input type='checkbox' name='TE_VICTIMES'  value='1' id='TE_VICTIMES' $checked $disabled
            title=\"Cette propri�t� ne p�ut �tre activ�e que si la case 'Rapport et Statistiques est coch�e\">
          <span class=small>Des victimes peuvent �tre enregistr�es.</span>";
echo " </td>
      </tr>";
      
if ( $TE_MULTI_DUPLI == 1 ) $checked='checked';
else $checked='';

echo "<tr bgcolor=$mylightcolor>
          <td ><b>Duplication multiple possible</b></td>
          <td align=left><input type='checkbox' name='TE_MULTI_DUPLI' value='1' $checked>
          <span class=small>Il est possible de faire des duplications multiples </span>";
echo " </td>
      </tr>";
      
if ( $syndicate == 0 ) {
    if ( $COLONNE_RENFORT == 1 ) $checked='checked';
    else $checked='';

    echo "<tr bgcolor=$mylightcolor>
              <td ><b>Colonne de ".$renfort_label." possible</b></td>
              <td align=left><input type='checkbox' name='COLONNE_RENFORT' value='1' $checked>
              <span class=small>La propri�t� colonne de ".$renfort_label." peut �tre activ�e.</span>";
    echo " </td>
          </tr>";
}
if ( $ACCES_RESTREINT == 1 ) $checked='checked';
else $checked='';

echo "<tr bgcolor=$mylightcolor>
          <td ><b>Acc�s restreint</b></td>
          <td align=left><input type='checkbox' name='ACCES_RESTREINT' value='1' $checked>
          <span class=small>Seuls les inscrits ou ceux qui ont la permission 26 peuvent voir cet �v�nement.</span>";
echo " </td>
      </tr>";
//=====================================================================
// documents
//=====================================================================

echo "<tr class=TabHeader >
          <td colspan=2>Documents g�n�r�s</td>
    </td></tr>";    

if ( $EVAL_PAR_STAGIAIRES == 1 ) $checked='checked';
else $checked='';
echo "<tr bgcolor=$mylightcolor>
          <td  ><b>Fiche �valuation par les stagiaires</b></td>
          <td align=left><input type='checkbox' name='EVAL_PAR_STAGIAIRES'  value='1' $checked>
          <span class=small>Une fiche d'�valuation de la formation est disponible</span>
    </td></tr>";
      
if ( $PROCES_VERBAL == 1 ) $checked='checked';
else $checked='';
echo "<tr bgcolor=$mylightcolor>
          <td  ><b>Proc�s verbal</b></td>
          <td align=left><input type='checkbox' name='PROCES_VERBAL'  value='1' $checked>
          <span class=small>Un proc�s verbal de r�sultats de la formation est disponible </span>
    </td></tr>";
    
if ( $FICHE_PRESENCE == 1 ) $checked='checked';
else $checked='';
echo "<tr bgcolor=$mylightcolor>
          <td ><b>Fiche de pr�sence</b> </td>
          <td  align=left><input type='checkbox' name='FICHE_PRESENCE'  value='1' $checked>
          <span class=small>Une fiche de pr�sence est cr��e, apr�s cl�ture des inscriptions</span>
    </td></tr>";

if ( $ORDRE_MISSION == 1 ) $checked='checked';
else $checked='';
echo "<tr bgcolor=$mylightcolor>
          <td  ><b>Ordre de mission</b> </td>
          <td align=left><input type='checkbox' name='ORDRE_MISSION'  value='1' $checked>
          <span class=small>Un ordre de mission est cr��, apr�s cl�ture des inscriptions</span>
    </td></tr>";
      
if ( $CONVENTION == 1 ) $checked='checked';
else $checked='';
echo "<tr bgcolor=$mylightcolor>
          <td ><b>Convention</b> </td>
          <td align=left><input type='checkbox' name='CONVENTION'  value='1' $checked>
          <span class=small>Une convention est cr��e </span>
    </td></tr>";
    
if ( $EVAL_RISQUE == 1 ) $checked='checked';
else $checked='';
echo "<tr bgcolor=$mylightcolor>
          <td ><b>Grille �valuation risques</b> </td>
          <td align=left><input type='checkbox' name='EVAL_RISQUE'  value='1' $checked>
          <span class=small>Une Grille d'�valuation des risques peut �tre cr��e </span>
    </td></tr>";
    
if ( $CONVOCATIONS == 1 ) $checked='checked';
else $checked='';
echo "<tr bgcolor=$mylightcolor>
          <td  ><b>Convocations</b> </td>
          <td  align=left><input type='checkbox' name='CONVOCATIONS'  value='1' $checked>
          <span class=small>Des convocations sont cr��es, apr�s cl�ture des inscriptions </span>
    </td></tr>";
    
if ( $FACTURE_INDIV == 1 ) $checked='checked';
else $checked='';
echo "<tr bgcolor=$mylightcolor>
          <td ><b>Factures individuelles</b> </td>
          <td align=left><input type='checkbox' name='FACTURE_INDIV'  value='1' $checked>
          <span class=small>Des factures individuelles sont cr��es, si le montant est renseign� </span>
    </td></tr>";
      

//=====================================================================
// statistiques
//=====================================================================

if ( $TE_MAIN_COURANTE == 1 ) $style="";
else  $style="style='display:none'";

echo "<tr class='TabHeader statRow' $style>
      <td>Statistiques</td>
      <td>Incr�mentation selon rapport / fiche bilan</td>
</td></tr>";

$list=array('','VICTIMES','INTERVENTIONS','VI_INFORMATION','VI_REFUS','VI_IMPLIQUE','VI_MALAISE','VI_SOINS','VI_MEDICALISE',
            'VI_DETRESSE_VITALE','VI_DECEDE','VI_VETEMENT','VI_ALIMENTATION','VI_REPOS','VI_TRAUMATISME','VI_REPARTI');

asort($list);

$transporteur=array();

$transporteur['VI_TRANSPORT'] = 'Transport Tous types';
$transporteur['TRANSPORT_AUTRE'] = 'Transport Autre';
$list2=array('VI_TRANSPORT','TRANSPORT_AUTRE','TRANSPORT_ASS');
$query = "select T_CODE, T_NAME from transporteur where T_CODE='ASS'";
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);
$transporteur['TRANSPORT_ASS'] = $T_NAME;
asort($list2);

$figures=array();
$query="select be.TB_NUM, count(1) as NB from bilan_evenement be, evenement e where e.TE_CODE='".$TE_CODE."' and be.E_CODE = e.E_CODE group by be.TB_NUM";
$result=mysqli_query($dbc,$query);
while ( custom_fetch_array($result)) {
    $figures[$TB_NUM] = $NB;
}

$query="select tb.TB_ID,tb.TB_NUM,tb.TB_LIBELLE,tb.VICTIME_DETAIL,tb.VICTIME_DETAIL2
        from type_bilan tb
        where tb.TE_CODE='".$TE_CODE."'
        order by tb.TB_NUM";
$result=mysqli_query($dbc,$query);
$nb=mysqli_num_rows($result);

echo "<input type='hidden' name='nb_stats' id='nb_stats' value='".$nb."'>";

while ( custom_fetch_array($result)) {
    $i = $TB_NUM;
    $NB1 = intval(@$figures[$i]);
    if ( $NB1 == 0 ) $color = 'orange';
    else $color = 'green';
    $NB1= "<i class='fa fa-check-circle' style='color:$color;' title='Cette statistique a �t� renseign�e sur $NB1 �v�nements'></i>";
    
    $help = "La statistique n�$i est automatiquement incr�ment�e si une intervention\nou une fiche victime est enregistr�e avec une propri�t� particuli�re";
    echo "<tr bgcolor=$mylightcolor class='statRow' $style>
          <td> <span class='badge' title='statistique n�$i'>".$i."</span> <input type = 'text' name='tb_".$i."' value=\"".$TB_LIBELLE."\" size='25' maxlength=40></td>
          <td>
          <select name='victime1_".$i."' title=\"".$help."\">";
    foreach ($list as $value) {
        if ( $value == $VICTIME_DETAIL ) $selected='selected';
        else $selected='';
        $lib=str_replace("vi_","",strtolower($value));
        $lib=str_replace("_"," ",ucfirst($lib));
        if ( $lib == '' ) $lib="non d�fini";
        echo "<option value='".$value."' $selected>".$lib."</option>";
    }
    echo "<optgroup label='Transport victime (par)'>";
    foreach ($list2 as $value) {
        if ( $value == $VICTIME_DETAIL ) $selected='selected';
        else $selected='';
        $lib=$transporteur[$value];
        echo "<option value='".$value."' $selected>".$lib."</option>";
    }
    
    echo "</select> ou 
            <select name='victime2_".$i."' title=\"".$help."\">";
    foreach ($list as $value) {
        if ( $value == $VICTIME_DETAIL2 ) $selected='selected';
        else $selected='';
        $lib=str_replace("vi_","",strtolower($value));
        $lib=str_replace("_"," ",ucfirst($lib));
        if ( $lib == '' ) $lib="non d�fini";
        echo "<option value='".$value."' $selected>".$lib."</option>";
    }
    echo "<optgroup label='Transport victime (par)'>";
    foreach ($list2 as $value) {
        if ( $value == $VICTIME_DETAIL2 ) $selected='selected';
        else $selected='';
        $lib=$transporteur[$value];
        echo "<option value='".$value."' $selected>".$lib."</option>";
    }
    
    echo "</select> ".$NB1;
    if ( $i == $nb ) 
        echo " <a href='#' onclick=\"javascript:delete_stat('".$TB_ID."','".$TE_CODE."');\"><i class='fa fa-trash' title='supprimer cette statistique'></i></a>";
    echo "</td>
    </tr>";
}

// ajout nouvelle stat
$next = $i + 1;
echo "<tr height=28 bgcolor=$mylightcolor class='statRow' $style>";
echo "<td align=center>
    <a href='upd_type_evenement.php?TE_CODE=".$TE_CODE."&operation=addstat' title='cliquer pour ajouter une statistique � ce type d'�v�nement'>
    <i class='fa fa-plus-circle fa-lg' style='color:green;' title=\"Ajouter une nouvelle statistique � ce type d'�v�nement\"></i></a>
    <b>Ajouter une statistique</b></td><td></td>";
echo "</tr>";

echo "</table>";
echo "<p><input type='submit' class='btn btn-default' value='sauver'> ";
echo "</form>";

if ( $NB > 0 ) {
    $disabled='disabled';
    $t="Suppression impossible de ce type car il y a $NB �v�nements dans la base";
}
else {
    $disabled="";
    $t="Supprimer ce type";
}

echo "<input type='button' class='btn btn-default' value='supprimer' onclick=\"javascript:suppress('".$TE_CODE."');\" title=\"".$t."\" $disabled $disabled_code> ";
echo "<input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:redirect('type_evenement.php');\">";
echo "</form>";
echo "</div>";
writefoot();
?>
