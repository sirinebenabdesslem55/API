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
get_session_parameters();
$section=$_SESSION['SES_SECTION'];
$suggestedsection=$_SESSION['SES_SECTION'];
$id=$_SESSION['id'];


if (isset ($_GET["suggestedcompany"])) $suggestedcompany=intval($_GET["suggestedcompany"]);
else $suggestedcompany=0;

if (isset ($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement=0;

if (isset ($_GET["category"])) $statut=$_GET["category"];
else if (isset ($_GET["statut"])) $statut=$_GET["statut"];
else $statut='';

if ( $block_personnel and $statut <> 'EXT' ) {
    write_msgbox("ERREUR", $error_pic, "La création de fiches personnel est bloquée.<br><p align=center>
                    <a onclick=\"javascript:history.back(1);\"><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
    exit;
}

$full=true; // saisie normale, tous les champs

if ( $statut == 'EXT' ) {
    check_all(37);
    $mysection=get_highest_section_where_granted($id,37);
    if ( $evenement > 0 ) {
        $full=false; // saisie rapide
        $suggestedsection = get_section_organisatrice($evenement);
    }
    else if ( check_rights($id, 37, $filter)) $suggestedsection=$filter;
}
else { // internes
    check_all(1);
    $mysection=get_highest_section_where_granted($id,1);
    if ( check_rights($id, 1, $filter)) $suggestedsection=$filter;
    $suggestedcompany=0;
}

if ( check_rights($id, 24) ) $section='0';
else if ( $mysection <> '' ) {
    if ( is_children($section,$mysection)) $section=$mysection;
}

writehead();
echo "
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/personnel.js?version=".$version."?patch=".$patch_version."'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<script type='text/javascript' src='js/zipcode.js'></script>
<script type='text/javascript' src='js/ddslick.js'></script>";

echo "
<STYLE type='text/css'>
.categorie{color:".$mydarkcolor."; background-color:".$mylightcolor.";font-size:10pt;}
.type{color:".$mydarkcolor."; background-color:white;font-size:10pt;}
</STYLE>
</head>";
echo "<body onload='changedTypeIns();'>";

// en cas d'erreur d'insertion, remettre les données dans le formulaire
if (isset ($_GET["prenom"])) $prenom=$_GET["prenom"];
else $prenom="";
if (isset ($_GET["prenom2"])) $prenom2=$_GET["prenom2"];
else $prenom2="";
if (isset ($_GET["nom"])) $nom=$_GET["nom"];
else $nom="";
if (isset ($_GET["nom_naissance"])) $nom_naissance=$_GET["nom_naissance"];
else $nom_naissance="";
if (isset ($_GET["birth1"])) $birth1=$_GET["birth1"];
else $birth1="";
if (isset ($_GET["birthplace"])) $birthplace=$_GET["birthplace"];
else $birthplace="";
if (isset ($_GET["birthdep"])) $birthdep=$_GET["birthdep"];
else $birthdep="";
if ( intval($birthdep) == 0 ) $birthdep="";
if (isset ($_GET["debut1"])) $debut1=$_GET["debut1"];
else $debut1=date('d-m-Y');
if (isset ($_GET["service"])) $service=$_GET["service"];
else $service="";
if (isset ($_GET["address"])) $address=$_GET["address"];
else $address="";
if (isset ($_GET["city"])) $city=$_GET["city"];
else $city="";
if (isset ($_GET["zipcode"])) $zipcode=$_GET["zipcode"];
else $zipcode="";
if (isset ($_GET["phone"])) $phone=$_GET["phone"];
else $phone="";
if (isset ($_GET["phone2"])) $phone2=$_GET["phone2"];
else $phone2="";
if (isset ($_GET["matricule"])) $matricule=$_GET["matricule"];
else $matricule="";
if (isset ($_GET["email"])) $email=$_GET["email"];
else $email="";
if (isset ($_GET["grade"])) $grade=$_GET["grade"];
else if ( $nbsections == 0 ) $grade="";
else $grade="-";
if (isset ($_GET["profession"])) $profession=$_GET["profession"];
else $profession="SPP";
if (isset ($_GET["civilite"])) $civilite=$_GET["civilite"];
else $civilite="1";
if (isset ($_GET["habilitation"])) $habilitation=$_GET["habilitation"];
else $habilitation="0";
if (isset ($_GET["type_salarie"])) $type_salarie=$_GET["type_salarie"];
else $type_salarie="";
if (isset ($_GET["heures"])) $heures=$_GET["heures"];
else $heures="";
if (isset ($_GET["hide"])) $hide=$_GET["hide"];
else $hide="0";
if (isset ($_GET["section"])) $suggestedsection=$_GET["section"];
if (isset ($_GET["statut"])) $statut=$_GET["statut"];
if (isset ($_GET["regime_travail"])) $regime_travail=$_GET["regime_travail"];
else $regime_travail="0";

//=====================================================================
// affiche la fiche personnel
//=====================================================================
$disabled="";

if ( $syndicate == 1 ) $t = 'un adhérent';
else $t = 'une personne ';

echo "<div align=center><font size=4><b>Ajouter ".$t."<br></b></font>";
if ( $evenement > 0 ) echo "<br><small>avec inscription immédiate sur l'événement</small>";

echo "<p><div class='table-responsive'><table cellspacing=0 border=0>";
echo "<form name='personnel' action='save_personnel.php' method=POST>";
print insert_csrf('insert_personnel');
echo "<input type='hidden' name='P_ID' value='100'>";
echo "<input type='hidden' name='operation' value='insert'>";
echo "<input type='hidden' name='habilitation' value='0'>";
echo "<input type='hidden' name='habilitation2' value='-1'>";
echo "<input type='hidden' name='old_member' value='0'>";
echo "<input type='hidden' name='evenement' value='$evenement'>";

//=====================================================================
// ligne 1
//=====================================================================

echo "<tr class=TabHeader>
             <td width=400 colspan=2>Informations obligatoires</td>
      </tr>";
      
echo "<tr bgcolor=$mylightcolor>
            <td width=400 colspan=2></td>";
echo "</tr>";

//=====================================================================
// ligne profession
//=====================================================================

if ( $syndicate == 1 and $full) {
  $query2="select TP_CODE, TP_DESCRIPTION from type_profession order by TP_CODE desc";
  $result2=mysqli_query($dbc,$query2);

  echo "<tr bgcolor=$mylightcolor>
            <td><b>Type Profession</b></td>
            <td align=left>
        <select name='profession' $disabled>";
        while ($row2=@mysqli_fetch_array($result2)) {
                $TP_CODE=$row2["TP_CODE"];
                $TP_DESCRIPTION=$row2["TP_DESCRIPTION"];
                if ( $TP_CODE <> '-' ) $TP_DESCRIPTION=$TP_CODE.' - '.$TP_DESCRIPTION;
                if ( $profession == $TP_CODE ) $selected="selected";
                else $selected="";
                echo "<option value='$TP_CODE' $selected>$TP_DESCRIPTION</option>"; 
        }
  echo "</select>";
  echo "</tr>";
}

//=====================================================================
// ligne grade
//=====================================================================

if ( $grades == 1 and $full) {

    $query2=query_grades();
    $result2=mysqli_query($dbc,$query2);

    echo "<script type='text/javascript'>
        var ddData = [";
    while (custom_fetch_array($result2)) {
       echo "    {
            text: \"".$G_DESCRIPTION."\",
            value: '".$G_GRADE."',";
            if ( $grade == $G_GRADE ) echo "selected: true,";
            else echo "selected: false,";
            $img=$grades_imgdir."/".$G_GRADE.".png";
            if (! file_exists($img) or $G_GRADE == '-') $img="";
            echo "description: \"\",
            imageSrc: \"".$img."\"
            },";
    }
    echo "];";
    echo "</script>";

    echo "<tr bgcolor=$mylightcolor>
            <td><b>Grade</b> $asterisk</td>
            <td align=left>
            <div id='iconSelector'></div><input type=hidden name='grade' id='grade' value=\"".$grade."\">";
    
?>
<script type="text/javascript">

$('#iconSelector').ddslick({
    data:ddData,
    width:330,
    height:400,
    background:"<?php echo $mylightcolor; ?>",
    selectText: "Choisir le grade",
    imagePosition:"left",
    onSelected: function(data){
        document.getElementById("grade").value = data.selectedData.imageSrc;
    }   
});
</script>

<?php
}
     
//=====================================================================
// ligne type
//=====================================================================

if ( $full ) {
    $ext_style="style='background-color:#00ff00;color:black;'";
    $other_style="style='background-color:white;color:black;'";
    
    $query2="select S_STATUT, S_DESCRIPTION from statut";
    if ( $sdis  ) $query2 .= " where S_CONTEXT = 3";
    else $query2 .= " where S_CONTEXT =".$nbsections;
    if ( $army ) $query2 .= " and S_STATUT not in ('BEN','SAL','ADH','FONC')";
    else if ( $syndicate ) $query2 .= " and S_STATUT not in ('BEN','ACT','RES','CIV')";
    else $query2 .= " and S_STATUT not in ('ADH','FONC','ACT','RES','CIV')";
    if (! check_rights($id, 37) or $externes == 0 ){
        $query2 .= " and S_STATUT <> 'EXT'";
    }
    if ( $block_personnel ) 
        $query2 .= " and S_STATUT = 'EXT'";
    $query2 .= " order by S_DESCRIPTION";
    
    $result2=mysqli_query($dbc,$query2);

    echo "<tr bgcolor=$mylightcolor>
            <td><b>Statut</b> $asterisk</td>
            <td align=left>
            <select name='statut' id='statut' $disabled onchange=\"javascript:changedTypeIns();\">";
    while (custom_fetch_array($result2)) {
        $selected='';
        if ( $statut == $S_STATUT ) $selected='selected';
        else if ( $statut == 'EXT' and $S_STATUT == 'EXT') $selected='selected';
        else if ( $statut == '' and $S_STATUT == 'RES' and $army ) $selected='selected';
        else if ( $statut == '' and $S_STATUT == 'ADH' and $syndicate ) $selected='selected';
        else if ( $statut == '' and ($S_STATUT == 'BEN' or $S_STATUT == 'SPV') ) $selected='selected';
        else $selected='';
        if ( $S_STATUT == 'EXT' ) $style= $ext_style;
        else $style = $other_style;
        echo "<option value='$S_STATUT' $selected $style >$S_DESCRIPTION</option>";
    }
    echo "</select></tr>";


    // particularités des SPP
    if ( $statut == 'SPP') $style="";
    else  $style="style='display:none'";
    echo "<tr bgcolor=$mylightcolor id='tsppRow' $style class='pad0 trcolor'>
          <td><b>Régime travail</b> $asterisk</td>
          <td align=left>";
          
    echo " <select name='regime_travail' id='regime_travail'
                title='Choisir le régime de travail'>";
    $query2="select TRT_CODE, TRT_DESC from type_regime_travail order by TRT_ORDER asc";
    $result2=mysqli_query($dbc,$query2);
    while (custom_fetch_array($result2)) {
        if ( $regime_travail == $TRT_CODE ) $selected='selected';
        else $selected='';
        echo "<option value='$TRT_CODE' $selected title=\"".$TRT_DESC."\">".$TRT_CODE."</option>";
    }
    echo "</select>";
    
    // particularités des salariés
    $query2="select TS_CODE, TS_LIBELLE from type_salarie order by TS_LIBELLE asc";
    $result2=mysqli_query($dbc,$query2);

    if ( $nbsections == 0 ) {
    echo "<tr bgcolor=$mylightcolor id='tsRow'>
          <td><b>Salarié</b> $asterisk</td>
          <td align=left>";
    echo " <select name='type_salarie' id='type_salarie'
                onchange=\"javascript:changedSalarie();\"
                title='A préciser pour le personnel salarié ou fonctionnaire seulement'>";
    echo "<option value='0'>---choisir---</option>";
         while ($row2=@mysqli_fetch_array($result2)) {
                      $TS_CODE=$row2["TS_CODE"];
                      $TS_LIBELLE=$row2["TS_LIBELLE"]; 
                      if ( $type_salarie == $TS_CODE) $selected='selected';
                      else $selected='';
                      echo "<option value='$TS_CODE' $selected>$TS_LIBELLE</option>";
                     }
    echo "</select>";
                
    echo " <i><font size=1>heures / semaine</font></i> 
                <input type='text' name='heures' id='heures' size='3' value='$heures'
                    title='A préciser pour le personnel salarié seulement'
                    onchange='checkNumber(form.heures,\"\");'>";
    echo "</tr>";
    }
    else {
        echo "<tr bgcolor=$mylightcolor id='tsRow' style='display:none'>
        <input type='hidden' id='type_salarie' value=''>
        <input type='hidden' id='heures' value=''>
        </tr>";
    }
}
else 
    echo "<input type='hidden' name='statut' value='EXT'>";


//=====================================================================
// ligne civilité
//=====================================================================

$query2="select TC_ID, TC_LIBELLE from type_civilite" ;
if ( $syndicate == 1 or $nbsections > 0 ) $query2 .=" where TC_ID < 4 ";
$query2 .=" order by TC_ID";
$result2=mysqli_query($dbc,$query2);

echo "<tr bgcolor=$mylightcolor>
            <td ><b>Civilité</b></font> $asterisk</td>
            <td align=left>
            <select name='civilite' id='civilite'>";
            while ($row2=@mysqli_fetch_array($result2)) {
                  $TC_ID=$row2["TC_ID"];
                  $TC_LIBELLE=$row2["TC_LIBELLE"];
                  if ( $TC_ID == $civilite) $selected='selected';
                  else $selected='';
                  echo "<option value='$TC_ID' $selected>$TC_LIBELLE</option>";
             }
             echo "</select>";    
echo "</tr>";

//=====================================================================
// ligne nom
//=====================================================================

echo "<tr bgcolor=$mylightcolor>
            <td ><b>Nom</b> $asterisk</td>
            <td align=left>
            <input type='text' name='nom' size='25' value=\"".$nom."\" $disabled onchange='isValid3(form.nom,'nom');' maxlength='30'>";
echo "</tr>";


//=====================================================================
// ligne prénom
//=====================================================================

echo "<tr bgcolor=$mylightcolor>
            <td><b>Prénom</b> $asterisk</td>
            <td align=left>
            <input type='text' name='prenom' size='20' value=\"".$prenom."\" $disabled onchange='isValid3(form.prenom,'prénom');' maxlength='25'></td>";
echo "</tr>";

//=====================================================================
// ligne 2eme prénom
//=====================================================================

echo "<tr bgcolor=$mylightcolor>
            <td align=right><i>Deuxième prénom</i></td>
            <td align=left>
            <input type='text' id='prenom2' name='prenom2' size='20' value=\"".$prenom2."\" $disabled onchange='isValid4(form.prenom2,'prénom2');' maxlength='25'
            title='saisissez le 2ème prénom, facultatif'>
            <input type='checkbox' id='no_prenom' name='no_prenom' value='1' title=\"Cocher si il n'y a pas de 2ème prénom.\" onchange='no_second_firstname();'>
            <span class='small'>Cocher si pas de 2ème prénom</span></td>";
echo "</tr>";

//=====================================================================
// ligne nom de naissance
//=====================================================================
if ( $full ) {
    echo "<tr bgcolor=$mylightcolor>
          <td align=right><i>Nom de naissance</i></td>
          <td align=left>
            <input type='text' name='nom_naissance' size='25' value=\"".$nom_naissance."\" $disabled onchange='isValid4(form.nom_naissance,'nom_naissance');' maxlength='30'
            title='saisissez le nom de naissance, ou nom de jeune fille'></td>";
    echo "</tr>";    
}
//=====================================================================
// ligne matricule
//=====================================================================
if ( $full ) {
    if ( $army == 0 and $nbsections == 0) $i = "Identifiant";
    else $i = "Matricule";

    echo "<tr bgcolor=$mylightcolor id=iRow>";
    echo "<td><b>".$i."</b> $asterisk</td>
          <td align=left>
                <input type='text' name='matricule' size='10' value=\"$matricule\" $disabled onchange='isValid(form.matricule);' ><font size=1><i> Doit être unique dans la base</i></font></td>";        
    echo "</tr>";
}
else 
    echo "<input type='hidden' name='matricule' value=\"".$matricule."\">";

//=====================================================================
// section
//=====================================================================
if ( $full ) {
    if ( $syndicate == 1 ) $a = "Numéro de département";
    else if ( $nbsections == 0 ) $a = "Affectation";
    else $a = "Section";
        
    echo "<tr bgcolor=$mylightcolor>
              <td><b>".$a."</b> $asterisk</td>
              <td align=left>";
    echo "<select id='groupe' name='groupe' $disabled>";
        
    $level=get_level($suggestedsection);
    if ( $level == 0 ) $mycolor=$myothercolor;
    elseif ( $level == 1 ) $mycolor=$my2darkcolor;
    elseif ( $level == 2 ) $mycolor=$my2lightcolor;
    elseif ( $level == 3 ) $mycolor=$mylightcolor;
    else $mycolor='white';
    $class="style='background: $mycolor;'";

    if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
    else $sectionorder=$defaultsectionorder;
        
    if ( check_rights($_SESSION['id'], 24))
        display_children2(-1, 0, $suggestedsection, $nbmaxlevels, $sectionorder);
    else { 
        echo "<option value='$suggestedsection' $class >".get_section_code($suggestedsection)." - ".get_section_name($suggestedsection)."</option>";
        display_children2($section, $level +1, $suggestedsection, $nbmaxlevels);
    }
        
    echo "</select></td> ";
    echo "</tr>";


    if (  $syndicate  == 1 ) {
            echo "<tr bgcolor=$mylightcolor>
                 <td><i>Service</i></td>
                 <td><input name=service type=text size=30 value=\"$service\"></td>
                 </tr>";
    }
}
else 
    echo "<input type='hidden' name='groupe' value='".$suggestedsection."'>";

//=====================================================================
// company
//=====================================================================

if (  $externes == 1 ) {
    echo "<tr bgcolor=$mylightcolor id='yRow'>
              <td><b>Entreprise</b> $asterisk</td>
              <td align=left>";
              
    echo "<select id='company' name='company' $disabled style='max-width:330px;font-size:10pt;'>";
    echo companychoice($suggestedsection,$suggestedcompany, true, $statut);
    echo "</select>";
    echo "</td></tr>";
}
else {
    echo "<tr id='yRow' style='display:none' />";
    echo "<input type='hidden' name='company' id='company' value='0'>";
}
//=====================================================================
// habilitations appli
//=====================================================================

if ( $full ) {
    # can grant admin only if granted on 9
    $query2="select GP_ID, GP_DESCRIPTION, GP_USAGE from groupe where GP_ID < 100";

    if ( $statut == 'EXT' ) 
        $query2 .= "  and GP_USAGE in ('all','externes')";
    else 
        $query2 .= "  and GP_USAGE in ('all','internes')";

    if (! check_rights($_SESSION['id'], 9) )
        $query2 .="   and not exists (select 1 from habilitation h, fonctionnalite f
                        where f.F_ID = h.F_ID
                        and f.F_TYPE = 2
                        and h.GP_ID= groupe.GP_ID)";

    if (! check_rights($_SESSION['id'], 46) )
        $query2 .="   and not exists (select 1 from habilitation h, fonctionnalite f
                        where f.F_ID = h.F_ID
                        and f.F_TYPE = 3
                        and h.GP_ID= groupe.GP_ID
                        and groupe.GP_USAGE = 'externes')";

    $query2 .="   order by GP_ORDER, GP_ID asc";

    $result2=mysqli_query($dbc,$query2);

    if ((check_rights($_SESSION['id'], 9) ) or (check_rights($_SESSION['id'], 25) ))
        $disabled2=""; 
    else $disabled2="disabled";

    echo "<tr bgcolor=$mylightcolor id=gRow>
              <td><b>Droit d’accès </b> $asterisk
              <a href=habilitations.php>".$miniquestion_pic."</a></td>
              <td align=left>
            
             <select name='habilitation' $disabled2>";
                 while ($row2=@mysqli_fetch_array($result2)) {
                      $GP_ID=$row2["GP_ID"];
                      $GP_DESCRIPTION=$row2["GP_DESCRIPTION"];
                      $GP_USAGE=$row2["GP_USAGE"];
                      $selected='';
                      if ( $habilitation == '0' ) {
                        if ($statut == 'EXT') $default=-1;
                        else $default=0;
                        if ($GP_ID == $default ) $selected='selected';
                      }
                      else if ($GP_ID == $habilitation ) $selected='selected';
                      echo "<option value='$GP_ID' $selected>$GP_DESCRIPTION</option>";
                 }
                echo "</select></td>";
    echo "</tr>";
}
else 
    echo "<input type='hidden' name='habilitation' value='-1'>";

//=====================================================================
// ligne date engagement
//=====================================================================

if ( $syndicate == 1 ) $t='Date adhésion';
else if ( $statut == 'EXT' )  $t='Date inscription';
else $t='Date engagement';

echo "<tr bgcolor=$mylightcolor >
          <td><b>".$t."</b> $asterisk</td>
          <td align=left>
            <input type='text' name='debut' size='10' value='".$debut1."' onchange='checkDate2(personnel.debut)' placeholder='JJ-MM-AAAA'
            class='datepicker' data-provide='datepicker'>
            </td>";
echo "</tr>";

//=====================================================================
// licence adhérent
//=====================================================================
if ( $licences ) {
    echo "<tr class=TabHeader>
             <td width=300 colspan=2>Informations de licence</td>
      </tr>";
    echo "<TR bgcolor=$mylightcolor>
                <td><b>Numéro Licence</b></td>
                <td align=left>
                <input type='text' name='licnum' size='13' value='' autocomplete='off' >
                </td>";
    echo "</TR>";
    
    echo "<TR bgcolor=$mylightcolor>
                <td><b>Date Licence</b></td>
                <td align=left>
                <input type='text' name='licence_date' size='13' value='' onchange='checkDate2(personnel.licence_date)'
                    placeholder='JJ-MM-AAAA' autocomplete='off'
                    class='datepicker' data-provide='datepicker'>
                </td>";
    echo "</TR>";

    echo "<TR bgcolor=$mylightcolor>
                <td><b>Expiration Licence</b></td>
                <td align=left>
                 <input type='text' name='licence_end' size='13' value='' onchange='checkDate2(personnel.licence_end)'
                    placeholder='JJ-MM-AAAA' autocomplete='off'
                    class='datepicker' data-provide='datepicker'>
                </td>";
    echo "</TR>";
}

//=====================================================================
// moyen de paiement
//=====================================================================
if ( $bank_accounts == 1 and $cotisations == 1 and $statut <> 'EXT' ) {
    echo "<tr bgcolor=$mylightcolor>";
    echo "<td><b>Type paiement</b> $asterisk</td>";
    echo "<td align=left>";
    $query2="select TP_ID, TP_DESCRIPTION from type_paiement";
    if ( $bank_accounts == 0 ) $query2 .=" where ( TP_ID <> 1 or TP_ID =".$TP_ID.")";
    $query2 .=" order by TP_DESCRIPTION" ;
    $result2=mysqli_query($dbc,$query2);
    echo "<select name='type_paiement'>";
    while ($row2=@mysqli_fetch_array($result2)) {
            $_TP_ID=$row2["TP_ID"];
            $_TP_DESCRIPTION=$row2["TP_DESCRIPTION"];
            if ( $_TP_ID == 1 ) $selected='selected';
            else $selected='';
            echo "<option value='$_TP_ID' $selected>$_TP_DESCRIPTION</option>";
    }
    echo "</select></td>";
}

//=====================================================================
// intercalaire
//=====================================================================
if ( $full ) {
    echo "<tr class=TabHeader>
             <td width=300 colspan=2>Informations personnelles</td>
      </tr>";
}
//=====================================================================
// ligne date de naissance
//=====================================================================

echo "<tr bgcolor=$mylightcolor>
            <td><b>Date de naissance</b></td>
            <td align=left>
            <input type='text' name='birth' size='10' value='$birth1' $disabled onchange='checkDate2(form.birth)' placeholder='JJ-MM-AAAA'>
          </td>";
echo "</tr>";

//=====================================================================
// lieu de naissance
//=====================================================================

if ( $syndicate == 0 ) {
    echo "<tr bgcolor=$mylightcolor>
            <td><b>Lieu de naissance</b></td>
            <td align=left>
          <input type='text' name='birthplace' size='25' value=\"$birthplace\" $disabled ></td>";        
    echo "</tr>";
    
    echo "<tr bgcolor=$mylightcolor>
            <td align=right><i>Département </i></td>
            <td align=left>
          <input type='text' name='birthdep' size='3' maxlength='3' value=\"$birthdep\" $disabled onchange=\"checkNumberNullAllowed(form.birthdep,'');\"></td>";        
    echo "</tr>";
}

//=====================================================================
// nationalité
//=====================================================================
if ( $syndicate == 0 ) {
    echo "<tr bgcolor=$mylightcolor><td><b>Nationalité</b>";
    $query2="select ID, NAME from pays order by ID asc";
    $result2=mysqli_query($dbc,$query2);
    echo " <td align=left><select name='pays' id='pays' $disabled title=\"Choisissez le pays correspondant à la nationalité de la personne\">";
    echo " <option value='0' selected>Non renseignée</option>";
    while ($row2=@mysqli_fetch_array($result2)) {
        $_ID=$row2["ID"];
        $_NAME=$row2["NAME"];
        if ( $_ID == $default_pays_id ) $selected = 'selected';
        else $selected = '';
        echo "<option value='$_ID' $selected>".$_NAME."</option>";
    }
    echo "</select></td></tr>";
}

//=====================================================================
// ligne email
//=====================================================================

echo "<tr bgcolor=$mylightcolor>
            <td width=300 colspan=2></td>";        
echo "</tr>";

echo "<tr bgcolor=$mylightcolor>
            <td align=right>E-Mail</td>
            <td align=left>
                <input type='text' name='email' size='25' $disabled
            value='$email' onchange='mailCheck(form.email,\"\")'></td>";    
echo "</tr>";
      
//=====================================================================
// ligne phone
//=====================================================================

echo "<tr bgcolor=$mylightcolor>
            <td align=right>Téléphone portable</td>
            <td align=left>
            <input type='text' name='phone' size='12' value='$phone' $disabled onchange='checkPhone(form.phone,\"\",\"".$min_numbers_in_phone."\")'>";
            
if ( $syndicate == 0 and $full)
    echo "Numéro abrégé <input type='text' name='abbrege' size='5' value='' $disabled></td>";        
echo "</tr>";

if ( $full ) {
    echo "<tr bgcolor=$mylightcolor>
              <td align=right>Autre Téléphone</td>
              <td align=left>
                <input type='text' name='phone2' size='12' value='$phone2' $disabled onchange='checkPhone(form.phone2,\"\",\"".$min_numbers_in_phone."\")'>";        
    echo "</tr>";
}
//=====================================================================
// ligne address
//=====================================================================

echo "<tr bgcolor=$mylightcolor>
            <td align=right>Adresse</td>
            <td align=left>
            <textarea name='address' cols='25' rows='3' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' >".$address."</textarea></td>";
echo "</tr>";

echo "<tr bgcolor=$mylightcolor>
            <td align=right>Code postal</td>
            <td align=left><input type='text' name='zipcode' id='zipcode' maxlength='5' size='5' value='$zipcode' autocomplete='off'>";
echo "</td></tr>";

echo "<tr bgcolor=$mylightcolor>
            <td align=right>Ville</td>
            <td align=left><input type='text' name='city' id='city' size='30' maxlength='30' value=\"$city\" autocomplete='off'>";
          
          
echo  "<div id='divzipcode' 
            style='display: none;
            position: absolute; 
            border-style: solid;
            border-width: 2px;
            background-color: $mylightcolor; 
            border-color: $mydarkcolor;
            width: 500px;
            height: 140px;
            padding: 5px;
            overflow-y: auto'>
            </div>";
echo "</td></tr>";

//=====================================================================
// ligne contact
//=====================================================================
if ( $syndicate == 0 and $full ) {
    echo "<tr bgcolor=$mylightcolor id=uRow1>
            <td colspan=2 width=300 align=left><b>Personne à prévenir en cas d'urgence</b></td>";
    echo "</tr>";

    echo "<tr bgcolor=$mylightcolor id=uRow2>
            <td align=right>Nom</td>
            <td align=left><input type='text' name='relation_nom' size='20' value=''></td>";
    echo "</tr>";
    echo "<tr bgcolor=$mylightcolor id=uRow3>
            <td align=right>Prénom</td>
            <td align=left><input type='text' name='relation_prenom' size='20' value=''></td>";
    echo "</tr>";
    echo "<tr bgcolor=$mylightcolor id=uRow4>
            <td align=right>Téléphone</td>
            <td align=left><input type='text' name='relation_phone' size='12' 
            value='' onchange='checkPhone(form.relation_phone,\"\",\"".$min_numbers_in_phone."\")'></td>";
    echo "</tr>";
    echo "<tr bgcolor=$mylightcolor id=uRow5>
            <td align=right>E-Mail</td>
            <td align=left>
                <input type='text' name='relation_email' size='25'
            value='$email' onchange='mailCheck(form.relation_email,\"\")'></td>";
    echo "</tr>";
}

//=====================================================================
// hide my contact infos?
//=====================================================================
if ( $full ) {
    $checked='checked';
    echo "<tr bgcolor=$mylightcolor id=cRow2>
            <td align=right>Infos de contact</td>
            <td align=left>
            <input type='checkbox' name='hide'  value='1' $checked title='Si cette case est cochée, seules certaines personnes habilitées pourront voir mes informations personnelles'>
            <i> Masquer au public</i></td>";        
    echo "</tr>";
}
else 
    echo "<input type='hidden' name='hide' value='1'>";

echo "</td></tr></table></div>";// end left table
echo "<p><input type='submit' class='btn btn-default' value='sauver'>";

if ( $evenement > 0 ) $target="evenement_display.php?evenement=".$evenement."&from=inscription";
else $target="personnel.php";

echo " <input type='button' class='btn btn-default' value='Annuler' name='annuler' onclick=\"javascript:bouton_redirect('".$target."');\"></form></div>";
writefoot();
?>
