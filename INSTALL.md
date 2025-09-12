# Working on project

   - Run `make` to reload containers

# Installation

  - Install [docker](https://docs.docker.com/get-docker/)
  - Install [docker-compose](https://docs.docker.com/compose/install/)
  - Install [Symfony Local Web Server](https://symfony.com/doc/current/setup/symfony_server.html)
  - Install [PhpStorm URL Handler](https://github.com/sanduhrs/phpstorm-url-handler)
  - Add [PHP-PPA repository](ppa:ondrej/php) with add-apt-repository
  - Install everything with `make install`

## If auto-install fails
  - Install PHP and needed library with `make env`, (see [Makefile](Makefile) for more details)
  - Install Git Hooks `make hooks`
  - Install Composer dependencies with `make dependencies`
  - Then run `make` to reload containers

# Create nginx vHost

## fix permission issues

 - Add your USER to the www-data group

 - Edit `/etc/php/7.4/fpm/pool.d/www.conf` and replace :
 
       user = ww-data
       group = ww-data
 
 - with
 
       user = {YOUR_LINUX_USERNAME}
       group = {YOUR_LINUX_USERNAME}

## Create vHost

- Create the file `/etc/nginx/sites-available/ms-base` according to the template `config/nginx/vhost.conf` or `config/nginx/vhost-with-xdebug.conf`
- Enable the vHost using `ls -s /etc/nginx/sites-available/ms-base /etc/nginx/sites-enabled/ms-base`
- Reload nginx to apply configuration : `sudo service nginx reload`
