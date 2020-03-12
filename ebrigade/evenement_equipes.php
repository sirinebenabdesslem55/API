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
check_all(41);
$id=$_SESSION['id'];
$mysection=$_SESSION['SES_SECTION'];

if (isset ($_POST["evenement"]))  $evenement=intval($_POST["evenement"]);
else $evenement=intval($_GET["evenement"]);
if (isset ($_POST["equipe"]))  $equipe=intval($_POST["equipe"]);
else if (isset ($_GET["equipe"])) $equipe=intval($_GET["equipe"]);
else $equipe=0;
if (isset ($_GET["action"])) $action=secure_input($dbc,$_GET["action"]);
else $action='display';

if (isset ($_POST["EE_NAME"])) $EE_NAME=secure_input($dbc,$_POST["EE_NAME"]);
if (isset ($_POST["EE_DESCRIPTION"])) $EE_DESCRIPTION=secure_input($dbc,$_POST["EE_DESCRIPTION"]);
if (isset ($_POST["EE_ORDER"])) $EE_ORDER=intval($_POST["EE_ORDER"]);
else $EE_ORDER=1;

if (isset ($_POST["EE_ID_RADIO"])) $EE_ID_RADIO=secure_input($dbc,$_POST["EE_ID_RADIO"]);
else $EE_ID_RADIO="";

if (isset ($_POST["EE_SIGNATURE"])) $EE_SIGNATURE=intval($_POST["EE_SIGNATURE"]);
else $EE_SIGNATURE=0;

if (isset ($_POST["icon"])) $ICON=secure_input($dbc,$_POST["icon"]);
else $ICON='';
writehead();

?>
<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
</STYLE>
<script type='text/javascript' src='js/ddslick.js'></script>
<script type='text/javascript'>

function redirect_evenement(evenement){
    url="evenement_display.php?evenement="+evenement;
    self.location.href=url;
}

function redirect_equipes(evenement){
    url="evenement_equipes.php?evenement="+evenement;
    self.location.href=url;
}

<?php

$query="select EE_ICON from evenement_equipe where E_CODE=".$evenement." and EE_ID=".$equipe;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$current=$row[0];


echo "var ddData = [";
// photos des personnes
$evts=get_event_and_renforts($evenement,false);
$query="select distinct p.P_ID, p.P_NOM, p.P_PRENOM, p.P_PHOTO
        from  pompier p, section s, evenement e, section s2, evenement_participation ep
        where ep.E_CODE in (".$evts.")
        and ep.EE_ID = ".$equipe."
        and e.E_CODE = ep.E_CODE
        and p.P_ID=ep.P_ID
        and p.P_SECTION=s.S_ID
        and e.S_ID = s2.S_ID
        and p.P_PHOTO is not null
        order by p.P_NOM";
$result=mysqli_query($dbc,$query);
while ($row=@mysqli_fetch_array($result)) {
    $P_ID=$row["P_ID"];
    $photo=str_replace('/','',$row["P_PHOTO"]);
    $name=my_ucfirst($row["P_PRENOM"])." ".strtoupper($row["P_NOM"]);
    echo "    {
        text: '".$name."',
        value: \"". $trombidir."/".$photo."\",";
    if ( $current == $trombidir."/".$photo) echo "selected: true,";
    else echo "selected: false,";
    echo " description: \"\",
        imageSrc: \"". $trombidir."/".$photo."\"
        },";
}

// plus choix d'ic�nes
$f = 0;
$file_arr = array();
$name_arr = array();

$dir=opendir('images/sitac');
while ($file = readdir ($dir)) {
    if ($file != "." && $file != ".." && ( file_extension($file) == 'png'  || file_extension($file) == 'gif') ) {
        $file_arr[$f] = "images/sitac/".$file;
        $name_arr[$f] = $file;
        $f++;
    }
}
closedir($dir);

$dir=opendir('images/vehicules');
while ($file = readdir ($dir)) {
    if ($file != "." && $file != ".." && ( file_extension($file) == 'png'  || file_extension($file) == 'gif') ) {
        $file_arr[$f] = "images/vehicules/".$file;
        $name_arr[$f] = $file;
        $f++;
    }
}
closedir($dir);

array_multisort( $file_arr, $name_arr );

for( $i=0 ; $i < count( $file_arr ); $i++ ) {
    echo "    {
        text: '".$name_arr[$i]."',
        value: '".$name_arr[$i]."',";
        if ( $current == $file_arr[$i] ) echo "selected: true,";
        else echo "selected: false,";
        echo "description: \"\",
        imageSrc: \"".$file_arr[$i]."\"
        },";
}

echo "];";

echo "</script>";
echo "</head>";
echo "<body>";

//=====================================================================
// recup�rer infos evenement
//=====================================================================
$query="select e.TE_CODE, e.E_LIBELLE, e.E_CLOSED, e.E_CANCELED, e.E_OPEN_TO_EXT, e.S_ID, te.TE_ICON
        from evenement e, type_evenement te
        where te.TE_CODE = e.TE_CODE
        and e.E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);
$row=custom_fetch_array($result);

echo "<div align=center><table class='noBorder'>
      <tr><td>
      <font size=4><b><img src=images/evenements/".$TE_ICON." height=30> ".$E_LIBELLE."</b></font></td></tr>
      </table><p>";
// bloquer les changements dans le pass�
$ended=get_number_days_after_block($evenement);
$changeallowed=true;
if ( $ended > 0 ) {
    if ( ! check_rights($id, 19, "$S_ID") ) $changeallowed=false;
}

if ( ( is_chef_evenement($id, $evenement) or check_rights($id, 15, "$S_ID")) and $changeallowed ) $update_allowed=true;
else if ( is_operateur_pc($id,$evenement)) $update_allowed=true;
else $update_allowed=false;

if ( $update_allowed ) $disabled="";
else $disabled="disabled";

//=====================================================================
// sauver informations globales ou nouvelles
//=====================================================================
if ( $update_allowed ) {
    if (isset($_POST["equipe"])) {
        $query="update evenement_equipe
             set EE_NAME=\"".$EE_NAME."\",
             EE_ORDER=\"".$EE_ORDER."\",
             EE_ICON=\"".$ICON."\",
             EE_SIGNATURE=".$EE_SIGNATURE.",
             EE_DESCRIPTION=\"".$EE_DESCRIPTION."\",
             EE_ID_RADIO=\"".$EE_ID_RADIO."\"
             where E_CODE=".$evenement."
             and EE_ID=".$equipe;
        $result=mysqli_query($dbc,$query);
        $action='display';
    }
    else if (isset($_POST["EE_NAME"])) {
        $query="select max(EE_ID) + 1 from evenement_equipe where E_CODE=".$evenement;
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        if ( $row[0] == '' ) $NEWID=1;
        else $NEWID=$row[0];
        $query="insert into evenement_equipe(E_CODE, EE_ID, EE_NAME, EE_ORDER, EE_DESCRIPTION, EE_ID_RADIO, EE_SIGNATURE, EE_ICON)
            values (".$evenement.",".$NEWID.",\"".$EE_NAME."\",".$EE_ORDER.",\"".$EE_DESCRIPTION."\",\"".$EE_ID_RADIO."\",".$EE_SIGNATURE.",\"".$ICON."\")";
        $result=mysqli_query($dbc,$query);
        $action='display';
    }

    if ( $equipe > 0 and $action == 'delete') {
        $query="delete from evenement_equipe 
             where E_CODE=".$evenement."
             and EE_ID=".$equipe;
        $result=mysqli_query($dbc,$query);
    
        $query="delete from intervention_equipe 
            where E_CODE=".$evenement."
             and EE_ID=".$equipe;
        $result=mysqli_query($dbc,$query);
        
        $query="delete from geolocalisation where TYPE='Q' and CODE = ".$evenement." and CODE2=".$equipe;
        $result=mysqli_query($dbc,$query);
     
        $evts=get_event_and_renforts($evenement,$exclude_canceled_r=false);
        $query="update evenement_participation
             set EE_ID=null
             where E_CODE in (".$evts.")
             and EE_ID=".$equipe;
        $result=mysqli_query($dbc,$query);
        $query="update evenement_vehicule
             set EE_ID=null
             where E_CODE in (".$evts.")
             and EE_ID=".$equipe;
        $result=mysqli_query($dbc,$query);
    
        $query="update evenement_materiel
             set EE_ID=null
             where E_CODE in (".$evts.")
             and EE_ID=".$equipe;
        $result=mysqli_query($dbc,$query);
        $action='display';
    }
}
//=====================================================================
// afficher une ou toutes les �quipes
//=====================================================================

echo "<form action='evenement_equipes.php' method='POST'>";
$num=0;

//modifier
if ( $action == 'update') {
    echo "<table cellspacing=0 border=0>";
     $querym="select EE_NAME, EE_ORDER, EE_ICON, EE_DESCRIPTION, EE_SIGNATURE, EE_ID_RADIO from evenement_equipe
        where E_CODE=".$evenement."
        and EE_ID=".$equipe;
    $resultm=mysqli_query($dbc,$querym);
    custom_fetch_array($resultm);
      echo "<input type=hidden name='evenement' value='".$evenement."'>
            <input type=hidden name='equipe' value='".$equipe."'>
          <tr class=TabHeader><td colspan=2>Modification</td></tr>
            <tr bgcolor=$mylightcolor><td width=100>Nom �quipe</td>
            <td width=320><input name=EE_NAME type=text size=20 maxlength='20' value=\"".$EE_NAME."\" $disabled></td>
          </tr>
          <tr bgcolor=$mylightcolor><td >Ordre affichage</td>
            <td>
          <select name=EE_ORDER $disabled>";
    for ( $i=1; $i <= 50; $i++ ) {
        if ( $i == $EE_ORDER ) $selected="selected";
        else $selected="";
        echo "<option value='".$i."' $selected>$i</option>";
    }
    echo "</select></td></tr>";
    
    // select icon
    echo "<tr bgcolor=$mylightcolor><td>Ic�ne</td>
    <td><div id='iconSelector'></div><input type=hidden name='icon' id='icon' value=\"".$EE_ICON."\">";
    
?>
<script type="text/javascript">

$('#iconSelector').ddslick({
    data:ddData,
    width:300,
    height:400,
    selectText: "Choisir une ic�ne pour cette �quipe",
    imagePosition:"left",
    onSelected: function(data){
        document.getElementById("icon").value = data.selectedData.imageSrc;
        //alert(document.getElementById("icon").value);
    }   
});
</script>
<?php
    echo "</td></tr>";
    
    echo "
        <tr class=TabHeader><td colspan=2>Modification</td></tr>
            <tr bgcolor=$mylightcolor><td width=100>Identifiant Radio</td>
            <td width=320><input name=EE_ID_RADIO type=text size='12' maxlength='12' value=\"".$EE_ID_RADIO."\" $disabled></td>
          </tr>";
    
    
    // signature
    if ( $assoc ) {
        if ( $EE_SIGNATURE == 1 ) $checked='checked';
        else $checked='';
        echo " <tr bgcolor=$mylightcolor><td>Signature</td>
                <td><input type=checkbox value=1 name='EE_SIGNATURE' $checked $disabled>
                <span class=small>propose une case signature sur l'extraction Excel</span></td></tr>";
    }
    echo "
          <tr bgcolor=$mylightcolor><td>Description/Mission</td><td>
          <textarea cols=35 rows=3 name=EE_DESCRIPTION style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' $disabled>".$EE_DESCRIPTION."</textarea></td>
          ";
          
    // personnel engag� sur l'�quipe
    // trouver tous les participants
    $evts=get_event_and_renforts($evenement,false);
    $query="select distinct p.P_ID, p.P_NOM, p.P_PHONE, p.P_PRENOM, s.S_ID, 
        p.P_OLD_MEMBER, s.S_CODE, s2.S_CODE,
        EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),p.P_BIRTHDATE))))+0 AS age,
        tp.TP_LIBELLE
        from  pompier p, section s, evenement e, section s2, evenement_participation ep
        left join type_participation tp on tp.TP_ID = ep.TP_ID
        where ep.E_CODE in (".$evts.")
        and ep.EE_ID = ".$equipe."
        and e.E_CODE = ep.E_CODE
        and p.P_ID=ep.P_ID
        and ep.EP_ABSENT = 0
        and p.P_SECTION=s.S_ID
        and e.S_ID = s2.S_ID
        order by tp.TP_NUM, p.P_NOM";
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result)) {
        if ($age < 18 ) $cmt=" <font color=red>(-18)</font> ";
        else $cmt="";
        echo "<tr bgcolor=$mylightcolor><td align=right><i class='fa fa-user fa-lg'></i></td><td align=left>".
        " <a href=upd_personnel.php?pompier=".$P_ID." title='voir fiche personnel'>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</a><span class=small2>".$cmt." ".$TP_LIBELLE."</span></td></tr>";
    }
    
    // v�hicules affect�s � l'�quipe
    $query="select distinct ev.E_CODE as EC,v.V_ID,v.V_IMMATRICULATION,v.TV_CODE, vp.VP_LIBELLE, v.V_MODELE, v.V_INDICATIF,
        vp.VP_ID, vp.VP_OPERATIONNEL, s.S_DESCRIPTION, s.S_ID, s.S_CODE,
        DATE_FORMAT(v.V_ASS_DATE, '%d-%m-%Y') as V_ASS_DATE,
        DATE_FORMAT(v.V_CT_DATE, '%d-%m-%Y') as V_CT_DATE,
        DATE_FORMAT(v.V_REV_DATE, '%d-%m-%Y') as V_REV_DATE,
        ee.EE_ID, ee.EE_NAME
        from vehicule v, vehicule_position vp, section s, evenement e, evenement_vehicule ev
        left join evenement_equipe ee on (ee.E_CODE=".$evenement." and ee.EE_ID=ev.EE_ID)
        where v.V_ID=ev.V_ID
        and e.E_CODE=ev.E_CODE
        and s.S_ID=v.S_ID
        and vp.VP_ID=v.VP_ID
        and ev.E_CODE in (".$evts.")
        and ev.EE_ID = ".$equipe."
        order by e.E_PARENT, ev.E_CODE asc";
    $result=mysqli_query($dbc,$query);

    while (custom_fetch_array($result)) {
        if ( $V_INDICATIF <> '' ) $V_IDENT = $V_INDICATIF;
        else $V_IDENT = $V_IMMATRICULATION;
        if ( $VP_OPERATIONNEL == -1) $mytxtcolor='black';
        else if ( $VP_OPERATIONNEL == 1) $mytxtcolor=$red;      
        else if ( my_date_diff(getnow(),$V_ASS_DATE) < 0 ) {
              $mytxtcolor=$red;
              $VP_LIBELLE = "assurance p�rim�e";
        }
        else if ( my_date_diff(getnow(),$V_CT_DATE) < 0 ) {
              $mytxtcolor=$red;
              $VP_LIBELLE = "CT p�rim�";      
        }
        else if ( $VP_OPERATIONNEL == 2) {
          $mytxtcolor=$orange;
        }
        else if (( my_date_diff(getnow(),$V_REV_DATE) < 0 ) and ( $VP_OPERATIONNEL <> 1)) {
            $mytxtcolor=$orange;
            $VP_LIBELLE = "r�vision � faire";
        }  
        else $mytxtcolor=$green;
        
        echo "<tr bgcolor=$mylightcolor><td align=right><i class='fa fa-car fa-lg'></i></td><td align=left>".
        " <a href=upd_vehicule.php?vid=".$V_ID." title='voir fiche v�hicule'>".$TV_CODE." - ".$V_MODELE." - ".$V_IDENT."</a> <font size=1 color=$mytxtcolor>".$VP_LIBELLE."</font></td></tr>";
    }
    
    echo "</table><p>";
    if ( $update_allowed ) echo " <input type=submit class='btn btn-default' name='OK' value='sauver' $disabled>";
    echo " <input type=button class='btn btn-default' value='retour �quipes' onclick=\"redirect_equipes('".$evenement."');\">
            <input type=button class='btn btn-default' value='annuler' onclick=\"redirect_evenement('".$evenement."');\"></td>";
}
// ajouter
else if ( $action == 'insert') {
     echo "<input type=hidden name='evenement' value='".$evenement."'>
        <table cellspacing=0 border=0>
             <tr class=TabHeader><td colspan=2>Ajouter</td></tr>
            <tr bgcolor=$mylightcolor><td width=100>Nom �quipe</td><td width=200><input name=EE_NAME type=text size=20 value='' $disabled></td>
          <tr bgcolor=$mylightcolor><td >Ordre affichage</td><td>
          <select name=EE_ORDER $disabled>";
    for ( $i=1; $i <= 50; $i++ ) {
        echo "<option value='".$i."'>$i</option>";
    } 
    echo "</select></td>";
    
    // select icon
    echo "<tr bgcolor=$mylightcolor><td>Ic�ne</td>
    <td><div id='iconSelector'></div><input type=hidden name='icon' id='icon' value=''>";
    
?>
<script type="text/javascript">

$('#iconSelector').ddslick({
    data:ddData,
    width:300,
    height:400,
    selectText: "Choisir une ic�ne pour cette �quipe",
    imagePosition:"left",
    onSelected: function(data){
        document.getElementById("icon").value = data.selectedData.imageSrc;
        //alert(document.getElementById("icon").value);
    }   
});
</script>
<?php
    echo "</td></tr>";
    
    echo "
        <tr class=TabHeader><td colspan=2>Modification</td></tr>
            <tr bgcolor=$mylightcolor><td width=100>Identifiant Radio</td>
            <td width=320><input name=EE_ID_RADIO type=text size='12' maxlength='12' value='' $disabled></td>
          </tr>";
    
    if ( $assoc ) {
        if ( $EE_SIGNATURE == 1 ) $checked='checked';
        else $checked='';
        echo "  <tr bgcolor=$mylightcolor><td>Signature</td>
                <td><input type=checkbox value=1 name='EE_SIGNATURE' $checked $disabled>
                <span class=small>propose une case signature sur l'extraction Excel</span></td></tr>";
    }        
    echo " <tr bgcolor=$mylightcolor><td>Description/Mission</td><td>
            <textarea cols=35 rows=3 name=EE_DESCRIPTION style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' $disabled></textarea></td>
           </tr>";         
          
    echo "</table><p>";
    if ( $update_allowed ) echo " <input type=submit class='btn btn-default' name='OK' value='sauver' $disabled>";
    echo " <input type=button class='btn btn-default' value='retour �quipes' onclick=\"redirect_equipes('".$evenement."');\">
            <input type=button class='btn btn-default' value='annuler' onclick=\"redirect_evenement('".$evenement."');\"></td>";
}


//lister
else if ( $action == 'display') {
     $evts=get_event_and_renforts($evenement,$exclude_canceled_r=true);
    
    $querym="select EE_ID, EE_NAME, EE_DESCRIPTION , EE_ORDER, EE_SIGNATURE, EE_ICON, EE_ID_RADIO
        from evenement_equipe
        where E_CODE=".$evenement."
        order by EE_ORDER, EE_NAME";
    $resultm=mysqli_query($dbc,$querym);
    $num=mysqli_num_rows($resultm);
    
    if ( $num == 0 ) echo "<span class=small>Aucune �quipe n'a �t� cr��e</span><p>";
    else {
        echo  "<table cellspacing=0 border=0>
            <tr class=TabHeader>
            <td width=50 align=center> ic�ne </td>
            <td width=200>Nom �quipe</td>
            <td width=150 align=left>Description</td>
            <td width=50 align=center><i class='fa fa-user fa-lg' title=\"Nombre de personnes engag�es sur l'�quipe\"></i></td>
            <td width=50 align=center><i class='fa fa-car fa-lg' title=\"Nombre de v�hicules affect�s � l'�quipe\"></i></td>
            <td width=80 align=left>ID Radio</td>";
        if ( $assoc ) 
            echo "<td width=60>Signature</td>";
        echo "<td width=50 align=center> Ordre </td>
            <td width=10></td>
            <td width=10></td>
            </tr>";

        while ( custom_fetch_array($resultm) ) {
            if ( $EE_ICON <> "" ) $EE_ICON="<img src='".$EE_ICON."' height=22>";
            
            $q2="select count(distinct P_ID) from evenement_participation
                 where E_CODE in (".$evts.")
                 and EE_ID=".$EE_ID;
            $r2=mysqli_query($dbc,$q2);
            $row2=mysqli_fetch_array($r2);
            $nb=$row2[0];
            
            $q2="select count(distinct V_ID) from evenement_vehicule
                 where E_CODE in (".$evts.")
                 and EE_ID=".$EE_ID;
            $r2=mysqli_query($dbc,$q2);
            $row2=mysqli_fetch_array($r2);
            $nb2=$row2[0];
            
            if ($EE_SIGNATURE == 1 ) $img="<i class='fa fa-check-square fa-lg' style='color:green;'  title='Une signature est propos�e sur le document Excel'></i>";
            else $img="";
            
            echo "<tr bgcolor=$mylightcolor>
                <td align=center><b>".$EE_ICON."</b></td>
                <td><b>".$EE_NAME."</b></td>
                <td align=left><small>".$EE_DESCRIPTION."</small></td>
                <td align=center><small>".$nb."</small</td>
                <td align=center><small>".$nb2."</small</td>
                <td align=left><small>".$EE_ID_RADIO."</small</td>";
            if ( $assoc  ) 
                echo "<td align=center><small>".$img."</small</td>";
            echo "<td align=center><b>".$EE_ORDER."</b></td>
                ";
            
            if (  $update_allowed )
                echo "<td><a href=evenement_equipes.php?evenement=".$evenement."&equipe=".$EE_ID."&action=update>
                    <i class='fa fa-edit ' title=\"modifier cette �quipe\"></i></a></td>
                <td><a href=evenement_equipes.php?evenement=".$evenement."&equipe=".$EE_ID."&action=delete>
                    <i class='fa fa-trash ' title=\"supprimer cette �quipe\"></i></a></td>";
            else echo "<td></td><td></td>";
        }
        echo "</table><p>";
    }
    if ( $update_allowed ) {
        echo "<a href=evenement_equipes.php?evenement=".$evenement."&equipe=0&action=insert><input type=button class='btn btn-default' value='Ajouter' title='Ajouter une �quipe'></a>";
    }
}

$_SESSION['from']='infos';
if ( $action == 'display') {
    if ($equipe > 0 or isset($_POST["EE_NAME"]))
        echo " <input type=button class='btn btn-default' value='termin�' onclick=\"redirect_evenement('".$evenement."');\">";
    else
        echo " <input type=button class='btn btn-default' value='annuler' onclick=\"redirect_evenement('".$evenement."');\">";
    echo "</div>";
}
writefoot();
?>
