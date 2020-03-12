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
if ( $SMS_CONFIG[1] == 4 or $SMS_CONFIG[1] == 7 ) $MAX = $maxdestsmsgateway;
else $MAX = $maxdestsms;

writehead();
?>
<script type="text/javascript" src="js/tokeninput/src/jquery.tokeninput.js"></script>
<link rel="stylesheet" href="js/tokeninput/styles/token-input.css" type="text/css" />
<link rel="stylesheet" href="js/tokeninput/styles/token-input-facebook.css" type="text/css" />
 
<script type="text/javascript">
    var MaxNB = <?php echo $maxdestmessage; ?>;
    var MaxSMS = <?php echo $MAX; ?>;
    var MaxWithoutConfirm = 10;
    var MaxcharMail=  <?php echo $maxchar_mail; ?>;
    var MaxcharSMS=  <?php echo $maxchar_sms; ?>;

    function mydisplay(l1,message,mode,compteur,subject) {
        var dests = l1.value;
        var nbdest;
        if ( dests.length == 0 ) nbdest = 0;
        else nbdest = dests.split(",").length;
        if ( message.length == 0 )  {
             alert("Le texte du message est vide");
             return;
        }
        if (dests.length == 0) {
             alert("Aucun destinataire");
             return;
        }
         if (mode[0].checked) {
              choice="mail";
              if (nbdest > MaxNB) {
                   alert("Vous avez choisi d'envoyer un mail à "+ nbdest +" personnes. \n Le maximum autorisé par le menu 'message' est "+ MaxNB+ "\n pour envoyer un message à un plus grand nombre de destinataires, utiliser plutôt le menu 'alerte', qui n'a pas de limitation.");
                 return;
              }
              else if (nbdest > MaxWithoutConfirm) {
                 if ( confirm("Vous allez envoyer un email à "+ nbdest +" personnes.\nContinuer?"))
                   confirmed = 1;
               else return;
            }
         } 
         else {
              if (mode[1].checked) choice="sms";
              else return;
              if (nbdest > MaxSMS) {
                   alert("Vous avez choisi d'envoyer un SMS à "+ nbdest +" personnes. \n Le maximum autorisé est "+ MaxSMS);
                 return;
              }
              if ( compteur.value > MaxcharSMS ) {
                   alert("La longueur des messages SMS est limitée à " + MaxcharSMS + " caractères.\nVous avez: " + compteur.value + " caratères.");
                   return;
              }
              if ( confirm("Vous allez envoyer un SMS à "+ nbdest +" personnes.\nATTENTION l'envoi de ces SMS a un coût.\nContinuer?"))
                   confirmed = 1;
            else return;
         }
        url="mail_send.php?dest="+dests+"&mode="+choice+"&message="+message+"&subject="+subject;
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
echo "<body><div align=center >";
echo "<FORM name='formulaire' id='formulaire'>";

$disabled='disabled';
$credits = get_sms_credits($mysection);
if (( check_rights($_SESSION['id'], 23) ) and ($SMS_CONFIG[1] <> 0)) {
    if ( intval($credits) > 0  or $credits == "Solde illimité" or $credits == "OK" ) $disabled='';
}
$sms_mode = false;
if ( isset($_GET["mode"])) {
    if ($_GET["mode"] == 'sms') $sms_mode = true;
}

$prepopulate="";

if (isset($_POST['SelectionMail'])) {
    $ids = $_POST['SelectionMail'];
    $ids = str_replace(",,",",",$ids);
    $ids = trim($ids, ',');
    $query="select P_ID, upper(P_NOM), P_PRENOM 
            from pompier 
            where P_ID in (".$ids.")
            and P_EMAIL <>'' 
            and P_OLD_MEMBER = 0
            order by P_NOM, P_PRENOM ";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        $prepopulate .= "{id: ".$row[0].", name: \"".$row[1]." ".ucfirst($row[2])."\"},";
    }
    $prepopulate="prePopulate: [ ".rtrim($prepopulate,',')." ],";
}

echo "<font size=4><b> Envoyer un message</b></font>";

echo "<p><TABLE cellspacing=0 border=0 >";
if (isset($_POST['Messagebody']))$msg=$_POST['Messagebody'];
else $msg="";
$msg .="\n\n\n\nEnvoyé par ".$from = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM']))." depuis ".$application_title);
$nbchar=strlen($msg);
echo "<tr bgcolor=$mylightcolor>
           <td>
           <table class='noBorder'>";  
          
echo " <tr bgcolor=$mylightcolor><td align=center ><b>Destinataires <i class='far fa-lightbulb fa-lg' title='Saisissez les premières lettres du nom de chaque destinataire dans le champ ci-dessous'></i></b>
        <input type='text' id='input-facebook-theme' name='liste2' />
        <script type='text/javascript'>
        $(document).ready(function() {
            $(\"#input-facebook-theme\").tokenInput(\"mail_create_input.php\", {
                theme: \"facebook\",
                $prepopulate
                preventDuplicates: true,
                hintText: \"Saisissez les premières lettres du nom\",
                noResultsText: \"Aucun résultat\",
                searchingText: \"Recherche en cours\"
                
            });
        });
        </script>
 </td></tr>";

if ($sms_mode) {
    $style="style='display:none'";
    $sms_selected="selected";
}
else {
    $style="";
    $sms_selected="";
}

echo " <tr id='subjectrow' name='subjectrow' bgcolor=$mylightcolor $style><td align=center >
        Sujet: <input type=texte name='subject' size=60 maxlength='100' style='WIDTH:300px;'padding-left:5px'>
        </td></tr>";
          
echo " <tr>
      <td bgcolor=$mylightcolor align=center>
          <B>Votre message</B>
          <span class=small2>caractères</span> <input type='text' name='comptage' size='1' value='".$nbchar."' readonly=readonly style='FONT-SIZE: 8pt;border:0px;color:$mydarkcolor;background-color:$mylightcolor;'>
          <span id='field1'>/ <input type='text' name='maxchar' id='maxchar' size='1' value='".$maxchar_mail."' readonly=readonly style='FONT-SIZE: 8pt;border:0px;color:$mydarkcolor;background-color:$mylightcolor;'></span>
          <BR>
          <textarea name='mymessage'  rows='12'
            style='FONT-SIZE: 10pt; FONT-FAMILY: Arial; WIDTH: 370px'
              wrap='soft' 
            onFocus='Compter(this,formulaire.comptage)' 
            onKeyDown='Compter(this,formulaire.comptage)' 
            onKeyUp='Compter(this,formulaire.comptage)' 
            onBlur='Compter(this,formulaire.comptage)'>".$msg."</textarea>
      </td>
      </tr></table>
      </td>
      </tr>";


echo "<tr bgcolor=$mylightcolor>
         <td>
         <table class='noBorder'>
         <tr>
            <td bgcolor=$mylightcolor align=center >
            <input type='radio' name='mode' id='mode' value='mail' checked onchange=\"change_type_message();\" />
            <b>e-mail</b>
            <input type='radio' name='mode' id='mode' value='sms' $disabled onchange=\"change_type_message();\" $sms_selected/>
            <b>sms</b>
            <input type='button' value='Envoyer' class='btn btn-primary'
            onclick='mydisplay(this.form.liste2, escape((this.form.mymessage).value),this.form.mode, this.form.comptage, escape((this.form.subject).value))'>
            </td>
        </tr></table>
        </td>
        </tr>";
echo"</TABLE>";
echo "</FORM>";

if ( check_rights($_SESSION['id'], 23)){
    show_sms_account_balance($mysection, $credits);
}
echo "<p><input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">";

if ( $mail_allowed == 0 ) {
   echo " <br><i class='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i> Attention, les mails sont désactivés, cette application n'enverra aucun mail.";
}
echo "</div>";
writefoot();
?>
