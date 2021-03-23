FROM php:7.4-cli

RUN apt-get update
RUN apt-get install -y zip unzip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

#COPY . /app
#RUN composer install

CMD ["tail", "-f", "/dev/null"]
