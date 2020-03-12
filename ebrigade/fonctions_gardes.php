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

//=====================================================================
// effectuer un remplacement sur tableau de garde
//=====================================================================
function replace_personnel($evenement,$eh_id,$replaced,$substitute){
    global $dbc;
    $E=intval($evenement); $H=intval($eh_id); $R=intval($replaced); $S=intval($substitute);
    $queryadd = ""; $cmt = "";
    if ( $H > 0 ) {
        $queryadd =" and EH_ID=".$H;
        if ( $H == 1 ) $cmt = 'le jour';
        else $cmt = 'la nuit';
    }
    
    // enregistrer l'absence
    $query="update evenement_participation set EP_ABSENT = 1 , EP_EXCUSE=1
            where E_CODE=".$E." and P_ID=".$R.$queryadd;
    $result=mysqli_query($dbc,$query);
    
    $query="update evenement_participation set EP_COMMENT = \"A été remplacé ".$cmt."\"
            where E_CODE=".$E." and P_ID=".$R;
    $result=mysqli_query($dbc,$query);
    
    // ajouter le remplaçant
    $query="insert into evenement_participation(E_CODE, EH_ID, P_ID, EP_COMMENT, EP_DATE, EP_DATE_DEBUT, EP_DATE_FIN, EP_DEBUT, EP_FIN, EP_BY, EP_DUREE)
    select ".$E.",EH_ID, ".$S.",\"Ajouté pour faire un remplacement ".$cmt.".\", NOW(), EP_DATE_DEBUT, EP_DATE_FIN, EP_DEBUT, EP_FIN,".$_SESSION['id'].", EP_DUREE
    from evenement_participation
    where E_CODE=".$E.$queryadd."
    and P_ID=".$R;
    $result=mysqli_query($dbc,$query);
    
    // changer piquet
    $query = "update evenement_piquets_feu set P_ID = ".$S." where P_ID = ".$R." and E_CODE = ".$E.$queryadd;
    $result=mysqli_query($dbc,$query);
}  

//=====================================================================
// notification remplacement
//=====================================================================
function replace_notify($evenement,$eh_id,$status,$replaced,$substitute) {
    // status = requested, accepted, rejected, approved, refused
    global $id,$jours,$dbc,$assoc,$army,$nbsections;
    $by_name = my_ucfirst(get_prenom($id))." ".strtoupper(get_nom($id));
    $replaced_name = rtrim(my_ucfirst(get_prenom($replaced))." ".strtoupper(get_nom($replaced)));
    if ( intval($substitute) > 0 ) 
        $substitute_name = my_ucfirst(get_prenom($substitute))." ".strtoupper(get_nom($substitute));
    else
        $substitute_name ="";
    
    $query="select TE_CODE from evenement where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $TE_CODE=$row[0];
    
    if ( $TE_CODE == 'GAR' ) {
        $te = 'garde';
    }
    else {
        $te = 'evenement';
    }
        
    $query="select DATE_FORMAT(EH_DATE_DEBUT, '%w'), DATE_FORMAT(EH_DATE_DEBUT, '%d-%m-%Y') from evenement_horaire where E_CODE=".$evenement;
    if ( $eh_id > 1 ) $query .= " and EH_ID = ".$eh_id;
    else $query .= " and EH_ID = 1";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $date=$jours[$row[0]]." ".$row[1];
    
    $query="select sum(EP_DUREE) from evenement_participation where E_CODE=".$evenement." and P_ID=".$replaced;
    if ( $eh_id > 0 ) $query .= " and EH_ID=".$eh_id;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    if ( intval($row[0]) == 12 ) {
        if ( $eh_id == 2 ) $period=', 12h nuit';
        else if ( $eh_id == 1 ) $period=', 12h jour';
        else $period=', 12h';
    }
    else if ( intval($row[0]) == 24 )
        $period = ", pour 24 heures";
    else
        $period="";
    
    if ( $status == 'requested' ) {
        $subject="Nouvelle demande de remplacement ".$te." du ".$date." pour ".$replaced_name;
        $message="Bonjour,\nUne demande de remplacement a été enregistrée pour ".$replaced_name.", sur ".$te." du ".$date.$period.".";
        if ( intval($substitute) > 0 ) $message .="\nLe remplaçant proposé est ".$substitute_name.".";
        $message .="\nCette demande a été enregistrée par ".$by_name.".\n";
    }
    else if ( $status == 'accepted' ) {
        $subject="Demande de remplacement ".$te." du ".$date." pour ".$replaced_name." acceptée par le remplaçant";
        $message="Bonjour,\nConcernant la demande de remplacement de ".$replaced_name.", sur ".$te." du ".$date.$period.".";
        if ( intval($substitute) > 0 ) $message .="\nLe remplaçant proposé ".$substitute_name." a accepté.";
    }
    else if ( $status == 'refused' ) {
        $subject="Demande de remplacement ".$te." du ".$date." pour ".$replaced_name." refusée par le remplaçant";
        $message="Bonjour,\nConcernant la demande de remplacement de ".$replaced_name.", sur ".$te." du ".$date.$period.".";
        if ( intval($substitute) > 0 ) $message .="\nLe remplaçant proposé ".$substitute_name." a refusé.";
    }
    else if ( $status == 'approved' ) {
        $subject="Demande de remplacement ".$te." du ".$date." pour ".$replaced_name." approuvée";
        $message="Bonjour,\nLe remplacement de ".$replaced_name.", sur ".$te." du ".$date.$period." est approuvé.";
        if ( intval($substitute) > 0 ) $message .="\nLe remplaçant est ".$substitute_name;
        $message .="\nEnregistré par ".$by_name.".\n";        
    }
    else if ( $status == 'rejected' ) {
        $subject="Demande de remplacement ".$te." du ".$date." pour ".$replaced_name." rejetée";
        $message="Bonjour,\nLe remplacement de ".$replaced_name.", sur ".$te." du ".$date.$period." est rejeté.";
        if ( intval($substitute) > 0 ) $message .="\nLe remplaçant est ".$substitute_name.".";
        $message .="\nEnregistrée par ".$by_name.".\n";        
    }
    $S_ID=get_section_organisatrice($evenement);
    if ( $assoc or $army ) $perm=21;
    else $perm=60;
    
    if ( $nbsections == 0 ) $level = 'local';
    else $level = 'tree';
    
    $destid = get_granted($perm, "$S_ID", $level, $avoidspam = 'yes');
    $destid .= ",".$id.",".$replaced.",".$substitute;
    $chefs=get_chefs_evenement($evenement);
    if ( count($chefs) > 0 )  $destid .= ",".implode(",",$chefs);
    //echo "<pre>".$subject."\n".$message."\ndest:".$destid."</pre>";
    $nb = mysendmail("$destid" , $id , "$subject" , "$message" );
}
  
//=====================================================================
// est ce qu'un pompier donné est absent un jour donné ?
//=====================================================================

function is_out($P_ID, $year, $month, $day) {
    global $dbc;
    // absence enregistrée ?
    $query="select count(1) as NB from indisponibilite where P_ID =".$P_ID."
                 and I_DEBUT <= '".$year."-".$month."-".$day."'
         and I_FIN >= '".$year."-".$month."-".$day."'
         and I_STATUS in ('ATT','VAL')";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row["NB"];
}

//=====================================================================
// remplacements
//=====================================================================

function table_remplacements($evenement, $status, $date1, $date2 , $replaced = 0, $substitute = 0, $section=0) {
    global $dbc, $mylightcolor, $id, $nbsections, $grades, $gardes, $assoc, $army, $grades_imgdir;
    $H ="";
    $query="select r.R_ID, r.EH_ID, 
    r.REPLACED, p1.P_NOM n1, p1.P_PRENOM p1, p1.P_GRADE g1,
    r.SUBSTITUTE,
    r.ACCEPT_BY, r.ACCEPTED, date_format(r.ACCEPT_DATE,'%d-%m-%Y %H:%i') ACCEPT_DATE, p2.P_NOM n2, p2.P_PRENOM p2, p2.P_GRADE g2,
    r.REQUEST_BY, date_format(r.REQUEST_DATE,'%d-%m-%Y %H:%i') REQUEST_DATE, p3.P_NOM n3, p3.P_PRENOM p3, p3.P_GRADE g3,
    r.APPROVED, date_format(r.APPROVED_DATE,'%d-%m-%Y %H:%i') APPROVED_DATE, r.APPROVED_BY, p4.P_NOM n4, p4.P_PRENOM p4, p4.P_GRADE g4,
    r.REJECTED, date_format(r.REJECT_DATE,'%d-%m-%Y %H:%i') REJECT_DATE, r.REJECT_BY, p5.P_NOM n5, p5.P_PRENOM p5, p5.P_GRADE g5,
    date_format(eh.EH_DATE_DEBUT,'%d-%m-%Y') 'date_garde', r.E_CODE , e.E_LIBELLE, e.TE_CODE, s.S_CODE
    from remplacement r left join pompier p1 on p1.P_ID=r.REPLACED
    left join pompier p2 on p2.P_ID = r.SUBSTITUTE
    left join pompier p3 on p3.P_ID = r.REQUEST_BY
    left join pompier p4 on p4.P_ID = r.APPROVED_BY
    left join pompier p5 on p5.P_ID = r.REJECT_BY,
    evenement e,
    evenement_horaire eh left join section s on s.S_ID = eh.SECTION_GARDE
    where e.E_CODE=eh.E_CODE 
    and e.E_CODE = r.E_CODE 
    and eh.E_CODE = r.E_CODE 
    and eh.EH_ID=1";
    if ( intval($evenement)  > 0 ) $query .=" and r.E_CODE=".$evenement;
    if ( $status == 'VAL' ) $query .=" and r.APPROVED=1";
    else if ( $status == 'REJ' ) $query .=" and r.REJECTED=1";
    else if ( $status == 'ACC' ) $query .=" and r.ACCEPTED=1 and r.APPROVED=0 and r.REJECTED=0";
    else if ( $status == 'DEM' ) $query .=" and r.ACCEPTED=0 and r.APPROVED=0 and r.REJECTED=0";
    else if ( $status == 'ATT' ) $query .=" and r.APPROVED=0 and r.REJECTED=0";
    if ( $date1 <> "" ) {
        $tmp=explode ( "-",$date1); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
        $query .="  and eh.EH_DATE_FIN   >= '$year1-$month1-$day1'";
    }
    if ( $date2 <> "" ) {
        $tmp=explode ( "-",$date2); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
        $query .=" and eh.EH_DATE_DEBUT <= '$year2-$month2-$day2' ";
    }
    if ( $substitute > 0 )  $query .= " and r.SUBSTITUTE = ".$substitute;
    if ( $replaced > 0 )  $query .= " and r.REPLACED = ".$replaced;
    if ( $nbsections == 0 and intval($section) > 0 )  $query .= " and p1.P_SECTION in (".get_family(intval($section)).")";
    $query .=" order by eh.EH_DATE_DEBUT";
    
    $result=mysqli_query($dbc,$query);
    write_debugbox($query);

    $nbR=intval(mysqli_num_rows($result));
    $H .= "<span class='badge'>".$nbR." </span> demande";
    if ( $nbR > 1 ) $H .= "s";
    $H .= " de remplacement trouvée";
    if ( $nbR > 1 ) $H .= "s";
    
    if ( $nbR > 0 ) {
        $H .= "<table cellspacing=0 border=0 >
            <tr CLASS='MenuRub'>";
        if ( $evenement == 0 ) {
            if ( $gardes ) $H .= "<td width=20></td>";
            $H .= "<td width=90>Date</td>";
            if ( $assoc or $army ) 
                $H .= "<td width=330>Evénement</td>";
        }
        $H .= "<td width=200>A remplacer</td>
                <td width=200>Remplaçant proposé</td>";
        if ( $gardes ) $H.= "<td width=100>Période</td>";
        $H.= "<td width=120>Date Demande</td>
                <td >Statut</td>
                <td width=120>Date statut</td>
                <td >Détail</td>
            </tr>";
        
        while ($row=@mysqli_fetch_array($result)) {
            $rid=$row["R_ID"];
            $evt=$row["E_CODE"];
            $date_garde = $row["date_garde"];
            $replaced = strtoupper($row["n1"])." ".my_ucfirst($row["p1"]);
            $grade_replaced = $row["g1"];
            if ( $grade_replaced <> "" )  $grade_replaced = "<img src=".$grades_imgdir."/".$grade_replaced.".png style='PADDING:1px;' class='img-max-20'>";
            $substitute = strtoupper($row["n2"])." ".my_ucfirst($row["p2"]);
            $grade_substitute = $row["g2"];
            if ( $grade_substitute <> "" ) $grade_substitute="<img src=".$grades_imgdir."/".$grade_substitute.".png style='PADDING:1px;' class='img-max-20'>";
            $date_request = $row["REQUEST_DATE"];
            $requested_by = strtoupper($row["n3"])." ".my_ucfirst($row["p3"]);
            $date_accept = $row["ACCEPT_DATE"];
            $accepted = $row["ACCEPTED"];
            $rejected = $row["REJECTED"];
            $approved = $row["APPROVED"];
            $date_approve = $row["APPROVED_DATE"];
            $date_reject = $row["REJECT_DATE"];
            $libelle = $row["E_LIBELLE"];
            $te_code = $row["TE_CODE"];
            $SECTION_JOUR = intval(preg_replace('/[^0-9.]+/', '', $row["S_CODE"]));
            if ( $SECTION_JOUR <> 0 )  $img="<small><i class='badge badge".$SECTION_JOUR."' title='section $SECTION_JOUR'>".$SECTION_JOUR."</i></small>";
            else $img="";
            $approved_by = strtoupper($row["n4"])." ".my_ucfirst($row["p4"]);
            if ( $row["EH_ID"] == 1 ) $periode = "Jour";
            else if ( $row["EH_ID"] == 2 ) $periode = "Nuit";
            else $periode = "24h";
            if ( $grades == 0 ) {
                $grade_replaced ="";
                $grade_substitute ='';
            }

            if ( $approved == 1 ) {
                $status='Approuvé';
                $color='green';
                $t='Approuvé par '.$approved_by;
                $status_date=$date_approve;
            }
            else if ( $rejected == 1 ) {
                $status='Rejeté';
                $color='red';
                $t='Demande de remplacement rejetée';
                $status_date=$date_reject;
            }
            else if ( $accepted == 1 ) {
                $status='Accepté par le rempaçant';
                $color='purple';
                $t="Accepté par le rempaçant, mais le remplacement n'est pas encore approuvé";
                $status_date=$date_accept;
            }
            else {
                $status='Demandé';
                $color='orange';
                $t='Le remplacement a été demandé';
                $status_date="";
            }
            $H .= "<tr bgcolor=$mylightcolor>";
            if ( $evenement == 0 ) {
                if ( $gardes ) $H .= "<td >".$img."</td>";
                $H .= "<td ><a href=evenement_display.php?tab=2&evenement=".$evt." title='voir événement'>".$date_garde."</a></td>";
                if ($assoc or $army )
                    $H .= "<td ><a href=evenement_display.php?tab=2&evenement=".$evt." title=\"voir cet événement ".$te_code."\" class='small2'>".$libelle."</a></td>";
            }
            $H .= "<td >".$grade_replaced." <a href='upd_personnel.php?pompier=".$row["REPLACED"]."'>".$replaced."</a></td>
            <td >".$grade_substitute." <a href='upd_personnel.php?pompier=".$row["SUBSTITUTE"]."'>".$substitute."</a></td>";
            if ( $gardes ) $H.= "<td ><b>".$periode."</b></td>";
            $H.= "<td class='small'>".$date_request."</td>
            <td ><span class='badge' style='background-color:".$color.";' title=\"".$t."\">".$status."</span></td>
            <td class='small'>".$status_date."</td>
            <td align=center><a href=\"remplacement_edit.php?evenement=".$evt."&rid=".$rid."\"><i class='fa fa-edit fa-lg' title='voir ou modifier cette demande'></i></a></td>
            </tr>";
        }
        $H .= "</table>";
    }
    $H .= "<p>";
    if ( $evenement > 0 ) {
        $label ='';
        $S_ID=get_section_organisatrice($evenement);
        if ( $nbsections > 0 and check_rights($id, 6)) $label='Ajouter';
        else if ( $gardes and check_rights($id, 6, $S_ID)) $label='Ajouter';
        else if ( ( $assoc or $army )  and check_rights($id, 15, $S_ID)) $label='Ajouter';
        else if ( is_inscrit($id,$evenement)) $label='Me faire remplacer';
        if ($label <> '' )
        $H .= "<input type='button' class='btn btn-default' value='".$label."' title='Ajouter une demande de remplacement' onclick='javascript:self.location.href=\"remplacement_edit.php?evenement=".$evenement."\";'>";
    }
    else {
        if ( $assoc or $army ) $t="l'événement concerné";
        else $t="la garde concernée";
        $H .= "<span class=small>Pour ajouter une demande de remplacement ouvrir $t, onglet remplacements</span><p>";
    }
    return $H;
}

//=====================================================================
// code evenement de la garde du jour ?
//=====================================================================
function get_garde_jour($section=0, $eqid=0, $date=0) {
    global $dbc,$nbsections,$filter;
    // section 0
    // eqid 0 => choix auto
    // date format YYYY-MM-DD ou 0 = date du jour
    $query = "select distinct e.E_CODE from evenement e, evenement_horaire eh
            where e.TE_CODE='GAR'
            and e.e_canceled=0
            and eh.E_CODE = e.E_CODE";
    if ( $date == 0 ) 
        $query .=  " and eh.EH_DATE_DEBUT='".date('Y-m-d')."'";
    else 
        $query .=  " and eh.EH_DATE_DEBUT='".$date."'";

    if ( $eqid == 0 ) {
        $query .=  " and e.E_EQUIPE=(select min(EQ_ID) from type_garde";
        if ( $nbsections == 0 ) $query .= " where S_ID = ".intval($filter);
        $query .= " )";
    }
    else 
        $query .=  " and e.E_EQUIPE=".$eqid;

    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    return intval($row[0]);
}

//=====================================================================
// quelle est la section de garde pour un jour donné ?
//=====================================================================

function get_section_pro_jour($eqid, $year, $month, $day, $period='J') {
    global $dbc;
    global $gardes, $debug;
    $sppsub=get_regime_travail($eqid);
    // $sppsub = 3 ou 4 ou 5 . Nombre de sections caserne organisation SPP
    if ( $gardes == 0 ) return 0;
    if ( $period == 'N' ) $field="ASSURE_PAR2";
    else $field="ASSURE_PAR1";
    $query="select type_garde.S_ID, ".$field." as ASSURE_PAR, DATE_FORMAT(ASSURE_PAR_DATE, '%d-%c-%Y') as ASSURE_PAR_DATE, S_ORDER
            from type_garde, section
            where ".$field." = section.S_ID
            and EQ_ID=".$eqid;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $CENTRE = intval($row["S_ID"]);
    $ASSURE_PAR_DATE=$row["ASSURE_PAR_DATE"];
    $S_ORDER=intval($row["S_ORDER"]);
        
    if ( $sppsub == 0 ) return 0;
    else if ( $S_ORDER == 0 ) return 0;
    else { 
         $num = my_date_diff($ASSURE_PAR_DATE, $day."-".$month."-".$year);
        $reste = $num % $sppsub;
        $s = ($S_ORDER + $reste) % $sppsub;
        if ( $s <= 0 ) $s = $s + $sppsub;
        if ( $day == 1 ) write_debugbox (
            "num=".$num."<br>".
            "S_ORDER=".$S_ORDER."<br>".
            "sppsub=".$sppsub."<br>".
            "ASSURE_PAR_DATE=".$ASSURE_PAR_DATE."<br>".
            "s=".$s."<br>");
        $query="select S_ID from section where S_PARENT=".$CENTRE." and S_ORDER=".$s;
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
         return intval($row["S_ID"]);
    }
}

//=====================================================================
// compte le personnel SPP pour la période J, N 
//=====================================================================
function count_personnel_spp_jour($year, $month, $day, $type, $section) {
    global $dbc;
    $query="select count(1) as NB from pompier p 
        where p.P_SECTION =".$section."
        and p.P_OLD_MEMBER=0
        and p.P_STATUT='SPP'
        and not exists (select 1 from indisponibilite i 
            where i.P_ID=p.P_ID
            and i.I_DEBUT <='".$year."-".$month."-".$day."'
            and i.I_FIN >='".$year."-".$month."-".$day."'
            and i.I_STATUS='VAL')";
    if ( $type == 'J' ) 
        $query .= " and p.P_REGIME in ('12h','24h')";
    else
        $query .= " and p.P_REGIME in ('24h')";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    return intval($row["NB"]);
}    

//=====================================================================
// compétences requises pour la garde
//=====================================================================
function show_competences($garde, $partie) {
    global $dbc,$red,$green;
    $competences = "";
    $queryp="select gc.PS_ID, p.TYPE, p.DESCRIPTION, gc.nb 
                from garde_competences gc left join poste p on gc.PS_ID = p.PS_ID
                where gc.EQ_ID=".$garde." 
                and gc.EH_ID=".$partie."
                order by p.PH_LEVEL desc, p.PS_ORDER, gc.PS_ID";
    $resultp=mysqli_query($dbc,$queryp);
    while ( $row = mysqli_fetch_array($resultp) ) {
        $desc=$row["nb"]." ".$row["DESCRIPTION"]." requis";
        $competences .= " <a title=\"".$desc."\"><span class='badge' >".$row["nb"]." ".$row["TYPE"]."</span></a>";
    }
    return $competences;
}  

//=====================================================================
// tableau de garde : display subgroup
//=====================================================================

function get_equipe_evenement($evenement) {
    global $dbc;
    $query="select E_EQUIPE from evenement where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    return intval($row["E_EQUIPE"]);
}

function get_regime_travail($eqid) {
    global $dbc;
    $query="select EQ_REGIME_TRAVAIL from type_garde where EQ_ID=".$eqid;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    return intval($row["EQ_REGIME_TRAVAIL"]);
}
function get_garde_id($section) {
    global $dbc;
    $query ="select EQ_ID from type_garde where EQ_REGIME_TRAVAIL <> 0 and S_ID = ".get_section_parent($section);
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    return intval($row["EQ_ID"]);
}
function get_regime($section) {
    global $dbc, $gardes;
    if ( $gardes == 0 ) return 0;
    $parent=get_section_parent($section);
    $query="select max(EQ_REGIME_TRAVAIL) from type_garde where EQ_SPP = 1 and S_ID in(".$section.",".$parent.")";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $regime=intval($row[0]);
    return $regime;
}

function get_section_garde_evenement($evenement,$EH_ID){
    global $dbc;
    $query="select SECTION_GARDE from evenement_horaire where E_CODE=".$evenement." and EH_ID=".$EH_ID;
      $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    return intval($row["SECTION_GARDE"]);
}


function get_inscrits_garde($evenement, $partie = 0, $flag=-1) {
    global $dbc;
    $liste="";
    $query="select distinct p.P_ID
        from evenement_participation ep, pompier p, evenement e
        where p.P_OLD_MEMBER = 0
        and p.P_ID = ep.P_ID
        and ep.EP_ABSENT = 0
        and ep.E_CODE = e.E_CODE
        and e.E_CODE = ".intval($evenement);
    if ( $flag === 1 or $flag === 0 )
        $query .= " and ep.EP_FLAG1 = ".$flag;
    if ( intval($partie) > 0 )
        $query .= " and ep.EH_ID = ".intval($partie);
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
       $liste .= $row["P_ID"].",";
    }
    return rtrim($liste,',');
}

function display_subgroup($status, $T, $year, $month, $day, $evenement, $section_jour, $section_nuit, $centre=0){
    global $dbc,$grades, $mylightcolor, $display_order, $grades_imgdir,$nbsections;
    //$T= J, N, A, I
    if ( $T == 'J' ) $comment='de la section du jour';
    else if ( $T == 'N' ) $comment='de la section de nuit';
    else if ( $T == 'A' ) $comment='des autres sections';
    else if ( $T == 'I' ) $comment='indisponible';
    else $comment = '';
    //$status=SPP ou SPV  
    // couleur pour lignes sélectionnées
    $mycolor2='#00FF00';
    
    // déjà inscrits
    if ( $status == 'SPP' ) $flag = 1;
    else $flag = 0;
    $inscritsJ=explode(",",get_inscrits_garde($evenement,1,$flag));
    $inscritsN=explode(",",get_inscrits_garde($evenement,2,$flag));
    $nb_parties=get_nb_sessions($evenement);
    $eqid=get_equipe_evenement($evenement);
    $regime=get_regime_travail($eqid);
    $html='';
    
    if ( $nbsections > 0 ) {
        $limit_cis_sp="";
    }
    else {
        $family=get_family("$centre");
        $limit_cis_sp=" and p.P_SECTION in (".$family.")";
    }
    // trouver les autres gardes du jour, pour éviter d'engager sur gardes caserne et FDF le même jour
    $query1="select distinct e.E_CODE from evenement e, evenement_horaire eh 
           where e.E_CODE=eh.E_CODE and e.TE_CODE='GAR'
           and eh.EH_DATE_DEBUT='".$year."-".$month."-".$day."'
           and e.E_CODE <> ".$evenement;
    $result1=mysqli_query($dbc,$query1);
    $other_gardes="0";
    while ($row1=@mysqli_fetch_array($result1) ) {
        $other_gardes .= intval($row1[0]).",";
    }
    $other_gardes=rtrim($other_gardes,',');
    
    // gardes de hier, qui était déjà de garde?
    $from_unix_time = mktime(0, 0, 0, $month, $day, $year);
    $day_before = strtotime("yesterday", $from_unix_time);
    $formatted = date('Y-m-d', $day_before);
    $query1="select distinct e.E_CODE from evenement e, evenement_horaire eh 
           where e.E_CODE=eh.E_CODE and e.TE_CODE='GAR'
           and eh.EH_DATE_DEBUT='".$formatted."'";
    $result1=mysqli_query($dbc,$query1);
    $inscrits_hier=array();
    while ($row1=@mysqli_fetch_array($result1) ) {
        $tmp_array=explode(",",get_inscrits_garde($row1["E_CODE"],2));
        foreach ($tmp_array as $pid) {
            if (intval($pid) > 0 ) array_push($inscrits_hier, $pid);
        }
    }
    
    // gardes de demain, qui sera de garde?
    $from_unix_time = mktime(0, 0, 0, $month, $day, $year);
    $day_after = strtotime("tomorrow", $from_unix_time);
    $formatted = date('Y-m-d', $day_after);
    $query1="select distinct e.E_CODE from evenement e, evenement_horaire eh 
           where e.E_CODE=eh.E_CODE and e.TE_CODE='GAR'
           and eh.EH_DATE_DEBUT='".$formatted."'";
    $result1=mysqli_query($dbc,$query1);
    $inscrits_demain=array();
    while ($row1=@mysqli_fetch_array($result1) ) {
        $tmp_array=explode(",",get_inscrits_garde($row1["E_CODE"],1));
        foreach ($tmp_array as $pid) {
            if (intval($pid) > 0 ) array_push($inscrits_demain, $pid);
        }
    }
    
    if ($status =='SPP') {
            $query="select distinct p.P_ID, upper(p.P_NOM) P_NOM, p.P_PRENOM, p.P_STATUT, p.P_REGIME,g.G_DESCRIPTION, g.G_GRADE, ep.P_ID as OTHER_GARDE, 0 as DISPO, '' as DC_COMMENT
            from pompier p left join evenement_participation ep on ep.P_ID=p.P_ID and ep.E_CODE in (".$other_gardes."), grade g
            where p.P_STATUT ='SPP'
            and g.G_GRADE = p.P_GRADE
            and p.P_OLD_MEMBER = 0";
            $query .= $limit_cis_sp;
            if ( $regime > 0 ) {
                if ($T == 'J') $query .=" and p.P_SECTION=".$section_jour;
                else if ($T == 'N') $query .=" and p.P_SECTION=".$section_nuit;
                else {
                    $query .=" and p.P_SECTION not in (".$section_jour.",".$section_nuit.")";
                    $query .=" and p.P_SECTION in (".get_family(get_section_parent($section_jour)).",".get_family(get_section_parent($section_nuit)).")";
                }
            }           
    }
    else if ($T == 'I' ) {
        $query="select distinct p.P_ID, upper(p.P_NOM) P_NOM, p.P_PRENOM, p.P_STATUT, p.P_REGIME, g.G_DESCRIPTION, g.G_GRADE, ep.P_ID as OTHER_GARDE, 0 as DISPO, '' as DC_COMMENT
            from pompier p left join evenement_participation ep on ep.P_ID=p.P_ID and ep.E_CODE in (".$other_gardes."), grade g
            where p.P_OLD_MEMBER = 0
            and g.G_GRADE = p.P_GRADE
            and p.P_STATUT ='".$status."'
            and not exists (select 1 from disponibilite d where d.P_ID=p.P_ID and d.D_DATE='".$year."-".$month."-".$day."' )";
        $query .=" and p.P_SECTION in (".get_family(get_section_parent($section_jour)).",".get_family(get_section_parent($section_nuit)).")";
        $query .= $limit_cis_sp;
    }
    else { // SPV
        $query="select distinct p.P_ID, upper(p.P_NOM) P_NOM, p.P_PRENOM, p.P_STATUT, p.P_REGIME, g.G_DESCRIPTION, g.G_GRADE, ep.P_ID as OTHER_GARDE, sum( d.PERIOD_ID * d.PERIOD_ID ) as DISPO, DC_COMMENT
            from pompier p 
            left join evenement_participation ep on (ep.P_ID=p.P_ID and ep.E_CODE in (".$other_gardes."))
            left join disponibilite_comment dc on (dc.P_ID = p.P_ID and dc.DC_YEAR ='".$year."' and dc.DC_MONTH ='".$month."'),
            disponibilite d ,grade g
            where p.P_OLD_MEMBER = 0
            and g.G_GRADE = p.P_GRADE
            and d.P_ID=p.P_ID
            and d.D_DATE='".$year."-".$month."-".$day."'";
        if ( $regime > 0 ) {
                if ($T == 'J') $query .=" and p.P_SECTION=".$section_jour;
                else if ($T == 'N') $query .=" and p.P_SECTION=".$section_nuit;
                else if ($T == 'other') $query.=" and p.P_ID in (select section_role.P_ID from section_role where GP_ID = ".get_specific_outside_role()." and S_ID = (".get_section_parent($section_jour)."))";
                else {
                    $query .=" and p.P_SECTION not in (".$section_jour.",".$section_nuit.")";
                    $query .=" and p.P_SECTION in (".get_family(get_section_parent($section_jour)).",".get_family(get_section_parent($section_nuit)).")";
                }
        }
        else { // cas du regime de garde autre
          $query="select distinct p.P_ID, upper(p.P_NOM) P_NOM, p.P_PRENOM, p.P_STATUT, p.P_REGIME, g.G_DESCRIPTION, g.G_GRADE, ep.P_ID as OTHER_GARDE, sum( d.PERIOD_ID * d.PERIOD_ID ) as DISPO, DC_COMMENT
            from pompier p 
            left join evenement_participation ep on (ep.P_ID=p.P_ID and ep.E_CODE in (".$other_gardes."))
            left join disponibilite_comment dc on (dc.P_ID = p.P_ID and dc.DC_YEAR ='".$year."' and dc.DC_MONTH ='".$month."'),
            disponibilite d ,grade g
            where p.P_OLD_MEMBER = 0
            and g.G_GRADE = p.P_GRADE
            and d.P_ID=p.P_ID
            and d.D_DATE='".$year."-".$month."-".$day."'";
          if ($T == 'other')$query.=" and p.P_ID in (select section_role.P_ID from section_role where GP_ID = ".get_specific_outside_role()." and S_ID = (".get_section_parent($section_jour)."))";
        }  
        if ($T != 'other') $query .= $limit_cis_sp;
        $query .=" group by p.P_ID";
    }
    if ( $display_order == 'name' ) $query .= " order by p.P_NOM, p.P_PRENOM";
    else $query .= " order by g.G_LEVEL desc";
    $result=mysqli_query($dbc,$query);
    write_debugbox($query);
    $nb=mysqli_num_rows($result);
    
    if (  $nb > 0 ) {
        if ( $status == 'SPP' ) $status_long = "Personnel Professionnel";
        else if ( $status == 'SPV' ) $status_long = "Personnel Volontaire";
        else $status_long = "Personnel";
        if ( $T == 'I' ) $ti=$status_long." ".$comment;
        else if ( $regime > 0 ) $ti=$status_long." ".$comment;
        else if ( $status == 'SPP' ) $ti=$status_long;
        else $ti= $status_long." disponible";
        if ( $nb_parties == 2 ) $col=7; else $col=6;
        $html .= "<tr class=TabHeader><td colspan='".$col."' >".$ti."</td></tr>";
    }
    while ($row=@mysqli_fetch_array($result) ) {
        $P_ID=$row["P_ID"];
        $P_NOM=$row["P_NOM"];
        $P_PRENOM=my_ucfirst($row["P_PRENOM"]);
        $G_DESCRIPTION=my_ucfirst($row["G_DESCRIPTION"]);
        $G_GRADE=$row["G_GRADE"];
        $DISPO=$row["DISPO"];
        $P_STATUT=$row["P_STATUT"];
        $P_REGIME=$row["P_REGIME"];
        $OTHER_GARDE=intval($row["OTHER_GARDE"]);
        $dispo = '';
        $comment = '';
        $dispocomment = $row["DC_COMMENT"];
        $mycolor=$mylightcolor;
        if ( $status == 'SPV' or $status == 'BEN' or $T== 'other' ) {
            $dispo=substr(dispo_label($DISPO),2,200);
            if ( $dispocomment <> '' )
                $dispo .=" <i class='fa fa-info-circle fa-lg' title=\"".$dispocomment."\"></i>";
        }
        if ( $P_STATUT == 'SPP' and $status == 'SPV') $class='SPPV';
        else $class=$P_STATUT;
        
        if ( $P_STATUT == 'SPP' and $status <> 'SPV' ) $regime="<span class=small title='Régime de travail $P_REGIME'>(".$P_REGIME.")</span>";
        else $regime="";

        if ( $grades ) $g= "<img src=".$grades_imgdir."/".$G_GRADE.".png height=18 title=\"".$G_DESCRIPTION."\">";
        else $g="";
        
        if (is_out($P_ID, $year, $month, $day)) {
            $comment .=" <i class='fa fa-exclamation-triangle fa-lg' style='color:red' title='ATTENTION Absence enregistrée ce jour'></i>";
        }
        if (in_array($P_ID, $inscrits_hier)) {
            $comment .=" <i class='fa fa-exclamation-triangle fa-lg' style='color:orange' title='ATTENTION Déjà de garde la nuit précédente'></i>";
        }
        if (in_array($P_ID, $inscrits_demain)) {
            $comment .=" <i class='fa fa-exclamation-triangle fa-lg' style='color:purple' title='ATTENTION Déjà prévu de garde jour demain '></i>";
        }
        if ($OTHER_GARDE > 0) {
            $comment .=" <i class='fa fa-exclamation-triangle fa-lg' style='color:red' title='ATTENTION Déjà engagé sur autre garde'></i>";
            $mycolor='lightgrey';
        }
        
        $checked1='';
        $checked2='';
        $c=$mycolor;
        $mycolor3=$mycolor;
        if (in_array($P_ID, $inscritsJ)) {
            $checked1='checked';
            $c=$mycolor2;
            $mycolor3='#ffcccc';
        }
        if (in_array($P_ID, $inscritsN)) {
            $checked2='checked';
            $c=$mycolor2;
            $mycolor3='#ffcccc';
        }
        
        $nb_heures=get_heures_gardes($P_ID,$year,$month);
        if ( $nb_heures == 0 ) $nb_heures='';
        else $nb_heures="<span class=badge title=\"Nombre d'heures de gardes attribuées ce mois\" style='font-size: 10px;'>".$nb_heures."</span>";
        if ( $DISPO == 30 ) {
            $check24=1; 
        }
        else $check24=0;
        $html .= "<tr bgcolor=".$c." id='row_".$P_ID."'>
            <td width=30><input type='checkbox' value='1' name='check1_".$P_ID."_".$status."' id='check1_".$P_ID."_".$status."' title='JOUR: cocher pour inscrire' $checked1 
                    onchange=\"checkGarde(this, check2_".$P_ID."_".$status.", row_".$P_ID.",'".$mycolor2."','".$mycolor3."', total1, total2, '".$check24."');\"></td>";
          
        if ( $nb_parties == 2 ) 
            $html .= "<td width=30><input type='checkbox' value='1' name='check2_".$P_ID."_".$status."'  id='check2_".$P_ID."_".$status."' title='NUIT: cocher pour inscrire' $checked2 
                    onchange=\"checkGarde(this, check1_".$P_ID."_".$status.", row_".$P_ID.",'".$mycolor2."','".$mycolor3."', total2, total1, 0);\"></td>";
        else 
            $html .= "<input type='hidden'  name='check2_".$P_ID."_".$status."'  id='check2_".$P_ID."_".$status."' value='0'>";
        $html .= "<td width=30>".$g."</td>
            <td width=200 ><a href='upd_personnel.php?pompier=".$P_ID."'><span class=$class>".$P_NOM." ".$P_PRENOM." ".$regime."</span></a></td>
            <td width=120 class='small'>".$dispo."</td>
            <td width=50>".$nb_heures."</td>
            <td class='small'>".$comment."</td>
            </tr>";
    }
    return $html;
}

function desinscrire_garde($evenement, $old_inscrits, $new_inscrits, $partie, $year1,$month1, $day1) {
    global $dbc, $show_indispos, $show_spp;
    foreach ($old_inscrits as $pid) {
        if ( intval($pid) > 0 ) {
            if (! in_array($pid, $new_inscrits)) {
                $nb=0;
                $pid_statut=get_statut($pid);
                // Attention si les indisponibles sont masqués, on ne les désinscrit pas
                if ( $pid_statut == 'SPP' and $show_spp == 1 ) $nb = 1;
                else if ( $pid_statut <> 'SPP' and $show_indispos == 1) $nb = 1;
                else if (  $pid_statut <> 'SPP' ) {
                    $query="select 1 from disponibilite where P_ID=".$pid." and D_DATE='".$year1."-".$month1."-".$day1."'";
                    $result=mysqli_query($dbc,$query);
                    $nb=mysqli_num_rows($result);
                }
                if ( $nb > 0 ) {
                    $query="delete from evenement_participation where E_CODE=".$evenement." and P_ID=".$pid." and EH_ID=".$partie;
                    $result=mysqli_query($dbc,$query);
                    if ( mysqli_affected_rows($dbc) > 0 ) {
                        insert_log('DESINSCP', $pid, "partie ".$partie, $evenement);
                        $query="delete from evenement_piquets_feu where E_CODE=".$evenement." and P_ID=".$pid." and EH_ID=".$partie;
                        $result=mysqli_query($dbc,$query);
                    }
                }
            }
        }
    }
}

//=====================================================================
// Nombre d'heures de gardes attribuées ce mois
//=====================================================================
function get_heures_gardes($pid,$year,$month) {
    global $dbc;
    if ( intval($month) < 10 ) $month="0".$month;
    $query="select sum(EP_DUREE) from evenement_participation ep, evenement_horaire eh, evenement e
            where ep.P_ID=".$pid." 
            and ep.EP_ABSENT=0
            and eh.EH_DATE_DEBUT >= '".$year."-".$month."-01'
            and eh.EH_DATE_FIN <= DATE_ADD('".$year."-".$month."-01', INTERVAL 1 MONTH)
            and eh.EH_DATE_DEBUT < DATE_ADD('".$year."-".$month."-01', INTERVAL 1 MONTH)
            and ep.E_CODE = eh.E_CODE
            and ep.EH_ID = eh.EH_ID
            and e.E_CODE = ep.E_CODE
            and e.E_CODE = eh.E_CODE
            and e.TE_CODE='GAR'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return intval($row[0]);
}

//=====================================================================
// label dispo
//=====================================================================

function dispo_label($DISPO) {
    global $dbc;
    // 24h
    if ( $DISPO == 30 ) $label = " - disponible 24h";
    // 0h
    else if ( $DISPO == 0 ) $label = " - non disponible";
    // 12h
    else if ( $DISPO == 25 ) $label = " - disponible 12h nuit";
    else if ( $DISPO == 5 ) $label = " - disponible 12h jour";
    else if ( $DISPO == 10 ) $label = " - disponible matin et soir";
    else if ( $DISPO == 13 ) $label = " - disponible après-midi et soir";
    else if ( $DISPO == 17 ) $label = " - disponible matin et nuit";
    else if ( $DISPO == 20 ) $label = " - disponible après-midi et nuit";
    // 6h
    else if ( $DISPO == 1 ) $label = " - disponible matin seulement";
    else if ( $DISPO == 4 ) $label = " - disponible après-midi seulement";
    else if ( $DISPO == 9 ) $label = " - disponible soir seulement";
    else if ( $DISPO == 16 ) $label = " - disponible nuit seulement";
    // 18h
    else if ( $DISPO == 14 ) $label = " - disponible matin, après-midi et soir";
    else if ( $DISPO == 21 ) $label = " - disponible matin, après-midi et nuit";
    else if ( $DISPO == 26 ) $label = " - disponible matin, soir et nuit";
    else if ( $DISPO == 29 ) $label = " - disponible après-midi, soir et nuit";
    else $label="";
    
    return $label;
}

function is_dispo_jour($DISPO) {
    if ( in_array($DISPO, array(5,14,21,30)) ) return true;
    else return false;
}

function is_dispo_nuit($DISPO) {
    if ( in_array($DISPO, array(25,26,29,30)) ) return true;
    else return false;
}

//=====================================================================
// compter SPP
//=====================================================================

function get_number_spp() {
    global $dbc;
    // y a t il des SPP
    $query="select count(1) as 'NB' from pompier where P_STATUT='SPP' and P_OLD_MEMBER = 0";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $NB=$row['NB'];
    return $NB;
}

// ===============================================
// LES HORAIRES DU SP PENDANT SA GARDE
// =============================================== 
 function get_horaire($P_ID, $E_CODE) {
    $d=1;
    $n=1;
    $i=0;
    
    $EP_DEBUT_JOUR="";
    $EP_FIN_JOUR="";
    $EP_DEBUT_NUIT="";
    $EP_FIN_NUIT="";
    $HR_DEBUT_JOUR="";
    $HR_FIN_JOUR="";
    $HR_DUREE_JOUR =""; 
    $HR_DUREE_NUIT="";
    $HR_DEBUT_NUIT="";
    $HR_FIN_NUIT="";
    
    global $dbc;
    $arrow=" <i class='fa fa-arrow-right'></i> ";
    $repH = "SELECT EH_DEBUT, EH_FIN, EH_DUREE, EH_ID  FROM evenement_horaire WHERE  E_CODE = ".$E_CODE;
    $resultH=mysqli_query($dbc,$repH);
    while ($hor = @mysqli_fetch_array($resultH)){
        if ($hor['EH_ID'] == 1){
            $HR_DEBUT_JOUR = $hor['EH_DEBUT']; 
            $HR_FIN_JOUR = $hor['EH_FIN']; 
            $HR_DUREE_JOUR = $hor['EH_DUREE']; 
        }
        else {
            $HR_DEBUT_NUIT = $hor['EH_DEBUT'];
            $HR_FIN_NUIT = $hor['EH_FIN'];
            $HR_DUREE_NUIT = $hor['EH_DUREE'];
        }
      }
    $m=''; $am='';
    $SP_TOTAL_TIME_PARTICIPATION_GARDE = '';
    $rep = "SELECT EH_ID, EP_DUREE, EP_DEBUT, EP_FIN FROM evenement_participation WHERE P_ID =".$P_ID." AND E_CODE = ".$E_CODE;
    $result=mysqli_query($dbc,$rep);
    $h_m=0; $h_am=0;
    while ($data = @mysqli_fetch_array($result)){
        if ($data['EH_ID'] == 1) {
            $m = 1;
            $h_m = intval($data['EP_DUREE']);
            $EP_DEBUT_JOUR = $data['EP_DEBUT'];
            $EP_FIN_JOUR = $data['EP_FIN'];
        }
        if ($data['EH_ID'] == 2) {
            $am = 1;
            $h_am = intval($data['EP_DUREE']);
            $EP_DEBUT_NUIT = $data['EP_DEBUT'];
            $EP_FIN_NUIT = $data['EP_FIN'];
        }
    }
    $SP_TOTAL_TIME_PARTICIPATION_GARDE = $h_m + $h_am;
    $HORAIRE = '';
    //Seulement le jour
    if ($m == 1 && $am == ''){
        if($h_m == $HR_DUREE_JOUR){ 
            $HORAIRE = substr($HR_DEBUT_JOUR,0,-3).$arrow.substr($HR_FIN_JOUR,0,-3);
        }
        else{
            $HORAIRE = substr($EP_DEBUT_JOUR,0,-3).$arrow.substr($EP_FIN_JOUR,0,-3);
        }
    }
    //Seulement la nuit    
    elseif ($am == 1  && $m == ''){
        if ($h_am == $HR_DUREE_NUIT){
            $HORAIRE = substr($HR_DEBUT_NUIT,0,-3).$arrow.substr($HR_FIN_NUIT,0,-3);
           }
        else{
               $HORAIRE = substr($EP_DEBUT_NUIT,0,-3).$arrow.substr($EP_FIN_NUIT,0,-3);
        }    
    }
    //jour et nuit
    else {
        if($h_m != $HR_DUREE_JOUR || $h_am != $HR_DUREE_NUIT){
            if ($h_m != $HR_DUREE_JOUR){
                   $HORAIRE = substr($EP_DEBUT_JOUR,0,-3).$arrow.substr($EP_FIN_JOUR,0,-3);
               }
               else {
                   $HORAIRE .= '<br>'.substr($HR_DEBUT_JOUR,0,-3).$arrow.substr($HR_FIN_JOUR,0,-3);
               }    
               if ($h_am != $HR_DUREE_NUIT){
                   $HORAIRE .= '<br>'.substr($EP_DEBUT_NUIT,0,-3).$arrow.substr($EP_FIN_NUIT,0,-3);
               }
               else {
                   $HORAIRE .= '<br>'.substr($HR_DEBUT_NUIT,0,-3).$arrow.substr($HR_FIN_NUIT,0,-3);
               }    
        }
        else {
            $HORAIRE = '24h';
        }    
       }

       $RETURN = array($HORAIRE, $SP_TOTAL_TIME_PARTICIPATION_GARDE);        
    return $RETURN;
}

// =======================================================================================
// SI LE SPP NE FAIT PAS TOUS SES HORAIRES DE GARDE ALORS IL LUI RESTE DU TEMPS DE DISPO
// =======================================================================================
function dispo_hr_spp ($P_ID, $date) {
    global $dbc;
    $period = array();
    $querySP="select PERIOD_ID from disponibilite where P_ID=".$P_ID." and D_DATE = '".$date."'";
    $resultSP=mysqli_query($dbc,$querySP);
    $i=0;
    while($rowSP=@mysqli_fetch_array($resultSP)) {
        $period[$i] = $rowSP['PERIOD_ID'];
        $i++;
    }
    $SPP_DISPO_TIME_DAY = 0;
    foreach ($period as $key => $period_code) {
        $queryP="select DP_DUREE from disponibilite_periode where DP_ID = '".$period_code."'";
        $resultP=mysqli_query($dbc,$queryP);
        $i=0;
        
        while($rowP=@mysqli_fetch_array($resultP)) {
            $SPP_DISPO_TIME_DAY = intval($SPP_DISPO_TIME_DAY) + intval($rowP['DP_DUREE']);
        }
    }
    return $SPP_DISPO_TIME_DAY;
}

// =======================================================================================
// get notification?
// =======================================================================================
function get_reminder ($P_ID, $F_ID){
    global $dbc, $cron_allowed;
    if ( $cron_allowed == 0 ) return 0;
    if (! check_rights($P_ID, $F_ID) ) return 0;
    $query = "select count(1) from notification_block where P_ID = ".intval($P_ID)." and F_ID=".intval($F_ID);
    $result = mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    if ( $row[0] > 0 ) return 0;
    else return 1;
}

// =======================================================================================
// Fonction pour afficher les postes d'un engin sous forme de tableau
// =======================================================================================
function display_postes ($evenement, $vehicule, $showjour=true, $shownuit=true, $print_mode=false) {
    global $dbc, $mylightcolor, $mydarkcolor, $personnel, $comps, $grades, $grades_imgdir;

    $html ="<table cellspacing='0' border='0'>";
    $html .="<tr class='TabHeader'><td>Rôle</td>";
    if ( $showjour ) $html .="<td>Jour</td>";
    if ( $shownuit ) $html .="<td>Nuit</td>" ;

    $query="SELECT tev.TV_CODE, tev.ROLE_ID, tev.ROLE_NAME, tev.EH_ID, epf.P_ID, epf.P_NOM, epf.P_PRENOM, epf.P_GRADE, tev.COMPETENCE, tev.PS_ID, tev.DESCRIPTION
            from 
            ( select ev.E_CODE, v.V_ID, v.TV_CODE, tvr.ROLE_ID, tvr.ROLE_NAME, ev.EH_ID, tvr.PS_ID, ps.TYPE COMPETENCE, ps.DESCRIPTION
                from type_vehicule_role tvr left join poste ps on ps.PS_ID = tvr.PS_ID, evenement_vehicule ev, vehicule v
                where tvr.TV_CODE = v.TV_CODE
                and ev.E_CODE = ".$evenement." and ev.V_ID =".$vehicule."
                and v.V_ID = ev.V_ID
            ) tev
            left join (
                select e.V_ID, e.E_CODE, e.EH_ID, e.ROLE_ID, e.P_ID, p.P_NOM, p.P_PRENOM, p.P_GRADE
                from evenement_piquets_feu e
                left join pompier p on e.P_ID = p.P_ID
                where e.E_CODE = ".$evenement." and e.V_ID =".$vehicule."
            ) epf
            on (epf.V_ID = tev.V_ID and epf.EH_ID = tev.EH_ID and epf.E_CODE = tev.E_CODE and epf.ROLE_ID = tev.ROLE_ID)
            where tev.E_CODE = ".$evenement."
            and tev.V_ID =".$vehicule."
            order by tev.ROLE_ID, tev.EH_ID
            ";
    $result=mysqli_query($dbc,$query);
    
    while ($row = mysqli_fetch_array($result)){
        $TV_CODE = $row["TV_CODE"];
        $ROLE_ID = $row["ROLE_ID"];
        $ROLE_NAME = $row["ROLE_NAME"];
        $EH_ID = $row["EH_ID"];
        $P_ID = intval($row["P_ID"]);
        $P_GRADE = $row["P_GRADE"];
        $P_NOM = $row["P_NOM"];
        $P_PRENOM = $row["P_PRENOM"];
        $COMPETENCE = $row["COMPETENCE"];
        $DESCRIPTION = $row["DESCRIPTION"];
        $PS_ID = intval($row["PS_ID"]);
        $alert=""; $select_special="";
        if ( $PS_ID > 0 ) $cmt = "<i class ='fa fa-exclamation-triangle' title=\"compétence requise ".$COMPETENCE." - ".$DESCRIPTION."\"></i> <span class=small title=\"compétence requise ".$COMPETENCE." - ".$DESCRIPTION."\">".$COMPETENCE."</span>";
        else $cmt="";
        if ( $grades ) $grade = "<img src=".$grades_imgdir."/".$P_GRADE.".png title='".$P_GRADE."' class='img-max-18'>";
        else $grade = "";
        if ( $P_ID == 0 ) {
            if ( $print_mode ) $name = '';
            else $name = "<span class='noprint' style='color:grey'><small>Choisir</small></span>";
        }
        else {
            if ( $PS_ID > 0 and ! $print_mode ) {
                if ( ! isset( $comps[$P_ID][$PS_ID] )) {
                    $alert = " <a href=upd_personnel.php?tab=2&pompier=".$P_ID.">
                            <i class='fa fa-exclamation-triangle' style='color:orange;' title=\"Cette personne n'a pas la compétence ".$COMPETENCE." - ".$DESCRIPTION." valide. Cliquer pour voir ses compétences.\"></i></a>";
                    $select_special="style='background:#cccccc;' title='Attention, personnel non qualifié pour ce rôle'";
                }
            }
            $name = "<span class='printable'>".$grade." ".strtoupper($P_NOM)." ".ucfirst($P_PRENOM).$alert."</span>";
        }
        
        if ( $print_mode ) $modify=$name;
        else {
            // ======================== choisir la personne pour ce piquet ===========
            $current=" <div id='htmldiv_".$EH_ID."_".$vehicule."_".$ROLE_ID."' name='htmldiv_".$EH_ID."_".$vehicule."_".$ROLE_ID."' title='changer le personnel'>".$name."</div>";
            $modify = "<a href=\"javascript:ReverseContentDisplay('r_".$EH_ID."_".$vehicule."_".$ROLE_ID."');\" >".$current."</a>";
            $modify .=  "<div name='r_".$EH_ID."_".$vehicule."_".$ROLE_ID."' id='r_".$EH_ID."_".$vehicule."_".$ROLE_ID."'
               align='left'
               style='display: none;
               position: absolute; 
               border-style: solid;
               border-width: 2px;
               background-color: $mylightcolor; 
               border-color: $mydarkcolor;
               width: 280px;
               height: 120px;
               padding: 5px;'>
            <i class='fa fa-user fa-lg'></i> <b>".$ROLE_NAME." ".$TV_CODE."</b>
            <br>";

            $modify .= "<select id='select_".$EH_ID."_".$vehicule."_".$ROLE_ID."' name='select_".$EH_ID."_".$vehicule."_".$ROLE_ID."' $select_special
            onchange=\"savepiquet('".$evenement."',
                                  '".$grades_imgdir."',
                                  select_".$EH_ID."_".$vehicule."_".$ROLE_ID.",
                                  htmldiv_".$EH_ID."_".$vehicule."_".$ROLE_ID.",
                                  r_".$EH_ID."_".$vehicule."_".$ROLE_ID.");\">
            <option value='0_0'>Personne</option>";
            
            
            foreach ($personnel[$EH_ID] as $pid => $other_name) {
                if ( $pid == $P_ID) $selected='selected';
                else $selected='';
                $option_special= "style='background:white;' title='Personnel qualifié pour ce rôle'";
                $prefix='1';
                if ( $PS_ID > 0 ) {
                    if ( ! isset( $comps[$pid][$PS_ID] ) ) { 
                        $option_special="style='background:#cccccc;' title='Attention, personnel non qualifié pour ce rôle'";
                        $prefix='0';
                    }
                    else {
                       $VAL = $comps[$pid][$PS_ID];
                       if ( $VAL == 1 ) $option_special="style='background:#ccffcc;'  title='Compétence principale'";
                       else $option_special="style='background:#80d4ff;'  title='Compétence secondaire, éviter ce piquet si possible'";
                    }
                }
                $modify .= "<option value='".$prefix."_".$pid."' $selected $option_special>".$other_name."</option>";
            }
           
            $modify .= "</select>
            <p><div align=center>
                <a onmouseover=\"HideContent('r_".$EH_ID."_".$vehicule."_".$ROLE_ID."'); return true;\" class='btn btn-default'
                            href=\"javascript:HideContent('r_".$EH_ID."_".$vehicule."_".$ROLE_ID."');\">fermer</a>
            </div>
            </form>
            </div>";
            // =============================
        }
        if ($EH_ID == 1) {
           $html .="<tr bgcolor=$mylightcolor><td width=150>".$ROLE_NAME." ".$cmt."</td>";
           $html .="<td width=250>".$modify." </td>";
        }
        else if ($EH_ID == 2) {
            $html .="<td width=250>".$modify."</td>";
            $html .= "</tr>";
        }
    }
    $html .= "</table>";
    return $html ;
}

// =======================================================================================
// send mail
// =======================================================================================
function mail_garde($nom, $prenom, $email, $heures) {
    global $EQ_NOM, $month, $year, $cisname;
    $Subject="Tableau ".$EQ_NOM." disponible pour ".moislettres($month)." ".$year;
    $SenderName=$_SESSION['SES_PRENOM']." ".$_SESSION['SES_NOM'];
    $Mailcontent="Bonjour ".my_ucfirst($prenom).",
Le tableau ".$EQ_NOM." est disponible pour ".moislettres($month)." ".$year."
Au total ". $heures." heures de garde vous ont été attribuées.
Vous pouvez voir le détail sur ".$cisname;
    if ( @$_SERVER["HTTP_HOST"] <> '127.0.0.1' ) mysendmail2($email,$Subject,$Mailcontent,$SenderName,$_SESSION['SES_EMAIL'],$Attachment="None");
}
?>
