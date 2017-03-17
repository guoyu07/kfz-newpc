/*!
 * 项目工具
 *
 * @author dongnan 
 */

'use strict';

var fs = require("fs");
var _ = require('underscore');
var project = {};
/**
 * 分析包依赖关系
 * @param {Object} json
 * @returns {Object}
 */
project.analyzeJson = function (json) {
    json.jsDeps || (json.jsDeps = []);
    json.cssDeps || (json.cssDeps = []);
    for (var i in json.jsDeps) {
        if (json.jsDeps[i].indexOf('src/') === 0) {
            continue;
        }
        if (json.jsDeps[i].indexOf('/') === 0) {
            json.jsDeps[i] = 'src' + json.jsDeps[i];
        } else {
            json.jsDeps[i] = 'src/' + json.jsDeps[i];
        }
    }
    for (var i in json.cssDeps) {
        if (json.cssDeps[i].indexOf('src/') === 0) {
            continue;
        }
        if (json.cssDeps[i].indexOf('/') === 0) {
            json.cssDeps[i] = 'src' + json.cssDeps[i];
        } else {
            json.cssDeps[i] = 'src/' + json.cssDeps[i];
        }
    }
    if (json.spriteDeps) {
        for (var i in json.spriteDeps) {
            json.cssDeps.push('build/css/icon_' + json.spriteDeps[i] + '.css');
        }
    }
    if (json.type === 'page') {
        if (!json.name) {
            console.log(json);
            throw new Error("缺少属性name，作者：" + json.author);
        }
        if (!json.module) {
            throw new Error(json.name + "格式错误，缺少属性module，作者：" + json.author);
        }
        var module = json.module.toLowerCase();
        var Module = module.substr(0, 1).toUpperCase() + module.substr(1);
        var prefix = 'src/modules/' + Module;
        //自动添加页面js
        if (fs.existsSync(prefix + '/views/' + json.name + '.js')) {
            json.jsDeps.push(prefix + '/views/' + json.name + '.js');
        }
        //自动添加页面css
        if (fs.existsSync(prefix + '/views/' + json.name + '.less')) {
            json.cssDeps.push(prefix + '/views/' + json.name + '.less');
        } else if (fs.existsSync(prefix + '/views/' + json.name + '.css')) {
            json.cssDeps.push(prefix + '/views/' + json.name + '.css');
        }
    }
    if (json.widgetDeps) {
        json.widgetDeps.forEach(function (widget) {
            if (widget.indexOf('/') < 1 || widget.indexOf('/') === widget.length - 1) {
                throw new Error("widget:" + widget + "名称格式错误，正确的格式为'widgetType/widgetName'");
            }
            var wArr = widget.split('/');
            var widgetPrefix = 'src/widgets/' + wArr[0] + '/' + wArr[1] + '/' + wArr[0];
            var widgetJsonFile = widgetPrefix + '.json';
            if (fs.existsSync(widgetJsonFile)) {
                var widgetJson = JSON.parse(fs.readFileSync(widgetJsonFile));
                widgetJson = project.analyzeJson(widgetJson);
                //自动添加模块js
                if (fs.existsSync(widgetPrefix + '.js')) {
                    widgetJson.jsDeps.push(widgetPrefix + '.js');
                }
                json.jsDeps = widgetJson.jsDeps.concat(json.jsDeps);
                //自动添加模块css
                if (fs.existsSync(widgetPrefix + '.less')) {
                    widgetJson.cssDeps.push(widgetPrefix + '.less');
                } else if (fs.existsSync(widgetPrefix + '.css')) {
                    widgetJson.cssDeps.push(widgetPrefix + '.css');
                }
                json.cssDeps = widgetJson.cssDeps.concat(json.cssDeps);
            }
        });
    }
    json.jsDeps = _.uniq(json.jsDeps);
    json.cssDeps = _.uniq(json.cssDeps);
    return json;
};

/**
 * 将js依赖文件切分成多组
 * @param {Array} deps
 * @returns {Object}
 */
project.divideJsDeps = function (deps) {
    var group = {libs: [], deps: []};
    deps.forEach(function (dep) {
        if (dep.indexOf('src/libs/') >= 0) {
            group.libs.push(dep);
        } else {
            group.deps.push(dep);
        }
    });
    return group;
};

/**
 * 将css依赖文件切分成多组
 * @param {Array} deps
 * @returns {Object}
 */
project.divideCssDeps = function (deps) {
    var group = {libs: [], deps: []};
    deps.forEach(function (dep) {
        if (dep.indexOf('src/css/') >= 0 || dep.indexOf('src/less/') >= 0 || dep.indexOf('build/css/') >= 0 || dep.indexOf('src/libs/') >= 0) {
            group.libs.push(dep);
        } else {
            group.deps.push(dep);
        }
    });
    return group;
};

/**
 * 返回widget的phtml内容
 * @param {String} widget
 * @returns {String}
 */
project.widgetPhtml = function (widget) {
    if (widget.indexOf('/') < 1 || widget.indexOf('/') === widget.length - 1) {
        throw new Error("widget:" + widget + "名称格式错误，正确的格式为'widgetType/widgetName'");
    }
    var wArr = widget.split('/');
    var widgetPrefix = 'src/widgets/' + wArr[0] + '/' + wArr[1] + '/' + wArr[0];
    var widgetHtml = widgetPrefix + '.phtml';
    if (!fs.existsSync(widgetHtml)) {
        throw new Error("widget:" + widget + "的phtml文件不存在，文件路径:" + widgetHtml);
    }
    return fs.readFileSync(widgetHtml);
};

/**
 * Expose `project`
 */

module.exports = project;