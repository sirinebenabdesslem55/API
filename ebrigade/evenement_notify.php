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
writehead();
$id=$_SESSION['id'];

$action=secure_input($dbc,$_GET["action"]);
$evenement=$_GET["evenement"];
// check input parameters
$evenement=intval(secure_input($dbc,$evenement));
if ( $evenement == 0 ) {
    param_error_msg();
    exit;
}
?>

<SCRIPT>
function redirect(evenement, from) {
         url = "evenement_display.php?evenement="+evenement+"&from="+from;
         self.location.href = url;
}
</SCRIPT>
<?php

$query="select E.E_CODE, EH.EH_ID _EH_ID, E.S_ID,E.TE_CODE, TE.TE_LIBELLE, E.E_LIEU, EH.EH_DATE_DEBUT _EH_DATE_DEBUT, EH.EH_DATE_FIN _EH_DATE_FIN,
    TIME_FORMAT(EH.EH_DEBUT, '%k:%i') as _EH_DEBUT, S.S_CODE, E_PARENT, E_EXTERIEUR, E_VISIBLE_INSIDE,
                TIME_FORMAT(EH.EH_FIN, '%k:%i') as _EH_FIN, S.S_EMAIL, S.S_EMAIL2, S.S_EMAIL3, E.TAV_ID,
                E.E_NB, E.E_COMMENT, E.E_LIBELLE, S.S_DESCRIPTION, E.E_CLOSED, E.E_CANCELED, E.E_CANCEL_DETAIL, 
    E.E_HEURE_RDV, E.E_LIEU_RDV
    from evenement E, type_evenement TE, section S, evenement_horaire EH
    where E.TE_CODE=TE.TE_CODE
    and E.E_CODE = EH.E_CODE
    and S.S_ID=E.S_ID
    and E.E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);

$EH_ID= array();
$EH_DEBUT= array();
$EH_DATE_DEBUT= array();
$EH_DATE_FIN= array();
$EH_FIN= array();
$EH_DUREE= array();
$horaire_evt= array();
$date1=array();
$month1=array();
$day1=array();
$year1=array();
$date2=array();
$month2=array();
$day2=array();
$year2=array();
$i=1;

while ( custom_fetch_array($result)) {
    if ( $i == 1 ) {
        $E_HEURE_RDV=substr($E_HEURE_RDV,0,5);   
        $need_check= array('created','enroll','closed','canceled');
        if (in_array($action, $need_check)) {
                if (! check_rights($id, 15, "$S_ID") and ! is_chef_evenement($id, $evenement)) {
                        check_all(15);
                        check_all(24);
                }
        }
    }

    $EH_DEBUT[$i]=$_EH_DEBUT;
    $EH_DATE_DEBUT[$i]=$_EH_DATE_DEBUT;
    $EH_DATE_FIN[$i]=$_EH_DATE_FIN;
    $EH_FIN[$i]=$_EH_FIN;
    $EH_ID[$i]=$_EH_ID;

    $tmp=explode ( "-",$EH_DATE_DEBUT[$i]); $year1[$i]=$tmp[0]; $month1[$i]=$tmp[1]; $day1[$i]=$tmp[2];
    $date1[$i]=mktime(0,0,0,$month1[$i],$day1[$i],$year1[$i]);
    if (( $EH_DATE_FIN[$i] <> '' ) and ( $EH_DATE_FIN[$i] <> $EH_DATE_DEBUT[$i] )) {
            $tmp=explode ( "-",$EH_DATE_FIN[$i]); $year2[$i]=$tmp[0]; $month2[$i]=$tmp[1]; $day2[$i]=$tmp[2];
            $date2[$i]=mktime(0,0,0,$month2[$i],$day2[$i],$year2[$i]);
            $infos_dates[$i] = "date: du ".date_fran($month1[$i], $day1[$i] ,$year1[$i])." ".moislettres($month1[$i])." ".$year1[$i]." � ".$EH_DEBUT[$i];
            $infos_dates[$i] .= " au ".date_fran($month2[$i], $day2[$i] ,$year2[$i])." ".moislettres($month2[$i])." ".$year2[$i]." � ".$EH_FIN[$i]."\n";
    }
    else {
            $infos_dates[$i] = "date: le ".date_fran($month1[$i], $day1[$i] ,$year1[$i])." ".moislettres($month1[$i])." ".$year1[$i]." de ".$EH_DEBUT[$i]." � ".$EH_FIN[$i]."\n";
    }
    $i++;
}

$nb=0;$nb2=0;
$subject=$TE_LIBELLE.":  ".$E_LIBELLE;

$url=get_plain_url($cisurl);
$siteurl = "http://".$url."/index.php?evenement=".$evenement;

$message_desc1  = "<a href=".$siteurl." title='cliquer pour voir le d�tail'>".$TE_LIBELLE." : ".$E_LIBELLE."</a>.\n\n";
$message_desc1 .= "organis� par: ".$S_CODE." - ".$S_DESCRIPTION."\n";
$message_desc1 .= "lieu: ".$E_LIEU.".\n";

if ( $E_PARENT <> '' ) {
        $S2=get_section_organisatrice("$E_PARENT");
        $message_desc1 .= "renfort pour: ";
        $message_desc1 .= get_section_code("$S2")." - ".get_section_name("$S2")."\n";
}

$chefs=get_chefs_evenement($evenement);
$chefs_parent=get_chefs_evenement($E_PARENT);

$message_desc = $message_desc1;

if ( $gardes == 1 and $TE_CODE == 'GAR' ) {
        if (isset($day1[1]))
                $message_desc .= "date: le ".date_fran($month1[1], $day1[1] ,$year1[1])." ".moislettres($month1[1])." ".$year1[1]."\n";
}
else {
        for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
                if (isset($infos_dates[$i]))
                        $message_desc .= $infos_dates[$i];
        }
}
        
if ( $E_NB == 0 ) 
    $message_desc .= "personnes requises: pas de limite\n";
else
    $message_desc .= "personnes requises: ".$E_NB.".\n";

if ( $E_HEURE_RDV <> '' ) 
$message_desc .= "heure de rendez-vous: ".$E_HEURE_RDV."\n";
if ( $E_LIEU_RDV <> '' ) 
$message_desc .= "lieu de rendez-vous: ".$E_LIEU_RDV."\n";

if ( $E_COMMENT <> '' ) 
$message_desc .= "commentaire: ".$E_COMMENT."\n";

$sp=get_section_parent("$S_ID");

$admins=get_granted(21,"$S_ID",'local','yes');
$adminsparent=get_granted(21,"$sp",'local','yes');
$veille=false;
$secretariat=false;
$mail_formation=false;
$destid="";
$message_complement="";

$SenderName = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
$SenderMail = $_SESSION['SES_EMAIL'];
$COLMAIL='NO';

if ( $action == 'created' and $E_VISIBLE_INSIDE == 1 ) {
    $subject="cr�ation - ".$subject;
    $ttte="";
    if ( $TE_CODE == 'DPS' and $TAV_ID == 5) $ttt="un nouveau DPS de grande envergure";
    else if ( $TE_CODE == 'DPS') $ttt="un nouveau DPS";
    else if ( $TE_CODE == 'ALERT') {
        $ttt="une nouvelle alerte des b�n�voles";
        $ttte="e";
    }
    else  $ttt="un nouvel �v�nement";
    if ( $E_EXTERIEUR == 1 ) {
        $ttt .= " hors d�partement";
        $subject .= " hors d�partement";
    }
    $message = "Bonjour,\n
Pour information, ".$ttt." vient d'�tre cr��".$ttte.":\n";
    $message .= $message_desc;
    if (( $S_EMAIL <> "" ) and ( $TE_CODE == 'DPS' or $TE_CODE == 'ALERT' )) $veille=true;
    if ( $S_EMAIL3 <> "" and  $TE_CODE == 'FOR') $mail_formation=true;
    if ( $S_EMAIL2 <> "" ) $secretariat=true;
    $destid = $admins;
    if ( count($chefs) > 0 )  $destid .= ",".implode(",",$chefs);
    // si evenement sur antenne locale , prevenir aussi le departement
    if ( get_children("$S_ID") == '' ) $destid .= ",".$adminsparent;
    // si renfort, prevenir les responsables de l'�v�nement principal
    if ( $E_PARENT <> '' ) {
        if ( count($chefs_parent) > 0 ) $destid .= ",".implode(",",$chefs_parent);
        $destid .= ",".get_granted(21,"$S2",'local','yes');
    }
    // si DPS GE, ou alerte des b�n�voles ou hors d�partement pr�venir le niveau national et toujours le niveau parent
    if ( $TAV_ID == 5 or $TE_CODE == 'ALERT' or $E_EXTERIEUR == 1 ) {
        $destid .= ",".get_granted(21,0,'local','yes');
        $destid .= ",".$adminsparent;
    }
}

if ( $action == 'enroll' and $E_VISIBLE_INSIDE == 1 ) {
    $COLMAIL='E_MAIL1';
    $subject="inscriptions ouvertes - ".$subject;
    $message = "Bonjour,\n
Tu peux d�s maintenant t'inscrire pour:\n";
    $message .= $message_desc;
    $destid=get_granted(39,"$S_ID",'tree','yes').",".$admins;
}

if ( $action == 'closed' ) {
    $COLMAIL='E_MAIL2';
    $subject="validation - ".$subject;
    $message = "Bonjour,\n
Voici la liste des personnes retenues:\n";
    $message .= get_noms_inscrits($evenement);
    $message .="\nPour participer �:\n";
    $message .= $message_desc;
    $message .= get_vehicules_inscrits($evenement);
    if (( $S_EMAIL <> "" ) and ( $TE_CODE == 'DPS' )) $veille=true;
    if ( $S_EMAIL3 <> "" and  $TE_CODE == 'FOR') $mail_formation=true;
    if ( $S_EMAIL2 <> "" ) $secretariat=true;
    $destid=get_inscrits($evenement,'no').",".$admins;
    if ( count($chefs) > 0 ) $destid .= ",".implode(',',$chefs);
    
    // notifier aussi les responsables des renfort
    $renforts = get_renforts($evenement);
    $nb_renforts = count($renforts);
    if ( $nb_renforts > 0  ) {
        $destid_r  = "";
        $query2="select distinct E_CHEF from evenement_chef where E_CODE in (".implode(',',$renforts).")";
        $result2=mysqli_query($dbc,$query2);
        $nb_resp = mysqli_num_rows($result2);
        while ( custom_fetch_array($result2)) {
            $destid_r .= $E_CHEF.",";
        }
        $destid_r = rtrim($destid_r,",");
        if ( $destid_r <> '' ) {
            $message_r = "Bonjour,\n\n";
            $message_r .= "L'�v�nement principal auquel est rattach� votre renfort est maintenant valid�:\n";
            $message_r .= $message_desc; 
            $message_r .= "Merci de valider le renfort pour lequel vous �tes d�sign� responsable, et de ne plus modifier la liste des inscrits.";
            $nb2 = mysendmail("$destid_r" , $id , "$subject" , "$message_r" );
            if ( $nb_resp > 1 ) $s1="s"; else $s1="";
            if ( $nb_renforts > 1 ) $s2="s"; else $s2="";           
            $message_complement = "\nDe plus, un message sp�cifique a �t� envoy� � ".$nb_resp." responsable".$s1." enregistr�".$s1." sur ".$nb_renforts." renfort".$s2.".";
        }
    }
}


if ( $action == 'canceled' ) {
    $COLMAIL='E_MAIL3';
    $subject="annulation - ".$subject;
    $message = "Bonjour,\n
L'�v�nement suivant a �t� annul� (".$E_CANCEL_DETAIL."):\n";
    $message .= $message_desc;
    $destid = get_inscrits($evenement,$includecanceledevents= 'yes').",".$admins;
    if ( count($chefs) > 0 ) $destid .= ",".implode(',',$chefs);
    if (( $S_EMAIL <> "" ) and ( $TE_CODE == 'DPS' )) $veille=true;
    if ( $S_EMAIL3 <> "" and  $TE_CODE == 'FOR') $mail_formation=true;
    if ( $S_EMAIL2 <> "" ) $secretariat=true;
}

if ( $action == 'desinscrit'  and isset ($_GET["P_ID"])) {
    $COLMAIL='NO';
    $P_ID=intval($_GET["P_ID"]);
    $query="select P_PRENOM, P_NOM from pompier where P_ID = ".$P_ID;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $prenom = my_ucfirst($row["P_PRENOM"]);
    $nom = strtoupper($row["P_NOM"]);
    // notifier la personne qui est d�sinscrite et �ventuellement le chef
    $subject="Participation annulee - ".$subject;
    $message = "Bonjour,\n
La participation de ".$prenom." ".$nom." a �t� annul�e pour:\n";
    $message .= $message_desc;
    if ( $E_VISIBLE_INSIDE == 0 ) $destid='';
    else {
        $destid = $P_ID;
        if ( $E_CLOSED == 1 ) {
                //notifier le chef si une personne est d�sinscrite alors que l'�v�nement est cl�tur�
                if ( count($chefs) > 0 ) $destid .=','.implode(',',$chefs);
                if ( count($chefs_parent) > 0 )  $destid .= ",".implode(',',$chefs_parent);
        }
    }
}

if ( $action == 'absent'  and isset ($_GET["P_ID"])) {
    $COLMAIL='NO';
    $P_ID=intval($_GET["P_ID"]);
    $subject="Absence signal�e - ".$subject;
    $query="select P_PRENOM, P_NOM, P_SEXE from pompier where P_ID = ".$P_ID;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
      $prenom = my_ucfirst($row["P_PRENOM"]);
      $nom = strtoupper($row["P_NOM"]);
    $sexe = $row["P_SEXE"];
    if ( $sexe == 'M' ) $absent='absent';
    else $absent='absente';
    $message = "Bonjour,\n".
$prenom." ".$nom." sera ".$absent." pour:\n";
    $message .= $message_desc1;
    for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
            if (isset($infos_dates[$i])) {
                    $query2="select EP_ABSENT from evenement_participation where E_CODE=".$evenement." and EH_ID=".$EH_ID[$i]." and P_ID=".$P_ID;
                    $result2=mysqli_query($dbc,$query2);
                    $row2=mysqli_fetch_array($result2);
                    if ( $row2[0] == 1 ) 
                            $message .= $infos_dates[$i]."\n";
            }
    }

    $destid=$P_ID;
    if ( count($chefs) > 0 ) $destid .= ",".implode(',',$chefs);
    if ( count($chefs_parent) > 0 ) $destid .= ",".implode(',',$chefs_parent);
}

if (( $action == 'inscription' ) and isset ($_GET["P_ID"])) {
    $COLMAIL='NO';
    $destid='';
    $P_ID=intval($_GET["P_ID"]);
    $query="select ep.EP_FLAG1, p.P_PRENOM, p.P_NOM, p.P_STATUT, p.TS_CODE, p.P_SECTION, p.P_EMAIL, s.S_EMAIL2, s.S_CODE
            from evenement_participation ep, pompier p, section s 
            where ep.E_CODE=".$evenement." 
            and p.P_ID = ep.P_ID 
            and s.S_ID = p.P_SECTION
            and p.P_ID = ".$P_ID;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $prenom = my_ucfirst($row["P_PRENOM"]);
    $statut_of=$row["P_STATUT"];
    if ( $row["TS_CODE"] == 'SC' ) $SC=True;
    else $SC=False;
    $usersection=$row["P_SECTION"];
    $usersectioncode = $row["S_CODE"];
    $nom = strtoupper($row["P_NOM"]);
    $S_EMAIL2 = $row["S_EMAIL2"];

    // cas inscription d'un salari�, notifier ses responsables en indiquant son statut
    if ( $statut_of == 'SAL' ) {
        if ( get_level("$usersection") == $nbmaxlevels - 1 )  $level = 'parent';
        else $level = 'local';
        $destid=get_granted(13,"$usersection",$level,'yes');
        if ($row["EP_FLAG1"] == 1 ) {
            if ( $SC ) $as='service civique';
            else $as='salari�(e)';
        }
        else if ( $syndicate == 1 )  $as='adh�rent';
        else $as='b�n�vole';
        $subject="inscription en tant que ".$as." de ".$prenom." ".$nom;
        $message = "Bonjour,\n
Pour information, ".$prenom." ".$nom."\nvient de s'inscrire en tant que ".$as." pour:\n";
        $message .= $message_desc;
        if ( $destid <> '' ) $nb = mysendmail("$destid" , $id , "$subject" , "$message" );
        $destid='';
        if ( $S_EMAIL2 <> "" ) $nb2 = mysendmail2("$S_EMAIL2","$subject","$message",$SenderName,$SenderMail);
    }
    if ( $E_PARENT <> '' ) {
        // cas inscription sur un renfort alors que evenement principal cl�tur�
        // notifier responsable evenement principal
        $query="select E_CLOSED from evenement where E_CODE=".$E_PARENT;
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $E_CLOSED=$row["E_CLOSED"];
        if ( $E_CLOSED == 1 and count($chefs_parent) > 0 ) {
            $subject="inscription - ".$subject;
            $message = "Bonjour,\n
Pour information,".$prenom." ".$nom."
              \n vient de s'inscrire � un renfort pour un �v�nement principal d�j� cl�tur�:\n";
            $message .= $message_desc;
            $destid = implode(',',$chefs_parent);
            if ( $destid <> '' ) $nb = mysendmail("$destid" , $id , "$subject" , "$message" );
            $destid='';
        }
    }
    // si un agent s'inscrit pour un �v�nement ext�rieur � sa section ou � la section n+1, et qu'il
    // est plus bas dans la hi�rarchie que la section organisatrice 
    // alors on notifie son chef de section
    // sauf cas gardes SP, on ne notifie pas
    if ( $usersection <> $S_ID  and ( $gardes == 0 or $TE_CODE <> 'GAR')) {
        if (  get_section_parent($usersection) <> $S_ID 
            and ( get_level($usersection) >= get_level("$S_ID"))) {
            $subject="inscription - ".$subject;
            $message = "Bonjour,\n
Pour information, ".$prenom." ".$nom." (".$usersectioncode."),
\nvient de s'inscrire pour participer � un �v�nement ext�rieur:\n";
            $message .= $message_desc;
            $destid = get_granted(21,"$usersection",'parent','yes');
            if ( $S_EMAIL2 <> "" ) $nb2 = mysendmail2("$S_EMAIL2","$subject","$message",$SenderName,$SenderMail);
        }
    }
}

if ( $COLMAIL <> 'NO' ) {
    $query="update evenement set ".$COLMAIL."=1 where E_CODE=".$evenement ;
    $result=mysqli_query($dbc,$query);
}

if ( $secretariat )
    $nb2 = mysendmail2("$S_EMAIL2","$subject","$message",$SenderName,$SenderMail);
if ( $veille )
    $nb2 = mysendmail2("$S_EMAIL","$subject","$message",$SenderName,$SenderMail);
if ( $mail_formation )
    $nb2 = mysendmail2("$S_EMAIL3","$subject","$message",$SenderName,$SenderMail);
if ( $destid <> '' )
    $nb = mysendmail("$destid" , $_SESSION['id'] , "$subject" , "$message" );
        
if ( $COLMAIL <> 'NO' ) {
    if ( $nb2 == 1 ) $addthis="<br>Et � cette adresse aussi: ".$S_EMAIL;
    else $addthis="";
    write_msgbox("OK", $star_pic, "Le message suivant a �t� envoy� �: ".$nb." personnes.".$addthis."<p><font face=courrier size=1>Sujet:[".$cisname."] ".$subject."<p>".nl2br($message.$message_complement)."</font><p align=center><a href=evenement_display.php?evenement=".$evenement."&from=choice><input type='submit' class='btn btn-default' value='Retour'></a>",30,0);
}
else {
    if ( $action <> 'created' ) $action='inscription';
        echo "<body onload=redirect('".$evenement."','".$action."');>";
}

writefoot();
?>
