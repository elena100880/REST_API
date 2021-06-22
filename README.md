# REST_API
test exercise


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


### Dockerfile:



