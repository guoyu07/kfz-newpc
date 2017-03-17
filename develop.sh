#!/bin/bash

project_dir=$(cd `dirname $0`; pwd)
project_name=$(basename $project_dir)
port=80
# 当deamon="true"时，将以守护进程的方式启动当前项目docker容器
deamon="false"

command=$1
if [ $# -lt 1 ];
then
        command=""
fi

start() {
    while getopts "p:d" arg
        do
            case $arg in
                 p)
                    port=$OPTARG
                    ;;
                 d)
                    deamon="true"
                    ;;
                 *) #不认识的参数
                    echo "unkonw argument $arg"
                    exit 1
                    ;;
            esac
        done

    # 检查docker容器是否正在运行
    if [ $(docker ps -f name=develop_${project_name} | wc -l) -gt 1 ] ; then
        echo "docker容器:develop_${project_name}正在运行, 是否进入容器？[Y/n]"
        read yon
        case $yon in
             n|N|no|No|NO)
                 exit 1
                 ;;
             ""|y|Y|yes|Yes|YES)
                 attach
                 exit
                 ;;
             *) #不认识的参数
                 echo "unkonw argument $yon"
                 exit 1
                 ;;
        esac
    fi

    echo "开始启动..."
    echo "准备获取最新的dongnan/droppn:1.0镜像..."
    # 获取最新的dongnan/droppn:1.0镜像
    docker pull dongnan/droppn:1.0

    echo "准备编译项目docker镜像..."
    # 编译项目的Dockerfile
    docker build $project_dir/docker -t develop/$project_name

    # 删除之前同名的旧容器，如果不存在会报错，可忽略
    docker rm develop_${project_name} > /dev/null 2>&1

    echo "准备运行项目docker容器..."
    # 运行docker容器
    run_opts="-p $port:80 --name develop_${project_name} -v $project_dir:/data/webroot/$project_name:rw -v $project_dir/vhost:/etc/nginx/servers develop/$project_name"
    #project_cmd="find /data/webroot/ -type f -name 'project.sh' | xargs bash "
    if [ $deamon = "true" ];
    then
        docker_cmd="supervisord -c /etc/supervisor/supervisord.conf -n"
        docker run -d $run_opts /bin/sh -c "$docker_cmd"
    else
        docker_cmd="supervisord -c /etc/supervisor/supervisord.conf && /bin/bash"
        docker run --rm -it $run_opts /bin/sh -c "$docker_cmd"
    fi
}

stop() {
    echo "准备停止项目docker容器..."
    docker stop develop_${project_name}
}

restart() {
    stop
    start $*
}

attach() {
    whereisnsenter=$(whereis nsenter)
    if [ "$whereisnsenter" = "nsenter:" ] ;
    then
        echo "你还没有安装nsenter,需要先安装nsenter才能使用"
        exit 1
    fi

    PID=$(docker inspect --format "{{.State.Pid}}" "develop_${project_name}")
    if [ -z "$PID" ]; then
        exit 1
    fi
    shift
    OPTS="--target $PID --mount --uts --ipc --net --pid --"
    if [ -z "$1" ]; then
        sudo nsenter $OPTS su - root
    else
        sudo nsenter $OPTS env --ignore-environment -- "$*"
    fi
}

case $command in
    "")
        start
        ;;
    "-p"|"-d")
        start $@
        ;;
    "start")
        $@
        ;;
    "stop")
        stop
        ;;
    "restart")
        $@
        ;;
    "attach")
        $@
        ;;
    "help"|"-h")
        echo "start     启动项目docker容器，此为默认命令，可省略"
        echo "  [-p xxxx] 指定端口绑定，此端口将与docker容器的80端口绑定，默认为'80'"
        echo "  [-d]    使用守护进程的方式启动docker容器"
        echo "stop      停止项目docker容器"
        echo "restart   重启项目docker容器（重启时，端口号将被重置为默认端口）"
        echo "  [-p xxxx] 指定端口绑定，此端口将与docker容器的80端口绑定，默认为'80'"
        echo "  [-d]    使用守护进程的方式重启docker容器"
        echo "attach    进入项目docker容器"
        echo "help|-h   查看帮助信息"
        ;;
    *)  #不认识的参数
        echo "unkonw argument $command, accept 'start', 'stop', 'restart', 'attach', 'help', '-h'"
        exit 1
        ;;
esac

# 目的是当docker运行出错时，保持错误提示，需要按下回车键后才继续
#read -p "按回车键继续..."
