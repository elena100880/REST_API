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

in each project folder (composer and docker-compose should be installed):
+ `composer install`

then put a file **docker-composer_simple.yaml** and a folder **Dockerfile** into folder, which is a Parent-folder for both projects' folders, and execute there:
+ `docker-compose -f docker-compose_simple.yaml up`

Then open localhost/<route_path_for_client_container> in your browser.


***
### Dockerfile:

Docker-compose_simple.yaml uses a Dockerfile_simple with official images (php:8.0-apache, mysql, phpmyadmin), pdo_mysql and mod rewrite (so you can skip index.php in URLs).

Also, you can use my own and more complicated **Dockerfile_mine**. It additionally includes the installation of Composer, XDebug for VSC, Nano, some PHP extensions.

Take notice that **building image from Dockerfile_mine will take more time**.

In order to do so put a file **docker-composer_mine.yaml** and a folder **Dockerfile**(if not done yet) into folder, which is a Parent-folder for both projects' folders, and execute there:
+ `sudo chmod 777 ./REST_API/my_sql/ -R` - if project was initially launched with another Dockerfie
+ `docker-compose -f docker-compose_mine.yaml up`

Then open localhost/<route_path_for_client_container> in your browser.


***
### DataBase
For easier using MySQL Database `/REST_API/my_sql/sql_data/mysql_db` is added to the repository.

PHPMyAdmin panel is available at **localhost:8080**.

Credentials for phpmyadmin:
+ login - root 
+ password - 1008



***
### Pages in client's app:
  * **localhost/products/all** - list and filter for **products in store**  with links to pages:
  * add a product - **localhost/product/add**;
  * editing and deleting a particular product - **localhost/product/edit/{id}**;






