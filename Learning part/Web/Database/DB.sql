DROP DATABASE IF EXISTS PIF2025_2026;
CREATE DATABASE PIF2025_2026;
USE PIF2025_2026;
/* adding forign key and some table left */

CREATE TABLE User(
    Username VARCHAR(255) PRIMARY KEY,
    First_name CHAR(50) NOT NULL,
    Last_name CHAR(50) NOT NULL,
    Role VARCHAR(50) NOT NULL,
);
CREATE TABLE Station(
    Station_id int PRIMARY KEY AUTO_INCREMENT,
    Serial_number VARCHAR(255) NOT NULL,
    Name VARCHAR(50),
    Description CHAR,
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
    Station_id VARCHAR(255),
    FOREIGN KEY (Station_id) REFERENCES Station(Station_id) 
);
CREATE TABLE Collection(
    Collection_id int PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(50) NOT NULL,
    Description CHAR,
    Measurement_id VARCHAR(255),
    FOREIGN KEY (Measurement_id) REFERENCES Measurement(Measurement_id) 
);
CREATE TABLE CollectionMeasurement(
    Username VARCHAR(255),
    Collection_id int,
    FOREIGN KEY (Username) REFERENCES User(Username),
    FOREIGN KEY (Collection_id) REFERENCES Collection(Collection_id) 
);
CREATE TABLE Friendship();
CREATE TABLE CollectionShare();