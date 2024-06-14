# Guía para Configurar el Proyecto

Sigue estos pasos después de clonar el repositorio para configurar y utilizar el proyecto correctamente:

## 1º Crear un archivo .env:

Crea un archivo .env en la raíz del proyecto y define todas las variables de entorno necesarias. 

## 2º Instalar las dependencias de Composer

Instala todas las dependencias PHP necesarias para el proyecto ejecutando el siguiente comando:

**composer install**

## 3º Generar las claves JWT

Como las claves JWT no están en el repositorio, necesitarás generar las tuyas propias.

1. Si no tienes instalado OpenSSL, instálalo desde aquí para Windows. https://slproweb.com/products/Win32OpenSSL.html
2. Luego, ejecuta el siguiente comando para generar las claves JWT:

**php bin/console lexik:jwt:generate-keypair**

3. Si el comando anterior falla, crea manualmente la carpeta jwt en config y ejecuta los siguientes comandos:

**mkdir config/jwt**

**openssl genrsa -out config/jwt/private.pem -aes256 4096**

**openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem**

Durante la generación, se te pedirá que proporciones una frase de contraseña. Esta debe ser la misma que la establecida en JWT_PASSPHRASE en el archivo .env.

## 4º Crear la base de datos y las tablas

**php bin/console doctrine:database:create**

**php bin/console make:migration**

**php bin/console doctrine:migrations:migrate**

## 5º Para permitir CORS con nuestro proyecto de frontend utilizamos nelmio/cors-bundle:

El paquete nelmio/cors-bundle se instala automáticamente al ejecutar composer install, ya que está en el composer.json. Modifica el archivo /config/packages/nelmio_cors.yaml con la siguiente configuración:

nelmio_cors:
  defaults:
    origin_regex: true
    allow_origin: ['^http://localhost:5173$', '^http://35.181.213.37:80']
    allow_methods: ['GET', 'OPTIONS', 'POST', 'PUT', 'PATCH', 'DELETE']
    allow_headers: ['Content-Type', 'Authorization']
    expose_headers: ['Link']
    allow_credentials: true
    max_age: 3600
  paths:
    '^/api':
      origin_regex: true
      allow_origin: ['^http://localhost:5173$', '^http://35.181.213.37:80']
      allow_methods: ['GET', 'OPTIONS', 'POST', 'PUT', 'PATCH', 'DELETE']
      allow_headers: ['Content-Type', 'Authorization']
      max_age: 3600
      allow_credentials: true

## 6º Iniciar el servidor de desarrollo de Symfony

**symfony server:start**

## Enlace de ayuda:

https://www.binaryboxtuts.com/php-tutorials/symfony-6-json-web-tokenjwt-authentication/

## !Listo para funcionar!
