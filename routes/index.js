var express = require('express');
var router = express.Router();
var phpjs = require('phpjs');
var async = require('async');
var form = require( 'express-form2' );
var field = form.field;
var yandexdisk = require('../lib/yadisk');


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
            res.render('event', {
                name: req.body.name
            });
        });
    });
});

module.exports = router;
