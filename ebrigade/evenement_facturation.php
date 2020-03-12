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

?>
<script type='text/javascript' src='js/checkForm.js'></script>
</head>
<?php

$msgerr ="";
$evenement=(isset($_POST['evenement'])?intval($_POST['evenement']):(isset($_GET['evenement'])?intval($_GET['evenement']):0));

// le chef, le cadre de l'�v�nement ont toujours acc�s � cette fonctionnalit�, les autres doivent avoir 29 et/ou 24
if ( ! check_rights($id, 29, get_section_organisatrice($evenement)) and ! is_chef_evenement($id, $evenement)) {
    check_all(29);
    check_all(24);
}
$voircompta = true;

$evtType = "";
$evtTitre = "";
$evtLieu = "";
$evtDateDebut = "";
$evtDateFin = "";
$evtNB ="";
$evtOrga="";
$evtContact="";
$evtAdresse="";
$evtCP="";
$evtVille="";
$evtMobile="";
$evtTel="";
$evtFax="";
$evtEmail="";

$styleEvt="background-color:white;color:black;";
$factureStatut ="Aucun devis, facture, paiement";

$sqlevt = "SELECT e.*, eh.*, te.TE_LIBELLE, te.TE_ICON,
        date_format(eh.eh_date_debut,'%d-%m-%Y') dtdb, 
        date_format(eh.eh_date_fin,'%d-%m-%Y') dtfn 
        FROM evenement e, type_evenement te, evenement_horaire eh
        WHERE e.TE_CODE = te.TE_CODE
        and e.e_code=eh.e_code
        and e.E_CODE=$evenement";
$resevt=mysqli_query($dbc,$sqlevt);
$defaultDateHeure='';
$evtDuree=0;
$evtDureeTotale=0;
if($resevt){
    while($rowevt=mysqli_fetch_array($resevt)){
        $EH_ID=$rowevt['EH_ID'];
        if ( $EH_ID == 1 ) {
            $evtType = $rowevt['TE_CODE'];
            $evtIcon = $rowevt['TE_ICON'];
            $evtTypeLibelle = $rowevt['TE_LIBELLE'];
            $evtConvention = $rowevt['E_CONVENTION'];
            $evtTitre = $rowevt['E_LIBELLE'];
            $evtLieu = $rowevt['E_LIEU'];
            $evtNB = $rowevt['E_NB'];
            $evtSection = $rowevt['S_ID'];//." Section");
            $evtClosed = $rowevt['E_CLOSED'];//." Clotur� ");
            $evtCompany=$rowevt['C_ID'];
        }
        $evtDateDebut = $rowevt['dtdb'];
        $evtDateFin = $rowevt['dtfn'];
        $evt_hdtdb = substr($rowevt['EH_DEBUT'],0,5);
        $evt_hdtfn = substr($rowevt['EH_FIN'],0,5);
        $evtDuree= $rowevt['EH_DUREE'] + $evtDuree;
        $evtDureeTotale= $rowevt['E_NB'] * $rowevt['EH_DUREE'] + $evtDureeTotale;
        
        if ($evtDateDebut!=$evtDateFin) 
            $defaultDateHeure .= "du ".datesql2txt($evtDateDebut)." � ".$evt_hdtdb." au ".datesql2txt($evtDateFin)." � ".$evt_hdtfn.",\n";
        else 
            $defaultDateHeure .= "le ".datesql2txt($evtDateDebut)." de ".$evt_hdtdb." � ".$evt_hdtfn.",\n";

    }
    $evtDuree .= " Heures / intervenant";
    $defaultDateHeure = substr($defaultDateHeure,0,strlen($defaultDateHeure) -2);
    
    if ( $evtCompany <> '' ) {
        $queryC="select C_NAME, C_ADDRESS, C_ZIP_CODE, C_CITY, C_EMAIL, C_FAX, C_PHONE, C_CONTACT_NAME
                from company where C_ID=".$evtCompany;
        $resultC=mysqli_query($dbc,$queryC);
        $rowC=mysqli_fetch_array($resultC);
        $evtOrga=$rowC['C_NAME'];
        $evtAdresse=$rowC['C_ADDRESS'];
        $evtCP=$rowC['C_ZIP_CODE'];
        $evtVille=$rowC['C_CITY'];
        if (substr($rowC['C_PHONE'],0,2)=='06' ) $evtMobile=$rowC['C_PHONE'];
        else $evtTel=$rowC['C_PHONE'];
        $evtFax=$rowC['C_FAX'];
        $evtEmail=$rowC['C_EMAIL'];
        $evtContact=$rowC['C_CONTACT_NAME'];
    }
}


$devisLieu=(isset($_POST['devisLieu'])?secure_input($dbc,STR_replace("\"","",$_POST['devisLieu'])):"$evtLieu");
$devisDateHeure=(isset($_POST['devisDateHeure'])?secure_input($dbc,STR_replace("\"","",$_POST['devisDateHeure'])):"$defaultDateHeure");
$devisDate=(isset($_POST['devisDate'])?secure_input($dbc,$_POST['devisDate']):'');
if ( $devisDate <> '') {
    $tmp=explode ( "-",$devisDate); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
    $devisDate=$year.'-'.$month.'-'.$day;
}
$devisMontant=(isset($_POST['devisMontant'])?secure_input($dbc,STR_replace("\"","",$_POST['devisMontant'])):"0");
if ( $devisMontant == "" ) $devisMontant=0;
$devisAcompte=(isset($_POST['devisAcompte'])?secure_input($dbc,STR_replace("\"","",$_POST['devisAcompte'])):"0");
if ( $devisAcompte == "" ) $devisAcompte=0;
$devisNumero=(isset($_POST['devisNumero'])?secure_input($dbc,STR_replace("\"","",$_POST['devisNumero'])):"");
$devisCom=(isset($_POST['devisCom'])?secure_input($dbc,STR_replace("\"","",$_POST['devisCom'])):"");
$devisOrga=(isset($_POST['devisOrga'])?secure_input($dbc,STR_replace("\"","",$_POST['devisOrga'])):"$evtOrga");
$devisCivilite=(isset($_POST['devisCivilite'])?secure_input($dbc,STR_replace("\"","",$_POST['devisCivilite'])):"Madame, Monsieur");
$devisContact=(isset($_POST['devisContact'])?secure_input($dbc,STR_replace("\"","",$_POST['devisContact'])):"$evtContact");
$devisAdresse=(isset($_POST['devisAdresse'])?secure_input($dbc,STR_replace("\"","",$_POST['devisAdresse'])):"$evtAdresse");
$devisCP=(isset($_POST['devisCP'])?secure_input($dbc,STR_replace("\"","",$_POST['devisCP'])):"$evtCP");
$devisVille=(isset($_POST['devisVille'])?secure_input($dbc,STR_replace("\"","",$_POST['devisVille'])):"$evtVille");
$devisTel1=(isset($_POST['devisTel1'])?secure_input($dbc,STR_replace("\"","",$_POST['devisTel1'])):"$evtMobile");
$devisTel2=(isset($_POST['devisTel2'])?secure_input($dbc,STR_replace("\"","",$_POST['devisTel2'])):"$evtTel");
$devisFax=(isset($_POST['devisFax'])?secure_input($dbc,STR_replace("\"","",$_POST['devisFax'])):"$evtFax");
$devisEmail=(isset($_POST['devisEmail'])?secure_input($dbc,STR_replace("\"","",$_POST['devisEmail'])):"$evtEmail");
$devisURL=(isset($_POST['devisURL'])?secure_input($dbc,STR_replace("\"","",$_POST['devisURL'])):"");
$devisAccepte=(isset($_POST['devisAccepte'])?secure_input($dbc,$_POST['devisAccepte']):"0");

$factLieu=(isset($_POST['factLieu'])?secure_input($dbc,STR_replace("\"","",$_POST['factLieu'])):'');
$factDateHeure=(isset($_POST['factDateHeure'])?secure_input($dbc,STR_replace("\"","",$_POST['factDateHeure'])):'');
$factDate=(isset($_POST['factDate'])?secure_input($dbc,STR_replace("\"","",$_POST['factDate'])):'');
if ( $factDate <> '' ) {
    $tmp=explode ( "-",$factDate); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
    $factDate=$year.'-'.$month.'-'.$day;
}
$factNumero=(isset($_POST['factNumero'])?secure_input($dbc,STR_replace("\"","",$_POST['factNumero'])):"");
$factMontant=(isset($_POST['factMontant'])?secure_input($dbc,STR_replace("\"","",$_POST['factMontant'])):"");
if ( $factMontant == "" ) $factMontant=0;
$factAcompte=(isset($_POST['factAcompte'])?secure_input($dbc,STR_replace("\"","",$_POST['factAcompte'])):"0");
if ( $factAcompte == "" ) $factAcompte=0;
$factCom=(isset($_POST['factCom'])?secure_input($dbc,STR_replace("\"","",$_POST['factCom'])):"");
$factOrga=(isset($_POST['factOrga'])?secure_input($dbc,STR_replace("\"","",$_POST['factOrga'])):"");
$factCivilite=(isset($_POST['factCivilite'])?secure_input($dbc,STR_replace("\"","",$_POST['factCivilite'])):"Madame, Monsieur");
$factContact=(isset($_POST['factContact'])?secure_input($dbc,STR_replace("\"","",$_POST['factContact'])):"");
$factAdresse=(isset($_POST['factAdresse'])?secure_input($dbc,STR_replace("\"","",$_POST['factAdresse'])):"");
$factCP=(isset($_POST['factCP'])?secure_input($dbc,STR_replace("\"","",$_POST['factCP'])):"");
$factVille=(isset($_POST['factVille'])?secure_input($dbc,STR_replace("\"","",$_POST['factVille'])):"");
$factTel1=(isset($_POST['factTel1'])?secure_input($dbc,STR_replace("\"","",$_POST['factTel1'])):"");
$factTel2=(isset($_POST['factTel2'])?secure_input($dbc,STR_replace("\"","",$_POST['factTel2'])):"");
$factFax=(isset($_POST['factFax'])?secure_input($dbc,STR_replace("\"","",$_POST['factFax'])):"");
$factEmail=(isset($_POST['factEmail'])?secure_input($dbc,STR_replace("\"","",$_POST['factEmail'])):"");

$relanceDate=(isset($_POST['relanceDate'])?secure_input($dbc,STR_replace("\"","",$_POST['relanceDate'])):'');
if ( $relanceDate <> '') {
    $tmp=explode ( "-",$relanceDate); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
    $relanceDate=$year.'-'.$month.'-'.$day;
}
$relanceNum=(isset($_POST['relanceNum'])?intval($_POST['relanceNum']):"0");
$relanceCom=(isset($_POST['relanceCom'])?secure_input($dbc,STR_replace("\"","",$_POST['relanceCom'])):"");
$paiementDate=(isset($_POST['paiementDate'])?$_POST['paiementDate']:'');
if ( $paiementDate <> '' && $paiementDate <> '00-00-0000') {
    $tmp=explode ( "-",$paiementDate); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
    $paiementDate=$year.'-'.$month.'-'.$day;
}
$paiementCom=(isset($_POST['paiementCom'])?secure_input($dbc,STR_replace("\"","",$_POST['paiementCom'])):"");
$frmaction="Cr�er";
// echo "<pre>";
// print_r($_POST);
// echo "</pre>"; 


// GESTION AJOUT / MODIFICATION
if (isset($_POST['frmaction'])){
    //echo "<br />D $devisDate - F $factDate - R $relanceDate - P $paiementDate";
    switch($_POST['frmaction']){
    case 'Modifier':
      if ($_POST['tab'] == 1 ) 
        $sql = "update evenement_facturation SET
devis_lieu= ".("$devisLieu"<>''?"\"".$devisLieu."\"":'NULL' )."
,devis_date_heure= ".("$devisDateHeure"<>''?"\"".$devisDateHeure."\"":'NULL' )."
,devis_Date = ".($devisDate<>''?"'".$devisDate."'":'NULL')."
,devis_montant = \"$devisMontant\"
,devis_acompte = ".$devisAcompte."
,devis_numero = \"$devisNumero\"
,devis_comment = \"$devisCom\"
,devis_orga = \"$devisOrga\"
,devis_civilite = \"$devisCivilite\"
,devis_contact = \"$devisContact\"
,devis_adresse = \"$devisAdresse\"
,devis_cp = \"$devisCP\"
,devis_ville = \"$devisVille\"
,devis_tel1 = \"$devisTel1\"
,devis_tel2 = \"$devisTel2\"
,devis_fax = \"$devisFax\"
,devis_email = \"$devisEmail\"
,devis_url = \"$devisURL\"
,devis_accepte = \"$devisAccepte\"
where E_ID='$evenement'";
    if ($_POST['tab'] == 2 ) 
      $sql = "update evenement_facturation SET
facture_lieu= ".("$factLieu"<>''?"\"".$factLieu."\"":'NULL' )."
,facture_date_heure= ".("$factDateHeure"<>''?"\"".$factDateHeure."\"":'NULL' )."
,facture_Date = ".($factDate<>''?"\"".$factDate."\"":'NULL')."
,facture_numero = \"$factNumero\"
,facture_montant = \"$factMontant\"
,facture_acompte = ".$factAcompte."
,facture_comment = \"$factCom\"
,facture_orga = \"$factOrga\"
,facture_civilite = \"$factCivilite\"
,facture_contact = \"$factContact\"
,facture_adresse = \"$factAdresse\"
,facture_cp = \"$factCP\"
,facture_ville = \"$factVille\"
,facture_tel1 = \"$factTel1\"
,facture_tel2 = \"$factTel2\"
,facture_fax = \"$factFax\"
,facture_email = \"$factEmail\"
where E_ID='$evenement'";
    if ($_POST['tab'] == 3 ) 
      $sql = "update evenement_facturation SET
relance_Date = ".($relanceDate<>''?"\"".$relanceDate."\"":'NULL')."
,relance_num = \"$relanceNum\"
,relance_comment = \"$relanceCom\"
where E_ID='$evenement'";
    if ($_POST['tab'] == 4 ) 
      $sql = "update evenement_facturation SET
paiement_date = ".($paiementDate<>''?"\"".$paiementDate."\"":'NULL')."
,paiement_comment = \"$paiementCom\"
where E_ID='$evenement'";

$query2="select devis_montant from evenement_facturation where E_ID='$evenement'";
$res2=mysqli_query($dbc,$query2);
$row2=mysqli_fetch_array($res2);
$prev_montant=$row2[0];

if ( $prev_montant <> $devisMontant and intval($devisMontant) > 0 ) {
    // notifier, le devis est cr��, ensuite le montant
    
    $destid=get_granted(35,"$evtSection",'parent','yes');            
    $subject  = "Devis �mis pour ".$evtTitre;
    $message  = "Bonjour,\n";
    $message .= "Un devis a �t� �mis pour l'�v�nement: ".$evtTitre."\n";
    $message .= "pour un montant total de : ".$devisMontant." ".$default_money_symbol."\n";
    $message .= "organis� par: ".get_section_code($evtSection)."\n";
    $message .= "lieu: ".$devisLieu."\n";
    $message .= "dates et heures: ".$devisDateHeure."\n";
        
    $nb = mysendmail("$destid" , $_SESSION['id'] , "$subject" , "$message" );

}

    break;
    case 'Cr�er':
        $sql = "INSERT into evenement_facturation(e_id,devis_lieu,devis_date_heure,
devis_Date,devis_numero,devis_Montant,devis_acompte,devis_comment,devis_Orga,devis_Civilite,devis_Contact,devis_Adresse,devis_CP,devis_Ville,devis_Tel1,devis_Tel2,devis_Fax,devis_Email,devis_URL,devis_accepte,
facture_lieu,facture_date_heure,facture_Date,facture_numero,facture_Montant,facture_acompte,facture_comment,facture_Orga,facture_Civilite,facture_Contact,facture_Adresse,facture_CP,facture_Ville,facture_Tel1,facture_Tel2,facture_Fax,facture_Email,
relance_Date,relance_num,relance_comment,
paiement_Date,paiement_comment
) VALUES('$evenement',".("$devisLieu"<>''?"\"".$devisLieu."\"":'NULL' ).",".("$devisDateHeure"<>''?"\"".$devisDateHeure."\"":'NULL' ).",
".($devisDate<>''?"\"".$devisDate."\"":'NULL').",\"$devisNumero\",\"$devisMontant\",".$devisAcompte.",\"$devisCom\",\"$devisOrga\",\"$devisCivilite\",\"$devisContact\",\"$devisAdresse\",
\"$devisCP\",\"$devisVille\",\"$devisTel1\",\"$devisTel2\",\"$devisFax\",\"$devisEmail\",\"$devisURL\",\"$devisAccepte\",
".("$factLieu"<>''?"\"".$factLieu."\"":'NULL' ).",".("$factDateHeure"<>''?"\"".$factDateHeure."\"":'NULL' ).",".($factDate<>''?"\"".$factDate."\"":'NULL').",\"$factNumero\",\"$factMontant\",
".$factAcompte.",\"$factCom\",\"$factOrga\",\"$factCivilite\",\"$factContact\",\"$factAdresse\",\"$factCP\",\"$factVille\",\"$factTel1\",\"$factTel2\",\"$factFax\",\"$factEmail\",
".($relanceDate<>''?"\"".$relanceDate."\"":'NULL').",\"$relanceNum\",\"$relanceCom\",
".($paiementDate<>''?"\"".$paiementDate."\"":'NULL').",\"$paiementCom\"
)";

    break;
        
    case 'CopierDevis':
        $sql="delete from evenement_facturation_detail 
    where e_id = '$evenement'
    AND ef_type='facture'";
        $res = mysqli_query($dbc,$sql);
        
        $sqldetail="insert into evenement_facturation_detail(e_id,ef_lig,ef_type,ef_txt,ef_qte,ef_pu,ef_rem,ef_frais)
(select e_id,ef_lig,'facture',ef_txt,ef_qte,ef_pu,ef_rem,ef_frais from evenement_facturation_detail 
where e_id = '$evenement'
and ef_type='devis'
)";
        $res = mysqli_query($dbc,$sqldetail);
        // calcul le montant � facturer selon dle d�tail
        $sqlcalc="select * from evenement_facturation_detail 
where e_id = '$evenement'
and ef_type='facture'";
        $res = mysqli_query($dbc,$sqlcalc);
        $out="";
        $num=0;
        $TotalDoc=0;
        while($rowcalc=mysqli_fetch_array($res)){
            $num++;
            $TotalLigne = ($rowcalc['ef_qte']*$rowcalc['ef_pu']*(1-($rowcalc['ef_rem']/100)));    
            $TotalDoc += $TotalLigne;
        }
        if ( $TotalDoc <> 0 ) $value= $TotalDoc;
        else $value = "devis_montant";
        $sql = "UPDATE evenement_facturation 
SET facture_montant = ".$value.",
facture_acompte = devis_acompte,
facture_date = now()
WHERE E_ID='$evenement'";
        break;
    default:
    }
    $res = mysqli_query($dbc,$sql);
}// Fin Action

$sqlfact = "SELECT * FROM evenement_facturation WHERE E_ID=$evenement";
$resfact=mysqli_query($dbc,$sqlfact);
echo (mysqli_errno($dbc)>0?mysqli_error($dbc):'');
if($resfact){
    while($rowfact=mysqli_fetch_array($resfact)){
        // DEVIS
        $devisLieu=$rowfact['devis_lieu'];
        if ( $devisLieu=='') $devisLieu = $evtLieu;
        $devisDateHeure=$rowfact['devis_date_heure'];
        if ( $devisDateHeure =='') {
            $devisDateHeure=$defaultDateHeure;
        }
        $devisDate=$rowfact['devis_date'];
        $devisNumero=$rowfact['devis_numero'];
        if ( $devisNumero == '' ) $devisNumero= $evenement;
        $devisAccepte=$rowfact['devis_accepte'];
        if($devisDate!=""){
            $tmp=explode ( "-",$devisDate); $year=$tmp[0]; $month=$tmp[1]; $day=$tmp[2];
            $devisDate=$day.'-'.$month.'-'.$year;
            if(checkdate($month,$day,$year)){
                $factureStatut = "Devis transmis le $devisDate";
                $styleEvt=(($devisAccepte==0)?"background-color:grey;color:white;":"background-color:green;color:white;");
            }else{
                $devisDate="";
            }
        }else{
            $devisDate="";
        }
        $devisMontant=$rowfact['devis_montant'];
        $devisAcompte=$rowfact['devis_acompte'];
        $devisCom=$rowfact['devis_comment'];
        $devisOrga=$rowfact['devis_orga'];
        $devisCivilite=$rowfact['devis_civilite'];
        $devisContact=$rowfact['devis_contact'];
        $devisAdresse=$rowfact['devis_adresse'];
        $devisCP=$rowfact['devis_cp'];
        $devisVille=$rowfact['devis_ville'];
        $devisTel1=$rowfact['devis_tel1'];
        $devisTel2=$rowfact['devis_tel2'];
        $devisFax=$rowfact['devis_fax'];
        $devisEmail=$rowfact['devis_email'];
        $devisURL=$rowfact['devis_url'];

        // FACTURE
        $factLieu=$rowfact['facture_lieu'];
        if ( $factLieu=='') $factLieu = $evtLieu;
        $factDateHeure=$rowfact['facture_date_heure'];
        if ( $factDateHeure =='') {
            $factDateHeure=$defaultDateHeure;
        }
        $factDate=$rowfact['facture_date'];
        if($factDate!=""){
            $tmp=explode ( "-",$factDate); $year=$tmp[0]; $month=$tmp[1]; $day=$tmp[2];
            $factDate=$day.'-'.$month.'-'.$year;
            if(checkdate($month,$day,$year)){
                $factureStatut = "Facture �mise le $factDate";
                $styleEvt="background-color:orange;color:black;";
            }else{
                $factDate="";
            }    
        }else{
            $factDate="";
        }
        $factNumero=$rowfact['facture_numero'];
        if ( $factNumero == '' ) $factNumero= $evenement;
        $factMontant=$rowfact['facture_montant'];
        $factAcompte=$rowfact['facture_acompte'];
        $factOrga=$rowfact['facture_orga'];
        $factCivilite=$rowfact['facture_civilite'];
        $factContact=$rowfact['facture_contact'];
        $factAdresse=$rowfact['facture_adresse'];
        $factCP=$rowfact['facture_cp'];
        $factVille=$rowfact['facture_ville'];
        $factTel1=$rowfact['facture_tel1'];
        $factTel2=$rowfact['facture_tel2'];
        $factFax=$rowfact['facture_fax'];
        $factEmail=$rowfact['facture_email'];
        $factCom=$rowfact['facture_comment'];

        // RELANCE
        $relanceDate=$rowfact['relance_date'];
        if($relanceDate!=""){
            $tmp=explode ( "-",$relanceDate); $year=$tmp[0]; $month=$tmp[1]; $day=$tmp[2];
            $relanceDate=$day.'-'.$month.'-'.$year;
            if(checkdate($month,$day,$year)){
                $factureStatut = "Relance en date du $relanceDate...";
                $styleEvt="background-color:red;color:white;";
            }else{
                $relanceDate="";
            }    
        }else{
            $relanceDate="";
        }

        $relanceNum=$rowfact['relance_num'];
        $relanceCom=$rowfact['relance_comment'];

        $paiementDate=$rowfact['paiement_date'];
        if($paiementDate!=""){
            $tmp=explode ( "-",$paiementDate); $year=$tmp[0]; $month=$tmp[1]; $day=$tmp[2];
            $paiementDate=$day.'-'.$month.'-'.$year;
            if(checkdate($month,$day,$year)){
                $factureStatut = "Paiement enregistr�...";
                $styleEvt="background-color:white;color:grey;";
            }else{
                $paiementDate="";
            }    
        }else{
            $paiementDate="";
        }

        $paiementCom=$rowfact['paiement_comment'];

        $frmaction="Modifier";
    }
}
else
    $msgerr .= "Pas de facturation en cours...";

?>
<script type="text/javascript">
$(document).ready(function() {    
    $("input#factNumero").keyup(function(){
        var trouve;
        trouve = $("input#factNumero").val();
        $.post("evenement_facturation_num.php",{trouve:trouve,section:<?php echo $evtSection; ?>,evenement:<?php echo $evenement; ?>},    
        function (data){        
            $("#infoNum").empty();
            $("#infoNum").append(data);
        });
    });
});
function RecupAdresse(input){
    if (input.checked==false){
        if(confirm("Voulez-vous effacer ces informations?")==true){
             $("#factLieu").val("");
             $("#factDateHeure").val("");
            $("#factOrga").val("");
            $("#factCivilite").val("");
            $("#factContact").val("");
            $("#factAdresse").val("");
            $("#factCP").val("");
            $("#factVille").val("");
            $("#factTel1").val("");
            $("#factTel2").val("");
            $("#factFax").val("");
            $("#factEmail").val("");
        }
    }else{
        $("#factLieu").val($("#devisLieu").val());
        $("#factDateHeure").val($("#devisDateHeure").val());
        $("#factOrga").val($("#devisOrga").val());
        $("#factCivilite").val($("#devisCivilite").val());
        $("#factContact").val($("#devisContact").val());
        $("#factAdresse").val($("#devisAdresse").val());
        $("#factCP").val($("#devisCP").val());
        $("#factVille").val($("#devisVille").val());
        $("#factTel1").val($("#devisTel1").val());
        $("#factTel2").val($("#devisTel2").val());
        $("#factFax").val($("#devisFax").val());
        $("#factEmail").val($("#devisEmail").val());
    }
}
function CopierDevis(){
    $("#frmaction").val("CopierDevis");
    $("form").submit();
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

</script>
<style type="text/css">
form label{
float:left;
clear:left;
width:140px;
    text-align:right;
    padding-right:1em;
}
#intro{
display:block;
width:100%;
<?php echo $styleEvt; ?>
}
#frmaction{
clear:both;
width:100%;
    border-top:1px solid black;
}
#frmaction input{
margin:auto;
}
div#resultat{
    text-align:left;
}
#factNumero{
    font-weight:bold;
}
#devisNumero{
    font-weight:bold;
}
#infoNum{
clear:both;
color:red;
font-weight:bold;
margin-left:200px;
}
</style>
<?php

// fonction pour afficher les boutons sauver, retour, imprimer, d�tail
function Buttons($tabn = 'null'){
    global $evtIcon,$devisDate,$factDate,$relanceDate,$paiementDate,$evenement;
    echo "<p><table class='noBorder'><tr class='bigFont'><td><input type='button' class='btn btn-default' id='retour' value='retour' 
           onclick='bouton_redirect(\"evenement_display.php?from=facturation&evenement=$evenement\");'> </td>
          <td><input type='submit' class='btn btn-default' id='btaction".$tabn."' value=sauver></td> ";
    echo " <td>";
    $url="evenement_modal.php?action=facturation&evenement=".$evenement;
    print write_modal( $url, "facturation_".$evenement, "<input type='submit' class='btn btn-default' id='btaction".$tabn."' value='d�tail' title=\"voir le d�tail de l'�v�nement\">");
    echo "</td>";
    if ( $tabn == 'facture' && $factDate <> ""){
        echo " <td><a href='pdf.php?id=".$evenement."&pdf=facture' target='_blank'><i class='fa fa-print fa-lg' title='Imprimer la facture au format pdf' ></i></a></td>";
    }
    if( $tabn == 'devis' && $devisDate <>""){
        echo " <td><a href='pdf.php?id=".$evenement."&pdf=devis' target='_blank'><i class='fa fa-print fa-lg' title='Imprimer au format pdf'></i></a></td>";
    }
    if( $tabn == 'relance' && $relanceDate <>""){
        echo " <td><a href='pdf.php?id=".$evenement."&pdf=relance' target='_blank'><i class='fa fa-print fa-lg' title='Imprimer au format pdf'></i></a></td>";
    }
    if( $tabn == 'paiement' && $paiementDate <>""){
        echo " <td><a href='pdf.php?id=".$evenement."&pdf=facturepayee' target='_blank'><i class='fa fa-print fa-lg' title='Imprimer la facture acquit�e au format pdf'></i></a></td>";
    }
    echo "</tr></table>";
}
//================================================
// EN TETE
//================================================

if ( isset($_GET['status']) ) $status=$_GET['status'];
else $status=get_etat_facturation($evenement,"code");
if ( isset ($_POST['frmaction']))
  if ( $_POST['frmaction'] =='CopierDevis') $status ='facture';

$etatfacturation=get_etat_facturation($evenement,"txt");
$cssfacturation=get_etat_facturation($evenement,"css");

echo "<div >
        <table class='noBorder'>
        <tr>
          <td rowspan=2 width=60><i class='far fa-money-bill-alt fa-3x'></i></td>
          <td ><font size=4><b>".$evtTitre."</b></font>
        </td></tr>
        <tr>
          <td>".$evtTypeLibelle." ".$evtLieu." - ".$evtDateDebut."</td>
        </tr></table>
        ";

echo "<form name='frmGesCom' method='post'>";

echo "<span style=\"color:red;width:100%;clear:both;\">$msgerr</span>"; 

if ( isset($_GET["tab"])) $tab=intval($_GET["tab"]);
else if ( $status == 'devis') $tab = 1;
else if ( $status == 'facture' ) $tab = 2;
else if ( $status == 'relance' ) $tab = 3;
else if ( $status == 'paiement' ) $tab = 4;
else $tab = 1;
if ( intval($tab) == 0 ) $tab = 1;

echo  "<p><ul class='nav nav-tabs  noprint' id='myTab' role='tablist'>";
// DEVIS
if ( $tab == 1 ) {
    $class='active';
    if ( $devisAccepte == 1 ) $color="green";
    else $color="#5A5A5A";
}
else {
    $color='#27537a';
    $class='';
}
if ( $devisAccepte == 1 ) $cmt = "devis du ".$devisDate." (accept�)";
else if ( $devisDate <> '' ) $cmt = "devis du ".$devisDate;
else $cmt="devis";

echo "<li class='nav-item'>
    <a class='nav-link $class' href='evenement_facturation.php?evenement=".$evenement."&tab=1' title='Devis' role='tab' aria-controls='tab1' href='#tab1' >
    <span style='color:$color'>".$cmt."</span></a></li>";


// FACTURE
if ( $tab == 2 ) {
    $class='active';
    $color='orange';
}
else {
    $color='#27537a';
    $class='';
}
if ( $factDate <> '' ) $cmt = "facture �mise le ".$factDate;
else $cmt="facture";

echo "<li class='nav-item'>
    <a class='nav-link $class' href='evenement_facturation.php?evenement=".$evenement."&tab=2' title='Facture' role='tab' aria-controls='tab2' href='#tab2' >
    <span style='color:$color'>".$cmt."</span></a></li>";


// RELANCE
if ( $tab == 3 ) {
    $class='active';
    $color='red';
}
else {
    $color='#27537a'; 
    $class='';
}
if ( $relanceDate <> '' ) $cmt = "Relance le ".$relanceDate;
else $cmt="Relance";

echo "<li class='nav-item'>
    <a class='nav-link $class' href='evenement_facturation.php?evenement=".$evenement."&tab=3' title='Relance impay�' role='tab' aria-controls='tab3' href='#tab3' >
    <span style='color:$color'>".$cmt."</span></a></li>";

// PAIEMENT
if ( $tab == 4 ) {
    $class='active';
    $color='black';
}
else {
    $color='#27537a';
    $class='';
}
if ( $paiementDate <> '' ) $cmt= "Paiement le ".$paiementDate;
else $cmt="Paiement";

echo "<li class='nav-item'>
    <a class='nav-link $class' href='evenement_facturation.php?evenement=".$evenement."&tab=4' title='Enregistrer le paiement' role='tab' aria-controls='tab4' href='#tab4' >
    <span style='color:$color'>".$cmt."</span></a></li>";

echo "</ul><p>";// fin tabs

//================================================
// DEVIS
//================================================
if ( $tab == 1 ) {
    echo "<div id='devis'>";
    echo  "<table cellspacing=0>
            <tr class='MenuRub'><td colspan=2>Devis</td></tr>";
    if ( $devisDate == '' ) $warn=" <i class='fa fa-exclamation-triangle' style='color:orange; padding-left:5px;' title='saisissez la date et sauvez'></i>";
    else $warn="";
    echo "<tr bgcolor=$mylightcolor><td><label for='devisDate' title='JJ-MM-AAAA'><b>Date du devis</label></b></td><td>
             <input type='text' name='devisDate' id='devisDate' placeholder='JJ-MM-AAAA' value=\"".$devisDate."\" 
             onfocus='fillDate(frmGesCom.devisDate);'
             onchange='checkDate2(frmGesCom.devisDate);'> $warn</td></tr>";
    $queryF="select count(1) as NB from evenement_facturation
            where e_id='$evenement'";
    $resF = mysqli_query($dbc,$queryF);
    $rowF=mysqli_fetch_array($resF);

    $query="select count(1) as NB from evenement_facturation_detail
            where e_id='$evenement'
            and ef_type='devis'";
    $res = mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($res);
    if ( $row['NB'] > 0 ) $disabled='disabled';
    else $disabled='';
    if ( $rowF['NB'] > 0 ) {
        echo "<tr bgcolor=$mylightcolor><td><label for='devisMontant'><b>Montant</b></label></td><td>
             <input type='hidden' name='devisMontant' id='devisMontant' value=\"".$devisMontant."\">
             <input type='text' name='devisMontant' id='devisMontant' value=\"".round($devisMontant,2)."\" $disabled>";
        echo " <a href='evenement_facturation_detail.php?evenement=".$evenement."&type=devis'>D�tail</a></td></tr>";
        echo "<tr bgcolor=$mylightcolor><td><label for='devisAcompte'><b>Acompte demand�</b></label></td><td>
          <input type='text' name='devisAcompte' id='devisAcompte' value=\"".round($devisAcompte,2)."\" 
          title=\"Si un acompte doit �tre vers� � l'acceptation du devis, saisir le montant ici\"></td></tr>";
    }
    echo "
          <tr bgcolor=$mylightcolor><td><label for='devisNumero'><b>Devis Num�ro</b></label></td><td>
             <input type='text' name='devisNumero' id='devisNumero' value=\"".$devisNumero."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='devisCom'>Commentaire</label></td><td>
             <textarea name='devisCom' id='devisCom' cols='40' rows='3'
             style='font-size:10pt; font-family:Arial;'>".$devisCom."</textarea></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='efLieu'><b>Lieu</b></label></td><td>
             <input type='text' name='devisLieu' id='devisLieu' size='35' maxlength='50' value=\"".$devisLieu."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='devisDateHeure'><b>Dates, heures</b></label></td><td>
             <textarea name='devisDateHeure' id='devisDateHeure' cols='40' rows='5'
             style='font-size:10pt; font-family:Arial;'>".$devisDateHeure."</textarea></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='devisOrga'><b>Organisme demandeur</b></label></td><td>
             <input type='text' name='devisOrga' id='devisOrga'  size='35' value=\"".$devisOrga."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='devisContact'>Civilit�</label></td><td>
             <input type='text' name='devisCivilite' id='devisCivilite' value=\"".$devisCivilite."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='devisContact'>Contact</label></td><td>
             <input type='text' name='devisContact' size='35' id='devisContact' value=\"".$devisContact."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='devisAdresse'>Adresse</label></td><td>
             <textarea name='devisAdresse' id='devisAdresse' cols='40' rows='3'
             style='font-size:10pt; font-family:Arial;'>".$devisAdresse."</textarea></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='devisCP'>CP</label></td><td>
             <input type='text' name='devisCP' id='devisCP' value=\"".$devisCP."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='devisVille'>Ville</label></td><td>
             <input type='text' name='devisVille' id='devisVille' value=\"".$devisVille."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='devisTel1'>T�l mobile</label></td><td>
             <input type='text' name='devisTel1' id='devisTel1' value=\"".$devisTel1."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='devisTel2'>T�l fixe</label></td><td>
             <input type='text' name='devisTel2' id='devisTel2' value=\"".$devisTel2."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='devisFax'>Fax</label></td><td>
             <input type='text' name='devisFax' id='devisFax' value=\"".$devisFax."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='devisEmail'>Email</label></td><td>
             <input type='text' name='devisEmail' id='devisEmail' value=\"".$devisEmail."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='devisURL'>Site internet</label></td><td>
             <input type='text' name='devisURL' id='devisURL' value=\"".$devisURL."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='devisAccepte'>Devis accept�</label></td><td>
            <select name='devisAccepte' id='devisAccepte'>
            <option value='0' ".($devisAccepte==0?' selected':'').">Non</option>
            <option value='1' ".($devisAccepte==1?' selected':'').">Oui</option>
            </select>
        </td></tr></table>";
    echo Buttons('devis');
    echo "</div>";
}
//================================================
// FACTURE
//================================================
if ( $tab == 2 ) {
    echo "<div id='facture'>";
    if ($evtClosed==0) 
        echo  " <i class='fa fa-exclamation-triangle' style='color:orange;'></i> Attention, cet �v�nement n'est pas cl�tur� !!! Il faut fermer les inscriptions";
    echo  "<table cellspacing=0>
            <tr class='MenuRub'><td colspan=2>Facture</td></tr>";
    if ( $factMontant==$devisMontant ) $cmt='';
    else $cmt="Devis = ".$devisMontant;
    if ( $factDate == '' ) $warn=" <i class='fa fa-exclamation-triangle' style='color:orange; padding-left:5px;' title='saisissez la date et sauvez'></i>";
    else $warn="";
    echo "<tr bgcolor=$mylightcolor><td><label for='factDate'>Date de facturation </label></td><td>
             <input type='text' name='factDate' id='factDate' value=\"".$factDate."\" placeholder='JJ-MM-AAAA' 
             onfocus='fillDate(frmGesCom.factDate);'
             onchange='checkDate2(frmGesCom.factDate);'> $warn</p>";
    $query="select count(1) as NB from evenement_facturation_detail
            where e_id='$evenement'
            and ef_type='facture'";
    $res = mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($res);
    if ( $row['NB'] > 0 ) $disabled='disabled';
    else $disabled='';
    if ("$factMontant"=='') $factMontant=$devisMontant;
    echo "<tr bgcolor=$mylightcolor><td><input type='hidden' name='factMontant' id='factMontant' value=\"".$factMontant."\">
             <label for='factMontant'><b>Montant prestation</b></label></td><td>
             <input type='text' name='factMontant' id='factMontant' value=\"".round($factMontant,2)."\" $disabled> ";
        echo " <a href='evenement_facturation_detail.php?evenement=".$evenement."&type=facture'>D�tail</a> </td></tr>";
        if($factMontant=="0" && $devisMontant<>"0"){ 
            echo "<tr bgcolor=$mylightcolor><td><label for='CopieDevis'><i>Copier le montant du devis</i></label></td><td>
             <input type='checkbox' name='CopieDevis' id='CopieDevis' onclick='CopierDevis();'></td></tr>";
        }
        echo "<tr bgcolor=$mylightcolor><td><label for='factAcompte'><b>Acompte d�j� vers�</b></label></td><td>
          <input type='text' name='factAcompte' id='factAcompte' value=\"".round($factAcompte,2)."\"  title=\"Si un acompte a d�j� �t� vers�, saisir le montant ici\"></td></tr>";
          
        if ( ("$factContact"=="$devisContact") and ("$factOrga" == "$devisOrga") and ("$factAdresse" == "$devisAdresse") ) 
            $checked ='checked';
        else 
            $checked='';
        echo "<tr bgcolor=$mylightcolor><td><label for='factNumero'><b>Facture Num�ro</b></label></td><td>
             <input type='text' name='factNumero' id='factNumero' value=\"".$factNumero."\"><div id='infoNum'></div></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='factCom'>Commentaire</label></td><td>
             <textarea name='factCom' id='factCom' cols='40' rows='3'
             style='font-size:10pt; font-family:Arial;'>".$factCom."</textarea></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='factIdem'><i>Identique devis</i></label></td><td>
             <input type='checkbox' name='factIdem' id='factIdem' onclick='javascript:RecupAdresse(this);' $checked></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='factLieu'><b>Lieu</b></label></td><td>
             <input type='text' name='factLieu' id='factLieu' size='35' maxlength='50' value=\"".$factLieu."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='factDateHeure'><b>Dates, heures</b></label></td><td>
             <textarea name='factDateHeure' id='factDateHeure' cols='40' rows='5'
             style='font-size:10pt; font-family:Arial;'>".$factDateHeure."</textarea></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='factOrga'><b>Organisme payeur</b></label></td><td>
             <input type='text' name='factOrga' id='factOrga'  size='35' value=\"".$factOrga."\" ></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='factContact'>Civilit�</label></td><td>
             <input type='text' name='factCivilite' id='factCivilite' value=\"".$factCivilite."\" ></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='factContact' size='35'>Contact</label></td><td>
             <input type='text' name='factContact' id='factContact' value=\"".$factContact."\" ></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='factAdresse'>Adresse</label></td><td>
             <textarea name='factAdresse' id='factAdresse' cols='40' rows='3'
             style='font-size:10pt; font-family:Arial;'>".$factAdresse."</textarea></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='factCP'>CP</label></td><td>
             <input type='text' name='factCP' id='factCP' value=\"".$factCP."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='factVille'>Ville</label></td><td>
             <input type='text' name='factVille' id='factVille' value=\"".$factVille."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='factTel1'>T�l mobile</label></td><td>
             <input type='text' name='factTel1' id='factTel1' value=\"".$factTel1."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='factTel2'>T�l fixe</label></td><td>
             <input type='text' name='factTel2' id='factTel2' value=\"".$factTel2."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='factFax'>Fax</label></td><td>
             <input type='text' name='factFax' id='factFax' value=\"".$factFax."\"></td></tr>
          <tr bgcolor=$mylightcolor><td><label for='factEmail'>Email</label></td><td>
             <input type='text' name='factEmail' id='factEmail' value=\"".$factEmail."\"></td></tr>
          </table>";
        
        // needed to allow copie from devis to facture
        echo "<input type='hidden' id='devisLieu' name='devisLieu' value=\"".$devisLieu."\">";
        echo "<input type='hidden' id='devisOrga' name='devisOrga' value=\"".$devisOrga."\">";
        echo "<input type='hidden' id='devisDateHeure' name='devisDateHeure' value=\"".$devisDateHeure."\">";
        echo "<input type='hidden' id='devisCivilite' name='devisCivilite' value=\"".$devisCivilite."\">";
        echo "<input type='hidden' id='devisContact' name='devisContact' value=\"".$devisContact."\">";
        echo "<input type='hidden' id='devisAdresse' name='devisAdresse' value=\"".$devisAdresse."\">";
        echo "<input type='hidden' id='devisCP' name='devisCP' value=\"".$devisCP."\">";
        echo "<input type='hidden' id='devisVille' name='devisVille' value=\"".$devisVille."\">";
        echo "<input type='hidden' id='devisTel1' name='devisTel1' value=\"".$devisTel1."\">";
        echo "<input type='hidden' id='devisTel2' name='devisTel2' value=\"".$devisTel2."\">";
        echo "<input type='hidden' id='devisFax' name='devisFax' value=\"".$devisFax."\">";
        echo "<input type='hidden' id='devisEmail' name='devisEmail' value=\"".$devisEmail."\">";
          
          
    echo Buttons('facture');
    echo "</div>";
}
//================================================
// RELANCE
//================================================
if ( $tab == 3 ) {
    if ( $relanceDate == '' ) $warn=" <i class='fa fa-exclamation-triangle' style='color:orange; padding-left:5px;' title='saisissez la date et sauvez'></i>";
    else $warn="";
    echo "<div id='relance'>";
    echo  "<table cellspacing=0>
                <tr class='MenuRub'><td colspan=2>Relance</td></tr>";
    echo "<tr  bgcolor=$mylightcolor><td><label for='relanceDate'>Date de relance </label></td><td>
             <input type='text' name='relanceDate' id='relanceDate' value=\"".$relanceDate."\" placeholder='JJ-MM-AAAA' 
             onfocus='fillDate(frmGesCom.relanceDate);'
             onchange='checkDate2(frmGesCom.relanceDate);'> $warn</td></tr>
          <tr  bgcolor=$mylightcolor><td><label for='relanceNum'>Nombre de relance</label></td><td>
             <input type='text' name='relanceNum' id='relanceNum' value=\"".$relanceNum."\"></td></tr>
          <tr  bgcolor=$mylightcolor><td><label for='relanceCom'>Commentaire</label></td><td>
             <textarea name='relanceCom' id='relanceCom' cols='40' rows=6>".$relanceCom."</textarea></td></tr>
            </table>";
       
    echo Buttons('relance');
    echo "</div>";
}
//================================================
// PAIEMENT
//================================================
if ( $tab == 4 ) {
    if ( $paiementDate == '' ) $warn=" <i class='fa fa-exclamation-triangle' style='color:orange; padding-left:5px;' title='saisissez la date et sauvez'></i>";
    else $warn="";
    echo "<div id='paiement'>";
    echo  "<table cellspacing=0>
                <tr class='MenuRub'><td colspan=2>Paiement</td></tr>";
    echo "<tr bgcolor=$mylightcolor><td><label for='paiementDate'>Date du paiement </label></td><td>
             <input type='text' name='paiementDate' id='paiementDate' value=\"".$paiementDate."\" placeholder='JJ-MM-AAAA' 
             onfocus='fillDate(frmGesCom.paiementDate);'
             onchange='checkDate2(frmGesCom.paiementDate);'> $warn</td></tr>
          <tr bgcolor=$mylightcolor><td><label for='paiementCom'>Commentaire</label></td><td>
             <textarea name='paiementCom' id='paiementCom' cols='40'>".$paiementCom."</textarea></td></tr>
            </table>";

    echo Buttons('paiement');
    echo "</div>";
}
echo "<input type='hidden' name='tab' id='tab' value=".$tab.">";
echo "<input type='hidden' name='frmaction' id='frmaction' value=".$frmaction.">";
echo "<input type='hidden' name='evenement' id='evenement' value=".$evenement.">";
echo "</form></div>";

writefoot();
