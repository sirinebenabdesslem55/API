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
destroy_my_session_if_forbidden($id);
$SES_NOM=$_SESSION['SES_NOM'];
$SES_PRENOM=$_SESSION['SES_PRENOM'];
$SES_GRADE=$_SESSION['SES_GRADE'];

$nbjours=intval($_POST["nbjours"]);
$month=intval($_POST["month"]);
$year=intval($_POST["year"]);
$person=intval($_POST["person"]);
if (get_matricule($person) == '' ) {
    param_error_msg();
    exit;
}

$hissection=get_section_of($person);

if ( $id <> $person ) {
 check_all(10);
 if (! check_rights($id, 10, $hissection)) check_all(24);
}


//=====================================================================
// purger les disponibilit�s de la personne pour le mois en cours
//=====================================================================

$query="delete from disponibilite
        where P_ID=".$person."
        and D_DATE>='".$year."-".$month."-01'
        and D_DATE<='".$year."-".$month."-".$nbjours."'
        and D_DATE >= '".date('Y-m-d')."'";
$result=mysqli_query($dbc,$query);


//=====================================================================
// enregistrer les disponibilit�s saisies
//=====================================================================
//echo "<pre>";
//print_r($_POST);
//echo "</pre>";

$query="select P_NOM,P_PRENOM, P_EMAIL from pompier where P_ID=".$person;
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$prenom=my_ucfirst($row["P_PRENOM"]);
$nom=strtoupper($row["P_NOM"]);
$email = $row["P_EMAIL"];

$i=1;
while ( $i <= $nbjours ) {
    for ( $z = 1; $z <= 4; $z++ ) {
        if ( isset($_POST[$z."_".$i])) {
            $query="insert into disponibilite (P_ID, D_DATE, PERIOD_ID) values ( ".$person.", '".$year."-".$month."-".$i."', ".$z.")";
            $result=mysqli_query($dbc,$query);
        }
    }
    $i=$i+1;
}

$dispos=array();
$dispos[1]=0;
$dispos[2]=0;
$dispos[3]=0;
$dispos[4]=0;
$query="select PERIOD_ID, count(*) as NB from disponibilite
        where P_ID=".$person."
        and D_DATE>='".$year."-".$month."-01'
        and D_DATE<='".$year."-".$month."-".$nbjours."'
        group by PERIOD_ID";
$result=mysqli_query($dbc,$query);
while ( $row=@mysqli_fetch_array($result)) {
    $dispos[$row[0]]=$row[1];
}

if ( $dispo_periodes == 1 ) {
    $detail = $dispos[1]." p�riodes de 24h";
    // recopier dispo periode 1 sur 2, 3 et 4
    $query="insert into disponibilite (P_ID, D_DATE, PERIOD_ID) 
            select d.P_ID, d.D_DATE, 2
            from disponibilite d
            where d.PERIOD_ID = 1
            and d.P_ID = ".$person."
            and D_DATE>='".$year."-".$month."-01'
            and D_DATE<='".$year."-".$month."-".$nbjours."'
            and D_DATE >= '".date('Y-m-d')."'";
    $result=mysqli_query($dbc,$query);
    $query="insert into disponibilite (P_ID, D_DATE, PERIOD_ID) 
            select d.P_ID, d.D_DATE, 3
            from disponibilite d
            where d.PERIOD_ID = 1
            and d.P_ID = ".$person."
            and D_DATE>='".$year."-".$month."-01'
            and D_DATE<='".$year."-".$month."-".$nbjours."'
            and D_DATE >= '".date('Y-m-d')."'";
    $result=mysqli_query($dbc,$query);
    $query="insert into disponibilite (P_ID, D_DATE, PERIOD_ID) 
            select d.P_ID, d.D_DATE, 4
            from disponibilite d
            where d.PERIOD_ID = 1
            and d.P_ID = ".$person."
            and D_DATE>='".$year."-".$month."-01'
            and D_DATE<='".$year."-".$month."-".$nbjours."'
            and D_DATE >= '".date('Y-m-d')."'";
    $result=mysqli_query($dbc,$query);
}
else if ( $dispo_periodes == 2 ) {
    $detail = $dispos[1]." jours et ".$dispos[4]." nuits";
    // recopier dispo periode 1 sur 2 
    $query="insert into disponibilite (P_ID, D_DATE, PERIOD_ID) 
            select d.P_ID, d.D_DATE, 2
            from disponibilite d
            where d.PERIOD_ID = 1
            and d.P_ID = ".$person."
            and D_DATE>='".$year."-".$month."-01'
            and D_DATE<='".$year."-".$month."-".$nbjours."'
            and D_DATE >= '".date('Y-m-d')."'";
    $result=mysqli_query($dbc,$query);
    // recopier dispo periode 4 sur 3
    $query="insert into disponibilite (P_ID, D_DATE, PERIOD_ID) 
            select d.P_ID, d.D_DATE, 3
            from disponibilite d
            where d.PERIOD_ID = 4
            and d.P_ID = ".$person."
            and D_DATE>='".$year."-".$month."-01'
            and D_DATE<='".$year."-".$month."-".$nbjours."'
            and D_DATE >= '".date('Y-m-d')."'";
    $result=mysqli_query($dbc,$query);
}
else if ( $dispo_periodes == 3 ) {
    $detail = $dispos[1]." matins, ".$dispos[2]." apr�s-midis et ".$dispos[4]." nuits";
    // recopier dispo periode 4 sur 3
    $query="insert into disponibilite (P_ID, D_DATE, PERIOD_ID) 
            select d.P_ID, d.D_DATE, 3
            from disponibilite d
            where d.PERIOD_ID = 4
            and d.P_ID = ".$person."
            and D_DATE>='".$year."-".$month."-01'
            and D_DATE<='".$year."-".$month."-".$nbjours."'
            and D_DATE >= '".date('Y-m-d')."'";
    $result=mysqli_query($dbc,$query);
}
else {
    $detail = $dispos[1]." matins, ".$dispos[2]." apr�s-midis, ".$dispos[3]." soirs et ".$dispos[4]." nuits";
}

$moislettres=moislettres($month);
if ( $id == $person ) {
    write_msgbox("OK", $star_pic, "Merci <b>".$prenom."</B> tes disponibilit�s pour <b>".$moislettres."</b> ont �t� enregistr�es (".$detail.")<p align=center>
                    <a href=index_d.php ><input type='button' class='btn btn-default' value='retour'></a>",30,0);
}
else {
    write_msgbox("OK", $star_pic, "Les disponibilit�s de ".$prenom." ".$nom." pour <b>".$moislettres."</b> ont �t� enregistr�es (".$detail.")<p align=center>
            <a href=personnel.php ><input type='button' class='btn btn-default' value='retour'></a>",30,0);
}

insert_log('UPDDISPO', $person, $moislettres." ".$year.": ".$detail);


if(isset($_POST['msg'])){
    $msg = secure_input($dbc,$_POST['msg']);
    $msg = str_replace("\"","'",$msg);
    $msg=preg_replace('#(<|>)#', '-', $msg);
    $msg=str_replace('&', 'et',$msg);
    $comment = '<br>Commentaire:<br><span style="color:red">'.clean_mail_data($msg).'</span>';
    $query = "delete from disponibilite_comment 
            where P_ID=".$person." and DC_MONTH=".$month." and DC_YEAR=".$year;
    $result=mysqli_query($dbc,$query);
    $query="insert into disponibilite_comment (P_ID, DC_MONTH, DC_YEAR, DC_COMMENT)
        values (".$person.",".$month.",".$year.",\"".$msg."\")";
    $result=mysqli_query($dbc,$query);
}
else $comment = '';

$message  = "Bonjour,\n";
$subject = "Disponibilites enregistrees pour ".$moislettres." ".$year;                   
$message = "Les disponibilit�s de ".$prenom." ".$nom."\n";
$message .= "ont bien �t� enregistr�es pour le mois de ".$moislettres." ".$year."\n";
$message .= $comment."\n";
$message .= $detail."\n";
$destid = $person.",".get_granted(57, $hissection, $level = 'parent', $avoidspam = 'yes');
$nb = mysendmail($destid , $id , "$subject" , "$message" ,"yes" );
writefoot();

?>
