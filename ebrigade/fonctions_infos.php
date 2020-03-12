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
// function display tile
//=====================================================================
function widget($function, $title, $link = '', $linkcomment='') {
    $inner_html=$function();
    if ( $title=='curdate' ) $title=curdate();
    if ( $link <> '' ) {
        if ( $link == 'factures' ) {
            $s=intval(@$_SESSION['SES_SECTION']);
            $start=date('d-m-Y', strtotime('-100 days'));
            $end=date("d-m-Y");
            $link = "export.php?filter=".$s."&subsections=0&exp=1tnonpaye&type_event=ALL&dtdb=".$start."&dtfn=".$end."&affichage=ecran&show=1";
        }
        $title="<a href=".$link." title=\"".$linkcomment."\" style='color:#ffffcc;'>".$title."</a>";
    }

    return "
    <div class='card card-default'>
        <div class='card-header' >
            <strong>".$title."</strong>
        </div>
        <div class='card-body'>
            <div class='row'>".$inner_html."</div>
        </div>
    </div>";
}

//=====================================================================
// write buttons
//=====================================================================

function widget_condition($type,$value) {
    global $id;
    // output value can be  
    // true: display
    // false: do not display
    $configs= array('evenements','gardes','disponibilites','assoc','vehicules','consommables','remplacements','syndicate','army');
    $ret=false;
    if ( $type == 'multi_check_rights_notes' ) {
        if ( multi_check_rights_notes($id)) $ret = true;
    }
    else if ( $type == 'permission' ) {
        if ( check_rights($id, $value) ) $ret = true;
    }
    else if ( in_array($type,$configs) ) {
        global $$type;
        if ( $$type == $value ) $ret = true;
    }
    return $ret;
}

function write_buttons() {
    global $dbc, $id, $nbsections, $evenements, $gardes, $disponibilites;
    $out="";
    $query = "select w.W_ID, w.W_TITLE, w.W_LINK, w.W_LINK_COMMENT, w.W_ICON
            from widget w left join widget_user wu on wu.W_ID = w.W_ID and wu.P_ID = ".$id."
            where W_TYPE='button'
            and ( wu.WU_VISIBLE is null or wu.WU_VISIBLE = 1 )
            order by wu.WU_ORDER, w.W_ORDER";
    $result=mysqli_query($dbc,$query);
    while ($row = mysqli_fetch_array($result)) {
        $W_ID=$row["W_ID"];
        $W_TITLE=$row["W_TITLE"];
        $W_LINK=$row["W_LINK"];
        $W_LINK_COMMENT=$row["W_LINK_COMMENT"];
        $W_ICON=$row["W_ICON"];
        // evaluate the display conditions
        $query2="select WC_TYPE,WC_VALUE from widget_condition where W_ID=".$W_ID;
        $result2=mysqli_query($dbc,$query2);
        $num2=mysqli_num_rows($result2);
        $display=true;
        while ($row2 = mysqli_fetch_array($result2)) {
            $display=widget_condition($row2["WC_TYPE"],$row2["WC_VALUE"]);
            if ( ! $display ) break;
        }
        if ( $display ) {
            $out .= " <span class='form-group'>
                    <a class='btn btn-ebrigade btn-lg' href='".$W_LINK."' title=\"".$W_LINK_COMMENT."\">
                    <i class='fa ".$W_ICON."'></i> ".$W_TITLE."</a>
                </span>";
            
        }
    }
    return $out;
}

function write_boxes($style='default') {
    global $dbc, $id, $nbsections, $evenements, $gardes, $disponibilites, $assoc, $army, $remplacements, $syndicate;
    $out="";

    if ( $style == 'configure' )
        $css="style='cursor: all-scroll;'";
    else
        $css="";
    $out .= "<div class='container-fluid'>
            <div class='row'>
                <div class='col-sm-4' >
                    <div class='row'>";
                    
    if ( $style == 'configure' )
        $out .= "<ul id='sortable1' class='dropzone'>";
                
    $query = "select w.W_ID, w.W_FUNCTION, w.W_TITLE, w.W_LINK, w.W_LINK_COMMENT, w.W_COLUMN, 
            case 
                when wu.WU_COLUMN is null then w.W_COLUMN
                else wu.WU_COLUMN
            end as WCOL,
            case 
                when wu.WU_ORDER is null then w.W_ORDER
                else wu.WU_ORDER
            end as WORDER,
            case 
                when wu.WU_VISIBLE is null then 1
                else wu.WU_VISIBLE
            end as WVISI
            from widget w left join widget_user wu on wu.W_ID = w.W_ID and wu.P_ID = ".$id."
            where w.W_TYPE='box' and w.W_FUNCTION is not null
            order by WCOL, WORDER";
    write_debugbox($query);
    $result=mysqli_query($dbc,$query);
    $prev_col=1;
    while ($row = mysqli_fetch_array($result)) {
        $W_ID=$row["W_ID"];
        $W_FUNCTION=$row["W_FUNCTION"];
        $W_TITLE=$row["W_TITLE"];
        if ( $W_TITLE=='curdate' ) $W_TITLE=curdate();
        $W_LINK=$row["W_LINK"];
        $W_LINK_COMMENT=$row["W_LINK_COMMENT"];
        $WCOL=$row["WCOL"];
        $WVISI=$row["WVISI"];
        
        // nouvelle colonne
        if ( $WCOL <> $prev_col ) {

                $out .= " </div>
                </div>
                <div class='col-sm-4' >
                    <div class='row'>";
                    
                if ( $style == 'configure' ) 
                $out .= "</ul><ul id='sortable".$WCOL."' class='dropzone'>";
        
        }
        $prev_col = $WCOL;
        // evaluate the display conditions
        $query2="select WC_TYPE,WC_VALUE from widget_condition where W_ID=".$W_ID;
        $result2=mysqli_query($dbc,$query2);
        $num2=mysqli_num_rows($result2);
        $display=true;
        while ($row2 = mysqli_fetch_array($result2)) {
            $display=widget_condition($row2["WC_TYPE"],$row2["WC_VALUE"]);
            if ( ! $display ) break;
        }
        if ( $display ) {
            if ( $style == 'configure' ) {
                if ( $WVISI == 0 ) {
                    $checked = '';
                    $class = 'dgrey';
                }
                else {
                    $checked = 'checked';
                    $class = 'ddefault';
                }
                $checkbox="<input type='checkbox' id='C".$W_ID."' title=\"Cocher pour activer l'affichage de ce widget\" value='1' $checked onchange=\"activateWidget('".$W_ID."');\">";
                $out .= "<li class='draggable ".$class."' $css id='".$W_ID."' title=\"".$W_LINK_COMMENT."\">".$W_TITLE." ".$checkbox."</li>";
            }
            else if ( $WVISI == 1 )
                $out .= "<div class='col-sm-12' >".widget($W_FUNCTION, $W_TITLE, $W_LINK, $W_LINK_COMMENT)."</div>";
        }
    }
    if ( $style == 'configure' ) 
        $out .= "   </ul>";

        $out .= "   </div>
                </div>
            </div>
        </div>";
    
    return $out;
}

//=====================================================================
// some technical functions
//=====================================================================
function curdate() {
    $madate=date_fran(date('m'), date('j'), date('Y')) ."-".date('m-Y H:i').' (semaine '.date('W').')';
    return ucfirst($madate);
}

function bday($date){
    global $dbc, $nbsections, $id, $N;
    $section = get_section_of($id);
      $query="select P_NOM, P_PRENOM, P_ID from pompier 
              where P_OLD_MEMBER=0
            and P_STATUT <> 'EXT'";
    if ( $nbsections == 0 )
            $query.=" and P_HIDE=0 and P_SECTION in (".get_section_and_subsections("$section").")";
    $query.=" and date_format(P_BIRTHDATE,'%m-%d') = '".date("m-d", $date )."'
            order by P_NOM";
    $result=mysqli_query($dbc,$query);
    $num=mysqli_num_rows($result);
    $out1="";
    if ( $num <> 0 ) {
         $N++;
         while ($row = mysqli_fetch_array($result)) {
            $P_ID=$row["P_ID"];
            $P_PRENOM=my_ucfirst($row["P_PRENOM"]);
            $P_NOM=strtoupper($row["P_NOM"]);
            $out1 .=" <a href=upd_personnel.php?pompier=".$P_ID." >".$P_PRENOM." ".$P_NOM."</a> ";
        }
    }
    return $out1;
}

// warning si infos perso manquantes
function missing_field($row,$field, $description) {
    global $id;
    if ($row[$field] == '0' or $row[$field] == '') {
        return  "<tr><td><i class='fa fa-exclamation-triangle fa-lg' style='color:orange;' title='Attention vos données personnelles sont incomplètes' ></i>
                    <b>".$description."</b> à renseigner sur votre <a href=upd_personnel.php?pompier=$id&tab=1>fiche personnel</a></td></tr>";
    }
}

//=====================================================================
// function alerts
//=====================================================================
$bell="<i class='fa fa-bell fa-2x fa-spin' style='color:#ff6600;'></i>";

//=====================================================================
// function consommables
//=====================================================================

function show_alerts_consommables() {
    global $dbc, $bell, $nbsections, $assoc, $army;
    $id=intval(@$_SESSION['id']);
    if ( $nbsections > 0 and check_rights($id,71,0)) $mysection=0;
    else $mysection=get_highest_section_where_granted($id,71);
    if ( ( $assoc or $army ) and $mysection == 0 ) $mysection=intval(@$_SESSION['SES_SECTION']);
    $out="";
    $a=" <a href='consommable.php?order=C_DATE_PEREMPTION&filter".$mysection."' title=\"Cliquer pour voir les produits consommables\" >";
    $query="select count(1) as NB from consommable c
            where datediff(c.C_DATE_PEREMPTION, '".date("Y-m-d")."') <= 30
            and c.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    if (  $nb > 0 ) {
        $out .= "<table class=noBorder><tr><td>".$a.$bell."</a></td>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td>".$a."<span class='badge orange-badge'>".$nb."</span> produit".$s." consommable".$s." périmés ou bientôt périmés</a></td></tr></table>";
    }
    return $out;
}

//=====================================================================
// function véhicules
//=====================================================================

function show_alerts_vehicules() {
    global $dbc, $bell, $nbsections, $assoc, $army;
    $id=intval(@$_SESSION['id']);
    if ( $nbsections > 0 and check_rights($id,17,0)) $mysection=0;
    else $mysection=get_highest_section_where_granted($id,17);
    if ( ( $assoc or $army ) and $mysection == 0 ) $mysection=intval(@$_SESSION['SES_SECTION']);
    $out = "";
    
    // des véhicules  indisponibles
    $query="select count(1) from vehicule v, vehicule_position vp
    where vp.VP_ID=v.VP_ID
    and vp.VP_OPERATIONNEL < 2
    and vp.VP_OPERATIONNEL >= 0
    and v.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    if (  $nb > 0 ) {
        $a=" <a href='vehicule.php?order=TV_CODE&filter=".$mysection."&TV_CODE=ALL&subsections=1&includeold=0&order=VP_OPERATIONNEL' title=\"Cliquer pour voir les véhicules\" >";
        $out .= "<table class=noBorder><tr><td>".$a.$bell."</a></td>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td>".$a."<span class='badge orange-badge'>".$nb."</span> véhicule".$s." indisponible".$s."</a></td></tr></table>";
    }
    
    // des assurances périmées?
    $query="select count(1) from vehicule v, vehicule_position vp
    where vp.VP_ID=v.VP_ID
    and v.V_ASS_DATE < NOW()
    and vp.VP_OPERATIONNEL >=0
    and v.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    if (  $nb > 0 ) {
        $a=" <a href='vehicule.php?order=TV_CODE&filter=".$mysection."&TV_CODE=ALL&subsections=1&includeold=0&order=DT_ASS' title=\"Cliquer pour voir les véhicules\" >";
        $out .= "<table class=noBorder><tr><td>".$a.$bell."</a></td>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td>".$a."Assurance périmée pour <span class='badge orange-badge'>".$nb."</span> véhicule".$s."</a></td></tr></table>";
    }
        
    // des CT périmés?
    $query="select count(1) from vehicule v, vehicule_position vp
    where vp.VP_ID=v.VP_ID
    and datediff(v.V_CT_DATE,'".date("Y-m-d")."') <= 0
    and vp.VP_OPERATIONNEL >=0
    and v.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    if (  $nb > 0 ) {
        $a=" <a href='vehicule.php?order=TV_CODE&filter=".$mysection."&TV_CODE=ALL&subsections=1&includeold=0&order=DT_CT' title=\"Cliquer pour voir les véhicules\" >";
        $out .= "<table class=noBorder><tr><td>".$a.$bell."</a></td>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td>".$a."Contôle technique périmé pour <span class='badge orange-badge'>".$nb."</span> véhicule".$s."</a></td></tr></table>";
    }
    
    // des CT a refaire dans moins de 2 mois?
    $query="select count(1) from vehicule v, vehicule_position vp
    where vp.VP_ID=v.VP_ID
    and datediff(v.V_CT_DATE,'".date("Y-m-d")."') <= 60
    and datediff(v.V_CT_DATE,'".date("Y-m-d")."') > 0
    and vp.VP_OPERATIONNEL >=0
    and v.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    if (  $nb > 0 ) {
        $a=" <a href='vehicule.php?order=TV_CODE&filter=".$mysection."&TV_CODE=ALL&subsections=1&includeold=0&order=DT_CT' title=\"Cliquer pour voir les véhicules\" >";
        $out .= "<table class=noBorder><tr><td>".$a.$bell."</a></td>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td>".$a."Contôle technique bientôt périmé pour <span class='badge orange-badge'>".$nb."</span> véhicule".$s."</a></td></tr></table>";
    }
    
    // des révisions à faire
    $query="select count(1) from vehicule v, vehicule_position vp
    where vp.VP_ID=v.VP_ID
    and datediff(v.V_REV_DATE,'".date("Y-m-d")."') <= 0
    and vp.VP_OPERATIONNEL >=0
    and v.S_ID in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]);
    if (  $nb > 0 ) {
        $a=" <a href='vehicule.php?order=TV_CODE&filter=".$mysection."&TV_CODE=ALL&subsections=1&includeold=0&order=DT_REV' title=\"Cliquer pour voir les véhicules\" >";
        $out .= "<table class=noBorder><tr><td>".$a.$bell."</a></td>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td>".$a."Révision à faire pour <span class='badge orange-badge'>".$nb."</span> véhicule".$s."</a></td></tr></table>";
    }
    //if ($out <> "") $out ="<div align=left>".$out."</div>";
    return $out;
}

//=====================================================================
// function CP à valider
//=====================================================================

function show_alerts_cp() {
    global $dbc, $bell, $nbsections;
    $out="";
    $id=intval(@$_SESSION['id']);
    if ( $nbsections > 0 and check_rights($id,13,0)) $mysection=0;
    else $mysection=get_highest_section_where_granted($id,13);
    // des CP à valider?
    $query="select date_format(min(i.I_DEBUT),'%d-%m-%Y') I_DEBUT, date_format(max(i.I_FIN),'%d-%m-%Y') I_FIN, count(1) as NB from pompier p, indisponibilite i, type_indisponibilite ti, indisponibilite_status ist
    where p.P_ID=i.P_ID
    and i.TI_CODE=ti.TI_CODE
    and i.I_STATUS=ist.I_STATUS
    and p.P_STATUT in ('SAL','SPP','FONC')
    and ti.TI_CODE in ('CP','RTT')
    and p.P_ID <> ".$id."
    and ist.I_STATUS = 'ATT'
    and i.I_FIN >= NOW()";
    $query .=" and P_SECTION in (".get_family("$mysection").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row["NB"]);
    if ( $nb == 0 )
         $out .= "<span class=small>Pas de demandes de congés à valider</span>";
    else {
        $min=$row["I_DEBUT"];
        $max=$row["I_FIN"];
        $a=" <a href='indispo_choice.php?filter=".$mysection."&validation=ATT&person=ALL&dtdb=".$min."&dtfn=".$max."' title=\"Vous avez $nb Congés a valider\" >";
        $out = "<table class=noBorder><tr><td>".$a.$bell."</a></td>";
        if ( $nb > 1 ) $s='s';
        else $s='';
        $out .= "<td>".$a."Vous avez <span class='badge orange-badge'>".$nb."</span> demande".$s." de congés à valider</a></td></tr></table>";
    }
    return $out;
}

//=====================================================================
// function horaires à valider
//=====================================================================

function show_alerts_horaires() {
    global $dbc, $bell, $nbsections;
    $out="";
    $id=intval(@$_SESSION['id']);
    if ( $nbsections == 0 ) $mysection=intval(@$_SESSION['SES_SECTION']);
    else $mysection=get_highest_section_where_granted($id,13);
    if ( $mysection == 0 and $nbsections == 0 ) $list='0';
    else $list=get_family("$mysection");
    
    $query = " select p.P_ID, p.P_NOM, p.P_PRENOM, 
        sf.s_code,
        hv.ANNEE,
        hv.SEMAINE,
        concat('<a href=horaires.php?view=week&year=',hv.ANNEE,'&week=',hv.SEMAINE,'&from=export&person=',p.P_ID,' target=_blank>',hv.SEMAINE,'</a>') 'Semaine', 
        concat('<span class=',hs.HS_CLASS,'>',hs.HS_DESCRIPTION,'</span>') 'statut', 
        sum(round(h.H_DUREE_MINUTES/60, 2)) 'DUREE'
        from pompier p, section_flat sf left join section sp on sp.s_id = sf.s_parent, horaires h, horaires_statut hs, horaires_validation hv
        where  p.P_SECTION = sf.S_ID
        and p.P_ID = h.P_ID
        and hs.HS_CODE = hv.HS_CODE
        and hv.P_ID = h.P_ID
        and (
            ( YEAR(h.H_DATE) = hv.ANNEE and WEEK(h.H_DATE,1) = hv.SEMAINE )
              or 
            ( WEEK(h.H_DATE,1) = 53 and hv.SEMAINE=1 and YEAR(h.H_DATE) + 1 = hv.ANNEE )
        )
        and hv.HS_CODE ='ATTV'
        AND date_format(h.H_DATE,'%Y-%m-%d') < '".date("Y-m-d")."'
        AND DATEDIFF('".date("Y-m-d")."', date_format(h.H_DATE,'%Y-%m-%d')) < 100
        and sf.s_id in (".$list.")
        group by p.P_ID, hv.ANNEE, hv.SEMAINE
        order by p.P_NOM, p.P_PRENOM, hv.ANNEE desc, hv.SEMAINE desc";
    $result=mysqli_query($dbc,$query);
    //write_debugbox($query);
    $num=mysqli_num_rows($result);
    if ( $num == 0 ) 
         $out .= "<span class=small>Pas d'horaires à valider</span>";
    else {
        $out .=   "<table class='noBorder'>";
        while ($row = mysqli_fetch_array($result)) {
            $P_ID=$row["P_ID"];
            $nom=strtoupper($row["P_NOM"])." ".my_ucfirst($row["P_PRENOM"]);
            $section=$row["s_code"];
            $annee=$row["ANNEE"];
            $semaine=$row["SEMAINE"];
            $duree=$row["DUREE"];
            $a=" <a href='horaires.php?view=week&year=".$annee."&week=".$semaine."&person=".$P_ID."' title=\"Horaires de travail à valider\" >";
            $out .= "<tr>
                    <td align=left width=200 title='Voir tous les horaires de cette personne'><a href=horaires.php?from=accueil&person=".$P_ID."&view=list>".$nom."</a></td>
                    <td width=50>".$a.$annee."</a></td>
                    <td width=100>".$a." semaine ".$semaine."</a></td>
                    <td width=60 title='durée'>".$a.$duree." h</a></td>
                    <td width=60 title='durée'>".$a."<span class='orange12'>à valider</span></a></td>
                    </tr>";
        }
        $out .= "</table>";
    }
    return $out;
}    

//=====================================================================
// function remplacements evenements
//=====================================================================

function show_alerts_remplacements() {
    global $dbc, $bell, $nbsections, $gardes, $nbmaxlevels, $sdis;
    $out="";
    $id=intval(@$_SESSION['id']);
    if ( $nbsections > 0 ) $sid=0;
    else if ( $gardes == 1 and check_rights($id, 24) and check_rights($id,6) ) $sid=0;
    else {
        $sid=intval(@$_SESSION['SES_SECTION']);
        if ( $sdis == 1 and get_level("$sid") ==  $nbmaxlevels - 1 ) $sid = get_section_parent("$sid");
    }
    // des remplacements de gardes à approuver?
    if ( $gardes == 1 and check_rights($id, 6) ) {
        $query="select date_format(min(eh.EH_DATE_DEBUT),'%d-%m-%Y') DEBUT, date_format(max(eh.EH_DATE_FIN),'%d-%m-%Y') FIN, count(1) as NB
        from remplacement r, evenement_horaire eh, evenement e
        where eh.E_CODE = r.E_CODE 
        and eh.EH_ID=1
        and e.E_CODE = eh.E_CODE
        and e.TE_CODE = 'GAR'
        and eh.EH_DATE_FIN >= NOW()
        and r.APPROVED = 0 and r.REJECTED = 0";
        if ( $sid > 0 )
            $query .=" and e.S_ID in (".get_family("$sid").")";
        $txt="à approuver";
        $status="ATT";
    }
    else if ( $gardes == 1 and check_rights($id, 61) ){
        $query="select date_format(min(eh.EH_DATE_DEBUT),'%d-%m-%Y') DEBUT, date_format(max(eh.EH_DATE_FIN),'%d-%m-%Y') FIN, count(1) as NB
        from remplacement r, evenement_horaire eh, evenement e
        where eh.E_CODE = r.E_CODE 
        and eh.EH_ID=1
        and e.E_CODE = eh.E_CODE
        and e.TE_CODE = 'GAR'
        and r.APPROVED = 0 and r.REJECTED = 0 and r.ACCEPTED = 0
        and eh.EH_DATE_FIN >= NOW()
        and r.SUBSTITUTE = ".$id; 
        $txt="à accepter";
        $status="DEM";
    }
    else if ( check_rights($id, 15) ) {
        $query="select date_format(min(eh.EH_DATE_DEBUT),'%d-%m-%Y') DEBUT, date_format(max(eh.EH_DATE_FIN),'%d-%m-%Y') FIN, count(1) as NB
        from remplacement r, evenement_horaire eh, evenement e
        where eh.E_CODE = r.E_CODE 
        and eh.EH_ID=1
        and e.E_CODE = eh.E_CODE
        and eh.EH_DATE_FIN >= NOW()
        and r.APPROVED = 0 and r.REJECTED = 0";
        if ( $sid > 0 )
            $query .=" and e.S_ID in (".get_family("$sid").")";
        $txt="à approuver";
        $status="ATT";
    }
    else {
        $query="select date_format(min(eh.EH_DATE_DEBUT),'%d-%m-%Y') DEBUT, date_format(max(eh.EH_DATE_FIN),'%d-%m-%Y') FIN, count(1) as NB
        from remplacement r, evenement_horaire eh, evenement e
        where eh.E_CODE = r.E_CODE 
        and eh.EH_ID=1
        and e.E_CODE = eh.E_CODE
        and r.APPROVED = 0 and r.REJECTED = 0 and r.ACCEPTED = 0
        and eh.EH_DATE_FIN >= NOW()
        and r.SUBSTITUTE = ".$id; 
        $txt="à accepter";
        $status="DEM";
    }
    if ( $query <> "" ) {
        $result = mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $nb=intval($row["NB"]);
        if (  $nb > 0 ) {
            if ( $nb > 1 ) $s = 's';
            else $s='';
            $min=$row["DEBUT"];
            $max=$row["FIN"];
            $url="remplacements.php?filter=".$sid."&dtdb=".$min."&dtfn=".$max."&status=".$status;
            if ( $status == 'DEM' ) $url .="&substitute=".$id;
            $a=" <a href='".$url."' title=\"Vous avez $nb remplacement".$s." ".$txt."\" >";
            $out = "<table class=noBorder><tr><td>".$a.$bell."</a></td>";
            $out .= "<td>".$a."Vous avez <span class='badge orange-badge'>".$nb."</span> remplacement".$s." ".$txt."</a></td></tr></table>";
        }
    }
    if ( $out == "" ) $out = "<span class=small>Aucune demande de remplacement $txt</span>";
    return $out;
}

//=====================================================================
// function remplacements evenements
//=====================================================================

function show_proposed_remplacements() {
    global $dbc, $bell, $nbsections, $gardes, $nbmaxlevels, $sdis;
    $out="";
    $id=intval(@$_SESSION['id']);
    if ( $nbsections > 0 ) $sid=0;
    else {
        $sid=intval(@$_SESSION['SES_SECTION']);
        if ( $sdis == 1 and get_level("$sid") ==  $nbmaxlevels - 1 ) $sid = get_section_parent("$sid");
    }
    // des recherches de remplaçants
    $query="select date_format(min(eh.EH_DATE_DEBUT),'%d-%m-%Y') DEBUT, date_format(max(eh.EH_DATE_FIN),'%d-%m-%Y') FIN, count(1) as NB
    from remplacement r, evenement_horaire eh, evenement e
    where eh.E_CODE = r.E_CODE 
    and eh.EH_ID=1
    and e.E_CODE = eh.E_CODE
    and r.APPROVED = 0 and r.REJECTED = 0
    and r.SUBSTITUTE = 0
    and eh.EH_DATE_FIN >= NOW()";
    if ( $sid > 0 )
        $query .=" and e.S_ID in (".get_family("$sid").")";
    $result = mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row["NB"]);
    if (  $nb > 0 ) {
        if ( $nb > 1 ) $s = 's';
        else $s='';
        $min=$row["DEBUT"];
        $max=$row["FIN"];
        $flag_icon="<span class='fa-stack fa-lg'><i class='fa fa-circle fa-stack-2x'></i><i class='fa fa-flag fa-stack-1x fa-inverse'></i></span>";
        $url="remplacements.php?filter=".$sid."&dtdb=".$min."&dtfn=".$max."&status=DEM";
        $a=" <a href='".$url."' title=\"Il y a $nb recherche".$s." de remplaçant en cours.\" >";
        $out = "<table class=noBorder><tr><td>".$a.$flag_icon."</a></td>";
        $out .= "<td>".$a."Il y a <span class='badge yellow-badge'>".$nb."</span> recherche".$s." de remplaçant".$s." en cours.</a></td></tr></table>";
    }
    if ( $out == "" ) $out = "<span class=small>Aucun recherche de remplaçant.</span>";
    return $out;
}

//=====================================================================
// function welcome
//=====================================================================

function welcome() {
    global $nbsections,$dbc,$trombidir,$syndicate;
    $id=intval(@$_SESSION['id']);
    $section=intval(@$_SESSION['SES_SECTION']);
    $statut=@$_SESSION['SES_STATUT'];

    // affichage pompier
    if (date('n') == 12 and date('j') > 15 ) {
        $img='xmas.png';
        $msg='Joyeux Noël';
    }
    else if (date('n') == 1 and date('j') < 20 ) {
        $img='happy-new-year.png';
        $msg='Bonne année '.date('Y').',';
    }
    else {
        $img='keditbookmarks.png';
        $msg='Bonjour';
    }

    // affichage association
    $query="select P_PHOTO,P_SEXE from pompier where P_ID=".$id;
    $result=mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $link="<a href=upd_personnel_photo.php?pompier=$id >";
    if ( $row["P_PHOTO"] == '' ) {
        $width='50';
        if ( $row["P_SEXE"] == 'M') $img2='images/boy.png';
        else $img2='images/girl.png';
        $txt="Veuillez enregistrer votre photo <br>en cliquant sur ".$link."ce lien</a>";
    }
    else if (! file_exists($trombidir."/".$row["P_PHOTO"])) {
        $width='50';
        if ( $row["P_SEXE"] == 'M') $img2='images/boy.png';
        else $img2='images/girl.png';
        $txt="Photo enregistrée mais non trouvée sur le serveur";
    }
    else {
        $width='100';
        $img2=$trombidir.'/'.$row["P_PHOTO"];
        $filedate = date("Y-m-d",filemtime($img2));
        if ( $filedate == date("Y-m-d")) $img2 .="?timestamp=".time();
        $txt="Vous pouvez modifier votre photo <br>en cliquant sur ".$link."ce lien</a>";
    }

    // accueil, photo
    $out= "<table class='noBorder'><tr>";
    $out .=  "<td colspan=2><b>".$msg." <a href=upd_personnel.php?pompier=$id&tab=1 >".ucfirst(get_prenom($id))." ".strtoupper(get_nom($id))."</a></b></td></tr>";

    $out .=  "<tr><td valign='middle'> <img src=".$img2." class='rounded' width='".$width."'></td><td> ".$txt."</td>";
    $out .=  "</tr></table>";
    
    if ( $syndicate == 0 and $statut <> 'EXT') {
        $query= "select P_PHONE, P_EMAIL, P_PRENOM2, P_ADDRESS, P_CITY, P_ZIP_CODE, P_BIRTHDATE, P_BIRTHPLACE,
                        P_BIRTH_DEP, P_RELATION_PRENOM, P_RELATION_NOM, P_RELATION_PHONE, P_PAYS
                 from pompier where P_ID=".$id;
        $result=mysqli_query($dbc,$query);
        $row = mysqli_fetch_array($result);
        $out .=  "<table class='noBorder'>";
        $out .=missing_field($row,"P_PRENOM2", "Deuxième prénom");
        $out .=missing_field($row,"P_PHONE", "Téléphone");
        $out .=missing_field($row,"P_EMAIL", "Adresse mail");
        $out .=missing_field($row,"P_ADDRESS", "Adresse");
        $out .=missing_field($row,"P_CITY", "Ville");
        $out .=missing_field($row,"P_ZIP_CODE", "Code postal");
        $out .=missing_field($row,"P_BIRTHDATE", "Date de naissance");
        $out .=missing_field($row,"P_BIRTHPLACE", "Lieu de naissance");
        $out .=missing_field($row,"P_BIRTH_DEP", "Département de naissance");
        if ( $syndicate == 0 ) {
            $custom=count_entities("custom_field", "CF_TITLE='Tél Père'");
            if ( $custom == 0 or $statut <> 'JSP') {
                $out .=missing_field($row,"P_RELATION_NOM", "Nom de la personne à prévenir en cas d'urgence");
                $out .=missing_field($row,"P_RELATION_PRENOM", "Prénom de la personne à prévenir en cas d'urgence");
                $out .=missing_field($row,"P_RELATION_PHONE", "Téléphone de la personne à prévenir en cas d'urgence");
            }
        }
        $out .= missing_field($row,"P_PAYS", "Nationalité");
            $out .=  "</table>";
    }
    return $out;
}
//=====================================================================
// function duty
//=====================================================================

function show_duty() {
    global $dbc,$trombidir ;
    $id=intval(@$_SESSION['id']);
    $section=intval(@$_SESSION['SES_SECTION']);
    $out="";
   
    if ( check_rights($id,40)) $_40=true;
    else $_40=false;
    if ( check_rights($id,44)) $_44=true;
    else $_44=false;
    $query= "select p.P_PRENOM, p.P_NOM, p.P_CODE, p.P_PHOTO, p.P_SEXE, p.P_ID, s.S_ID, s.S_DESCRIPTION, ".phone_display_mask('se.S_PHONE2')." as S_PHONE2,
            ".phone_display_mask('p.P_PHONE')." as P_PHONE
            from pompier p, section_flat s, section_role sr, section se
            where p.P_ID = sr.P_ID
            and se.S_ID = s.S_ID
            and s.S_ID = sr.S_ID
            and sr.GP_ID=107
            and s.S_ID in (".get_family_up("$section").")
            order by s.NIV asc";
    $result=mysqli_query($dbc,$query);
    $num=mysqli_num_rows($result);
    if ( $num > 0 ) {
        $out .=   "<table class='noBorder'>";
        while ($row = mysqli_fetch_array($result)) {
            $P_ID=$row["P_ID"];
            $S_ID=$row["S_ID"];
            $S_DESCRIPTION=$row["S_DESCRIPTION"];
            $P_PRENOM=$row["P_PRENOM"];
            $P_NOM=$row["P_NOM"];
            $S_PHONE2=$row["S_PHONE2"];
            if ( intval($S_PHONE2) > 0 ) $phone="N° Veille Opérationnelle <br>".$S_PHONE2." ";
            else if ( intval($row["P_PHONE"]) > 0 ) $phone=$row["P_PHONE"];
            else $phone="";
            if ( $row["P_SEXE"] == 'M') $img2='images/boy.png';
            else $img2='images/girl.png';
            $class='img-max-50';
            $P_PHOTO=$row["P_PHOTO"];
            if ( $P_PHOTO <> '' and file_exists($trombidir."/".$row["P_PHOTO"])) {
                $img2=$trombidir.'/'.$row["P_PHOTO"];
                $class = "rounded";
            }
            $width='50';
            
            $out .= "<tr><td width=60>";
            $out .= "<a href=upd_personnel.php?pompier=$P_ID ><img src=".$img2." class=$class width=$width></a>
            </td>
            <td width=150>";
            if ( $_40 ) $out .= "<a href=upd_personnel.php?pompier=$P_ID >";
            $out .= my_ucfirst($P_PRENOM)." ".strtoupper($P_NOM)."</a><br><span class=small> ".$phone."</span></td>
            <td>pour ";
            if ( $_44 ) $out .= "<a href=upd_section.php?S_ID=$S_ID >";
            $out .= $S_DESCRIPTION."</a>";
            $date1=date('d-m-Y');
            $date0 = date('d-m-Y', strtotime($date1 . ' -1 day'));
            if ( $_44 ) {
                $out .= " <a href=pdf_bulletin.php?date=".$date0."&section=".$S_ID." target=_blank
                title=\"Afficher le bulletin de renseignements quotidien du ".$date0."\"> <i class='far fa-file-pdf fa-lg' style='color:red;'></i></a>";
                $out .= " <a href=pdf_bulletin.php?date=".$date1."&section=".$S_ID." target=_blank
                title=\"Afficher le bulletin de renseignements quotidien du ".$date1."\"> <i class='far fa-file-pdf fa-lg' style='color:red;'></i></a>";
            }
            $out .= "</td></tr>";
        }
        $out .= "</table>";    
    }

    return $out;
}

//=====================================================================
// fonctions birthdays
//=====================================================================

function show_birthdays() {
    global $N;
    $id=intval(@$_SESSION['id']);
    $out="";
    if ( check_rights($id,40)) {
        $today = mktime(0,0,0,date('m'), date('d'), date('Y'));
        $tomorrow = mktime(0,0,0,date('m'), date('d')+1, date('Y'));
        $dayafter = mktime(0,0,0,date('m'), date('d')+2, date('Y'));
         
        $d1=date("d", $today)." ".date_fran_mois(date("m", $today));
        $d2=date("d", $tomorrow)." ".date_fran_mois(date("m", $tomorrow));
        $d3=date("d", $dayafter)." ".date_fran_mois(date("m", $dayafter));
         
        $N=0;
        $names1=bday($today);
        $names2=bday($tomorrow);
        $names3=bday($dayafter);
         
        if ( $N > 0 ) {
            $out .= "<table class='noBorder'>
                <tr>
                <td rowspan=3 width=80 align=center><i class='fa fa-birthday-cake fa-3x' style='color:#ff0066; padding-left:4px;'></td>";
            $out .= "<td width=100>".($names1==""?"":"Aujourd'hui <br><small>".$d1."</small>")."</td>
                <td>".$names1."</td>
                </tr>"; 
            $out .= "<tr>
                <td width=100>".($names2==""?"":"Demain <br><small>".$d2."</small>")."</td>
                <td>".$names2."</td>
                </tr>";
            $out .= "<tr>
                <td width=100>".($names3==""?"":"Après demain <br><small>".$d3."</small>")."</td>
                <td>".$names3."</td>
                </tr>";
            $out .= "</table>";
        }
        else
            $out .= "<i class='fa fa-birthday-cake fa-lg' style='color:#ff0066; padding-left:4px;'></i> <span class=small style='padding-left:4px;'> Aucun anniversaire à souhaiter</span>";
    }
    return $out;
}

//=====================================================================
// fonctions about
//=====================================================================
function show_about() {
    global $application_title, $version, $wikiurl, $patch_version;
    if ( $patch_version <> '' ) $version = $patch_version;
    $out = "<table class='noBorder'>";
    $out .= "<tr><td><i class='fa fa-info-circle fa-lg' style='padding-left:4px;'></i> </td><td> Vous utilisez <a href=about.php>".$application_title."</a> version<b> ".$version."</b></td></tr>";
    $out .= "<tr><td><i class='fa fa-book fa-lg' style='padding-left:4px;'></i> </td><td> <a href='".$wikiurl."' target='_blank'>Voir la documentation en ligne</a> </td></tr>";
    $out .= "</table>";
    return $out;
}
//=====================================================================
// function infos en cours
//=====================================================================

function show_infos() {
    global $dbc, $gardes, $nbsections;
    $out="";
    $out1="";
    $id=intval(@$_SESSION['id']);
    $section=intval(@$_SESSION['SES_SECTION']);
    if ( $gardes == 1 ) {
        // affichage des consignes, sans possibilité de les supprimer
        $query="SELECT m.M_DUREE,
                DATE_FORMAT(m.M_DATE, '%m%d%Y%T') as FORMDATE2, DATE_FORMAT(m.M_DATE,'%d-%m-%Y') as FORMDATE3,
                m.M_TEXTE, m.M_OBJET, m.M_FILE, m.M_ID, tm.TM_ID, tm.TM_LIBELLE, tm.TM_COLOR, tm.TM_ICON, m.S_ID
                FROM message m, type_message tm
                where m.M_TYPE='consigne'
                and m.TM_ID=tm.TM_ID";
        if ( $nbsections == 0 )
            $query .= " and S_ID in (".get_family_up("$section").")";
        $query .= " and (datediff('".date("Y-m-d")."', m.M_DATE ) <= M_DUREE or M_DUREE = 0 )"; 
        $query .= "    order by M_DATE desc";
        $result=mysqli_query($dbc,$query);

        $out .= "<b><a href='message.php?catmessage=consigne' title='Voir les consignes en cours'>Consignes opérationnelles</a></b>";
        $out .= "<table class='noBorder' width=95% style='table-layout:fixed;'>";
        $num=mysqli_num_rows($result);
        if ( $num > 0 ) {
            while ($row = mysqli_fetch_array($result) ) {
                $out .= "<tr><td style='word-wrap:break-word;'><i class='fa fa-".$row["TM_ICON"]." fa-lg' style='color:".$row["TM_COLOR"].";' title=\"message ".$row["TM_LIBELLE"]."\"></i> ";
                $out .= " <span style='color:".$row["TM_COLOR"].";word-wrap:break-word;'><b>".$row["M_OBJET"]." </b><br>".force_blank_target($row["M_TEXTE"])."</span>";
                if ( $row["M_FILE"] <> "") 
                    $out .= " <i><a href=\"showfile.php?section=".$row["S_ID"]."&evenement=0&message=".$row["M_ID"]."&file=".$row["M_FILE"]."\"</a>".$row["M_FILE"]."</i>";
                $out .= "</td></tr>";
            }
        }
        else {
            $out .= "<p><span class=small>Pas de consignes en cours</span>";
        }
        $out .= "</table><p>";
        $out1 = "<b><a href='message.php?catmessage=amicale' title='Voir les informations en cours'>Informations diverses</a></b>";

    }

    if ( check_rights($id,44)) {
        // affichage des infos diverses, sans possibilité de les supprimer
        $query="SELECT m.M_DUREE,
            DATE_FORMAT(m.M_DATE, '%m%d%Y%T') as FORMDATE2, DATE_FORMAT(m.M_DATE,'%d-%m-%Y') as FORMDATE3,
            m.M_TEXTE, m.M_OBJET, m.M_FILE, m.M_ID, tm.TM_ID, tm.TM_LIBELLE, tm.TM_COLOR, tm.TM_ICON, m.S_ID
            FROM message m, type_message tm
            where m.M_TYPE='amicale'
            and m.TM_ID=tm.TM_ID";
        if ( $nbsections == 0 )  {
            if ( $section == 0 ) $query .= " and S_ID = 0 ";
            else $query .= " and S_ID in (".get_family_up($section).")";
        }
        $query .= " and (datediff('".date("Y-m-d")."', m.M_DATE ) <= M_DUREE or M_DUREE = 0 )"; 
        $query .= "    order by m.M_DATE desc";
        $result=mysqli_query($dbc,$query);
        $num=mysqli_num_rows($result);
        if ( $num > 0 ) {
            while ($row = mysqli_fetch_array($result) ) {
                $out1 .= "<tr><td style='color:".$row["TM_COLOR"].";word-wrap:break-word;'><i class='fa fa-".$row["TM_ICON"]." fa-lg' style='color:".$row["TM_COLOR"].";' title=\"message ".$row["TM_LIBELLE"]."\"></i> ";
                $out1 .= "<b>".$row["M_OBJET"]."</b><br>".force_blank_target($row["M_TEXTE"]);
                if ( $row["M_FILE"] <> "") 
                    $out1 .= " <i> fichier joint - 
                        <a href=showfile.php?section=".$row["S_ID"]."&evenement=0&message=".$row["M_ID"]."&file=".$row["M_FILE"].">"
                        .$row["M_FILE"]."</a></i>";
                $out1 .= "</font></td></tr>";
            }
            if ( $out1 <> "" ) 
                $out .= "<table width=95% class='noBorder' cellpadding=10 style='table-layout:fixed;'>".$out1."</table>";
        }
    }
    else {
        // affichage générique pour les externes qui n'ont pas le droit de voir les infos
        $out .= "<table width=95% class='noBorder' cellpadding=10 style='table-layout:fixed;'>";
        $out .= "<tr><td style='word-wrap:break-word;'>
            Vous pouvez visualiser votre calendrier en cliquant sur <b>'Calendrier'</b> dans le menu,
            ou voir vos informations personnelles, y compris les formations suivies en cliquant sur <b>'Mes infos'
            </b>.
         </td></tr>";
        $out .= "</table>";
    }
    return $out;
}

//=====================================================================
// function events
//=====================================================================

function show_events() {
    global $dbc;
    global $nbsections, $gardes;
    $id=intval(@$_SESSION['id']);
    $section=intval(@$_SESSION['SES_SECTION']);
    $out="";
    // affichage des événements en cours
    $query="select E.E_CODE, EH.EH_ID, E.TE_CODE, TE.TE_ICON, TE.TE_LIBELLE, 
        E.E_LIEU, EH.EH_DEBUT, EH.EH_FIN, E.E_NB, E.E_LIBELLE, E.E_CODE,
        DATE_FORMAT(EH.EH_DATE_DEBUT,'%d-%m-%Y') as FORMDATE1, 
        E.S_ID, S.S_DESCRIPTION, E.E_CLOSED,E.E_CANCELED
        from evenement E, type_evenement TE, section S, evenement_horaire EH
        where E.TE_CODE=TE.TE_CODE
        and E.E_CODE = EH.E_CODE
        and E.S_ID = S.S_ID
        and E.E_CANCELED=0
        and E.E_VISIBLE_INSIDE=1";

    $query .=" and TE.TE_CODE <> 'MC'";
    if ( $gardes ) $query .=" and TE.TE_CODE <> 'GAR'";
    if ( $_SESSION['SES_STATUT'] == 'EXT' )
        $query .= " and E.S_ID in (".get_family("$section").")";
    else if ( ! check_rights($id,40))
        $query .= " and E.S_ID in (".get_family("$section").",".get_section_parent("$section").")";
    else 
        $query .= " and E.S_ID in (".get_family_up("$section").")";
    $query .= " and EH.EH_DATE_FIN >= CURDATE()";
            
    $query .= " order by EH.EH_DATE_DEBUT limit 0,20";
    $result=mysqli_query($dbc,$query);

    while ($row = mysqli_fetch_array($result) ) {
        $E_CODE=$row["E_CODE"];
        $EH_ID=$row["EH_ID"];
        $TE_CODE=$row["TE_CODE"];
        $TE_ICON=$row["TE_ICON"];
        $TE_LIBELLE=$row["TE_LIBELLE"];
        $E_LIBELLE=$row["E_LIBELLE"];
        $E_LIEU=$row["E_LIEU"];
        $E_CODE=$row["E_CODE"];
        $E_CLOSED=$row["E_CLOSED"];
        $E_CANCELED=$row["E_CANCELED"];
        $S_ID=$row["S_ID"];
        $E_NB=$row["E_NB"];
        $EH_DEBUT=$row["EH_DEBUT"];
        $FORMDATE1=$row["FORMDATE1"];
        $EH_FIN=$row["EH_FIN"];
        $E_NB=$row["E_NB"];
        $S_DESCRIPTION=$row["S_DESCRIPTION"];

        if ( $E_CANCELED == 1 ) $myimg="<i class='fa fa-circle' style='color:red;' title='événement annulé'></i>";
        elseif ( $E_CLOSED == 1 ) $myimg="<i class='fa fa-circle' style='color:orange;'  title='inscriptions fermées'></i>";
        else $myimg="<i class='fa fa-circle' style='color:green;' title='inscriptions ouvertes'></i>";
          
        if ( $EH_ID > 1 ) $sess=' session n°'.$EH_ID;
        else $sess='';
          
        $out .= "<tr><td width=30><img src=images/evenements/".$TE_ICON." class='img-max-22'> </td>
                   <td><b>
                   <a href=evenement_display.php?evenement=$E_CODE&from=scroller >".$FORMDATE1." : ".$E_LIBELLE.$sess;
        $out .= " <i>(".$S_DESCRIPTION.") </i>";
        $out .= "</a></b>".$myimg."<br>";
        $out .= $TE_LIBELLE." - lieu: ".$E_LIEU;
           
        $out .= "</td></tr>";
    }
    if ( $out == "" ) $out = "<span class=small>Pas d'événements prévus</span>";
    $out = "<table width=95% class='noBorder' >".$out."</table>"; 
    return $out;
}

//=====================================================================
// function factures en cours
//=====================================================================

function show_factures() {
    global $dbc;
    global $nbsections, $gardes, $default_money_symbol;
    $id=intval(@$_SESSION['id']);
    $out = "";
    $section=intval(@$_SESSION['SES_SECTION']);
    //if ( $section > 0 ) $list = get_family($section);
    //else $list = 0;
    $list = $section;
    $query = "select  
    e.te_code,
    e.e_code,
    e.e_libelle,
    date_format(eh.eh_date_debut,'%d-%m-%Y')  eh_date_debut,
    ef.facture_montant,
    ef.devis_montant,
    facture_date,
    relance_date,
    DATEDIFF('".date("Y-m-d")."', date_format(ef.facture_date,'%Y-%m-%d')) as facture_depuis,
    DATEDIFF('".date("Y-m-d")."', date_format(ef.relance_date,'%Y-%m-%d')) as relance_depuis,
    DATEDIFF('".date("Y-m-d")."', date_format(eh.eh_date_fin,'%Y-%m-%d')) as termine_depuis
    from evenement e, evenement_facturation ef, evenement_horaire eh
    where e.e_code = ef.e_id ";
    $query .= (isset($list)?"  AND e.s_id in(".$list.") ":"");
    $query .=" AND e.e_canceled = 0
        AND ef.paiement_date is null    
        AND eh.eh_date_fin <= now()
        AND eh.e_code = e.e_code
        AND ( ef.devis_montant is not null or ef.facture_montant is not null)
        AND ( ef.devis_montant  > 0 or ef.facture_montant > 0 ) 
        AND eh.eh_id = 1 and e.te_code <> 'MC'
        AND date_format(eh.eh_date_fin,'%Y-%m-%d') < '".date("Y-m-d")."'
        AND DATEDIFF('".date("Y-m-d")."', date_format(eh.eh_date_fin,'%Y-%m-%d')) < 100
        order by eh.eh_date_debut desc, e.te_code";

    $res=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($res);
    if ($number > 0 ) {
        $out .= "<table class='noBorder' width=95%>";
        while($row=mysqli_fetch_array($res)){
            $te_code=$row['te_code'];
            $e_code=$row['e_code'];
            $e_libelle=$row['e_libelle'];
            $eh_date_debut=$row['eh_date_debut'];
            $montant=$row['facture_montant'];
            $facture_date=$row['facture_date'];
            $relance_date=$row['relance_date'];
            $facture_depuis=$row['facture_depuis'];
            $relance_depuis=$row['relance_depuis'];
            $termine_depuis=$row['termine_depuis'];
            if ( $relance_date <> '' ) $c = "<span style='color:red;'>relance depuis ".$relance_depuis." j</span>";
            else if ( $facture_date <> '' ) $c = "<span style='color:#e65c00;'>facturé depuis ".$facture_depuis." j</span>";
            else $c = "<span style='color:grey;'>à facturer depuis ".$termine_depuis." j</span>";
            if ( intval($montant) == 0 ) $montant = $row['devis_montant'];
            $out .="<tr>
                    <td width=240><a href='evenement_facturation.php?evenement=".$e_code."' title='Voir cet événement ".$te_code."'>".$e_libelle."</a> <small>".$c."</small></td>
                    <td width=70>".$eh_date_debut."</td>
                    <td width=50>".$montant." ".$default_money_symbol."</td>
                    </tr>";   
        }
        $out .= "</table>";
    }
    return $out;
}

//=====================================================================
// statistiques manquantes
//=====================================================================

function show_stats_manquantes() {
    global $dbc;
    global $nbsections, $default_money_symbol;
    $id=intval(@$_SESSION['id']);
    $out = "";
    $section=intval(@$_SESSION['SES_SECTION']);
    $list = $section;
    if ( $nbsections > 0 ) $list=0;
    $query = "select
    e.e_lieu,
    e.te_code,
    e.e_code,
    e.e_libelle,
    te.te_libelle,
    date_format(eh.eh_date_debut,'%d-%m-%Y')  eh_date_debut,
    date_format(eh.eh_date_fin,'%d-%m-%Y')  eh_date_fin,
    DATEDIFF('".date("Y-m-d")."', date_format(eh.eh_date_fin,'%Y-%m-%d')) as termine_depuis
    from evenement e, type_evenement te, evenement_horaire eh
    where e.te_code = te.te_code ";
    $query .= (isset($list)?" AND e.s_id in(".$list.") ":"");
    $query .=" AND e.e_canceled = 0
        AND eh.eh_date_fin <= now()
        AND eh.e_code = e.e_code
        AND eh.eh_id = 1 and e.te_code <> 'MC' and te.TE_MAIN_COURANTE=1
        AND date_format(eh.eh_date_fin,'%Y-%m-%d') < '".date("Y-m-d")."'
        AND DATEDIFF('".date("Y-m-d")."', date_format(eh.eh_date_fin,'%Y-%m-%d')) < 30
        AND exists (select 1 from type_bilan tb where tb.TE_CODE=e.TE_CODE)
        AND e.E_PARENT is null
        AND not exists (select 1 from bilan_evenement be where be.E_CODE=e.E_CODE)
        order by eh.eh_date_fin desc, e.te_code";

    $res=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($res);
    if ($number > 0 ) {
        $out .= "<table class='noBorder' width=95%>";
        while($row=mysqli_fetch_array($res)){
            $te_code=$row['te_code'];
            $e_code=$row['e_code'];
            $e_lieu=$row['e_lieu'];
            $e_libelle=$row['e_libelle'];
            $te_libelle=$row['te_libelle'];
            $eh_date_fin=$row['eh_date_fin'];
            $termine_depuis=$row['termine_depuis'];
            $c = "<span style='color:orange;' >terminé depuis ".$termine_depuis." j</span>";
            $out .="<tr>
                    <td width=300><a href='evenement_display.php?evenement=".$e_code."&tab=8' title=\"Renseigner les statistiques de cet événement de type ".$te_libelle."\">".$e_libelle."</a> <small>".$e_lieu." ".$c."</small></td>
                    <td width=60>".$eh_date_fin."</td>
                    </tr>";   
        }
        $out .= "</table>";
    }
    if ( $out == "" ) $out = "<span class=small>Pas de statistiques manquantes</span>";
    return $out;
}

//=====================================================================
// notes de frais à valider ou rembourser
//=====================================================================

function show_notes() {
    global $dbc, $nbsections, $default_money_symbol, $mylightcolor, $nbmaxlevels, $syndicate;
    $id=intval(@$_SESSION['id']);
    $limit_days=1000;
    $out="";
    $section_me=intval(@$_SESSION['SES_SECTION']);
    $section_parent=intval(@$_SESSION['SES_PARENT']);
    $section_perm=$section_me;
    $section_perm1 = get_highest_section_where_granted($id,73);
    $section_perm2 = get_highest_section_where_granted($id,74);
    $section_perm3 = get_highest_section_where_granted($id,75);
    if ( get_level("$section_perm1") < $nbmaxlevels - 1 ) {
        $section_perm = $section_perm1;
        $perm_dep = true;
    }
    else if ( get_level("$section_perm2") < $nbmaxlevels - 1 ) {
        $section_perm = $section_perm2;
        $perm_dep = true;
    }
    else if ( get_level("$section_perm3") < $nbmaxlevels - 1 ) {
        $section_perm = $section_perm3;
        $perm_dep = true;
    }
    else $perm_dep = false;
    if ( $section_perm == 0 and $nbsections == 0 ) $list='0';
    else $list=get_family("$section_perm");
    $query = "  select p.P_ID,n.NF_ID,NF_CREATE_DATE, n.FS_CODE,
                concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'beneficiaire',
                s.S_CODE 'section',
                n.TOTAL_AMOUNT 'montant',
                fs.FS_CLASS 'class',
                fs.FS_DESCRIPTION 'statut',
                date_format(n.NF_CREATE_DATE,'%d-%m-%Y') 'dc',
                '' as 'type',
                tm.TM_DESCRIPTION 'motif',
                n.NF_DEPARTEMENTAL, n.NF_NATIONAL, n.NF_VERIFIED
                from note_de_frais n, note_de_frais_type_statut fs, note_de_frais_type_motif tm, pompier p, section s
                where fs.FS_CODE=n.FS_CODE
                and p.P_ID = n.P_ID
                and p.P_SECTION = s.S_ID
                and tm.TM_CODE = n.TM_CODE
                and ( p.P_SECTION = ".$section_me." or n.S_ID = ".$section_me." )
                and fs.FS_CODE in ('ATTV','VAL','VAL1','VAL2')
                and datediff(NOW(), n.NF_CREATE_DATE) < $limit_days
                and n.NF_DEPARTEMENTAL = 0 and n.NF_NATIONAL  = 0";
    if ( $perm_dep ) {
        $query .= " union all
                select p.P_ID,n.NF_ID,NF_CREATE_DATE, n.FS_CODE,
                concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'beneficiaire',
                s.S_CODE 'section',
                n.TOTAL_AMOUNT 'montant',
                fs.FS_CLASS 'class',
                fs.FS_DESCRIPTION 'statut',
                date_format(n.NF_CREATE_DATE,'%d-%m-%Y') 'dc',
                'Départemental' as 'type',
                tm.TM_DESCRIPTION 'motif',
                n.NF_DEPARTEMENTAL, n.NF_NATIONAL, n.NF_VERIFIED
                from note_de_frais n, note_de_frais_type_statut fs, note_de_frais_type_motif tm, pompier p, section s
                where fs.FS_CODE=n.FS_CODE
                and p.P_ID = n.P_ID
                and p.P_SECTION = s.S_ID
                and tm.TM_CODE = n.TM_CODE
                and fs.FS_CODE in ('ATTV','VAL','VAL1','VAL2')
                and datediff(NOW(), n.NF_CREATE_DATE) < $limit_days
                and n.NF_DEPARTEMENTAL = 1";
        if ( $syndicate == 1 and $section_me > 1 )
            $query .= " and ( n.S_ID in (".$section_parent.",".$section_me.") or s.S_PARENT in (".$section_parent.",".$section_me."))";
        else if ( $syndicate == 0 or ! multi_check_rights_notes($id,'0') )
            $query .= " and ( p.P_SECTION in(".$list.") or n.S_ID in(".$list."))";
    }
    if ( $section_me == 0 or ($syndicate == 1 and multi_check_rights_notes($id,'0')) )
        $query .= " union all
                select p.P_ID,n.NF_ID,NF_CREATE_DATE, n.FS_CODE,
                concat(upper(p.p_nom),' ',CAP_FIRST(p.p_prenom)) 'beneficiaire',
                s.S_CODE 'section',
                n.TOTAL_AMOUNT 'montant',
                fs.FS_CLASS 'class',
                fs.FS_DESCRIPTION 'statut',
                date_format(n.NF_CREATE_DATE,'%d-%m-%Y') 'dc',
                'National' as 'type',
                tm.TM_DESCRIPTION 'motif',
                n.NF_DEPARTEMENTAL, n.NF_NATIONAL, n.NF_VERIFIED
                from note_de_frais n, note_de_frais_type_statut fs, note_de_frais_type_motif tm, pompier p, section s
                where fs.FS_CODE=n.FS_CODE
                and p.P_ID = n.P_ID
                and p.P_SECTION = s.S_ID
                and tm.TM_CODE = n.TM_CODE
                and fs.FS_CODE in ('ATTV','VAL','VAL1','VAL2')
                and datediff(NOW(), n.NF_CREATE_DATE) < $limit_days
                and n.NF_NATIONAL = 1";
    $query .= " order by NF_CREATE_DATE desc";

    write_debugbox($query);
    $res=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($res);

    if ( $number == 0 ) {
        $out = "<span class=small>Pas de notes de frais à traiter</span>";
    }
    else {
        $out .= "<table class='noBorder' width=95%>";
        while($row=mysqli_fetch_array($res)){
            $note=$row["NF_ID"];
            $pid=$row["P_ID"];
            $beneficiaire=$row["beneficiaire"];
            $section=$row["section"];
            $montant=$row["montant"];
            $statut=$row["statut"];
            $class=$row["class"];
            $fscode=$row["FS_CODE"];
            $type=$row["type"];
            $dc=$row["dc"];
            $departemental=$row["NF_DEPARTEMENTAL"];
            $national=$row["NF_NATIONAL"];
            $verified=$row["NF_VERIFIED"];
            $cmt="";
            if ( $departemental == 1 ) $cmt .= "<span title='Note de frais départementale'>D</span>";
            else if ( $national == 1 ) $cmt .= "<span title='Note de frais nationale'>N</span>";
            if ( $syndicate == 1 ) {
                if ( $fscode == 'VAL'  ) $statut = 'Validée trésorier';
                else if ( $fscode == 'VAL1' ) $statut = 'Validée président';
            }
            if ( $verified == 1 ) $v = " <i class='fas fa-check' title='vérifié par la comptabilité'></i>";
            else $v='';
            $a="<a href=note_frais_edit.php?from=accueil&action=update&person=".$pid."&nfid=".$note." title='Voir la note de frais'>";
            $b="<a href=upd_personnel.php?pompier=".$pid."&tab=9>";
            $out .= "<tr bgcolor=$mylightcolor>
                <td>".$b.$beneficiaire."</a></td>
                <td >".$a.$montant.$default_money_symbol.$v."</a></td>
                <td class=small2>".$cmt."</td>
                <td class=small2>".$dc."</td>
                <td>".$a."<span class=".$class.">".$statut."</span></a></td>
                </tr>";
        }
        $out .= "</table>";
    }
    return $out;
}
//=====================================================================
// function participations
//=====================================================================

function show_participations_mc() {
    return show_participations($type='MC');
}

function show_participations($type='default') {
    global $dbc;
    global $nbsections, $gardes;
    $id=intval(@$_SESSION['id']);
    $section=intval(@$_SESSION['SES_SECTION']);
    $out="";
    // les prochaines participations
    $query = write_query_participations($id, $kind='future', $order='asc', $type);
    $res=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($res);

    if ( $number == 0 ) {
        if ( $type == 'MC' ) $out .= "<span class=small>Pas de main courante visibles</span>";
        else  $out ="<span class=small>Pas de prochaines participations prévues</span>";
    }
    else {
        $out .= "<table class='noBorder' width=95%>";
        while($row=mysqli_fetch_array($res)){
            $te_libelle=$row['te_libelle'];
            $te_code=$row['te_code'];
            if ( $te_code == 'GAR' and $gardes == 1 ) $gardeSP = true;
            else $gardeSP = false;
            $e_code=$row['e_code'];
            if ( $e_code == 0 and $type == 'default') {
                //astreinte
                $datedeb=$row['datedeb'];
                $datefin=$row['datefin'];
                $out .= "<tr>";
                $out .= "<td align=left width=25><i class='fa fa-star fa-lg' style='color:yellow;' title=\"".$te_libelle."\"></i></td>";
                if ( $datedeb !=$datefin )  $out .= "<td width=135>".$datedeb." au ".$datefin."</td>";
                else  $out .= "<td align=left width=135>".$row['datedeb']." </td>";
                $out .= "<td width=60></td>";
                $out .= "<td align=left><a href=\"astreinte_edit.php?from=personnel&astreinte=".$row['eh_id']."\" >". $row['e_libelle']."</a></td>";
                $out .= "</tr>";
            }
            else {
                // evenement ou garde
                if ( $row['epdatedeb'] == "" ) {
                    $datedeb=$row['datedeb'];
                    $datefin=$row['datefin'];
                    $debut=$row['eh_debut'];
                    $fin=$row['eh_fin'];
                    $duree=$row['eh_duree'];
                }
                else {
                    $datedeb=$row['epdatedeb'];
                    $datefin=$row['epdatefin'];
                    $debut=$row['ep_debut'];
                    $fin=$row['ep_fin'];
                    $duree=$row['ep_duree'];
                }
                $eh_description=$row['eh_description'];
                if ( $eh_description <> '') $eh_description = " - ".$eh_description; 
             
                // commentaire sur l'inscription
                $cmt="";
                if ( $row['tp_id'] > 0 ) {
                    $cmt=get_fonction($row['tp_id'])."\n";
                }
                $cmt .= $row['ep_comment'];
             
                if ( $row['ep_flag1'] == 1 ) { 
                    $txtimg="sticky-note' style='color:purple;";
                    if ($nbsections > 0 ) $as = 'SPP';
                    else $as = 'salarié(e)';
                    $cmt="Participation en tant que ".$as." \n".$cmt;
                }
                else if ( $cmt  <> '' ) $txtimg="sticky-note";

                if ( $cmt <> '' ) $txtimg="<i class='fa fa-".$txtimg."' title=\"".$cmt."\" ></i>";
                else $txtimg="";
                
                $tmp=explode ( "-",$datedeb); $year1=$tmp[2]; $month1=$tmp[1]; $day1=$tmp[0];
                $datedeb=my_ucfirst(date_fran($month1, $day1 ,$year1))." ".moislettres($month1)." ".$year1;
                // affichage spécial pour les gardes
                if ( $gardeSP ) {
                    $datefin=$datedeb;
                    $libelle=$row['EQ_NOM']." ".$duree."h";
                    if ( $row['eh_id'] == 1 ) {
                        if ( intval($duree) < 24 ) $libelle.=" jour";
                    }
                    else $libelle.=" nuit";
                }
                else  {
                    $n=get_nb_sessions($e_code);
                    if ( $n > 1 ) $part=" partie ".$row['eh_id']."/".$n;
                    else $part="";
                    $libelle=$row['e_libelle']." ".$part." ".$eh_description; 
                }
             
                 $out .= "<tr>";
                if (  $row['e_visible_inside'] == 0 ) $libelle .= " <i class='fa fa-exclamation-triangle' style='color:orange;' title='ATTENTION événement caché, seules les personnes inscrites ou ayant la permission n°9 peuvent le voir'></i>";
                if ( $row['EQ_ICON'] == "" ) $img="images/evenements/".$row['te_icon'];
                else $img=$row['EQ_ICON'];
                $out .= "<td align=left width=25><img border=0 src=".$img." class='img-max-22' title=\"".$te_libelle."\"></td>";
                if ( $te_code == 'MC' ) $out .= "<td align=left width=135>Main courante</td>";
                else $out .= "<td align=left width=135>".$datedeb."</td>";
                if ( $te_code == 'MC' ) $out .="<td width=1></td>";
                else if ( $duree <= 24 ) $out .= "<td class=small align=left width=60>".$debut." à ".$fin."</td>";
                else $out .= "<td class=small width=60> à ".$debut."</td>";
                if ( $gardeSP) $out .= "<td align=left width=120> <a href=\"feuille_garde.php?evenement=".$e_code."\" >".$libelle."</a></td>";
                else {
                    if (  $te_code == 'MC' ) $tab = 8;
                    else $tab=2;
                    $out .= "<td align=left width=120> <a href=\"evenement_display.php?evenement=".$e_code."&tab=".$tab."&pid=".$id."\" >".$libelle."</a> ";
                    if (  $te_code <> 'MC' )  $out .= "<span class=small>durée ".$duree."h</span>";
                    $out .= "</td>";
                }
                $out .= "</tr>";
            }
        }
        $out .= "</table>";
    }
    return $out;
}


//=====================================================================
// function query participations
//=====================================================================

function write_query_participations($P_ID, $kind='all', $order='desc', $type='default') {
    // type = default ou MC (main courante)
    // kind = all ou future
    global $gardes;
    $id=intval(@$_SESSION['id']);
    $conditions="";
    $conditions2="";
    if ( $kind == 'future') {
        $conditions.=" and date_format(eh.eh_date_fin,'%Y-%m-%d') >= date_format(now(),'%Y-%m-%d')";
        $conditions2.=" and date_format(a.AS_FIN,'%Y-%m-%d') >= date_format(now(),'%Y-%m-%d')";
    }
    if ( (! check_rights($id,9) and $id <> $P_ID ) or $gardes == 1 ) 
        $conditions .= " and e.e_visible_inside = 1 "; 
    if ( $type == 'MC' ) 
        $conditions .= " and e.te_code = 'MC' ";
    else 
        $conditions .= " and e.te_code <> 'MC' ";
    
    $query = "
        select eh.eh_id, e.te_code, tg.EQ_NOM, tg.EQ_ICON, tg.EQ_ID, e.e_code, e_libelle, 
        date_format(eh.eh_date_debut,'%d-%m-%Y') 'datedeb', eh.eh_date_debut sortdate,
        date_format(eh.eh_debut, '%H:%i') eh_debut, 
        date_format(eh.eh_fin, '%H:%i') eh_fin,
        date_format(eh.eh_date_fin,'%d-%m-%Y') 'datefin',
        eh.eh_duree,
        e.e_lieu,
        eh.eh_description, 
        date_format(ep.ep_date_debut,'%d-%m-%Y') 'epdatedeb',
        date_format(ep.ep_debut, '%H:%i') ep_debut, date_format(ep.ep_fin, '%H:%i') ep_fin,
        date_format(ep.ep_date_fin,'%d-%m-%Y') 'epdatefin',
        ep.ep_flag1,
        ep.ep_comment,
        ep.ep_asa,
        ep.ep_das,
        ep.ep_km,
        ep.ep_absent,
        ep.ep_excuse,
        ep.tp_id,
        ep.ep_duree,
        e.e_visible_inside,
        case 
        when date_format(eh.eh_date_fin,'%Y-%m-%d') >= date_format(now(),'%Y-%m-%d') then 1
        else 0
        end
        as 'future',
        te.te_libelle as 'te_libelle',
        te.te_icon
        from evenement e left join type_garde tg on tg.EQ_ID = e.E_EQUIPE,
        evenement_participation ep, evenement_horaire eh, type_evenement te
        where e.e_code = ep.e_code
        and te.te_code=e.te_code
        and ep.eh_id = eh.eh_id
        and e.e_code = eh.e_code
        AND ep.p_id = '$P_ID'
        AND e.e_canceled = 0
        AND ep.EP_ABSENT = 0".$conditions;
  
    if ( $gardes == 1 and $type =='default' ) {
        $query.=" and e.te_code <> 'GAR'
        union all
        select min(eh.eh_id), e.te_code, tg.EQ_NOM, tg.EQ_ICON, tg.EQ_ID, e.e_code, e_libelle, 
        date_format(min(eh.eh_date_debut),'%d-%m-%Y') 'datedeb', eh.eh_date_debut sortdate,
        date_format(min(eh.eh_debut), '%H:%i') eh_debut, 
        date_format(min(eh.eh_fin), '%H:%i') eh_fin,
        date_format(max(eh.eh_date_fin),'%d-%m-%Y') 'datefin',
        sum(eh.eh_duree),
        e.e_lieu,
        eh.eh_description, 
        date_format(min(ep.ep_date_debut),'%d-%m-%Y') 'epdatedeb',
        date_format(min(ep.ep_debut), '%H:%i') ep_debut, date_format(ep.ep_fin, '%H:%i') ep_fin,
        date_format(max(ep.ep_date_fin),'%d-%m-%Y') 'epdatefin',
        ep.ep_flag1,
        ep.ep_comment,
        ep.ep_asa,
        ep.ep_das,
        ep.ep_km,
        ep.ep_absent,
        ep.ep_excuse,
        ep.tp_id,
        ep.ep_duree,
        e.e_visible_inside,
        case 
        when date_format(eh.eh_date_fin,'%Y-%m-%d') >= date_format(now(),'%Y-%m-%d') then 1
        else 0
        end
        as 'future',
        te.te_libelle as 'te_libelle',
        te.te_icon
        from evenement e left join type_garde tg on tg.EQ_ID = e.E_EQUIPE,
        evenement_participation ep, evenement_horaire eh, type_evenement te
        where e.e_code = ep.e_code
        and te.te_code=e.te_code
        and ep.eh_id = eh.eh_id
        and e.e_code = eh.e_code
        AND ep.p_id = '$P_ID'
        AND e.e_canceled = 0
        AND ep.EP_ABSENT = 0
        AND e.TE_CODE='GAR'".$conditions." group by e.e_code";
    }
    if ( $type =='default' )
    $query .= " union all
        select a.AS_ID eh_id, 'AST' te_code,null as EQ_NOM, null as EQ_ICON, 0 as EQ_ID,  0 e_code, concat(g.gp_description, ' ', s.s_code) e_libelle , 
        date_format(a.AS_DEBUT,'%d-%m-%Y') 'datedeb',
        a.AS_DEBUT sortdate,
        '' eh_debut, 
        '' eh_fin,
        date_format(a.AS_FIN,'%d-%m-%Y') 'datefin',
        0 eh_duree,
        '' e_lieu,
        '' eh_description,
        '' epdatedeb,
        '' ep_debut,
        '' ep_fin,
        '' epdatefin,
        0 ep_flag1,
        '' ep_comment,
        0 ep_asa,
        0 ep_das,
        '' ep_km,
        0 ep_absent,
        0 ep_excuse,
        0 tp_id,
        0 ep_duree,
        1 e_visible_inside,
        case 
        when date_format(a.AS_FIN,'%Y-%m-%d') >= date_format(now(),'%Y-%m-%d') then 1
        else 0
        end
        as 'future',
        'astreinte' as 'te_libelle',
        null as 'te_icon'
        from astreinte a, groupe g, section s
        where a.P_ID=".$P_ID."
        and a.GP_ID = g.GP_ID
        and s.S_ID = a.S_ID".$conditions2."
        order by sortdate ".$order.", eh_debut ".$order."
        ";
    return $query;
}

?>
