DROP DATABASE IF EXISTS PIF2025_2026;
CREATE DATABASE PIF2025_2026;
USE PIF2025_2026;
/* adding forign key and some table left */

CREATE TABLE User(
    Username VARCHAR(50) PRIMARY KEY,
    First_name CHAR(50) NOT NULL,
    Last_name CHAR(50) NOT NULL,
    Role VARCHAR(50) NOT NULL
);
CREATE TABLE Station(
    Station_id int PRIMARY KEY AUTO_INCREMENT,
    Serial_number VARCHAR(255) NOT NULL,
    Name VARCHAR(50),
    Description CHAR
);
CREATE TABLE Measurement(
    Measurement_id int PRIMARY KEY AUTO_INCREMENT,
    Timestamp DATETIME NOT NULL,
    Humidity VARCHAR(255),
    Air_pressure VARCHAR(255),
    Light_intensity VARCHAR(255),
    Air_quality VARCHAR(255)
);
CREATE TABLE Collection(
    Collection_id int PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(50) NOT NULL,
    Description CHAR
);
CREATE TABLE CollectionMeasurement();
CREATE TABLE Friendship();
CREATE TABLE CollectionShare();