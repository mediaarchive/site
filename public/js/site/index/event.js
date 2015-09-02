function filedrop_class (classname){
    $('#filedrop')
        .removeClass('over')
        .removeClass('drag')
        .addClass(classname);
}

$(function(){
    $(document)
        .on('dragover', function(){
            filedrop_class('drag');
        })
        .on('dragleave', function(){
            filedrop_class();
        });

    $('#filedrop')
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
                temp: location.href.substr(location.href.lastIndexOf('/') + 1)
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
            drop: function() {
                filedrop_class();
            },
            uploadStarted: function(i, file, len){
                console.log('upload started',file);
                $('#status_list .file:eq(' + i + '):not(.list-group-item-success,list-group-item-danger) .status').text('Загрузка файла...');
            },
            uploadFinished: function(i, file, response, time) {
                console.log('upload started',file);
            },
            progressUpdated: function(i, file, progress) {
                // this function is used for large files and updates intermittently
                // progress is the integer value of file being uploaded percentage to completion

            },
            globalProgressUpdated: function(progress) {
                // progress for all the files uploaded on the current instance (percentage)
                // ex: $('#progress div').width(progress+"%");
            },
            //beforeEach: function(file) {},
            //beforeSend: function(file, i, done) {
            //    done();
            //}
        });
});