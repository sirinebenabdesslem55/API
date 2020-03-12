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
  
include_once("config.php");
include_once("fonctions_import.php");

// script to be used by cronjob in command line. Or for test purpose in a browser
if ( is_cli() ) {
    $cli=1;
    print "Start import ".date('Y-m-d H:i:s')."\n\n";
}
else {
    $cli=0;
    check_all(14);
    writehead();
?>
    <script>
    $(document).ready(function(){
        $('[data-toggle="popover"]').popover();
    });
    window.setInterval(function() {
        var elem = document.getElementById('importconsole');
        elem.scrollTop = elem.scrollHeight;
    }, 5000);
    </script>
    

<?php

    $html = "<script type='text/javascript' src='js/checkForm.js?version=".$version."'></script>
             <script type='text/javascript' src='js/api_import.js?version=".$version."'></script>
          </head>
          <body>
          <div align=center style='width:300px'>";
}

$nb = get_nb_records_api($cli);

// ===================================
// Command line mode
// ===================================
if ( $cli == 1 ) {
    if ( $nb > 0 ) {
        $limit=$argv[1];
        if ( $limit == 'all' ) $limit=$nb;
        else $limit=intval($argv[1]);
        if ( $limit == 0 ) {
            echo "ERROR - invalid parameter: \"".$argv[1]."\" int value expected\n";
            exit;
        }
        $start=intval($argv[2]);
        $organismes = build_list_organismes();
        $affiliations = build_affiliations();
        import_multiple_users($limit,$start,$cli=1);
    }
}
// ===================================
// Save in GUI mode
// ===================================
else if ( isset($_GET["pid"]) or isset($_GET["number"])) {
    if ( $nb > 0 ) {
        @set_time_limit(3600); // 1h
        $organismes = build_list_organismes();
        $affiliations = build_affiliations();
        if ( isset($_GET["pid"])) {
            $pid=intval($_GET["pid"]);
            $ret = import_one_user($pid);
        }
        else if (isset($_GET["number"])) {
            $query="update pompier set P_LICENCE=null where P_LICENCE=''";
            $res=mysqli_query($dbc,$query);
            $limit=intval($_GET["number"]);
            $start=intval($_GET["start"]);
            $ret = import_multiple_users($limit,$start,$cli=0);
        }
        print " <input type='button' class='btn btn-default' value='retour' onclick=\"redirect('import_api.php');\">";
    }
}
// ===================================
// Display form
// ===================================
else {
    $helptext="L'import de donn�es utilisateurs peut �tre fait pour une seule fiche, ou pour toutes les fiches retourn�es par l'API. L'API utilis�e est $import_api_url. Dans le cas d'une seule fiche, le num�ro doit �tre fourni, par exemple 421917. Si la fiche existe d�j�, alors elle sera mise � jour. Sinon elle sera cr��e sur $application_title. Pour des situations de tests, on peut lancer l'import de toutes les fiches mais limiter le nombre � 5, 10, 20,100,1000. Cliquer sur le bouton 'Importer' pour lancer l'import. L'import peut aussi �tre fait en ligne de commande sur le serveur avec scripts/import.sh. Dans ce mode il est possible d'importer un nombre illimit� de fiches.";
    $help = " <a href='#' data-toggle='popover' title='Information import unitaire' data-trigger='hover' data-content=\"".$helptext."\"><i class='fa fa-question-circle fa-lg' ></i></a>";
    $html .= "<div align=left>
                <h4><b>Importer fiches</b></h4> ";
    $html .= "<small>Il y a $nb fiches retourn�es par l'API</small><p><p>";
    $html .= "Nombre de fiches � importer ";
    $html .= "<select name='number' id='number' title='S�lectionnez le nombre de fiches � importer' onchange='changenumber();'>
                <option value='1' selected>Une seule</option>
                <option value='0'>Toutes</option>
                <option value='5'>5</option>
                <option value='10'>10</option>
                <option value='20'>20</option>
                <option value='50'>50</option>
                <option value='100'>100</option>
                <option value='200'>200</option>
                <option value='500'>500</option>
                <option value='1000'>1000</option>
                <option value='2000'>2000</option>
                <option value='5000'>5000</option>
                <option value='10000'>10000</option>
              </select> ".$help."<p>";
              
    $html .= "<div id='divstart' style='display:none;' >D�but �
                <input type='text' name='start' id='start' value='0' size=6 title='Nombre �l�ments � partir duquel les donn�es API sont import�es' onchange=\"checkNumberNullAllowed(this,'0');\">
               </div>";

    $html .= "<div id='divpid' style='display:inline;' >Num�ro de la fiche 
                <input type='text' name='pid' id='pid' value='' size=6 title='Saisir le num�ro de la fiche personnel (licenci�)' onchange=\"checkNumberNullAllowed(this,'');\">
               </div>";
    if ( $nb == 0 ) $disabled='disabled';
    else $disabled='';
    $html .= "<p><input type='button' class='btn btn-default' value='Importer' name='importer' onclick=\"run_import();\" title=\"Lancer l'import\" $disabled><p>";
}

if ( is_cli() ) 
    print "\nEnd import ".date('Y-m-d H:i:s')."\n";
else {
    $html .= "</div></div>";
    print $html;
    writefoot();
}


?>
