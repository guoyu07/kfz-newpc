var fs = require("fs");
var project = require("./project");
var _ = require('underscore');
var crypto = require('crypto');
var exec = require('child_process').exec;

exec("find ./src/views -type f -name '*.json'", function (error, stdout, stderr) {
    var lines = stdout.split("\n");
    for (var i in lines) {
        if (!lines[i]) {
            continue;
        }
        var json = JSON.parse(fs.readFileSync(lines[i]));
        json = project.analyzeJson(json);
        console.log(json);
        var jsGroup = project.divideJsDeps(json.jsDeps);
        var md5 = crypto.createHash('md5');
        md5.update(jsGroup.libs.join(','));
        var libshash = md5.digest('hex');
        console.log(libshash);
        console.log(project.divideCssDeps(json.cssDeps));
    }
});

