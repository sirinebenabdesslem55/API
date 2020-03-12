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
ini_set('memory_limit', '256M');
@set_time_limit($mytimelimit);

date_default_timezone_set('Europe/Paris');

/** Include PHPExcel */
require_once 'lib/PHPExcel/Classes/PHPExcel.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("eBrigade ".$version)
							 ->setLastModifiedBy("eBrigade ".$version)
							 ->setTitle("Report")
							 ->setSubject("Report")
							 ->setDescription("Report")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Report");

// Freeze panes
$objPHPExcel->getActiveSheet()->freezePane('A2');

// Rows to repeat at top
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);


// Add the columns heads
$columns_letters=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
$columns_title=array();
  
for($c=0;$c<$numcol;$c++){
 	$letter=$columns_letters[$c];
	$columns_title[$c]=$tab[0][$c];
	$objPHPExcel->getActiveSheet()->setCellValue($letter.'1', utf8_encode($columns_title[$c]));
 	$objPHPExcel->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getStyle($letter."1")->getAlignment()->setWrapText(true);
}			 
$final_column=$letter;

// Add the data
for($lig=1;$lig<count($tab);$lig++){
 	$i=$lig+1;
	for($c=0;$c<$numcol;$c++){
	 	$letter=$columns_letters[$c];
	 	$cell=NettoyerTexte($tab[$lig][$c]);
	 	if ( $columns_title[$c] == 'voir' ) $cell=str_replace('voir','',strip_tags($cell));	
		$objPHPExcel->getActiveSheet()->setCellValue($letter.$i, utf8_encode($cell));
	}
}

// premiere ligne couleur du theme
$color=substr($mylightcolor,1);
$objPHPExcel->getActiveSheet()->getStyle('A1:'.$final_column.'1')->applyFromArray(
	array('fill' => array('type'	=> PHPExcel_Style_Fill::FILL_SOLID,
	                      'color'	=> array('argb' => $color)),
		  'font' => array('bold' 	=> true),
	)
);

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

$name=fixcharset($export_name);
$name=str_replace("'","",$name);
$name=substr($name,0,27);
$name=utf8_encode($name);

$objPHPExcel->getActiveSheet()->setTitle($name);

// Zoom 85%
$objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(85);


// Redirect output to a client web browser (Excel2007) - works starting PHP 5.2 only
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$export_name.'.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');


$objWriter->save('php://output');
exit;

?>
