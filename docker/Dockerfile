# vim:set ft=dockerfile:
FROM dongnan/droppn:1.0
MAINTAINER DongNan <dongyh@126.com>

COPY resolv.conf /etc/resolv.conf

# RUN what you want
RUN \
    usermod -a -G root web \
    && usermod -a -G root php \
    && mkdir -p /var/log/www/default \
    && ln -s /var/log/www /data/logs/www \
    && chmod 775 -R /var/log/www

RUN \
    DEBIAN_FRONTEND=noninteractive apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends vim dos2unix \
    && echo "export TERM=xterm" >> /root/.bashrc

RUN npm install --global gulp

COPY docker-entrypoint.sh /usr/local/bin/
RUN \
    dos2unix /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh \
    && ln -s /usr/local/bin/docker-entrypoint.sh / # backwards compat
ENTRYPOINT ["docker-entrypoint.sh"]

COPY php.ini /etc/php/php.ini

# dongnan/droppn:1.0 默认会执行"/bin/bash" 可以使用 -it 方式运行docker进入容器
#CMD ["/bin/bash"]