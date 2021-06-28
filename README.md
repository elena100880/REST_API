# REST_API
test exercise

Corresponding client's app is here: https://github.com/elena100880/UI_for_REST_API.git


### Initial task: 

Napisz dwie aplikacje, ktуre będą komunikować się ze sobą przy pomocy REST. Pierwsza aplikacja 
będzie źrуdłem danych, z ktуrego druga aplikacja (aplikacja kliencka) będzie korzystać. Dostęp do bazy 
ma tylko pierwsza aplikacja. 

Aplikacja kliencka ma umożliwić pobranie listy produktуw, ktуre: 
- Znajdują się na składzie
- Nie znajdują się na składzie
- Znajdują się na składzie w ilości większej niż 5 

Aplikacja kliencka powinna także umożliwiać edycję produktu, dodanie nowego i usunięcie.


Zadanie powinno być zrealizowane w formie bundle’a Symfony, ktуre będą dociągane przez 
Composera.


Technologie, na ktуrych powinno opierać się zadanie: 
- Symfony  
- baza SQL 

Struktura bazy:

Tabela items

<table>
  <tr>
    <td>id</td><td>name</td><td>amount</td>
  </tr>
  <tr>
    <td>1</td><td>Product 1</td><td>4</td>
  </tr>
  <tr>
    <td>2</td><td>Product 2</td><td>12</td>
  </tr>
  <tr>
    <td>3</td><td>Product 5</td><td>0</td>
  </tr>
  <tr>
    <td>4</td><td>Product 7</td><td>6</td>
  </tr>
  <tr>
    <td>5</td><td>Product 8</td><td>2</td>
  </tr>
</table>



***

### Launch with Docker in Linux:

Execute commands:
+ `git clone https://github.com/elena100880/UI_for_REST_API.git`
+ `git clone https://github.com/elena100880/REST_API.git`

in project folder:
+ `composer install`

then put docker-composer.yaml into folder, which is a Parent for both projects folders, and execute there:
+ `docker-compose up`

Then open localhost/index.php/<route_path_for_client_container> in your browser


***
### Dockerfile:

Docker-compose.yaml file in the project folder uses an official image php:8.0-apache.

Also, you can use my Dockerfile from rep: https://github.com/elena100880/dockerfile.

It includes php:8.0-apache official image (or you can change it to php:7.4-apache) and the installation of Composer, XDebug, Nano, some PHP extensions and enabling using mod rewrite (so you can skip index.php in URLs).

Execute the following commands:
+ `docker build . -t php:8.0-apache-xdebug` -  in the folder with Dockerfile.
+ `docker run -p -d 80:80 -v "$PWD":/var/www -w="/var/www" php:8.0-apache-xdebug composer install` - in the projects folders.

change 6th  and 14th lines in  docker-composer.yaml:
+ `image: php:8.0-apache` into `image: php:8.0-apache-xdebug`

change constant IP in ProductInStoreController in client app in line 20:
+ `private const IP = "api/index.php"` into `private const IP =  "api"`

put docker-composer.yaml into folder, which is a Parent for both projects folders, and execute there:
+ `docker-compose up`

Then open localhost/<route_path_for_client_container> in your browser.


***
### DataBase
For easier using  **/var/data.db** file is added to the REST_API repository.

SQLite DB is used.



***
### Pages in client's app:
  * **localhost/index.php/products/all** - list and filter for **products in store**  with links to pages:
  * add a product - **localhost/index.php/product/add**;
  * editing and deleting a particular product - **localhost/index.php/product/edit/{id}**;






