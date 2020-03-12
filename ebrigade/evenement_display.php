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
include_once ($basedir."/fonctions_documents.php");
@session_start();
check_all(0);
if (isset ($_GET["print"])) $print=1;
else $print=0;
$id=$_SESSION['id'];
if ( $print == 1 ) $nomenu=1;
writehead();
$mysection=$_SESSION['SES_SECTION'];
$myparent=$_SESSION['SES_PARENT'];

// ====================================================
// parameters
// ====================================================
get_session_parameters();

$possibleorders= array('E_PARENT','EE_NAME','TSP_ID','TP_LIBELLE','P_NOM','G_LEVEL');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='E_PARENT';

// if param e provided, keep it 5 minutes as a cookie
if (isset($_GET["evenement"])){
    $evenement=intval($_GET["evenement"]);
    @setcookie("evenement", $evenement, time()+60*5);
}

if (isset ($_GET["id"])) $evenement=intval($_GET["id"]);

if (is_inscrit($id,$evenement)) $is_inscrit = true;
else $is_inscrit = false;

// test permission visible
if (! $is_inscrit ) {
    if ( get_company_evenement($evenement) == $_SESSION['SES_COMPANY'] ) {
        if (! check_rights($id,41))
            check_all(45);
    }
    else {
        check_all(41);
        if ( ! check_rights($id,40)) {
            $organisateur=get_section_organisatrice ($evenement);
            if (! check_rights($id,41, $organisateur))
                if ( $organisateur <> $myparent and get_section_parent($organisateur) <> $myparent )
                    check_all(40);
        }
    }
}

// from: scroller , inscription , calendar, choice, vehicule, personnel, formation, calendar
if ( isset ( $_SESSION['from_interventions'])) {
    $from ='interventions';
    unset($_SESSION['from_interventions']);
}
else if (isset ($_GET["from"])) $from=secure_input($dbc,$_GET["from"]);
else if (isset ($_SESSION['eventabdoc'])) {
    $from='document';
    unset($_SESSION['eventabdoc']);
}
else if (isset ($_SESSION['from'])) {
    $from=secure_input($dbc,$_SESSION['from']);
    unset($_SESSION['from']);
}
else $from='default';

if (isset ($_GET["section"])) $section=intval(secure_input($dbc,$_GET["section"]));
else $section=$mysection;
if (isset ($_GET["type"])) $type=secure_input($dbc,$_GET["type"]);
else $type="ALL";
if (isset ($_GET["date"])) $date=secure_input($dbc,$_GET["date"]);
else $date="FUTURE";
if (isset ($_GET["day"])) $day=secure_input($dbc,$_GET["day"]);
else $day="";
if (isset ($_GET["pid"])) $pid=secure_input($dbc,$_GET["pid"]);
else $pid="";

// ====================================================
// permissions
// ====================================================

if (! $is_inscrit ) {
    if (! check_rights($id,41))
        if ( get_company_evenement($evenement) == $_SESSION['SES_COMPANY'] )
            check_all(45);
        else
            check_all(41);
}

$query="select s.S_ID, s.S_CODE, s.S_HIDE, s.S_PARENT, e.E_OPEN_TO_EXT , sf.NIV
        from evenement e left join section s on e.S_ID = s.S_ID
        left join section_flat sf on e.S_ID = sf.S_ID
        where e.E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);
$nb = mysqli_num_rows($result);
$row=@mysqli_fetch_array($result);
$S_ID=intval($row["S_ID"]);
$NIV=intval($row["NIV"]);
$S_CODE=$row["S_CODE"];
if ( $S_CODE == "" ) $S_CODE = get_section_code($S_ID);
$S_HIDE=intval($row["S_HIDE"]);
$S_PARENT=intval($row["S_PARENT"]);
$E_OPEN_TO_EXT=intval($row["E_OPEN_TO_EXT"]);
if ( $nb == 0  ) {
    // remove cookie if set
    @setcookie("evenement", "", time()-3600);
    write_msgbox("ERREUR", $error_pic, "Evenement n°$evenement introuvable<br><p align=center>
        <a href='index.php' target='_top'><input type='submit' class='btn btn-default' value='Retour'></font></a> ",10,0);
    exit;
}
else if ( $S_HIDE == 1 and $E_OPEN_TO_EXT == 0 ) {
    if (! check_rights($id,41, intval($S_ID)) and ! $is_inscrit) {
        $my_parent_section = get_section_parent($_SESSION['SES_SECTION']);
        if ( ($S_PARENT <> $my_parent_section or $NIV <> $nbmaxlevels -1 )
              and $S_ID <> $my_parent_section ) {
            // cas personne ayant des permissions sur une antenne du département (exemple V.BARA sur 67 Strasbourg), OK, sinon msg erreur
            if ( ! has_role_in_dep($id, $S_ID)) {
                write_msgbox("ERREUR", $error_pic, "Vous n'avez pas les permissions pour voir <br>l'événement n°".$evenement."<br> organisé par ".$S_CODE." <br><p align=center>
                <a href=\"javascript:history.back(1)\"><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
                exit;
            }
        }
    }
}

if ( isset($_GET["anomalie"]) ) {
    $query="update evenement set E_ANOMALIE=".intval($_GET["anomalie"])." where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
}

// ====================================================
// get data
// ====================================================

$evts=get_event_and_renforts($evenement);
$chefs=get_chefs_evenement($evenement);

$query="select E.E_CODE, E.S_ID,E.TE_CODE, TE.TE_ICON, TE.TE_LIBELLE, E.E_LIEU, 
        EH.EH_DATE_DEBUT _EH_DATE_DEBUT,EH.EH_DATE_FIN _EH_DATE_FIN, 
        date_format(EH_DATE_FIN,'%d-%m-%Y') LAST_DAY,
        EH.EH_DESCRIPTION _EH_DESCRIPTION,
        TIME_FORMAT(EH.EH_DEBUT, '%k:%i') as _EH_DEBUT, S.S_CODE,
        TIME_FORMAT(EH.EH_FIN, '%k:%i') as _EH_FIN, E.E_MAIL1, E.E_MAIL2, E.E_MAIL3, E.E_OPEN_TO_EXT, E.E_ANOMALIE,
        E.E_NB, E.E_NB_STAGIAIRES, E.E_COMMENT, E.E_COMMENT2, E.E_LIBELLE, S.S_DESCRIPTION, E.E_CLOSED, E.E_CANCELED, E.E_CANCEL_DETAIL,
        E.E_CONVENTION, E.E_PARENT, E.E_CREATED_BY, E_ALLOW_REINFORCEMENT, E.TF_CODE, E.PS_ID, E.E_EQUIPE,
        date_format(E.E_CREATE_DATE,'%d-%m-%Y %H:%i') E_CREATE_DATE, E.C_ID, E.E_CONTACT_LOCAL, ".phone_display_mask('E.E_CONTACT_TEL')." as E_CONTACT_TEL,
        S.DPS_MAX_TYPE, E.TAV_ID, EH.EH_ID _EH_ID, EH.EH_DUREE _EH_DUREE, E.E_FLAG1, E.E_VISIBLE_OUTSIDE, E.E_ADDRESS, E.E_TARIF, E.E_PARTIES,
        date_format(E.E_DATE_ENVOI_CONVENTION,'%d-%m-%Y') E_DATE_ENVOI_CONVENTION, E_EXTERIEUR, S.S_HIDE, S.S_PARENT,
        TE.TE_MAIN_COURANTE, TE.TE_VICTIMES, TE.TE_MULTI_DUPLI, TE.ACCES_RESTREINT, E.E_VISIBLE_INSIDE,
        TE.TE_PERSONNEL, TE.TE_VEHICULES, TE.TE_MATERIEL, TE.TE_CONSOMMABLES, E.E_URL, E.E_COLONNE_RENFORT,
        TE.EVAL_PAR_STAGIAIRES, TE.PROCES_VERBAL, TE.FICHE_PRESENCE, TE.ORDRE_MISSION, TE.CONVENTION, TE.EVAL_RISQUE, TE.CONVOCATIONS, TE.FACTURE_INDIV,
        tg.EQ_ICON, tg.EQ_NOM, 
        E.E_HEURE_RDV as E_HEURE_RDV, E.E_LIEU_RDV, ".phone_display_mask('E.E_TEL')." as E_TEL
        from evenement E left join type_garde tg on E.E_EQUIPE = tg.EQ_ID,
        evenement_horaire EH, type_evenement TE, section S
        where E.TE_CODE=TE.TE_CODE
        and E.E_CODE=EH.E_CODE
        and S.S_ID=E.S_ID
        and E.E_CODE=".$evenement."
        order by EH.EH_ID";
$result=mysqli_query($dbc,$query);
$queryevt=$query;

$EH_ID= array();
$EH_DEBUT= array();
$EH_DATE_DEBUT= array();
$EH_DATE_FIN= array();
$EH_FIN= array();
$EH_DUREE= array();
$horaire_evt= array();
$description_partie= array();
$date1=array();
$month1=array();
$day1=array();
$year1=array();
$date2=array();
$month2=array();
$day2=array();
$year2=array();
$E_DUREE_TOTALE = 0;
$i=1;
while (custom_fetch_array($result)) {
    if ( $i == 1 ) { 
        $PS_ID_FORMATION=$PS_ID;
        $E_EQUIPE=intval($E_EQUIPE);
        $S_DESCRIPTION=stripslashes($S_DESCRIPTION);
        $E_LIBELLE=stripslashes($E_LIBELLE);
        $E_LIEU=stripslashes($E_LIEU);
        $E_NB_STAGIAIRES=intval($E_NB_STAGIAIRES);
        $E_COMMENT=stripslashes($E_COMMENT);
        $E_COMMENT2=stripslashes($E_COMMENT2);
        $E_ADDRESS=stripslashes($E_ADDRESS);
        $E_HEURE_RDV=substr($E_HEURE_RDV,0,5);
        if ( $E_EXTERIEUR == 1 ) $E_LIEU .= " <span style='background-color:yellow;'>hors département</span>";
      
        if ( $S_HIDE == 1 and $E_OPEN_TO_EXT == 0 ) {
            if (! check_rights($id,41, intval($S_ID))) {
                $my_parent_section = get_section_parent($_SESSION['SES_SECTION']);
                if ( $S_PARENT <> $my_parent_section and $S_ID <> $my_parent_section ) {
                    if ( ! has_role_in_dep($id, $S_ID)) {
                        write_msgbox("ERREUR", $error_pic, "Vous n'avez pas les permissions pour voir <br>l'événement n°".$evenement."<br> organisé par ".$S_CODE." <br><p align=center>
                        <a href=\"javascript:history.back(1)\"><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
                        exit;
                    }
                }
            }
        }
        if ( $ACCES_RESTREINT == 1 ) {
            if (! check_rights($id,26, intval($S_ID)) and ! $is_inscrit and ! in_array($id,$chefs) and $E_CREATED_BY <> $id) {
                write_msgbox("ERREUR", $error_pic, "Vous n'avez pas les permissions pour voir <br>l'événement n°".$evenement."<br>
                Car son accès est restreint aux inscrits et aux personnes ayant la permission n°26.<p align=center>
                <a href=\"javascript:history.back(1)\"><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
                exit;
            }
        }
    }
    
    // tableau des sessions
    $EH_ID[$i]=$_EH_ID;
    $description_partie[$i]=$_EH_DESCRIPTION;
    $EH_DEBUT[$i]=$_EH_DEBUT;
    $EH_DATE_DEBUT[$i]=$_EH_DATE_DEBUT;
    if ( $_EH_DATE_FIN == '' ) 
        $EH_DATE_FIN[$i]=$_EH_DATE_DEBUT;
    else 
        $EH_DATE_FIN[$i]=$_EH_DATE_FIN;
    $EH_FIN[$i]=$_EH_FIN;
    $EH_DUREE[$i]=$_EH_DUREE;
    if ( $EH_DUREE[$i] == "") $EH_DUREE[$i]=0;
    $E_DUREE_TOTALE = $E_DUREE_TOTALE + $EH_DUREE[$i];
    $tmp=explode ( "-",$EH_DATE_DEBUT[$i]); $year1[$i]=$tmp[0]; $month1[$i]=$tmp[1]; $day1[$i]=$tmp[2];
    $date1[$i]=mktime(0,0,0,$month1[$i],$day1[$i],$year1[$i]);
    $tmp=explode ( "-",$EH_DATE_FIN[$i]); $year2[$i]=$tmp[0]; $month2[$i]=$tmp[1]; $day2[$i]=$tmp[2];
    $date2[$i]=mktime(0,0,0,$month2[$i],$day2[$i],$year2[$i]);
    
    $very_end=$day2[$i]."-".$month2[$i]."-".$year2[$i];
    if ( $EH_DATE_DEBUT[$i] == $EH_DATE_FIN[$i])
        $horaire_evt[$i]=date_fran($month1[$i], $day1[$i] ,$year1[$i])." ".moislettres($month1[$i])." ".$year1[$i]." de ".$EH_DEBUT[$i]." à ".$EH_FIN[$i];
    else
        $horaire_evt[$i]="\ndu ".date_fran($month1[$i], $day1[$i] ,$year1[$i])." ".moislettres($month1[$i])." ".$EH_DEBUT[$i]." au "
                         .date_fran($month2[$i], $day2[$i] ,$year2[$i])." ".moislettres($month2[$i])." ".$year2[$i]." ".$EH_FIN[$i];
    $i++;
}



// ==========================================
// which tab to display?
// ==========================================
if ( isset($_GET["tab"]))$tab=secure_input($dbc,$_GET["tab"]);
else if ( $from == 'inscription' || $from == 'gardes' || $from =='calendar') $tab=2;
else if ( $from == 'vehicule' ) $tab=3;
else if ( $from == 'materiel' ) $tab=4;
else if ( $from == 'consommables' ) $tab=9;
else if ( $from == 'formation' ) $tab=5;
else if ( $from == 'tarif' ) $tab=6;
else if ( $from == 'document' ) $tab=7;
else if ( $from == 'interventions' ) $tab=8;
else if ( $from == 'choice' and $TE_CODE == 'MC' ) $tab=8;
else if ( $from == 'piquets') $tab=11;
else $tab="1";

// ==========================================
// evaluate permissions
// ==========================================

if ( $gardes == 1 and $TE_CODE == 'GAR' ) $gardeSP = true;
else $gardeSP = false;

// permission voir tableau de garde
if ( $gardeSP ) {
    check_all(61);
    if ( $sdis == 1 ) {
        if ( ! check_rights($id, 61,"$S_ID") )
            if ( $myparent <> "$S_ID" )
                check_all(24);
    }
}

$chef=false;
$chefs_parent=get_chefs_evenement($E_PARENT);
if ( in_array($id,$chefs) or in_array($id,$chefs_parent)) {
    $chef=true;
}

if ( $assoc ) {
    $voircompta = check_rights($id, 29,"$S_ID");
    // le chef de l'événement a toujours accès à ces fonctionnalités
    if ( $chef ) {
         $voircompta = true;
    }
    // le cadre de permanence a toujours accès à ces fonctionnalités
    if ( get_cadre ($S_ID) == $id ) {
         $voircompta = true;
    }
}
else {
    $voircompta = false;
}
if (check_rights($id, 15, $S_ID)) $granted_event=true;
else if ( $chef ) $granted_event=true;
else $granted_event=false;

if (is_operateur_pc($id,$evenement)) $is_operateur_pc=true;
else $is_operateur_pc=false;


$nbsessions=sizeof($EH_ID);
$nummaxpartie=max($EH_ID);
$organisateur= $S_ID;
if (get_level("$organisateur") > $nbmaxlevels - 2 ) $departement=get_family(get_section_parent("$organisateur"));
else $departement=get_family("$organisateur");

$evts_not_canceled=get_event_and_renforts($evenement,true);
$query1="select count(distinct P_ID) as NB from evenement_participation
     where E_CODE in (".$evts_not_canceled.")
    ";
$result1=mysqli_query($dbc,$query1);
$row1=@mysqli_fetch_array($result1);
$NP=$row1["NB"];

$query2="select count(distinct P_ID) as NB from evenement_participation
     where E_CODE in (".$evts_not_canceled.") and EP_ABSENT=0
    ";
$result2=mysqli_query($dbc,$query2);
$row2=@mysqli_fetch_array($result2);
$NP2=$row2["NB"];

$ischef=is_chef($id,$S_ID);

$OPEN_TO_ME = 1;
$perm_via_role=false;
if (( $E_OPEN_TO_EXT == 0 ) and ( ! check_rights($id, 39, $S_ID) )) {
        // hors département?
        if ( get_section_parent("$mysection") <> get_section_parent("$S_ID")) {
               $list = preg_split('/,/' , get_family_up("$S_ID"));
               if (! in_array($mysection,$list)) {
                   $list = preg_split('/,/' , get_family("$S_ID"));
                   if (! in_array($mysection,$list)) {
                   // permission sur une antenne via rôle? => permettre inscription sur tout le département
                    if ( get_level("$S_ID") ==  $nbmaxlevels - 2 ) $sections_roles_list = get_family("$S_ID");
                    else if ( get_level("$S_ID") ==  $nbmaxlevels - 1 ) $sections_roles_list = get_family(get_section_parent("$S_ID"));
                    else $sections_roles_list = "$S_ID";
            
                    $query3="select count(*) as NB from
                    habilitation h, section_role sr
                    where sr.GP_ID = h.GP_ID
                    and h.F_ID = 39
                    and sr.S_ID in (".$sections_roles_list.")
                    and sr.P_ID =".$id;
                    $result3=mysqli_query($dbc,$query3);
                    $row3=@mysqli_fetch_array($result3);
                    if ( $row3["NB"] == 0 ) $OPEN_TO_ME = 0;
                }
               }
        }
        else {
              // je peux inscrire sur les antennes voisines mais pas les départements voisins
              // si je suis à un niveau supérieur à antenne -> je ne peux pas m'inscrire
              if ( get_level("$mysection") + 2 <= $nbmaxlevels  )
                  $OPEN_TO_ME = 0;
        }
}
// événement national,régional
$list = preg_split('/,/'  , get_family_up("$mysection"));
if (( $nbsections == 0) and ( $mysection <> $S_ID ) and ( in_array($S_ID,$list))) {
  if (( get_level($S_ID) < $nbmaxlevels - 2 ) and ( ! check_rights($id, 26)))
      $OPEN_TO_ME = -2;
}
// cas particulier un agent lambda ne doit pas s'inscrire lui même sur un événement extérieur
elseif (( $nbsections == 0) and ( $E_OPEN_TO_EXT == 1 ) and (! check_rights($id, 39, $S_ID) )) {
    if (( get_section_parent("$mysection") <> $S_ID )
      and ( get_section_parent("$mysection") <> get_section_parent("$S_ID"))) {
        if ( ! check_rights($id, 26))
            $OPEN_TO_ME = -1;
    }
    elseif (get_section_parent("$mysection") == get_section_parent("$S_ID")
        and get_level("$mysection") + 2 <= $nbmaxlevels) {
        if ( ! check_rights($id, 26))
            $OPEN_TO_ME = -1;
    }
}

// definition des permissions
if (check_rights($id, 15, $organisateur) or $chef) $granted_event=true;
else $granted_event=false;
if (check_rights($id, 10, $organisateur) or $chef) $granted_personnel=true;
else $granted_personnel=false;
if (check_rights($id, 17, $organisateur) or $granted_event or $chef) $granted_vehicule=true;
else $granted_vehicule=false;
if (check_rights($id, 19, $organisateur)) $granted_delete=true;
else $granted_delete=false;
if (check_rights($id, 26, $organisateur)) { 
     $veille=true;
    $SECTION_CADRE=get_highest_section_where_granted($id,26);
}
else $veille=false;

$granted_inscription=false;
if ( $gardeSP and check_rights($id, 6, $S_ID) and $sdis == 1)
    $granted_inscription=true;
else if ( $gardeSP and check_rights($id, 6) and $sdis == 0)
    $granted_inscription=true;
else if (($OPEN_TO_ME == 1 ) and (check_rights($id, 28) or check_rights($id, 10))) 
   $granted_inscription=true;


if (is_operateur_pc($id,$evenement)) $is_operateur_pc=true;
else $is_operateur_pc=false;

// cas particulier
if (check_rights($id, 17) and (! $granted_vehicule))  {
     if ( $E_OPEN_TO_EXT == 1 ) $granted_vehicule=true;
}

if ( $gardeSP and check_rights($id, 6, "$organisateur")) {
    $granted_personnel=true;
    $granted_vehicule=true;
}

if ((check_rights($id, 47, "$organisateur")) or $chef or $granted_event)
$documentation=true;
else $documentation=false;

if ( $granted_event or $granted_vehicule ) $granted_consommables=true;
else $granted_consommables=false;



if ( $ACCES_RESTREINT == 1 ) {
    if (! check_rights($id,26, intval($S_ID)) and ! $is_inscrit and ! $chef and $E_CREATED_BY <> $id) {
        write_msgbox("ERREUR", $error_pic, "Vous n'avez pas les permissions pour voir <br>l'événement n°".$evenement."<br> 
            Car son accès est restreint aux inscrits et aux personnes ayant la permission n°26.<p align=center>
            <a href=\"javascript:history.back(1)\"><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
        exit;
    }
}

// ==========================================
// counters and number of docs
// ==========================================

$query="select count(distinct P_ID) as NB from evenement_participation
     where E_CODE in (".$evts.") and EP_ABSENT=0";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$NB1=$row["NB"];

$query="select count(distinct V_ID) as NB from evenement_vehicule
     where E_CODE in (".$evts.")";
$result=@mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$NB2=$row["NB"];

$query="select sum(em.EM_NB) as NB from evenement_materiel em, materiel m
     where em.E_CODE in (".$evts.")
    and em.MA_ID = m.MA_ID
    and m.MA_PARENT is null";
$result=@mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$NB3=$row["NB"];

$NB4=0;
$mypath=$filesdir."/files/".$evenement;
if (is_dir($mypath)) {
       $dir=opendir($mypath); 
       while ($file = readdir ($dir)) {
          if ($file != "." && $file != ".." and (file_extension($file) <> "db")) $NB4++;
    }
}
if ( intval($E_PARENT) > 0 ) {
    $mypath=$filesdir."/files/".$E_PARENT;
    if (is_dir($mypath)) {
        $dir=opendir($mypath); 
        while ($file = readdir ($dir)) {
            if ($file != "." && $file != ".." and (file_extension($file) <> "db")) $NB4++;
        }
    }
    $query="select E_CLOSED from evenement where E_CODE=".$E_PARENT;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $PARENT_CLOSED=$row["E_CLOSED"];
}
else
    $PARENT_CLOSED=0;

// ajout documents générés
if ( $granted_event ) {
    if ( $EVAL_PAR_STAGIAIRES == 1  and $competences == 1)  $NB4++;
    if ( $FACTURE_INDIV == 1  and $E_TARIF > 0 )  $NB4++;
    if ( $FICHE_PRESENCE == 1  and $E_CLOSED == 1 )  $NB4++;
    if ( $PROCES_VERBAL == 1  and $E_CLOSED == 1 and  $PS_ID <> '' and in_array($TF_CODE,array('I','C','R','M')))  $NB4++;
    if ( $ORDRE_MISSION == 1  and $E_CLOSED == 1 )  $NB4++;
    if ( $CONVENTION == 1  and $E_PARENT == 0 ) $NB4++;
    if ( $CONVENTION == 1  and $E_PARENT == 0 and signature_president_disponible($S_ID)) $NB4++; 
    if ( $EVAL_RISQUE == 1  and dim_ready($evenement))  $NB4 = $NB4 + 2;
    if ( $CONVOCATIONS == 1  and $E_CLOSED == 1 ) $NB4 = $NB4 + 2; // convocations collective et individuelles
    if ( ! $gardeSP and $TE_PERSONNEL == 1 and $TE_CODE <> 'MC' and $TE_CODE <> 'FOR' and $ORDRE_MISSION == 1 and ($assoc or $army))  $NB4++; // demande de renforts
}

// ajout des documents spécifiques formation ou DPS
$query1="select TYPE from poste where PS_ID='".$PS_ID."' union select '".$TE_CODE."'";
$result1=mysqli_query($dbc,$query1);
$row1=@mysqli_fetch_array($result1);
$type_doc=$row1["TYPE"];
// documents SST, PSC1 ou autres  (DPS)
$NB4 = $NB4 + count_specific_documents($type_doc); 

// attestations de présence SST
if ( $E_CLOSED == 1 and ( $type_doc == 'SST' or $type_doc == 'PRAP' ) and $granted_event) $NB4 = $NB4 + 1;

// main courante
$query1="select count(1) from evenement_log where E_CODE=".$evenement;
$result1=mysqli_query($dbc,$query1);
$row1=@mysqli_fetch_array($result1);
$NB5=$row1[0];

// produits consommés
$query="select count(1) as NB from evenement_consommable
     where E_CODE in (".$evts.")";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$NB6=$row["NB"];

// PDF produits consommés
if ( $NB6 > 0 and $granted_event) $NB4 = $NB4 + 1;

// remplacements
$query1="select count(1) from remplacement where E_CODE=".$evenement;
$result1=mysqli_query($dbc,$query1);
$row1=@mysqli_fetch_array($result1);
$NB7=$row1[0];

// ====================================================
// header and tabs
// ====================================================

if ( $print == 1 ) 
echo "<link rel='stylesheet' href='".$basedir."/css/print.css'>";

if ( $autorefresh == 1 ) echo "<meta http-equiv='Refresh' content='20'>";
echo "
<link rel='stylesheet' type='text/css' href='css/print.css' media='print' />
<STYLE type='text/css'>
.section{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.categorie{color:black; background-color:white; font-size:10pt;}
.materiel{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>
<script type=text/javascript>
function fermerfenetre(){
    var obj_window = window.open('', '_self');
    obj_window.opener = window;
    obj_window.focus();
    opener=self;
    self.close();
}

$(function(){
    $('.statut_select').change(function() {
        var bgcolor=$('option:selected',this).css('background-color');
        var txtcolor=$('option:selected',this).css('color');
        $(this).css('background-color', bgcolor);
        $(this).css('color', txtcolor);
    });
});

</script>

<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<script type='text/javascript' src='js/evenement.js?version=".$patch_version."'></script>";

echo "</head>";

if ( $print == 1 ) 
   echo "<body onload='javascript:window.print();'>";
else 
   echo "<body>";

if ( $EQ_ICON <> '' ) $img=$EQ_ICON;
else $img="images/evenements/".$TE_ICON;
if ( $EQ_NOM <> '' ) $t=$EQ_NOM;
else $t=$TE_LIBELLE;
$img = "<img class='img-max-50 hide_mobile' src=".$img." title=\"".$t."\">";
write_debugbox($queryevt);

$cmt=get_info_evenement($evenement);
if ( $sdis ) $cmt = $S_CODE." - ".$cmt;
if (! $print ) { 
    echo "<div align=center >
            <table class='noBorder'>
            <tr >
            <td width=90>".$img."</td>
            <td width=600><b>".$cmt."</b><br>";

    if ( $E_ADDRESS <> "" and $geolocalize_enabled ) {
        $querym="select LAT, LNG from geolocalisation where TYPE='E' and CODE=".$evenement;
        $resultm=mysqli_query($dbc,$querym);
        $NB=mysqli_num_rows($resultm);
        if ( $NB > 0 ) {
            custom_fetch_array($resultm);
            $url = $waze_url."&ll=".$LAT.",".$LNG."&";
            echo " <a href=".$url." target=_blank><i class='fab fa-waze fa-2x' title='Voir la carte Waze' class='noprint'></i></a>";
            if ( check_rights($id,76) or is_operateur_pc($id,$evenement))
                echo " <a href=sitac.php?evenement=".$evenement." ><i class='fa fa-map fa-2x noprint' title=\"Voir la carte Google Maps\"></i></a>";
        }
    }

    if ( check_rights($id, 49) and $log_actions == 1 ) {
        echo " <a href=\"history.php?lccode=E&lcid=$evenement&order=LH_STAMP&ltcode=ALL\"><i class='fa fa-search fa-2x noprint' title=\"Historique des modifications\" class=\"noprint\" ></i></a>";
    }

    if ( $TE_CODE == 'DPS' ){
      echo " <a href='dps.php?evenement=$evenement'><i class='fa fa-calculator fa-2x noprint' title='Dimensionnement DPS' ></i></a>";
    }

    if ( $voircompta and $TE_CODE <> 'MC' and ! $gardeSP)
      echo " <a href='evenement_facturation.php?evenement=$evenement' ><i class='far fa-money-bill-alt fa-2x noprint' title='Facturation' ></i></a> ";

    if (check_rights($id, 41) and $TE_CODE <> 'MC') {
        echo " <a href='#'><i class='far fa-file-excel fa-2x noprint' style='color:green' title='Excel' onclick=\"window.open('    evenement_xls.php?evenement=$evenement')\"/></i></a>";
        if ( $_SESSION['SES_BROWSER'] != "Mozilla unknown" ) {
            if ( $gardeSP ) 
                echo " <a href=\"feuille_garde.php?evenement=$evenement\"><i class='fa fa-print fa-2x noprint' title='Version imprimable' ></i></a>";
            else
                echo " <a href=\"evenement_display.php?evenement=$evenement&print=1&from=print\" target=_blank><i class='fa fa-print fa-2x noprint' title=imprimer  ></i></a>";
        }
        echo " <a href=\"evenement_ical.php?evenement=$evenement&section=$section\" target=_blank><i class='far fa-calendar-alt fa-2x noprint' title=\"Télécharger le fichier ical\"></i></a>";
    }

    if ( $NB1 > 0 ) 
        echo " <a href='evenement_trombinoscope.php?fonction=1&competence=1&section=1&evenement=$evenement' target='_blank'>
      <i class='fa fa-camera fa-2x noprint' title='Personnel participant avec photos' ></i></a>";

    if (! $gardeSP )
        echo " <a href='evenement_choice.php'><i class='fa fa-arrow-circle-left fa-2x noprint' title='retour liste des événements'></i></a>";
      
    echo "</td>
            </tr></table>
            </div>";
}
if ($voircompta and ! $print) {
    $color=get_etat_facturation($evenement,"css");
    if ( $color <> "" ) 
        echo "<p style='color:".$color.";'><i class='fa fa-euro-signfa-lg'></i> ".get_etat_facturation($evenement,"txt")."</p>"; 
    else echo "<p>";
}
else echo "<p>";
if ( $E_ANOMALIE and $gardeSP ) {
    echo "<p><font color=red><i class='fa fa-exclamation-circle fa-lg'></i> <b>Anomalie sur le tableau de garde.</b></font></p>";
}
 
if ( $E_VISIBLE_INSIDE == 0  ) {
    if ( $gardeSP ) 
        echo "<p><font color=orange><i class='fa fa-exclamation-triangle fa-lg'></i> <b>Le tableau de garde n'est pas accessible par le personnel.</b></font></p>";
    else {
        // événement caché
            if ( ! $is_inscrit and ! check_rights($id,9) and ! in_array($id, $chefs)) {
            write_msgbox("ERREUR", $error_pic, "Evenement n°$evenement introuvable<br><p align=center>
            <a href='index.php' target='_top'><input type='submit' class='btn btn-default' value='Retour'></font></a> ",10,0);
            exit;
        }
        echo "<p><font color=orange><i class='fa fa-exclamation-triangle fa-lg'></i> <b>Evénement caché, seules les personnes inscrites ou disposant de la permission n°9 peuvent le voir.</b></font></p>";
    }
}

echo  "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";

// infos generales
if ( $tab == 1 ) $class='active'; else $class='';
echo "<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=1&evenement=$evenement\" role='tab' aria-controls='tab1' href='#tab1'>
<span title=\"Informations générales sur l'événement\"><i class='fa fa-info'></i> Infos</span></a></li>";

// personnel
if ( $TE_PERSONNEL == 1 ) {
    if ( $syndicate == 1 ) $label = "Inscriptions";
    else $label = "Personnel";
    if ( $tab == 2 ) $class='active'; else $class='';
    echo "\n"."<li class='nav-item'><a class='nav-link $class'  href=\"evenement_display.php?pid=$pid&from=$from&tab=2&evenement=$evenement\" role='tab' aria-controls='tab2' href='#tab2'>
    <span title=\"Personnel inscrit sur l'événement\"><i class='fa fa-user'></i> $label $NB1</span></a></li>";
}
//vehicule
if ( $vehicules == 1 and $TE_VEHICULES ==  1) {
    if ( $tab == 3 ) $class='active'; else $class='';
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=3&evenement=$evenement\" role='tab' aria-controls='tab3' href='#tab3'>
    <span title=\"Véhicules engagés sur l'événement\"><i class='fa fa-car'></i> Véhic. $NB2</span></a></li>";
}
//piquets de feu
if ( $gardes == 1 and $TE_CODE == 'GAR' and $vehicules == 1 and $TE_VEHICULES == 1 and $TE_PERSONNEL == 1 and $NB2 > 0 and $NB1 > 0 ) {
    if ( $tab == 11 ) $class='active'; else $class='';
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=11&evenement=$evenement\" role='tab' aria-controls='tab11' href='#tab11'>
    <span title=\"Affectation des personnels sur les engins\"><i class='fa fa-user'></i>/<i class='fa fa-car'></i> Piquets</span></a></li>";
}
//materiel
if ( $materiel == 1 and $TE_MATERIEL == 1) {
    if ( $tab == 4 ) $class='active'; else $class='';
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=4&evenement=$evenement\" role='tab' aria-controls='tab4' href='#tab4'>
    <span title='Matériel engagé'><i class='fa fa-cog'></i> Mat. $NB3</span></a></li>";
}

//consommable
if ( $consommables == 1 and $TE_CONSOMMABLES == 1) {
    if ( $tab == 9 ) $class='active'; else $class='';
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=9&evenement=$evenement\" role='tab' aria-controls='tab9' href='#tab9'>
    <span title=\"Utilisation de produits consommables sur l'événement\"><i class='fa fa-coffee'></i> Conso. $NB6</span></a></li>";
}

//formation
if ($competences == 1  and  $TE_CODE == 'FOR'  and  $PS_ID <> "" and $TF_CODE <> "" and $TE_PERSONNEL == 1){
    if ( $tab == 5 ) $class='active'; else $class='';
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=5&evenement=$evenement\" role='tab' aria-controls='tab5' href='#tab5'>
    <span title=\"Informations concernant la formation et les diplômes\"><i class='fa fa-certificate'></i> Formation </span></a></li>";
}

// factures individuelles
if ( $E_TARIF > 0 and $granted_event) {
    if ( $tab == 6 ) $class='active'; else $class='';
    echo "\n"."<li class='nav-item'><a class='nav-link $class' class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=6&evenement=$evenement\" role='tab' aria-controls='tab6' href='#tab6'>
    <span title=\"Tarif et factures individuelles\"><i class='fa fa-euro-sign'></i> Tarif</span></a></li>";
}

// documents
if ( $tab == 7 ) $class='active'; else $class='';
echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=7&evenement=$evenement\" role='tab' aria-controls='tab7' href='#tab7'>
<span title=\"Documents générés ou attachés à l'événement\"><i class='far fa-folder-open'></i> Docs $NB4</span></a></li>";

// Rapport compte rendu 
if ( $TE_MAIN_COURANTE == 1){
    if ( $tab == 8 ) $class='active'; else $class='';
    if ( $TE_VICTIMES == 0) $t="<i class='fa fa-edit'></i> Rapport. $NB5";
    else $t="<i class='fa fa-ambulance'></i> Rapport. $NB5";
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=8&evenement=$evenement&autorefresh=$autorefresh\" role='tab' aria-controls='tab8' href='#tab8'>
    <span title=\"Compte rendu et main courante\">".$t."</span></a></li>";
}

// remplacements
if ( $remplacements and $TE_CODE <> 'MC' ) {
    if ( $tab == 10 ) $class='active'; else $class='';
    $t="<i class='fa fa-user-times'></i> Remplacements $NB7";
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=10&evenement=$evenement\" role='tab' aria-controls='tab10' href='#tab10'>
    <span title=\"Demandes de remplacement pour cette garde\">".$t."</span></a></li>";
}
echo "\n"."</ul>"; // fin tabs


echo "<div id='export' style='position: relative;' align=center><p>";


// bloquer les changements dans le passé
$ended=get_number_days_after_block($evenement);
$changeallowed=true;
if ( $ended > 0 ) {
    if ( ! $granted_delete ) {
        $granted_personnel=false;
        $granted_vehicule=false;
        $granted_inscription=false;
        $documentation=false;
        $changeallowed=false;
        $granted_consommables=false;
    }
    $link="<a href=upd_section.php?S_ID=".$organisateur."&status=parametrage title='Voir la configuration'>".$ended."</a>";
    
    if (! $print ) echo "<table class='noBorder'><tr><td><i class='fa fa-exclamation-triangle' style='color:orange' title=\"Cet événement n'est plus modifiable, sauf par les personnes ayant la permission n°19\"></i></td>
                    <td><font size=1><i> Les modifications sur cet événement terminé ne sont plus possibles depuis ".$link." jours.</i></font></td>
                    </tr></table>";
}

//=====================================================================
// équipes 
//=====================================================================

if ( intval($E_PARENT) > 0 ) $evts_list=$evenement.",".intval($E_PARENT);
else $evts_list=$evenement;

$querye="select E_CODE, EE_ID, EE_NAME, EE_DESCRIPTION
         from evenement_equipe 
         where E_CODE in (".$evts_list.")
         order by EE_ORDER, EE_NAME";
$resulte=mysqli_query($dbc,$querye);
$equipes=array();
while ($rowe=@mysqli_fetch_array($resulte)) {
    array_push($equipes, array($rowe["EE_ID"],$rowe["EE_NAME"]));
}
$nbe=sizeof($equipes);

//=====================================================================
// titre si impression
//=====================================================================
if ( $print) {

    $logo=get_logo();
     
    $queryz="select S_DESCRIPTION from section where S_ID = 0";
    $resultz=mysqli_query($dbc,$queryz);
    $rowz=mysqli_fetch_array($resultz);
    $S_DESCRIPTION0 =  $rowz["S_DESCRIPTION"];
    
    if ( $gardeSP ) {
        echo "<table class='noBorder'><tr>
        <td width=90><img src=".$logo." style='max-width:60px';></td>
        <td><font size=5>".$S_DESCRIPTION0."</font><br><font size=4>".get_info_evenement($evenement)."</font></td>
        </tr></table>";
    }
    else {
        echo "<table class='noBorder'><tr><td width=90><img src=".$logo." style='max-width:60px';></td><td><font size=5>".$S_DESCRIPTION0."</font><br><font size=4>Section : ".$S_CODE." - ".$S_DESCRIPTION."</font></td></tr></table>
        <p>Bonjour, veuillez trouver ci-dessous les éléments relatifs à la mise en place de :<br>
        ".$TE_LIBELLE." - ".$E_LIBELLE." (".$E_LIEU.")";
    
        for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
            if (isset($horaire_evt[$i]))
                echo "<br>".$horaire_evt[$i];
        }
        
        // info demandeur DPS
        if ( $C_ID <> "" and $C_ID > 0)
            echo "<br>Pour le compte de <b>".get_company_name($C_ID)."</b>";
    }
}

//=====================================================================
// informations générales
//=====================================================================
if (( $tab == 1 ) or $print){

if ( $E_CREATED_BY <> '' and ! $print) 
    $author = "<font size=1><i> - créé par ".my_ucfirst(get_prenom($E_CREATED_BY))." ".strtoupper(get_nom($E_CREATED_BY))."
               le ". $E_CREATE_DATE."
                </i></font>";
else 
    $author='';

echo "<div style='max-width: 850px;'>";
if ( $print ) echo "<table cellspacing=0 border=0 width=850>";
else echo "<table cellspacing=0 border=0 >";
echo "<tr><td CLASS='MenuRub'>Description".$author."</td><td align=right CLASS='MenuRub'>N° ".$evenement."</td></tr>";
echo "<tr><TD CLASS='Menu' bgcolor=$mylightcolor colspan=2>";

if ( ! $print and $TE_PERSONNEL == 1) {
    if ( $E_CANCELED == 1 ) {
        if ( $E_CANCEL_DETAIL <> '' ) $pr=" - ".$E_CANCEL_DETAIL." ";
        else $pr='';
        echo "<font size=3 color=red><b>Evénement annulé ".$pr."</b></font>";
    }
    else if ( ! $gardeSP ) {
        if ( $E_CLOSED == 1 ) echo "<font color=orange><b>Inscriptions fermées</b></font>";
        else if ( $OPEN_TO_ME == 0 ) echo "<font color=orange><b>Inscriptions interdites pour les personnes des autres ".$levels[3]."s</b></font>";
        else if ( $OPEN_TO_ME == -1 ) echo "<font color=green><b>Inscriptions possibles pour les personnes des autres ".$levels[3]."s par leur responsable</b></font>";
        else echo "<font color=green><b>Inscriptions ouvertes</b></font>";
    }
}
echo "<table class='noBorder'>";

if ( intval($E_PARENT) > 0 and  $nbsections == 0) {
    echo "<tr><td width=33%><b>".ucfirst($renfort_label)." pour </b></td>";
    $queryR="select e.TE_CODE, e.E_LIBELLE, s.S_CODE, s.S_DESCRIPTION
            from evenement e, section s 
            where s.S_ID = e.S_ID
            and e.E_CODE=".$E_PARENT;
    $resultR=mysqli_query($dbc,$queryR);
    $rowR=@mysqli_fetch_array($resultR);
    $ER_LIBELLE=stripslashes($rowR["E_LIBELLE"]);
    $SR_CODE=$rowR["S_CODE"];
    $SR_DESCRIPTION=$rowR["S_DESCRIPTION"];
    echo "<td width=67%><a href=evenement_display.php?evenement=".$E_PARENT.">
    ".$ER_LIBELLE." organisé par ".$SR_CODE." - ".$SR_DESCRIPTION."</a></td></tr>";
}

for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
    if ( $nbsessions == 1 ) $t="Dates et heures";
    else if (isset($EH_ID[$i])) $t="Date Partie ".$EH_ID[$i];
    if ( isset($horaire_evt[$i])) {
        if ( $description_partie[$i] <> "" ) $dp = " - <i>".$description_partie[$i]."</i>";
        else $dp="";
        echo "<tr><td ><b>".$t." </b></td>
            <td> ".$horaire_evt[$i].$dp."
         </td></tr>";
    }
}
if($E_DUREE_TOTALE <> ''){
echo "<tr><td ><b>Durée totale </b></td>
        <td > ".$E_DUREE_TOTALE." heures</td></tr>";
}

if( $E_HEURE_RDV <> '' or $E_LIEU_RDV <> ''){
echo "<tr><td ><b>RDV </b></td>
        <td > ".$E_HEURE_RDV." ".$E_LIEU_RDV."</td></tr>";
}

if ( $gardeSP ) {
    $t = 'Section de garde';
    $desc = "<a href=upd_section.php?S_ID=".$S_ID.">".$S_CODE."</a>";
    $SECTION_GARDE=get_section_garde_evenement($evenement, 1);
    if ( $SECTION_GARDE > 0 and $SECTION_GARDE <> $S_ID ) {
        $desc .= " - <i class='fa fa-sun fa-lg' style='color:yellow;' title='section du jour'></i> 
                    <a href=upd_section.php?S_ID=".$SECTION_GARDE."> ".get_section_code($SECTION_GARDE)."</a>";
        $SECTION_GARDE2=get_section_garde_evenement($evenement, 2);
        if ( $SECTION_GARDE <> $SECTION_GARDE2 )
            $desc .= " - <i class='fa fa-moon fa-lg' style='color:black;' title='section nuit'></i> 
                        <a href=upd_section.php?S_ID=".$SECTION_GARDE2.">".get_section_code($SECTION_GARDE2)."</a>";
    }
}
else {
    $t = 'Organisateur';
    $desc = "<a href=upd_section.php?S_ID=".$S_ID.">".$S_CODE." - ".get_section_name($organisateur)."</a>";
}
echo "<tr><td ><b>".$t." </b></td>
        <td >".$desc."</td></tr>";

if ( intval($E_TEL) > 0 )
    echo "<tr><td title=\"Donne tous les droits d'accès sur cet évenement\"><b>Téléphone Contact</b></td>
        <td><a href='tel:".$E_TEL."'>".$E_TEL."</a></td></tr>";
     
     
if ( $gardeSP ) $t = "Responsable de la garde";
else if ( $syndicate == 1 )  $t = "Gestionnaire de l'événement";
else $t = "Responsable ".$cisname;

echo "<tr><td title=\"Donne tous les droits d'accès sur cet évenement\"><b> ".$t." </b></td>
        <td>";
if ( count($chefs) > 0 ) {
    for ( $c = 0; $c < count($chefs); $c++ ) {
        $queryz="select ".phone_display_mask('P_PHONE')." P_PHONE, P_HIDE, P_NOM, P_PRENOM from pompier where P_ID=".$chefs[$c];
        $resultz=mysqli_query($dbc,$queryz);
        $rowz=mysqli_fetch_array($resultz);
        $phone =  $rowz["P_PHONE"];
        $P_HIDE = $rowz["P_HIDE"];
        if ( $syndicate == 1 or intval($E_TEL) > 0 ) $phone="";
        else if ( $phone <> '' ) {
            if (is_iphone())
                    $phone=" <small><a href='tel:".$phone."'>".$phone."</a></small>";
            else 
                $phone = " - ".$phone;
            if ($P_HIDE == 1 and  $nbsections == 0 ) {
                if (( ! $ischef ) 
                    and ( ! in_array($id, $chefs) )
                    and (! check_rights($id, 2))
                    and (! check_rights($id, 12)))
                    $phone=" - **********";
            } 
        }
        echo "<a href=upd_personnel.php?pompier=".$chefs[$c]." title=\"A tous les droits d'accès sur cet évenement\"> 
            ".my_ucfirst($rowz["P_PRENOM"])." ".strtoupper($rowz["P_NOM"])."</a> ".$phone;
        if ( $c < (count($chefs) -1) ) echo "<br>";
    }
}

if ( $granted_event and (!$print) and $changeallowed) {
    $url="evenement_detail.php?evenement=".$evenement."&what=responsable";
    print write_modal( $url, "C".$evenement, "<i class='fa fa-user fa-lg' title='choisir les responsables'></i>");
}
echo "</td></tr>";

echo "<tr><td width=30%><b>Lieu </b></td>
        <td width=70%> ".$E_LIEU."</td></tr>";

if ( $E_ADDRESS <> "" ) {
    $map="";
    if ( $geolocalize_enabled ) {
        $querym="select LAT, LNG from geolocalisation where TYPE='E' and CODE=".$evenement;
        $resultm=mysqli_query($dbc,$querym);
        $NB=mysqli_num_rows($resultm);
        if ( $NB > 0 ) {
            custom_fetch_array($resultm);
            $url = $waze_url."&ll=".$LAT.",".$LNG."&pin=1";
            $map = " <a href=".$url." target=_blank><i class='fab fa-waze fa-lg' title='Voir la carte Waze' class='noprint'></i></a>";
            if ( check_rights($id,76) or is_operateur_pc($id,$evenement))
                $map .= " <a href=sitac.php?evenement=".$evenement." ><i class='fa fa-map noprint' style='color:green;' title=\"Voir la carte Google Maps\"></i></a>";
        }
    }
    echo "<tr><td width=30%><b>Adresse exacte </b></td>
        <td width=70%>".$E_ADDRESS." ".$map."</td></tr>";
}

if ( $E_URL <> "" ) {
    if ( $cisname == 'Protection Civile' ) $t = "Lien URL vers calendrier ADPC";
    else $t = "Lien URL vers descriptif";
    $E_URL=str_replace("http://","",$E_URL);
    $E_URL=str_replace("https://","",$E_URL);
    echo "<tr><td width=30%><b>".$t."</b></td>
        <td width=70%><a href=http://".$E_URL." target='_blank'>".$E_URL."</a></td></tr>";
}

// compétences requises
$querym="select EH_ID from evenement_horaire where E_CODE=".$evenement." order by EH_ID";
$resultm=mysqli_query($dbc,$querym);
 
 
if ( $TE_PERSONNEL == 1 and $TE_CODE <> 'MC' ) {
    if ( $nbsessions == 1 ) $showcpt = "<tr><td width=30%><b>Personnel requis </b></td><td>";
    else $showcpt = "<tr><td colspan=2><b>Personnel requis </b>";


    while ( $rowm=mysqli_fetch_array($resultm) ) {
        $i=$rowm["EH_ID"];
        if ( $nbsessions > 1 ) $showcpt .= "</td></tr><tr><td align=right><font size=1>partie ". $i."</font></td><td>";
        $nbt=0;
        
        // -------------------------
        // PARTIE A OPTIMISER - ne pas executer une fois par partie
        // -------------------------
        $queryt="select ec.nb 'nbt'
                from evenement_competences ec 
                where ec.E_CODE=".$evenement." 
                and ec.PS_ID = 0
                and ec.EH_ID=".$i;
        $resultt=mysqli_query($dbc,$queryt);
        custom_fetch_array($resultt);

        $queryp="select ec.PS_ID, p.TYPE, p.DESCRIPTION, ec.nb 
                from evenement_competences ec , poste p
                where ec.E_CODE=".$evenement." 
                and ec.PS_ID = p.PS_ID
                and ec.EH_ID=".$i."
                order by p.PH_LEVEL desc, p.PS_ORDER, ec.PS_ID";
        $resultp=mysqli_query($dbc,$queryp);
        $nbp=mysqli_num_rows($resultp);
        // -------------------------
        // FIN PARTIE A OPTIMISER 
        // -------------------------
        
        // total personnel demandé
        $type='TOTAL';
        $inscrits=get_nb_competences($evenement,$i,0);
        if ( $inscrits == $nbt ) $col=$green;
        else if ($inscrits > $nbt ) {
            if ( $gardeSP ) $col=$orange;
            else $col=$blue;
        }
        else $col=$red;
        $desc = $inscrits." participants.";
        $showcpt .= " <a title=\"$nbt personnes requises\n".$desc."\"><span class='badge' style='background-color:".$col."'> $nbt </span></a>";
        if ( $nbp > 0 ) $showcpt .= " <small>dont </small>";
        
        // détail par compétence
        while ( custom_fetch_array($resultp) ) {
            $inscrits=get_nb_competences($evenement,$i,$PS_ID);
            if ($inscrits >= $nb ) $col=$green;
            else $col=$red;
            $desc=$nb." ".$DESCRIPTION." requis, ";
            if ( $inscrits < 2 ) $desc .= $inscrits." participant ayant cette compétence valide.";
            else $desc .= "\n".$inscrits." participants ayant cette compétence valide.";
            $showcpt .= " <a title=\"".$desc."\"><span class='badge' style='background-color:".$col."'>".$nb." ".$TYPE."</span></a>";
            $showcpt = rtrim($showcpt,',');
        }
        if ( $granted_event and (!$print) and $changeallowed ) {
            $showcpt .= " <a href='#'><i class='fa fa-edit fa-lg' title='Modifier les compétences demandées' 
                        onclick=\"modifier_competences('".$evenement."',".$i.")\"></i></a>
                        </td></tr>";
        }
    }
    $showcpt = rtrim($showcpt,',');
    $showcpt .= "</td></tr>";
    print $showcpt;
    
    // options inscriptions
    if ( !$print and $granted_event and ! $gardeSP ) {
        echo "<tr><td><b>Options inscription </b></td><td>";
        if ( $E_PARENT > 0 ) $e = $E_PARENT;
        else $e = $evenement;
        $nboptions=count_entities("evenement_option", "E_CODE=".$e);
        if ( $nboptions > 0 ) echo  "<span class='badge'>".$nboptions." options</span>";
        echo  " <a href=evenement_options.php?evenement=".$e."&renfort=".$evenement." title=\"Voir et modifier les options d'inscriptions\">
            <i class='fa fa-edit fa-lg'></i></a></td></tr>"; 
    }

    // Véhicules requis
    if ( $vehicules == 1 and $TE_VEHICULES ==  1 and ! $gardeSP ) {
        echo "<tr><td><b>Véhicules requis</b></td><td>";
        $url="demande_renfort.php?evenement=".$evenement;
        $detail="";
        $querym="select NB_VEHICULES 
            from demande_renfort_vehicule
            where TV_CODE = '0'
            and E_CODE=".$evenement;
        $resultm=mysqli_query($dbc,$querym);
        $rowm=mysqli_fetch_array($resultm);
        $demandes=intval($rowm["NB_VEHICULES"]);
        
        $querym="select count(1) from evenement_vehicule where E_CODE in (".$evts.")";
        $resultm=mysqli_query($dbc,$querym);
        $rowm=mysqli_fetch_array($resultm);
        $inscrits=intval($rowm[0]);
        if ( $demandes > 0 ) {
            if ( $inscrits >= $demandes ) $col=$green;
            else $col = $red;
            $detail .=  "<span class='badge' style='background-color:".$col."' title='$demandes vehicules demandes\n$inscrits inscrits'>".$demandes."</span>";
        }
        $querym="select t.TV_CODE, t.TV_LIBELLE, t.TV_USAGE , d.NB_VEHICULES 'demandes'
                from type_vehicule t, demande_renfort_vehicule d
                where d.TV_CODE = t.TV_CODE
                and E_CODE=".$evenement."
                order by t.TV_LIBELLE";
        $resultm=mysqli_query($dbc,$querym);
        $nbm=mysqli_num_rows($resultm);
        if ( $nbm > 0 ) {
            $detail .= " <small>dont </small>";
            while ( custom_fetch_array($resultm) ) {
                $query2="select count(1) from evenement_vehicule ev, vehicule v where ev.E_CODE in (".$evts.") and ev.V_ID=v.V_ID and v.TV_CODE='".$TV_CODE."'";
                $result2=mysqli_query($dbc,$query2);
                $row2=mysqli_fetch_array($result2);
                $inscrits=intval($row2[0]);
                if ($inscrits >= $demandes ) $col=$green;
                else $col=$red;
                $detail .= " <a title=\"". $demandes." ".$TV_LIBELLE." demandes\n".$inscrits." engages\"><span class='badge' style='background-color:".$col."'>".$demandes." ".$TV_CODE."</span></a>";
            }
        }
        echo $detail;
        if ( $granted_event ) echo " <a href='".$url."' title='Modifier les véhicules et matériel demandés'><i class='fa fa-edit fa-lg noprint' ></i></a>";
        echo "</td><tr>";
    }
    // Matériel requis
    if ( $materiel == 1 and $TE_MATERIEL ==  1 and ! $gardeSP ) {
        $detail="";
        echo "<tr><td><b>Matériel requis</b></td><td>";
        $querym="select tm.TM_ID, tm.TM_CODE
                from type_materiel tm, demande_renfort_materiel drm 
                where tm.TM_ID = drm.TYPE_MATERIEL
                and tm.TM_USAGE not in ('Habillement','Promo-Com','ALL')
                and drm.E_CODE = ".$evenement."
                order by tm.TM_USAGE, tm.TM_CODE"; 
        $resultm=mysqli_query($dbc,$querym);
        while ( $rowm=mysqli_fetch_array($resultm)) {
            $queryn="select count(1) from evenement_materiel em, materiel m
                    where em.MA_ID = m.MA_ID
                    and m.TM_ID='".$rowm["TM_ID"]."'
                    and em.E_CODE in (".$evts.")";
            $resultn=mysqli_query($dbc,$queryn);
            $rown=mysqli_fetch_array($resultn);
            $inscrits=intval($rown[0]);
            if ( $inscrits > 0 ) $col=$green;
            else $col = $red;
            $detail .=  " <span class='badge' style='background-color:".$col."'  title=\"besoin de ".$rowm["TM_CODE"]."\n$inscrits engagés\">".$rowm["TM_CODE"]."</span>";
        }
        
        $querym="select cm.TM_USAGE, cm.CM_DESCRIPTION
                from categorie_materiel cm, demande_renfort_materiel drm 
                where cm.TM_USAGE = drm.TYPE_MATERIEL
                and cm.TM_USAGE not in ('Habillement','Promo-Com','ALL')
                and drm.E_CODE = ".$evenement."
                order by cm.TM_USAGE"; 
        $resultm=mysqli_query($dbc,$querym);
        while ( $rowm=mysqli_fetch_array($resultm)) {
            $queryn="select count(1) from evenement_materiel em, type_materiel tm, materiel m
                    where em.MA_ID = m.MA_ID
                    and m.TM_ID = tm.TM_ID
                    and tm.TM_USAGE='".$rowm["TM_USAGE"]."'
                    and em.E_CODE in (".$evts.")";
            $resultn=mysqli_query($dbc,$queryn);
            $rown=mysqli_fetch_array($resultn);
            $inscrits=intval($rown[0]);
            if ( $inscrits > 0 ) $col=$green;
            else $col = $red;
            $detail .=  " <span class='badge' style='background-color:".$col."'  title=\"besoin de ".$rowm["CM_DESCRIPTION"]."\n$inscrits engagés\">".$rowm["TM_USAGE"]."</span>";
        }
        echo $detail;
        if ( $granted_event ) echo " <a href='".$url."' title='Modifier les véhicules et matériel demandés'><i class='fa fa-edit fa-lg noprint' ></i></a>";
    echo "</td><tr>";
    }
}
 
 
// cas du DPS
if ( $TE_CODE == 'DPS' ) {
    $warn="";
    if ( $TAV_ID == 1  or  $TAV_ID == '' ) $tdps='Non défini';
    else {
        // type de DPS choisi
        $querydps="select TAV_ID, TA_VALEUR from type_agrement_valeur
               where TA_CODE = 'D'
               and TAV_ID=".$TAV_ID;
        $resultdps=mysqli_query($dbc,$querydps);
        $rowdps=mysqli_fetch_array($resultdps);
        $tdps = $rowdps["TA_VALEUR"];
        
        //comparer avec agrément
        $queryag="select a.S_ID, a.A_DEBUT, a.A_FIN, tav.TAV_ID, tav.TA_VALEUR,
                    DATEDIFF(NOW(), a.A_FIN) as NB_DAYS
                    from agrement a, type_agrement_valeur tav
                    where a.TA_CODE=tav.TA_CODE
                    and a.TAV_ID= tav.TAV_ID
                    and a.TA_CODE='D'
                    and a.S_ID in (".$S_ID.",".get_section_parent("$S_ID").")";
        $resultag=mysqli_query($dbc,$queryag);
        $rowag=mysqli_fetch_array($resultag);
        $debut = $rowag["A_DEBUT"];
        $tag = $rowag["TA_VALEUR"];
        $tagid = $rowag["TAV_ID"];
        $nbd = $rowag["NB_DAYS"];
        $sectionag = $rowag["S_ID"];

        if ( $tagid <> "" and ( !$print)) {
            if ( $TAV_ID > $tagid or $debut == '') {
                $title="ATTENTION Il n'y a pas d'agrément ou l'agrément est insuffisant pour ce type de DPS.";
                if ( $tagid > 1 and $debut <> '') 
                    $title .=" L'agrément permet seulement l'organisation de DPS de type $tag.";
                $warn_img="<i class='fa fa-exclamation-circle fa-lg' style='color:red;' title=\"$title\" ></i>";
            }
            else if  ( $nbd > 0  ) 
                $warn_img="<i class='fa fa-exclamation-circle fa-lg' style='color:red;' title=\"ATTENTION agrément pour les DPS périmé\" ></i>";
            else if ( $DPS_MAX_TYPE <> '' and $DPS_MAX_TYPE < $TAV_ID ) {
                $warn_img="<i class='fa fa-exclamation-triangle' style='color:orange' title=\"ATTENTION le $levels[3] ne permet pas à cette $levels[4] d'organiser ce type de DPS\" border=0></i>";
                $warn="<a href=upd_section.php?S_ID=".$S_ID.">".$warn_img."</a>";
            }        
            else
                $warn_img="<i class='fa fa-check fa-lg' style='color:green'
                    title=\"Agrément valide pour ce type de DPS\"></i>";
                    
            if ( $warn == '')    
                $warn="<a href=upd_section.php?S_ID=".$sectionag."&status=agrements>".$warn_img."</a>";
        }
    }
    if ( $E_FLAG1 == 1 ) $interassociatif='<b>Inter-associatif</b>, ';
    else $interassociatif='';
    echo "<tr><td width=30%><b>Type de DPS </b></td>
        <td width=70%> ".$interassociatif." ".$tdps." ".$warn."</td></tr>";

}

if ( $E_CONVENTION <> "" ) {
    echo "<tr><td width=30%><b>Numéro de convention </b></td>
        <td width=70%> ".$E_CONVENTION."</td></tr>";
}    
if ( $E_DATE_ENVOI_CONVENTION <> "" ) {
    echo "<tr><td width=30%><b>Date envoi convention </b></td>
        <td width=70%> ".$E_DATE_ENVOI_CONVENTION."</td></tr>";
}
if ($E_CLOSED == 0  and  $nbsections == 0 and  $TE_PERSONNEL == 1) { 
   if ( $E_OPEN_TO_EXT == 1 && $E_ALLOW_REINFORCEMENT == 1 ) 
           $cmt="Possibles pour les personnes des autres ".$levels[3]."s et pour les ".$renfort_label."s.";
   elseif ( $E_OPEN_TO_EXT == 1 && $E_ALLOW_REINFORCEMENT == 0 ) 
           $cmt="Possibles pour les personnes extérieures.";
   elseif ( $E_OPEN_TO_EXT == 0 && $E_ALLOW_REINFORCEMENT == 1 ) 
           $cmt="Impossibles pour les personnes des autres ".$levels[3]."s, mais possible pour les ".$renfort_label."s.";
   else 
        $cmt="Impossibles pour les personnes des autres ".$levels[3]."s et pour les ".$renfort_label."s."; 
}
else  {
     if ( $E_OPEN_TO_EXT == 1) 
           $cmt="Possibles pour les personnes des autres ".$levels[3]."s.";
       else 
           $cmt="Impossibles pour les personnes des autres ".$levels[3]."s";
}
if ( ! $print and ! $gardeSP and $TE_PERSONNEL == 1 and $TE_CODE <> 'MC')
echo "<tr><td width=30%><b>Inscriptions</b></td> 
             <td width=70%>".$cmt."</td></tr>";
          
if ( $E_COMMENT <> "" ) {
    echo "<tr><td width=30%><b>Détails </b></td>
        <td width=70%> ".$E_COMMENT."</td></tr>";
}
if ( $E_VISIBLE_OUTSIDE == 1 ) {
    echo "<tr><td width=30%><b>Visible de l'extérieur </b></td>
        <td width=70%>Peut être vu dans un site externe sans identification <i class='fa fa-exclamation-triangle noprint' style='color:orange' title=\"Visible de l'extérieur\"></i></td></tr>";
}
if ( $E_COMMENT2 <> "" ) {
    echo "<tr><td width=30%><b>Commentaire extérieur </b></td>
        <td width=70%> ".$E_COMMENT2."</td></tr>";
}

if ( $C_ID <> '' and $C_ID > 0 ) {
     echo "<tr><td ><b>Pour le compte de </b></td>";
     if (check_rights($id, 37)) $company="<a href=upd_company.php?C_ID=".$C_ID.">".get_company_name($C_ID)."</a>";
     else $company=get_company_name($C_ID);
     echo "<td >".$company."</td></tr>";
 
     // responsable formation ou opérationnel
    $queryr="select p.P_ID, p.P_NOM, p.P_PRENOM, ".phone_display_mask('p.P_PHONE')." P_PHONE , tcr.TCR_DESCRIPTION
                from pompier p, company_role cr, type_company_role tcr 
                where p.P_ID=cr.P_ID
                and tcr.TCR_CODE = cr.TCR_CODE
                and cr.C_ID=".$C_ID;
    if ( $TE_CODE == 'FOR' ) $queryr .=" and cr.TCR_CODE='RF'";
    else $queryr .=" and cr.TCR_CODE='RO'";
    $resultr=mysqli_query($dbc,$queryr);
    $rowr=mysqli_fetch_array($resultr);
    $TCR_DESCRIPTIONr =  $rowr["TCR_DESCRIPTION"];
    $P_IDr         =  $rowr["P_ID"];
    $P_NOMr     =  $rowr["P_NOM"];
    $P_PRENOMr     =  $rowr["P_PRENOM"];
    $P_PHONEr     =  $rowr["P_PHONE"];
    if     ( $P_IDr <> "" ) {
        if ($P_PHONEr <> '') {
            if (is_iphone())
                 $phone=" - <a href='tel:".$P_PHONEr."'>".$P_PHONEr."</a>";
             else 
                $phone = " - ".$P_PHONEr."";
        }
        else $phone="";
        echo "<tr><td ><b>".$TCR_DESCRIPTIONr."</b></td>";
        echo "<td >
        <a href=upd_personnel.php?pompier=".$P_IDr.">".my_ucfirst($P_PRENOMr)." ".strtoupper($P_NOMr)."</a>".$phone."</td></tr>";
        
    }
 
}

if ( $granted_event and ( $E_CONTACT_LOCAL <> '' or $E_CONTACT_TEL <> '')) {
     echo "<tr><td width=25% ><b>Contact sur place </b></td>";
     if ( $E_CONTACT_TEL <> '') {
         if (is_iphone()) $E_CONTACT_TEL=" - <a href='tel:".$E_CONTACT_TEL."'>".$E_CONTACT_TEL."</a> ";
         else $E_CONTACT_TEL="- ".$E_CONTACT_TEL;
     }
     echo "<td width=75%>".$E_CONTACT_LOCAL." ".$E_CONTACT_TEL."</td></tr>";
}

// équipes, groupes (seulement pour événement principal)
if ($E_PARENT == '' and  $TE_PERSONNEL == 1 and $TE_CODE <> 'MC') {
    $querym="select EE_ID, EE_NAME, EE_DESCRIPTION from evenement_equipe
        where E_CODE=".$evenement."
        order by EE_ORDER,EE_NAME";
    $resultm=mysqli_query($dbc,$querym);
    $nbm=mysqli_num_rows($resultm);

    $showcpt = "<tr><td width=30%><b>Equipes </b></td><td>";

    while ( $rowm=mysqli_fetch_array($resultm) ) {
        $EE_ID=$rowm["EE_ID"];
        $type=$rowm["EE_NAME"];
        $desc=$rowm["EE_DESCRIPTION"];
        $showcpt .= " <a href=evenement_equipes.php?evenement=".$evenement."&equipe=".$EE_ID."&action=update>".$type."</a>,";
    }
    $showcpt = rtrim($showcpt,',');

    if ( !$print and ( $granted_event or is_operateur_pc($id,$evenement)))
        $showcpt .= " <a href=evenement_equipes.php?evenement=".$evenement." title=\"Voir l'organisation des équipes\">
            <i class='fa fa-edit fa-lg'></i></a>  ";
    $showcpt .= "</td></tr>";
    print $showcpt;
}

if ($nbsections == 0 ) {
//------------------------
// Renforts
//------------------------
    $queryA="select e.E_CODE as CE_CODE, e.E_CANCELED as CE_CANCELED, e.E_CLOSED as CE_CLOSED,
                s.S_CODE CS_CODE, s.S_DESCRIPTION CS_DESCRIPTION
                from evenement e, section s
                where e.E_PARENT=".$evenement."
                and e.S_ID = s.S_ID
                order by s.S_CODE, e.E_CODE";
    $resultA=mysqli_query($dbc,$queryA);
    $nb_renforts=mysqli_num_rows($resultA);
    
    if ( $nb_renforts > 0  or $E_COLONNE_RENFORT == 1) {
        // ajout possible si colonne de renfort
        if ( $E_COLONNE_RENFORT == 1 and $granted_event and $E_CLOSED == 0) {
            $url="evenement_modal.php?action=colonne&evenement=".$evenement;
            $plus= write_modal( $url, "add_renfort", "<i class='fa fa-plus-circle fa-lg' style='color:green'  title='Ajouter ".$renfort_label."'></i>");
        }
        else
            $plus="";
    
        echo "<tr><td colspan=2><b>".ucfirst($renfort_label)."s</b> ".$plus."</td></tr>";
                 
        while ( custom_fetch_array($resultA)) {
            if ( $CE_CANCELED == 1 ) {
                 $color="red";
                 $info="événement annulé";
            }
            elseif ( $CE_CLOSED == 1 ) {
                   $color="orange";
                   $info="événement clôturé";
            }
            else {
                   $color="green";
                   $info="événement ouvert";
            }
            if ($granted_event and ! $print and $changeallowed)
                  $cancelbtn = "<a href=\"javascript:cancel_renfort('".$evenement."','".$CE_CODE."')\">
                    <i class='fa fa-trash' title='détacher ce renfort' ></i></a>";
            else $cancelbtn ='';
            
            echo "<tr><td colspan=2> <a href=evenement_display.php?evenement=".$CE_CODE.">
                <i class='fa fa-plus-square lg' style='color:".$color."' title='$info' ></i></a>
                <a href=evenement_display.php?evenement=".$CE_CODE.">".ucfirst($renfort_label)." de ".$CS_CODE." - ".$CS_DESCRIPTION."</a> ".$cancelbtn."</a> ";
            
            $queryR="select eh.EH_ID,
            DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m') as EH_DATE_DEBUT0,
            DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m') as EH_DATE_FIN0,
            eh.EH_DATE_DEBUT as EH_DATE_DEBUT1,
            TIME_FORMAT(eh.EH_DEBUT, '%k:%i') EH_DEBUT0,  
            TIME_FORMAT(eh.EH_FIN, '%k:%i') EH_FIN0
            from evenement e, evenement_horaire eh
            where eh.E_CODE = e.E_CODE
            and e.E_CODE=".$CE_CODE."
            order by eh.EH_ID";
            $resultR=mysqli_query($dbc,$queryR);
            $nbpr=mysqli_num_rows($resultR);
             //$nbsessions=sizeof($EH_ID);
            //$nummaxpartie=max($EH_ID);

            $EH_ID0= array();
            $EH_DEBUT0= array();
            $EH_DATE_DEBUT0= array();
            $EH_DATE_DEBUT1= array();
            $EH_DATE_FIN0= array();
            $EH_FIN0= array();
            $j=1;
            // mettre les dates pour ce renfort dans un tableau
            while ( $rowR=@mysqli_fetch_array($resultR)) {
                $EH_DATE_DEBUT0[$j]=$rowR["EH_DATE_DEBUT0"];
                $EH_DATE_DEBUT1[$j]=$rowR["EH_DATE_DEBUT1"];
                $EH_DATE_FIN0[$j]=$rowR["EH_DATE_FIN0"];
                $EH_DEBUT0[$j]=$rowR["EH_DEBUT0"];
                $EH_FIN0[$j]=$rowR["EH_FIN0"];
                $EH_ID0[$j]=$rowR["EH_ID"];
                if ( $EH_DATE_DEBUT0[$j] <> $EH_DATE_FIN0[$j] ) $dates_renfort=$EH_DATE_DEBUT0[$j] ." au ".$EH_DATE_FIN0[$j];
                else $dates_renfort=$EH_DATE_DEBUT0[$j];
                $detail_renfort[$j]=$dates_renfort." - ".$EH_DEBUT0[$j]."-".$EH_FIN0[$j];
                $j++;
            }
            
            // boucle sur les dates de l'événement principal
            $j=1;$c="";
            if ( $E_COLONNE_RENFORT == 1 ) {
                if ( evenements_overlap( $evenement, $CE_CODE )) echo "<i class='fa fa-clock fa-lg' style='color:green'  title=\"".$detail_renfort[$j]."\"></i>";
                else echo "<i class='fa fa-ban fa-lg' style='color:red'  title=\"Les dates de ".$renfort_label." ne correspondent pas à celles de l'événement principal.\"></i>";
                echo " <small>".$detail_renfort[$j]."</small>";
            }
            else {
              for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
                if (isset($EH_ID[$i])) {
                     if (isset($EH_ID0[$j]) 
                         and $EH_DATE_DEBUT1[$j]==$EH_DATE_DEBUT[$i] 
                        and ($EH_DEBUT0[$j]==$EH_DEBUT[$i] or ($nbpr == 1 and $nbsessions == 1))) {
                          if ( $nbpr == 1 ) $c = " <font size=1>".$detail_renfort[$j]."</font>";
                          if ( $CE_CANCELED == 0 ) $clock="green";
                        else {
                             $clock="red";
                             $detail_renfort[$j] = "ANNULE";
                        }
                        echo "<i class = 'fa fa-clock fa-lg' style='color:". $clock."' title=\"".$detail_renfort[$j]."\"></i>";
                        $j++;
                     }
                     else {
                        echo "<i class = 'fa fa-ban' style='color:red' title=\"".ucfirst($renfort_label)." non activé pour la Partie n°".$EH_ID[$i]."\"></i>";
                    }
                }
              }
            }
            echo $c." </td></tr>";
        }
    }
}

//------------------------
// type de formation
//------------------------

if ( $TE_CODE == 'FOR' ){
    if ( intval($PS_ID_FORMATION) == 0 ) {
        $_TYPE="";
        $_DESCRIPTION="<i>non défini</i>";
    }
    else {
        $query2="select PS_ID, TYPE, DESCRIPTION from poste where PS_ID =".intval($PS_ID_FORMATION);
        $result2=mysqli_query($dbc,$query2);
        $row2=@mysqli_fetch_array($result2);
        $_TYPE=$row2["TYPE"];
        $_DESCRIPTION=$row2["DESCRIPTION"];
    }
    echo "<tr><td><b>Formation pour</td><td><span class='badge noprint'>".$_TYPE."</span> ".$_DESCRIPTION."</td></tr>";
    
    if ( $TF_CODE == '' ) {
        $_TF_LIBELLE="<i>non défini</i>";
    }
    else {
        $query2="select TF_LIBELLE from type_formation where TF_CODE='".$TF_CODE."'";
        $result2=mysqli_query($dbc,$query2);
        $row2=@mysqli_fetch_array($result2);
        $_TF_LIBELLE=$row2["TF_LIBELLE"];
    }
    echo "<tr><td><b>Type de formation</td><td>".$_TF_LIBELLE."</td></tr>";
}

//=====================================================================
// stats
//=====================================================================

if ( $TE_MAIN_COURANTE == 1 and intval($E_PARENT) == 0 ){
    $queryN="select TB_NUM,TB_LIBELLE from type_bilan where TE_CODE='".$TE_CODE."' order by TB_NUM";
    $resultN=mysqli_query($dbc,$queryN);
    if ( mysqli_num_rows($resultN) > 0 ) {
        if ( $granted_event) $K="<a href=evenement_display.php?from=interventions&evenement=".$evenement." title=\"Modifier les statistiques dans l'onglet Rapport\">Statistiques</a>";
        else $K="Statistiques:";
        $S=get_main_stats($evenement);
        $finished=my_date_diff($LAST_DAY,getnow());
        if ( strlen($S) == 0 ) {
            if ( $finished > 0 )
            $S = "<a href=evenement_display.php?from=interventions&evenement=".$evenement." 
            title=\"Modifier les statistiques dans l'onglet Rapport\">
            <span class='badge' style='background-color:red;'> Aucune statistique enregistrée</span></a>";
        }
        echo "<tr>
         <td width=25%><b>".$K."</b></td>
           <td width=75%>".$S."</td>
        </tr>";
    }
} 
echo "</table>";
echo "</tr>";

//=====================================================================
// email notifications
//=====================================================================

if ( $granted_event  and ! $print and $changeallowed  and ! $gardeSP and $TE_CODE <> 'MC' and $E_VISIBLE_INSIDE == 1) {

   echo "<tr><td CLASS='MenuRub' colspan=2>Notifications par email</td></tr>";
   echo "<tr><td CLASS='Menu' bgcolor=$mylightcolor colspan=2>";

   echo "<table class='noBorder' >";
     
     // email ouverture
     if ( $E_CLOSED == 0 and $E_CANCELED == 0 ) {
        echo "<tr><td ><i class='fa fa-at fa-lg'></i></td>";
         echo "<td ><font size=1 >
         Avertir tout le personnel que le nouvel événement a été créé. </td>";
         if ( $E_MAIL1 == 0 ) {
             echo "<td ><input type='button' class='btn btn-default' value='envoyer' onclick='bouton_redirect(
             \"evenement_notify.php?evenement=".$evenement."&action=enroll\",\"notify\");'></td></tr>";
         }
         else echo "<td ><i class='fa fa-check fa-lg' title='déjà envoyé'></i></td></tr>";
     }
     
     // email cloture
     if ( $E_CLOSED == 1 and $E_CANCELED == 0 ) {
        echo "<tr><td ><i class='far fa-envelope'></i></td>";
         echo "<td ><font size=1 >
             Envoyer au personnel inscrit (liste validée) les informations relatives à l'événement.</td>";
         if ( $E_MAIL2 == 0 ) {
             echo "<td ><input type='button' class='btn btn-default' value='envoyer' onclick='bouton_redirect(
             \"evenement_notify.php?evenement=".$evenement."&action=closed\",\"notify\");'></td></tr>";
         }
         else echo "<td ><i class='fa fa-check-square fa-lg' style='color:green;' title='déjà envoyé'></i></td></tr>";
     }
         
     // email annulation
     if ( $E_CANCELED == 1 ) {
        echo "<tr><td ><i class='far fa-envelope'></i></td>";
         echo "<td ><font size=1>
         Avertir le personnel inscrit que l'événement a été annulé.</td>";
         if ( $E_MAIL3 == 0 ) {
             echo "<td ><input type='button' class='btn btn-default' value='envoyer' 
             onclick='bouton_redirect(\"evenement_notify.php?evenement=".$evenement."&action=canceled\",\"notify\");'></td></tr>";
         }
         else echo "<td ><i class='fa fa-check-square fa-lg' style='color:green;' title='déjà envoyé'></i></td></tr>";
     }    
    
 echo "</table>";
 echo "</tr>";
 
}

//=====================================================================
// bouton de retour et de modification
//=====================================================================
echo "</table></td><p class='noprint'>";

if (! $print) {
    echo "<div align=center>";
    if ( $from == "export" ) {
        echo "<input type='button' class='btn btn-default' value='fermer cette page' onclick='fermerfenetre();'> ";
    }
    elseif ( $from == "personnel" ) {
        echo "<input type='button' class='btn btn-default' value='retour' 
        onclick='bouton_redirect(\"upd_personnel.php?from=inscriptions&pompier=".$pid."\");'> ";
    }
    elseif ( $from == "personnel_note" ) {
        echo "<input type='button' class='btn btn-default' value='retour' 
        onclick='bouton_redirect(\"upd_personnel.php?from=notes_de_frais&pompier=".$pid."\");'> ";
    }
    elseif ( $from == "diplomes" or $from == "history" or $from="note" ) {
        if ( ! is_iphone())
                echo "<input type='button' class='btn btn-default' value='retour' onclick='javascript:history.back(1);'> ";
    }
    else {
        echo "<input type='button' class='btn btn-default' value='retour' 
        onclick='bouton_redirect(\"evenement_choice.php\");'> ";
    }

    if ( $granted_event and $changeallowed) {
      echo " <input type='button' class='btn btn-default' value='modifier' 
      onclick='bouton_redirect(\"evenement_edit.php?evenement=".$evenement."&action=update\",\"update\");'> ";
    }
    if ( ! $gardeSP and $granted_event ) {
        echo " <input type='button' class='btn btn-default' value='dupliquer' title='dupliquer cet événement'
        onclick='bouton_redirect(\"evenement_edit.php?evenement=".$evenement."&action=copy\",\"copy\");'> ";
        if ($TE_MULTI_DUPLI == 1 and $nbsessions == 1 ) {
            echo " <input type='button' class='btn btn-default' value='duplication multiple' 
            onclick='bouton_redirect(\"evenement_duplicate.php?evenement=".$evenement."\",\"update\");'> ";
        }
    }
    if ( $granted_delete and ! $gardeSP) {
        $csrf = generate_csrf('delete');
        echo " <input type='button' class='btn btn-default' value='supprimer' 
        onclick='bouton_redirect(\"evenement_save.php?action=delete&evenement=".$evenement."&csrf_token_delete=".$csrf."\",\"delete\");'> ";
    }
    else if ( $granted_delete and $gardeSP and $E_PARENT <> NULL ) {
        $csrf = generate_csrf('delete');
        echo " <input type='button' class='btn btn-default' value='supprimer' 
        onclick='bouton_redirect(\"evenement_save.php?action=delete&evenement=".$evenement."&csrf_token_delete=".$csrf."\",\"delete\");'> ";
        }

    if ( $nbsections == 0 and ! $gardeSP
        and ( check_rights($id, 15) or $chef )
        and  $E_ALLOW_REINFORCEMENT == 1  
        and  $E_PARENT == '' ) {
            if ( $E_CLOSED == 1 or $E_CANCELED == 1 ) $disabled='disabled';
            else $disabled="";

            // pour une ADPC on peut créer les renforts pour chaque antenne
            $level_orga = get_level("$S_ID");
            if ( ( $assoc or $syndicate or $sdis ) and ($level_orga == $nbmaxlevels - 2  or $level_orga <= 2  )) {
                if ( $level_orga <= 2 ) $t=$levels[3];
                else $t=$levels[4];
                
                echo "<div class='dropdown show' style='display: inline-block;'>
                          <a class='btn btn-default dropdown-toggle' href='#' role='button' id='dropdownMenuLink1' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' 
                            title='créer des événements en ".$renfort_label." de celui-ci'>
                              <i class='fa fa-plus'></i> ".$renfort_label."
                          </a>
                          <div class='dropdown-menu' aria-labelledby='dropdownMenuLink1'>
                            <a class='dropdown-item' href='evenement_edit.php?evenement=".$evenement."&action=renfort'>Créer ".$renfort_label." simple</a></li>
                            <a class='dropdown-item' href='evenement_multi_renforts.php?evenement=".$evenement."'>Créer ".$renfort_label." pour chaque ".$t."</a>
                          </div>
                      </div>";
            }
            else 
                echo "<input type='button' class='btn btn-default' value='créer ".$renfort_label."' title='créer un événement en ".$renfort_label." de celui-ci' $disabled
                    onclick='bouton_redirect(\"evenement_edit.php?evenement=".$evenement."&action=renfort\",\"renfort\");'> ";
    }

    //=====================================================================
    // boutons d'inscription /désinscription
    //=====================================================================

    $query="select DATEDIFF(NOW(), ep.EP_DATE) as NB_DAYS 
                from evenement_participation ep, evenement e
                where ep.E_CODE = e.E_CODE
                and ( e.E_CODE=".$evenement." or e.E_PARENT=".$evenement.")
                and ep.P_ID=".$id;
    $r1=mysqli_query($dbc,$query);
    $num=mysqli_num_rows($r1);

    if ( $E_CLOSED == 0  and $E_CANCELED == 0  and (! $print ) and  (!$gardeSP) and $TE_PERSONNEL == 1){
        if ( my_date_diff(date('d')."-".date('m')."-".date('Y'),$very_end) >= 0 ) {
            if ( $num == 0 ) {
                $disabled_inscr="";
                // si photo_obligatoire on peut bloquer les inscriptions
                if ( $photo_obligatoire ) {
                    $photo = get_photo($id);
                    if ( $photo == '' ) {
                        $since=get_nb_days_since_creation($id);
                        if ( $since > $limit_days_photo ) 
                            $disabled_inscr=" disabled title=\"Inscription interdite: Vous n'avez pas enregistré votre photo\" ";
                    }
                }
                // attention si il y a déjà inscription sur principal, bloquer le bouton s'inscrire
                $query2="select count(*) as NB from evenement_participation ep, evenement e
                    where ep.E_CODE = e.E_CODE
                    and e.E_CODE =(select E_PARENT from evenement where E_CODE=".$evenement." )
                    and ep.P_ID=".$id;
                $r2=mysqli_query($dbc,$query2);
                $rowd=@mysqli_fetch_array($r2);
                $num2=$rowd["NB"];
                if ($num2 > 0 and $disabled_inscr == '' ) 
                    $disabled_inscr=" disabled title=\"Inscription interdite: Vous êtes déjà inscrit sur l'événement principal\" ";
                
                if (( $OPEN_TO_ME == 1 ) and check_rights($id, 39))
                echo " <input type='button' class='btn btn-default' value=\"s'inscrire\" $disabled_inscr
                onclick='bouton_redirect(\"evenement_inscription.php?evenement=".$evenement."&action=inscription\",\"inscription\");'> ";
                if ( $OPEN_TO_ME == -1 )
                echo " <input type='button' class='btn btn-default' value=\"s'inscrire\" $disabled_inscr
                onclick=\"alert('Votre inscription sur cet événement extérieur ne peut être faite que par votre responsable.');\"> ";
                if (( $OPEN_TO_ME == -2 ) or ( $OPEN_TO_ME == -3 ))
                    echo " <input type='button' class='btn btn-default' value=\"s'inscrire\" $disabled_inscr
                    onclick=\"alert('Votre inscription sur cet événement national ou régional ne peut être faite que par votre responsable.');\"> ";
            }
            else {
                $row=mysqli_fetch_array($r1);
                if ( $row["NB_DAYS"] < 1 and $row["NB_DAYS"] >= 0) $show_btn=true;
                else if ( $granted_event and $changeallowed) $show_btn=true;
                else $show_btn=false;

                if (check_rights($id, 39) and $show_btn )
                    echo " <input type='button' class='btn btn-default' value=\"se désinscrire\" onclick='bouton_redirect(\"evenement_inscription.php?evenement=".$evenement."&action=desinscription\",\"desinscription\");'>";
            }
        }
        if ( $granted_event ) {
            echo " <input type='button' class='btn btn-default' value='clôturer' title='fermer les inscriptions pour cet événement et ses ".$renfort_label."s'
            onclick='bouton_redirect(\"evenement_inscription.php?evenement=".$evenement."&action=close\",\"close\");' > ";
        }
    }
    if ($E_CLOSED == 1 and $E_CANCELED == 0  and ! $print ) {
        if ( $granted_event and $changeallowed) {
            // ne pas permettre d'ouvrir un renfort sui le principal est fermé
            $queryd="select E_CLOSED from evenement where E_CODE =".$E_PARENT;
            $resultd=mysqli_query($dbc,$queryd);
            $rowd=@mysqli_fetch_array($resultd);
            $c=$rowd["E_CLOSED"];
            if ( $c == 1 and ! check_rights($id, 14) and ! $chef ) { 
                $disabled="disabled"; 
                $t="On ne peut pas réouvrir un ".$renfort_label." pour lequel l'événement principal est clôturé";
            }
            else { 
                $disabled=''; 
                $t="ouvrir les inscriptions pour cet événement et ses ".$renfort_label."s";
            }
            echo " <input type='button' class='btn btn-default' value='ouvrir' $disabled title=\"".$t."\" 
            onclick='bouton_redirect(\"evenement_inscription.php?evenement=".$evenement."&action=open\",\"open\");' >";
        }
    }
    if ( check_rights($id, 11) and $num > 0 and ! $print  and ! $gardeSP )
        echo " <input type='button' class='btn btn-default' value=\"Note de frais\" title='Créer une note de frais pour me faire rembourser de mes dépenses sur cet événement'
                onclick='bouton_redirect(\"note_frais_edit.php?evenement=".$evenement."&action=insert&person=".$id."\",\"note de frais\");'>";
    echo "</div>";
}

echo "</div><p style='margin-bottom:200px' class='noprint'>";

}

//=====================================================================
// participants
//=====================================================================
if ( $tab == 2  or $print ){
    $tableau_visible=true;
    $nboptions=count_entities("evenement_option", "E_CODE in (".$evenement.",".intval($E_PARENT).")");
    if ( $gardeSP and $E_VISIBLE_INSIDE == 0 ) {
        if ( $sdis == 1 and ! check_rights($id, 6, "$organisateur")) $tableau_visible=false;
        if ( $sdis == 0 and ! check_rights($id, 6))  $tableau_visible=false;
    }
    if  ( $tableau_visible ) {
        $_date = $EH_DATE_DEBUT[1];
        $last = $EH_DATE_FIN[$nbsessions];
        $found=false;
        $found2=false;
        $select_style= "style='background-color:$mydarkcolor; color:white;'";
        if ( $nbsessions == 2 and $gardeSP) {
            $date_selector =", Montrer le personnel <select name='evenement_periode' id='evenement_periode' onchange=\"change_periode('".$evenement."')\" $select_style >";
            $date_selector .="<option value='0' selected $select_style> sur toutes les périodes</option>";
            if ( $evenement_periode == '1' ) {
                    $selected = 'selected';
                    $found2=true;
            }
            else $selected = '';
            $date_selector .="<option value='1' $selected $select_style>présent le jour</option>";
            if ( $evenement_periode == '2' ) {
                    $selected = 'selected';
                    $found2=true;
            }
            else $selected = '';
            $date_selector .="<option value='2' $selected $select_style>présent la nuit</option>";
            $date_selector .="</select>";
        }    
        else if ( $_date <> $last  ) {
            $date_selector =", Montrer le personnel <select name='evenement_date' id='evenement_date' onchange=\"change_date('".$evenement."')\" $select_style>";
            $date_selector .="<option value='' selected $select_style> sur toutes les dates</option>";
            while ( $_date <> $last ) {
                $tmp = explode ("-",$_date);
                $year=$tmp[0]; $month=$tmp[1]; $day=$tmp[2];
                if ( $evenement_date == $_date ) {
                    $selected = 'selected';
                    $found=true;
                }
                else $selected = '';
                $date_selector .="<option value='".$_date."' $selected $select_style>présent le ".$day."-".$month."-".$year."</option>";
                $real_date = date_create($_date);
                date_modify($real_date, '+1 day');
                $_date = date_format($real_date, 'Y-m-d');
            }
            $tmp = explode ("-",$_date);
            $year=$tmp[0]; $month=$tmp[1]; $day=$tmp[2];
            if ( $evenement_date == $_date ) {
                $selected = 'selected';
                $found=true;
            }
            else $selected = '';
            $date_selector .="<option value='".$_date."' $selected $select_style>présent le ".$day."-".$month."-".$year."</option>";
            $date_selector .="</select>";
        }
        else $date_selector="";

        
        if ( $evenement_show_competences == 1 ) $checked='checked';
        else $checked='';
        
        if ( $competences == 1 ) 
            $competences_checkbox = "<label for='evenement_show_competences' style='margin-right: 6px;'>Montrer les compétences </label><input type=checkbox id='evenement_show_competences' name='evenement_show_competences' values='1' $checked 
                    title='Montrer les compétences du personnel'
                    onchange=\"show_competences('".$evenement."')\"
                    style='margin-right: 20px;'>";
        else      
            $competences_checkbox = "";
        
        if ( $evenement_show_absents == 1 ) $checked='checked';
        else $checked='';
        
        $absents_checkbox = "<label for='evenement_show_absents' style='margin-right: 6px;'>Montrer les absents </label><input type=checkbox id='evenement_show_absents' name='evenement_show_absents' values='1' $checked 
                    title='Montrer le personnel inscrit mais absent'
                    onchange=\"show_absents('".$evenement."','2')\"
                    style='margin-right: 20px;'>";
                    
         
        if ( $E_NB == 0 ) $cmt = "Pas de limite sur le nombre";
        else $cmt = "requis ".$E_NB;
        if ( $TE_CODE == 'FOR' and  $E_NB_STAGIAIRES > 0 ) $cmt .=", dont places stagiaires $E_NB_STAGIAIRES";

        $queryf="select count(1) from type_participation where TE_CODE='".$TE_CODE."'";
        if ( $gardeSP )    $queryf .=    " and EQ_ID in (0,".intval($E_EQUIPE).")";
        $resultf=mysqli_query($dbc,$queryf);
        $nbfn=mysqli_num_rows($resultf);


        // trouver tous les participants
        $query="select distinct e.E_PARENT, ep.EP_DATE, ep.E_CODE as EC, p.P_ID, p.P_NOM, ".phone_display_mask('p.P_PHONE')." P_PHONE, p.P_PRENOM, p.P_GRADE, g.G_DESCRIPTION, s.S_ID, 
                p.P_HIDE, p.P_STATUT, p.P_OLD_MEMBER, s.S_CODE, p.P_EMAIL, p.C_ID, p.P_CIVILITE, p.TS_CODE, tp.TP_LIBELLE, ee.EE_NAME,
                EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),p.P_BIRTHDATE))))+0 AS AGE,
                max(ep.TSP_ID) TSP_ID,tp.PS_ID, tp.PS_ID2,
                ep.EP_FLAG1, ep.TP_ID, ep.EE_ID, ep.EP_COMMENT, ep.EP_KM, ep.EP_BY, ee.EE_DESCRIPTION, ep.EP_REMINDER, max(ep.EP_ASTREINTE) EP_ASTREINTE,
                ep.EP_ASA, ep.EP_DAS, tsp.TSP_CODE, tsp.TSP_COLOR, g.G_LEVEL,
                y.P_NOM P_NOM_BY, y.P_PRENOM P_PRENOM_BY,
                case
                when tp.TP_NUM is null then 1000
                else tp.TP_NUM
                end
                as TP_NUM
                from evenement_participation ep 
                left join type_participation tp on tp.TP_ID = ep.TP_ID
                left join pompier y on y.P_ID = ep.EP_BY
                left join evenement_equipe ee on (ee.E_CODE in (".$evts_list.") and ee.EE_ID=ep.EE_ID)
                left join type_statut_participation tsp on tsp.TSP_ID = ep.TSP_ID,
                pompier p left join grade g on g.G_GRADE=p.P_GRADE, section s, evenement e, evenement_horaire eh
                where ep.E_CODE in (".$evts.")
                and eh.E_CODE = ep.E_CODE
                and ep.EH_ID = eh.EH_ID
                and e.E_CODE = ep.E_CODE
                and p.P_ID=ep.P_ID
                and p.P_SECTION=s.S_ID";
                
        if ( $evenement_show_absents == 0 ) 
            $query .= "    and ep.EP_ABSENT = 0 ";

        if ( $evenement_date <> '' and $found ) {
            $query .= "
                        and ((  eh.EH_DATE_FIN >= '".$evenement_date."' and  eh.EH_DATE_DEBUT <= '".$evenement_date."' and ep.EP_DATE_DEBUT is null)
                          or ( ep.EP_DATE_FIN >= '".$evenement_date."' and  ep.EP_DATE_DEBUT <= '".$evenement_date."'))";
        }
        else if ( $evenement_periode > 0 and $found2 ) {
            $query .= "    and ep.EH_ID=".$evenement_periode;
        }
        $query .= " group by ep.P_ID";
        if ( $order == 'EE_NAME' ) $query .= " order by EE_NAME desc, p.P_NOM";
        else if ( $order == 'TP_LIBELLE' ) $query .= " order by TP_LIBELLE desc, p.P_NOM";
        else if ( $order == 'TSP_ID' ) $query .= " order by TSP_ID, p.P_NOM";
        else if ( $order == 'E_PARENT' ) $query .= "    order by e.E_PARENT, ep.E_CODE asc, p.P_NOM";
        else if ( $order == 'P_NOM' ) $query .= "    order by p.P_NOM, ep.EP_DATE asc";
        else if ( $order == 'G_LEVEL' ) $query .= "    order by g.G_LEVEL desc, p.P_NOM";
        else if ( $gardeSP and $grades ) $query .= "    order by e.E_PARENT, TP_NUM, ep.E_CODE asc, g.G_LEVEL desc, p.P_NOM";
        else $query .= " order by p.P_NOM, ep.EP_DATE asc";
        write_debugbox( $query);
        $result=mysqli_query($dbc,$query);
        $nbparticipants=mysqli_num_rows($result);
        $listePompiers = "";
        $arrayPompiers=array();
        $mailist = "";
        if ( $nbparticipants > 0 or $NP > 0 ) {
            if ( $print ) echo "<table cellspacing=0 border=0 width=800>";
            else echo "<table cellspacing=0 border=0 >";
            if ( $gardeSP and $grades ) $colspan=10;
            else $colspan=9;
            if ( $print ) $colspan = $colspan -1;
            if ( $date_selector <> '' ) {
                $date_selector .= " soit ".$nbparticipants." personnes.";
                if ( $gardeSP and $nbsessions == 2 ) {
                    $get_ev = get_inscrits_garde($evenement,1);
                    if (empty($get_ev))
                        $inscrits1=0;
                    else
                        $inscrits1=count(explode(",",$get_ev));

                    $get_ev = get_inscrits_garde($evenement,2);
                    if (empty($get_ev))
                        $inscrits2=0;
                    else
                        $inscrits2=count(explode(",",$get_ev));

                    $date_selector .= " Dont jour <span class='badge' style='background-color:yellow; color:$mydarkcolor' title='inscrits jour'>".$inscrits1."</span> ";
                    $date_selector .= " nuit <span class='badge' style='background-color:#CEE3F6; color:$mydarkcolor' title='inscrits nuit'>".$inscrits2."</span>";
                }
            }
            if ( $print ) $date_selector='';
            echo "<tr CLASS='TabHeader'><td colspan=$colspan>Participants: ".$cmt." , inscrits $NP, présents $NP2 ".$date_selector." </td></tr>
                  <tr bgcolor=$mylightcolor >";
            if ( $gardeSP and $grades ) echo "<td width=40 class=small><a href=evenement_display.php?evenement=".$evenement."&tab=2&order=G_LEVEL title='trier par Grade décroissant'>Grade</a></td>";
            if ( $print ) echo "<td width=290 class=small>Personnel présent</td>";
            else {
                echo "<td width=330 class=small>";
                if ( "$evts" <> "$evenement" ) echo " <a href=evenement_display.php?evenement=".$evenement."&tab=2&order=E_PARENT title='trier par ".$renfort_label."'>".ucfirst($renfort_label)."</a> /";
                echo " <a href=evenement_display.php?evenement=".$evenement."&tab=2&order=P_NOM title='trier par Nom'>Inscrits</a></td>";
            }
            if ( ! $print) echo "<td style='max-width:160px; min-width:60px' class=small align=left>Horaires</td>";
            if ( $nbfn > 0 ){
                if ($granted_event and (!$print) and $changeallowed) echo "<td width=170 class=small><a href=evenement_display.php?evenement=".$evenement."&tab=2&order=TP_LIBELLE title='trier par fonction'>Fonction</a></td>";
                else echo "<td width=100 class=small>Fonction</td>";
            }
            else echo "<td></td>";
            if ($nbsections == 0) echo "<td width=130 class=small><a href=evenement_display.php?evenement=".$evenement."&tab=2&order=EE_NAME title='trier par équipe'>Equipe</a></td>";
            else echo "<td></td>";
            if (! $print and $changeallowed ) echo "<td width=70 class=small>Infos</td>";
            if (! $print and $nboptions > 0 ) echo "<td width=70 class=small>Options</td>";
            if ( $gardes == 0 and $TE_VICTIMES == 1)
                echo "<td width=22 class=small><a href=evenement_display.php?evenement=".$evenement."&tab=2&order=TSP_ID title='trier par statut'>Statut.</a></td>";
            else echo "<td></td>";
            echo "<td colspan=2></td>";

            $prevEC='';
            while (custom_fetch_array($result)) {
                // affiche les infos pour ce renfort
                if ( $EC <> $prevEC ) {
                    $queryR="select e.E_CANCELED as CE_CANCELED, e.E_CLOSED as CE_CLOSED, eh.EH_ID,
                    s.S_CODE CS_CODE, s.S_DESCRIPTION CS_DESCRIPTION,
                    DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m') as EH_DATE_DEBUT0,
                    DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m') as EH_DATE_FIN0,
                    TIME_FORMAT(eh.EH_DEBUT, '%k:%i') EH_DEBUT0,  
                    TIME_FORMAT(eh.EH_FIN, '%k:%i') EH_FIN0,
                    eh.EH_DATE_DEBUT as EH_DATE_DEBUT1
                    from evenement e, section s, evenement_horaire eh
                    where e.S_ID = s.S_ID
                    and eh.E_CODE = e.E_CODE
                    and e.E_CODE=".$EC."
                    order by eh.EH_ID";
                    $resultR=mysqli_query($dbc,$queryR);
                    $EH_DATE_DEBUT0 = Array();
                    $EH_DATE_DEBUT0 = Array();
                    $EH_DEBUT0 = Array();
                    $EH_FIN0 = Array();
                    $EH_DATE_DEBUT1 = Array();
                    $EH_ID0=array();
                    $horaire_renfort = Array();
                
                    $n=1;
                    while ( $rowR=@mysqli_fetch_array($resultR)) {
                        $EH_ID0[$n]=$rowR["EH_ID"];
                        $EH_DATE_DEBUT0[$n]=$rowR["EH_DATE_DEBUT0"];
                        $EH_DATE_DEBUT1[$n]=$rowR["EH_DATE_DEBUT1"];
                        $EH_DATE_FIN0[$n]=$rowR["EH_DATE_FIN0"];
                        $EH_DEBUT0[$n]=$rowR["EH_DEBUT0"];
                        $EH_FIN0[$n]=$rowR["EH_FIN0"];
                        $CE_CANCELED=$rowR["CE_CANCELED"];
                        $CE_CLOSED=$rowR["CE_CLOSED"];
                        $CS_CODE=$rowR["CS_CODE"];
                        $CS_DESCRIPTION=$rowR["CS_DESCRIPTION"];
                        if ( $CE_CANCELED == 1 ) {
                            $color="red";
                            $info="événement annulé";
                        }
                        elseif ( $CE_CLOSED == 1 ) {
                            $color="orange";
                            $info="événement clôturé";
                        }
                        else {
                            $color="green";
                            $info="événement ouvert";
                        }
                        if ( $EH_DATE_DEBUT0[$n] <> $EH_DATE_FIN0[$n] ) $dates_renfort=$EH_DATE_DEBUT0[$n] ." au ".$EH_DATE_FIN0[$n];
                        else $dates_renfort=$EH_DATE_DEBUT0[$n];
                        $horaire_renfort[$n]=$dates_renfort." - ".$EH_DEBUT0[$n]."-".$EH_FIN0[$n];
                        $n++;
                    }
                    if ( $EC <> $evenement and $order == 'E_PARENT') {
                        if ( mysqli_num_rows($resultR) == 1 ) $dt=$horaire_renfort[1];
                        else $dt="";
                        echo "<tr bgcolor=$mylightcolor ><td>
                        <b><i><a href=evenement_display.php?evenement=$EC&from=inscription>
                        <i class='fa fa-plus-square lg' style='color:".$color."' title='$info' ></i>
                        ".ucfirst($renfort_label)."s de ".$CS_CODE."</i></b></a>
                        </td>
                        <td colspan=8><b><i>".$dt."</i></b></td></tr>";
                    }
                    $prevEC = $EC;
                }
                $HORAIRE = get_horaire($P_ID, $E_CODE);

                if ( $gardeSP and $grades ) $P_GRADE="<img src=".$grades_imgdir."/".$P_GRADE.".png title='".$G_DESCRIPTION."' class='img-max-22' >";
                else $P_GRADE="";
                $F_PS_ID=$PS_ID;
                $F_PS_ID2=$PS_ID2;
                $TP_ID=intval($TP_ID);
                if ( $TS_CODE == 'SC' ) $SC = True;
                else $SC = False;
                if ( $P_EMAIL <> "" ) $mailist .= $P_ID.",";
                if ( intval($P_PHONE) > 0 ) {
                    if (is_iphone())
                        $P_PHONE=" <small><a href='tel:".$P_PHONE."'>".$P_PHONE."</a></small>";
                    else $P_PHONE=" - ".$P_PHONE;
                    if (( ($P_HIDE == 1) ) and ( $nbsections == 0 )) {
                        if (( ! $ischef ) 
                        and ( ! in_array($id,$chefs) )
                        and (! check_rights($id, 2))
                        and (! check_rights($id, 12)))
                            $P_PHONE=" ***** ";
                    }
                }
                else $P_PHONE="";
                $listePompiers .= $P_ID.",";
                if ( in_array($P_ID,$arrayPompiers)) $warn_duplicate_pid=true; 
                else {
                    $arrayPompiers[] = $P_ID;
                    $warn_duplicate_pid=false;
                }
              
                if ( is_children($S_ID,$organisateur)) $prio=true; 
                else $prio=false; 

                if ( check_rights($id, 10,"$S_ID")) $granted_update=true;
                else $granted_update=false;
              
                // récupérer horaires de la personne dans un tableau
                $clock="";
                $EP_DATE_DEBUT=array();
                $EP_DATE_FIN=array();
                $EP_DATE_DEBUT1=array();
                $EP_DATE_FIN1=array();
                $EP_DEBUT=array();
                $EP_FIN=array();
                $EP_ABSENT=array();
                $EP_EXCUSE=array();
                $EP_FLAG1=array();
                $EH_ID1=array();
                $full_absent=true;
              
                $query_horaires="select EH_ID,
                   DATE_FORMAT(EP_DATE, '%d-%m %H:%i') as EP_DATE, 
                   DATE_FORMAT(EP_DATE_DEBUT,'%d-%m-%Y') EP_DATE_DEBUT, 
                   DATE_FORMAT(EP_DATE_FIN,'%d-%m-%Y') EP_DATE_FIN,
                   TIME_FORMAT(EP_DEBUT, '%k:%i') EP_DEBUT,  
                   TIME_FORMAT(EP_FIN, '%k:%i') EP_FIN,
                   EP_DATE_DEBUT EP_DATE_DEBUT1,
                   EP_DATE_FIN EP_DATE_FIN1,
                   EP_ABSENT,EP_EXCUSE, EP_ASTREINTE, EP_FLAG1
                   from evenement_participation
                   where E_CODE=".$EC."
                   and P_ID=".$P_ID."
                   order by EH_ID";
                $resultH=mysqli_query($dbc,$query_horaires);
                $j=1;
                while ( $rowH=@mysqli_fetch_array($resultH)) {
                    $EH_ID1[$j]=$rowH["EH_ID"];
                    $EP_DATE_DEBUT[$j]=$rowH["EP_DATE_DEBUT"];    // DD-MM-YYYY
                    $EP_DATE_FIN[$j]=$rowH["EP_DATE_FIN"];
                    $EP_DATE_DEBUT1[$j]=$rowH["EP_DATE_DEBUT1"];  // YYYY-MM-DD
                    $EP_DATE_FIN1[$j]=$rowH["EP_DATE_FIN1"];
                    $EP_DEBUT[$j]=$rowH["EP_DEBUT"];
                    $EP_FIN[$j]=$rowH["EP_FIN"];
                    $EP_ABSENT[$j]=$rowH["EP_ABSENT"];
                    if ( $EP_ABSENT[$j] == 0 ) $full_absent = false;
                    $EP_EXCUSE[$j]=$rowH["EP_EXCUSE"];
                    $EP_ASTREINTE[$j]=$rowH["EP_ASTREINTE"];
                    $EP_FLAG1[$j]=$rowH["EP_FLAG1"];
                    $j++;
                }
                if ( $E_COLONNE_RENFORT == 1 ) {
                    $overlap = evenements_overlap( $evenement, $EC );
                }
                else $overlap = false;
              
                // boucle sur les dates de l'événement principal
                $j=1;$clock="";$p1=0;$p2=0;
                for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
                    $subclock="";
                    if (isset($EH_ID[$i])) {
                        if (( isset($EH_ID0[$j]) 
                            and $EH_DATE_DEBUT1[$j]==$EH_DATE_DEBUT[$i] 
                            and ( $EH_DEBUT0[$j]==$EH_DEBUT[$i]  or ( $nbsessions == 1 and sizeof($EH_ID0) == 1))
                            ) or ( $overlap )) { // renfort actif sur cette partie
                            $num_partie=$EH_ID0[$j];
                            if (in_array($num_partie, $EH_ID1)) { // personne inscrite
                                $key = array_search($num_partie, $EH_ID1);
                                 if ($EP_FLAG1[$key]==0 and $P_STATUT =="SPP"){
                                    $normalicon="purple";
                                    $normalicon2="#cc00ff";
                                    $titleprefix="Garde en tant que SPV ";
                                }
                                else if ( $EP_ASTREINTE[$key] == 1 ) {
                                    $normalicon="blue";
                                    $normalicon2="#3333cc";
                                    $titleprefix="ASTREINTE (garde non rémunérée) ";
                                }
                                else {
                                    $normalicon="green";
                                    $normalicon2="orange";
                                    $titleprefix="";
                                }
                                if ($nbsessions == 1 ) $t=" de l'événement";
                                else $t=" de la partie n°".$num_partie;
                                if ( $EP_ABSENT[$key] == 1 ) {
                                    if ( $EP_EXCUSE[$key] == 0 ) $n='non excusée';
                                    else $n='excusée';
                                    $subclock ="<i class='fa fa-clock fa-lg' style='color:darkgrey' title=\"Absence ".$n."\ncliquer pour modifier\"></i>";
                                }
                                elseif ( $EP_DATE_DEBUT[$key] <> "" ) {
                                    if ( $EP_DATE_DEBUT[$key] == $EP_DATE_FIN[$key] ) $horaire_p[$key]= substr($EP_DATE_DEBUT[$key],0,5).", ".$EP_DEBUT[$key]."-".$EP_FIN[$key];
                                    else $horaire_p[$key]= substr($EP_DATE_DEBUT[$key],0,5)." au ".substr($EP_DATE_FIN[$key],0,5).", ".$EP_DEBUT[$key]."-".$EP_FIN[$key];
                                    $subclock ="<i class='fa fa-clock fa-lg' style='color:".$normalicon2."' title=\"".$titleprefix."horaires différents de ceux $t \n".$horaire_p[$key]."\"></i>";
                                }
                                else if ( isset($horaire_renfort[$i])) $subclock ="<i class='fa fa-clock fa-lg' style='color:".$normalicon."'  title=\"".$titleprefix."horaires identiques à ceux $t \n".$horaire_renfort[$i]."\"></i>";
                                else $subclock ="<i class='fa fa-clock fa-lg' style='color:".$normalicon."' title=\"".$titleprefix."horaires identiques à ceux de la partie n°".$i." \n".$horaire_renfort[$j]."\"></i>";
                                if ( $num_partie == 1 ) {
                                    $tmp_arr = explode(":",$EH_DEBUT[$i]);
                                    $heure_deb = $tmp_arr[0];
                                    if ( intval($heure_deb) >= 18 ) $p2=1;
                                    else $p1=1;
                                }
                                else if ( $num_partie == 2 ) $p2=1;
                            }
                            else if ( $E_COLONNE_RENFORT == 1 ) {
                                if ( $overlap ) $subclock ="<i class='fa fa-clock fa-lg' style='color:green'></i>";
                                else $subclock ="<i class='fa fa-ban fa-lg' style='color:red' title=\"Les dates de ".$renfort_label." ne correspondent pas à celles de l'événement principal.\"></i>";
                            }
                            else $subclock ="<i class='far fa-circle fa-lg' style='color:grey' title=\"Pas inscrit(e) pour la Partie n°".$EH_ID[$i]."\"></i>";
                            $j++;
                        }
                        else {
                            if ( $E_COLONNE_RENFORT == 1 ) $subclock ="<i class='fa fa-ban fa-lg' style='color:red' title=\"Les dates de ".$renfort_label." ne correspondent pas à celles de l'événement principal\"></i>";
                            else $subclock ="<i class='fa fa-ban fa-lg' style='color:red'  title=\"".ucfirst($renfort_label)." inactif pour la Partie n°".$EH_ID[$i]."\"></i>";
                        }
                    }
                    if ( $CE_CANCELED == 1 and $subclock <> "" ) $subclock = "<i class='fa fa-clock fa-lg' style='color:red'  title=\"annulé\"></i>";
                    $clock .= $subclock;
                }
              
                // Cas garde SP, vérifier la dispo des SPV, sinon Warning
                $warnclock="";
                if ( $gardeSP and $P_STATUT == 'SPV') {
                    $query1="select sum( d.PERIOD_ID * d.PERIOD_ID ) as NUM from disponibilite d where d.P_ID=".$P_ID." and d.D_DATE='".$year1[1]."-".$month1[1]."-".$day1[1]."'";
                    $result1=mysqli_query($dbc,$query1);
                    custom_fetch_array($result1);
                    $label=dispo_label($NUM);
                    $array_jour=array(30,5,14,21);
                    $array_nuit=array(30,25,26,29);
                    $array_aprem=array(30,5,13,4,14,20,21,29);
                    $heure_debut_garde = intval(substr($EH_DEBUT0[1],0,2));
                    if ( $NUM == 0                                                                  ||
                        ( $p1 == 1 and $heure_debut_garde < 12 and ! in_array($NUM,$array_jour))    ||
                        ( $p1 == 1 and $heure_debut_garde >= 12 and ! in_array($NUM,$array_aprem))  ||
                        ( $p2 == 1 and ! in_array($NUM,$array_nuit))  )        
                        $warnclock = " <i class='fa fa-exclamation-triangle fa-lg' style='color:red' title=\"Attention: ce SPV n'a pas la disponibilité suffisante pour cette garde ".$label."\"></i>";
                }
              
                if ( $EP_FLAG1[1] == 1 and $EP_COMMENT <> '') $txtimg="sticky-note' style='color:purple;";
                else if ( $EP_FLAG1[1] == 1 ) $txtimg="sticky-note' style='color:#7D62A4;";
                else if ( $EP_COMMENT <> '' ) $txtimg="sticky-note";
                else $txtimg="sticky-note' style='color:grey;";

                if ( $TP_ID == 0 ) { $F_PS_ID=0; $F_PS_ID2=0; }
                if ( $EP_BY <> "" and $EP_BY <> $P_ID) {
                    $inscritPar="par ".my_ucfirst($P_PRENOM_BY)." ".strtoupper($P_NOM_BY);
                }
                else $inscritPar="";
                $popup="Inscrit le: ".$EP_DATE;
                if ( $gardeSP and $P_STATUT == 'SPP' and $EP_FLAG1[1] == 1 ) $popup .= "\nGarde en qualité de SPP";
                else if ( $EP_FLAG1[1] == 1 ) {
                    if ( $SC ) $ss = "service civique";
                    else $ss = "salarié(e)";
                    $popup .= "\nParticipation en tant que ".$ss;
                }
                if ( $EP_COMMENT <> "" ) $popup .= "\nCommentaire: ".$EP_COMMENT;

                $myimg="";
                if ( $gardeSP ) { // vérifier que pas inscrit sur 2 tableaux de gardes
                    $querySP="select count(1) from evenement_participation ep, evenement e, evenement_horaire eh
                        where ep.P_ID=".$P_ID." 
                        and ep.E_CODE = e.E_CODE 
                        and e.E_CODE <> ".$evenement." 
                        and eh.E_CODE = ep.E_CODE
                        and ep.EH_ID = eh.EH_ID
                        and e.TE_CODE='GAR'
                        and e.E_CODE = eh.E_CODE
                        and eh.EH_DATE_DEBUT = '".$year1[1]."-".$month1[1]."-".$day1[1]."'";
                    $resultSP=mysqli_query($dbc,$querySP);
                    $rowSP=@mysqli_fetch_array($resultSP);
                    $autre_garde=$rowSP[0];
                    if ( $autre_garde > 0 ) $myimg="<i class='fa fa-exclamation' style='color:red' title='attention cet agent est parallèlement inscrit sur une autre garde'></i>";
                }
                if ( $nbsessions == 1 and ! $gardeSP and $nbparticipants < 30 and $TE_CODE <> 'MC' ) {
                    $nb = get_nb_inscriptions($P_ID, $year1[1], $month1[1], $day1[1], $year2[$nummaxpartie], $month2[$nummaxpartie], $day2[$nummaxpartie], 0, $EC) ;
                    if ( $nb > 1 ) 
                        $myimg="<i class='fa fa-exclamation' style='color:red'  title='attention cet agent est parallèlement inscrit sur $nb autres événements'></i>";
                    else if ( $nb == 1 )
                        $myimg="<i class='fa fa-exclamation' style='color:#ff8000'  title='attention cet agent est parallèlement inscrit sur 1 autre événement'></i>";
                }
              
                $cmt=""; 
                if ( $P_OLD_MEMBER > 0 ) {
                    $altcolor="<font color=black>";
                    $extcmt="ATTENTION: Ancien membre";
                }
                else if ( $gardeSP and $P_STATUT == 'SPP' and $EP_FLAG1 == 0 ) {
                    $altcolor="<font color=purple>";
                    $extcmt="SPP de ".$S_CODE." en garde SPPV";
                }
                else if ( $P_STATUT=='SPP') {
                    $altcolor="<font color=red>";
                    $extcmt="SPP de ".$S_CODE;
                }
                else if ( $P_STATUT=='EXT') {
                    $altcolor="<font color=green>";
                    $extcmt="Personnel externe ".get_company_name("$C_ID");
                }
                else {
                    $altcolor=(($prio)?"":"<font color=purple>");
                    $extcmt=$S_CODE;
                }
                if ( $P_CIVILITE > 3 ) 
                    $cmt="<span class='badge' style='background-color:purple; color:white; font-size:9px; padding:2px;'>chien</span>";
                else if ( $AGE <> '' )
                    if ($AGE < 18 ) $cmt="<span class='badge' style='background-color:red; color:white; font-size:9px; padding:2px;'>-18</span>";
            
                // nouvelle ligne
                if ( ! $print or ! $full_absent ) {
                    $date = $year1[1]."-".$month1[1]."-".$day1[1];
                    $SP_SPECIFIC_TEXT="";
                    if ( $gardeSP and $P_STATUT == 'SPV' ) {
                        $SP_DISPO_TIME_DAY = dispo_hr_spp ($P_ID, $date); // les heures dispo
                        $SP_HORAIRE_GARDE = get_horaire($P_ID, $E_CODE); // horaires prévus de garde
                        $SP_HORAIRE_GARDE = $SP_HORAIRE_GARDE[1];
                        $free_time = $SP_DISPO_TIME_DAY - $SP_HORAIRE_GARDE;
                        if($free_time > 0)
                            $SP_SPECIFIC_TEXT = "<br><small><span style='color:black; font-style:italic; '>Dispo restante: ".$free_time."h</span></small>";
                    }
                    echo "<tr bgcolor=$mylightcolor>";
                    if ( $gardeSP and $grades ) echo "<td align=left >".$altcolor.$P_GRADE."</font></td>";
                    echo "<td style='padding-left:3px;'><a href=upd_personnel.php?pompier=$P_ID title=\"$extcmt\">".$altcolor.strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</a> ".$cmt." ".$P_PHONE;
                    if ( $warn_duplicate_pid ) echo "<i class='fa fa-exclamation-triangle' style='color:orange' title='Attention cette personne apparaît plusieurs fois dans la liste'></i>";
                    echo $SP_SPECIFIC_TEXT;
                    echo "</td>";

                    // affiche horaires
                    if ( ! $print) {
                        $url="evenement_horaires.php?evenement=".$EC."&pid=".$P_ID."&vid=0";
                        if (! $changeallowed ) echo "<td>".$clock.$warnclock."</td>";
                        
                        else if ($granted_event or ($P_ID == $id and $E_CLOSED == 0 and $PARENT_CLOSED == 0) or ($granted_update and $E_CLOSED == 0 and $PARENT_CLOSED == 0) 
                            or ($granted_inscription and (check_rights($id,15,"$organisateur") or $gardeSP)) ) {
                            echo "<td>" ;
                            print write_modal($url,"Horaires_".$P_ID, $clock) ;
                            echo $warnclock."</td>";
                        }
                        else 
                            echo "<td>".$clock.$warnclock."</td>";
                    }
                  
                    // compétences
                    $required_comp = intval($F_PS_ID + $F_PS_ID2);
                    $postes ="";
                    if ( $evenement_show_competences == 1 ) $postes=get_competences($P_ID, $TE_CODE);
                    else if ( $required_comp > 0 ) $null=get_competences($P_ID, $TE_CODE);
                    
                    // affiche fonctions / équipes
                    if (($granted_personnel or $granted_event or $granted_update or ($granted_inscription and $gardeSP))
                        and ! $print and $changeallowed ) {
                        if ( $nbfn > 0 ) {
                            $warnflag="";
                            if ( $required_comp > 0  and ! $found) {
                                $warnflag="<i class='fa fa-exclamation-triangle fa-lg' style='color:orange' title=\"Attention cette personne n'est pas qualifiée pour assurer cette fonction\"></i>";
                            }
                            if (  ($granted_event or ($granted_inscription and $gardeSP) ) and $changeallowed ) {
                                // choix fonction
                                $url="evenement_modal.php?action=fonction&evenement=".$evenement."&pid=".$P_ID;
                                if ( $TP_ID == "" or $TP_ID == 0 ) $TP_LIBELLE="<div id='divfn".$P_ID."' style='color:grey; font-size:9px; font-style: italic;' title='sélectionner une fonction'>choisir fonction</div>";
                                else $TP_LIBELLE="<div id='divfn".$P_ID."' style='font-size:9px; font-style: italic;' title='changer la fonction'>".$TP_LIBELLE." ".$warnflag."</div>";
                                echo "<td>";
                                print write_modal( $url, "fonction_".$P_ID, $TP_LIBELLE);
                                echo "</td>";
                            }
                            else {
                                if ( $TP_ID == "" or $TP_ID == 0 ) $TP_LIBELLE="";
                                echo  "<td><span style='font-size:9px; font-style: italic;'>".$TP_LIBELLE."</span></a> ".$warnflag."</td>";
                            }
                        }
                        else echo "<td></td>";

                        // choix équipe
                        if ( $nbe > 0 ) {
                            if (! $granted_event or ! $changeallowed) {
                                if ( $EE_ID == "" or $EE_ID == 0 ) $EE_NAME="";
                                echo  "<td><font size=1>".$EE_NAME." </font></a></td>";
                            }
                            else {
                                // choix équipe
                                $url="evenement_modal.php?action=equipe&evenement=".$evenement."&pid=".$P_ID;
                                if ( $EE_ID == "" or $EE_ID == 0 ) $EE_NAME="<div id='divpe".$P_ID."' style='color:grey; font-size:9px; font-style:italic;' title='Choisir une équipe'>Choisir équipe</div>";
                                else $EE_NAME="<div id='divpe".$P_ID."' style='font-size:9px; font-style:italic;' title='Changer équipe'>".$EE_NAME."</div>";
                                echo "<td>";
                                print write_modal( $url, "equipe_".$P_ID, $EE_NAME);
                                echo "</td>";
                            }
                        }
                        else echo "<td></td>";
                    }
                    else { // impression ou personne sans habilitations
                        if ( $nbfn > 0 ) {
                            if ( $TP_ID == "" ) $TP_LIBELLE="-";
                            echo "<td><font size=1>".$TP_LIBELLE."</font></td>";
                        }
                        else echo "<td></td>";
                        if ( $nbe > 0 ) {
                            if ( $EE_ID == "" ) $EE_NAME="-";
                            echo "<td><font size=1>".$EE_NAME."</font></td>";
                        }
                        else echo "<td></td>";
                    }
                      
                    // Infos
                    if (! $print and $changeallowed ) {
                        $textbox_disabled='disabled';
                        $can_save=false;
                        if (($granted_personnel or $granted_event or $granted_update or $id == $P_ID)) {
                            $textbox_disabled='';
                            $can_save=true;
                        }
                      
                        if ( $EP_REMINDER == 1 and $cron_allowed == 1 and $P_EMAIL <> "") 
                            $bell="<i class='fa fa-bell' style='color:red;' title=\"Une notification de rappel sera envoyée la veille par mail\" ></i>";
                        else
                            $bell='';
                        
                        if ( $EP_ASTREINTE == 1 ) {
                            $garde_astreinte="<i class='fa fa-exclamation-triangle' style='color:orange;' title=\"Astreinte (garde non rémunérée) sur les parties de la garde montrant une horloge bleue ou orange.\" ></i>";
                        }
                        else {
                            $garde_astreinte='';
                        }
                      
                        if ( $EP_KM <> '' ) $_km="<span class=small2 title='$EP_KM km parcourus en véhicule personnel'>".$EP_KM."</a></span>";
                        else $_km='';
                        if ( $EP_ASA == 1 ) $_asa="<span class=small2 title=\"Autorisation spéciale d'absence\">ASA</a></span>";
                        else $_asa='';
                        if ( $EP_DAS == 1 ) $_das="<span class=small2 title=\"Décharge d'activité de service\">DAS</a></span>";
                        else $_das='';
                      
                        echo "<td>
                          <table class='noBorder'><tr><td>";
                        $url="evenement_info_participant.php?evenement=".$evenement."&pid=".$P_ID;
                        print write_modal( $url, "infos_".$P_ID,"<i class='fa fa-".$txtimg."' title=\"".$popup."\"></i>");
                        echo "</td>";
                        echo "<td>";
                        print write_modal( $url, "infos_".$P_ID ,$garde_astreinte." ".$bell." ".$_km." ".$_asa." ".$_das." ".$myimg);
                        echo "</td>";
                        echo "</tr></table>";
                        echo "</td>";
                    }
                    
                    // Options inscriptions
                    if (! $print and $nboptions > 0 ) {
                        if ( $granted_inscription or $id == $P_ID ) {
                            if ( intval($E_PARENT) > 0 ) $e=$E_PARENT;
                            else $e=$evenement;
                            $nbchoix=count_entities("evenement_option_choix","P_ID=".$P_ID." and E_CODE=".$e);
                            if (  $nbchoix > 0 ) {
                                $color='green';
                                $title="Voir et modifier les options d'inscription pour cette personne";
                            }
                            else {
                                $color='red';
                                $title="Renseigner les options d'inscription pour cette personne";
                            }
                            echo "<td><a href='evenement_option_choix.php?evenement=".$evenement."&pid=".$P_ID."' title=\"".$title."\"><i class ='fa fa-cog ' style='color:".$color."'></i></td>";
                        }
                        else echo "<td></td>";
                    }
                    // statut participation?
                    // sur les activités opérationnelles associatives on peut choisir un statut
                    if ( $gardes == 0 and $TE_VICTIMES == 1 and ! $print) {
                        $url="evenement_modal.php?action=statut&evenement=".$evenement."&pid=".$P_ID;
                        if ( $granted_event )
                            $tsp= write_modal( $url, "statut_".$P_ID, "<div id='sp".$P_ID."' style='color:".$TSP_COLOR.";' ><i class='fa fa-info-circle' title=\"Statut, cliquer pour modifier: ".$TSP_CODE."\" ></i></div>");
                        else
                            $tsp="<i class='fa fa-info-circle' title=\"Statut: ".$TSP_CODE."\" ></i>";
                    }
                    else
                        $tsp="";
                    echo "<td>".$tsp."</td>";
                  
                    // suppression
                    if (($granted_event or ($granted_inscription and (check_rights($id,15,"$organisateur") or $gardeSP)) )
                        and ! $print and $changeallowed and ( $E_CLOSED == 0 or $chef or check_rights($id,14))) {
                        echo "<td >
                        <a href=\"javascript:desinscrire('".$evenement."','".$EC."','".$P_ID."');\" title='désinscrire' >
                        <i class='fa fa-trash'></i></a> </td>";
                    }
                    else echo "<td ></td>";
                  
                    echo "<td><span class='small'>".$postes."</span></td>";
                    echo "</tr>";
                } // ! $print or ! $full_absent
            }
            echo "</table>";
        }
        else echo "Aucun personnel inscrit. (".$cmt.").<br>";

        //=====================================================================
        // inscrire d'autres personnes
        //=====================================================================

        echo "<p class='noprint'>";
        if ( $gardeSP and $granted_event ) {
            if ( $E_ANOMALIE == 1 ) $checked='checked';
            else $checked='';
            echo  " <label for='evenement_anomalie' style='margin-right: 6px;'>Garde en anomalie </label><input type=checkbox id='evenement_anomalie' name='evenement_anomalie' values='1' $checked 
                    title='Cocher si cette garde présente une anomalie'
                    onchange=\"change_anomalie('".$evenement."')\"
                    style='margin-right: 20px;'>";
        }
        echo "<span class='noprint'>";
        echo $competences_checkbox;
        echo "</span>";
        echo "<span class='noprint'>";
        echo $absents_checkbox;
        echo "</span><br>";
        
        if ( $from == "calendar" ) {
            echo "<input type='button' class='btn btn-default' value='retour' onclick='javascript:history.back(1);'> ";
        }

        if ( $E_CLOSED == 0  and  $E_CANCELED == 0  and ! $print and $changeallowed){
            if ( $granted_personnel  or  $granted_inscription ) {
                if ( $syndicate == 1 ) $label = "Inscrire";
                else $label = "Personnel";
                if ( $gardeSP ) $cat='personnel_garde';
                else $cat='personnel';
                echo " <label class='btn btn-default btn-file' title='inscrire du personnel' onclick=\"inscrire(".$evenement.",'".$cat."')\">
                    <i class='fa fa-plus' ></i> $label
                    </label>"; 
                    
                if ( $sdis == 1 ) // deuxième bouton pour pouvoir inscrire du personnel des autres centres
                    echo " <label class='btn btn-default btn-file' title='inscrire du personnel des autres centres' onclick=\"inscrire(".$evenement.",'personnel')\">
                    <i class='fa fa-plus' ></i> Externes
                    </label>"; 

                else if ( $externes == 1 and ! $gardeSP ) {
                    echo " <label class='btn btn-default btn-file' title='inscrire des externes' onclick=\"inscrire(".$evenement.",'personnelexterne')\">
                    <i class='fa fa-plus' ></i> Externes
                    </label>"; 
                    if (check_rights($id, 37)) {
                        echo " <label class='btn btn-default btn-file' title='créer et inscrire externes' onclick=\"nouvel_externe(".$evenement.");\"> 
                        <i class='fa fa-user-plus' ></i> Créer Externes
                        </label>";
                    }
                }
            }
        }

        if ( ! $print ) {
            // demande de remplacement
            if ( $remplacements and is_present($id,$evenement)) {
                echo " <p class='noprint'><label class='btn btn-default' title='Je veux demander à être remplacé' onclick=\"bouton_redirect('remplacement_edit.php?replaced=".$id."&evenement=$evenement');\">
                    <i class='fa fa-user-times' ></i> Me faire remplacer
                    </label>";
            }
            
            if ( $gardeSP ) {
                echo "<p class='noprint'>";
                // garde veille
                $date_veille = date('Y-m-d', strtotime($year1[1].'-'.$month1[1].'-'.$day1[1].' - 1 days'));
                $garde_veille=get_garde_jour(0, $E_EQUIPE, $date_veille);
                if ( $garde_veille  > 0 )
                    echo " <label class='btn btn-default' title='Garde précédente' onclick=\"bouton_redirect('evenement_display.php?evenement=$garde_veille&from=gardes');\">
                        <i class='fa fa-chevron-left' ></i>
                        </label>"; 
                        
                // retour tableau garde
                echo " <input class='btn btn-default' type='button' value='tableau de garde' style='vertical-align:top;' 
                  onclick='bouton_redirect(\"tableau_garde.php?equipe=".$E_EQUIPE."&filter=".$organisateur."&month=".$month1[1]."&year=".$year1[1]."\");'> ";
                
                // garde suivante
                $date_suivante = date('Y-m-d', strtotime($year1[1].'-'.$month1[1].'-'.$day1[1].' + 1 days'));
                $garde_suivante=get_garde_jour(0, $E_EQUIPE, $date_suivante);
                
                if ( $garde_suivante  > 0 ) {
                    if ( substr($date_suivante,8,2) <> '01' or $granted_event) 
                        echo "<label class='btn btn-default' title='Garde suivante' onclick=\"bouton_redirect('evenement_display.php?evenement=$garde_suivante&from=gardes');\">
                        <i class='fa fa-chevron-right' ></i>
                        </label>";
                }
            }
        }

        if((strlen($listePompiers)-1)>1 and  $granted_event and ! $print and $changeallowed){
            echo  "<form name='FrmEmail' method='post' action='mail_create.php'>";
            echo  "<input type='hidden' name='Messagebody' value=\"".str_replace("'","",$E_LIBELLE)."\">
              <input type='hidden' name='SelectionMail'
                    value=\"".rtrim($listePompiers,',')."\" />";
            if ( check_rights($id, 43)) {
                echo "<input type='submit' class='btn btn-default noprint' value='message' title=\"envoyer un message aux inscrits à partir de l'application web\"/>";
                echo " <input type='button' class='btn btn-default noprint' onclick=\"getListMails('".rtrim($mailist,',')."');\" value=\"ListeMails\" title=\"Récupérer la liste des adresses email des inscrits\">";
                echo " <input type='button' class='btn btn-default noprint' onclick=\"getListContacts('".rtrim($mailist,',')."');\" value=\"ListeContacts\" title=\"Récupérer la liste des contacts au format csv, pour les importer dans un groupe de messagerie\">";
            }
            if ( $mailist <> "" ) 
                echo " <input type='button' value='mailto' class='btn btn-default noprint'
                    onclick=\"DirectMailTo('".rtrim($mailist,',')."','".$evenement."')\" 
                    title=\"envoyer un message aux inscrits à partir de votre logiciel de messagerie\"/>";
            echo "</form>";
        }
    }
    else {
         echo "<p class='noprint'>Le tableau de garde n'est pas accessible par le personnel.</p>";
    }
}
//=====================================================================
// véhicules demandés
//=====================================================================
if ( (( $tab == 3 ) or ($print)) and ( $vehicules == 1 )) {

$queryf="select TFV_ID, TFV_NAME from type_fonction_vehicule order by TFV_ORDER";
$resultf=mysqli_query($dbc,$queryf);
$fonctions=array();
while ($rowf=@mysqli_fetch_array($resultf)) {
    array_push($fonctions, array($rowf["TFV_ID"],$rowf["TFV_NAME"]));
}
$nbfn=sizeof($fonctions);

$query="select distinct ev.E_CODE as EC,v.V_ID,v.V_IMMATRICULATION,v.TV_CODE, vp.VP_LIBELLE, v.V_MODELE, v.V_INDICATIF,
        vp.VP_ID, vp.VP_OPERATIONNEL, s.S_DESCRIPTION, s.S_ID, s.S_CODE, ev.EV_KM,
        DATE_FORMAT(v.V_ASS_DATE, '%d-%m-%Y') as V_ASS_DATE,
        DATE_FORMAT(v.V_CT_DATE, '%d-%m-%Y') as V_CT_DATE,
        DATE_FORMAT(v.V_REV_DATE, '%d-%m-%Y') as V_REV_DATE,
        ee.EE_ID, ee.EE_NAME,
        tfv.TFV_ID, tfv.TFV_NAME,
        tv.TV_ICON
        from vehicule v, type_vehicule tv, vehicule_position vp, section s, evenement e, evenement_vehicule ev
        left join evenement_equipe ee on (ee.E_CODE in (".$evts_list.") and ee.EE_ID=ev.EE_ID)
        left join type_fonction_vehicule tfv on ev.TFV_ID = tfv.TFV_ID
        where v.V_ID=ev.V_ID
        and e.E_CODE=ev.E_CODE
        and s.S_ID=v.S_ID
        and tv.TV_CODE = v.TV_CODE
        and vp.VP_ID=v.VP_ID
        and ev.E_CODE in (".$evts.")
        order by e.E_PARENT, ev.E_CODE asc";
$result=mysqli_query($dbc,$query);

$nbvehic=mysqli_num_rows($result);
if ( $nbvehic > 0 ) {
    if ( $print ) echo "<table cellspacing=0 border=0 width=800>";
    else echo "<table cellspacing=0 border=0 >";
    echo "<tr><td CLASS='MenuRub'>Véhicules</td></tr>";
    echo "<tr><td CLASS='Menu' bgcolor=$mylightcolor>";
    echo "<table class='noBorder'>";
    $prevEC='';
    while (custom_fetch_array($result)) {
        if ( $TV_ICON == "" ) $vimg="";
        else $vimg="<img src=".$TV_ICON." height=25 class='noprint'>";
      
        if ( $V_MODELE == "" ) $vehicule_string = $TV_CODE;
        else $vehicule_string = $TV_CODE." - ".$V_MODELE;
      
        // affiche d'où vient le renfort
        if ( $EC <> $prevEC ) {
            $queryR="select e.E_CANCELED as CE_CANCELED, e.E_CLOSED as CE_CLOSED, eh.EH_ID,
                s.S_CODE CS_CODE, s.S_DESCRIPTION CS_DESCRIPTION,
                DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m') as EH_DATE_DEBUT0,
                DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m') as EH_DATE_FIN0,
                TIME_FORMAT(eh.EH_DEBUT, '%k:%i') EH_DEBUT0,  
                TIME_FORMAT(eh.EH_FIN, '%k:%i') EH_FIN0
                from evenement e, section s, evenement_horaire eh
                where e.S_ID = s.S_ID
                and e.E_CODE = eh.E_CODE
                and e.E_CODE=".$EC;
            $resultR=mysqli_query($dbc,$queryR);
            $EH_DATE_DEBUT0 = Array();
            $EH_DATE_DEBUT0 = Array();
            $EH_DEBUT0 = Array();
            $EH_FIN0 = Array();
            $horaire_renfort = Array();
            
            while ( $rowR=@mysqli_fetch_array($resultR)) {
                $n=$rowR["EH_ID"];
                $EH_DATE_DEBUT0[$n]=$rowR["EH_DATE_DEBUT0"];
                $EH_DATE_FIN0[$n]=$rowR["EH_DATE_FIN0"];
                $EH_DEBUT0[$n]=$rowR["EH_DEBUT0"];
                $EH_FIN0[$n]=$rowR["EH_FIN0"];
                $CE_CANCELED=$rowR["CE_CANCELED"];
                $CE_CLOSED=$rowR["CE_CLOSED"];
                $CS_CODE=$rowR["CS_CODE"];
                $CS_DESCRIPTION=$rowR["CS_DESCRIPTION"];
                if ( $CE_CANCELED == 1 ) {
                    $color="red";
                    $info="événement annulé";
                }
                elseif ( $CE_CLOSED == 1 ) {
                    $color="orange";
                    $info="événement clôturé";
                }
                else {
                    $color="green";
                    $info="événement ouvert";
                }
                if ( $EH_DATE_DEBUT0[$n] <> $EH_DATE_FIN0[$n] ) $dates_renfort=$EH_DATE_DEBUT0[$n] ." au ".$EH_DATE_FIN0[$n];
                else $dates_renfort=$EH_DATE_DEBUT0[$n];
                $horaire_renfort[$n]=$dates_renfort." - ".$EH_DEBUT0[$n]."-".$EH_FIN0[$n];
            }
            if ( $EC <> $evenement ) {
                echo "<tr CLASS='Menu' bgcolor=$mylightcolor height=25><td colspan=7>
                    <b><i><a href=evenement_display.php?evenement=$EC&from=inscription>
                    <i class='fa fa-plus-square lg' style='color:".$color."' title='$info' ></i>
                    ".ucfirst($renfort_label)." de ".$CS_CODE."</i></b></a>
                    </td></tr>";
            }
            $prevEC = $EC;
        }
      
        if ( $VP_OPERATIONNEL == -1) $mytxtcolor='black';
        else if ( $VP_OPERATIONNEL == 1) $mytxtcolor=$red;
        else if ( my_date_diff(getnow(),$V_ASS_DATE) < 0 ) {
              $mytxtcolor=$red;
              $VP_LIBELLE = "assurance périmée";
        }
        else if ( my_date_diff(getnow(),$V_CT_DATE) < 0 ) {
              $mytxtcolor=$red;
              $VP_LIBELLE = "CT périmé";
        }
        else if ( $VP_OPERATIONNEL == 2) {
            $mytxtcolor=$orange;
        }
        else if (( my_date_diff(getnow(),$V_REV_DATE) < 0 ) and ( $VP_OPERATIONNEL <> 1)) {
            $mytxtcolor=$orange;
            $VP_LIBELLE = "révision à faire";
        }  
        else $mytxtcolor=$green;
      
        // récupérer horaires du véhicule
        $clock="";
        for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
            if ( isset ($horaire_renfort[$i])) {
              $query_horaires="select  EH_ID,
               DATE_FORMAT(EV_DATE_DEBUT,'%d-%m-%Y') EV_DATE_DEBUT, 
               DATE_FORMAT(EV_DATE_FIN,'%d-%m-%Y') EV_DATE_FIN,
               TIME_FORMAT(EV_DEBUT, '%k:%i') EV_DEBUT,  
               TIME_FORMAT(EV_FIN, '%k:%i') EV_FIN,
               DATE_FORMAT(EV_DATE_DEBUT,'%Y-%m-%d') EV_DATE_DEBUT1,
               DATE_FORMAT(EV_DATE_FIN,'%Y-%m-%d') EV_DATE_FIN1
               from evenement_vehicule
               where E_CODE=".$EC."
               and EH_ID = ".$i."
               and V_ID=".$V_ID;
               $resultH=mysqli_query($dbc,$query_horaires);
               $rowH=@mysqli_fetch_array($resultH);
               $EH_ID=$rowH["EH_ID"];
               if ( $EH_ID <> "" ) {
                  $EV_DATE_DEBUT=$rowH["EV_DATE_DEBUT"];    // DD-MM-YYYY
                  $EV_DATE_FIN=$rowH["EV_DATE_FIN"];
                  $EV_DATE_DEBUT1=$rowH["EV_DATE_DEBUT1"];  // YYYY-MM-DD
                  $EV_DATE_FIN1=$rowH["EV_DATE_FIN1"];
                  $EV_DEBUT=$rowH["EV_DEBUT"];
                  $EV_FIN=$rowH["EV_FIN"];
                  if ($nbsessions == 1 ) $t=" de l'événement";
                  else $t=" de la partie n°$EH_ID";
                  if ( $EV_DATE_DEBUT <> "" ) {
                      if ( $EV_DATE_DEBUT1 == $EH_DATE_DEBUT0[$i] and $EV_DATE_FIN1 == $EH_DATE_FIN0[$i] ) $horaire_v=$EV_DEBUT."-".$EV_FIN;
                      else if ( $EV_DATE_DEBUT == $EV_DATE_FIN ) $horaire_v= substr($EV_DATE_DEBUT,0,5).", ".$EV_DEBUT."-".$EV_FIN;
                      else $horaire_v= substr($EV_DATE_DEBUT,0,5)." au ".substr($EV_DATE_FIN,0,5).", ".$EV_DEBUT."-".$EV_FIN;
                      $clock .="<i class='fa fa-clock fa-lg' style='color:orange' title=\"horaires différents de ceux $t \n$horaire_v \ncliquer pour modifier\"></i>";
                  }
                  else $clock .="<i class='fa fa-clock fa-lg' style='color:green' title=\"horaires identiques à ceux $t \n".$horaire_renfort[$i]." \ncliquer pour modifier\"></i>";
               }
               else $clock .="<i class='fa fa-clock fa-lg' style='color:red'  title=\"pas engagé sur cette partie \ncliquer pour modifier\"></i>";
            }
        }
      
        if ( $gardeSP ) $myimg="";
        else {
            $nb = get_nb_engagements('V', $V_ID, $year1[1], $month1[1], $day1[1], $year2[$nummaxpartie], $month2[$nummaxpartie], $day2[$nummaxpartie] , $EC);
            if ( $nb > 1 ) 
                $myimg="<a href=evenement_vehicule.php?vehicule=".$V_ID."&dtdb=".$day1[1]."-".$month1[1]."-".$year1[1]."&dtfn=".$day2[$nummaxpartie]."-".$month2[$nummaxpartie]."-".$year2[$nummaxpartie]."&order=dtdb&filter=".$S_ID.">
                <i class='fa fa-exclamation noprint' style='color:red' title='attention ce véhicule est parallèlement engagé sur $nb autres événements' border=0></i></a>";
            else if ( $nb == 1 )
                $myimg="<a href=evenement_vehicule.php?vehicule=".$V_ID."&dtdb=".$day1[1]."-".$month1[1]."-".$year1[1]."&dtfn=".$day2[$nummaxpartie]."-".$month2[$nummaxpartie]."-".$year2[$nummaxpartie]."&order=dtdb&filter=".$S_ID.">
                <i class='fa fa-exclamation noprint' style='color:#ff8000' title='attention ce véhicule est parallèlement engagé sur 1 autre événement' ></i></a>";
            else $myimg="";
        }
        $altcolor=(($S_ID==$organisateur)?"":"<font color=purple>");
        echo "<tr><td style='max-width=50px' class=noprint>".$vimg."</td><td width=280><a href=upd_vehicule.php?from=evenement&vid=$V_ID 
        title=\"$S_CODE - $S_DESCRIPTION\">".$altcolor.$vehicule_string."</a>
        <span style='color:$mytxtcolor;' class='small2 noprint'>".$VP_LIBELLE."</span></td>";
        if ( $assoc ) {
            echo "<td style='min-width=105px;'>".$V_INDICATIF."</td>";
            echo "<td style='min-width=95px;'>".$V_IMMATRICULATION."</td>";
        }
        // affiche horaires
        if ( ! $print and $changeallowed) {
            if ($granted_event or ($granted_vehicule and $E_CLOSED == 0)) {
                $url="evenement_horaires.php?evenement=".$EC."&pid=0&vid=".$V_ID;
                echo "<td style='max-width=100px;' >";
                print write_modal($url,"Horaire_".$V_ID, $clock);
                echo"</td>";
            }
            else 
                echo "<td style='max-width=100px;'>".$clock."</td>";
        }
        echo "<td style='max-width=100px;'>".$myimg."</td>";
      
        // choix fonction
        if ( ! $print ) {
            // choix fonction
            $url="evenement_modal.php?&action=vfonction&evenement=".$evenement."&vid=".$V_ID;
            if ( $TFV_ID == "" or $TFV_ID == 0 ) $TFV_NAME="<div id='divfn".$V_ID."' style='color:grey; font-size:9px; font-style: italic;' title='sélectionner une fonction'>choisir fonction</div>";
            else $TFV_NAME="<div id='divfn".$V_ID."' style='font-size:9px; font-style: italic;' title='changer la fonction'>".$TFV_NAME."</div>";
            echo "<td width=120 align=center>";
            print write_modal( $url, "fonction_".$V_ID, $TFV_NAME);
            echo "</td>";
        }
      
        // choix équipe
        if ( $nbe > 0 ) {
            if (! $granted_event or ! $changeallowed or $print) {
                if ( $EE_ID == "" or $EE_ID == 0 ) $EE_NAME="";
                echo  "<td width=120><font size=1>".$EE_NAME."</font></td>";
            }
            else {
                // choix équipe
                $url="evenement_modal.php?action=vequipe&evenement=".$evenement."&vid=".$V_ID;
                if ( $EE_ID == "" or $EE_ID == 0 ) $EE_NAME="<div id='divpe".$V_ID."' style='color:grey; font-size:9px; font-style:italic;' title='Choisir une équipe' class='noprint'>Choisir équipe</div>";
                else $EE_NAME="<div id='divpe".$V_ID."' style='font-size:9px; font-style:italic;' title='Changer équipe'>".$EE_NAME."</div>";
                echo "<td align=center width=120>";
                print write_modal( $url, "equipe_".$V_ID, $EE_NAME);
                echo "</td>";
            }
        }
        else echo "<td></td>";
      
        if ( $granted_vehicule ) $readonly="";
        else $readonly="readonly";
      
        // kilométrage
        if ( $EV_KM == '' ) $showEV_KM = 'renseigner ';
        else $showEV_KM  = $EV_KM;
      
        $url="evenement_modal.php?action=km&evenement=".$evenement."&vid=".$V_ID;
        $showEV_KM = "<div id='vkmdiv".$V_ID."' style='font-size:9px; font-style:italic;' title='Renseigner le kilométrage'>".$showEV_KM." km</div>";
        if ( ! $print ) {
            echo "<td width=120>";
            if ( $readonly == '') print write_modal( $url, "km_".$V_ID, $showEV_KM);
            else echo "$showEV_KM km";
            echo "</td>";
        }
        
        if ( $nbsections == 0 ) echo "<td width=250><font size=1>$S_CODE</td>";
        else echo "<td width=250></td>";
        
        // supprimer  
        if ( $granted_vehicule and (! $print)) {
            echo "<td width=20>
            <a href=evenement_vehicule_add.php?evenement=".$evenement."&EC=".$EC."&action=remove&V_ID=".$V_ID."&from=evenement title='désengager ce véhicule' >
            <i class='fa fa-trash'></i></a>";
            echo "</td>";
        }
        else
              echo "<td width=20></td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</tr></td></table>";
}
else echo "Aucun véhicule engagé.<br>";

//=====================================================================
// ajouter un véhicule
//=====================================================================

    if (( $E_CANCELED == 0 ) and ! $print) {
        if ( $granted_vehicule ) {
            echo "<p>";
            $url="evenement_detail.php?evenement=".$evenement."&what=vehicule";
            print write_modal( $url, "V".$evenement, "<input type='button' class='btn btn-default'  value='engager des véhicules' title='engager des véhicules'/>");
        }
    }
}


//=====================================================================
// matériel demandés
//=====================================================================
if ( $tab == 4  and $materiel == 1 ) {
         
$query="select em.E_CODE as EC, m.MA_ID, tm.TM_CODE, m.TM_ID, vp.VP_LIBELLE, m.MA_MODELE, m.MA_NUMERO_SERIE,
        vp.VP_ID, vp.VP_OPERATIONNEL, s.S_DESCRIPTION, s.S_ID, s.S_CODE, em.EM_NB, m.MA_NB, m.MA_PARENT, tm.TM_LOT,
        cm.TM_USAGE, cm.PICTURE, cm.CM_DESCRIPTION,
        ee.EE_ID, ee.EE_NAME,
        DATE_FORMAT(m.MA_REV_DATE, '%d-%m-%Y') as MA_REV_DATE
        from evenement_materiel em left join evenement_equipe ee on ( ee.EE_ID = em.EE_ID and ee.E_CODE=".$evenement.") ,
        materiel m, vehicule_position vp, section s, 
        type_materiel tm, categorie_materiel cm, evenement e
        where m.MA_ID=em.MA_ID
        and e.E_CODE=em.E_CODE
        and cm.TM_USAGE=tm.TM_USAGE
        and tm.TM_ID = m.TM_ID
        and s.S_ID=m.S_ID
        and vp.VP_ID=m.VP_ID
        and em.E_CODE in (".$evts.")
        and MA_PARENT is null
        and e.E_CANCELED = 0
        order by cm.TM_USAGE, tm.TM_CODE, tm.TM_LOT desc, m.S_ID,  m.MA_MODELE";

$result=mysqli_query($dbc,$query);
$nbmat=mysqli_num_rows($result);
if ( $nbmat > 0 ) {
 
    if ( $print ) echo "<table cellspacing=0 border=0 width=800>";
    else echo "<table cellspacing=0 border=0 >";

    echo "<tr><td CLASS='MenuRub'>Matériel</td></tr>";
    echo "<tr><td CLASS='Menu' bgcolor=$mylightcolor>";
    echo "<table class='noBorder'>";
    $prevTM_USAGE='';
    $prevEC=$evenement;
    while (custom_fetch_array($result)) {
      
        if ( $VP_OPERATIONNEL == -1) $mytxtcolor='black';
        else if ( $VP_OPERATIONNEL == 1) $mytxtcolor=$red;
        else if ( $VP_OPERATIONNEL == 2) $mytxtcolor=$orange;
        else $mytxtcolor=$green;
      
        $myimg="";
        if ( $nbmat < 30 ) {
            $nb = get_nb_engagements('M', $MA_ID, $year1[1], $month1[1], $day1[1], $year2[$nummaxpartie], $month2[$nummaxpartie], $day2[$nummaxpartie], $EC) ;
            if ( $nb > 1 ) {
                $myimg="<a href=evenement_materiel.php?matos=".$MA_ID."&dtdb=".$day1[1]."-".$month1[1]."-".$year1[1]."&dtfn=".$day2[$nummaxpartie]."-".$month2[$nummaxpartie]."-".$year2[$nummaxpartie]."&order=dtdb&filter=".$S_ID.">
                <i class='fa fa-exclamation-triangle' style='color:#ff8000;' title='attention ce matériel est parallèlement engagé un ou des autres événements' ></i></a>";
            }
        }
      
        // affiche catégorie
        if ( $TM_USAGE <> $prevTM_USAGE) {
        echo "<tr><td colspan=5 ><i class='fa fa-".$PICTURE." fa-lg' style='color:purple;'></i><b> $CM_DESCRIPTION</b></td></tr>";
        }
        $prevTM_USAGE=$TM_USAGE;
      
        if ( $VP_OPERATIONNEL == -1) $mytxtcolor='black';
        else if ( $VP_OPERATIONNEL == 1) $mytxtcolor=$red;
        else if ( my_date_diff(getnow(),$MA_REV_DATE) < 0 ) {
              $mytxtcolor=$orange;
              $VP_LIBELLE = "date dépassée";
        }
        else if ( $VP_OPERATIONNEL == 2) {
            $mytxtcolor=$orange;
        }
        else $mytxtcolor=$green;
      
        $element="<font color=$mylightcolor>.....";
        if ( $TM_LOT == 1 ) $element .="</font><i class='fa fa-plus-square fa-lg' title=\"Ceci est un lot de matériel\"></i> ";
        elseif ( $MA_PARENT > 0  ) $element .="...</font><i class='fa fa-minus'  title=\"élément d'un lot de matériel\"></i> ";
        else $element .="</font><i class='fa fa-caret-right fa-lg' title=\"Ne fait pas partie d'un lot\"></i> ";
      
        $altcolor=(($S_ID==$organisateur)?"":"<font color=purple>");
        
        $title = $TM_CODE;
        if ( $MA_MODELE <> "" ) $title .= " - ".$MA_MODELE;
        if ( $MA_NUMERO_SERIE <> "" )  $title .= "- ".$MA_NUMERO_SERIE;
        echo "<tr valign=baseline><td width=350>".$element."<font size=1><a href=upd_materiel.php?from=evenement&mid=$MA_ID title=\"$S_CODE - $S_DESCRIPTION\">".$altcolor.$title."</a>
        <font color=$mytxtcolor>".$VP_LIBELLE."</font></td>";
        echo "<td width=20>".$myimg."</td>";
        
        // choix équipe
        if ( $nbe > 0 ) {
            if (! $granted_event or ! $changeallowed) {
                if ( $EE_ID == "" or $EE_ID == 0 ) $EE_NAME="";
                echo  "<td><font size=1>".$EE_NAME." </font></a></td>";
            }
            else {
                // choix équipe
                $url="evenement_modal.php?action=mequipe&evenement=".$evenement."&mid=".$MA_ID;
                if ( $EE_ID == "" or $EE_ID == 0 ) $EE_NAME="<div id='divpe".$MA_ID."' style='color:grey; font-size:9px; font-style:italic;' title='Choisir une équipe'>Choisir équipe</div>";
                else $EE_NAME="<div id='divpe".$MA_ID."' style='font-size:9px; font-style:italic;' title='Changer équipe'>".$EE_NAME."</div>";
                echo "<td width=120>";
                print write_modal( $url, "equipe_".$MA_ID, $EE_NAME);
                echo "</td>";
            }
        }
        else echo "<td></td>";
        
        if ( $granted_vehicule ) $readonly="";
        else $readonly="readonly";

        echo "<td width=100 align=center>";
        if ( $MA_NB > 1 ) {
            // choix nombre
            if ( $EM_NB == '' )  $EM_NB = 0;
            if ( $readonly == ''){
                $EM_NB="<div id='mnbdiv".$MA_ID."' style='font-size:11px;' title='Renseigner le nombre'>".$EM_NB." unités</div>";
                $url="evenement_modal.php?action=mnombre&evenement=".$evenement."&mid=".$MA_ID;
                print write_modal( $url, "nombre_".$MA_ID, $EM_NB);
            }
            else echo "$EM_NB pièces";
        }
        echo "</td>";
        if ( $nbsections == 0 ) echo "<td width=120><font size=1>$S_CODE</td>";
        else echo "<td ></td>";
 
        if ( $granted_vehicule  and (! $print) ) {
            echo "<td width=20>
            <a href=evenement_materiel_add.php?evenement=".$evenement."&EC=".$EC."&action=remove&MA_ID=".$MA_ID."&from=evenement title='désengager ce matériel'>
            <i class='fa fa-trash'></i></a></td>";
        }
        else
            echo "<td width=20></td>";
        echo "</tr>";
    }
    echo "</table></td></tr>";
    echo "</table>";
}
else echo "Aucun matériel engagé.<br>";

//=====================================================================
// ajouter du matériel
//=====================================================================

if (( $E_CANCELED == 0 ) and (! $print) and ( $granted_vehicule )) {
    echo "<p>";
     echo "<input type='button' class='btn btn-default'  name='ajouter' value='engager du matériel' title='engager du matériel'
           onclick=\"redirect('evenement_detail.php?evenement=".$evenement."&what=materiel');\">";
  }
}

//=====================================================================
// formation / diplômes
//=====================================================================
if (( $tab == 5 ) and (! $print) and ( $TE_CODE == 'FOR' ) and ( $PS_ID <> "") and ($TF_CODE <> "")){

if ( $granted_event and ( check_rights($id, 4, $organisateur) or $chef )) $disabledtf="";
else $disabledtf="disabled";

echo "<p class='noprint'>";
$query="select p.PS_DIPLOMA, p.PS_NUMERO, p.PS_EXPIRABLE, p.PS_NATIONAL, p.PS_SECOURISME, p.PS_PRINTABLE, p.PS_PRINT_IMAGE, p.PS_ID, p.TYPE, tf.TF_LIBELLE, e.F_COMMENT,
        p.PH_CODE, p.PH_LEVEL, ph.PH_UPDATE_LOWER_EXPIRY, ph.PH_UPDATE_MANDATORY
        from type_formation tf, poste p left join poste_hierarchie ph on ph.PH_CODE = p.PH_CODE,
        evenement e
        where e.PS_ID=p.PS_ID
        and e.TF_CODE=tf.TF_CODE
        and e.E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$_TYPE=$row["TYPE"];
$_PS_ID=$row["PS_ID"]; 
$_TF_LIBELLE=$row["TF_LIBELLE"];
$_PS_EXPIRABLE=$row["PS_EXPIRABLE"];
$_F_COMMENT=$row["F_COMMENT"];
$_PS_PRINTABLE=$row["PS_PRINTABLE"];
$_PS_PRINT_IMAGE=$row["PS_PRINT_IMAGE"];
$_PS_NATIONAL=$row["PS_NATIONAL"];
$_PS_SECOURISME=$row["PS_SECOURISME"];
$_PS_DIPLOMA=$row["PS_DIPLOMA"];
$_PS_NUMERO=$row["PS_NUMERO"];
$_PH_CODE=$row["PH_CODE"];
$_PH_LEVEL=$row["PH_LEVEL"];
$_PH_UPDATE_LOWER_EXPIRY=$row["PH_UPDATE_LOWER_EXPIRY"];
$_PH_UPDATE_MANDATORY=$row["PH_UPDATE_MANDATORY"];

$printdiplomes=false;
if ($_PS_PRINTABLE == 1 ){
    if ( $_PS_NATIONAL == 1 ) {
        if (check_rights($id, 48, "0" )) $printdiplomes=true;
    }
    else if (check_rights($id, 48, "$S_ID")) $printdiplomes=true;
}
    
if ($_TYPE <> "") {
    if ($_TF_LIBELLE <> "") $tt=$_TF_LIBELLE." pour ".$_TYPE;
     else $tt ="formation pour ".$_TYPE;
}
else $tt="formation";

if ( $print ) echo "<table cellspacing=0 border=0 width=800>";
else echo "<table cellpading=0 cellspacing=0 border=0 width=700>";


if (( $E_PARENT <> '' ) and ( $E_PARENT > 0) and ( $nbsections == 0)) {
    $queryR="select e.TE_CODE, e.E_LIBELLE, s.S_CODE, s.S_DESCRIPTION 
            from evenement e, section s 
            where s.S_ID = e.S_ID
            and e.E_CODE=".$E_PARENT;
    $resultR=mysqli_query($dbc,$queryR);
    $rowR=@mysqli_fetch_array($resultR);
    $ER_LIBELLE=stripslashes($rowR["E_LIBELLE"]);
    $SR_CODE=$rowR["S_CODE"];
    $SR_DESCRIPTION=$rowR["S_DESCRIPTION"];
   echo "<tr><td CLASS='MenuRub' colspan=2>informations</td></tr>";
   echo "<tr><td><b>Voir événement principal </b></td><td><a href=evenement_display.php?evenement=".$E_PARENT."&from=formation>
    ".$ER_LIBELLE." organisé par ".$SR_CODE." - ".$SR_DESCRIPTION."</a></td></tr>";
   echo "</table>";
}
else { 

echo "<tr><td CLASS='MenuRub' colspan=2>Résultats de ".$tt."</td></tr>";

if($E_DUREE_TOTALE!=''){     
echo "<tr bgcolor=$mylightcolor><td width=40%><b>Durée effective </b></td>
        <td width=60%> ".$E_DUREE_TOTALE." heures</td></tr>";
}
//instructeurs
$queryi="select distinct ep.E_CODE as EC, p.P_ID,p.P_NOM,".phone_display_mask('p.P_PHONE')." P_PHONE, p.P_PRENOM, p.P_GRADE, s.S_ID, s.S_CODE, p.P_STATUT, c.C_NAME,
        EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),p.P_BIRTHDATE))))+0 AS AGE, ep.TP_ID, tp.TP_NUM, tp.TP_LIBELLE
        from evenement_participation ep, pompier p, section s, type_participation tp, company c
        where ep.E_CODE in (".$evts.")
        and tp.TP_ID = ep.TP_ID
        and p.C_ID = c.C_ID
        and p.P_ID=ep.P_ID
        and p.P_SECTION=s.S_ID
        and ep.TP_ID > 0";
if ( $evenement_show_absents == 0 ) 
    $queryi .= " and ep.EP_ABSENT = 0 ";
$queryi .= " order by p.P_NOM, p.P_PRENOM";
$resulti=mysqli_query($dbc,$queryi);

//stagiaires
$query="select distinct ep.E_CODE as EC, p.P_ID,p.P_NOM,".phone_display_mask('p.P_PHONE')." P_PHONE, p.P_PRENOM, p.P_GRADE, s.S_ID, s.S_CODE, p.P_STATUT, c.C_NAME,
        EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),p.P_BIRTHDATE))))+0 AS AGE, ep.TP_ID
        from evenement_participation ep, pompier p, section s, company c
        where ep.E_CODE in (".$evts.")
        and p.C_ID = c.C_ID
        and p.P_ID=ep.P_ID
        and p.P_SECTION=s.S_ID
        and ep.TP_ID=0";
if ( $evenement_show_absents == 0 ) 
    $query .= " and ep.EP_ABSENT = 0 ";
$query .= " order by p.P_NOM, p.P_PRENOM";
$result=mysqli_query($dbc,$query);
$nbstagiaires=mysqli_num_rows($result);

if ( mysqli_num_rows($resulti) > 0 ) {
    while (custom_fetch_array($resulti)) {
        if ( $P_STATUT == 'EXT' ) {
            $colorbegin="<font color=green>";
            $colorend="</font>";
            $title="Personnel externe ".$C_NAME." (".$S_CODE.")";
        }
        else {
            $colorbegin="";
            $colorend="";
            $title=$S_CODE;
        }
      
        echo "<tr bgcolor=$mylightcolor><td width=40%>";
        echo "<b>".$TP_LIBELLE."</b>";
        echo "</td><td width=60%>";
        echo " <a href=upd_personnel.php?pompier=$P_ID title=\"$title\">".
        $colorbegin.strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM).$colorend."</a>";
        echo "</td></tr>";
    }
}
else if ( count($chefs) > 0 ) {
    echo "<tr bgcolor=$mylightcolor><td width=40%><b>Responsable </b></td>
        <td width=60%>";
    for ( $c = 0; $c < count($chefs); $c++ ) {
        echo " <a href=upd_personnel.php?pompier=".$chefs[$c]."> 
        ".my_ucfirst(get_prenom($chefs[$c]))." ".strtoupper(get_nom($chefs[$c]))."</a> ";
    }
    echo "</td></tr>";
}

$nbadmis=0;
$nbdiplomes=0;

if ( mysqli_num_rows($result) > 0 ) {
    echo "<tr bgcolor=$mylightcolor height=25><td colspan=2 valign=bottom><b>Réussite des stagiaires à la formation";
    if ( $TF_CODE == 'I' and $_PS_NUMERO == 1 ) echo " / numéro de diplôme";
    if ( $_PS_EXPIRABLE == 1 ) echo " / compétence valide jusqu'au";
    echo"</b></td></tr>";
    
    echo "<tr><td colspan=2 bgcolor=$mylightcolor>
          <form name='diplomes' action='evenement_diplome.php' method='POST'>";
    echo "<input type=hidden name='evenement' value='".$evenement."'>";
    while (custom_fetch_array($result)) {

        $query1="select count(1) as NB from evenement_participation
               where P_ID =".$P_ID."
               and EP_ABSENT = 0 
               and E_CODE in (".$evts.")";
        $result1=mysqli_query($dbc,$query1);
        $row1=@mysqli_fetch_array($result1);
        $n1=$row1["NB"];
      
        $query1="select count(1) as NB from evenement_participation 
               where P_ID=".$P_ID." 
               and E_CODE in (".$evts.")
               and EP_ABSENT = 0 
               and EP_DATE_DEBUT is not null";
        $result1=mysqli_query($dbc,$query1);
        $row1=@mysqli_fetch_array($result1);
        $n2=$row1["NB"];

      
        if ( check_rights($id, 10,"$S_ID")) $granted_update=true;
        else $granted_update=false;
      
        if (($granted_event or ($P_ID == $id and $E_CLOSED == 0) or ($granted_update and $E_CLOSED == 0)) and $changeallowed ) {
            $url="evenement_horaires.php?evenement=".$evenement."&pid=".$P_ID."&vid=0";
            if ($n1 < $nbsessions)
                $clock="<i class='fa fa-clock fa-lg' style='color:grey' title=\"Attention n'est pas présent à toutes les parties de la formation\" border=0></i>";
            else if ($n2 > 0)
                $clock="<i class='fa fa-clock fa-lg' style='color:orange'  title='Attention horaires différents de ceux de la formation' border=0></i>";
            else 
                $clock="<i class='fa fa-clock fa-lg' style='color:green'  title='Présence totale sur la formation' border=0></i>";
        
            $warn="<a href='".$url."'>".$clock."</a>";
        }
        else {
            if ($n1 < $nbsessions) $warn="<i class='fa fa-clock fa-lg' style='color:red'  title=\"Attention n'est pas présent à toutes les parties de la formation\" border=0></i>";
            else if ($n2 > 0) $warn="<i class='fa fa-clock fa-lg' style='color:orange'  title='Attention horaires différents de ceux de la formation' border=0></i>";
            else $warn="<i class='fa fa-clock fa-lg' style='color:green'  title='Présence totale sur la formation' border=0></i>";
        }
      
        $query1="select PF_ADMIS, PF_DIPLOME, date_format(PF_EXPIRATION,'%d-%m-%Y') PF_EXPIRATION  from personnel_formation pf
                 where pf.P_ID=".$P_ID." and pf.E_CODE=".$evenement;
        $result1=mysqli_query($dbc,$query1);
        $row1=@mysqli_fetch_array($result1);
        $PF_DIPLOME=$row1["PF_DIPLOME"];
        $PF_EXPIRATION=$row1["PF_EXPIRATION"];
        if ($row1["PF_ADMIS"] == 1) {
              $checked="checked"; 
              $nbadmis++;
        }
        else $checked="";
        if ( $PF_DIPLOME <> "" ) $nbdiplomes++;
        $cmt1=""; $cmt2="";
        if ( $AGE <> '' )
            if ($AGE < 18 ) 
                $cmt1="<font color=red>(-18)</font>";
          
        $for=strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM);
        echo "<input type=checkbox id='dipl_".$P_ID."' name='dipl_".$P_ID."' 
              title=\"cochez cette case si ".$for." a réussi la formation\"
              value='".$P_ID."' $checked $disabledtf";
            
        if ($_PS_EXPIRABLE == 1) 
            echo "onchange=\"change_date_exp('".$P_ID."');\"";
        echo ">";
              
        if ( $TF_CODE == 'I' and $_PS_NUMERO == 1) {
            echo "<input type=text id='num_".$P_ID."' name='num_".$P_ID."' size='10'
            style='width:100px;'
              title=\"saisissez le numéro de diplôme décerné à ".$for."\"
              value='".$PF_DIPLOME."' $disabledtf>";
        }
        if ($_PS_EXPIRABLE == 1) {
            echo " <input type='text' size='10' id='exp_".$P_ID."' name='exp_".$P_ID."' $disabledtf  
            value='".$PF_EXPIRATION."'
            placeholder='JJ-MM-AAAA'
            class='datepicker' data-provide='datepicker'
            style='width:100px;'
            title =\"saisissez ici la date de validité de la compétence pour ".$for." au format JJ-MM-AAAA\"
            onchange=\"change_date_exp('".$P_ID."');\">
            ";
        }
      
        if ( $P_STATUT == 'EXT' ) {
            $colorbegin="<font color=green>";
            $colorend="</font>";
            $title="Personnel externe ".$C_NAME." (".$S_CODE.")";
        }
        else {
            $colorbegin="";
            $colorend="";
            $title=$S_CODE;
        }
      
        $query1="select Q_VAL, DATE_FORMAT(Q_EXPIRATION, '%d-%m-%Y') as Q_EXPIRATION, 
                  DATEDIFF(Q_EXPIRATION,NOW()) as NB
                  from qualification
                where P_ID=".$P_ID." 
                and PS_ID=".$PS_ID;
        $result1=mysqli_query($dbc,$query1);
        $row1=@mysqli_fetch_array($result1);
        $Q_VAL=$row1["Q_VAL"];
        $Q_EXPIRATION=$row1["Q_EXPIRATION"];
        $NB=$row1["NB"];
        if ( $Q_VAL <> '' ) {
            if ( $Q_EXPIRATION <> '') {
                if ($NB <= 0) 
                    $cmt2="<font size=1 color=red>Compétence $_TYPE expirée depuis $Q_EXPIRATION</font>";
                else if ($NB < 61) 
                    $cmt2="<font size=1 color=orange>Compétence $_TYPE expire le $Q_EXPIRATION</font>";
                else if ( $Q_VAL == 2 ) 
                    $cmt2="<font size=1 color=blue>Compétence secondaire $_TYPE expire le $Q_EXPIRATION</font>"; 
                else if ( $Q_VAL == 1 ) 
                    $cmt2="<font size=1 color=green>Compétence principale $_TYPE expire le $Q_EXPIRATION</font>";
            }
            else if ( $Q_VAL == 2 ) 
                $cmt2="<font size=1 color=blue>Compétence secondaire $_TYPE valide</font>";
            else if ( $Q_VAL == 1 ) 
                $cmt2="<font size=1 color=green>Compétence principale $_TYPE valide</font>";
        }
        else {
            $cmt2="<font size=1 color=black>En formation pour obtenir la compétence $_TYPE</font>";
                // cas particulier: ne pas montrer PSE1 si PSE2 valide
            if ( $_TYPE == 'PSE1') {
                $query3="select count(1) as NB from qualification q, poste p
                where q.P_ID=".$P_ID." and p.PS_ID=q.PS_ID and p.TYPE='PSE2'";
                $result3=mysqli_query($dbc,$query3);
                $row3=@mysqli_fetch_array($result3);
                $NB=$row3["NB"];
                if ( $NB == 1 ) $cmt2="<font size=1 color=blue>Possède la compétence supérieure PSE2</font>";
            }
        }
                   
        echo " <a href=upd_personnel.php?pompier=$P_ID title=\"$title\">".$colorbegin.
        strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM).$colorend."</a> ".$cmt1." ".$cmt2." ".$warn;
      
        echo "<br>";
    }
    echo "<table class='noBorder'><tr><td><b>Commentaire</b></td>";
    echo "</tr><tr><td><input type=text size=80 name=comment value =\"".$_F_COMMENT."\" $disabledtf></td>";
    
    echo "</tr></table>";
    if ( $disabledtf == "" ) {
        if ( $_PH_UPDATE_LOWER_EXPIRY == 1 and $_PH_LEVEL > 0 and $_PS_EXPIRABLE == 1 ) {
            $query1="select p.PH_LEVEL, ph.PH_CODE, ph.PH_NAME, ph.PH_UPDATE_LOWER_EXPIRY, p.PS_ID, p.TYPE
                    from poste_hierarchie ph, poste p
                    where ph.PH_CODE = '".$_PH_CODE."' 
                    and p.PH_LEVEL <= ".$_PH_LEVEL."
                    and p.PS_ID <> ".$_PS_ID."
                    and p.PH_CODE = ph.PH_CODE
                    and p.PS_EXPIRABLE=1
                    order by p.PH_LEVEL asc";
            $result1=mysqli_query($dbc,$query1);
            $number=mysqli_num_rows($result1);

            if ( $number > 0 ) {
                $competencesH="";
                while ($row1=@mysqli_fetch_array($result1)) {
                    $hierarchieN = $row1["PH_NAME"];
                    $competencesH .= $row1["TYPE"].",";
                }
                if ( $_PH_UPDATE_MANDATORY == 1 ) 
                    echo "<table class='noBorder'><tr><td  class=small2>La date de validité des compétences inférieures de la hiérarchie ".$hierarchieN." sera automatiquement prolongée (".rtrim($competencesH,',').").</td></tr></table>";
                else
                    echo "<table class='noBorder'><tr><td><input type='checkbox' name='update_hierarchy' value='1' checked
                    title=\"Cocher pour reporter aussi l'expiration des compétences inférieures de la hiérarchie\"></td>
                    <td class=small2> Prolonger aussi la validité des compétences expirables de la hiérarchie ".$hierarchieN." (".rtrim($competencesH,',').")</td></tr></table>";
            }
        }
        echo "<input type='submit' class='btn btn-default' value='sauver' style='margin:5px'>";
    }
    echo "</form></td></tr>";
}

echo "</table>";

if ( $evenement_show_absents == 1 ) $checked='checked';
else $checked='';

echo "<div align=center class='hide_mobile noprint'><label for='evenement_show_absents' style='margin-right: 6px;'>Montrer les absents </label><input type=checkbox id='evenement_show_absents' name='evenement_show_absents' values='1' $checked 
            title='Montrer le personnel inscrit mais absent'
            onchange=\"show_absents('".$evenement."','5')\"
            style='margin-right: 20px;'>
            </div>";

if (( $printdiplomes or $granted_event ) and $nbstagiaires > 0 ) {
       
    $t = "Diplômes";
    if ( $TF_CODE == 'R' ) $t = "Attestation"; 
    else $t .= " ou attestations";
    
    echo "<p><div class='dropdown show' style='display: inline-block; white-space: nowrap; align:top'  >
                  <a class='btn btn-default dropdown-toggle' href='#' role='button' id='dropdownMenuLink2' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' 
                    title='imprimer ".$t.", choisissez un des différents modes proposés' style='margin-bottom:7px; margin:0px;' >
                    <i class='fa fa-print fa-lg'></i> $t
                  </a>
                  <div class='dropdown-menu' aria-labelledby='dropdownMenuLink2'>";
            
    if ( $granted_event ) {
        $link = "pdf_attestation_formation.php?evenement=".$evenement."&section=".$S_ID;
        echo "<a class='dropdown-item' href=$link target=_blank
        title=\"Imprimer des attestations sur papier vierge, possible pour tous les stagiaires ayant réussi ou échoué.\">
        Attestations de formation</a>";
    }
    
    if ( $printdiplomes and $nbadmis > 0 and 
        ( $TF_CODE == 'I' or  ( $TF_CODE == 'T' and ( $_PS_DIPLOMA == 0 or $_PS_NUMERO == 0)) )
       ) {
        if ( $_PS_PRINT_IMAGE == 1 or $printfulldiplome ) {
            echo "<a class='dropdown-item' href='pdf_diplome.php?evenement=".$evenement."&mode=3' target=_blank
                    title=\"Choisissez cette option si vous utilisez de feuilles de papier vierges, l'image du diplôme sera imprimée en même temps que les informations du stagiaire diplômé.\">
                    Diplôme $_TYPE sur papier blanc</a>";
        }
        else  if ( $_PS_PRINT_IMAGE == 0 ) {
            if ( $_PS_SECOURISME == 1 and $_PS_NUMERO == 1 ) {
                echo "<a class='dropdown-item' href='pdf_diplome.php?evenement=".$evenement."&mode=1' target=_blank
                    title=\"Choisissez cette option si vous disposez de feuilles de diplômes pre-imprimées, ayant chacune un numéro unique. 
Les n° de diplômes doivent être saisis ci-dessus avant de lancer l'impression. ATTENTION: Les feuilles doivent être introduites dans le bon ordre dans l'imprimante.\">
                    Diplôme $_TYPE sur papier pré-imprimé numéroté</a>";
            }
            if ( $_PS_NUMERO == 1 ) 
                echo "<a class='dropdown-item' href='pdf_diplome.php?evenement=".$evenement."&mode=2' target=_blank
                    title=\"Choisissez cette option si vous disposez de feuilles de diplômes pre-imprimées, sans numéro unique.
Les n° de diplômes doivent être saisis ci-dessus avant de lancer l'impression, ils seront imprimés.\">
                    Diplôme $_TYPE sur papier pré-imprimé non numéroté</a>";
            else 
                echo "<a class='dropdown-item' href='pdf_diplome.php?evenement=".$evenement."&mode=2' target=_blank
                    title=\"Choisissez cette option si vous disposez de feuilles de diplômes pre-imprimées non numérotées.\">
                    Diplôme $_TYPE sur papier pré-imprimé</a>";
            echo "<a class='dropdown-item' href='pdf_diplome.php?evenement=".$evenement."&mode=4' target=_blank
                    title=\"Choisissez cette option si vous utilisez de feuilles de papier vierges, un aperçu du diplôme officiel sera imprimé.
Les n° de diplômes doivent être saisis ci-dessus avant de lancer l'impression.\">
                    Aperçu avant impression $_TYPE</a>";
        }
    }
    echo "</div>
    </div><p>";
}
} // if E_PARENT > 0 

}

//=====================================================================
// tarif de la formation
//=====================================================================
if (( $tab == 6 ) and (! $print) and ( $E_TARIF > 0 ) and $granted_event){

if ( $changeallowed ) $disabled_tarif='';
else $disabled_tarif='disabled';

echo "<p><table cellpading=0 cellspacing=0 border=0>";
echo "<tr>
            <td width=200 class=TabHeader>Stagiaire</td>
            <td width=150 class=TabHeader>Entreprise</td>
            <td width=100 class=TabHeader align=left>Tarif formation</td>
            <td width=40 class=TabHeader align=left> Convoc. </td>
            <td width=40 class=TabHeader align=left> Facture </td>
      </tr>";
      
//stagiaires
$query="select distinct ep.E_CODE as EC, p.P_ID, p.P_NOM, ".phone_display_mask('p.P_PHONE')." P_PHONE, p.P_PRENOM, 
        p.P_GRADE, s.S_ID, s.S_CODE, p.P_STATUT, c.C_ID, c.C_NAME, ep.EP_TARIF, ep.EP_PAID, ep.MODE_PAIEMENT, tp.TP_DESCRIPTION
        from evenement_participation ep left join type_paiement tp on tp.TP_ID=ep.MODE_PAIEMENT, pompier p, section s, company c
        where ep.E_CODE in (".$evts.")
        and p.C_ID = c.C_ID
        and ep.EP_ABSENT = 0
        and p.P_ID=ep.P_ID
        and p.P_SECTION=s.S_ID
        and ep.TP_ID=0
        order by p.P_NOM, p.P_PRENOM";
$result=mysqli_query($dbc,$query);
$nbstagiaires=mysqli_num_rows($result);

echo "<input type=hidden name='evenement' value='".$evenement."'>";
$total_to_pay=0;
$total_paid=0;
while (custom_fetch_array($result)) {
    $MODE_PAIEMENT = intval($MODE_PAIEMENT);
    if ( $P_STATUT == 'EXT' ) {
        $colorbegin="<font color=green>";
        $colorend="</font>";
        $title="Personnel externe ".$C_NAME." (".$S_CODE.")";
    }
    else {
        $colorbegin="";
        $colorend="";
        $title=$S_CODE;
    }    
    
    if ( $EP_TARIF == '' ) $tarif = $E_TARIF;
    else $tarif = floatval($EP_TARIF);
    
    $total_to_pay = $tarif + $total_to_pay;
    if ( $EP_PAID == 1 ) {
        $checked='checked';
        $total_paid = $tarif + $total_paid;
    }
    else $checked='';
    
    $showtarif=$tarif;
    if ( $EP_PAID == 1 ) {
        $color='green';
        $title='paiement réalisé ';
        if ( $MODE_PAIEMENT > 0 ) {
            $title.=" - ".$TP_DESCRIPTION;
            $showtarif.=" ".$TP_DESCRIPTION;
        }
    }
    else {
        $color='red';
        $title='pas encore payé';
    }
    
    if ( $C_ID == 0 ) $company='';
    else $company="<a href=upd_company.php?C_ID=".$C_ID.">".$colorbegin.$C_NAME.$colorend."</a>";
    $for=strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM);
    echo "<tr bgcolor=$mylightcolor><td><a href=upd_personnel.php?pompier=".$P_ID." title=\"$title\">".$colorbegin.$for.$colorend."</a></td>";
    echo "<td >".$company."</td>";
    
    if ( $changeallowed ) $title .= ". Cliquer pour modifier";
    $displayed="<span class=badge style='background-color:$color;' title=\"".$title."\">".$showtarif."</span>";
    echo "<td >";
    
    $url="evenement_tarif_formation.php?evenement=".$evenement."&pid=".$P_ID;
    if ( $changeallowed ) print write_modal( $url, $P_ID, $displayed);
    else print $displayed;
    echo "</td>";
                 
    $myimg="<i class='far fa-file-pdf fa-lg'  style='color:red;' ></i>";
    $link="pdf_document.php?P_ID=".$P_ID."&section=".$S_ID."&evenement=".$evenement."&mode=18"; 
    echo "<td align=center><a href=".$link." target='_blank' title=\"Voir la convocation de ".$for."\">".$myimg."</a>";
    $link="pdf_document.php?P_ID=".$P_ID."&section=".$S_ID."&evenement=".$evenement."&mode=7"; 
    echo "<td align=center><a href=".$link." target='_blank' title=\"Voir la facture de ".$for."\">".$myimg."</a>";
    echo "</tr>";
}

if ( $total_to_pay > $total_paid ) $T="<font color=red><b>".round($total_paid,2)." ".$default_money_symbol."</b></font>";
else $T="<font color=green><b>".round($total_paid,2)." ".$default_money_symbol."</b></font>";

echo "<tr bgcolor=$mylightcolor>
  <td align=left>";
echo "  </td>
  <td align=right><b>Total</b></td>
  <td align=center><b>".$T." / ".round($total_to_pay,2)." ".$default_money_symbol."</b></td>
  <td></td>
  <td></td>";
echo "</tr></table>";
}

//=====================================================================
// tab 7 documents
//=====================================================================

if (( $tab == 7 ) and (! $print)){
    echo "<div id='documents'>";

    include_once ($basedir."/fonctions_documents.php");

    $table="";
    $tableHead = "<table cellspacing=0 border=0>";
    if ( $document_security == 1 ) $s="Secu.";
    else $s="";
    $tableHead .= "<tr class='TabHeader'>
          <td width=30 ></td>
            <td width=300 >
                 Documents attachés</td>
          <td width=50  align=center>
                 ".$s."</td>
            <td width=120 align=center>
                 Auteur</td>
            <td width=100 align=center>
                 Date</td>
            <td width=20 >Suppr.</td>
      </tr>";

    // DOCUMENTS ATTACHES
    $table .=show_attached_docs($evenement);
    if ( intval($E_PARENT) > 0 ) $table .=show_attached_docs($E_PARENT);

    // DOCUMENTS GENERES
    if ($granted_event) {
        if ( $FICHE_PRESENCE == 1  and $E_CLOSED == 1 ) {
            // fiche de présence spécifique SST
            if ( $type_doc == 'SST') $table .=show_auto_doc("Fiche de présence SST", "8", true);
            else if ( $type_doc == 'PRAP') $table .=show_auto_doc("Fiche de présence PRAP", "8", true);
            else $table .=show_auto_doc("Fiche de présence", "1", true);
            if ( $type_doc == 'SST') $table .=show_auto_doc("Attestations de présence SST", "10", true);
            else if ( $type_doc == 'PRAP') $table .=show_auto_doc("Attestations de présence PRAP", "10", true); 
        }
        if ( $PROCES_VERBAL == 1  and $E_CLOSED == 1 and  $PS_ID <> '' and in_array($TF_CODE,array('I','C','R','M'))) 
            $table .=show_auto_doc("Procès verbal", "5", true);
        if ( $EVAL_PAR_STAGIAIRES == 1  and $competences == 1) {
            $level=get_level("$S_ID");
            $sstspec=$basedir."/images/user-specific/documents/fiche_de_fin_de_stage_SST.pdf";
            if ( $type_doc == 'SST' and (file_exists($sstspec)) and $level < 3 ) $table .=show_auto_doc("Fiche d'évaluation de la formation SST", "9", false);
            else if ( $type_doc == 'PRAP' and (file_exists($sstspec)) and $level < 3 ) $table .=show_auto_doc("Fiche d'évaluation de la formation PRAP", "9", false);
            else $table .=show_auto_doc("Fiche d'évaluation de la formation", "3", false);
        }
        if ( $FACTURE_INDIV == 1  and $E_TARIF > 0 ) $table .=show_auto_doc("Factures individuelles", "7", false);
        if ( $ORDRE_MISSION == 1  and $E_CLOSED == 1 ) $table .=show_auto_doc("Ordre de mission", "4", false);
        if ( $CONVENTION == 1  and $E_PARENT == 0 ) {
            if ( $TE_CODE == 'FOR' ) $docnum=26;
            else $docnum=6;
            $table .=show_auto_doc("Convention sans signature", $docnum, $secured=false, $signed=false);
            if ( signature_president_disponible($S_ID))$table .= show_auto_doc("Convention signée par le président", $docnum, $secured=false, $signed=true);
        }
        if ( $EVAL_RISQUE == 1  and dim_ready($evenement)) {
            $table .=show_auto_doc("Grille d'évaluation des risques - complète", "-1", false);
            $table .=show_auto_doc("Grille d'évaluation des risques - page 1", "-2", false);
        }
        if ( $CONVOCATIONS == 1  and $E_CLOSED == 1 ) {
            $table .=show_auto_doc("Convocation collective", "15", false);
            $table .=show_auto_doc("Convocations individuelles", "18", false);
        }
        if ( ! $gardeSP and $TE_PERSONNEL == 1 and $TE_CODE <> 'MC' and $TE_CODE <> 'FOR' and $ORDRE_MISSION == 1 and ( $assoc or $army ) ) {
            if ( $E_ALLOW_REINFORCEMENT == 1 ) $title="Demande de ".$renfort_label."s";
            else $title="Demande de personnels et de moyens";
            $table .=show_auto_doc($title, "25", false);
        }
        if ( $NB6 > 0 ) {
            $table .=show_auto_doc("Produits consommables utilisés", "27", false);
        }
    }

    // DOCUMENTS HARDCODES
    $table .=show_specific_documents($type_doc);

    // AJOUT DOCUMENTS
    if ($documentation) {
        $table .= "<tr>
            <td colspan=6 bgcolor=$mylightcolor colspan=2 align=left style='padding:3px'><b>Attacher un nouveau fichier</b> ";
        $table .= "<input type='button' class='btn btn-default' id='userfile' name='userfile' value='Ajouter'
            onclick=\"openNewDocument('".$evenement."','".$S_ID."');\" ></td>";
        $table .= " </tr>";
    }   
    
    $tableEnd = "</table>";
    
    if ( $table <> "" ) 
        echo $tableHead.$table.$tableEnd;
    else
        echo "Aucun document";
    echo "</div>";
}

//=====================================================================
// tab 8 compte rendu
//=====================================================================
if ( $tab == 8  and ! $print and $TE_MAIN_COURANTE == 1) {
    echo "<div id='interventions'>";
    
    if ( intval($E_PARENT) > 0 ) {
        echo "<td align=center><input type='button' class='btn btn-default' id='victall' name='victall' value=\"Voir les informations sur l'événement principal\" 
                onclick=\"javascript:self.location.href='evenement_display.php?evenement=".$E_PARENT."&tab=8';\" 
                title=\"Voir les statistiques et le rapport sur l'événement principal\"></td>";
    }
    else {
        if ( $granted_event or $is_operateur_pc ) {
            echo "<table class='noBorder'><tr>";
            echo "<td align=center><a class='btn btn-default' onclick=\"refresh_interventions('".$evenement."');\" title='Rafraichir la page'><i class='fa fa-redo' ></i></a></td>";
            echo "<td align=center><a class='btn btn-default' href='evenement_rapport.php?evenement=".$evenement."' title='générer le rapport PDF' target='_blank'><i class='far fa-file-pdf' style='color:red;'></i></a></td>";
            
            if ( $TE_VICTIMES == 1 ) {
                echo "<td align=center><input type='button' class='btn btn-default' id='victall' name='victall' value='Voir victimes' 
                onclick=\"javascript:self.location.href='liste_victimes.php?evenement_victime=".$evenement."&type_victime=ALL&from=evenement';\" 
                title='Voir la liste des victimes enregistrées sur cet événement'></td>";
            }
            
            echo "<td align=center>
                    <div class='dropdown show' >
                      <a class='btn btn-default dropdown-toggle' href='#' role='button' id='dropdownMenuLink' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' 
                        title='créer des éléments ou données pour cet événement'>
                          <i class='fa fa-plus'></i> Ajouter
                      </a>
                      <div class='dropdown-menu' aria-labelledby='dropdownMenuLink'>
                        <a class='dropdown-item' href='#' onclick=\"nouvelle_intervention('".$evenement."','M');\">Message</a>";
            if ($TE_VICTIMES == 1 ) {
                echo "<a class='dropdown-item' href='#' onclick=\"nouvelle_intervention('".$evenement."','I');\">Intervention</a>";
                if ( ( $granted_event or $is_operateur_pc ) and ! $gardeSP ) 
                    echo "<a class='dropdown-item' href='#' onclick=\"new_cav('".$evenement."');\" >Centre d'accueil de victimes</a>";
                $nbcav=count_entities("centre_accueil_victime", "E_CODE=".$evenement." and CAV_OUVERT=1");
                if ( $nbcav > 0 ) 
                    echo "<a class='dropdown-item' href='victimes.php?from=list&action=insert&evenement_victime=".$evenement."&numcav=0' >Victime</a>";
            }
            echo "</div>
                </div>
                </td></tr>";
                
            if ( $autorefresh == 1 ) $checked='checked';
            else $checked='';
            echo "<tr><td colspan=4 align=center>  <input type='checkbox' id='autorefresh' name='autorefresh' value='1'
                title='cocher pour activer le rafraichissement automatique toutes les 20 secondes'
                onclick=\"autorefresh_interventions('".$evenement."');\" $checked>
                <label for='autorefresh' class=small>rafraîchissement auto.</label></td></tr>";
            echo "</table>";
        }
        echo "<table cellspacing=0 border=0>";
        
        // ==========================
        // stats
        // ==========================
        
        $queryN="select tb.TB_NUM, tb.TB_LIBELLE, be.BE_VALUE
            from type_bilan tb left join bilan_evenement be on (be.E_CODE=".$evenement." and be.TB_NUM = tb.TB_NUM)
            where tb.TE_CODE='".$TE_CODE."' 
            order by tb.TB_NUM";

        $resultN=mysqli_query($dbc,$queryN);
        if ( mysqli_num_rows($resultN) > 0 ) {
            if ( $E_PARTIES > 1 ) $cmt = " sur les $E_PARTIES parties";
            else $cmt ="";
            echo "<tr>
                <td colspan=9 class=TabHeader>Statistiques et bilan $cmt</td>
            </tr>";
        
            echo "<tr bgcolor=$mylightcolor height=30>
                    <td colspan=3 align=center><b>STATS</b><span class='success green12' style='display:none'><br>OK</span></td>
                    <td colspan=6 align=left>";
            $inscrits=array();
            $inscrits=explode(",",get_inscrits($evenement));
            $out = "";
            while ( $rowR=@mysqli_fetch_array($resultN)) {
                $TB_NUM=$rowR["TB_NUM"];
                $TB_LIBELLE=$rowR["TB_LIBELLE"];
                $BE_VALUE=$rowR["BE_VALUE"];
                if ( $granted_event
                    or $is_operateur_pc
                    or ( $TE_CODE == 'AH' and in_array($id, $inscrits) )
                )
                $out .= "<input type='text' style='width:35;' maxlength=4 size=2 id='nombre".$TB_NUM."' name='nombre".$TB_NUM."' value='$BE_VALUE' 
                        title=\"saisir ici le nombre de ".$TB_LIBELLE." réalisés sur cet événement\"
                        onchange='updatenumber(\"nombre".$TB_NUM."\",\"".$evenement."\",\"".$TB_NUM."\",this.value,\"$BE_VALUE\")'> ".$TB_LIBELLE."<br>";
                else {
                    $out .= $BE_VALUE." ".$TB_LIBELLE.", ";
                }
            }
            echo rtrim(rtrim($out),",");
            echo "</td>";
            
            echo "<tr bgcolor=$mylightcolor height=30>
            <td colspan=3 align=center><b>DETAIL</b></td>
            <td colspan=6 class=small width=700>".get_messages_stats($evenement).", ".get_inter_victimes_stats($evenement).". ".get_detailed_stats($evenement)."</td>
            </tr>"; 
        }     
        
        // ==========================
        // rapport sans interventions
        // ==========================

        if ( $TE_VICTIMES == 0 ) {
            
            $query="select e.EL_ID, e.E_CODE, e.TEL_CODE ,
            date_format(e.EL_DEBUT,'%d-%m-%Y') DATE_DEBUT, date_format(e.EL_DEBUT,'%H:%i') HEURE_DEBUT,
            e.EL_TITLE, e.EL_COMMENTAIRE, e.EL_IMPORTANT,
            tel.TEL_DESCRIPTION, TIMESTAMPDIFF(MINUTE,e.EL_DEBUT,e.EL_DATE_ADD) TIMEDIFF ,
            date_format(e.EL_DATE_ADD,'le %d-%m-%Y à %H:%i') DATE_ADD,
            date_format(e.EL_SLL,'%H:%i') HEURE_SLL,
            TIMESTAMPDIFF(MINUTE,e.EL_DATE_ADD,NOW()) NEW, date_format(e.EL_DEBUT,'%Y-%m-%d') EL_DEBUT,
            p.P_NOM, p.P_PRENOM
            from evenement_log e left join pompier p on p.P_ID = e.EL_AUTHOR, 
            type_evenement_log tel  
            where tel.TEL_CODE = e.TEL_CODE
            and e.E_CODE=".$evenement."
            order by EL_DEBUT desc, HEURE_DEBUT desc";
            $result=mysqli_query($dbc,$query);
            
            
            echo "<tr>
              <td width=10 class=TabHeader>Date</td>
              <td width=25 class=TabHeader></td>
              <td width=50 class=TabHeader align=left>Heure</td>
              <td width=100 class=TabHeader align=left>Rédacteur</td>
                <td width=600 class=TabHeader>Message</td>
            </tr>";
            
            if ( @mysqli_num_rows($result) == 0 ) {
                echo "<tr bgcolor=$mylightcolor>
                <td colspan=5 class=small>Aucun compte rendu n'a été saisi. Cliquez sur 'Message' pour ajouter un bloc.</td></tr>";
            }
            
            $prev_DATE_DEBUT="";
            while (custom_fetch_array($result) ) {
                $AUTHOR_NOM=strtoupper($P_NOM);
                $AUTHOR_PRENOM=my_ucfirst($P_PRENOM);

                if ( $DATE_DEBUT <> $prev_DATE_DEBUT ) {
                    echo "<tr bgcolor=$mylightcolor>
                       <td colspan=9 align=left><b>".$DATE_DEBUT."</b></td>
                       </tr>";
                    $prev_DATE_DEBUT=$DATE_DEBUT;
                }
            
                if ( abs($NEW) < 10 ) $new="<i class='fa fa-star' style='color:yellow;' title=\"Cette ligne a été ajoutée il y a moins de 10 minutes\" ></i>";
                else $new='';
                
                if ( $granted_event or $is_operateur_pc ) $title_msg = "<a href=intervention_edit.php?evenement=".$E_CODE."&numinter=".$EL_ID."&action=update&type=".$TEL_CODE." title='Cliquer pour éditer' >".$EL_TITLE."</a>";
                else $title_msg = $EL_TITLE;
                
                echo "<tr bgcolor=$mylightcolor>
                <td></td>
                <td align=left valign=top><i class='far fa-file-text fa-lg' title='".$TEL_DESCRIPTION."'></i></td>
                <td class=small2 valign=top><div align=left>".$HEURE_DEBUT." ".$new."</div></td>
                <td class=small2 valign=top><div align=left>".$AUTHOR_PRENOM." ".$AUTHOR_NOM."</div></td>
                <td align=left><b>".$title_msg."</b>
                    <br><span class=small2>".nl2br(wordwrap($EL_COMMENTAIRE,180,"\n"))."</span></td>
                </tr>";
            }
        }
        // ==========================
        // rapport avec interventions
        // ==========================
        else { 
        
            // centres accueil victimes
            $query="select c.CAV_ID, c.CAV_NAME, c.CAV_ADDRESS, c.CAV_COMMENTAIRE, c.CAV_RESPONSABLE, c.CAV_OUVERT, p.P_NOM, p.P_PRENOM
                    from centre_accueil_victime c left join pompier p on p.P_ID =  c.CAV_RESPONSABLE
                    where c.E_CODE=".$evenement." order by c.CAV_NAME";
            $result=mysqli_query($dbc,$query);

            if ( @mysqli_num_rows($result) > 0 ) {
                $query2="select count(1) from victime where CAV_ID in (select CAV_ID from centre_accueil_victime where E_CODE=".$evenement.")";
                $result2=mysqli_query($dbc,$query2);
                $row2=@mysqli_fetch_array($result2);
                $nb1=intval($row2[0]);
                
                echo "<tr class=TabHeader>
                        <td colspan=6 >Centres d'accueil des victimes</td>
                        <td colspan=1 align=left width=200>Responsable</td>
                        <td align=center><a href='liste_victimes.php?evenement_victime=".$evenement."&type_victime=cav&from=evenement'
                            class='Tabheader' style='color:yellow;' title=\"Voir les victimes de tous les centres d'accueil\" >Victimes: ".$nb1."</a></td>
                        <td align=center>Transports</a></td>
                      </tr>";
                while ($row=@mysqli_fetch_array($result) ) {
                    $CAV_ID=$row["CAV_ID"];
                    $P_NOM=strtoupper($row["P_NOM"]);
                    $P_PRENOM=my_ucfirst($row["P_PRENOM"]);
                    
                    $CAV_OUVERT=$row["CAV_OUVERT"];
                    $CAV_NAME = $row["CAV_NAME"]; 
                    if ( $CAV_OUVERT == 0 ) {
                            $CAV_CMT = "<span class=small title='le centre est fermé, on ne peut pas ajouter de victimes'>fermé</span>";
                            $color='red';
                            $title="centre accueil victimes fermé, on ne peut pas ajouter de victimes";
                    }
                    else {
                            $CAV_CMT = "";
                            $color='green';
                            $title="centre accueil victimes ouvert";
                    }
                    $CAV_RESPONSABLE=intval($row["CAV_RESPONSABLE"]);
                    if ( $CAV_RESPONSABLE <> 0 ) $resp= "<a href=upd_personnel.php?pompier=".$CAV_RESPONSABLE." title='Voir la fiche du responsable'>".$P_PRENOM." ".$P_NOM."</a>";
                    else $resp="";
                    
                    $query2="select count(1) from victime where CAV_ID =".$CAV_ID;
                    $result2=mysqli_query($dbc,$query2);
                    $row2=@mysqli_fetch_array($result2);
                    $nb=intval($row2[0]);
                    if ( $nb > 9 ) $style="style='background-color:red;'";
                    else if ( $nb > 0 ) $style="style='background-color:orange;'";
                    else $style="";
                    
                    $query2="select count(1) as NB from victime where CAV_ID =".$CAV_ID." and VI_TRANSPORT=1";
                    $result2=mysqli_query($dbc,$query2);
                    $row2=@mysqli_fetch_array($result2);
                    $nbt=intval($row2[0]);
                    if ($nbt == 0 ) $nbt="";

                    
                    echo "<tr bgcolor=$mylightcolor>
                            <td></td>
                            <td><i class='fa fa-h-square fa-lg' style='color:".$color.";' title=\"".$title."\"></i></td>
                            <td colspan=4 align=left><b><a href=cav_edit.php?numcav=".$CAV_ID." title='éditer ce centre'>".$CAV_NAME."</a> ".$CAV_CMT."</b></td>
                            <td colspan=1 align=left>".$resp."</td>
                            <td align=center><a href='liste_victimes.php?evenement_victime=".$evenement."&type_victime=".$CAV_ID."&from=evenement' title=\"Voir les victimes de ce centre d'accueil\">
                                <span class='badge' ".$style.">".$nb."</span></a></td>
                            <td align=center><span class='badge'>".$nbt."</span></td>
                          </tr>";
                }
            }
        
        
            $query="select e.EL_ID, e.E_CODE, e.TEL_CODE ,date_format(e.EL_DEBUT,'%d-%m-%Y') DATE_DEBUT, date_format(e.EL_DEBUT,'%H:%i') HEURE_DEBUT,
            date_format(e.EL_FIN,'%d-%m') DATE_FIN, date_format(e.EL_FIN,'%H:%i') HEURE_FIN, e.EL_IMPORTANT,
            e.EL_TITLE, e.EL_ADDRESS,e.EL_COMMENTAIRE,e.EL_RESPONSABLE, p.P_NOM, p.P_PRENOM,
            tel.TEL_DESCRIPTION, e.EL_ORIGINE, e.EL_DESTINATAIRE, TIMESTAMPDIFF(MINUTE,e.EL_DEBUT,e.EL_DATE_ADD) TIMEDIFF ,
            date_format(e.EL_DATE_ADD,'le %d-%m-%Y à %H:%i') DATE_ADD,
            date_format(e.EL_SLL,'%H:%i') HEURE_SLL,
            TIMESTAMPDIFF(MINUTE,e.EL_DATE_ADD,NOW()) NEW, date_format(e.EL_DEBUT,'%Y-%m-%d') EL_DEBUT,
            date_format(e.EL_DEBUT, '%Y-%m-%d') as M_DATE_DEBUT, date_format(e.EL_FIN, '%Y-%m-%d') as M_DATE_FIN,
            p2.P_NOM AUTHOR_LASTNAME, p2.P_PRENOM AUTHOR_FIRSTNAME
            from evenement_log e 
               left join pompier p on p.P_ID = e.EL_RESPONSABLE
               left join pompier p2 on p2.P_ID = e.EL_AUTHOR,
            type_evenement_log tel
            where tel.TEL_CODE = e.TEL_CODE
            and e.E_CODE=".$evenement."
            order by EL_DEBUT desc, HEURE_DEBUT desc";
            $result=mysqli_query($dbc,$query);

            if ( @mysqli_num_rows($result) > 0 ) {
                $query2="select count(1) from victime where EL_ID in (select EL_ID from evenement_log where E_CODE=".$evenement.")";
                $result2=mysqli_query($dbc,$query2);
                $row2=@mysqli_fetch_array($result2);
                $nb2=intval($row2[0]);
                
                echo "<tr>
                <td colspan=3 width=85 class=TabHeader>Interventions</td>
                <td width=40 class=TabHeader align=left title='Arrivée sur les lieux'>SLL</td>
                <td width=40 class=TabHeader align=left>Fin</td>
                <td width=400 class=TabHeader align=left>Origine => Destinataire: Titre</td>
                <td width=150 class=TabHeader align=left>Responsable</td>
                <td width=80 class=TabHeader><a href='liste_victimes.php?evenement_victime=".$evenement."&type_victime=intervention' 
                class='Tabheader' style='color:yellow;' title=\"Voir les victimes sur interventions\">Victimes: ".$nb2."</a></td>
                <td width=60 class=TabHeader>Transports</td>
                </tr>";
          
                $prev_DATE_DEBUT="";
                $prev_key="";
                while (custom_fetch_array($result) ) {
                    $P_NOM=strtoupper($P_NOM);
                    $P_PRENOM=my_ucfirst($P_PRENOM);
                    if ( $HEURE_FIN == '00:00' ) $HEURE_FIN ='';
                    if ( $EL_ORIGINE <> '' or $EL_DESTINATAIRE <> '' ) {
                        $fromto = "<font size=1>".$EL_ORIGINE;
                        if ( $EL_DESTINATAIRE <> '' ) $fromto .=" => ".$EL_DESTINATAIRE;
                        $fromto .=" : </font>";
                    }
                    else $fromto='';
            
                    $query2="select d.D_CODE, t.T_CODE, t.T_NAME, d.D_NAME
                        from victime v, destination d, transporteur t
                        where d.D_CODE=v.D_CODE
                        and v.VI_TRANSPORT=1
                        and t.T_CODE=v.T_CODE 
                        and v.EL_ID=".$EL_ID;
                    $result2=mysqli_query($dbc,$query2);
                    $nbt=@mysqli_num_rows($result2);
                    $cmt="";
                    while ($row2=@mysqli_fetch_array($result2)) {
                        $trans=$row2["T_NAME"];
                        $dest=$row2["D_NAME"];
                        if ( $cmt == "" ) $v="";
                        else $v=", ";
                        $cmt .= $v."Un transport par ".$trans." vers ".$dest;
                    }
                    if ($nbt > 0 ) {
                        if ( $granted_event or $is_operateur_pc )
                            $transports="<a href=intervention_edit.php?evenement=".$E_CODE."&numinter=".$EL_ID."&action=update&type=".$TEL_CODE." title=\"".$cmt."\">".$nbt."</a>";
                        else 
                            $transports="<span title=\"".$cmt."\">".$nbt."</span>";
                    }
                    else $transports="";
                    
                    $TEL_DESCRIPTION = "Enregistré par ".my_ucfirst($AUTHOR_FIRSTNAME)." ".strtoupper($AUTHOR_LASTNAME)." - ".$DATE_ADD." - ".$TEL_DESCRIPTION;
            
                    if ( $TEL_CODE == 'I' ) {
                        if ( $EL_IMPORTANT == 1 ) {
                            $img="class='fa fa-medkit' style='color:red'";
                            $TEL_DESCRIPTION .= " important, sera imprimé dans le bulletin de renseignements quotidiens";
                        }
                        else $img="class='fa fa-medkit' ";
                        $query2="select VI_ID, VI_NUMEROTATION, VI_SEXE, VI_AGE
                         from victime where EL_ID=".$EL_ID." order by VI_NUMEROTATION" ;
                        $result2=mysqli_query($dbc,$query2);
                        $nbv="";
                        while ($row2=@mysqli_fetch_array($result2) ) {
                            $VI_ID=$row2["VI_ID"];
                            $VI_NUMEROTATION=$row2["VI_NUMEROTATION"];
                            if (intval($VI_NUMEROTATION) == 0 ) $VI_NUMEROTATION='?';
                            $VI_SEXE=$row2["VI_SEXE"];
                            $age=$row2["VI_AGE"];
                            if ( $age <> '' ) $age .=" ans";
                            if ( $granted_event or $is_operateur_pc )
                                $nbv .= "<a href='victimes.php?victime=".$VI_ID."&from=evenement' title='".$VI_SEXE." ".$age." : voir la fiche de la victime ".$VI_NUMEROTATION."'>V".$VI_NUMEROTATION."</a> ";
                            else 
                                $nbv .= "<a href='pdf_document.php?numinter=".$EL_ID."&evenement=".$evenement."&section=".$S_ID."&mode=17&victime=".$VI_ID."' target=_blank title='".$VI_SEXE." ".$age.", voir la fiche victime'>V".$VI_NUMEROTATION."</a> ";
                        }
                    }

                    else if ( $TEL_CODE == 'M' ) {
                        if ( $EL_IMPORTANT == 1 ) {
                            $img="class='far fa-file-text' style='color:red'";
                            $TEL_DESCRIPTION .= " important, sera imprimé dans le bulletin de renseignements";
                        }
                        else $img="class='far fa-file-text'";
                        $nbv="";
                    }
                    if ( $E_PARTIES > 1 ) {
                        $key = array_search($M_DATE_DEBUT, $EH_DATE_DEBUT);
                        if ( $key == "" ) $key=array_search(end($EH_DATE_DEBUT), $EH_DATE_DEBUT);
                        if ( $key <> $prev_key ) {
                            $tmp=explode ( "-",$EH_DATE_DEBUT[$key]); $month1=$tmp[1]; $day1=$tmp[2]; $year1=$tmp[0];
                            $tmp=explode ( "-",$EH_DATE_FIN[$key]); $month2=$tmp[1]; $day2=$tmp[2]; $year2=$tmp[0];
                            $date1=date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1." ".$EH_DEBUT[$key];
                            $date2=date_fran($month2, $day2 ,$year2)." ".moislettres($month2)." ".$year2." ".$EH_FIN[$key];
                            echo "<tr bgcolor=$mylightcolor height=30>
                            <td colspan=9 align=left><b>Partie n°".$EH_ID[$key]."</b><span class=small> - du ".$date1." au ".$date2."</span></td>
                            </tr>";
                            $prev_key=$key;
                        }
                    }
                    else if ( $DATE_DEBUT <> $prev_DATE_DEBUT ) {
                        $tmp=explode ( "-",$DATE_DEBUT); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
                        $date1=date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1;
                        echo "<tr bgcolor=$mylightcolor>
                            <td colspan=9 align=left><b>".$date1."</b></td>
                            </tr>";
                        $prev_DATE_DEBUT=$DATE_DEBUT;
                    }
            
                    $td=abs($TIMEDIFF);
                    if ( ($td > 10 and $TEL_CODE == 'M') or ($td > 120 and $TEL_CODE == 'I')) 
                        $warn=" <i class='fa fa-exclamation' style='color:orange' title=\"Attention cette ligne n'a pas été enregistrée en direct, mais ".$DATE_ADD."\" ></i>";
                    else $warn='';
            
                    if ( abs($NEW) < 10 ) $new="<i class='fa fa-star' style='color:orange' title=\"Cette ligne a été ajoutée il y a moins de 10 minutes\" ></i>";
                    else $new='';
            
                    echo "<tr bgcolor=$mylightcolor>
                    <td></td>
                    <td align=left><i ".$img." title='".$TEL_DESCRIPTION."'></i></td>
                    <td class=small2><div align=left>".$HEURE_DEBUT." ".$new."</div></td>
                    <td class=small2><div align=left>".$HEURE_SLL."</div></td>
                    <td class=small2><div align=left>".$HEURE_FIN."</div></td>
                    <td align=left>".$fromto;
                    if ( $granted_event or $is_operateur_pc )
                        echo "<a href=intervention_edit.php?evenement=".$E_CODE."&numinter=".$EL_ID."&action=update&type=".$TEL_CODE." title='Cliquer pour éditer' >".$EL_TITLE." ".$warn."</a>";
                    else
                        echo  "<a href='pdf_document.php?numinter=".$EL_ID."&evenement=".$evenement."&section=".$S_ID."&mode=16' target=_blank title='voir la fiche intervention'>".$EL_TITLE."</a> ".$warn;
                    echo "</td><td align=left><a href=upd_personnel.php?pompier=".$EL_RESPONSABLE." title='Voir la fiche'>".$P_PRENOM." ".$P_NOM."</a></td>
                    <td align=center>".$nbv."</td>
                    <td align=center>".$transports."</td>
                    </tr>";
            
                }
            }
        }
        echo "</table><p>";
    }
    echo "</div>";
 
}


//=====================================================================
// produits consommés
//=====================================================================
if ( $tab == 9  and $consommables == 1 ) {
 
$query="select ec.E_CODE, ec.EC_ID, ec.C_ID, ec.EC_NOMBRE, ec.EC_DATE_CONSO,
        c.S_ID, tc.TC_ID, c.C_DESCRIPTION, c.C_NOMBRE, DATE_FORMAT(c.C_DATE_ACHAT, '%d-%m-%Y') as C_DATE_ACHAT, 
        DATE_FORMAT(c.C_DATE_PEREMPTION, '%d-%m-%Y') as C_DATE_PEREMPTION,
        tc.TC_DESCRIPTION, tc.TC_CONDITIONNEMENT, tc.TC_UNITE_MESURE, tc.TC_QUANTITE_PAR_UNITE,
        tum.TUM_CODE, tum.TUM_DESCRIPTION,tco.TCO_DESCRIPTION,tco.TCO_CODE,cc.CC_NAME, cc.CC_CODE, cc.CC_IMAGE, cc.CC_DESCRIPTION
        from evenement_consommable ec left join consommable c on c.C_ID = ec.C_ID,
        categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum, type_consommable tc
        where ec.TC_ID = tc.TC_ID
        and tc.CC_CODE = cc.CC_CODE
        and tc.TC_CONDITIONNEMENT = tco.TCO_CODE
        and tc.TC_UNITE_MESURE = tum.TUM_CODE
        and ec.E_CODE in (".$evts.")
        order by cc.CC_NAME, tc.TC_DESCRIPTION";
        
$result=mysqli_query($dbc,$query);
$nbmat=mysqli_num_rows($result);
if ( $nbmat > 0 ) {
 
    echo "<table cellspacing=0 border=0 >";

    echo "<tr><td CLASS='MenuRub'>Produits consommés sur cet événement</td></tr>";
    echo "<tr><td CLASS='Menu' bgcolor=$mylightcolor>";
    echo "<table class='noBorder'>";
    $prevCC_NAME='';
    $prevEC=$evenement;
    while (custom_fetch_array($result)) {
        $TC_DESCRIPTION=ucfirst($TC_DESCRIPTION);
        if ( $TCO_CODE == 'PE' ) $label =  $TC_DESCRIPTION." (".$TUM_DESCRIPTION."s) ".$C_DESCRIPTION;
        else if ( $TUM_CODE <> 'un' or $TC_QUANTITE_PAR_UNITE <> 1 ) $label = $TC_DESCRIPTION." (".$TCO_DESCRIPTION." ".$TC_QUANTITE_PAR_UNITE." ".$TUM_DESCRIPTION.") ".$C_DESCRIPTION;
        else $label = $TC_DESCRIPTION." ".$C_DESCRIPTION;
        
        if ( $C_ID > 0 ) {
            $query2="select s.S_ID, s.S_CODE, s.S_DESCRIPTION from section s, consommable c
                    where s.S_ID=c.S_ID and c.C_ID=".$C_ID;
            $result2=mysqli_query($dbc,$query2);
            custom_fetch_array($result2);
        }
        else {
            $S_ID=$organisateur;
            $S_CODE="";
        }

        // affiche catégorie
        if ( $CC_NAME <> $prevCC_NAME) {
            echo "<tr><td width=10><i class='fa fa-".$CC_IMAGE." fa-lg' style='color:saddlebrown;' title=\"".$CC_DESCRIPTION."\"></i><td colspan=4><b> $CC_NAME</b></td></tr>";
        }
        $prevCC_NAME=$CC_NAME;
        $altcolor=(($S_ID==$organisateur)?"":"<font color=purple>");
        echo "<tr valign=baseline><td align=right width=30><i class='fa fa-caret-right fa-lg'></i> </td><td style='font-size:11px;' width = 300>";
        if ( $C_ID > 0 ) 
            echo "<a href=upd_consommable.php?from=evenement&cid=$C_ID title=\"$S_CODE - $S_DESCRIPTION\"> ".$altcolor.$label."</a>    ";
        else
            echo "<span title=\"Origine du stock non précisée\">".$label."</span>";
        echo "</td>";
        if ( $granted_consommables ) $readonly="";
        else $readonly="readonly";
        echo "<td width=80 align=center>";
        
        // nombre d'uités consommées
        if ( $EC_NOMBRE == '' )  $EC_NOMBRE = 0;
        $url="evenement_modal.php?action=cnombre&evenement=".$evenement."&cid=".$EC_ID;
        $EC_NOMBRE = "<div id='cnbdiv".$EC_ID."' style='font-size:11px;' title='Renseigner le nombre'>".intval($EC_NOMBRE)." unités</div>";
        if ( ! $print ) {
            echo "<td width=120>";
            if ( $readonly == '') print write_modal( $url, "nombre_".$EC_ID, $EC_NOMBRE);
            else echo "$EC_NOMBRE unités";
            echo "</td>";
        }
        
        // section
        if ( $nbsections == 0 ) echo "<td ><font size=1>$S_CODE</td>";
        else echo "<td ></td>";
        
        if ( $granted_consommables  and (! $print) ) {
            echo "<td width=30 align=center>
            <a href=evenement_consommable_add.php?evenement=".$evenement."&action=remove&C_ID=".$C_ID."&EC_ID=".$EC_ID." title='supprimer cette ligne'>
            <i class='fa fa-trash'></i></a>";
            echo "</td>";
        }
        else {
              echo "<td width=20></td>";
        }
        
        echo "</tr>";
    }
    echo "</table></td></tr>";
    echo "</table>";
}
else echo "Aucun produit consommé.<br>";

//=====================================================================
// ajouter un produit consommé
//=====================================================================

if ( $E_CANCELED == 0 and ! $print and  $granted_consommables ) {
        echo "<p>";
        echo "<input type='button' class='btn btn-default' name='ajouter' value='ajout produit' title='enregistrer des consommations de produits'
           onclick=\"redirect('evenement_detail.php?evenement=".$evenement."&what=consommables')\">"; 
    }   
}

//=====================================================================
// remplacements gardes SP
//=====================================================================
if ( $tab == 10  and $remplacements == 1 ) {
    print table_remplacements($evenement, $status='', $date1='', $date2='' , $section=0);
    echo " <input type='button' class='btn btn-default' value='Tous les remplacements' name='see_all' onclick=\"javascript:self.location.href='remplacements.php';\">";
}
//=====================================================================
// piquets SP
//=====================================================================
if ($tab == 11) {
    $body = ""; $list="";
    // put personnel in a array
    $personnel = array();
    $query="select ep.EH_ID, p.P_ID, p.P_PRENOM, p.P_NOM, s.S_CODE, p.P_GRADE, p.P_STATUT
    from pompier p, section s, evenement_participation ep 
    where ep.P_ID = p.P_ID
    and ep.E_CODE=".$evenement."
    and ep.EP_ABSENT = 0
    and s.S_ID = p.P_SECTION
    and p.P_OLD_MEMBER = 0
    order by ep.EH_ID,p.P_NOM,p.P_PRENOM";
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result)){
        $value = strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM);
        if ( $grades ) $value .= " (".$P_GRADE.")";
        $personnel[$EH_ID][$P_ID] = $value;
        $list .= $P_ID.",";
    }
    $list .= '0';
    
    // put competences in a array
    $comps = array();
    $query="select q.P_ID, q.PS_ID , q.Q_VAL from qualification q 
            where ( Q_EXPIRATION is null or DATEDIFF(Q_EXPIRATION, '".$EH_DATE_DEBUT[1]."') > 0 )
            and P_ID in (".$list.")";
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result)){
        $comps[$P_ID][$PS_ID] = $Q_VAL;
    }
    // echo "<pre>";
    // print_r($comps);
    // echo "</pre>";
    $showjour = false;
    $shownuit = false;
    if ( $nbsessions == 2 ) {
        $showjour = true;
        $shownuit = true;
    }
    else if ( $nbsessions == 1 ) {
        if ( intval(substr($EH_DEBUT[1],0,2)) > 16 ) $shownuit = true;
        else $showjour = true;
    }

    $body .= "<a href='javascript:window.print();'><i class='fa fa-print fa-2x noprint' title=\"imprimer\"></i></a>";
    $query="SELECT distinct ev.E_CODE, v.TV_CODE, ev.V_ID, tv.TV_ICON, v.V_INDICATIF
            from evenement_vehicule ev, vehicule v, type_vehicule tv
            WHERE E_CODE = ".$evenement." 
            AND v.TV_CODE = tv.TV_CODE
            AND ev.V_ID = v.V_ID
            order by v.TV_CODE, v.V_INDICATIF";
    $result=mysqli_query($dbc,$query);
    write_debugbox($query); 
    while (custom_fetch_array($result)){
        if ( $V_INDICATIF <> '' ) $vname = $V_INDICATIF ;
        else $vname = $TV_CODE;
        $body .= "<table class='noBorder'><tr><td width=150 align=right><img src='".$TV_ICON."' class='img-max-40'></td>
                  <td width=250 align=left><h3>".$vname."</h3></td>";
        $body .= display_postes($evenement,$V_ID, $showjour, $shownuit);
        $body.="</tr></table>";
    }
    $body.= "</td></tr></table>";
    print $body ;
}

if ( $print ) {
    echo "<p><div><span class=small >Imprimé par: ".my_ucfirst(get_prenom($id))." ".strtoupper(get_nom($id))."</span></div>";
    echo "<p><div class='noprint' ><input type='button' value='fermer cette page' onclick='fermerfenetre();' ></div> ";
}
echo "<p>";
writefoot();
?>


