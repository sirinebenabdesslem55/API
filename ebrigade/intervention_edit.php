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
$mysection=$_SESSION['SES_SECTION'];

if (isset ($_GET["action"])) $action=$_GET["action"];
elseif (isset ($_POST["action"])) $action=$_POST["action"];
else $action='update';

if (isset ($_GET["numinter"])) $numinter=intval($_GET["numinter"]);
elseif (isset ($_POST["numinter"])) $numinter=intval($_POST["numinter"]);
else $numinter="0";

if (isset ($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
elseif (isset ($_POST["evenement"])) $evenement=intval($_POST["evenement"]);
else $evenement="0";

if (isset ($_GET["type"])) $type=$_GET["type"];
else $type="M";

if (isset ($_GET["from"])) $from=$_GET["from"];
else $from="default";

$_SESSION['from_interventions']=1;

//=====================================================================
// check_security
//=====================================================================
$granted_update=false;
if ( $numinter > 0 ) {
    $query="select E_CODE from evenement_log where EL_ID=".$numinter;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $evenement=$row[0];
    if (check_rights($_SESSION['id'], 15, (get_section_organisatrice ( $evenement )))) $granted_update=true;
}
else if ($evenement > 0 ) {
    if (check_rights($_SESSION['id'], 15, (get_section_organisatrice ( $evenement )))) $granted_update=true;
}
if ( is_chef_evenement($id, $evenement)) $granted_update=true;
else if ( is_operateur_pc($id,$evenement)) $granted_update=true;

if ($granted_update) 
    $disabled='';
else  {
    $disabled='disabled';
    check_all(24);
}

writehead();

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<script type='text/javascript' src='js/intervention_edit.js'></script>

<?php

echo "
<STYLE type='text/css'>
.categorie{color:".$mydarkcolor."; background-color:".$mylightcolor.";font-size:10pt;}
.selected{color:".$mydarkcolor."; background-color:yellow;font-size:10pt;}
.type{color:".$mydarkcolor."; background-color:white;font-size:10pt;}
</STYLE>
</head>";

//=====================================================================
// traiter delete
//=====================================================================

if (isset ($_GET["numinter"]) and $action=='delete' and $granted_update) {
    $query="select E_CODE from evenement_log where EL_ID=".$numinter;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $E_CODE=$row["E_CODE"];
 
    $query="delete from evenement_log where EL_ID=".$numinter;
    $result=mysqli_query($dbc,$query);
    
    $query="delete from intervention_equipe where EL_ID=".$numinter;
    $result=mysqli_query($dbc,$query);
    
    $query="delete from bilan_victime where V_ID in (select VI_ID from victime where EL_ID=".$numinter.")";
    $result=mysqli_query($dbc,$query);
    
    $query="delete from victime where EL_ID=".$numinter;
    $result=mysqli_query($dbc,$query);

    echo "<body onload=\"redirect('".$E_CODE."');\">";
}

//=====================================================================
// Sauver les modification 
//=====================================================================

if ( isset ($_POST["numinter"])  and ($action=='update' or $action=='insert') and $granted_update) {
    $E_CODE=intval($_POST["evenement"]);
    $evts=get_event_and_renforts($evenement,$exclude_canceled_r=true);
    $EL_RESPONSABLE=intval($_POST["responsable"]); if ( $EL_RESPONSABLE == 0 ) $EL_RESPONSABLE ='null';
    $TEL_CODE=secure_input($dbc,$_POST["type"]);
    if ( isset($_POST["important"])) $EL_IMPORTANT=intval($_POST["important"]); else $EL_IMPORTANT=0;
    if ( isset($_POST["imprimer"])) $EL_IMPRIMER=intval($_POST["imprimer"]); else $EL_IMPRIMER=0;
    $EL_COMMENTAIRE=substr(secure_input($dbc,$_POST["commentaire"]),0,3000);
    $EL_TITLE=secure_input($dbc,$_POST["titre"]);
    $EL_ADDRESS=secure_input($dbc,$_POST["address"]);
    $EL_ORIGINE=secure_input($dbc,$_POST["origine"]);
    $EL_DESTINATAIRE=secure_input($dbc,$_POST["destinataire"]);
    $DATE_DEBUT=secure_input($dbc,$_POST["date_debut"]);
    $HEURE_DEBUT=secure_input($dbc,$_POST["heure_debut"]);
    $HEURE_SLL=secure_input($dbc,$_POST["heure_sll"]);
    if ( $HEURE_SLL == '' ) $HEURE_SLL='null';
    else $HEURE_SLL="'".$HEURE_SLL."'";
    $tmp=explode ( "-",$DATE_DEBUT); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
    $DATE_DEBUT="'".$year."-".$month."-".$day." ".$HEURE_DEBUT."'";
    
    $DATE_FIN="null";
    if ( isset ($_POST["date_fin"])) {    
         if ( $_POST["date_fin"] <> "" or $_POST["heure_fin"] <> "") {
            $DATE_FIN=secure_input($dbc,$_POST["date_fin"]);
            $HEURE_FIN=secure_input($dbc,$_POST["heure_fin"]);
            if ( $DATE_FIN == "" and $HEURE_FIN <> "" ) $DATE_FIN = $_POST["date_debut"];
            if ( $DATE_FIN <> '' ) {
                $tmp=explode ( "-",$DATE_FIN); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
                $DATE_FIN="'".$year."-".$month."-".$day." ".$HEURE_FIN."'";
            }
        }
    }
    
    if ( $EL_TITLE == "" or $DATE_DEBUT == "" or $HEURE_DEBUT == "" ) {
        if ( $EL_TITLE == "" ) $msg="Le titre doit être renseigné ";
        else if ( $DATE_DEBUT == "" ) $msg="La date de début doit être renseignée ";
        else $msg= "L'heure de début doit être renseignée ";
        
        write_msgbox("erreur de paramètres", $error_pic, $msg."<p align=center><a href=\"javascript:history.back(1)\"><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
        exit;
    }

    if ( $action=='insert') {
         $query="insert into evenement_log (E_CODE, TEL_CODE, EL_RESPONSABLE, EL_COMMENTAIRE,EL_TITLE, EL_ADDRESS, EL_DEBUT, EL_SLL, EL_FIN, 
                                            EL_ORIGINE, EL_DESTINATAIRE, EL_DATE_ADD, EL_IMPORTANT,EL_IMPRIMER, EL_AUTHOR)
                 values (".$E_CODE.",'".$TEL_CODE."',".$EL_RESPONSABLE.",\"".$EL_COMMENTAIRE."\",\"".$EL_TITLE."\",\"".$EL_ADDRESS."\",".$DATE_DEBUT.",".$HEURE_SLL.",".$DATE_FIN.",
                                            \"".$EL_ORIGINE."\",\"".$EL_DESTINATAIRE."\", NOW(), ".$EL_IMPORTANT.",".$EL_IMPRIMER.",".$id.")";
        $result=mysqli_query($dbc,$query);
        insert_log("INSMAIN", $E_CODE, $complement="$EL_TITLE", $code="");
        
        // notification par mail des inscrits dans le cas de la main courante
        if ( $cron_allowed == 1 ) {
            $author = my_ucfirst($_SESSION['SES_PRENOM'])." ".strtoupper($_SESSION['SES_NOM']);
            $senderName = fixcharset($author);
            
            $query="select e.E_LIBELLE, s.S_CODE from evenement e, section s where e.S_ID = s.S_ID and e.E_CODE=".$E_CODE;
            $result=mysqli_query($dbc,$query);
            custom_fetch_array($result);
            $message = "<span style='background-color: #ffff0;'><strong>Le message d’information suivant vient d’être enregistré sur la main courante [".$E_LIBELLE."] de ".$S_CODE." par ".$author."</strong></span><p>".$EL_COMMENTAIRE;
            $subject = "[Main courante - ".$E_LIBELLE."] ".$EL_TITLE;
            $query="insert into mailer(MAILDATE, MAILTO, SENDERNAME, SENDERMAIL, SUBJECT, MESSAGE)
                    select NOW(), P_EMAIL, \"".$senderName."\",\"".$_SESSION['SES_EMAIL']."\",
                    \"".$subject."\", \"".$message."\"
                    from pompier p, evenement e, evenement_participation ep
                    where p.P_OLD_MEMBER=0
                    and p.P_STATUT <> 'EXT'
                    and p.P_EMAIL <> ''
                    and p.P_ID = ep.P_ID
                    and ep.EH_ID=1
                    and ep.E_CODE = e.E_CODE
                    and e.TE_CODE = 'MC'
                    and not exists (select 1 from notification_block nb where nb.P_ID = p.P_ID and nb.F_ID=58 )
                    and e.E_CODE=".$E_CODE;
            $result=mysqli_query($dbc,$query);
        }
        
    }
    else if ( $action=='update') {
        $query="update evenement_log  set
            TEL_CODE= '".$TEL_CODE."',
            EL_TITLE=\"".$EL_TITLE."\",
            EL_COMMENTAIRE=\"".$EL_COMMENTAIRE."\",
            EL_ADDRESS=\"".$EL_ADDRESS."\",
            EL_DEBUT=".$DATE_DEBUT.",
            EL_SLL=".$HEURE_SLL.",
            EL_RESPONSABLE=".$EL_RESPONSABLE.",
            EL_FIN=".$DATE_FIN.",
            EL_ORIGINE=\"".$EL_ORIGINE."\",
            EL_DESTINATAIRE=\"".$EL_DESTINATAIRE."\",
            EL_IMPORTANT=".$EL_IMPORTANT.",
            EL_IMPRIMER=".$EL_IMPRIMER.",
            EL_DATE_UPDATE=NOW(),
            EL_UPDATED_BY=".$id."
            where EL_ID=".$numinter." and E_CODE=".$E_CODE;
        $result=mysqli_query($dbc,$query);
        insert_log("UPDMAIN", $E_CODE, $complement="$EL_TITLE", $code="");
    }
    
    // get numinter
    if ( $action == 'insert') {
        $query="select max(EL_ID) from evenement_log where E_CODE=".$E_CODE;
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $numinter=$row[0];
    }
    
    // les équipes qui étaient déjà engagées avant la modification
    $eq_old=array();
    if ( $geolocalize_enabled ) {
        $query5="select EE_ID from intervention_equipe where E_CODE=".$E_CODE." and EL_ID =  ".$numinter;
        $result5=mysqli_query($dbc,$query5);
        $nbequipes=mysqli_num_rows($result5);
        while ($row5=@mysqli_fetch_array($result5)) {
            array_push($eq_old, $row5["EE_ID"]);
        }
    }
        
    // équipes maintenant engagées sur l'intervention
    $query="delete from intervention_equipe where EL_ID=".$numinter;
    $result=mysqli_query($dbc,$query);
    $query="select EE_ID from evenement_equipe where E_CODE=".$E_CODE." order by EE_ORDER ";
    $result=mysqli_query($dbc,$query);
    $nb_equipes=mysqli_num_rows($result);
    if ( $nb_equipes > 0 ) {
        while ($row=@mysqli_fetch_array($result)) {
            $eqid=$row["EE_ID"];
            if (isset($_POST["eq_".$eqid])){
                $query2="insert into intervention_equipe(EL_ID,E_CODE,EE_ID) values (".$numinter.", ".$E_CODE.", ".$eqid.")";
                $result2=mysqli_query($dbc,$query2);
            }
            else if (in_array($eqid, $eq_old)) {
                // remettre les équipes disponibles si elles ont été désengagées
                $query2="update evenement_equipe set IS_ID=1 where E_CODE in (".$evts.") and EE_ID =".$eqid;
                $result2=mysqli_query($dbc,$query2);
            }
        }
    }
    
    // mettre à jour statut équipe pour SITAC
    if ( $geolocalize_enabled ) {
        //$query="select EE_ID from evenement_participation where E_CODE in (".$evts.") and P_ID=".$EL_RESPONSABLE;
        $query="select EE_ID from intervention_equipe where EL_ID=".$numinter." and E_CODE in (".$evts.")";
        $result=mysqli_query($dbc,$query);
        $numeq = mysqli_num_rows($result);
        if ( $numeq > 0 ) {
            $equipes="";
            while ($row=@mysqli_fetch_array($result)) {
                $equipes .= $row[0].",";
            }
            $equipes = rtrim($equipes,",");
            // intervention en cours
            $query="update evenement_equipe set IS_ID=3 where E_CODE in (".$evts.")
                and EE_ID in(".$equipes.")
                and exists( select 1 from evenement_log where EL_ID=".$numinter." and EL_DEBUT < NOW()
                        and ( EL_FIN is null or EL_FIN > NOW() or TIME(EL_FIN) = '00:00:00')
                    )";
            $result=mysqli_query($dbc,$query);

            // SLL
            $query="update evenement_equipe set IS_ID=5 where E_CODE in (".$evts.")
                and EE_ID in(".$equipes.")
                and exists( select 1 from evenement_log where EL_ID=".$numinter." and EL_DEBUT < NOW()
                        and ( EL_FIN is null or EL_FIN > NOW() or TIME(EL_FIN) = '00:00:00')
                        and ( EL_SLL is not null and EL_SLL <> '00:00:00' and EL_SLL < NOW())
                    )";
            $result=mysqli_query($dbc,$query);
            // intervention terminée
            $query="update evenement_equipe set IS_ID=1 where E_CODE in (".$evts.")
                and EE_ID in(".$equipes.")
                and exists( select 1 from evenement_log where  EL_ID=".$numinter." and EL_DEBUT < NOW()
                        and ( TIME(EL_FIN) <> '00:00:00' and EL_FIN < NOW())
                    )";
            $result=mysqli_query($dbc,$query);
        }
    }
    
    // geolocalisation de l'intervention
    if ( $EL_ADDRESS <> '' ) {
        $ret=gelocalize($numinter,'I');
    }
    
    if ( $TEL_CODE == 'M' ) {
        $query="update evenement_log 
        set EL_FIN=null, EL_SLL=null, EL_RESPONSABLE=null, EL_ADDRESS=null
        where EL_ID=".$numinter." and E_CODE=".$E_CODE;
        $result=mysqli_query($dbc,$query);
    }
    
    update_main_stats($evenement);
    
    if ( $action == 'update' or $TEL_CODE == 'M' ) {
        echo "<body onload=\"redirect('".$E_CODE."');\" />";
        exit;
    }
    else $action = 'update';
    
}

//=====================================================================
// Detail compte rendu 
//=====================================================================

$query="select ev.TE_CODE, e.E_CODE, e.TEL_CODE ,date_format(e.EL_DEBUT,'%d-%m-%Y') DATE_DEBUT, date_format(e.EL_DEBUT,'%H:%i') HEURE_DEBUT,
date_format(e.EL_FIN,'%d-%m-%Y') DATE_FIN, date_format(e.EL_FIN,'%H:%i') HEURE_FIN, date_format(e.EL_SLL,'%H:%i') EL_SLL,
e.EL_TITLE, e.EL_ADDRESS,e.EL_COMMENTAIRE,e.EL_RESPONSABLE, p.P_NOM, p.P_PRENOM,
e.EL_ORIGINE, e.EL_DESTINATAIRE,  TIMESTAMPDIFF(MINUTE,e.EL_DEBUT,e.EL_DATE_ADD) TIMEDIFF , e.EL_IMPORTANT,  e.EL_IMPRIMER,
date_format(e.EL_DATE_ADD,'le %d-%m-%Y à %H:%i') DATE_ADD,
e.EL_AUTHOR, p2.P_NOM as 'AUTHOR_NOM', p2.P_PRENOM as 'AUTHOR_PRENOM', te.TE_VICTIMES,
date_format(e.EL_DATE_UPDATE,'le %d-%m-%Y à %H:%i') DATE_UPDATE,
e.EL_UPDATED_BY, p3.P_NOM as 'UPDATER_NOM', p3.P_PRENOM as 'UPDATER_PRENOM'
from evenement_log e left join pompier p on p.P_ID = e.EL_RESPONSABLE
left join pompier p2 on p2.P_ID = e.EL_AUTHOR
left join pompier p3 on p3.P_ID = e.EL_UPDATED_BY,
evenement ev, type_evenement_log tel, type_evenement te
where tel.TEL_CODE = e.TEL_CODE
and ev.TE_CODE = te.TE_CODE
and e.E_CODE = ev.E_CODE
and e.EL_ID=".$numinter;

$result=mysqli_query($dbc,$query);
if ( mysqli_num_rows($result) > 0 ) {
    custom_fetch_array($result);
    $P_NOM=strtoupper($P_NOM);
    $P_PRENOM=my_ucfirst($P_PRENOM);
    $AUTHOR_NOM=strtoupper($AUTHOR_NOM);
    $AUTHOR_PRENOM=my_ucfirst($AUTHOR_PRENOM);
    $UPDATER_NOM=strtoupper($UPDATER_NOM);
    $UPDATER_PRENOM=my_ucfirst($UPDATER_PRENOM);
    if ( $HEURE_FIN == '00:00' ) $HEURE_FIN='';
}
else if ( $action == 'insert' ) {
    $query="select e.TE_CODE, e.E_CODE, te.TE_VICTIMES
           from evenement e, type_evenement te
           where te.TE_CODE = e.TE_CODE
           and e.E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    $E_CODE=$evenement;
     
    $query2="select date_format(EH_DATE_DEBUT,'%d-%m-%Y') EH_DATE_DEBUT, date_format(EH_DATE_FIN,'%d-%m-%Y') EH_DATE_FIN
              from evenement_horaire where E_CODE=".$evenement." order by EH_DATE_DEBUT desc";
    $result2=mysqli_query($dbc,$query2);
    $DATE_DEBUT=date('d-m-Y');
    $HEURE_DEBUT=date('H:i');
    $DATE_FIN='';
    $HEURE_FIN='';
    $EL_ADDRESS="";
    $EL_IMPORTANT="0";
    $EL_IMPRIMER="1";
    $EL_COMMENTAIRE="";
    $EL_TITLE="";
    $EL_SLL="";
    $TEL_CODE=$type;
    $EL_ORIGINE="";
    $EL_DESTINATAIRE="";
    $TIMEDIFF=0;
    $DATE_ADD="";
    $EL_RESPONSABLE="";
    $AUTHOR_NOM=strtoupper($_SESSION["SES_NOM"]);
    $AUTHOR_PRENOM=my_ucfirst($_SESSION["SES_PRENOM"]);
    $UPDATER_NOM="";
    $UPDATER_PRENOM="";
}
else {
     if ( $action <> 'delete' ) echo "Compte rendu non trouvé";
    exit;
}

$textsize=strlen($EL_COMMENTAIRE);

if ( $TEL_CODE == 'I' ) {
     $img='ambulance';
     $t="Compte rendu d'intervention";
     $tit="Type d'intervention";
    
    if ( $numinter > 0 )  {
    $S_ID=get_section_organisatrice($evenement);
    $pdf="<a href=pdf_document.php?evenement=".$evenement."&section=".$S_ID."&mode=16&numinter=".$numinter." target=_blank
          title=\"Afficher la fiche intervention.\"><i class='far fa-file-pdf fa-lg' style='color:red;'></i></a>"; 
    }
    else $pdf="";
    
}
else {
    if ( $TE_VICTIMES == 0 )  $t="Elément de compte rendu de réunion";
    else $t="Message pour le rapport";
     $img='file-text-o';
     $tit="Titre";
    $pdf="";
}

echo "\n<div align=center><table class='noBorder'>
      <tr><td><i class='fa fa-".$img." fa-3x'></i></td>
      <td><font size=4><b> ".$t." ".$pdf."</b></font></td></tr>
      </table>";
      
echo "<p><div class='table-responsive'><form action=intervention_edit.php name=formulaire method=POST>
          <table cellspacing=0 border=0 style='max-width:650px;'>
          <tr>
               <td colspan=4 class=TabHeader>Informations</td>
         </tr>";

if ( $TE_VICTIMES == 0 ) {
    $style="style='display:none'";
    $style2="";
    $style3="style='display:none'";
    $cmt_info="Saisissez le texte de compte rendu";
    $cmt_tit="Texte";
    $tit="Titre";
    $tit_info="Saisissez le titre du compte rendu, 50 caractères maxi";
}
else if ( $TEL_CODE == 'I' ) {
    $style="";
    $style2="style='display:none'";
    $style3="";
    $cmt_info="Saisissez les infos concernant les circonstances de l'intervention, mais pas le bilan de la victime qui doit apparaître sur la fiche victime.";
    $cmt_tit="Message de situation";
    
    $url="evenement_modal.php?action=intervention&evenement=".$evenement;
    $tit= write_modal( $url, "type_inter", "Type d'intervention");
    
    $tit_info="Exemples: malaise, AVP, chute, enfant blessé ... 30 caractères maxi";
}
else  {
    $style="style='display:none'";
    $style2="";
    $style3="";
    $cmt_info="Saisissez le texte du message";
    $cmt_tit="Texte du message";
    $tit="Titre";
    $tit_info="Saisissez le titre du message, exemples: essai radio, ouverture du PC ..., 30 caractères maxi";
}

$td=abs($TIMEDIFF);
if ( ($td > 10 and $TEL_CODE == 'M' and $TE_VICTIMES == 1) or ($td > 120 and $TEL_CODE == 'I')) 
        $warn=" <i class='fa fa-exclamation' style='color:orange' title=\"Attention cette ligne n'a pas été enregistrée en direct, mais ".$DATE_ADD."\" ></i> ";
else $warn='';

$query3="select EE_NAME from evenement_equipe where E_CODE=".$evenement." order by EE_ORDER, EE_NAME";

if ( $TEL_CODE <> 'I' or $assoc ) {
    echo "<tr bgcolor=$mylightcolor id='rowOrigine' $style3><td width=150><i>Origine</i></td>";
    echo "<td colspan=3 ><input name=origine id=origine type=text size=40 value=\"".$EL_ORIGINE."\" $disabled>";
    if ( $granted_update ) {
        echo "<br>";
        $result3=mysqli_query($dbc,$query3);
        while ($row3=@mysqli_fetch_array($result3)) {
            echo "<a href=\"javascript:updateField('".str_replace("'","\'",$row3[0])."','origine');\" class=small >".$row3[0]."</a> ";
        }
    }
    echo "</td></tr>";

    echo "<tr bgcolor=$mylightcolor id='rowDestinataire' $style3><td><i>Destinataire</i></td>";
    echo "<td colspan=3><input name=destinataire id=destinataire type=text size=40 value=\"".$EL_DESTINATAIRE."\" $disabled>";
    if ( $granted_update ) {
        echo "<br>";
        $result3=mysqli_query($dbc,$query3);
        while ($row3=@mysqli_fetch_array($result3)) {
            echo "<a href=\"javascript:updateField('".str_replace("'","\'",$row3[0])."','destinataire');\" class=small >".$row3[0]."</a> ";
        }
    }
    echo "</td></tr>";
}
else {
    echo "<input type='hidden' name=origine id=origine value=''>";
    echo "<input type='hidden' name=destinataire id=destinataire value=''>";
}

echo "<tr bgcolor=$mylightcolor><td>".$tit." $asterisk</td>";
echo "<td><input name='titre' id='titre' type=text size=40 value=\"".$EL_TITLE."\" $disabled title=\"".$tit_info."\"></td>";

if ( $TE_VICTIMES == 0 ) echo "<td colspan=2><input type=hidden name='important' value='0'></td>";
else {
    if ( $EL_IMPORTANT == 1 ) $checked="checked";
    else $checked="";
    echo "<td align=center><label for='important'><i class ='fa fa-exclamation-triangle fa-lg' style='color:red;' title=\"Cocher si important\" ></i></label></td><td><input type='checkbox' name='important' value='1' $checked $disabled
        title=\"Cocher si intervention ou message important\" >
      </td>";
}

echo "</tr>";

echo "<tr bgcolor=$mylightcolor>
            <td>Date ".$warn." $asterisk</td>
            <td>
            <input type='text' name='date_debut' size='10' maxlength='10' value='".$DATE_DEBUT."' placeholder='JJ-MM-AAAA' class='datepicker' data-provide='datepicker'
            onchange='checkDate2(form.date_debut)' $disabled>
          </td>        
            <td align=center>Heure $asterisk</td>
            <td>
            <input type='text' name='heure_debut' value='".$HEURE_DEBUT."' onfocus=\"fillTime(form.heure_debut);\" 
            onchange=\"checkTime(form.heure_debut,'".$HEURE_DEBUT."');\" $disabled style='width:60px;' maxlength='5' placeholder='hh:mm'>
            </td>";        
echo "</tr>";

echo "<tr bgcolor=$mylightcolor id='rowSLL' $style>
            <td align=center><i>Heure sur les lieux</i></td>
            <td >
            <input type='text' name='heure_sll' value='".$EL_SLL."' onfocus=\"fillTime(form.heure_sll);\" 
            onchange=\"checkTime(form.heure_sll,'');\" $disabled style='width:60px;' maxlength='5' placeholder='hh:mm'>
            <font size=1><i>heure d'arrivée sur les lieux des secouristes</i></font></td>";
if ( $EL_IMPRIMER == 1 ) $checked="checked";
else $checked="";
echo "<td align=center><i class ='fa fa-print fa-lg' title=\"Cocher doit être imprimé dans le rapport\" ></i> </td>
      <td><input type='checkbox' name='imprimer' value='1' $checked $disabled
        title=\"Cocher doit être imprimé dans le rapport\" ></td>";
echo "</tr>";

echo "<tr bgcolor=$mylightcolor id='rowDateFin' $style>
            <td><i>Date Fin</i></td>
            <td>
            <input type='text' name='date_fin' size='10' maxlength='10' value='".$DATE_FIN."' placeholder='JJ-MM-AAAA' class='datepicker' data-provide='datepicker'
            onchange='checkDate2(form.date_fin)' $disabled>
          </td>
            <td><i>Heure Fin</i></td>
            <td>
            <input type='text' name='heure_fin' value='".$HEURE_FIN."' 
            onfocus=\"fillDate(form.date_fin); fillTime(form.heure_fin);\" 
            onchange=\"checkTime(form.heure_fin,'".$HEURE_FIN."');\" $disabled style='width:60px;' maxlength='5' placeholder='hh:mm'>
            </td>";        
echo "</tr>";

echo "<tr bgcolor=$mylightcolor >
            <td><i>".$cmt_tit."</i><br>
                <input type='text' name='comptage' size='4' value='$textsize' readonly title='nombre de caractères saisis'
                   style='FONT-SIZE: 10pt;border:0px; background:$mylightcolor; color:$mydarkcolor; font-weight:bold;'><br>
                <span class=small>3000 max</td>
            <td colspan=3>
            <textarea name='commentaire' cols='50' rows='8'
            style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;'
            value=\"$EL_COMMENTAIRE\" $disabled title=\"".$cmt_info."\"
            onFocus='CompterChar(this,3000,formulaire.comptage)' 
            onKeyDown='CompterChar(this,3000,formulaire.comptage)' 
            onKeyUp='CompterChar(this,3000,formulaire.comptage)' 
            onBlur='CompterChar(this,3000,formulaire.comptage)'
            >".$EL_COMMENTAIRE."</textarea>
          </td>";        
echo "</tr>";

$querym="select count(*) as NB from geolocalisation where TYPE='E' and CODE=".$evenement;
$resultm=mysqli_query($dbc,$querym);
$rowm=mysqli_fetch_array($resultm);
if ( $rowm["NB"] == 1 and $geolocalize_enabled==1) $map="<a href=sitac.php?evenement=".$evenement."><i class='fa fa-map fa-lg' style='color:green;' title='Voir la carte Google Maps' border=0></i></a>";
else $map="";

echo "<tr bgcolor=$mylightcolor id='rowAddress' $style>
            <td><i>Adresse intervention </i>
          <i class='fa fa-question-circle fa-lg' title=\"si l'adresse renseignée est correcte, alors l'intervention est marquée sur la carte\"></i></td>
            <td colspan=3><input type='text' name='address' size=40 value=\"".$EL_ADDRESS."\" $disabled> ".$map."</td>";        
echo "</tr>";

//=====================================================================
// équipes
//=====================================================================
$query2="select ee.EE_ID, ee.EE_NAME, ie.EL_ID, ee.EE_ID_RADIO
from evenement_equipe ee left join intervention_equipe ie on (ie.E_CODE=ee.E_CODE and ie.EE_ID = ee.EE_ID and ie.EL_ID=".intval($numinter).")
where ee.E_CODE=".$evenement." order by ee.EE_ORDER ";
$result2=mysqli_query($dbc,$query2);
$nb_equipes=mysqli_num_rows($result2);
$equipes_engagees=array();

if ( $nb_equipes > 0 ) {
    echo "<tr bgcolor=$mylightcolor id='rowEquipes' $style>
        <td><i>Equipes engagées</i></td>";
    echo "<td colspan=3>";
    $i=0;
    while (custom_fetch_array($result2)) {
        if ( $EL_ID > 0 ) {
            $checked = 'checked';
            array_push($equipes_engagees,$EE_ID);
        }
        else $checked = '';
        if ( $EE_ID_RADIO <> '' ) $radio =  " <span class=small2 style='color:green' title=\"identifiant radio ".$EE_ID_RADIO."\">".$EE_ID_RADIO."</span> ";
        else $radio = "";
        echo " <input type='checkbox' title=\"cocher si cette équipe ".$EE_NAME." participe à l'intervention\" value='1' id='eq_".$EE_ID."' name='eq_".$EE_ID."' $checked>
            <label for='eq_".$EE_ID."'>".$EE_NAME." ".$radio."</label>";

        $i++;
        if ( $i%4 == 0 ) echo "<br>";
    }
    echo "</td></tr>";
}

//=====================================================================
// responsable
//=====================================================================

echo "<tr bgcolor=$mylightcolor id='rowResponsable' $style>
    <td><i>Responsable</i></td>";
echo "<td colspan=3>";

$evts_not_canceled=get_event_and_renforts($evenement,true);


echo "<select name='responsable' id='responsable' $disabled style='font-size: 12px;'>";
echo "<option value='0' selected>............. Non défini .............</option>";
$query3="select distinct p.P_ID, p.P_NOM, p.P_PRENOM, ee.EE_ID, ee.EE_NAME, tp.TP_LIBELLE
    from pompier p, evenement_participation ep
    left join evenement_equipe ee on (ep.EE_ID = ee.EE_ID and ee.E_CODE = $evenement)
    left join type_participation tp on ep.TP_ID = tp.TP_ID
    where ep.P_ID = p.P_ID 
    and ep.EP_ABSENT=0
    and ep.E_CODE in (".$evts_not_canceled.")
    order by P_NOM, P_PRENOM";
$result3=mysqli_query($dbc,$query3);
while ($row3=@mysqli_fetch_array($result3)) {
    $_P_ID=$row3["P_ID"];
    $_P_NOM=strtoupper($row3["P_NOM"]);
    $_P_PRENOM=my_ucfirst($row3["P_PRENOM"]);
    $_ename=my_ucfirst($row3["EE_NAME"]);
    $_eid=$row3["EE_ID"];
    $_TP_LIBELLE=$row3["TP_LIBELLE"];
    if ( $_P_ID == $EL_RESPONSABLE ) $selected='selected';
    else $selected='';
    $details = '';
    if ( $_ename <> "" ) $details = $_ename;
    if ( $_TP_LIBELLE <> '' ) {
        if ( $details <> '' ) $details .= ' - ';
        $details .= " ".$_TP_LIBELLE;
    }
    if ( $details <> '' ) $details = "(".$details.")";
    if ( in_array($_eid, $equipes_engagees)) $class='class=selected';
    else $class='';    
    echo "<option value='$_P_ID' $selected $class>".$_P_NOM." ".$_P_PRENOM." ".$details."</option>";
}

echo "</select>";
echo "</td></tr>";

//=====================================================================
// victimes
//=====================================================================

if ( $TEL_CODE == 'I' and $action <> 'insert' ) {
     echo "<tr>
               <td colspan=4 class=TabHeader>Victimes ou Personnes prises en charge</td>
         </tr>";
     echo "<tr>";
     
    $query="select VI_ID, VI_NOM, VI_PRENOM, VI_SEXE, VI_ADDRESS, VI_COMMENTAIRE, VI_SEXE,
        VI_DETRESSE_VITALE, VI_INFORMATION, VI_SOINS, VI_MEDICALISE, VI_TRANSPORT, VI_VETEMENT, VI_ALIMENTATION, VI_TRAUMATISME, VI_DECEDE, VI_MALAISE, 
        victime.D_CODE, destination.D_NAME, transporteur.T_NAME, VI_NUMEROTATION, VI_REFUS, VI_IMPLIQUE,
        VI_AGE as age
        from victime, destination , transporteur
        where EL_ID=".$numinter."
        and destination.D_CODE=victime.D_CODE
        and transporteur.T_CODE=victime.T_CODE
        order by VI_NUMEROTATION,VI_NOM,VI_PRENOM";

    $result=mysqli_query($dbc,$query);

     while ( custom_fetch_array($result)) {
        $comments="";
        $VI_NOM=strtoupper($VI_NOM);
        $VI_PRENOM=my_ucfirst($VI_PRENOM);
        if ( $age <> '' ) $age.=" ans";
        if ( $VI_DETRESSE_VITALE == 1 ) $comments .= "<a title='Détresse vitale (Hémorragie, inconscience, ACR)' >détresse</a> ";
        if ( $VI_TRAUMATISME == 1 ) $comments .= "<a title='Traumatisme' >Traumatisme</a> ";
        if ( $VI_INFORMATION == 1 ) $comments .= "<a title='La personne a été assistée, ou des renseignements et informations lui ont été donnés' >assistée</a> ";
        if ( $VI_DECEDE == 1 ) $comments .= "<a title='La victime est décédée' >décédé</a> ";
        if ( $VI_MALAISE == 1 ) $comments .= "<a title='La victime eu un malaise avec ou sans perte de connaissance' >malaise</a> ";
        if ( $VI_SOINS == 1 ) $comments .= "<a title=\"Des soins ont été réalisés par l'équipe de secouristes\" >soins</a> ";
        if ( $VI_MEDICALISE == 1 ) $comments .= "<a title=\"La victime a été médicalisée\" >médicalisée</a> ";
        if ( $VI_TRANSPORT == 1 ) $comments .= "<a title=\"La victime a été transportée par ".$T_NAME.", destination: ".$D_NAME."\">transport</a> ";
        if ( $VI_VETEMENT == 1 ) $comments .= "<a title=\"Des vêtements ou une couverture ont été offerts à la victime\" >vêtements</a> ";
        if ( $VI_ALIMENTATION == 1 ) $comments .= "<a title=\"Des aliments ou une boisson ont été offerts à la victime\" >alimentation</a> ";
        if ( $VI_REFUS == 1 ) $comments .= "<a title=\"La victime a refusé d'être prise en charge\" >refus</a> ";
        if ( $VI_IMPLIQUE == 1 ) $comments .= "<a title=\"La personne est seulement impliquée, indemne\" >impliqué</a> ";
    
    echo "<tr bgcolor=$mylightcolor>
            <td> n° ".$VI_NUMEROTATION."</td>
            <td class=small>".$comments."</td>
            <td class=small width=50>".$VI_SEXE." ".$age."</td>    
            <td><a href='victimes.php?victime=".$VI_ID."&from=intervention' title=\"Cliquer pour voir la fiche de la personne prise en charge\">".$VI_PRENOM." ".$VI_NOM.".</a></td>";
    }

    echo "</tr>";
    if ( $granted_update ) {
        echo "<tr bgcolor=$mylightcolor id='rowAddVictime' $style>
            <td colspan=4 align=center><input type='button' class='btn btn-default' value='ajouter' title=\"Ajouter une victime ou personne prise en charge\" onclick='addVictime(".$numinter.");'></td>
          </td>";        
        echo "</tr>";
    }
}
if ( $action == 'update' ) {
    echo "<tr bgcolor=$mylightcolor>
        <td colspan=4 align=left class=small>Ajouté par ".$AUTHOR_PRENOM." ".$AUTHOR_NOM." - ".$DATE_ADD."</td>
    </tr>";
    if ( $UPDATER_NOM <> "")
    echo "<tr bgcolor=$mylightcolor>
        <td colspan=4 align=left class=small>Modifié par ".$UPDATER_PRENOM." ".$UPDATER_NOM." - ".$DATE_UPDATE."</td>
    </tr>";
}
echo "</table></div>";

echo "<input type=hidden name='type' value='".$TEL_CODE."'>";
echo "<input type=hidden name='numinter' value='".$numinter."'>";
echo "<input type=hidden name='action' value='".$action."'>";
echo "<input type=hidden name='evenement' value='".$evenement."'><p>";
echo "<table class='noBorder'><tr><td>";
if ( $granted_update ) {
    echo "<input type='submit' class='btn btn-default' value='sauver'>";
    if ( $numinter > 0 ) echo " <input type='button' class='btn btn-default' value='supprimer' onclick=\"deleteIt('".$numinter."','".$TEL_CODE."');\">";
}    
if ( $from == 'map' ) 
    echo " <input type='button' value='retour' class='btn btn-default'  title='Retour à la carte' onclick=\"javascript:history.back(1);\">";
else
    echo " <input type='button' value='retour' class='btn btn-default' onclick=\"redirect('".$evenement."');\">    ";
    
echo "</td></tr></table></div></form>";

writefoot();
?>

