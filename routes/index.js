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


    });
});

module.exports = router;
