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
include_once ("fonctions_sms.php");
check_all(43);
$id=$_SESSION['id'];
$mysection = $_SESSION['SES_SECTION'];
$SMS_CONFIG=get_sms_config($mysection);

get_session_parameters();

if ( isset($_GET["poste"])) $poste=intval($_GET["poste"]); 
else $poste=0;

$highestsection=get_highest_section_where_granted($id,43);
if ( isset($_GET["section"])) $section=intval($_GET["section"]);
else $section=$mysection;

if ( isset($_GET["message"]))  {
    $message=str_replace("</textarea>","",$_GET["message"]);
    $message=urldecode(str_replace('\n','%0A',secure_input($dbc,$message)));
}
else $message="\n\n\n\nEnvoyé par ".$from = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM']))." depuis ".$application_title);
$nbchar=strlen($message);

if ( isset($_GET["dispo"])) $dispo=secure_input($dbc,$_GET["dispo"]); 
else $dispo='0';

writehead();

if ( $dispo == '0' ) {
    if ( $poste <> 0 ) {
    $query="select count(distinct a.P_ID) as NB from pompier a, poste b, qualification c
        where a.P_ID=c.P_ID
        and a.P_OLD_MEMBER = 0
        and a.P_STATUT <> 'EXT'
        and b.PS_ID=c.PS_ID
        and b.PS_ID = $poste 
        and c.Q_VAL > 0
        and (a.P_SECTION in (".get_family("$section").")
             or a.P_ID in (select P_ID from section_role where S_ID in (".get_family("$section")."))
            )
        ";
    }
    else {
     $query="select count(1) as NB from pompier
         where P_OLD_MEMBER = 0
         and P_STATUT <> 'EXT'
        and (P_SECTION in (".get_family("$section").")
             or P_ID in (select P_ID from section_role where S_ID in (".get_family("$section")."))
            )";
    }
}
else {
     if ( $poste <> 0 ) { 
    $query="select count(distinct a.P_ID) as NB from pompier a, poste b, qualification c, disponibilite d
        where a.P_ID=c.P_ID
        and d.P_ID = a.P_ID
        and a.P_OLD_MEMBER = 0
        and a.P_STATUT <> 'EXT'
        and b.PS_ID=c.PS_ID
        and b.PS_ID = $poste 
        and d.D_DATE = '".$dispo."'
        and c.Q_VAL > 0
        and (P_SECTION in (".get_family("$section").")
             or a.P_ID in (select P_ID from section_role where S_ID in (".get_family("$section")."))
            )";
    }
    else {
     $query="select count(distinct p.P_ID) as NB from pompier p, disponibilite d
         where d.P_ID =p.P_ID
         and p.P_OLD_MEMBER = 0
         and p.P_STATUT <> 'EXT'
         and d.D_DATE = '".$dispo."'
        and (P_SECTION in (".get_family("$section").")
            or p.P_ID in (select P_ID from section_role where S_ID in (".get_family("$section")."))
        )";
    }
}

$year=date("Y");
$year='';
$month=date("m");
$day=date("d");


$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$NB=$row["NB"];

$credits = get_sms_credits($mysection);

?>
<SCRIPT LANGUAGE="JavaScript">
    var MaxcharMail=  <?php echo $maxchar_mail; ?>;
    var MaxcharSMS=  <?php echo $maxchar_sms; ?>;
    
    function displaymanager(p1,p2,p3,p4){
     self.location.href="alerte_create.php?poste="+p1+"&section="+p2+"&dispo="+p3+"&message="+p4;
     return true
    }
    
    function envoyer(message,mode,poste,section,dispo,compteur,subject) {
        if ( message.length == 0 )  {
             alert("Le texte du message est vide");
             return;
        }
         if (mode[0].checked) {
              choice="mail";
              if ( confirm("Vous allez envoyer un email à "+ <?php echo $NB ?> +" personnes.\nContinuer?"))
                   confirmed = 1;
            else return;
         } 
         else {
              if (mode[1].checked) choice="sms";
              else return;
              //choice = sms
              credits = <?php echo "'".$credits."'" ?> ;
             if ( credits == 'ERREUR' ) {
                   alert("Vous n'avez pas de crédits SMS.");
                   return;
              }
              if ( credits == '0' ) {
                   alert("Vous n'avez plus de crédits SMS.");
                   return;
              }
              if ( compteur.value > MaxcharSMS ) {
                   alert("La longueur des messages SMS est limitée à " + MaxcharSMS + " caractères.\nVous avez: " + compteur.value + " caratères.");
                   return;
              }
              if ( confirm("Vous allez envoyer un SMS à "+ <?php echo $NB ?> +" personnes.\nATTENTION l'envoi de ces SMS a un coût.\nContinuer?"))
                   confirmed = 1;
            else return;
         }
        url="alerte_send.php?poste="+poste+"&section="+section+"&mode="+choice+"&dispo="+dispo+"&message="+message+"&subject="+subject;
         self.location.href=url;
    }
    
    function Compter(Target, nomchamp) {
        var max = MaxcharMail;
        if (document.forms.formulaire.mode[1].checked==true) {
            var max = MaxcharSMS;
        }
        StrLen = Target.value.length
        if (StrLen > max ) {
            Target.value = Target.value.substring(0,max);
            CharsLeft = max;
        }
        else
        {
            CharsLeft = StrLen;
        }    
        nomchamp.value = CharsLeft;
    }
    
    function change_type_message() {    
        var row1=document.getElementById('subjectrow');
        var txt_field1=document.getElementById('maxchar');
        if (document.forms.formulaire.mode[1].checked==true) {
            row1.style.display = 'none';
            txt_field1.value = MaxcharSMS;
        }
        else { 
            row1.style.display = '';
            txt_field1.value = MaxcharMail;
        }
    }
    
    function redirect() {
        url = "index_d.php";
        self.location.href = url;
    }

</SCRIPT>
</HEAD>
<?php
echo "<body><div align=center>";
echo "<FORM name='formulaire' id='formulaire'>";
$disabled='disabled';

if (( check_rights($_SESSION['id'], 23)) and ( $SMS_CONFIG[1] > 0)) {
    if ( intval($credits) > 0  or $credits == "Solde illimité" or $credits == "OK" ) $disabled='';
}

echo "<font size=4><b> Alerter le personnel</b></font>";
echo "<p><TABLE cellspacing=0 border=0><tr bgcolor=$mylightcolor >
        <td>
        <table class='noBorder'>";
if ( $competences == 1 ){
    echo "<tr>
        <TD align='right' width=150><B>Qualification</B></td>";
      echo "<td align=left>
      <select id='menu1' name='menu1' style='max-width:250px;font-size: 12px;'
      onchange=\"displaymanager(document.getElementById('menu1').value,'".$section."','".$dispo."',escape((this.form.mymessage).value));\">
      <option value='0'>toutes qualifications</option>";
        $query2="select p.PS_ID, p.DESCRIPTION, e.EQ_NOM, e.EQ_ID from poste p, equipe e 
           where p.EQ_ID=e.EQ_ID
           order by p.EQ_ID, p.PS_ORDER";
        $result2=mysqli_query($dbc,$query2);
        $prevEQ_ID=0;
        while ($row=@mysqli_fetch_array($result2)) {
              $PS_ID=$row["PS_ID"];
              $EQ_ID=$row["EQ_ID"];
              $EQ_NOM=$row["EQ_NOM"];
              if ( $prevEQ_ID <> $EQ_ID ) echo "<OPTGROUP LABEL='".$EQ_NOM."'>";
              $prevEQ_ID=$EQ_ID;
              $DESCRIPTION=$row["DESCRIPTION"];
              if ($PS_ID == $poste ) $selected='selected';
              else $selected='';
              echo "<option value='".$PS_ID."' $selected>".$DESCRIPTION."</option>\n";
        }
        echo "</select>";
    echo "</td></tr>";
}
else echo "<input type='hidden' name='menu1' id='menu1' value='0'>";

echo "<tr><td align='right'>";
echo "<B>Personnel de</B></td>";
echo "<td align=left><select id='menu2' name='menu2'  style='max-width:250px;font-size: 12px;'
    onchange=\"displaymanager('".$poste."',document.getElementById('menu2').value,'".$dispo."',escape((this.form.mymessage).value));\">";    

if ( $highestsection == '' ) $highestsection=$mysection;
if ( check_rights($_SESSION['id'], 24) or $nbsections > 0 )
       display_children2(-1, 0, $section, $nbmaxlevels, $sectionorder);
else  {
    // montrer ma section
    if ( $highestsection <> $mysection ) {
        $family=explode(',',get_family($highestsection));
        if ( ! in_array($mysection,$family)) {
            $level=get_level($mysection);
            if ( $level == 0 ) $mycolor=$myothercolor;
            elseif ( $level == 1 ) $mycolor=$my2darkcolor;
            elseif ( $level == 2 ) $mycolor=$my2lightcolor;
            elseif ( $level == 3 ) $mycolor=$mylightcolor;
            else $mycolor='white';
            $class="style='background: $mycolor;'";
            if ( $section == $highestsection ) $selected='selected'; else $selected='';
            echo "<option value='$mysection' $class $selected>".get_section_code($mysection)." - ".get_section_name($mysection)."</option>";
            display_children2($mysection, $level +1, $section, $nbmaxlevels);
        }
    }
    // montrer mon niveau max
    $level=get_level($highestsection);
    if ( $level == 0 ) $mycolor=$myothercolor;
    elseif ( $level == 1 ) $mycolor=$my2darkcolor;
    elseif ( $level == 2 ) $mycolor=$my2lightcolor;
    elseif ( $level == 3 ) $mycolor=$mylightcolor;
    else $mycolor='white';
    $class="style='background: $mycolor;'";
    if ( $section == $highestsection ) $selected='selected'; else $selected='';
       echo "<option value='$highestsection' $class $selected>".get_section_code($highestsection)." - ".get_section_name($highestsection)."</option>";
    display_children2($highestsection, $level +1, $section, $nbmaxlevels);
}
echo "</select></td></tr>";

if ( $disponibilites == 1 ) {
    echo "<tr><td align='right' >";
    echo "<B>Disponibilité</B></td>";
    echo "<td align=left> ";
    echo " <select id='menu3' name='menu3' style='max-width:250px;font-size: 12px;'
    onchange=\"displaymanager('".$poste."','".$section."',document.getElementById('menu3').value,escape((this.form.mymessage).value));\"
     >
        <option value='0'> disponibles ou pas</option>";

    $m0=date("n");
    $y0=date("Y");
    $d0=date("d");
    for ($i=0; $i < 15 ; $i++) {
        $udate=mktime (0,0,0,$m0,$d0,$y0) + $i * 24 * 60 * 60;
        $year = date ( "Y", $udate);
        $month = date ( "m", $udate);
        $day = date ( "j", $udate);
        if ( $day < 10 ) $day = "0".$day;
        $mydate =$year."-".$month."-".$day;
        if ( "$dispo" == "$mydate" ) $selected = 'selected';
        else $selected = '';
        echo "<option value='".$mydate."' $selected>".$day." ".$mois[$month - 1]." ".$year."</option>";
    }
    echo "    </select>";
    echo "</td></tr>";
}
else echo "<input type='hidden' name='menu3' id='menu3' value='0'>";
        
        

echo "<tr><td align='right' >";
echo "<B>Nombre d'agents:</B></td>";
echo "<td align=left> ";    
print write_modal("destinataires.php?section=".$section."&poste=".$poste."&dispo=".$dispo, 'desti', "<span class='badge' style='background-color:purple;' title='cliquer pour voir les destinataires'>$NB</span>");     
echo "</td>";
    
echo "</tr></table></td>
    </tr>";
echo "<tr bgcolor=$mylightcolor>
         <td>
         <table class='noBorder'>
        <tr id='subjectrow' name='subjectrow' bgcolor=$mylightcolor>
            <td align=center ><b>Sujet <input type=texte name='subject'  maxlength='100' style='WIDTH: 340px;'  ></td>
        </tr>
         <tr>
         <td bgcolor=$mylightcolor align=center>
          <B>Votre message</B>
          <span class=small2>caractères</span> <input type='text' name='comptage' size='1' value='".$nbchar."' readonly=readonly style='FONT-SIZE: 8pt;border:0px;color:$mydarkcolor;background-color:$mylightcolor;'>
          <span id='field1'>/ <input type='text' name='maxchar' id='maxchar' size='1' value='".$maxchar_mail."' readonly=readonly style='FONT-SIZE: 8pt;border:0px;color:$mydarkcolor;background-color:$mylightcolor;'></span>
          <BR>
          <textarea name='mymessage'  rows='12' 
            style='FONT-SIZE: 10pt; FONT-FAMILY: Arial; WIDTH: 400px;'
              wrap='soft' 
            onFocus='Compter(this,formulaire.comptage)' 
            onKeyDown='Compter(this,formulaire.comptage)' 
            onKeyUp='Compter(this,formulaire.comptage)' 
            onBlur='Compter(this,formulaire.comptage)'>".$message."</textarea>
        </td>
        </tr>
        </table>
      </td>
    </tr>";
      
$disabled2='';
if ( $NB == 0 ) $disabled2='disabled';

echo "<tr bgcolor=$mylightcolor>
           <td>
           <table class='noBorder'>
           <tr><td bgcolor=$mylightcolor align=center >
          <input type='radio' name='mode' id='mode' value='mail' checked onchange=\"change_type_message();\" />
            <b>e-mail</b>
            <input type='radio' name='mode' id='mode' value='sms' $disabled onchange=\"change_type_message();\" />
            <b>sms</b>
          <input type='button' class='btn btn-default' value='Envoyer' $disabled2
          onclick=\"envoyer(escape((this.form.mymessage).value),this.form.mode,'".$poste."','".$section."','".$dispo."', this.form.comptage, escape((this.form.subject).value))\">
      </td>
      </tr></table>
      </td>
      </tr>";
echo"</TABLE>";
echo "</FORM>";

if ( check_rights($_SESSION['id'], 23)){
    show_sms_account_balance($mysection, $credits);
}
if ( $mail_allowed == 0 ) {
   echo "<p><i class='fa fa-exclamation-triangle fa-lg'></i> Attention, les mails sont désactivés, cette application n'enverra aucun mail.";
}
echo "<p><input type='button'  class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">";
echo "</div>";
writefoot();
?>
