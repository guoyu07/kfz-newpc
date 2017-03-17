@echo off

set project_dir=%cd%
set project_name=%cd%
set port=80

:a
if not "%project_name:\=%"=="%project_name%" set project_name=%project_name:*\=%&goto a

echo ������Ҫִ�е����
echo ����0��port      ָ���󶨵Ķ˿ںţ�Ȼ��������Ŀdocker����
echo ����1��start     ������Ŀdocker����
echo ����2��stop      ֹͣ��Ŀdocker����
echo ����3��restart   ������Ŀdocker����������ʱ���˿ںŽ�������ΪĬ�϶˿ڣ�
echo ����03��prestart ָ���󶨵Ķ˿ںţ�Ȼ��������Ŀdocker����
echo ����4��attach    ������Ŀdocker����
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
echo ���뽫Ҫ�󶨵Ķ˿ںţ�Ĭ��Ϊ80
set /p port=
if not defined %port% call:port else call:start
goto:eof

:prestart
echo ���뽫Ҫ�󶨵Ķ˿ںţ�Ĭ��Ϊ80
set /p port=
if not defined %port% call:prestart else call:restart
goto:eof

:start
echo ��ʼ����...
echo ׼����ȡ���µ�dongnan/droppn:1.0����...
:: ��ȡ���µ�dongnan/droppn:1.0����
docker pull dongnan/droppn:1.0

echo ׼��������Ŀdocker����...
:: ������Ŀ��Dockerfile
docker build %project_dir%\docker -t develop/%project_name%

:: ɾ��֮ǰͬ������������������ڻᱨ���ɺ���
docker rm develop_%project_name%

echo ׼��������Ŀdocker����...
:: ����docker����
docker run -p %port%:80 --name develop_%project_name% -v %project_dir%:/data/webroot/%project_name%:rw -v %project_dir%\vhost:/etc/nginx/servers --rm -it develop/%project_name% /bin/sh -c "supervisord -c /etc/supervisor/supervisord.conf && /bin/bash"

:: pauseĿ���ǵ�docker���г���ʱ�����ִ�����ʾ����Ҫ�����������Źرմ���
pause
goto:eof

:stop
echo ׼��ֹͣ��Ŀdocker����...
docker stop develop_%project_name%
goto:eof

:restart
call:stop
call:start
goto:eof

:attach
docker exec -it develop_%project_name% /bin/bash
goto:eof
