/**
 * Created by ClienDDev team (clienddev.ru)
 * Developer: Artur Atnagulov (atnartur)
 */

var YandexDisk = require('yandex-disk').YandexDisk;

module.exports = function(){
    return new YandexDisk(global.config.api.yandex_disk.ya_token);
}
