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
// map data
//=====================================================================

function get_map_data($mode,$param) {
    global $dbc, $syndicate, $assoc, $name;
    
    //MQ    Martinique
    //2B    Haute-Corse
    //2A    Corse-du-Sud
    //YT    Mayotte
    //GF    Guyane française
    //GP    Guadeloupe
    //WF    Wallis et Futuna
    //PF    Polynésie Francaise
    //SP    Saint Pierre et Miquelon
    
    $name = 'personne';
    $out="var Data = {";
    $curdate=date("Y-m-d");
    $subquery1="select P_ID, 
                case
                when substr(P_ZIP_CODE,1,3) = '971' then 'GP'
                when substr(P_ZIP_CODE,1,3) = '972' then 'MQ'
                when substr(P_ZIP_CODE,1,3) = '973' then 'GF'
                when substr(P_ZIP_CODE,1,3) = '974' then 'RE'
                when substr(P_ZIP_CODE,1,3) = '975' then 'SP'
                when substr(P_ZIP_CODE,1,3) = '976' then 'YT'
                when substr(P_ZIP_CODE,1,3) = '986' then 'WF'
                when substr(P_ZIP_CODE,1,3) = '987' then 'PF'
                when substr(P_ZIP_CODE,1,3) = '202' then '2B'
                when substr(P_ZIP_CODE,1,2) = '20' then '2A'
                else substr(P_ZIP_CODE,1,2) 
                end as ZIP 
                from pompier where P_OLD_MEMBER=0 and P_STATUT <> 'EXT'";
    $subquery2="select S_ID, 
                case
                when substr(S_ZIP_CODE,1,3) = '971' then 'GP'
                when substr(S_ZIP_CODE,1,3) = '972' then 'MQ'
                when substr(S_ZIP_CODE,1,3) = '973' then 'GF'
                when substr(S_ZIP_CODE,1,3) = '974' then 'RE'
                when substr(S_ZIP_CODE,1,3) = '975' then 'SP'
                when substr(S_ZIP_CODE,1,3) = '976' then 'YT'
                when substr(S_ZIP_CODE,1,3) = '986' then 'WF'
                when substr(S_ZIP_CODE,1,3) = '987' then 'PF'
                when substr(S_ZIP_CODE,1,3) = '202' then '2B'
                when substr(S_ZIP_CODE,1,2) = '20' then '2A'
                else substr(S_ZIP_CODE,1,2) 
                end as ZIP 
                from section";
    
    if ( in_array($mode, array(1,2,3)) ) {
        // opérations de secours
        if ( $mode == 1 ) $t='C_SEC';
        // autres opérations
        if ( $mode == 2 ) $t='C_OPE';
        // formations
        if ( $mode == 3 ) $t='C_FOR';
        $query = "select p.ZIP, count(1)
                from (".$subquery1." ) as p, evenement e, evenement_participation ep, type_evenement te, evenement_horaire eh
                where e.E_CODE = ep.E_CODE
                and ep.E_CODE = eh.E_CODE
                and ep.EH_ID = eh.EH_ID
                and e.E_CANCELED=0
                and e.TE_CODE = te.TE_CODE
                and te.CEV_CODE = '".$t."'
                and p.P_ID = ep.P_ID
                and eh.EH_DATE_DEBUT <= '".$curdate."' 
                and eh.EH_DATE_FIN   >= '".$curdate."'
                group by p.ZIP";
        $name = 'participant';
    }
    else if ( $mode == 5 ) { 
        // personnel disponible
        $query="select ZIP, count(1)
                from (".$subquery1." ) as p,
                (select distinct P_ID from disponibilite where d_date='".date('Y-m-d')."') as d
                where d.p_id = p.p_id
                group by ZIP";
        $name = 'personne';

    }
    else if ($mode == 4) {
        // cadres de veille
        $query="select ZIP, count(1)
           from (".$subquery1." ) as p, section_role sr, groupe g, section s
           where s.S_ID = sr.S_ID
           and sr.P_ID = p.P_ID
           and sr.GP_ID = g.GP_ID
           and g.GP_ID=107
           group by ZIP";
        $name = 'Cadres de veille opérationnelle';
    }
    else if ($mode == 9) {
        // véhicules
        $query="select ZIP, count(1)
        from  (".$subquery2." ) as s, vehicule v
        where v.S_ID = s.S_ID
        and v.VP_ID in (select VP_ID from vehicule_position where VP_OPERATIONNEL >= 0)
        group by ZIP";
        $name = "Véhicule";
    }
    else if ($mode == 8) {
        // matériel national
        $query="select ZIP, count(1)
        from  (".$subquery2." ) as s, materiel m  join type_materiel tm on tm.TM_ID = m.TM_ID
        where m.S_ID = s.S_ID
        and m.MA_EXTERNE = 1
        and m.VP_ID in (select VP_ID from vehicule_position where VP_OPERATIONNEL >= 0)
        group by ZIP";
        $name = "Matériel";
    }
    else if ($mode == 10 or $mode == 11) {
        // matériel de pompage
        if ( $mode == 10 ) $list = "'Motos Pompes','Vides Caves'";
        //hébergement
        if ( $mode == 11 ) $list = "'Lits Picots','Tentes','Couvertures'";
        $query="select ZIP, count(1)
        from  (".$subquery2." ) as s, materiel m  join type_materiel tm on tm.TM_ID = m.TM_ID
        where m.S_ID = s.S_ID
        and tm.TM_CODE in (".$list.")
        and m.VP_ID in (select VP_ID from vehicule_position where VP_OPERATIONNEL >= 0)
        group by ZIP";
        $name = "Matériel";
    }
    else if ($mode == 13) {
        // compétences
        $query =  "select ZIP, count(1)
           from (".$subquery1." ) as p, qualification q
           where q.P_ID=p.P_ID
           and ( q.Q_EXPIRATION is null or q.Q_EXPIRATION >= NOW())
           and q.PS_ID=".$param."
           group by ZIP";
        $name = "Compétence";
    }
    else if ($mode == 15) {
        // événements
        $query =  "select ZIP, count(1)
        from  (".$subquery2." ) as s, evenement e, evenement_horaire eh
        where e.S_ID = s.S_ID
        and e.E_CODE = eh.E_CODE
        and e.E_CANCELED = 0
        and eh.EH_DATE_DEBUT <= '".$curdate."' 
        and eh.EH_DATE_FIN   >= '".$curdate."'";
        if ( $param == 'ALL' ) $query .= " and e.TE_CODE <> 'MC'";
        else $query .= " and e.TE_CODE = '".$param."'";
        $query .= " group by ZIP";
        $name = "Evénement";
    }
    else { 
        // membres $mode == 6 or default
        $query="select ZIP, count(1)
            from (".$subquery1." ) as p
            group by ZIP";
        if ( $syndicate ) $name = 'adhérent';
        else if ( $assoc ) $name = 'bénévole';
    }

    $result=mysqli_query($dbc,$query);
    while ( $row=@mysqli_fetch_array($result)){
        $out .= "'FR-".$row[0]."': ".$row[1].",";
    }
    $out=rtrim($out,",")."}
    ";
    return $out;
}
?>
