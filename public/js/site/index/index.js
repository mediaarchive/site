$(function(){
    $('form').submit(function(){
        if($('form input[name=name]').val() == ''){
            alert('Укажите имя мероприятия');
            $('form input[name=name]').focus();
            return false;
        }
        else if($('form input[name=author_name]').val() == ''){
            alert('Укажите Ваше имя');
            $('form input[name=author_name]').focus();
            return false;
        }
        else{
            $('form button').attr('disabled', 'disabled');
            $('form button').html('<i class="fa fa-spin fa-spinner"></i> Загрузка...');
            return true;
        }
    });
});