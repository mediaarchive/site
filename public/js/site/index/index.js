$(function(){
    $('form').find('input[name=author_name], input[name=contact]')
        .each(function() {
            var key = $(this).attr('name');
            var cookie = getcookie('form_' + key);

            if(typeof cookie !== 'undefined' && cookie != '')
                $(this).val(cookie);
        })
        .blur(function(){
            var key = $(this).attr('name');
            var expires = new Date( (new Date()).getTime() + 1000 * 60 * 60 * 24 * 30 * 12 );

            setcookie('form_' + key, $(this).val(), expires.toUTCString(), '/');
        });

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