# Al clonar el repositorio:

Aquí están los pasos que tu compañero debería seguir después de clonar el repositorio:

## 1º Crear un archivo .env:

Este archivo debe contener todas las variables de entorno necesarias para el proyecto. Puede basarse en el archivo .env que proporcionaste, pero debe reemplazar los valores de las variables con los valores apropiados para su entorno local.

Luego, debe **editar el archivo .env** y establecer los valores correctos para su entorno.

## 2º Instalar las dependencias de Composer: Esto instalará todas las dependencias PHP necesarias para el proyecto.

**composer require jms/serializer-bundle**

**composer require friendsofsymfony/rest-bundle**

**composer require symfony/maker-bundle**

**composer require symfony/orm-pack --with-all-dependencies**

**composer require lexik/jwt-authentication-bundle**

**composer install**

## 3º Generar las claves JWT: Como las claves JWT no están en el repositorio, tu compañero necesitará generar las suyas propias.

Si no tienes instalado OPENSSL en el ordenador, instalalo (enlace para windows:)

**Instalar OPENSSL:** https://slproweb.com/products/Win32OpenSSL.html

**php bin/console lexik:jwt:generate-keypair**

Si falla ese comando, hay que crear manualmente la carpeta jwt en config (/config/jwt) y ejecutar lo siguiente:
Durante la generación, se le pedirá que proporcione una frase de contraseña. Esta debe ser la misma que la que se establece en JWT_PASSPHRASE en el archivo .env (Si el comando de arriba funciona esto no es necesario, lo hace automáticamente)

**mkdir config/jwt**

**openssl genrsa -out config/jwt/private.pem -aes256 4096**

**openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem**

## 4º Crear la base de datos y las tablas: Si estás utilizando Doctrine, puedes crear la base de datos y las tablas utilizando los comandos de consola de Doctrine:

**php bin/console doctrine:database:create**

**php bin/console make:migration**

**php bin/console doctrine:migrations:migrate**

## 5º Para permitir CORS con nuestro proyecto de frontend:

**composer require nelmio/cors-bundle**

Y modificamos el fichero /config/packages/nelmio_cors.yaml:

nelmio_cors:
defaults:
origin_regex: false
allow_origin: ['http://localhost:3001']
allow_methods: ['GET', 'OPTIONS', 'POST', 'PUT', 'PATCH', 'DELETE']
allow_headers: ['Content-Type', 'Authorization']
expose_headers: ['Link']
max_age: 3600
paths:
'^/api/': ~

## 6º Finalmente, iniciar el servidor de desarrollo de Symfony:

**symfony server:start**

## Enlace de ayuda:

https://www.binaryboxtuts.com/php-tutorials/symfony-6-json-web-tokenjwt-authentication/

## A funcionar!!
