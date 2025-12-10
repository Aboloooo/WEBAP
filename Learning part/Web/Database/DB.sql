DROP DATABASE IF EXISTS PIF_2026;
CREATE DATABASE PIF_2026;
USE PIF_2026;

-- Role table
CREATE TABLE Role(
    AccessLevelID int PRIMARY KEY AUTO_INCREMENT,
    level VARCHAR(50) NOT NULL
);
insert into Role(level) values("Admin");
insert into Role(level) values("Dev");
insert into Role(level) values("User");

-- Users table (renamed from User)
CREATE TABLE Users(
    UserID int PRIMARY KEY AUTO_INCREMENT,
    Fullname VARCHAR(100) NOT NULL,
    Email VARCHAR(255) not null,
    Username VARCHAR(255),
    Password VARCHAR(255),
    AccessLevelID int NOT NULL,
    FOREIGN KEY (AccessLevelID) REFERENCES Role(AccessLevelID)
);

-- Station table
CREATE TABLE Station(
    Station_id INT PRIMARY KEY AUTO_INCREMENT,
    Serial_number VARCHAR(255) NOT NULL,
    Name VARCHAR(50),
    Description VARCHAR(255),
    Owner VARCHAR(255),
    FOREIGN KEY (Owner) REFERENCES Users(Username)
);

-- Collection table
CREATE TABLE Collection(
    Collection_id INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(50) NOT NULL,
    Description VARCHAR(255),
    Creator VARCHAR(255),
    FOREIGN KEY (Creator) REFERENCES Users(Username)
);

-- CollectionMeasurement table
CREATE TABLE CollectionMeasurement(
    CollectionMeasurement_id INT PRIMARY KEY AUTO_INCREMENT,
    Collection_id INT,
    FOREIGN KEY (Collection_id) REFERENCES Collection(Collection_id)
);

-- Measurement table
CREATE TABLE Measurement(
    Measurement_id INT PRIMARY KEY AUTO_INCREMENT,
    Timestamp DATETIME NOT NULL,
    Humidity VARCHAR(255),
    Air_pressure VARCHAR(255),
    Light_intensity VARCHAR(255),
    Air_quality VARCHAR(255),
    Station_id INT,
    CollectionMeasurement_id INT,
    FOREIGN KEY (Station_id) REFERENCES Station(Station_id),
    FOREIGN KEY (CollectionMeasurement_id) REFERENCES CollectionMeasurement(CollectionMeasurement_id)
);

-- FriendList table
CREATE TABLE FriendList(
    UserA VARCHAR(255) NOT NULL,
    UserB VARCHAR(255) NOT NULL,
    PRIMARY KEY (UserA, UserB),
    FOREIGN KEY (UserA) REFERENCES Users(Username),
    FOREIGN KEY (UserB) REFERENCES Users(Username)
);

-- CollectionShare table
CREATE TABLE CollectionShare(
    Collection_id INT NOT NULL,
    Shared_by VARCHAR(255) NOT NULL,
    Shared_with VARCHAR(255) NOT NULL,
    PRIMARY KEY (Collection_id, Shared_by, Shared_with),
    FOREIGN KEY (Collection_id) REFERENCES Collection(Collection_id),
    FOREIGN KEY (Shared_by) REFERENCES Users(Username),
    FOREIGN KEY (Shared_with) REFERENCES Users(Username)
);
