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

-- First create Users without the circular dependency
CREATE TABLE Users(
    UserID int PRIMARY KEY AUTO_INCREMENT,
    Public_UserID CHAR(36) UNIQUE NOT NULL DEFAULT (UUID()),    
    Fullname VARCHAR(100) NOT NULL,
    Email VARCHAR(255) not null,
    Username VARCHAR(255) UNIQUE NOT NULL,
    Password VARCHAR(255),
    AccessLevelID int NOT NULL,
    owner_of_station int,
    FOREIGN KEY (AccessLevelID) REFERENCES Role(AccessLevelID)
);

-- Station table
CREATE TABLE Station(
    Station_id INT PRIMARY KEY AUTO_INCREMENT,
    Serial_number VARCHAR(255) NOT NULL,
    Name VARCHAR(50),
    Description VARCHAR(255),
    Status ENUM('available', 'assigned') DEFAULT 'available',
    Owner_ID int
);

-- Now add the foreign key constraints after both tables exist
ALTER TABLE Users
ADD CONSTRAINT fk_user_station
    FOREIGN KEY (owner_of_station) REFERENCES Station(Station_id)
    ON DELETE SET NULL;

ALTER TABLE Station
ADD CONSTRAINT fk_station_owner
    FOREIGN KEY (Owner_ID) REFERENCES Users(UserID)
    ON DELETE SET NULL;

-- Collection table
CREATE TABLE Collection(
    Collection_id INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(50) NOT NULL,
    Description VARCHAR(255),
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    Creator_ID int,
    FOREIGN KEY (Creator_ID) REFERENCES Users(UserID)
);

-- CollectionContains table (renamed from what you called CollectionMeasurement)
CREATE TABLE CollectionContains (
    Collection_id INT NOT NULL,
    Measurement_id INT NOT NULL,
    PRIMARY KEY (Collection_id, Measurement_id)
);

-- Measurement table - Now this references CollectionContains indirectly through Collection_id
CREATE TABLE Measurement(
    Measurement_id INT PRIMARY KEY AUTO_INCREMENT,
    Timestamp DATETIME NOT NULL,
    Humidity VARCHAR(255),
    Air_pressure VARCHAR(255),
    Light_intensity VARCHAR(255),
    Air_quality VARCHAR(255),
    Station_id INT,
    Collection_id INT,
    FOREIGN KEY (Station_id) REFERENCES Station(Station_id),
    FOREIGN KEY (Collection_id) REFERENCES Collection(Collection_id)
);

-- Now update the CollectionContains foreign keys to reference the tables
ALTER TABLE CollectionContains
ADD FOREIGN KEY (Collection_id) REFERENCES Collection(Collection_id),
ADD FOREIGN KEY (Measurement_id) REFERENCES Measurement(Measurement_id);

-- FriendList table
CREATE TABLE FriendList(
    UserA_ID int NOT NULL,
    UserB_ID int NOT NULL,
    PRIMARY KEY (UserA_ID, UserB_ID),
    FOREIGN KEY (UserA_ID) REFERENCES Users(UserID),
    FOREIGN KEY (UserB_ID) REFERENCES Users(UserID)
);

-- CollectionShare table
CREATE TABLE CollectionShare(
    Collection_id INT NOT NULL,
    Shared_by int NOT NULL,
    Shared_with int NOT NULL,
    PRIMARY KEY (Collection_id, Shared_by, Shared_with),
    FOREIGN KEY (Collection_id) REFERENCES Collection(Collection_id),
    FOREIGN KEY (Shared_by) REFERENCES Users(UserID),
    FOREIGN KEY (Shared_with) REFERENCES Users(UserID)
);