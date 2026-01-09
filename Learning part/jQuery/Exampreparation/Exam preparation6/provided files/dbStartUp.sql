create or replace database fruits;

use fruits;

create table fruits(
    fruitId int unique not null AUTO_INCREMENT,
    fruitName varchar(50) unique,
    availability int not null,
    PRIMARY KEY (fruitId)
);

insert into fruits(fruitName,availability) values ("Peach",20),("Apple",10),("Figues",4),("Cactus",1),("Oranges",0);
