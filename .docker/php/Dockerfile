FROM wearemobedia/php-fpm:latest

RUN apt-get update && apt-get install -y libc-client-dev libkrb5-dev && rm -r /var/lib/apt/lists/*
RUN apt-get install vim
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap
	
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	&& php -r "if (hash_file('sha384', 'composer-setup.php') === '906a84df04cea2aa72f40b5f787e49f22d4c2f19492ac310e8cba5b96ac8b64115ac402c8cd292b8a03482574915d1a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
	&& php composer-setup.php
	&& php -r "unlink('composer-setup.php');"
	&& mv composer.phar /usr/local/bin/composer

	
RUN export PHP_INI_DIR="/usr/local/etc/php"