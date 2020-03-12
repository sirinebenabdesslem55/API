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
check_all(42);

if ( isset($_GET['mid'])) $mid=intval($_GET['mid']);
else {
    $order=secure_input($dbc,$_GET["order"]);
    $filter=secure_input($dbc,$_GET["filter"]);
    $type=secure_input($dbc,$_GET["type"]);
    $old=intval($_GET['old']);
    $mad=intval($_GET['mad']);
    $subsections=intval($_GET['subsections']);
    $mid=0;
}

date_default_timezone_set('Europe/Paris');

/** Include PHPExcel */
require_once 'lib/PHPExcel/Classes/PHPExcel.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("eBrigade ".$version)
                             ->setLastModifiedBy("eBrigade ".$version)
                             ->setTitle("Materiel")
                             ->setSubject("Materiel")
                             ->setDescription("Liste du materiel")
                             ->setKeywords("office 2007 openxml php")
                             ->setCategory("Materiel");

// Freeze panes
$objPHPExcel->getActiveSheet()->freezePane('A2');

// Rows to repeat at top
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

// Add the columns heads
$columns=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N');
$columns_title=array("Catégorie","Type", "Nb", "Section","Modèle",
                    "N°Série","Statut","Date limite","N°inventaire","Lieu stockage",
                    "Commentaire","année","Mis à disposition","affecté à");
                     
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

$query1="select distinct m.TM_ID, tm.TM_CODE,tm.TM_DESCRIPTION,tm.TM_USAGE,
         m.VP_ID,vp.VP_LIBELLE, vp.VP_OPERATIONNEL,vp.VP_LIBELLE,
         DATE_FORMAT(m.MA_REV_DATE, '%d-%m-%Y') as MA_REV_DATE,
         m.MA_ID, m.MA_NUMERO_SERIE, m.MA_COMMENT, m.MA_MODELE, m.MA_EXTERNE,
         m.MA_ANNEE, m.MA_NB, m.S_ID, s.S_CODE ,m.MA_LIEU_STOCKAGE, m.MA_INVENTAIRE, m.AFFECTED_TO
        from materiel m, type_materiel tm, section s, vehicule_position vp, categorie_materiel cm
        where m.TM_ID=tm.TM_ID
        and cm.TM_USAGE = tm.TM_USAGE
        and m.VP_ID=vp.VP_ID
        and s.S_ID=m.S_ID"; 

if ( $mad == 1 ) {
// matériel mis à disposition seulement
    $query1 .= "\nand m.MA_EXTERNE=1";
    $title="Liste du matériel mis à disposition";
}

if ( $mid > 0 ) {
// matériel inclus dans le lot
    $query1 .= "\nand m.MA_PARENT=".$mid;
    $title="Liste du matériel inclus dans le lot";
}
else { 
// afficher tout le matériel
    if ( $type <> 'ALL' ) $query1 .= "\n and (tm.TM_ID='".$type."' or tm.TM_USAGE='".$type."')";
    // choix section
    if ( $nbsections == 0 ) {
        if ( $subsections == 1 ) {
                 $query1 .= "\nand m.S_ID in (".get_family("$filter").")";
        }
        else {
                 $query1 .= "\nand m.S_ID =".$filter;
        }
    }
    if ( $old == 1 ) $query1 .="\nand vp.VP_OPERATIONNEL <0";
    else $query1 .="\nand vp.VP_OPERATIONNEL >=0";

    $query1 .="\norder by ".$order;
    if ( $order == 'TM_USAGE' ) $query1 .=" desc";
    
    if ( $filter <> 0 ) $cmt=" de ".get_section_name("$filter");
    else $cmt=" de ".$cisname;
    $title="Liste du matériel".$cmt;
}

$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);

$i=2;
while (custom_fetch_array($result1)) {
    $S_CODE=" ".$S_CODE;
    if ( $MA_EXTERNE == 1 )$ext='oui';
    else $ext='non';
    if ( $AFFECTED_TO <> '' ) {
           $owner=strtoupper(substr(get_prenom($AFFECTED_TO),0,1).".".get_nom($AFFECTED_TO));
    }
    else $owner='';
    
    $columns_data=array($TM_USAGE, $TM_CODE, $MA_NB, $S_CODE, $MA_MODELE, 
                        $MA_NUMERO_SERIE, $VP_LIBELLE, $MA_REV_DATE, $MA_INVENTAIRE, $MA_LIEU_STOCKAGE, 
                        $MA_COMMENT, $MA_ANNEE, $ext, $owner);
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

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setTitle(utf8_encode(substr("materiel",0,30)));

// Zoom 85%
$objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(85);

// Redirect output to a client web browser (Excel2007) - works starting PHP 5.2 only
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="materiel.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objWriter->save('php://output');
exit;

?>
