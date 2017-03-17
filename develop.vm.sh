#!/bin/bash

# 初始化变量
project_dir=$(cd `dirname $0`; pwd)
project_name=$(basename $project_dir)
port=80

trap '[ "$?" -eq 0 ] || read -p "Looks like something went wrong in step ´$STEP´... Press any key to continue..."' EXIT

# TODO: I'm sure this is not very robust.  But, it is needed for now to ensure
# that binaries provided by Docker Toolbox over-ride binaries provided by
# Docker for Windows when launching using the Quickstart.
STEP="Looking for Docker Toolbox"
if [ -z "$DOCKER_TOOLBOX_INSTALL_PATH" ]; then
  echo "Docker Toolbox is not installed. Please re-run the Toolbox Installer and try again."
  exit 1
else
  DOCKER_TOOLBOX_INSTALL_PATH="$(echo $DOCKER_TOOLBOX_INSTALL_PATH | sed 's/\\/\//g' | sed 's/\(\w\):/\/\1/1')/"
fi

export PATH="$DOCKER_TOOLBOX_INSTALL_PATH:$PATH"
VM=${DOCKER_MACHINE_NAME-default}
DOCKER_MACHINE_PATH="$DOCKER_TOOLBOX_INSTALL_PATH/docker-machine.exe"
DOCKER_MACHINE="docker-machine.exe"

STEP="Looking for vboxmanage.exe"
if [ ! -z "$VBOX_MSI_INSTALL_PATH" ]; then
  VBOXMANAGE="${VBOX_MSI_INSTALL_PATH}VBoxManage.exe"
else
  VBOXMANAGE="${VBOX_INSTALL_PATH}VBoxManage.exe"
fi

BLUE='\033[1;34m'
GREEN='\033[0;32m'
NC='\033[0m'

#clear all_proxy if not socks address
if  [[ $ALL_PROXY != socks* ]]; then
  unset ALL_PROXY
fi
if  [[ $all_proxy != socks* ]]; then
  unset all_proxy
fi

if [ ! -f "${DOCKER_MACHINE_PATH}" ]; then
  echo "Docker Machine is not installed. Please re-run the Toolbox Installer and try again."
  exit 1
fi

if [ ! -f "${VBOXMANAGE}" ]; then
  echo "VirtualBox is not installed. Please re-run the Toolbox Installer and try again."
  exit 1
fi

start_machine () {
    echo "准备启动docker machine $VM ..."
    "${VBOXMANAGE}" list vms | grep \""${VM}"\" &> /dev/null
    VM_EXISTS_CODE=$?

    STEP="检查docker machine $VM 是否存在"
    echo "${STEP}..."
    if [ $VM_EXISTS_CODE -eq 1 ]; then
      "${DOCKER_MACHINE}" rm -f "${VM}" &> /dev/null || :
      rm -rf ~/.docker/machine/machines/"${VM}"
      #set proxy variables if they exists
      if [ "${HTTP_PROXY}" ]; then
        PROXY_ENV="$PROXY_ENV --engine-env HTTP_PROXY=$HTTP_PROXY"
      fi
      if [ "${HTTPS_PROXY}" ]; then
        PROXY_ENV="$PROXY_ENV --engine-env HTTPS_PROXY=$HTTPS_PROXY"
      fi
      if [ "${NO_PROXY}" ]; then
        PROXY_ENV="$PROXY_ENV --engine-env NO_PROXY=$NO_PROXY"
      fi
      "${DOCKER_MACHINE}" create -d virtualbox $PROXY_ENV "${VM}"
      "${DOCKER_MACHINE}" stop "${VM}"
    fi

    STEP="检查docker machine $VM 的状态"
    echo "${STEP}..."
    VM_STATUS="$(${DOCKER_MACHINE} status ${VM} 2>&1)"
    if [ "${VM_STATUS}" != "Running" ]; then
      # 共享项目目录到docker虚拟机
      "${VBOXMANAGE}" sharedfolder remove ${VM} --name "$project_name"
      "${VBOXMANAGE}" sharedfolder add ${VM} --name "$project_name" --hostpath "$(echo $project_dir | sed 's/\//\\/g' | sed 's/\\\(\w\)/\1:/1')" --automount
      # 共享项目vhost到docker虚拟机
      "${VBOXMANAGE}" sharedfolder remove ${VM} --name "nginx_servers"
      "${VBOXMANAGE}" sharedfolder add ${VM} --name "nginx_servers" --hostpath "$(echo $project_dir | sed 's/\//\\/g' | sed 's/\\\(\w\)/\1:/1')\vhost" --automount
      # 设置共享目录允许使用软链
      "${VBOXMANAGE}" setextradata ${VM} VBoxInternal2/SharedFoldersEnableSymlinksCreate/$project_name 1
      # 启动docker虚拟机
      "${DOCKER_MACHINE}" start "${VM}"
      yes | "${DOCKER_MACHINE}" regenerate-certs "${VM}"
    fi

    STEP="设置docker machine $VM 的环境"
    echo "${STEP}..."
    eval "$(${DOCKER_MACHINE} env --shell=bash --no-proxy ${VM})"

    STEP="docker machine $VM 启动成功"
    echo "${STEP}..."
    clear
    cat << EOF


                        ##         .
                  ## ## ##        ==
               ## ## ## ## ##    ===
           /"""""""""""""""""\___/ ===
      ~~~ {~~ ~~~~ ~~~ ~~~~ ~~~ ~ /  ===- ~~~
           \______ o           __/
             \    \         __/
              \____\_______/

EOF
    echo -e "${BLUE}docker${NC} is configured to use the ${GREEN}${VM}${NC} machine with IP ${GREEN}$(${DOCKER_MACHINE} ip ${VM})${NC}"
    echo "For help getting started, check out the docs at https://docs.docker.com"
    echo
}

stop_machine () {
    echo "准备停止docker machine $VM ..."
    STEP="检查docker machine $VM 的状态"
    echo "${STEP}..."
    VM_STATUS="$(${DOCKER_MACHINE} status ${VM} 2>&1)"
    if [ "${VM_STATUS}" == "Running" ]; then
      "${DOCKER_MACHINE}" stop "${VM}"
    fi
}

docker () {
  MSYS_NO_PATHCONV=1 docker.exe "$@"
  #ssh -i ~/.docker/machine/machines/${VM}/id_rsa docker@$(${DOCKER_MACHINE} ip ${VM}) "docker $@"
}
export -f docker

container_start () {
    # 启动docker machine
    start_machine
    
    # 检查docker容器是否正在运行
    if [ $(docker ps -f name=develop_${project_name} | wc -l) -gt 1 ] ; then
        echo "docker容器:develop_${project_name}正在运行, 是否进入容器？[Y/n]"
        read yon
        case $yon in
             n|N|no|No|NO)
                 return
                 ;;
             ""|y|Y|yes|Yes|YES)
                 container_attach
                 return
                 ;;
             *) #不认识的参数
                 echo "unkonw argument $yon"
                 return
                 ;;
        esac
    fi
    
    # 检查是否存在nsenter，不存在则安装
    STEP="检查是否存在nsenter，不存在则安装"
    echo "检查是否存在nsenter，不存在则安装..."
    ssh -i ~/.docker/machine/machines/${VM}/id_rsa docker@$(${DOCKER_MACHINE} ip ${VM}) "[ -f /var/lib/boot2docker/nsenter ] || sudo docker run --rm -v /var/lib/boot2docker/:/target jpetazzo/nsenter"
    ssh -i ~/.docker/machine/machines/${VM}/id_rsa docker@$(${DOCKER_MACHINE} ip ${VM}) "sudo ln -sf /var/lib/boot2docker/docker-enter /usr/bin/docker-enter && sudo chmod +x /usr/bin/docker-enter"
    
    # 准备启动develop环境
    STEP="准备启动develop环境"
    
    echo "准备启动docker容器..."
    echo "在docker虚拟机上 mount /data/webroot/$project_name"
    ssh -i ~/.docker/machine/machines/${VM}/id_rsa docker@$(${DOCKER_MACHINE} ip ${VM}) "sudo mkdir -p /data/webroot/$project_name"
    ssh -i ~/.docker/machine/machines/${VM}/id_rsa docker@$(${DOCKER_MACHINE} ip ${VM}) "sudo mount -t vboxsf $project_name /data/webroot/$project_name"
    
    echo "在docker虚拟机上 mount /etc/nginx/servers"
    ssh -i ~/.docker/machine/machines/${VM}/id_rsa docker@$(${DOCKER_MACHINE} ip ${VM}) "sudo mkdir -p /etc/nginx/servers"
    ssh -i ~/.docker/machine/machines/${VM}/id_rsa docker@$(${DOCKER_MACHINE} ip ${VM}) "sudo mount -t vboxsf nginx_servers /etc/nginx/servers"
    
    echo "准备获取最新的dongnan/droppn:1.0镜像..."
    # 获取最新的dongnan/droppn:1.0镜像
    docker pull dongnan/droppn:1.0

    echo "准备编译项目docker镜像..."
    # 编译项目的Dockerfile
    docker build ./docker -t develop/$project_name

    # 删除之前同名的旧容器，如果不存在会报错，可忽略
    docker rm develop_${project_name} &> /dev/null

    echo "准备运行项目docker容器..."
    # 运行docker容器
    run_opts="-p $port:80 --name develop_${project_name} -v /data/webroot/$project_name:/data/webroot/$project_name:rw -v /etc/nginx/servers:/etc/nginx/servers develop/$project_name"
    #project_cmd="find /data/webroot/ -type f -name 'project.sh' | xargs bash"
    docker_cmd='/bin/sh -c "supervisord -c /etc/supervisor/supervisord.conf -n"'
    #docker run -d $run_opts $docker_cmd
    ssh -i ~/.docker/machine/machines/${VM}/id_rsa docker@$(${DOCKER_MACHINE} ip ${VM}) "docker run -d $run_opts $docker_cmd"
    
}

container_port () {
    echo "输入将要绑定的端口号，默认为80"
    read port
    if [ -z $port ] ; then
        container_port
    fi
    container_start
}

container_stop() {
    echo "准备停止项目docker容器..."
    docker stop develop_${project_name}
    # 停止docker machine
    stop_machine
}

container_restart() {
    container_stop
    container_start
}

container_prestart() {
    echo "输入将要绑定的端口号，默认为80"
    read port
    if [ -z $port ] ; then
        container_prestart
    fi
    container_restart
}

container_attach() {
    ssh -i ~/.docker/machine/machines/${VM}/id_rsa docker@$(${DOCKER_MACHINE} ip ${VM}) -t "sudo /var/lib/boot2docker/docker-enter develop_${project_name}"
}

self_help () {
    echo "请输入要执行的命令："
    echo "输入0或port      指定绑定的端口号，然后启动项目docker容器"
    echo "输入1或start     启动项目docker容器"
    echo "输入2或stop      停止项目docker容器"
    echo "输入3或restart   重启项目docker容器（重启时，端口号将被重置为默认端口）"
    echo "输入03或prestart 指定绑定的端口号，然后重启项目docker容器"
    echo "输入4或attach    进入项目docker容器"
}

self_start () {
    self_help
    read command
    self_exec "$command"
}

self_exec () {
    command=$1
    case $command in
    "0"|"port")
      container_port;;
    "1"|"start")
      container_start;;
    "2"|"stop")
      container_stop;;
    "3"|"restart")
      container_restart;;
    "03"|"prestart")
      container_prestart;;
    "4"|"attach")
      container_attach;;
    *)
      echo "未识别命令:$command"
      self_start
      ;;
    esac
}

if [ $# -eq 0 ]; then
    # 启动
    self_start
    exec "$BASH" --login -i
else
    self_exec "$*"
    exec "$BASH" --login -i
fi
