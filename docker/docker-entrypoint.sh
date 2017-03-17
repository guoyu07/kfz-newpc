#!/bin/bash
set -eo pipefail
shopt -s nullglob

#执行项目脚本
for project_sh in `find /data/webroot/ -type f -name 'project.sh'` 
    do
        dir=$(dirname `readlink -f $project_sh`)
        cp $dir/project.sh $dir/.project.sh
        dos2unix $dir/.project.sh
        bash $dir/.project.sh start&
    done

exec "$@"