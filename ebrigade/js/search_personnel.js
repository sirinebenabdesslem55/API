$(document).ready(function($){
    $("input#trouveNom").keyup(function(){
        delay(function(){
            var trouve;
            var currentTime = new Date();
            var n = currentTime.getTime();
            var choixSection;
            choixSection = $("select#choixSection option:selected").val();
            trouve = $("input#trouveNom").val();
            oldmbr = $("select#oldmbr option:selected").val();
            $.post("search_personnel_result.php?nocache="+n,{trouve:trouve,section:choixSection,typetri:'nom'},    
            function (data){        
                $("#export").empty();
                $("#export").append(data);
            });
        }, 300 );
    });

    $('input#trouveVille').keyup(function(){
        delay(function(){
            var trouve;
            var currentTime = new Date();
            var n = currentTime.getTime();
            trouve = $('input#trouveVille').val();
            $.post('search_personnel_result.php',{trouve:trouve,typetri:'ville'},
            function (data){
                $('#export').empty();
                $('#export').html(' ').append(data);
            });
        }, 300 );
    });

    $('input#trouveMail').keyup(function(){
        delay(function(){
            var trouve;
            var currentTime = new Date();
            var n = currentTime.getTime();
            trouve = $('input#trouveMail').val();
            $.post('search_personnel_result.php',{trouve:trouve,typetri:'mail'},
            function (data){
                $('#export').empty();
                $('#export').html(' ').append(data);
            });
        }, 300 );
    });
    
    $('input#trouveTel').keyup(function(){
        delay(function(){
            var trouve;
            var currentTime = new Date();
            var n = currentTime.getTime();
            trouve = $('input#trouveTel').val();
            $.post('search_personnel_result.php',{trouve:trouve,typetri:'tel'},
            function (data){        
                $('#export').empty();
                $('#export').html(' ').append(data);
            });
        }, 300 );
    });

    $('input#trouveCpt').keyup(function(){
        delay(function(){
            var trouve;
            var currentTime = new Date();
            var n = currentTime.getTime();
            trouve = $('input#trouveCpt').val();
            $.post('search_personnel_result.php',{trouve:trouve,typetri:'compte'},    
            function (data){        
                $('#export').empty();
                $('#export').append(data);
            });
        }, 300 );
    });

    $("input").click(function(){    
        var dest;
        var Tri;
        var choixSection;
        var choixStatut;
        dest='';
        for (i=0; i<$("input").length; i++) {
            if($("input")[i].checked && $("input")[i].name == 'qualif' ) {
                dest += ','+$("input")[i].value;
            }
        }            
        $("input + label").css("color", "red");
        $("input:not(:checked) + label").css("color", "#191970");
        
        dest = dest.substr(1,dest.length);                        
        Tri = $("select#typeTri option:selected").val(); 
        choixSection = $("select#choixSection option:selected").val();
        choixStatut = $("select#choixStatut option:selected").val();
        //alert('Tri:'+Tri+'');
        if(dest!=''){
        $.post("search_personnel_result.php",{qualif:dest,typetri:Tri,section:choixSection,statut:choixStatut},        
        function (data){        
            $("#export").empty();
            $("#export").html(" ").append(data);
        });
        }else{
            $("#export").empty();
        }
    });

    $("select").change(function(){
        $("select#typeTri option:selected").each(function () {
           Tri = $(this).val();
        } );
        $("select#choixSection option:selected").each(function () {
           choixSection = $(this).val();
           $("input#trouveNom").val('');
        } );
        $("select#choixStatut option:selected").each(function () {
           choixStatut = $(this).val();
        } );
        var dest;
        var Tri;
        var choixSection;
        var choixStatut;
        dest='';
            
        for (i=0; i<$("input").length; i++) {
                if($("input")[i].checked && $("input")[i].name == 'qualif' ) {
                    dest += ','+$("input")[i].value;
            }
        }            
        $("input + label").css("color", "red");
        $("input:not(:checked) + label").css("color", "black");
            
        dest = dest.substr(1,dest.length);                    
        if(dest!=''){
            $.post("search_personnel_result.php",{qualif:dest,typetri:Tri,section:choixSection,statut:choixStatut},        
            function (data){    
                $("#export").empty();
                $("#export").html(" ").append(data);
            });
        }
        else{
            $("#export").empty();
        }
    });

});