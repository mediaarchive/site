function filedrop_class (classname){
    $('#filedrop')
        .removeClass('over')
        .removeClass('drag')
        .addClass(classname);
}

$(function(){
    var fileTemplate = Handlebars.compile($('#fileTemplate').html());
    var blocking_window = false;

    window.onbeforeunload = function(){
        if(blocking_window)
            return 'Загрузка файлов еще не закончена. Вы уверены, что хотите уйти с этой страницы?';
    }

    $(document)
        .on('dragover', function(){
            filedrop_class('drag');
        })
        .on('dragleave', function(){
            filedrop_class();
        });

    $('#filedrop')
        .click(function(){
            $('#fileinput').click();
        })
        .on('dragover', function(e){
            filedrop_class('over');
            e.dataTransfer.dropEffect = 'copy'; // Explicitly show this is a copy.
        })
        .on('dragleave', function(){
            filedrop_class();
        })
        .filedrop({
            fallback_id: 'fileinput',   // an identifier of a standard file input element, becomes the target of "click" events on the dropzone
            url: "/index/upload",              // upload handler, handles each file separately, can also be a function taking the file and returning a url
            paramname: 'file',          // POST parameter name used on serverside to reference file, can also be a function taking the filename and returning the paramname
            withCredentials: true,          // make a cross-origin request with cookies
            data: {
                temp_dir_name: temp_dir_name,
                full_path: encodeURIComponent(full_path)
            },
            error: function(err, file) {
                console.error(err, file);
                switch(err) {
                    case 'BrowserNotSupported':
                        alert('Браузер не поддерживает загрузку перетаскиванием')
                        break;
                    case 'TooManyFiles':
                        alert('Слишком много файлов (максимум разрешено 100 файлов)');
                        break;
                    case 'FileTooLarge':
                        alert('Файл слишком большой (максимальный размер файла 5 МБ)');
                        break;
                    case 'FileTypeNotAllowed':
                        alert('Файлы такого типа не поддерживаются');
                        break;
                    case 'FileExtensionNotAllowed':
                        alert('Файлы такого типа не поддерживаются');
                        break;
                    default:
                        alert('Произошла неизвестная ошибка');
                        break;
                }
            },
            // @TODO: написать запреты для загружаемых форматов и mime-type
            //allowedfiletypes: ['image/jpeg','image/png','image/gif'],   // filetypes allowed by Content-Type.  Empty array means no restrictions
            //allowedfileextensions: ['.jpg','.jpeg','.png','.gif'], // file extensions allowed. Empty array means no restrictions
            //maxfiles: 100,
            maxfilesize: 20,    // max file size in MBs
            queuefiles: 3, // паралельные загрузки
            drop: function(t) {
                console.log(t);
                filedrop_class();
            },
            rename: function(name){ return encodeURIComponent(name); },
            uploadStarted: function(i, file, len){
                console.log('upload started',file);
                blocking_window = true;
            },
            uploadFinished: function(i, file, response, time) {
                $file = $('#file_list .list-group-item[data-id="'+file.name+'"]');
                $file.removeClass('list-group-item-info');

                if(response.status != 'ok' || response == '') {
                    alert('Произошла ошибка при загрузке файла ' + file.name + '. Попробуйте загрузить этот файл еще раз');
                    console.error(response, file);
                    $file.addClass('list-group-item-danger');
                }
                else
                   $file.addClass('list-group-item-success');
            },
            progressUpdated: function(i, file, progress) {
                // this function is used for large files and updates intermittently
                // progress is the integer value of file being uploaded percentage to completion
                $('#file_list .list-group-item[data-id="'+file.name+'"] .progress_val').text(progress + '%');
            },
            globalProgressUpdated: function(progress) {
                // progress for all the files uploaded on the current instance (percentage)
                $('#progress').width(progress+"%");
            },
            beforeEach: function(file) {
                $('#file_list').append(fileTemplate({
                    name: file.name,
                    type: file.type,
                    size: file.size
                }));
            },
            //beforeSend: function(file, i, done) {
            //    done();
            //}
            afterAll: function(){
                blocking_window = false;
            }
        });

    $('.save_button').click(function(){
        $('button').attr('disabled', 'disabled');

        $.post('/index/save', {
            name: $('#name').text(),
            author_name: author_name,
            full_path: full_path,
            temp_dir_name: temp_dir_name
        }, function(res){
            res = JSON.parse(res);
            if(res.status != 'ok'){
                alert('Произошла неизвестная ошибка. Попробуйте еще раз');
                console.error(res);
            }
            else
                document.location.href = '/index/end';
        });
    });

    $('.cancel_button').click(function(){
        $('button').attr('disabled', 'disabled');

        $.post('/index/cancel', {
            full_path: full_path,
            temp_dir_name: temp_dir_name
        }, function(res){
            res = JSON.parse(res);
            if(res.status != 'ok'){
                alert('Произошла неизвестная ошибка. Попробуйте еще раз');
                console.error(res);
            }
            else
                document.location.href = '/index/end?cancel   ';
        });
    });
});
