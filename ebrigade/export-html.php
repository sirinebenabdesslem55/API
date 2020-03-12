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

function AfficheTableau($tab,$ClassCss,$numcol,$rupture,$somme){
    global $mylightcolor;
    $colSomme= array();
    $out ="";
    $lig=0;
    // titres
    $out .=  "<thead>";
    $out .=  "\n"."<tr>";
    for($col=0;$col<$numcol;$col++){
        $out .=  "<th align=left>";
        $out .=  $tab[$lig][$col];
        $out .=  "</th>";
        // rechercher les colonnes de rupture
        if(count($rupture)>0){
            if(in_array($tab[$lig][$col],$rupture)){
                $colRupture=$col;
            }else{
                $colRupture=1;
            }
        }        
        // rechercher les colonnes � sommer
        if(count($somme)>0){
            if(in_array($tab[$lig][$col],$somme)){
                $colSomme[$col]=$col;
            }        
        }
    }
    $out .=  "</tr>";
    $out .=  "</thead>";

    // valeurs
    $out .=  "<tbody>";
    $TotalSomme[]= array();
    $TotalSommeGlobal[]=array();
    for($lig=1;$lig<count($tab);$lig++){
        if(count($rupture)>0){
            if(($tab[$lig-1][$colRupture-1]!=$tab[$lig][$colRupture-1]) && $lig>1){
                $out .=  "\n"."<tr class=SousTotal>";
                for($col=0;$col<$numcol;$col++){
                    $css = (count($ClassCss)>0?" class=\"".$ClassCss[$col]."\"":"");
                    
                    if($col==min($colSomme)-1){
                        $out .=  "<th nowrap align=left>";
                        $out .=  "Sous-Total ".$tab[$lig-1][$colRupture-1].":";
                    }else{
                        $out .=  "<th align=left>";
                    }
                    
                    if(in_array($col,$colSomme)){
                        $out .=  number_format($TotalSomme[$col],0,"."," ");
                        $TotalSomme[$col]= 0;
                    }else{
                        $out .=  "&nbsp;";
                    }
                    
                    $out .=  "</th>";
                    
                }
                $out .=  "</tr>"."\n";
            }
        }        
        
        $out .=  "\n"."<tr>";
        for($col=0;$col<$numcol;$col++){
            $out .=  "<td nowrap align=left>";
            $out .= $tab[$lig][$col];

            if(in_array($col,$colSomme)){
                $TotalSomme[$col] =  (isset($TotalSomme[$col])?$TotalSomme[$col]+$tab[$lig][$col]:$tab[$lig][$col]);
                $TotalSommeGlobal[$col] =  (isset($TotalSommeGlobal[$col])?$TotalSommeGlobal[$col]+$tab[$lig][$col]:$tab[$lig][$col]);
            }else{
                $out .="&nbsp;";
            }            

            $out .=  "</td>";
        }
        $out .=  "</tr>";
        
    }
    if(count($tab)>1) {
        if(count($rupture)>0){
            //Sous Total
            $out .=  "\n"."<tr class=SousTotal>";
            for($col=0;$col<$numcol;$col++){
                if($col==min($colSomme)-1){
                    $out .=  "<th nowrap align=left>";
                    $out .=  "Sous-Total ".$tab[$lig-1][$colRupture-1].":";
                }else{
                    $out .=  "<th align=left>";
                }                    
                
                if(in_array($col,$colSomme)){            
                    $out .=  number_format( (float) $TotalSomme[$col],0,"."," ");
                }else{
                    $out .=  "&nbsp;";
                }

                $out .=  "</th>";
            }
            $out .=  "</tr>"."\n";
        }        
        if(count($somme)>0){
            // Total g�n�ral
            $out .=  "\n"."<tfoot><tr class=TabTotal>";
            for($col=0;$col<$numcol;$col++){
                if($col==min($colSomme)-1){
                    $out .=  "<th nowrap align=left >";
                    $out .=  "Total:";
                }else{
                    $out .=  "<th nowrap align=left >";
                }            

                if(in_array($col,$colSomme)){            
                    $out .=  number_format((float) $TotalSommeGlobal[$col],0,"."," ");
                }else{
                    $out .=  "&nbsp;";
                }

                $out .=  "</th>";
            }
            $out .=  "</tr></tfoot>"."\n";
        }
    }
    $out .=  "\n"."</tbody>";
    
    return "<table cellspacing='1' id=exportTable width=700 class='tablesorter'>
                    $out\n
                  </table>";
}
@set_time_limit($mytimelimit);
if (isset ($comment) ) print "<p><span class=small>".$comment."</span>";
echo AfficheTableau($tab,$ColonnesCss,$numcol,$RuptureSur,$SommeSur);

?>
