FROM ubuntu:20.04

RUN rm -rf /var/lib/apt/lists/*

#RUN apt update && apt dist-upgrade -y
#RUN apt install supervisor -y
#
#RUN apt install lsb-release ca-certificates apt-transport-https software-properties-common -y
#RUN add-apt-repository ppa:ondrej/php
#
#RUN apt update && apt install -y php8.1
#
#RUN apt install php8.1-pgsql -y \
#    php8.1-gd \
#    php8.1-fpm \
#    php8.1-curl \
#    php8.1-common \
#    php8.1-imap \
#    php8.1-redis \
#    php8.1-snmp \
#    php8.1-xml \
#    php8.1-zip \
#    php8.1-mbstring

COPY files/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
#COPY files/worker.conf /etc/supervisor/conf.d/worker.conf

#EXPOSE 8.18

CMD ["/usr/bin/supervisord"]