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
$my_parent_section = get_section_parent($_SESSION['SES_SECTION']);
get_session_parameters();

$section=$filter;

$fixed_company = false;
if ( $_SESSION['SES_STATUT'] == 'EXT' ) {
    if (! check_rights($id, 41)) {
        check_all(45);
        $company=$_SESSION['SES_COMPANY'];
        $_SESSION['company'] = $company;
        $fixed_company = true;
    }
}
else check_all(41);

if ($company <= 0 ) check_all(41);

// Libell� �v�nement
$lib=((isset ($_GET["lib"]))?"%".$_GET["lib"]."%":"%");

date_default_timezone_set('Europe/Paris');

/** Include PHPExcel */
require_once 'lib/PHPExcel/Classes/PHPExcel.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("eBrigade ".$version)
                             ->setLastModifiedBy("eBrigade ".$version)
                             ->setTitle("Evenements")
                             ->setSubject("Evenements")
                             ->setDescription("Liste des evenements")
                             ->setKeywords("office 2007 openxml php")
                             ->setCategory("Evenements");

// Freeze panes
$objPHPExcel->getActiveSheet()->freezePane('A2');

// Rows to repeat at top
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

$stats=array();
$stats[1]="Stat 1";
$stats[2]="Stat 2";
$stats[3]="Stat 3";
$stats[4]="Stat 4";


if ( $type_evenement <> 'ALL' and $type_evenement <> '') {
    $query="select TB_NUM, TB_LIBELLE from type_bilan where TE_CODE='".$type_evenement."' order by TB_NUM";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        $stats[$row["TB_NUM"]]=$row["TB_LIBELLE"];
    }
}

// Add the columns heads
$columns=array('A','B','C','D','E',  'F','G','H','I','J','K','L','M','N','O','P');
$columns_title=array("Numero","Type","DPS", "Activit�", "Statut","Organisateur",
                    "Renfort","Lieu","D�but","Fin","Heure d�but",
                    "Heure fin","Dur�e (h)","Pr�sents (hors renforts)","Requis","Facture");

foreach ($columns as $c => $letter) {
    $objPHPExcel->getActiveSheet()->setCellValue($letter.'1', utf8_encode($columns_title[$c]));
    $objPHPExcel->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getStyle($letter."1")->getAlignment()->setWrapText(true);
}
$final_column=$letter;

foreach ($columns as $c => $letter) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getStyle($letter."1")->getAlignment()->setWrapText(true);
}


$query="select E.TE_CODE, TE.TE_LIBELLE, E.E_LIEU, EH.EH_ID,
    DATE_FORMAT(EH.EH_DATE_DEBUT, '%d-%m-%Y') as EH_DATE_DEBUT,
    DATE_FORMAT(EH.EH_DATE_FIN, '%d-%m-%Y') as EH_DATE_FIN, 
    TIME_FORMAT(EH.EH_DEBUT, '%k:%i') as EH_DEBUT, 
    TIME_FORMAT(EH.EH_FIN, '%k:%i') as  EH_FIN,
    EH.EH_DATE_DEBUT as PLAIN_EH_DATE_DEBUT,
    EH.EH_DUREE,
    E.E_NB, E.E_LIBELLE, E.E_CODE, E.E_CLOSED, E.E_OPEN_TO_EXT, E.E_CANCELED, S.S_CODE, E.S_ID,
    E.E_PARENT, E.TAV_ID, S.S_HIDE, S.S_PARENT, SF.NIV
    from evenement E, type_evenement TE, section S, section_flat SF, evenement_horaire EH
    where E.TE_CODE=TE.TE_CODE
    and E.TE_CODE <> 'MC'
    and SF.S_ID = S.S_ID
    and E.E_VISIBLE_INSIDE = 1
    and E.E_CODE = EH.E_CODE
    and E.S_ID = S.S_ID";

// recherche par num�ro?
$s=0;
if (intval($search) > 0 ) {
    $query2="select count(*) as NB from evenement where E_CODE=".intval($search);
    $result2=mysqli_query($dbc,$query2);
    $row2=@mysqli_fetch_array($result2);
    $s=$row2["NB"]; 
}
if ( $s == 1 ) $query .= "\n and E.E_CODE=".intval($search);
// sinon recherche par crit�res
else {
if ( $ec_mode == 'MC' ) 
    $query .= "\n and TE.TE_CODE = 'MC'";
else
    $query .= "\n and TE.TE_CODE <> 'MC'";
if ( $type_evenement == 'ALLBUTGARDE' ) 
    $query .= "\n and TE.TE_CODE <> 'GAR'";
else if ( $type_evenement <> 'ALL' and $ec_mode == 'default' ) 
    $query .= "\n and (TE.TE_CODE = '".$type_evenement."' or TE.CEV_CODE = '".$type_evenement."')";

if ( $type_evenement == 'FOR' ) {
    if ( intval($competence) > 0 )
        $query .= " and E.PS_ID=".$competence;
} 

if ( ! check_rights($id,9))
$query .= " and E.E_VISIBLE_INSIDE=1";

if ( $subsections == 1 )
    $query .= "\n and S.S_ID in (".get_family("$section").")";
else 
    $query .= "\n and S.S_ID =".$section;

if ( $canceled == 0 )
    $query .= "\n and E.E_CANCELED = 0";

if ( $renforts == 0 )
    $query .= "\n and ( E.E_PARENT = 0 or E.E_PARENT is null ) ";

if ( $company <> '-1' )
    $query .= "\n and E.C_ID =".$company;

if($search <> ''){
    $search="%".$search."%";
    $query .= "\n and (E.E_LIBELLE like '$search' or E.E_LIEU like '$search')";
}

$tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
$tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];

$query .="\n and EH.EH_DATE_DEBUT <= '$year2-$month2-$day2' 
             and EH.EH_DATE_FIN   >= '$year1-$month1-$day1'";
$query .="\n order by EH.EH_DATE_DEBUT, EH.EH_DEBUT";
}

$result=mysqli_query($dbc,$query);

$evts_list="0";
$i=2;
while (custom_fetch_array($result)) {

    $S_DESCRIPTION=get_section_name("$S_ID");
    $organisateur=" ".$S_CODE;
    $evts_list .= ",".get_event_and_renforts($E_CODE,$exclude_canceled_r=true);

    if ( $E_CANCELED == 1 ) $state="�v�nement annul�";
    elseif ( $E_CLOSED == 1 ) $state="inscriptions ferm�es";
    elseif ( $E_OPEN_TO_EXT == 0 ) $state="inscriptions interdites pour personnes ext�rieures";
    else  $state="inscriptions ouvertes";
    
    $query2="select count(1) as NB from evenement_horaire where E_CODE=".$E_CODE;
    $result2=mysqli_query($dbc,$query2);
    $row2=mysqli_fetch_array($result2);
    $nbsessions=$row2["NB"];
    
    // cas o� on a les permissions de voir l'�v�nement
    if ( $S_HIDE == 0 
        or check_rights($id,41, $S_ID) 
        or $E_OPEN_TO_EXT == 1 
        or ($S_PARENT == $my_parent_section and $NIV == $nbmaxlevels -1) 
        or $S_ID == $my_parent_section ) {
        $query2="select count(1) as NP from evenement_participation ep
               where ep.E_CODE=".$E_CODE."
              and ep.EH_ID =". $EH_ID."
              and ep.EP_ABSENT = 0 ";
        $result2=mysqli_query($dbc,$query2);
        $row2=mysqli_fetch_array($result2);
        $NP=$row2["NP"];
        
        $query2="select count(distinct P_ID) as NP from evenement_participation ep, evenement e, evenement_horaire eh
               where e.E_PARENT=".$E_CODE."
              and ep.E_CODE=e.E_CODE
              and ep.EP_ABSENT=0
              and e.E_CANCELED=0
              and e.E_CODE=eh.E_CODE
              and ep.E_CODE=eh.E_CODE
              and ep.EH_ID=eh.EH_ID";
        if ( $nbsessions > 1 ) 
                $query2 .= " and eh.EH_DATE_DEBUT='".$PLAIN_EH_DATE_DEBUT."' 
                         and eh.EH_DEBUT = '".$EH_DEBUT."'";
        $result2=mysqli_query($dbc,$query2);
        $row2=mysqli_fetch_array($result2);
        $NP=$row2["NP"] + $NP;
    }
    else {
        $NP="";
        $E_NB="";
    }
    if ( $E_PARENT <> '' ) $renfort="renfort";
    else $renfort="";

    $query2="select count(1) as NR from evenement where E_PARENT=".$E_CODE;
    $result2=mysqli_query($dbc,$query2);
    $row2=mysqli_fetch_array($result2);
    $NR=$row2["NR"];
    $b2="";
    if ( $NR > 0 ) $renfort=$NR;

    $TA_SHORT="";
    if ( $type_evenement =='DPS') {
        $query2="select TA_SHORT from type_agrement_valeur 
                    where TA_CODE = 'D' and TAV_ID=".$TAV_ID;
        $result2=mysqli_query($dbc,$query2);
        $row2=mysqli_fetch_array($result2);
        $TA_SHORT=$row2["TA_SHORT"];
    }

    $myfact="";
    if (check_rights($id, 29)) {
         if (check_rights($id, 29, "$S_ID")) 
             $myfact=get_etat_facturation($E_CODE, "txt");
    }

    $columns_data=array($E_CODE, $TE_CODE, $TA_SHORT, $E_LIBELLE, $state, $organisateur, 
                        $renfort, $E_LIEU, $EH_DATE_DEBUT, $EH_DATE_FIN, $EH_DEBUT, 
                        $EH_FIN, $EH_DUREE, $NP, $E_NB, $myfact);
    foreach ($columns as $c => $letter) {
        $objPHPExcel->getActiveSheet()->setCellValue($letter.$i, utf8_encode($columns_data[$c]));
    }    
    $i++;
}

// premiere ligne couleur du theme
$color=substr($mylightcolor,1);
$objPHPExcel->getActiveSheet()->getStyle('A1:'.$final_column.'1')->applyFromArray(
    array('fill' => array('type'    => PHPExcel_Style_Fill::FILL_SOLID,
                          'color'    => array('argb' => $color)),
          'font' => array('bold'     => true),
    )
);

// si on a des permissions, on voit 2 onglets en plus
if ( check_rights($id, 15, intval($section))) {

    // =======================================================
    // deuxi�me onglet : personnel
    // =======================================================

    $sheet = $objPHPExcel->createSheet();
    $sheet->setTitle(utf8_encode("Liste du personnel"));
    $sheet->freezePane('A2');
    $objPHPExcel->setActiveSheetIndex(1);

    // Add the columns heads
    $columns=array('A','B','C','D','E','F','G','H' );
    $columns_title=array("Nom","Pr�nom","Adresse","Code postal","Ville", "T�l�phone", "Email", "Section");
                
    foreach ($columns as $c => $letter) {
        $objPHPExcel->getActiveSheet()->setCellValue($letter.'1', utf8_encode($columns_title[$c]));
        $objPHPExcel->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getStyle($letter."1")->getAlignment()->setWrapText(true);
    }
    $final_column=$letter;

    foreach ($columns as $c => $letter) {
        $objPHPExcel->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getStyle($letter."1")->getAlignment()->setWrapText(true);
    }

    $header=$objPHPExcel->getProperties()->getTitle();
    $sheet->getHeaderFooter()->setOddHeader($header);
    $printby = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
    $sheet->getHeaderFooter()->setOddFooter('&L&I' . utf8_encode(" imprim� par ".$printby." le ". date('d-m-Y � H:i')) . 
                                            '&RPage &P / &N');


    $query="select distinct p.P_NOM, p.P_PRENOM, p.P_ADDRESS, p.P_ZIP_CODE, p.P_CITY, s.S_CODE, p.P_EMAIL, p.P_PHONE
        from evenement_participation ep, pompier p, evenement e, section s
        where p.P_ID = ep.P_ID
        and e.E_CODE = ep.E_CODE
        and p.P_OLD_MEMBER = 0 
        and e.E_CANCELED = 0
        and ep.EP_ABSENT = 0
        and p.P_SECTION = s.S_ID
        and ep.E_CODE in (".$evts_list.")
        order by p.P_NOM, p.P_PRENOM";        
    $result=mysqli_query($dbc,$query);
    $i=2;
    while ($row=@mysqli_fetch_array($result)) {
        $P_NOM=strtoupper($row["P_NOM"]);
        $P_PRENOM=my_ucfirst($row["P_PRENOM"]);
        $P_ADDRESS=str_replace(array("\r\n", "\n", "\t", "\r"),' ',$row["P_ADDRESS"]);
        $P_ZIP_CODE=" ".$row["P_ZIP_CODE"];
        $P_CITY=$row["P_CITY"];
        $P_EMAIL=$row["P_EMAIL"];
        $P_PHONE=" ".$row["P_PHONE"];
        $S_CODE=" ".$row["S_CODE"];
        $columns_data=array($P_NOM, $P_PRENOM, $P_ADDRESS, $P_ZIP_CODE, $P_CITY, $P_PHONE, $P_EMAIL,  $S_CODE);
        foreach ($columns as $c => $letter) {
            $objPHPExcel->getActiveSheet()->setCellValue($letter.$i, utf8_encode($columns_data[$c]));
        }    
        $i++;
    }

    // premiere ligne couleur du theme
    $color=substr($mylightcolor,1);
    $objPHPExcel->getActiveSheet()->getStyle('A1:'.$final_column.'1')->applyFromArray(
        array('fill' => array('type'    => PHPExcel_Style_Fill::FILL_SOLID,
                              'color'    => array('argb' => $color)),
              'font' => array('bold'     => true),
        )
    );
    
    // =======================================================
    // troisieme onglet : personnel avec equipes
    // =======================================================

    $sheet = $objPHPExcel->createSheet();
    $sheet->setTitle(utf8_encode("Liste personnel equipes"));
    $sheet->freezePane('A2');
    $objPHPExcel->setActiveSheetIndex(2);

    // Add the columns heads
    $columns=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N');
    $columns_title=array("Numero","Evenement","Debut","Dur�e", "Equipe","Nom","Pr�nom","Fonction","Adresse","Code postal","Ville", "T�l�phone", "Email", "Section");
                
    foreach ($columns as $c => $letter) {
        $objPHPExcel->getActiveSheet()->setCellValue($letter.'1', utf8_encode($columns_title[$c]));
        $objPHPExcel->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getStyle($letter."1")->getAlignment()->setWrapText(true);
    }
    $final_column=$letter;

    foreach ($columns as $c => $letter) {
        $objPHPExcel->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getStyle($letter."1")->getAlignment()->setWrapText(true);
    }

    $header=$objPHPExcel->getProperties()->getTitle();
    $sheet->getHeaderFooter()->setOddHeader($header);
    $printby = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
    $sheet->getHeaderFooter()->setOddFooter('&L&I' . utf8_encode(" imprim� par ".$printby." le ". date('d-m-Y � H:i')) . 
                                            '&RPage &P / &N');


    $query="select p.P_NOM, p.P_PRENOM, p.P_ADDRESS, p.P_ZIP_CODE, p.P_CITY, s.S_CODE, p.P_EMAIL, p.P_PHONE, e.E_LIBELLE,  ee.EE_NAME, DATE_FORMAT(min(eh.EH_DATE_DEBUT), '%d-%m-%Y') EH_DATE_DEBUT,
        ep.EP_DUREE, tp.TP_LIBELLE, e.E_CODE
        from evenement_participation ep left join type_participation tp on tp.TP_ID=ep.TP_ID, pompier p, evenement e, section s, evenement_equipe ee, evenement_horaire eh
        where p.P_ID = ep.P_ID
        and ep.EH_ID = eh.EH_ID
        and ep.E_CODE = eh.E_CODE
        and ee.EE_ID = ep.EE_ID
        and ee.E_CODE = ep.E_CODE
        and e.E_CODE = ep.E_CODE
        and p.P_OLD_MEMBER = 0
        and e.E_CANCELED = 0
        and ep.EP_ABSENT = 0
        and p.P_SECTION = s.S_ID
        and ep.E_CODE in (".$evts_list.")
        group by P_NOM, P_PRENOM, P_ADDRESS, P_ZIP_CODE, P_CITY, S_CODE, P_EMAIL, P_PHONE, E_LIBELLE, EE_NAME
        union
        select p.P_NOM, p.P_PRENOM, p.P_ADDRESS, p.P_ZIP_CODE, p.P_CITY, s.S_CODE, p.P_EMAIL, p.P_PHONE, e.E_LIBELLE,  ee.EE_NAME, DATE_FORMAT(min(eh.EH_DATE_DEBUT), '%d-%m-%Y') EH_DATE_DEBUT,
        ep.EP_DUREE, tp.TP_LIBELLE, e.E_CODE
        from evenement_participation ep left join type_participation tp on tp.TP_ID=ep.TP_ID, pompier p, evenement e, section s, evenement_equipe ee, evenement_horaire eh
        where p.P_ID = ep.P_ID
        and ep.E_CODE = e.E_CODE
        and ep.EH_ID = eh.EH_ID
        and ep.E_CODE = eh.E_CODE
        and ee.EE_ID = ep.EE_ID
        and e.E_PARENT = ee.E_CODE
        and p.P_OLD_MEMBER = 0
        and e.E_CANCELED = 0
        and ep.EP_ABSENT = 0
        and p.P_SECTION = s.S_ID
        and ep.E_CODE in (".$evts_list.")
        group by P_NOM, P_PRENOM, P_ADDRESS, P_ZIP_CODE, P_CITY, S_CODE, P_EMAIL, P_PHONE, E_LIBELLE, EE_NAME
        order by P_NOM, P_PRENOM, EH_DATE_DEBUT";
    $result=mysqli_query($dbc,$query);
    $i=2;
    while ($row=@mysqli_fetch_array($result)) {
        $P_NOM=strtoupper($row["P_NOM"]);
        $P_PRENOM=my_ucfirst($row["P_PRENOM"]);
        $P_ADDRESS=str_replace(array("\r\n", "\n", "\t", "\r"),' ',$row["P_ADDRESS"]);
        $P_ZIP_CODE=" ".$row["P_ZIP_CODE"];
        $P_CITY=$row["P_CITY"];
        $P_EMAIL=$row["P_EMAIL"];
        $P_PHONE=" ".$row["P_PHONE"];
        $S_CODE=" ".$row["S_CODE"];
        $E_LIBELLE=$row["E_LIBELLE"];
        $EE_NAME=$row["EE_NAME"];
        $EH_DATE_DEBUT=$row["EH_DATE_DEBUT"];
        $EP_DUREE=$row["EP_DUREE"];
        $TP_LIBELLE=$row["TP_LIBELLE"];
        $E_CODE=$row["E_CODE"];
        $columns_data=array($E_CODE, $E_LIBELLE, $EH_DATE_DEBUT, $EP_DUREE, $EE_NAME, $P_NOM, $P_PRENOM, $TP_LIBELLE, $P_ADDRESS, $P_ZIP_CODE, $P_CITY, $P_PHONE, $P_EMAIL,  $S_CODE);
        foreach ($columns as $c => $letter) {
            $objPHPExcel->getActiveSheet()->setCellValue($letter.$i, utf8_encode($columns_data[$c]));
        }    
        $i++;
    }

    // premiere ligne couleur du theme
    $color=substr($mylightcolor,1);
    $objPHPExcel->getActiveSheet()->getStyle('A1:'.$final_column.'1')->applyFromArray(
        array('fill' => array('type'    => PHPExcel_Style_Fill::FILL_SOLID,
                              'color'    => array('argb' => $color)),
              'font' => array('bold'     => true),
        )
    );


    // =======================================================
    // troisieme onglet : vehicules
    // =======================================================

    $sheet = $objPHPExcel->createSheet();
    $sheet->setTitle(utf8_encode("Liste des vehicules"));
    $sheet->freezePane('A2');
    $objPHPExcel->setActiveSheetIndex(3);

    // Add the columns heads
    $columns=array('A','B','C','D','E','F' );
    $columns_title=array("Type","Immatriculation","Modele","Indicatif","Section", "Km parcourus");
                
    foreach ($columns as $c => $letter) {
        $objPHPExcel->getActiveSheet()->setCellValue($letter.'1', utf8_encode($columns_title[$c]));
        $objPHPExcel->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getStyle($letter."1")->getAlignment()->setWrapText(true);
    }
    $final_column=$letter;

    foreach ($columns as $c => $letter) {
        $objPHPExcel->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getStyle($letter."1")->getAlignment()->setWrapText(true);
    }

    $header=$objPHPExcel->getProperties()->getTitle();
    $sheet->getHeaderFooter()->setOddHeader($header);
    $printby = fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM'])));
    $sheet->getHeaderFooter()->setOddFooter('&L&I' . utf8_encode(" imprim� par ".$printby." le ". date('d-m-Y � H:i')) . 
                                            '&RPage &P / &N');


    $query="select distinct v.TV_CODE, v.V_IMMATRICULATION, v.V_MODELE, v.V_INDICATIF, s.S_CODE, sum(ev.EV_KM) 'KM'
        from evenement_vehicule ev, evenement e, section s, vehicule v
        where v.V_ID = ev.V_ID
        and v.S_ID = s.S_ID
        and e.E_CODE = ev.E_CODE
        and e.E_CANCELED = 0
        and ev.E_CODE in (".$evts_list.")
        group by v.TV_CODE, v.V_IMMATRICULATION, v.V_MODELE, v.V_INDICATIF, s.S_CODE
        order by v.TV_CODE, v.V_IMMATRICULATION";        

    $result=mysqli_query($dbc,$query);
    $i=2;
    while ($row=@mysqli_fetch_array($result)) {
        $TV_CODE=$row["TV_CODE"];
        $V_IMMATRICULATION=$row["V_IMMATRICULATION"];
        $V_MODELE=$row["V_MODELE"];
        $V_INDICATIF=$row["V_INDICATIF"];
        $S_CODE=" ".$row["S_CODE"];
        $KM=$row["KM"];
        $columns_data=array($TV_CODE, $V_IMMATRICULATION, $V_MODELE, $V_INDICATIF, $S_CODE, $KM);
        foreach ($columns as $c => $letter) {
            $objPHPExcel->getActiveSheet()->setCellValue($letter.$i, utf8_encode($columns_data[$c]));
        }    
        $i++;
    }

    // premiere ligne couleur du theme
    $color=substr($mylightcolor,1);
    $objPHPExcel->getActiveSheet()->getStyle('A1:'.$final_column.'1')->applyFromArray(
        array('fill' => array('type'    => PHPExcel_Style_Fill::FILL_SOLID,
                              'color'    => array('argb' => $color)),
              'font' => array('bold'     => true),
        )
    );
}
    
// =======================================================
// affichage
// =======================================================

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setTitle("Liste des evenements",0,30);

// Zoom 85%
//$objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(85);

// Redirect output to a client web browser (Excel2007) - works starting PHP 5.2 only
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="evenements.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');


$objWriter->save('php://output');
exit;

?>
