$(document).ready(function (){
    $('#fake_file_button').click(function (){
        $('#csv_csv_file').click();
    })
    $('#csv_csv_file').on('change', function(){
        if($(this).val().split('.')[1] === 'csv'){
            //renseigne le nom du fichier dans le span et ajoute la classe
            $('#file_button').addClass('file_loaded_button');

            $('#file_name_container').html($(this).val().split('\\')[2]).removeClass('d-none');
            //affiche le bouton submit si le fichier a bien l'extension csv
            $('#csv_form_submit').prop('hidden',false);
        }else{
            $('#error_msg').html('Le fichier séléctionnée n\'est pas de type CSV');
        }
    });
})