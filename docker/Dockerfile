FROM php:7-cli

ARG EDIT_USER
ARG EDIT_GROUP

RUN addgroup --gid ${EDIT_GROUP} --system ldl
RUN adduser --gid ${EDIT_GROUP} --shell /bin/bash --uid ${EDIT_USER} --system ldl

RUN apt-get update -y && apt-get install -y libpng-dev \
sudo \
libssl-dev \
libcurl4-openssl-dev \
pkg-config \
zlib1g-dev \
netcat \
locales-all \
libmagickwand-6.q16-dev \
libzip-dev \
git \
build-essential \
libicu-dev \
autoconf \
gcc \
make \
libjson-c-dev \
net-tools \
curl \
vim \
psmisc \
procps \
psutils \
libonig-dev \
g++ \
wget \
nano \
vim;

RUN usermod -a -G sudo ldl
COPY ./etc/sudoers /etc/sudoers

RUN docker-php-ext-install intl &&  \
    docker-php-ext-install pdo_mysql && \
    docker-php-ext-install mbstring && \
    docker-php-ext-install zip && \
    docker-php-ext-install gd && \
    docker-php-ext-install curl && \
    docker-php-ext-install bcmath && \
    docker-php-ext-install sockets && \
    docker-php-ext-install pcntl && \
    docker-php-ext-install mysqli

CMD /bin/start-container
