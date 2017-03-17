@echo off

set project_dir=%cd%
set project_name=%cd%
set port=80

:a
if not "%project_name:\=%"=="%project_name%" set project_name=%project_name:*\=%&goto a

echo 请输入要执行的命令：
echo 输入0或port      指定绑定的端口号，然后启动项目docker容器
echo 输入1或start     启动项目docker容器
echo 输入2或stop      停止项目docker容器
echo 输入3或restart   重启项目docker容器（重启时，端口号将被重置为默认端口）
echo 输入03或prestart 指定绑定的端口号，然后重启项目docker容器
echo 输入4或attach    进入项目docker容器
set /p command=

if %command%==port call:port
if %command%==0 call:port
if %command%==start call:start
if %command%==1 call:start
if %command%==stop call:stop
if %command%==2 call:stop
if %command%==restart call:restart
if %command%==3 call:restart
if %command%==prestart call:prestart
if %command%==03 call:prestart
if %command%==attach call:attach
if %command%==4 call:attach

goto:eof

:port
echo 输入将要绑定的端口号，默认为80
set /p port=
if not defined %port% call:port else call:start
goto:eof

:prestart
echo 输入将要绑定的端口号，默认为80
set /p port=
if not defined %port% call:prestart else call:restart
goto:eof

:start
echo 开始启动...
echo 准备获取最新的dongnan/droppn:1.0镜像...
:: 获取最新的dongnan/droppn:1.0镜像
docker pull dongnan/droppn:1.0

echo 准备编译项目docker镜像...
:: 编译项目的Dockerfile
docker build %project_dir%\docker -t develop/%project_name%

:: 删除之前同名的容器，如果不存在会报错，可忽略
docker rm develop_%project_name%

echo 准备运行项目docker容器...
:: 运行docker容器
docker run -p %port%:80 --name develop_%project_name% -v %project_dir%:/data/webroot/%project_name%:rw -v %project_dir%\vhost:/etc/nginx/servers --rm -it develop/%project_name% /bin/sh -c "supervisord -c /etc/supervisor/supervisord.conf && /bin/bash"

:: pause目的是当docker运行出错时，保持错误提示，需要按下任意键后才关闭窗口
pause
goto:eof

:stop
echo 准备停止项目docker容器...
docker stop develop_%project_name%
goto:eof

:restart
call:stop
call:start
goto:eof

:attach
docker exec -it develop_%project_name% /bin/bash
goto:eof
