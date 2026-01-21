create or replace database MiniWebShop;

use MiniWebShop;

create table Items(
    itemId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    itemName VARCHAR(50) NOT NULL UNIQUE,
    stock int not null
);

create table Orders(
    OrderId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    itemId int not null,
    quantity int not null
);
insert into items(itemName,stock) VALUES("Apples",2);
insert into items(itemName,stock) VALUES("Cereals",20);
insert into items(itemName,stock) VALUES("Cars",21);
insert into items(itemName,stock) VALUES("Boardgames",11);