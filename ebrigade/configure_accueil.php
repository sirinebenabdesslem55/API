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
include_once ("fonctions_infos.php");
check_all(0);
$id=$_SESSION['id'];
writehead();
?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
.dropzone { list-style-type: none; background: #F5F5F5; margin: 5px; margin-right: 10px;  padding: 5px; border-radius: 6px; min-height:40px; width:100%;}
.draggable { margin: 5px; padding: 5px; border-radius: 6px;}
.ddefault { background: <?php echo $mylightcolor; ?> }
.dgrey { background: #C0C0C0}
.dyellow { background: yellow}
</style>
<script src="js/jquery.min.12.js"></script>
<script src="js/jquery-ui.js"></script>
<script>
var targetDropZone = $("#sortable1");
var dropZoneId;
var itemID;

function removeClasses(wid) {
    $('#'+wid).removeClass('dyellow');
    $('#'+wid).removeClass('dgrey');
    $('#'+wid).removeClass('ddefault');
}

function activateWidget(wid) {
    var checkboxid =  document.getElementById('C'+wid);
    removeClasses(wid);
    if ( checkboxid.checked ) {
        $('#'+wid).addClass('ddefault');
        active=1;
    }
    else {
        $('#'+wid).addClass('dgrey');
        active=0;
    }
    $.post("save_accueil.php", { wid:wid, show:active });
    //$.post("save_accueil.php", { wid:wid, show:active })
    //.done(function( data ) {
    //    console.log( "Data Loaded: " + data );
    //});
}

$( function() {

$("ul.dropzone").droppable({
    drop: function( event, ui) {
        targetDropZone = $(this);
        itemID = ui.draggable.attr("id");
        dropZoneId = targetDropZone.attr("id");
        //console.log( "dropped " + itemID + " in " + dropZoneId );
    }
});

$("ul.dropzone").sortable({
    connectWith: "ul",
    dropOnEmpty: true,
    stop: function( ) {
        var itemOrder = targetDropZone.sortable("toArray");
        //console.log(itemOrder);
        dropZoneId = targetDropZone.attr("id");
        for (var i = 0; i < itemOrder.length; i++) {
            if ( itemID == itemOrder[i] ) {
                removeClasses(itemID);
                $('#'+itemID).addClass('dyellow');
                var zid = dropZoneId.substring(8, 9);
                var pos = i;
                if ( pos > 0 ) {
                    pos = pos + 1;
                    if ( pos == itemOrder.length ) {
                        pos = pos + 5;
                    }
                }
                $.post("save_accueil.php", { wid:itemID, zone:zid, position:pos });
                //$.post("save_accueil.php", { wid:itemID, zone:zid, position:pos })
                //.done(function( data ) {
                //    console.log( "Data Loaded: " + data );
                //});
                //console.log( "sorted " + itemID + " in zone " + zid + " at position " + pos);
            }
        }
    }
});

$( "#sortable1, #sortable2, #sortable3" ).disableSelection();

} );
</script>
</head>
<?php
$body = "<body align='center'>
    <div align='center'>
    <h3><b>Configuration de la page d'accueil</b></h3><p>";
$body .=  write_boxes('configure');
$body .= "<p><input type='button' class='btn btn-default' value='Terminé'  title='Enregistrer les changements et retour accueil' name='end' onclick=\"javascript:self.location.href='index_d.php';\">";
$body .= "<p><input type='button' class='btn btn-primary' value='Réinitialiser'  title='Supprimer ma configuration personnalisée et remettre la configuration par défaut' name='end' onclick=\"javascript:self.location.href='save_accueil.php?supprimer=1';\">";
$body .="</div>";

print $body;
writefoot();
?>