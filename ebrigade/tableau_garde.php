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
check_all(61);
writehead();
$id=$_SESSION['id'];
get_session_parameters();
test_permission_level(61);

if ( $month > 12 ) {
    $month=date('n');
    $_SESSION['month'] = $month;
}

if ( $nbsections <> 0 ) $filter = 0;
if ( is_lowest_level($filter) and $pompiers ) $filter = get_section_parent($filter);
if (isset($_GET["equipe"])) $equipe=intval($_GET["equipe"]);
else if ( $nbsections <> 0 ) $equipe=1;
else {
    $query="select min(EQ_ID) from type_garde where S_ID=".$filter;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $equipe=intval($row["0"]);
}
if (isset($_GET["person"])) $person=$_GET["person"];
else $person=$id;

if ( $tableau_garde_display_mode == 'month' ) {
    $periodelettre=moislettres($month);
    //nb de jours du mois
    $lastday=nbjoursdumois($month, $year);
    $firstday=1;
}
else {
    $periodelettre="semaine du ".get_day_from_week($week,$year,0,'S');
    $nbjoursdelaperiode=7;
    $timestamp = mktime( 0, 0, 0, 1, 1,  $year ) + ( $week * 7 * 24 * 60 * 60 );
    $timestamp_for_monday = mktime( 0, 0, 0, 1, 1,  $year ) + ((7+1-(date( 'N', mktime( 0, 0, 0, 1, 1,  $year ) )))*86400) + ($week-2)*7*86400 + 1 ;
    // trouver le lundi (premier jour de la semaine
    $firstday = date( 'j', $timestamp_for_monday );
    $month = date( 'n', $timestamp_for_monday );
    $lastday = $firstday + 6;
    //echo $firstday." ".$month." ";
}

echo "<link rel='stylesheet' type='text/css' href='css/print.css' media='print' />";
forceReloadJS('js/tableau_garde.js');
echo "</head>";

//=====================================================================
// formulaire
//=====================================================================
$yearnext=date("Y") +1;
$yearcurrent=date("Y");
$yearprevious = date("Y") - 1;
$yearminus2 = date("Y") - 2;
$yearminus3 = date("Y") - 3;
$yearminus4 = date("Y") - 4;
$yearminus5 = date("Y") - 5;

echo "<form>";

$EQ_NOM='Garde';
$EQ_ICON='images/gardes/GAR.png';

$nbtypesgardes=count_entities("type_garde", "S_ID=".$filter);

if ( $nbtypesgardes == 0 ) {
    if ( $nbsections <> 0 ) {
        write_msgbox("ERREUR", $error_pic, "Pas de type de garde paramétrés.<p align=center><input type='button' class='btn btn-default' value='retour' onclick='javascript:history.back(1);'>",10,0);
        exit;
    }
}
else {
    $queryg="select EQ_ID, EQ_NOM, EQ_JOUR, EQ_NUIT, S_ID, EQ_ICON from type_garde where EQ_ID=".$equipe;
    $resultg=mysqli_query($dbc,$queryg);
    custom_fetch_array($resultg);
    $EQ_NOM=ucfirst($EQ_NOM);
    $EQ_ID=intval($EQ_ID);
} 

//=====================================================================
// le tableau est il terminé ? sinon seuls certains peuvent le voir
//=====================================================================
$query="select PGS_STATUS from planning_garde_status
        where PGS_YEAR=".$year." and EQ_ID=".$equipe."
        and PGS_MONTH=".$month;
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$PGS_STATUS=$row["PGS_STATUS"];
if ( $PGS_STATUS <> '') $created = true;
else $created=false;
if ( $PGS_STATUS == 'READY' ) $ready=true;
else $ready=false;

//=====================================================================
// affiche le tableau
//=====================================================================

echo "<body>";
echo "<div align=center class='table-responsive'>";

// entête
if ( $nbtypesgardes > 0 ) {
    if ( $nbsections == 0 ) $S_DESCRIPTION=get_section_name($S_ID)." - ";
    else $S_DESCRIPTION="";
    echo "<table class='noBorder'><tr>
        <td width=80><img src='".$EQ_ICON."' class='img-max-50' title=\"".$EQ_NOM."\" ></td>
        <td align=center><font size=3><b> Tableau de ".$EQ_NOM."<br>".$S_DESCRIPTION.$periodelettre." ".$year."</td>";
    if ($ready or check_rights($id, 6, "$filter")) {
        echo "  <td width=50 align=right> <a href='#'><i class='far fa-file-excel fa-2x noprint' style='color:green;' title='Exporter la liste dans un fichier Excel' 
            onclick=\"window.open('tableau_garde_xls.php?filter=$filter&year=$year&month=$month&week=$week&equipe=$equipe&tableau_garde_display_mode=$tableau_garde_display_mode');\" /></i></a></td>";
    }
    echo"<td width=35 align=right> <a href='javascript:window.print();'>
        <i class='fa fa-print fa-2x noprint' title=\"imprimer\"></i></a></td>
        </tr></table>";
}

echo "<p class='noprint'><table class='noBorder' >";

//filtre section
if ( $nbsections == 0 ) {
    echo "<tr class='noprint'><td colspan=12 align=center>".choice_section_order('tableau_garde.php')." ";
    echo " <select id='filter' name='filter' 
    onchange=\"changeCentre(document.getElementById('filter').value,'".$month."','".$year."','".$tableau_garde_display_mode."','".$week."');\">";
    if ( $pompiers ) $maxL = $nbmaxlevels -1;
    else $maxL = $nbmaxlevels;
    display_children2(-1, 0, $filter, $maxL, $sectionorder);
    echo "</select></td></tr>";
}

//choix type de garde
if ( $nbtypesgardes == 0 ) {
    echo  "<div id='msgInfo' class='alert alert-info' role='alert'><strong>Info </strong>Aucune garde paramétrée ici. Choisissez un autre niveau de l'organigramme.</div>";
    exit;
}
else {
    echo " <tr class='noprint'><td>type</td>
            <td><select id='equipe' name='equipe' 
                onchange=\"redirect(".$month.",".$year.",".$filter.",document.getElementById('equipe').value, '".$tableau_garde_display_mode."', '".$week."', '".$person."')\">";
    $query="select distinct EQ_ID _EQ_ID, EQ_NOM _EQ_NOM from type_garde where S_ID=".$filter;
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result)) {
        echo "<option value='".$_EQ_ID."'";
        if ($_EQ_ID == $equipe ) echo " selected ";
        echo ">".$_EQ_NOM."</option>\n";
    }
    echo "</select></td>";

    // année
    echo "<td>année</td>";
    echo "<td><select name='year'  id='year'
        onchange=\"redirect('".$month."',document.getElementById('year').value,'".$filter."','".$equipe."', '".$tableau_garde_display_mode."', '".$week."', '".$person."')\">";
    if ($year == $yearminus5) echo "<option value='$yearminus5' selected>".$yearminus5."</option>";
    else echo "<option value='$yearminus5' >".$yearminus5."</option>";
    if ($year == $yearminus4) echo "<option value='$yearminus4' selected>".$yearminus4."</option>";
    else echo "<option value='$yearminus4' >".$yearminus4."</option>";
    if ($year == $yearminus3) echo "<option value='$yearminus3' selected>".$yearminus3."</option>";
    else echo "<option value='$yearminus3' >".$yearminus3."</option>";
    if ($year == $yearminus2) echo "<option value='$yearminus2' selected>".$yearminus2."</option>";
    else echo "<option value='$yearminus2' >".$yearminus2."</option>";
    if ($year == $yearprevious) echo "<option value='$yearprevious' selected>".$yearprevious."</option>";
    else echo "<option value='$yearprevious' >".$yearprevious."</option>";
    if ($year == $yearcurrent) echo "<option value='$yearcurrent' selected>".$yearcurrent."</option>";
    else echo "<option value='$yearcurrent' >".$yearcurrent."</option>";
    if ($year == $yearnext)  echo "<option value='$yearnext' selected>".$yearnext."</option>";
    else echo "<option value='$yearnext' >".$yearnext."</option>";
    echo  "</select></td>";

    // mode semaine ou mois
    echo "<td>affichage</td><td><select name='tableau_garde_display_mode' id='tableau_garde_display_mode' title='Affichage par mois ou par semaine'
        onchange=\"redirect('".$month."','".$year."','".$filter."','".$equipe."',document.getElementById('tableau_garde_display_mode').value, '".$week."', '".$person."')\">";
    if ( $tableau_garde_display_mode == 'month' ) $selected='selected'; else $selected='';
    echo  "<option value='month' $selected >Par mois</option>\n";
    if ( $tableau_garde_display_mode == 'week' ) $selected='selected'; else $selected='';
    echo  "<option value='week' $selected >Par semaine</option>\n";
    echo  "</select></td>";

    // mois
    if ( $tableau_garde_display_mode == 'month' ) {
        echo "<td>mois</td>
        <td><select name='month' id='month'
        onchange=\"redirect(document.getElementById('month').value,'".$year."', '".$filter."','".$equipe."', 'month', '".$week."', '".$person."')\">";
        $m=1;
        while ($m <=12) {
            $monmois = $mois[$m - 1 ];
            if ( $m == $month ) echo  "<option value='$m' selected >".$monmois."</option>\n";
            else echo  "<option value= $m >".$monmois."</option>\n";
            $m=$m+1;
        }
        echo  "</select></td>";
    }
    // semaine
    if ( $tableau_garde_display_mode == 'week' ) {
        echo "<td>semaine</td>
        <td><select name='week' id='week' 
        onchange=\"redirect('".$month."','".$year."','".$filter."','".$equipe."','week', document.getElementById('week').value, '".$person."')\">";
        $w=1;
        
        $jd=gregoriantojd(1,1,$year);
        if ( jddayofweek($jd)  == 0  and $year % 4 > 0 ) $maxweek=52;
        else $maxweek=53;
        while ($w <= $maxweek) {
            if ( $w < 10 ) $W1='0'.$w;
                else $W1=$w;
                if ( $w == $week ) $selected ='selected';
                else $selected='';
                echo  "<option value='$w' $selected>Semaine ".$W1." - ".get_day_from_week($w,$year,0,'S')."</option>\n";
                $w=$w+1;
        }
        echo  "</select></td>";
    }
    // filtre personnes
    $query="select P_ID, P_PRENOM, P_NOM from pompier where P_OLD_MEMBER=0 and P_SECTION in (".get_family("$filter").") and P_STATUT <> 'EXT' order by P_NOM";
    $result=mysqli_query($dbc,$query);
    echo "          <td> Agent</td><td>
                 <select id='person' name='person' title='surligner en vert les gardes pour une personne'
                 onchange=\"redirect(".$month.",".$year.",'".$filter."','".$equipe."', '".$tableau_garde_display_mode."','".$week."', document.getElementById('person').value)\">
                 <option value='0'>Tout le monde</option>";
      
    while (custom_fetch_array($result)) {
        echo "<option value='".$P_ID."'";
        if ($P_ID == $person ) echo " selected ";
        echo ">".strtoupper($P_NOM)." ".ucfirst($P_PRENOM)."</option>\n";
    }
    echo "</select></td>";

    if ( $horaires_tableau_garde == 1 ) $checked='checked';
    else $checked='';
    echo "<td><label for='horaires'>Horaires</label> <input type='checkbox' id='horaires' $checked
        title='cocher pour voir les horaires de chaque personne sur le tableau'
        onclick=\"redirect(".$month.",".$year.",'".$filter."','".$equipe."', '".$tableau_garde_display_mode."','".$week."', '".$person."');\">
        </td>";
    echo "<tr></table><p class='noprint'>";
  
    if ( check_rights($id, 5, "$filter") and $tableau_garde_display_mode == 'month' ) {
        if ( $created and ! $ready ) {
            echo "<table class='noBorder noprint'><TR>
            <td width=30><i class='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i><td>
            <td >Le tableau n'est pas accessible par le personnel.
            <input type='button' value='Montrer'  class='btn btn-default noprint' name='montrer' title='cliquer pour rendre le tableau de garde visible par le personnel'
            onclick=\"bouton_redirect('tableau_garde_status.php?month=$month&year=$year&filter=$filter&equipe=$equipe&action=montrer','montrer', '".$EQ_NOM."')\"></td>
            </tr></table>";
        }
    }
}

if (! $created ) {
    if ( check_rights($id, 5, "$filter")) {
        if (  $tableau_garde_display_mode == 'month' )
            echo " <p><input type='button' value='Créer' name='create'  class='btn btn-default noprint'
                onclick=\"bouton_redirect('tableau_garde_create.php?month=$month&year=$year&equipe=$equipe&filter=$filter','create', '".$EQ_NOM."')\">";
        else
            echo "<i>Le tableau de garde du mois n'est pas encore créé. <br>Basculez en mode d'affichage 'par mois' pour pouvoir le créer.</i>";
    }
    else 
        write_msgbox("Attention",$warning_pic,"Le tableau de $EQ_NOM pour $periodelettre $year n'est pas encore créé. Vous devez attendre qu'il soit créé par le bureau opérations.",30,30);
    exit;
}
else if ( ! $ready and ! check_rights($id, 5, "$filter")) {
    if ( (  $nbsections == 0 and check_rights($id, 6 , $filter))
            or ( $nbsections <> 0 and check_rights($id, 6)) ) {
         echo "<table class='noBorder'><tr>
            <td width=30><i class='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i><td>
            <td >Le tableau n'est pas accessible par le personnel.</td>
            </tr></table>";
    }
    else {
        write_msgbox("Attention",$warning_pic,"Le tableau de $EQ_NOM pour $periodelettre $year n'est pas encore disponible.",30,30);
        exit;
    }
}

if ( $created and $tableau_garde_display_mode == 'month' and $EQ_ID > 0) {
    if ( check_rights($id, 5, "$filter")) {
        echo " <table class='noBorder'><tr height=30>";
        if ( $ready )
            echo "<td><input type='button' class='btn btn-default noprint' value='Masquer' name='masquer' title='masquer ce tableau de garde, le personnel ne pourra plus le voir'
            onclick=\"bouton_redirect('tableau_garde_status.php?month=$month&year=$year&filter=$filter&equipe=$equipe&action=masquer','masquer', '".$EQ_NOM."')\"></td>";
        echo "<td><input type='button'  class='btn btn-default noprint' value='Supprimer' name='delete' title='Supprimer ce tableau de garde'
            onclick=\"bouton_redirect('tableau_garde_status.php?month=$month&year=$year&filter=$filter&equipe=$equipe&action=delete','delete', '".$EQ_NOM."')\"></td>";
        
        
        $comps = show_competences($equipe, '1');
        if ( $comps <> "" ) {
            if ( $pompiers ) $txt = "personnel SPV";
            else $txt = "personnel";
            // ne pas afficher si des SPV sont déjà inscrits sur ce tableau
            echo "<td>
                <a class='btn btn-default noprint' onclick=\"bouton_redirect('tableau_garde_status.php?month=$month&year=$year&filter=$filter&equipe=$equipe&action=spv');\" 
                    title='Remplir les cases vides du tableau avec le $txt disponible qui a les compétences requises pour les postes de cette garde'><i class='fa fa-user-plus fa-lg'></i></a>
            </td>";
        }
        echo "</tr></table><br>";
    }
}

echo "</form>";
    
// ===============================================
// affichage du tableau
// ===============================================

if ( $created ) {
    $queryp="select max(e.E_NB), max(e.E_CODE) from evenement e, evenement_horaire eh
        where TE_CODE = 'GAR'
        and e.E_CODE = eh.E_CODE
        and eh.EH_ID = 1
        and eh.EH_DATE_DEBUT >= '".$year."-".$month."-01'
        and eh.EH_DATE_DEBUT <= '".$year."-".$month."-31'
        and e.E_EQUIPE=".$equipe;
        
    if ( $nbsections == 0 ) $queryp .= " and e.S_ID=".$filter;

    $resultp=mysqli_query($dbc,$queryp);
    $rowp=@mysqli_fetch_array($resultp);
    $nbcol=$rowp[0];
    
    if ( $nbcol > 0 ) {
        // ===============================================
        // header
        // ===============================================
    
        echo "<table cellspacing=0 cellpadding=0 >";
        echo "<tr class=TabHeader>";
        echo  "<td width=90 class=small2>Jour</td>";
        $regime = get_regime_travail($equipe);
        if ( $regime > 0 ) echo  "<td width=10 class='small2'>S.</td>";
        echo "<td width=22 class='noprint'></td>";
        for ( $i=1; $i <= $nbcol; $i++) {
            echo "<td bgcolor=$mydarkcolor width=100 class=small>poste n°$i</td>";
        }
        echo "</tr>";

        // ===============================================
        // 1 ligne par garde
        // ===============================================

        $day=$firstday;
        while ( $day <= $lastday ) {
            $data="";
            $_dt= mktime(0,0,0,$month,$day,$year);
            if ( dateCheckFree($_dt)) $daycolor=$mylightcolor;
            else $daycolor="#FFFFFF";

            $query="select e.E_CODE, e.E_ANOMALIE, e.S_ID, eh.SECTION_GARDE, s.S_CODE
            from evenement e, evenement_horaire eh left join section s on s.S_ID = eh.SECTION_GARDE
            where e.E_CODE = eh.E_CODE
            and e.TE_CODE='GAR'
            and e.E_EQUIPE=".$equipe."
            and eh.EH_DATE_DEBUT = '".date("Y-m-d",$_dt)."'";
            if ( $nbsections == 0 ) $query .= " and e.S_ID=".$filter;
            $query .= " order by eh.EH_ID";
            $result=mysqli_query($dbc,$query);
            
            custom_fetch_array($result);
            $E_CODE=intval($E_CODE);
            $E_ANOMALIE=intval($E_ANOMALIE);
            $SNUM = intval(preg_replace('/[^0-9.]+/', '', $S_CODE));
            $SECTION_JOUR=intval($SNUM);
            $SECTION_NUIT=$SECTION_JOUR;
            if ( custom_fetch_array($result) ) {
               $SNUM = intval(preg_replace('/[^0-9.]+/', '', $S_CODE));
               $SECTION_NUIT = intval($SNUM);
               if ( $SECTION_NUIT == 0 ) $SECTION_NUIT = $SECTION_JOUR;
            }
            if ( $E_ANOMALIE == 1 ) {
                $daycolor="#FF6699";
                $rowtitle="ATTENTION garde en anomalie, cliquer pour vérifier le personnel et décocher la case 'Garde en anomalie'";
            }
            else $rowtitle='garde du '.date_fran($month, $day, $year);
    
            // remplir un tableau avec le personnel inscrit: jour partie 1 / jour partie 2 / nuit
            $day1_id=array();
            $day2_id=array();
            $night_id=array();
            $nightly_remain=array();
            $noms=array();
        
            for ( $i=1; $i <= $nbcol; $i++ ) {
                $day1_id[$i] = 0;
                $day2_id[$i] = 0;
                $night_id[$i] = 0;
            }
        
            if ( $E_CODE > 0 ) {
                // Trouver le nb de participant par période
                $get_ev = get_inscrits_garde($E_CODE,1);
                if (empty($get_ev))
                    $jour=0;
                else
                    $jour=count(explode(",",$get_ev));

                $get_ev = get_inscrits_garde($E_CODE,2);
                if (empty($get_ev))
                    $nuit=0;
                else
                    $nuit=count(explode(",",$get_ev));
            
                $query="select p.P_ID, p.P_GRADE, g.G_DESCRIPTION, p.P_STATUT, upper(p.P_NOM) RAW_NAME, ep.EH_ID, ep.EP_ASTREINTE,
                eh.EH_DEBUT, eh.EH_FIN,
                case
                when ep.EP_DEBUT is null then eh.EH_DEBUT
                else ep.EP_DEBUT
                end
                as DEBUT,
                case
                when ep.EP_FIN is null then eh.EH_FIN
                else ep.EP_FIN
                end
                as FIN,
                tp.TP_ID,
                tp.TP_LIBELLE,
                case
                when tp.TP_NUM is null then 1000
                else tp.TP_NUM
                end
                as TP_NUM
                from evenement_participation ep left join type_participation tp on tp.TP_ID = ep.TP_ID, 
                pompier p, grade g, evenement_horaire eh
                where ep.E_CODE IN (".get_event_and_renforts($E_CODE).") 
                and ep.EP_ABSENT = 0
                and eh.E_CODE = ep.E_CODE
                and eh.EH_ID = ep.EH_ID
                and p.P_ID = ep.P_ID
                and g.G_GRADE = p.P_GRADE" ;
                $query.= " order by ep.EH_ID, DEBUT asc, TP_NUM asc, g.G_LEVEL desc, p.P_NOM";
                $result=mysqli_query($dbc,$query);
                
                $d=1;
                $n=1;
                while ( custom_fetch_array($result) ) {
                    $HORAIRE = get_horaire($P_ID, $E_CODE);
                    $HORAIRE = $HORAIRE[0];
                    if (!empty( $TP_LIBELLE)) $FONCTION_DISPLAY ="<br><span style='font-size:9px;color:grey'>".$TP_LIBELLE."</span>";
                    else $FONCTION_DISPLAY='';
                    if ( $horaires_tableau_garde == 1 ) 
                        $HORAIRE_DISPLAY = '</span><br><span style="font-size:10px">'.$HORAIRE.'</span>';
                    else 
                        $HORAIRE_DISPLAY = '';
                    $FORMATED_NAME = $RAW_NAME.$FONCTION_DISPLAY.$HORAIRE_DISPLAY;
                    if ( $EP_ASTREINTE == 1 ) {
                        $P_NOM_ASTREINTE = $RAW_NAME ." <i class='fa fa-exclamation-triangle' style='color:orange;' title='astreinte (garde non rémunérée) pour au moins une partie de la garde'></i>";
                        if (isset ($noms[$P_ID])) {
                            $noms[$P_ID] = str_replace ( $RAW_NAME, $P_NOM_ASTREINTE, $noms[$P_ID]);
                        }
                        else
                            $FORMATED_NAME = $P_NOM_ASTREINTE.$FONCTION_DISPLAY.$HORAIRE_DISPLAY;    
                    }
                    if ( $P_ID == $person ) $FORMATED_NAME = str_replace ( $RAW_NAME, "<span  STYLE='background-color:$green; color:yellow; font-weight: bold; font-size: 18px;'>".$RAW_NAME."</span>", $FORMATED_NAME);    
                    if ( ! isset ($noms[$P_ID])) {
                        $noms[$P_ID] = "<table class='noBorder'><tr><td>";
                        if ( $grades ) 
                            $noms[$P_ID] .= "<a href=upd_personnel.php?pompier=".$P_ID." title='voir la fiche personnel'>
                                            <img src=".$grades_imgdir."/".$P_GRADE .".png class='img-max-18 hide_mobile' title=\"".$G_DESCRIPTION."\"></a>";        
                        if ( $P_STATUT == 'SPP' ) $noms[$P_ID] .= " </td><td class=red12>".$FORMATED_NAME."</td></tr>";
                        else  $noms[$P_ID] .= " </td><td class=blue12>".$FORMATED_NAME."</td></tr>";
                        $noms[$P_ID] .= "</table>";
                    }
                    // positionner jour
                    if ( $EH_ID == 1 ) {
                        if (! in_array($P_ID, $day2_id) ) {
                            $day1_id[$d] = $P_ID;
                            if ( $FIN < $EH_FIN ) {
                                // ne fait pas garde complète, chercher remplaçant
                                $query2="select p.P_ID, p.P_GRADE, p.P_NOM, g.G_DESCRIPTION
                                    from pompier p, grade g,
                                    evenement_participation ep
                                    where ep.E_CODE=".$E_CODE." 
                                    and ep.EH_ID=1
                                    and ep.P_ID = p.P_ID
                                    and ep.EP_DEBUT='".$FIN."'
                                    and p.P_GRADE = g.G_GRADE
                                    order by g.G_LEVEL desc, p.P_NOM";
                                $result2=mysqli_query($dbc,$query2);
                                while ( $row2=@mysqli_fetch_array($result2) ) {
                                    $P_ID2 = $row2["P_ID"];
                                    if (! in_array($P_ID2, $day2_id) ) {
                                        $day2_id[$d] = $P_ID2;
                                        break;
                                    }
                                }
                            }
                        }
                        $d++;
                    }
                    // positionner nuit qui sont deja presents de jour, sinon placer dans array $nightly_remain
                    else {
                        $found=false;
                        for ( $k=1; $k <= $nbcol; $k++ ) {
                            if ( $day2_id[$k] == $P_ID ) {
                                if (! in_array($P_ID2, $night_id) ) {
                                    $night_id[$k] = $P_ID;
                                    $found=true;
                                    break;
                                }
                            }
                            else if ( $day1_id[$k] == $P_ID ) {
                                if (! in_array($P_ID, $night_id) ) {
                                    $night_id[$k] = $P_ID;
                                    $found=true;
                                    break;
                                }
                            }
                        }
                        if (! $found )  {
                            if ( ! in_array($P_ID, $day1_id) ) {
                                $nightly_remain[$n]=$P_ID;
                                $n++;
                            }
                        }
                    }
                }

                // placer ceux qui ne feraient que la nuit
                for ( $l=1; $l <= sizeof($nightly_remain); $l++ ) {
                    for ( $k=1; $k <= $nbcol; $k++ ) {
                        if ( $night_id[$k] == 0 ) {
                            if (! in_array($nightly_remain[$l], $night_id)) {
                                $night_id[$k] = $nightly_remain[$l];
                                break;
                            }
                        }
                    }
                }
            }
            
            $data .= "<tr bgcolor=$daycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$daycolor'\" height=24  title=\"".$rowtitle."\"
                onclick=\"this.bgColor='#33FF00'; displaymanager('evenement_display.php?evenement=".$E_CODE."&from=gardes');\">";
            $data .= "<td class=small2>".date_fran($month, $day, $year)."</td>";

            if ( $SECTION_JOUR <> 0 ) {
                $img="<small><i class='badge badge".$SECTION_JOUR."' >".$SECTION_JOUR."</i></small>";
                if ( $SECTION_NUIT <> 0 and $SECTION_NUIT <> $SECTION_JOUR) {
                    $img .="<br><small><i class='badge badge".$SECTION_NUIT."' >".$SECTION_NUIT."</i></small>";
                }
            }
            else $img='-';
            if ( $regime > 0 ) $data .= "<td align=center>".$img."</td>";
            if ( $EQ_JOUR == 1 and $EQ_NUIT == 1 ) $t="<i style='color:$mydarkcolor'>J-<b>".$jour."</b><br><style='color:$mydarkcolor>N-<b>".$nuit."</b>";
            else if ( $EQ_JOUR == 1 ) $t="J";
            else if ( $EQ_NUIT == 1 ) $t="N";
            else $t="";
            $data .= "<td class='small noprint' align=center >".$t."</td>";
            for ( $i=1; $i <= $nbcol; $i++) {
                if ( $day1_id[$i] > 0 ) {
                    $case = $noms[$day1_id[$i]];
                    if ( $day2_id[$i] > 0 ) 
                        $case .= $noms[$day2_id[$i]];
                }
                else $case=" - ";
                if ( $EQ_NUIT == 1 and $EQ_JOUR == 1) {
                    if ( $night_id[$i] == 0 ) {
                        if ( $day1_id[$i] > 0 ) $case .=" <span STYLE='background-color:$yellow; color:#00000; font-size: 10px;' > jour seulement</span>";
                        else $case .="<br> -";
                    }
                    else if ( $night_id[$i] <> $day2_id[$i] and $night_id[$i] <> $day1_id[$i]) $case .= $noms[$night_id[$i]];
                }
                $data .= "<td align=left nowrap='nowrap'>".$case."</td>";
            }
            $data .= "</tr>";
    
            if ( $day < $lastday ) {
                $nbcol2= $nbcol+4;
                $data .=  "<tr height=1px bgcolor=grey><td colspan=".$nbcol2." style='padding-top:1px;'></td></tr>";
            }
            echo $data;

            $day=$day +1; 
        } //end loop of days
    
        echo "</table>";
    }
    else if ( $EQ_ID > 0 ) 
        echo "tableau introuvable";
}

echo "</div>";
writefoot();
?>
