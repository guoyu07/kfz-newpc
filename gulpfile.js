var gulp = require('gulp'),
        os = require('os'),
        crypto = require('crypto'),
        fs = require("fs"),
        _ = require("underscore"),
        fileinclude = require('gulp-file-include'),
        htmlreplace = require('gulp-html-replace'),
        project = require('./project'),
        execSync = require('child_process').execSync,
        less = require('gulp-less'),
        concat = require('gulp-concat'),
        cssmin = require('gulp-cssmin'),
        uglify = require('gulp-uglify'),
        md5 = require('gulp-md5-plus'),
        //gutil = require('gulp-util'),
        //fileinclude = require('gulp-file-include'),
        //clean = require('gulp-clean'),
        //base64 = require('gulp-css-base64'),
        //imagemin = require('gulp-imagemin'),
        spritesmith = require('gulp.spritesmith');

var tasks = {dev: [], prod: [], sprites: [], jslibshash: {dev: [], prod: []}, csslibshash: {dev: [], prod: []}};

/**
 * 构建项目模块
 * @param {String} module
 * @param {String} env
 * @returns {undefined}
 */
var gulpModule = function (module, env) {
    //初始化任务数组
    tasks[env] || (tasks[env] = []);
    module = module.toLowerCase();
    var Module = module.substr(0, 1).toUpperCase() + module.substr(1);
    if (!fs.existsSync('src/modules/' + Module)) {
        throw new Error("目录不存在：src/modules/" + Module);
    }
    var ret = execSync("find ./src/modules/" + Module + "/views -type f -name '*.json'", {encoding: 'utf8'});
    var lines = ret.split("\n");
    var moduleTasks = [];
    var moduleTask = {};
    for (var idx in lines) {
        if (!lines[idx]) {
            continue;
        }
        (function (idx, module, Module) {
            var moduleVars = {};
            var myTask = {};
            myTask.spriteDeps = [];
            var json = JSON.parse(fs.readFileSync(lines[idx]));
            json = project.analyzeJson(json);
            json.baseUrl = '/';
            //var name = json.name.substr(json.name.lastIndexOf('/') + 1);
            moduleVars.namePath = '';
            moduleVars.namePrefix = json.name.replace(/\//g, "_");
            if (json.name.lastIndexOf('/') > 0) {
                moduleVars.namePath = json.name.substr(0, json.name.lastIndexOf('/'));
            }
            moduleVars.jsDeps = [], moduleVars.cssDeps = [];
            //构建js
            var jsGroup = project.divideJsDeps(json.jsDeps);
            if (jsGroup.libs.length > 0) {
                var MD5 = crypto.createHash('md5');
                MD5.update(jsGroup.libs.join(','));
                moduleVars.libsHash = MD5.digest('hex');
                //相同的libs库只构建一次
                if (_.indexOf(tasks.jslibshash[env], moduleVars.libsHash) === -1) {
                    tasks.jslibshash[env].push(moduleVars.libsHash);
                    myTask.jslibs = env + ':jslibs:' + moduleVars.libsHash;
                    moduleTasks.push(myTask.jslibs);
                    gulp.task(myTask.jslibs, function (done) {
                        var pipe = gulp.src(jsGroup.libs)
                                .pipe(concat(moduleVars.libsHash + '.js'));
                        if (env === 'prod') {
                            pipe = pipe.pipe(uglify());
                        }
                        pipe.pipe(gulp.dest('webroot/libs/')).on('end', done);
                    });
                }
                moduleVars.jsDeps.push(json.baseUrl + 'libs/' + moduleVars.libsHash + '.js');
            }
            if (jsGroup.deps.length > 0) {
                myTask.js = env + ':module:' + module + ':js:' + json.name;
                moduleTasks.push(myTask.js);
                gulp.task(myTask.js, function (done) {
                    var pipe = gulp.src(jsGroup.deps)
                            .pipe(concat(moduleVars.namePrefix + '.js'));
                    if (env === 'prod') {
                        pipe = pipe.pipe(uglify());
                    }
                    pipe = pipe.pipe(gulp.dest('build/modules/' + Module + '/js/')).on('end', done);
                });
                moduleVars.jsDeps.push(json.baseUrl + 'modules/' + module + '/js/' + moduleVars.namePrefix + '.js');
            }

            //构建css
            if (json.spriteDeps) {
                for (var i in json.spriteDeps) {
                    myTask.spriteDeps.push('sprite:' + json.spriteDeps[i]);
                }
            }
            var cssGroup = project.divideCssDeps(json.cssDeps);
            if (cssGroup.libs.length) {
                var MD5 = crypto.createHash('md5');
                MD5.update(cssGroup.libs.join(','));
                moduleVars.cssHash = MD5.digest('hex');
                //相同的libs库只构建一次
                if (_.indexOf(tasks.csslibshash[env], moduleVars.cssHash) === -1) {
                    tasks.csslibshash[env].push(moduleVars.cssHash);
                    myTask.csslibs = env + ':csslibs:' + moduleVars.cssHash;
                    moduleTasks.push(myTask.csslibs);
                    gulp.task(myTask.csslibs, myTask.spriteDeps, function (done) {
                        var pipe = gulp.src(cssGroup.libs)
                                .pipe(less())
                                .pipe(concat(moduleVars.cssHash + '.css'));
                        if (env === 'prod') {
                            pipe = pipe.pipe(cssmin());
                        }
                        pipe.pipe(gulp.dest('webroot/css/')).on('end', done);
                    });
                }
                moduleVars.cssDeps.push(json.baseUrl + 'css/' + moduleVars.cssHash + '.css');
            }
            if (cssGroup.deps.length) {
                myTask.css = env + ':module:' + module + ':css:' + json.name;
                moduleTasks.push(myTask.css);
                gulp.task(myTask.css, function (done) {
                    var pipe = gulp.src(cssGroup.deps)
                            .pipe(less())
                            .pipe(concat(moduleVars.namePrefix + '.css'));
                    if (env === 'prod') {
                        pipe = pipe.pipe(cssmin());
                    }
                    pipe = pipe.pipe(gulp.dest('build/modules/' + Module + '/css/')).on('end', done);
                });
                moduleVars.cssDeps.push(json.baseUrl + 'modules/' + module + '/css/' + moduleVars.namePrefix + '.css');
            }

            //构建html
            myTask.html = env + ':module:' + module + ':html:' + json.name;
            moduleTasks.push(myTask.html);
            gulp.task(myTask.html, function (done) {
                var pipe = gulp.src('src/modules/' + Module + '/views/' + json.name + '.html')
                        .pipe(htmlreplace({
                            'head-css': {
                                src: moduleVars.cssDeps,
                                tpl: '<link rel="stylesheet" href="%s">'
                            },
                            'footer-js': {
                                src: moduleVars.jsDeps,
                                tpl: '<script src="%s"></script>'
                            }
                        }))
                        .pipe(fileinclude({
                            prefix: '@@',
                            basepath: 'src/'
                        }));
                if (module === 'index') {
                    pipe.pipe(gulp.dest('application/views/' + moduleVars.namePath));
                } else {
                    pipe.pipe(gulp.dest('application/modules/' + Module + '/views/' + moduleVars.namePath));
                }
                pipe.on('end', done);
            });
            myTask.phtml = env + ':module:' + module + ':phtml:' + json.name;
            moduleTasks.push(myTask.phtml);
            gulp.task(myTask.phtml, function (done) {
                var pipe = gulp.src('src/modules/' + Module + '/views/' + json.name + '.phtml')
                        .pipe(htmlreplace({
                            'head-css': {
                                src: moduleVars.cssDeps,
                                tpl: '<link rel="stylesheet" href="%s">'
                            },
                            'footer-js': {
                                src: moduleVars.jsDeps,
                                tpl: '<script src="%s"></script>'
                            }
                        }))
                        .pipe(fileinclude({
                            prefix: '@@',
                            basepath: 'src/'
                        }));
                if (module === 'index') {
                    pipe.pipe(gulp.dest('application/views/' + moduleVars.namePath));
                } else {
                    pipe.pipe(gulp.dest('application/modules/' + Module + '/views/' + moduleVars.namePath));
                }
                pipe.on('end', done);
            });

            //资源文件md5
            //将js加上6位md5,并修改html中的引用路径
            if (myTask.js) {
                myTask.jsmd5 = env + ':module:' + module + ':jsmd5:' + json.name;
                moduleTasks.push(myTask.jsmd5);
                gulp.task(myTask.jsmd5, [myTask.js, myTask.html], function (done) {
                    var pipe = gulp.src('build/modules/' + Module + '/js/*.js');
                    if (env === 'prod') {
                        if (module === 'index') {
                            pipe.pipe(md5(6, ['application/views/' + moduleVars.namePath + '/*.phtml', 'application/views/' + moduleVars.namePath + '/*.html']));
                        } else {
                            pipe.pipe(md5(6, ['application/modules/' + Module + '/views/' + moduleVars.namePath + '/*.phtml', 'application/modules/' + Module + '/views/' + moduleVars.namePath + '/*.html']));
                        }
                    }
                    pipe.pipe(gulp.dest('webroot/modules/' + module + '/js/'))
                            .on('end', done);
                });
            }

            //将css加上6位md5，并修改html中的引用路径
            if (myTask.css) {
                myTask.cssmd5 = env + ':module:' + module + ':cssmd5:' + json.name;
                moduleTasks.push(myTask.cssmd5);
                gulp.task(myTask.cssmd5, [myTask.css, myTask.html], function (done) {
                    var pipe = gulp.src('build/modules/' + Module + '/css/*.css');
                    if (env === 'prod') {
                        if (module === 'index') {
                            pipe.pipe(md5(6, ['application/views/' + moduleVars.namePath + '/*.phtml', 'application/views/' + moduleVars.namePath + '/*.html']));
                        } else {
                            pipe.pipe(md5(6, ['application/modules/' + Module + '/views/' + moduleVars.namePath + '/*.phtml', 'application/modules/' + Module + '/views/' + moduleVars.namePath + '/*.html']));
                        }
                    }
                    pipe.pipe(gulp.dest('webroot/modules/' + module + '/css/'))
                            .on('end', done);
                });
            }
        })(idx, module, Module);
    }

    tasks[env] = tasks[env].concat(moduleTasks);
    //复制资源文件
    //将图片拷贝到目标目录
    moduleTask.copyImg = env + ':copy:img:' + module;
    tasks[env].push(moduleTask.copyImg);
    gulp.task(moduleTask.copyImg, function (done) {
        gulp.src('src/modules/' + Module + '/img/**')
                .pipe(gulp.dest('webroot/modules/' + module + '/img/'))
                .on('end', done);
    });

    //如果不是默认模块,在模块目录新增.gitingore文件,将views目录忽略掉
    if (module !== 'index') {
        moduleTask.copyGitingore = env + ':copy:gitingore:' + module;
        tasks[env].push(moduleTask.copyGitingore);
        gulp.task(moduleTask.copyGitingore, function (done) {
            gulp.src('application/.gitignore')
                    .pipe(gulp.dest('application/modules/' + Module + '/'))
                    .on('end', done);
        });
    }
};

gulp.task('clear:build', function (done) {
    //先清空build目录
    execSync("rm -rf ./build/*");
    done && done.call(this);
});

gulp.task('clear:views', function (done) {
    //先清空build目录
    execSync("rm -rf application/views/* application/modules/*/views/*");
    done && done.call(this);
});

gulp.task('clear:webroot', function (done) {
    //清空webroot目录
    execSync("rm -rf ./webroot/css ./webroot/fonts ./webroot/img ./webroot/js ./webroot/modules ./webroot/widgets");
    done && done.call(this);
});

gulp.task('clear', ['clear:build', 'clear:views', 'clear:webroot']);

//合并图标
var sprites = execSync("cd src/sprites/ && ls -d */", {encoding: 'utf8'});
sprites.split("\n").forEach(function (sprite) {
    if (!sprite) {
        return;
    }
    sprite = sprite.substr(0, sprite.lastIndexOf('/'));
    (function (sprite) {
        gulp.task('sprite:' + sprite, function (done) {
            //var timestamp = +new Date();
            gulp.src('src/sprites/' + sprite + '/*.png')
                    .pipe(spritesmith({
                        imgName: 'img/icon_' + sprite + '.png',
                        cssName: 'css/icon_' + sprite + '.css',
                        padding: 10
                    })).pipe(gulp.dest('build/'))
                    .on('end', done);
        });
    })(sprite);
});

//先清空build目录
//execSync("rm -rf ./build/*");
//清空webroot目录
//execSync("rm -rf ./webroot/css ./webroot/img ./webroot/js ./webroot/modules ./webroot/widgets");

//根据modules(对应后端index模块)构建
//根据json配置构建页面
gulpModule('index', 'dev');
gulpModule('index', 'prod');
gulpModule('admin', 'dev');
gulpModule('admin', 'prod');
gulpModule('zhuanti', 'dev');
gulpModule('zhuanti', 'prod');

//处理widgets的php模板
gulp.task('widgets:copy:phtml', function (done) {
    gulp.src(['src/widgets/*/*/*.phtml'])
            .pipe(fileinclude({
                prefix: '@@',
                basepath: 'src/'
            }))
            .pipe(gulp.dest('application/views/_widgets_/'))
            .on('end', done);
});

//处理widgets的html模板
gulp.task('widgets:copy:html', function (done) {
    gulp.src(['src/widgets/*/*/*.html'])
            .pipe(fileinclude({
                prefix: '@@',
                basepath: 'src/'
            }))
            .pipe(gulp.dest('webroot/widgets/'))
            .on('end', done);
});

//复制widgets
gulp.task('widgets:copy:asset', function (done) {
    gulp.src(['src/widgets/*/*/*.css', 'src/widgets/*/*/img/*', 'src/widgets/*/*/*.js'])
            .pipe(gulp.dest('webroot/widgets/'))
            .on('end', done);
});

//处理widgets的less,less生成的css会覆盖源css
gulp.task('widgets:build:dev', ['widgets:copy:asset'], function (done) {
    gulp.src(['src/widgets/*/*/*.less'])
            .pipe(less())
            .pipe(gulp.dest('webroot/widgets/'))
            .on('end', done);
});
gulp.task('widgets:build:prod', ['widgets:copy:asset'], function (done) {
    gulp.src(['src/widgets/*/*/*.less'])
            .pipe(less())
            .pipe(cssmin())
            .pipe(gulp.dest('webroot/widgets/'))
            .on('end', done);
});

tasks.dev.push('dev:widgets');
gulp.task('dev:widgets', ['widgets:copy:phtml', 'widgets:copy:html', 'widgets:copy:asset', 'widgets:build:dev']);
tasks.prod.push('prod:widgets');
gulp.task('prod:widgets', ['widgets:copy:phtml', 'widgets:copy:html', 'widgets:copy:asset', 'widgets:build:prod']);

tasks.dev.push('copy:fonts');
tasks.prod.push('copy:fonts');
//复制fonts
gulp.task('copy:fonts', function (done) {
    gulp.src(['src/fonts/**'])
            .pipe(gulp.dest('webroot/fonts/'))
            .on('end', done);
});

//复制图片到目标目录
gulp.task('dev:copy:img', tasks.dev, function (done) {
    gulp.src(['build/img/**', 'src/img/**'])
            .pipe(gulp.dest('webroot/img/'))
            .on('end', done);
});

//发布
gulp.task('default', tasks.prod, function (done) {
    gulp.src(['build/img/**', 'src/img/**'])
            .pipe(gulp.dest('webroot/img/'))
            .on('end', done);
});

//开发
gulp.task('dev', ['dev:copy:img'], function (done) {
    execSync('/bin/sh -c "dos2unix project.sh && ./project.sh chown"');
    done && done.call(this);
});

//监听文件变化,自动构建
gulp.task('watch', function (done) {
    gulp.watch('src/**', ['dev'])
            .on('end', done);
});