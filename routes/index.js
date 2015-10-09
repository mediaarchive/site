var express = require('express');
var router = express.Router();
var phpjs = require('phpjs');
var async = require('async');
var multiparty = require('multiparty');
var path = require('path');
var telegram = require('node-telegram-bot-api');
var form = require( 'express-form2' );
var field = form.field;
var yandexdisk = require('../lib/yadisk');

var t;
router.use(function(req, res, next){
    t = new telegram(global.config.api.telegram.api_key);
    next();
});

router.get('/', function(req, res, next) {
    res.render('index');
});



router.post('/', form(
    field('name').trim().required(),
    field('author_name').trim().required(),
    field('contact').trim(),
    field('text').trim()
), function(req, res){
    var yadisk = yandexdisk();
    var date = new Date();
    yadisk.cd(global.config.api.yandex_disk.base_dir);

    var full_path = date.getFullYear() + '/' + phpjs.date('m') + '/' + phpjs.date('d') + '/' + req.body.name + '/';

    // создание директорий
    var dirs = [
        date.getFullYear() + '/',
        date.getFullYear() + '/' + phpjs.date('m') + '/',
        date.getFullYear() + '/' + phpjs.date('m') + '/' + phpjs.date('d') + '/',
        full_path
    ];

    async.each(dirs, function(dir, callback) {
        yadisk.mkdir(dir, function(err, res){
            if(err)
                console.error(err);

            callback();
        });
    }, function(err){
        console.log('dirs created');

        yadisk.cd(full_path);

        async.parallel([
            // info.txt
            function(callback){
                if(req.body.text == '')
                    return callback();

                var old_content = '';

                async.series([
                    function(callback){
                        yadisk.readFile('info.txt', 'utf8', function(err, res){
                            if(err)
                                console.log(err);

                            console.log(res);
                            if(res != '')
                                old_content = res + "\n\n";

                            callback();
                        });
                    },
                    function(callback){
                        yadisk.writeFile('info.txt', old_content + req.body.text, 'utf8', function(err){
                            if(err)
                                console.log(err);

                            callback();
                        })
                    }
                ], function(){
                    callback();
                });
            },

            // data.json
            function(callback){
                var content;

                async.series([
                    function(callback){
                        yadisk.readFile('data.json', 'utf8', function(err, res){
                            if(err)
                                console.log(err);

                            console.log(res);
                            if(typeof res != 'undefined' && res != '')
                                content = JSON.parse(res);

                            callback();
                        });
                    },
                    function(callback){
                        if(typeof content == 'undefined') {
                            content = {
                                authors: []
                            };
                        }

                        content.authors.push({
                            name: req.body.author_name,
                            contact: req.body.contact
                        });

                        yadisk.writeFile('data.json', JSON.stringify(content), 'utf8', function(err){
                            if(err)
                                console.log(err);

                            callback();
                        })
                    }
                ], function(){
                    callback();
                });
            }
        ], function(){
            res.cookie('full_path', full_path);
            res.cookie('author_name', req.body.author_name);

            res.render('event', {
                name: req.body.name
            });
        });
    });
});




router.post('/upload', form(
    field('full_path').trim().required(),
    field('author_name').trim().required()
), function(req, res){
    var form = new multiparty.Form();

    // парсим форму
    form.parse(req, function(err, fields, files) {
        if(err) {
            console.log(err);
            return res.json({error: 'file uploading', obj: err});
        }

        res.json({status: 'ok'});

        var file = files.file[0];

        if(!file)
            return;

        var full_path = fields.full_path[0];
        var author_name = fields.author_name[0];
        var extra_path = '';


        var yadisk = yandexdisk();
        yadisk.cd(global.config.api.yandex_disk.base_dir);

        var file_name = decodeURIComponent(file.originalFilename);
        var ext = path.extname(file_name).toLowerCase();

        console.log(file, full_path);

        async.series([
            function(callback){
                yadisk.exists(full_path, function(err, res){
                    if(err)
                        console.log(err);

                    if(!res) // если папка не найдена
                        return; // выходим

                    yadisk.cd(full_path);

                    callback();
                });
            },
            function(callback) {
                if(ext == '.png' || ext == '.jpg' || ext == '.jpeg') {
                    yadisk.mkdir('фото/', function (err, res) {
                        yadisk.mkdir('фото/' + author_name, function (err, res) {
                            callback();
                        });
                    });
                }
                else
                    callback();
            },
            function(callback){
                if(ext == '.png' || ext == '.jpg' || ext == '.jpeg')
                    yadisk.cd('фото/' + author_name);

                console.log(22, yadisk, ext, 'фото/' + author_name);

                yadisk.exists(file.originalFilename, function(err, res){
                    if(err)
                        console.log(err);


                    if(res) // если файл найден
                        file_name = path.basename(file.originalFilename) + ' (' + (new Date()).getTime() + ')' + path.extname(file.originalFilename);

                    callback();
                });
            },
            function(callback){
                yadisk.uploadFile(file.path, file_name, function(err, res){
                    if(err)
                        return console.log('file uploading', err, res);

                    console.log('file uploading ok', file.originalFilename);
                    callback();
                });
            }
        ]);
    });
});


router.post('/save', form(
    field('full_path').trim().required(),
    field('author_name').trim().required(),
    field('name').trim().required()
), function(req, res){
    res.send({status: 'ok'});

    t.sendMessage(
        global.config.api.telegram.chat_id,
        "Медиаархив: " + req.body.author_name + " cоздал новое мероприятие '" + req.body.name + "'. (" + req.body.full_path + ")"
    );
});

router.post('/cancel', form(
    field('full_path').trim().required(),
    field('author_name').trim().required(),
    field('name').trim().required()
), function(req, res){
    res.send({status: 'ok'});

    var yadisk = yandexdisk();
    yadisk.cd(req.body.full_path);
    yadisk.remove('фото/' + req.body.author_name);

    t.sendMessage(
        global.config.api.telegram.chat_id,
        "Медиаархив: " + req.body.author_name + " отменил создание мероприятия '" + req.body.name + "'. (" + req.body.full_path + ")"
    );
});

router.get('/end', function(req, res, next) {
    res.render('end', {
        cancel: typeof req.query.cancel != 'undefined'
    });
});

module.exports = router;
