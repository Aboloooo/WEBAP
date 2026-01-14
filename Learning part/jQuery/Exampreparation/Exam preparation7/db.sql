create or replace database messages;

use messages;

create table users(
    userId int unique not null AUTO_INCREMENT,
    userName varchar(50) unique,
    CarPrice int,
    PRIMARY KEY (userId)
);

create table messages(
    msgId int unique not null AUTO_INCREMENT,
    messageText varchar(250),
    userId int,
    FOREIGN KEY (userId) REFERENCES users(userId),
    PRIMARY KEY (msgId)
);

insert into users(userName) VALUES("John"),("Angela"),("Santa"),("Test");