FROM debian:jessie

ENV DEBIAN_FRONTEND="noninteractive" \
    TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && \
    echo $TZ > /etc/timezone && \
    apt-get update -qq && \
    echo 'en_US.UTF-8 UTF-8' > /etc/locale.gen && \
    apt-get install -qqy \
        locales \
        gnupg && \
    apt-get install --no-install-recommends -qqy \
        nginx \
        less && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ENV LANG="en_US.UTF-8" \
    LC_ALL="en_US.UTF-8" \
    LANGUAGE="en_US.UTF-8"

RUN ln -s /etc/nginx/sites-available/task.local.conf /etc/nginx/sites-enabled/task.local && \
    rm /etc/nginx/sites-enabled/default

RUN usermod -u 1000 www-data

WORKDIR /var/www/project
EXPOSE 80

RUN echo "upstream php-upstream { server php:9000; }" > /etc/nginx/conf.d/upstream.conf

ADD nginx.conf /etc/nginx/
ADD task.local.conf /etc/nginx/sites-available/

CMD ["nginx"]
