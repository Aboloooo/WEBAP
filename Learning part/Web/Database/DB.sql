DROP DATABASE IF EXISTS PIF_2026;
CREATE DATABASE PIF_2026;
USE PIF_2026;

CREATE TABLE User(
    Username VARCHAR(255) PRIMARY KEY,
    First_name CHAR(50) NOT NULL,
    Last_name CHAR(50) NOT NULL,
    Role VARCHAR(50) NOT NULL
);
CREATE TABLE Station(
    Station_id int PRIMARY KEY AUTO_INCREMENT,
    Serial_number VARCHAR(255) NOT NULL,
    Name VARCHAR(50),
    Description CHAR(255),
    Owner VARCHAR(255),
    FOREIGN KEY (Owner) REFERENCES User(Username) 
);
CREATE TABLE Measurement(
    Measurement_id int PRIMARY KEY AUTO_INCREMENT,
    Timestamp DATETIME NOT NULL,
    Humidity VARCHAR(255),
    Air_pressure VARCHAR(255),
    Light_intensity VARCHAR(255),
    Air_quality VARCHAR(255),
    Station_id int,
    CollectionMeasurement_id int,
    FOREIGN KEY (Station_id) REFERENCES Station(Station_id),
    FOREIGN KEY (CollectionMeasurement_id) REFERENCES Measurement(CollectionMeasurement_id)
);
CREATE TABLE Collection(
    Collection_id int PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(50) NOT NULL,
    Description CHAR(255),
    Creator VARCHAR(255),
    FOREIGN KEY (Creator) REFERENCES User(Username) 
);
CREATE TABLE CollectionMeasurement(
    CollectionMeasurement_id int PRIMARY KEY,
    Collection_id int,

    FOREIGN KEY (Collection_id) REFERENCES Collection(Collection_id) 
);
CREATE TABLE FriendList(
/* Users freindship can be saved pair by pair */
/* 
    (UserA,UserB)
    (UserA,UserC)
    (UserC,UserA)
we can see each user is friend with who in the example above
 */
    UserA VARCHAR(255) NOT NULL,
    UserB VARCHAR(255) NOT NULL,
    PRIMARY KEY (UserA, UserB),
    FOREIGN KEY (UserA) REFERENCES User(Username),
    FOREIGN KEY (UserB) REFERENCES User(Username)
);
CREATE TABLE CollectionShare(
    Collection_id INT NOT NULL,
    Shared_by VARCHAR(255) NOT NULL,
    Shared_with VARCHAR(255) NOT NULL,
    PRIMARY KEY (Collection_id, Shared_by, Shared_with),
    FOREIGN KEY (Collection_id) REFERENCES Collection(Collection_id),
    FOREIGN KEY (Shared_by) REFERENCES User(Username),
    FOREIGN KEY (Shared_with) REFERENCES User(Username)
);