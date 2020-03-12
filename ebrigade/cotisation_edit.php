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
$_SESSION['from_cotisation']=1;

if (isset ($_GET["rejet_id"])) $rejet_id=intval($_GET["rejet_id"]);
elseif (isset ($_POST["rejet_id"])) $rejet_id=intval($_POST["rejet_id"]);
else $rejet_id=-1;

if (isset ($_GET["paiement_id"])) $paiement_id=intval($_GET["paiement_id"]);
elseif (isset ($_POST["paiement_id"])) $paiement_id=intval($_POST["paiement_id"]);
else $paiement_id=-1;

if (isset ($_GET["rembourse"])) $rembourse=intval($_GET["rembourse"]);
elseif (isset ($_POST["rembourse"])) $rembourse=intval($_POST["rembourse"]);
else $rembourse=0;

if (isset ($_GET["pid"])) $pid=intval($_GET["pid"]);
elseif (isset ($_POST["pid"])) $pid=intval($_POST["pid"]);
else $pid=0;

if (isset ($_GET["note"])) $note=intval($_GET["note"]);
elseif (isset ($_POST["note"])) $note=intval($_POST["note"]);
else $note=0;

if (isset ($_GET["action"])) $action=$_GET["action"];
elseif (isset ($_POST["action"])) $action=$_POST["action"];
else $action='update';

if (isset ($_GET["periode"])) $periode=$_GET["periode"];
else $periode="";

if (isset ($_GET["from"])) $from=$_GET["from"];
else $from="P";

if ( $note > 0) $permission=75;
else $permission=53;

check_all($permission);

if (! check_rights($id, $permission, get_section_of($pid))) check_all(24);

writehead();

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript'>

function goback(from){
    if ( from == 'V' ) {
        self.location = 'virements.php';
    }
    else {
        self.location = 'upd_personnel.php?tab=8&pid=<?php echo $pid; ?>';
    }
}

function fillDate(form1,form2, defaultDate) {
     if (form1.checked) {
            if ( form2.value == '' ) {
                 form2.value = defaultDate;
            }
     }
    return true;
}

function updateMontant(form1,form2) {
     if ( form1.value > 0 && form2.value == 0 ) {
            form2.value = form1.value;
     }
    return true;
}

function changedType() {
    var type = document.getElementById('type_paiement');
    var numChequeRow = document.getElementById('numChequeRow');
    var ribRow = document.getElementById('ribRow');
    var fromRow = document.getElementById('fromRow');
    if (type.value == '4') {
        numChequeRow.style.display = '';
        ribRow.style.display = 'none';
        fromRow.style.display = 'none';
    } 
    else if (type.value == '1'  || type.value == '2'){
        numChequeRow.style.display = 'none';
        <?php 
            if ( $bank_accounts ) {
                echo "ribRow.style.display = '';"; 
                echo "fromRow.style.display = '';"; 
            }
        ?>
    }
    else {
        numChequeRow.style.display = 'none';
        ribRow.style.display = 'none';
        fromRow.style.display = 'none';
    }
}

function showModeRegul( regularise, typePaiement) {
    var modeRegulRow = document.getElementById('modeRegulRow');
    var montant_regul = document.getElementById('montant_regul');
    var date_regul = document.getElementById('date_regul');
    var observation = document.getElementById('observation');
    var representer= document.getElementById('representer');
    if ( regularise.checked ) {
        modeRegulRow.style.display = '';
        if (representer.checked ) {
            type_regularisation.value='3';
        }
        representer.checked = false;
        representer.disabled = true;
    }
    else {
        modeRegulRow.style.display = 'none';
        montant_regul.value = '';
        date_regul.value = '';
        observation.value= '';
        type_regularisation.value='0';
        representer.disabled = false;
    }
    return true;
}

function changeModeRegul ( type_regularisation, montant ) {
    var observation = document.getElementById('observation');
    var cmt = montant+ "<?php echo $default_money_symbol; ?> � repr�senter";
    if (type_regularisation.value == '3') {
        if ( observation.value == "" ) {
            observation.value = cmt; 
        }
    }
    else {
        if ( observation.value == cmt ) {
            observation.value = "";
        }
    }
}

function clickRepresenter(defaultDate, typePaiement) {
    var regularise= document.getElementById('regularise');
    var representer= document.getElementById('representer');
    var date_regul= document.getElementById('date_regul');
    var observation = document.getElementById('observation');
    var cmt = montant_rejet.value + "<?php echo $default_money_symbol; ?> au pr�l�vement suivant";
    updateMontant(montant_rejet,montant_regul);
    if ( observation.value == "" ) {
        observation.value = cmt; 
    }
    return true;
}

function clickRegularise(defaultDate, typePaiement) {
    var regularise= document.getElementById('regularise');
    var date_regul= document.getElementById('date_regul');
    var montant_rejet= document.getElementById('montant_rejet');
    var montant_regul= document.getElementById('montant_regul');
    fillDate(regularise,date_regul,defaultDate);
    updateMontant(montant_rejet,montant_regul);
    showModeRegul(regularise,typePaiement);
    return true;
}

function changedPeriode(pompier) {
    var periodeSelect = document.getElementById('periode');
    self.location = 'cotisation_edit.php?paiement_id=0&action=insert&pid=' + pompier + '&periode=' + periodeSelect.value;
}

<?php
echo "</script>";
echo "</head>";
//=====================================================================
// traiter delete
//=====================================================================
if (isset ($_GET["rejet_id"]) and $action=='delete') {
    verify_csrf('cotisation');
    $query="select ANNEE, MONTANT_REJET from rejet where R_ID=".$rejet_id." and P_ID=".$pid;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $cmt="Rejet de ".$row[1].$default_money_symbol." pour ".$row[0];
    insert_log('DELREJ', $pid, $cmt);
    
    $query="delete from rejet where R_ID=".$rejet_id." and P_ID=".$pid;
    $result=mysqli_query($dbc,$query);
    echo "<body onload='goback();'>";
    exit;
}
if (isset ($_GET["paiement_id"]) and $action=='delete') {
    verify_csrf('cotisation');
    $query="select ANNEE, MONTANT from personnel_cotisation  where PC_ID=".$paiement_id." and P_ID=".$pid;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( $rembourse == 0 ) {
        $cmt="Paiement de ".$row[1].$default_money_symbol." pour ".$row[0];
        insert_log('DELCOT', $pid, $cmt);
    }
    else {
        $cmt="Remboursement de ".$row[1].$default_money_symbol;
        insert_log('DELREM', $pid, $cmt);    
    }
    $query="delete from personnel_cotisation where PC_ID=".$paiement_id." and P_ID=".$pid;
    $result=mysqli_query($dbc,$query);
    echo "<body onload='goback();'>";
    exit;
}

//=====================================================================
// Sauver les modification 
//=====================================================================

if ( isset ($_POST["rejet_id"])  and ($action=='update' or $action=='insert')) {
    verify_csrf('cotisation');
    $ANNEE=intval($_POST["annee"]);
    if ( $ANNEE == 0 ) $ANNEE='null';
    $DEFAUT_ID=intval($_POST["defaut_bancaire"]);
    $MONTANT_REGUL=(float) str_replace(",",".",$_POST["montant_regul"]);
    $MONTANT_REJET=(float) str_replace(",",".",$_POST["montant_rejet"]);
    $PERIODE=secure_input($dbc,$_POST["periode"]);
    $DATE_REJET=secure_input($dbc,$_POST["date_rejet"]);
    if ( $DATE_REJET <> '') {
        $tmp=explode ( "-",$DATE_REJET); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
        $DATE_REJET="'".$year."-".$month."-".$day."'";
    }
    else $DATE_REJET = 'null';
    $REGULARISE=(isset($_POST["regularise"])?intval($_POST["regularise"]):0);
    $REPRESENTER=(isset($_POST["representer"])?intval($_POST["representer"]):0);
    $REMBOURSEMENT=0;
    $REGUL_ID=(isset($_POST["type_regularisation"])?intval($_POST["type_regularisation"]):0);
    $DATE_REGUL=secure_input($dbc,$_POST["date_regul"]);
    if ( $DATE_REGUL <> '') {
        $tmp=explode ( "-",$DATE_REGUL); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
        $DATE_REGUL="'".$year."-".$month."-".$day."'";
    }
    else $DATE_REGUL = 'null';
    $OBSERVATION=STR_replace("\"","",$_POST["observation"]);
    $OBSERVATION=secure_input($dbc,$OBSERVATION);
    
    if ( $action == 'insert' ) {
        $query="insert into rejet (P_ID,ANNEE, PERIODE_CODE, DATE_REJET, DEFAUT_ID, MONTANT_REJET, REPRESENTER, REGULARISE, REGUL_ID, DATE_REGUL, MONTANT_REGUL, OBSERVATION)
            values (".$pid.",".$ANNEE.",'".$PERIODE."',".$DATE_REJET.",".$DEFAUT_ID.",".$MONTANT_REJET.",".$REPRESENTER.", ".$REGULARISE.",".$REGUL_ID.",".$DATE_REGUL.",".$MONTANT_REGUL.",\"".$OBSERVATION."\")";
        $result=mysqli_query($dbc,$query);
        
        if ( $REPRESENTER  == 1  and $REGULARISE == 0 and $MONTANT_REGUL > 0 ) {
            $query="update pompier set MONTANT_REGUL = ROUND( MONTANT_REGUL + ".$MONTANT_REGUL." , 2 ) where P_ID=".$pid;
            $result=mysqli_query($dbc,$query);
        }
        $cmt="Rejet de ".$MONTANT_REJET.$default_money_symbol." pour ".$ANNEE;
        insert_log('INSREJ', $pid, $cmt);
        
    }
    else {
        $query="update rejet set
            ANNEE=".$ANNEE.",
            DEFAUT_ID=".$DEFAUT_ID.",
            PERIODE_CODE='".$PERIODE."',
            DATE_REJET=".$DATE_REJET.",
            MONTANT_REGUL=".$MONTANT_REGUL.",
            REGUL_ID=".$REGUL_ID.",
            MONTANT_REJET=".$MONTANT_REJET.",
            DATE_REGUL=".$DATE_REGUL.",
            OBSERVATION=\"".$OBSERVATION."\"
            where R_ID=".$rejet_id." 
            and P_ID=".$pid;
        $result=mysqli_query($dbc,$query);
        
        $query="update rejet set 
                REPRESENTER=".$REPRESENTER.",
                REGULARISE=".$REGULARISE."
                where R_ID=".$rejet_id." 
                and P_ID=".$pid;
        $result=mysqli_query($dbc,$query);
        
        if ( mysqli_affected_rows($dbc) == 1 and  $MONTANT_REGUL > 0 ) {
            if ( $REPRESENTER == 1 and $REGULARISE == 0 ) { // incrementer somme � repr�senter
                $query="update pompier set MONTANT_REGUL = ROUND( MONTANT_REGUL + ".$MONTANT_REGUL." , 2 ) where P_ID=".$pid;
                $result=mysqli_query($dbc,$query);
            }
            else if ( $REGULARISE == 1 ) { // r�duire somme � repr�senter
                $query="update pompier set MONTANT_REGUL = ROUND( MONTANT_REGUL - ".$MONTANT_REGUL." , 2 ) where P_ID=".$pid;
                $result=mysqli_query($dbc,$query);
                $query="update pompier set MONTANT_REGUL = 0 where MONTANT_REGUL < 0 and P_ID=".$pid;
                $result=mysqli_query($dbc,$query);
            }
        }
        
        $cmt="Rejet de ".$MONTANT_REJET.$default_money_symbol." pour ".$ANNEE;
        insert_log('UPDREJ', $pid, $cmt);
    }
    echo "<body onload='goback();'>";
    exit;
}

if (  isset ($_POST["paiement_id"])  and ($action=='update' or $action=='insert')) {
    verify_csrf('cotisation');
    $ANNEE=intval($_POST["annee"]);
    if ( $ANNEE == 0 ) $ANNEE='null';
    $MONTANT=(float) str_replace(",",".",$_POST["montant_paiement"]);
    $PERIODE=secure_input($dbc,$_POST["periode"]);
    $PC_DATE=secure_input($dbc,$_POST["date_paiement"]);
    if ( $PC_DATE <> '') {
        $tmp=explode ( "-",$PC_DATE); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
        $PC_DATE="'".$year."-".$month."-".$day."'";
    }
    else $PC_DATE = 'null';
    $COMMENTAIRE=STR_replace("\"","",$_POST["commentaire"]);
    $COMMENTAIRE=secure_input($dbc,$COMMENTAIRE);
    $REMBOURSEMENT=$rembourse;
    $TP_ID=intval($_POST["type_paiement"]);
    $paiement_id=intval($_POST["paiement_id"]);
    $NUM_CHEQUE=STR_replace("\"","",$_POST["num_cheque"]);
    $NUM_CHEQUE=secure_input($dbc,$NUM_CHEQUE);
    if (isset ($_POST["compte_a_debiter"])) $compte_a_debiter = intval($_POST["compte_a_debiter"]);
    else $compte_a_debiter=0;
    if ( $compte_a_debiter == 0 ) $compte_a_debiter = "null";
    $etablissement="null";
    $guichet="null";
    $compte="null";
    $code_banque="null";
    $iban="null";
    $bic="null";
    if ( $TP_ID == 1 or $TP_ID == 2) {
        if (isset($_POST["etablissement"])) {
            $etablissement=STR_replace("\"","",$_POST["etablissement"]);
            $etablissement="\"".secure_input($dbc,$etablissement)."\"";    
            $guichet=STR_replace("\"","",$_POST["guichet"]);
            $guichet="\"".secure_input($dbc,$guichet)."\"";
            $compte=STR_replace("\"","",$_POST["compte"]);
            $compte="\"".secure_input($dbc,$compte)."\"";
            $code_banque=STR_replace("\"","",$_POST["code_banque"]);
            $code_banque="\"".secure_input($dbc,$code_banque)."\"";
        }
        $iban="\"".str_replace('-','',str_replace(' ','',secure_input($dbc,$_POST["iban"])))."\"";
        $bic=STR_replace("\"","",$_POST["bic"]);
        $bic="\"".secure_input($dbc,$bic)."\"";
    }

    if ( $action == 'insert' ) {
    
        if ( $REMBOURSEMENT == 0 ) {
            $query="select 1 from personnel_cotisation where P_ID=".$pid." and ANNEE=".$ANNEE." and PERIODE_CODE = '".$PERIODE."' and REMBOURSEMENT=0";
            $result=mysqli_query($dbc,$query);
            $row=@mysqli_fetch_array($result);
            if ( $row[0] == 1 ) {
                write_msgbox("Erreur", $error_pic, "Il y a d�j� eu un paiement pour cette p�riode de $ANNEE<p><input type='button' class='btn btn-default' value='retour' onclick='goback();'>",30,0);
                exit;
            }
        }
        $query="insert into personnel_cotisation (P_ID, ANNEE, PERIODE_CODE, MONTANT, PC_DATE, TP_ID, COMMENTAIRE, NUM_CHEQUE, 
                    IBAN, BIC, REMBOURSEMENT, COMPTE_DEBITE)
                values (".$pid.",".$ANNEE.",'".$PERIODE."',".$MONTANT.",".$PC_DATE.",".$TP_ID.",\"".$COMMENTAIRE."\",\"".$NUM_CHEQUE."\", 
                    ".$iban.",".$bic.",".$REMBOURSEMENT.", ".$compte_a_debiter.")";
        $result=mysqli_query($dbc,$query);
        
        if ( $REMBOURSEMENT == 1 ) {
            $cmt="Remboursement de ".$MONTANT.$default_money_symbol;
            insert_log('INSREM', $pid, $cmt);
            if ( $note > 0 ) {    
                $action_link="<a href='upd_personnel.php?id=".$pid."'><input type='submit' class='btn btn-default' value='Retour'></a>";
                $_SESSION['from_cotisation']=1;
                write_msgbox("Remboursement Note de frais enregistr�", $star_pic, 
                " Le remboursement de la note de frais pour un montant total de <b>".$MONTANT.$default_money_symbol."</b>".
                " <br>a �t� enregistr� sur la fiche personnel.<p align=center>".$action_link,10,0);
                exit;
            }
        }
        else {
            $cmt="Paiement de ".$MONTANT.$default_money_symbol." pour ".$ANNEE;
            insert_log('INSCOT', $pid, $cmt);
            // cas de la personne en pr�l�vement, on consid�re que la r�gul a �t� faite.
            $query="update pompier set MONTANT_REGUL=0 where P_ID=".$pid." and TP_ID=".$TP_ID." and TP_ID = 1";
            $result=mysqli_query($dbc,$query);
        }
    }
    else {
        $query="update personnel_cotisation set 
            ANNEE=".$ANNEE.",
            PERIODE_CODE='".$PERIODE."',
            MONTANT=".$MONTANT.",
            PC_DATE=".$PC_DATE.",
            REMBOURSEMENT=".$REMBOURSEMENT.",
            COMPTE_DEBITE=".$compte_a_debiter.",
            TP_ID=".$TP_ID.",
            COMMENTAIRE=\"".$COMMENTAIRE."\",
            NUM_CHEQUE=\"".$NUM_CHEQUE."\",
            etablissement=".$etablissement.",
            guichet=".$guichet.",
            compte=".$compte.",
            code_banque=".$code_banque.",
            IBAN=".$iban.",
            BIC=".$bic."
            where PC_ID=".$paiement_id." 
            and P_ID=".$pid;
        $result=mysqli_query($dbc,$query);
        
        if ( $REMBOURSEMENT == 1 ) {
            $cmt="Remboursement de ".$MONTANT.$default_money_symbol;
            insert_log('UPDREM', $pid, $cmt);
        }
        else {
            $cmt="Paiement de ".$MONTANT.$default_money_symbol." pour ".$ANNEE;
            insert_log('UPDCOT', $pid, $cmt);
        }
    }
    echo "<body onload='goback();'>";
    exit;
}

echo "<body><div align='center'>";

// ==================================
// Editer Rejet 
// ==================================

if ( $rejet_id >= 0 ) {

$img="<i class='fa fa-exclamation-circle fa-2x' style='color:red;'></i>";
$comment='Rejet non r�gularis�';
$color='red';

$query="select po.P_NOM, po.P_PRENOM, r.R_ID, r.ANNEE, r.PERIODE_CODE, r.DEFAUT_ID, r.MONTANT_REJET, 
            date_format(r.DATE_REJET,'%d-%m-%Y') DATE_REJET,
            date_format(r.DATE_REGUL,'%d-%m-%Y') DATE_REGUL, r.MONTANT_REGUL, r.OBSERVATION, r.REGULARISE,
            r.REPRESENTER,
            d.D_ID,
            r.REGUL_ID,
            po.TP_ID
            from rejet r, periode p, defaut_bancaire d, pompier po
            where r.DEFAUT_ID=d.D_ID
            and po.P_ID = r.P_ID
            and r.PERIODE_CODE=p.P_CODE
            and r.P_ID=".$pid."
            and r.R_ID=".$rejet_id;

$result=mysqli_query($dbc,$query);
if ( mysqli_num_rows($result) > 0 ) {
    custom_fetch_array($result);
    $P_NOM=strtoupper($P_NOM);
    $P_PRENOM=my_ucfirst($P_PRENOM);
    if ( $REGULARISE == 1 ) {
        $img="<i class='far fa-check-square fa-2x' style='color:green;'></i>";
        $comment='Rejet r�gularis�';
        $color='green';
    }
    else if ( $REPRESENTER == 1 ) {
        $img="<i class='fa fa-exclamation-triangle fa-2x' style='color:orange;'></i>";
        $comment='Rejet en cours de r�gularisation';
        $color='orange';
    }
}
else if ( $action == 'insert' ) {
    $query2="select P_NOM, P_PRENOM, TP_ID
            from pompier where P_ID=".$pid;
    $result2=mysqli_query($dbc,$query2);
    $row2=@mysqli_fetch_array($result2);
    $P_NOM=strtoupper($row2[0]);
    $P_PRENOM=my_ucfirst($row2[1]);
    $TP_ID=$row2[2];
    if ( $TP_ID == 4 ) $D_ID=10;
    else $D_ID=0;
    
    $ANNEE=date("Y");
    $PERIODE_CODE="";
    $MONTANT_REJET="";
    $DATE_REJET=date('d-m-Y');
    $DATE_REGUL="";
    $MONTANT_REGUL=0;
    $OBSERVATION="";
    $REGULARISE=0;
    $REPRESENTER=0;
    $REGUL_ID=0;
    
    $query2="select P_CODE from periode where P_DESCRIPTION='".moislettres(date("m"))."'";
    $result2=mysqli_query($dbc,$query2);
    $row2=@mysqli_fetch_array($result2);
    $PERIODE_CODE=$row2[0];
}
else {
    echo "Rejet non trouv�";
    exit;
}

echo "<table class='noBorder'>
      <tr><td width=40 rowspan=2>".$img."</td>
      <td><font size=4><b> Rejet pour $P_PRENOM $P_NOM</b></font></td></tr>
      <tr><td><font size=1 color=$color><b>".$comment."</b></font></td></tr>
      </table>";
      
echo "<p><form action=cotisation_edit.php method=POST>
        <table cellspacing=0 border=0>
          <tr>
               <td colspan=2 class=TabHeader>Rejet de pr�l�vement</td>
         </tr>";
print insert_csrf('cotisation');

echo "<tr bgcolor=$mylightcolor><td>Ann�e</td>";
echo "<td witdh=200><input name=annee type=text size=4 onchange=\"checkNumber(form.annee,".$ANNEE.");\" value='".$ANNEE."'></td></tr>";

echo "<tr bgcolor=$mylightcolor><td>P�riode</td>";
echo "<td>";
$query2="select P_CODE,P_DESCRIPTION from periode order by P_ORDER";
$result2=mysqli_query($dbc,$query2);
echo "<select name='periode'>";
            while ($row2=@mysqli_fetch_array($result2)) {
                  $_P_CODE=$row2["P_CODE"];
                  $_P_DESCRIPTION=$row2["P_DESCRIPTION"];
                  if ( $PERIODE_CODE == $_P_CODE ) $selected='selected';
                  else $selected='';
                  echo "<option value='$_P_CODE' $selected>$_P_DESCRIPTION</option>";
    }
echo "</select>";
echo "</td></tr>";

echo "<tr bgcolor=$mylightcolor>
            <td>Date rejet</td>
            <td>
            <input type='text' name='date_rejet' size='10' value='".$DATE_REJET."' onchange='checkDate2(form.date_rejet)' class='datepicker' data-provide='datepicker'>
            <font size=1><i>JJ-MM-AAAA</i></font></td>";
echo "</tr>";

echo "<tr bgcolor=$mylightcolor><td>D�faut bancaire</td>";
echo "<td>";
$query2="select D_ID,D_DESCRIPTION from defaut_bancaire order by D_DESCRIPTION";
$result2=mysqli_query($dbc,$query2);
echo "<select name='defaut_bancaire' style='max-width:240px;font-size:12px;'>";
            while ($row2=@mysqli_fetch_array($result2)) {
                  $_D_ID=$row2["D_ID"];
                  $_D_DESCRIPTION=$row2["D_DESCRIPTION"];
                  if ( $D_ID == $_D_ID ) $selected='selected';
                  else $selected='';
                  echo "<option value='$_D_ID' $selected>$_D_DESCRIPTION</option>";
    }
echo "</select>";
echo "</td></tr>";

if ( $MONTANT_REJET == '' ) $DEFMONTANT_REJET = 0;
else $DEFMONTANT_REJET =  $MONTANT_REJET;

echo "<tr bgcolor=$mylightcolor><td>Montant Rejet</td>";
echo "<td ><input name=montant_rejet id=montant_rejet type=text size=4 onchange=\"checkFloat(form.montant_rejet,".$DEFMONTANT_REJET.");\" value='".$MONTANT_REJET."'> ".$default_money_symbol."</td></tr>";

echo "<tr>
               <td class=TabHeader colspan=2>R�gularisation</td>
         </tr>";      

// si la personne est en pr�l�vement, on peut choisir de repr�senter au prochain pr�l�vement (en cours de r�gularisation)
if ( $REPRESENTER == 1 or ( $bank_accounts == 1 and $TP_ID == 1)) {
    if ( $REGULARISE == '1') $dis="disabled";
    else $dis="";
    if ( $REPRESENTER == 1 ) $checked="checked";
    else $checked="";
    echo "<tr bgcolor=$mylightcolor><td>A repr�senter</td>
     <td><input type='checkbox' name='representer' id='representer' value='1' $checked $dis 
        title=\"Cocher si le le montant rejet� et pas encore r�gularis� doit �tre ajout� au prochain pr�l�vement (r�gularisation en cours)\" 
        onclick=\"clickRepresenter('".date("d-m-Y")."', '".$TP_ID."');\"><span class=small2> au prochain pr�l�vement</span></td></tr>";    
}
else echo "<input type='hidden' name='representer' id='representer' value='0'>";

if ( $REGULARISE == 1 ) $checked="checked";
else $checked="";
echo "<tr bgcolor=$mylightcolor><td>Rejet r�gularis�</td>
     <td><input type='checkbox' name='regularise' id='regularise' value='1' $checked 
        title=\"Cocher si le rejet a �t� r�gularis�\" 
        onclick=\"clickRegularise('".date("d-m-Y")."', '".$TP_ID."');\"></td></tr>";        

if ( $REGULARISE == '1') $style="";
else  $style="style='display:none'";

echo "<tr bgcolor=$mylightcolor id=modeRegulRow $style><td>Mode r�gularisation</td>";
    $query2="select TR_ID, TR_DESCRIPTION from type_regularisation";
    if ( $bank_accounts == 0 ) $query2 .=" where ( TR_ID < 3 or TR_ID =".$REGUL_ID.")";
    $query2 .=" order by TR_ID" ;     
    $result2=mysqli_query($dbc,$query2);
    echo "<td><select name='type_regularisation' id='type_regularisation'
            onchange=\"changeModeRegul(form.type_regularisation,form.montant_regul.value);\">";
            while ($row2=@mysqli_fetch_array($result2)) {
                  $_TR_ID=$row2["TR_ID"];
                  $_TR_DESCRIPTION=$row2["TR_DESCRIPTION"];
                  if ( $REGUL_ID == $_TR_ID ) $selected='selected';
                  else $selected='';
                  echo "<option value='$_TR_ID' $selected>$_TR_DESCRIPTION</option>";
    }
echo "</select></td>";
    
echo "<tr bgcolor=$mylightcolor><td>Montant R�gularisation</td>";
echo "<td ><input name=montant_regul id=montant_regul type=text size=4 onchange=\"checkFloat(form.montant_regul,'".$MONTANT_REGUL."');\" value='".$MONTANT_REGUL."'> ".$default_money_symbol."</td></tr>";

echo "<tr bgcolor=$mylightcolor>
            <td>Date r�gularisation</td>
            <td>
            <input type='text' name='date_regul' id='date_regul' size='10' value='".$DATE_REGUL."' onchange='checkDate2(form.date_regul)' class='datepicker' data-provide='datepicker'>
            <font size=1><i>JJ-MM-AAAA</i></font></td>";        
echo "</tr>";

echo "<tr bgcolor=$mylightcolor>
            <td>Observation</td>
            <td>
            <textarea name='observation' id='observation' cols='30' rows='2'
            style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;'
            value=\"$OBSERVATION\" >".$OBSERVATION."</textarea>
          </td>";        
echo "</tr>";

echo "</table>";// end left table

echo "<input type=hidden name='rejet_id' value='".$rejet_id."'>";
echo "<input type=hidden name='pid' value='".$pid."'>";
echo "<input type=hidden name='action' value='".$action."'>";
echo "<p>
<input type='submit' class='btn btn-default' value='sauver'>
<input type='button' class='btn btn-default' value='retour' onclick='goback();'>
</div></form>";
}
else if ( $paiement_id >= 0  ) {
//=====================================================================
// Editer Paiement 
//=====================================================================

if ( $rembourse ==1 ) $img="<i class='fa fa-money fa-3x' style='color:black;'></i> ";
else $img="<i class='fa fa-euro-signfa-3x' style='color:blue;'></i> ";
$number_months_to_pay = "";
$query="select po.P_NOM, po.P_PRENOM, pc.PC_ID, pc.ANNEE, pc.PERIODE_CODE, date_format(pc.PC_DATE,'%d-%m-%Y') PC_DATE,
            pc.COMMENTAIRE , pc.NUM_CHEQUE , pc.MONTANT, tp.TP_ID, tp.TP_DESCRIPTION,
            date_format(po.P_DATE_ENGAGEMENT,'%d-%m-%Y') P_DATE_ENGAGEMENT,
            date_format(po.P_FIN,'%d-%m-%Y') P_FIN, 
            pc.ETABLISSEMENT, pc.GUICHET, pc.COMPTE, pc.CODE_BANQUE, pc.REMBOURSEMENT, pc.COMPTE_DEBITE, pc.IBAN, pc.BIC
            from personnel_cotisation pc, periode p, pompier po, type_paiement tp
            where tp.TP_ID = pc.TP_ID
            and po.P_ID = pc.P_ID
            and pc.PERIODE_CODE=p.P_CODE
            and pc.P_ID=".$pid."
            and pc.PC_ID=".$paiement_id;

$result=mysqli_query($dbc,$query);
if ( mysqli_num_rows($result) > 0 ) {
    $row=@mysqli_fetch_array($result);
    $P_NOM=strtoupper($row["P_NOM"]);
    $P_PRENOM=my_ucfirst($row["P_PRENOM"]);
    $ANNEE=$row["ANNEE"];
    $PERIODE_CODE=$row["PERIODE_CODE"];
    $PC_DATE=$row["PC_DATE"];
    $COMMENTAIRE=$row["COMMENTAIRE"];
    $REMBOURSEMENT=$row["REMBOURSEMENT"];
    $COMPTE_DEBITE=$row["COMPTE_DEBITE"];
    $NUM_CHEQUE=$row["NUM_CHEQUE"];
    $MONTANT=$row["MONTANT"];
    $TP_DESCRIPTION=$row["TP_DESCRIPTION"];
    $TP_ID=strtoupper($row["TP_ID"]);
    $P_DATE_ENGAGEMENT=$row["P_DATE_ENGAGEMENT"];
    $P_FIN=$row["P_FIN"];
    $ETABLISSEMENT=$row["ETABLISSEMENT"];
    $GUICHET=$row["GUICHET"];
    $COMPTE=$row["COMPTE"];
    $CODE_BANQUE=$row["CODE_BANQUE"];
    $IBAN=$row["IBAN"];
    $BIC=$row["BIC"];
    $MONTANT_REGUL=0;
}
else if ( $action == 'insert' ) {
    $ANNEE=date("Y");
    $PERIODE_CODE="";
    $PC_DATE=date('d-m-Y');
    $MONTANT="";
    $COMMENTAIRE="";
    $NUM_CHEQUE="";
    if ( $rembourse == 1 ) {
        $REMBOURSEMENT=1;        
    }
    else $REMBOURSEMENT=0;
    $COMPTE_DEBITE="";
    
    $query2="select P_NOM,P_PRENOM, TP_ID, MONTANT_REGUL,
            date_format(P_DATE_ENGAGEMENT,'%d-%m-%Y') P_DATE_ENGAGEMENT,
            date_format(P_FIN,'%d-%m-%Y') P_FIN,
            YEAR(P_DATE_ENGAGEMENT) YEAR_ENGAGEMENT,
            MONTH(P_DATE_ENGAGEMENT) MONTH_ENGAGEMENT,
            YEAR(P_FIN) YEAR_FIN,
            MONTH(P_FIN) MONTH_FIN,
            IBAN, BIC
            from pompier left join compte_bancaire on ( CB_TYPE = 'P' and CB_ID = P_ID )
            where P_ID=".$pid;
    $result2=mysqli_query($dbc,$query2);
    $row2=@mysqli_fetch_array($result2);
    $P_NOM=strtoupper($row2[0]);
    $P_PRENOM=my_ucfirst($row2[1]);
    $TP_ID=$row2[2];
    $MONTANT=get_montant_cotisation($pid);
    $P_DATE_ENGAGEMENT=$row2["P_DATE_ENGAGEMENT"];
    $P_FIN=$row2["P_FIN"];
    $YEAR_ENGAGEMENT=$row2["YEAR_ENGAGEMENT"];
    $MONTH_ENGAGEMENT=$row2["MONTH_ENGAGEMENT"];
    $YEAR_FIN=$row2["YEAR_FIN"];
    $MONTH_FIN=$row2["MONTH_FIN"];
    $MONTANT_REGUL=(float)$row2["MONTANT_REGUL"];
    $IBAN=$row2["IBAN"];
    $BIC=$row2["BIC"];
    if ( $bank_accounts and $IBAN <> "") {
        if ( $REMBOURSEMENT == 1 ) $TP_ID=2; // virement
        else $TP_ID=1; // pr�l�vement
    }
    else $TP_ID=4; // ch�que
    
    if ( $periode == "" ) {
        if ( $TP_ID == 1 ) {
            $query2="select P_CODE, P_FRACTION from periode where P_DESCRIPTION='".moislettres(date("m"))."'";
            $result2=mysqli_query($dbc,$query2);
            $row2=@mysqli_fetch_array($result2);
            $PERIODE_CODE=$row2[0];
        }
        else {
            $PERIODE_CODE='A';
        }
    }
    else $PERIODE_CODE=$periode;

    $fraction=get_fraction($PERIODE_CODE);
    
    if ( $PERIODE_CODE == 'A' and  ($YEAR_ENGAGEMENT == $ANNEE or $YEAR_FIN == $ANNEE)) {
        // �ventuellement demander cotisation pour ann�e incompl�te
        $number_months_to_pay = 12;
        if ( $MONTH_ENGAGEMENT <> "" ) $number_months_to_pay =  $number_months_to_pay - $MONTH_ENGAGEMENT + 1;
        if ( $MONTH_FIN <> "" )  $number_months_to_pay = $number_months_to_pay - ( 12 - $MONTH_FIN );
        $coeff= $number_months_to_pay / 12 ;
        $MONTANT= round($coeff * $MONTANT , 2);
    }
    else {
        $MONTANT = round($MONTANT / $fraction , 2);
        $number_months_to_pay = 12 / $fraction;
    }
    if ( $REMBOURSEMENT == 1 ) {
        if ( $note > 0 ) {
            $COMMENTAIRE="Remboursement de la note de frais \nn�".$note;
            $query2="select TOTAL_AMOUNT from note_de_frais where NF_ID=".$note;
            $result2=mysqli_query($dbc,$query2);
            $row2=@mysqli_fetch_array($result2);
            $MONTANT=$row2["TOTAL_AMOUNT"];
        }
        else $MONTANT="";
    }
}
else {
    echo "Paiement non trouv�";
    exit;
}

if ( $COMMENTAIRE == "" and  $number_months_to_pay <> "" and $bank_accounts == 1 and $REMBOURSEMENT == 0 ) $COMMENTAIRE = $number_months_to_pay." mois, soit ".$MONTANT.$default_money_symbol;

// si on pr�l�ve, ajouter la r�gul
if ( $TP_ID == 1 and $MONTANT_REGUL <> 0 and $REMBOURSEMENT == 0) {
    $COMMENTAIRE = $COMMENTAIRE." et r�gul de ".$MONTANT_REGUL.$default_money_symbol;
    $MONTANT = $MONTANT + $MONTANT_REGUL;
}

if ( $REMBOURSEMENT == 0 ) $t="Paiement";
else $t="Remboursement";

echo "<table class='noBorder'>
      <tr><td>".$img."</td>
      <td><font size=4><b> ".$t." pour $P_PRENOM $P_NOM</b></font></td></tr>
      </table>";

if ( $REMBOURSEMENT == 0 ) $t2="Paiement ou pr�l�vement";
else $t2=$t;
echo "<p><form action=cotisation_edit.php method=POST>
        <table cellspacing=0 border=0>
          <tr>
               <td colspan=2 class=TabHeader>".$t2."</td>
         </tr>";
print insert_csrf('cotisation');

if ( $REMBOURSEMENT == 0 ) {
    echo "<tr bgcolor=$mylightcolor><td >Ann�e</td>";
    echo "<td ><input name=annee type=text size=4 onchange=\"javascript:checkNumber(form.annee,'".$ANNEE."');\" value='".$ANNEE."'></td></tr>";

    echo "<tr bgcolor=$mylightcolor><td>P�riode</td>";
    echo "<td>";
    $query2="select P_CODE,P_DESCRIPTION from periode order by P_ORDER";
    $result2=mysqli_query($dbc,$query2);

    if (  $action == 'insert' ) echo "<select name='periode' id='periode' onchange=\"javascript:changedPeriode('".$pid."');\">";
    else echo "<select name='periode'>";
    while ($row2=@mysqli_fetch_array($result2)) {
        $_P_CODE=$row2["P_CODE"];
        $_P_DESCRIPTION=$row2["P_DESCRIPTION"];
        if ( $PERIODE_CODE == $_P_CODE ) $selected='selected';
        else $selected='';
        echo "<option value='$_P_CODE' $selected>$_P_DESCRIPTION</option>";
    }
    echo "</select>";
    echo "</td></tr>";
}
else {
    echo "<input type=hidden name=annee value=".date('Y').">
          <input type=hidden name=periode value='A'>
          <input type=hidden name=rembourse value='1'>";
}

echo "<tr bgcolor=$mylightcolor><td >Type ".$t."</td>";
echo "<td >";
$query2="select TP_ID, TP_DESCRIPTION from type_paiement";
if ( $REMBOURSEMENT == 1 and $TP_ID <> 1) $query2 .=" where ( TP_ID <> 1 )"; // pas de pr�l�vement
$result2=mysqli_query($dbc,$query2);
echo "<select name='type_paiement'  id='type_paiement' onchange=\"javascript:changedType();\">";
            while ($row2=@mysqli_fetch_array($result2)) {
                  $_TP_ID=$row2["TP_ID"];
                  $_TP_DESCRIPTION=$row2["TP_DESCRIPTION"];
                  if ( $TP_ID == $_TP_ID ) $selected='selected';
                  else $selected='';
                  echo "<option value='$_TP_ID' $selected>$_TP_DESCRIPTION</option>";
    }
echo "</select>";
echo "</td></tr>";

if ( $TP_ID == '4' ) $style="";
else  $style="style='display:none'";
echo "<tr bgcolor=$mylightcolor id=numChequeRow $style><td>Num�ro de ch�que</td>";
echo "<td ><input name=num_cheque type=text size=15 value='".$NUM_CHEQUE."'></td></tr>";

if (($TP_ID == '1' or $TP_ID == '2') and $bank_accounts ) $style="";
else  $style="style='display:none'";

// d�finir depuis quel compte part le virement
if ( $REMBOURSEMENT == 1 ) {
    echo "<tr bgcolor=$mylightcolor $style id=fromRow><td>Remboursement par</td>";
    echo "<td>";
    $query2="select cb.CB_ID, s.S_CODE, s.S_DESCRIPTION, cb.IBAN, cb.BIC
        from section s, compte_bancaire cb
        where cb.CB_TYPE='S'
        and cb.CB_ID = s.S_ID";
    $result2=mysqli_query($dbc,$query2);
    echo "<select name='compte_a_debiter'  id='compte_a_debiter' style='max-width:360px;font-size:12px;'>";
    while ($row2=@mysqli_fetch_array($result2)) {
        $_CB_ID=$row2["CB_ID"];
        $_IBAN=$row2["IBAN"];
        $_BIC=$row2["BIC"];
        $_S_CODE=$row2["S_CODE"];
        if ( $_CB_ID <= 1 ) $_S_DESCRIPTION = "";
        else $_S_DESCRIPTION="- ".$row2["S_DESCRIPTION"];
        if ( $COMPTE_DEBITE == $_CB_ID ) $selected='selected';
        else $selected='';
        if ( $_IBAN <> "" or $COMPTE_DEBITE == $_CB_ID )
            echo "<option value='$_CB_ID' $selected>$_S_CODE $_S_DESCRIPTION : ".$_BIC." ".display_IBAN($_IBAN)."</option>";
    }
    echo "</select>";
    echo "</td></tr>";
}


if ( $REMBOURSEMENT == 1 ) $t="cr�dit�";
else $t="d�bit�";

echo "<tr bgcolor=$mylightcolor id=ribRow $style><td>Compte ".$t."</td>";
echo "<td ><input name=bic type=text maxlength=11  value='".$BIC."' style='width:100px;font-size:12px;'>
            <input name=iban type=text  maxlength=34  value='".display_IBAN($IBAN)."' style='width:220px;font-size:12px;'>
        </td></tr>";

if ( $REMBOURSEMENT == 1 ) $t="rembours�";
else $t="pay�";
echo "<tr bgcolor=$mylightcolor><td>Montant ".$t."</td>";
if ( $MONTANT == "" ) $DEFMONTANT=0;
else $DEFMONTANT =  $MONTANT;
echo "<td ><input name=montant_paiement type=text size=4 onchange=\"checkFloat(form.montant_paiement,".$DEFMONTANT.");\" value='".$MONTANT."'> ".$default_money_symbol."</td></tr>";

if ( $P_DATE_ENGAGEMENT <> "" and $REMBOURSEMENT == 0 ) {
    echo "<tr bgcolor=$mylightcolor class=small><td align=right>Date entr�e</td>";
    echo "<td >".$P_DATE_ENGAGEMENT."</td></tr>";
}
if ( $P_FIN <> "" and $REMBOURSEMENT == 0) {
    echo "<tr bgcolor=$mylightcolor class=small><td align=right>Date sortie</td>";
    echo "<td >".$P_FIN."</td></tr>";
}


echo "<tr bgcolor=$mylightcolor>
            <td>Date ".$t."</td>
            <td>
            <input type='text' name='date_paiement' size='10' value='".$PC_DATE."' onchange='checkDate2(form.date_paiement)' class='datepicker' data-provide='datepicker'>
            <font size=1><i>JJ-MM-AAAA</i></font></td>";
echo "</tr>";

echo "<tr bgcolor=$mylightcolor>
            <td>Commentaire</td>
            <td>
            <textarea name='commentaire' cols='30' rows='2'
            style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;'
            value=\"$COMMENTAIRE\" >".$COMMENTAIRE."</textarea>
          </td>";        
echo "</tr>";

echo "</table>";// end left table

echo "<input type=hidden name='paiement_id' value='".$paiement_id."'>";
echo "<input type=hidden name='pid' value='".$pid."'>";
if ( $note > 0 ) echo "<input type=hidden name='note' value='".$note."'>";
echo "<input type=hidden name='action' value='".$action."'>";
echo "<p>
<input type='submit' class='btn btn-default' value='sauver'>
<input type='button' class='btn btn-default' value='retour' onclick=\"goback('".$from."');\">
</div></form>";
}
writefoot();
