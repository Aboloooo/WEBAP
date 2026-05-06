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
    Fullname VARCHAR(100) NOT NULL,
    Email VARCHAR(255) not null,
    Username VARCHAR(255) UNIQUE NOT NULL,
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
    Status ENUM('available', 'assigned') DEFAULT 'available',
    Owner_id INT,
    FOREIGN KEY (Owner_id) REFERENCES Users(UserID)
);
insert into Station(Serial_number, Name, Description) values ("WST-202601-001" ,"Station 1" ,"This station can be changed after registration");
insert into Station(Serial_number, Name, Description) values ("WST-202601-002" ,"Station 2" ,"This station can be changed after registration");
insert into Station(Serial_number, Name, Description) values ("WST-202601-003" ,"Station 3" ,"This station can be changed after registration");
insert into Station(Serial_number, Name, Description) values ("WST-202601-004" ,"Station 4" ,"This station can be changed after registration");
insert into Station(Serial_number, Name, Description) values ("WST-202601-005" ,"Station 5" ,"This station can be changed after registration");

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
 
CREATE TABLE Measurement(
    Measurement_id INT PRIMARY KEY AUTO_INCREMENT,
    Timestamp DATETIME NOT NULL,
    Temperature DECIMAL(10,4),
    Humidity DECIMAL(10,4),
    Air_pressure DECIMAL(10,4),
    Light_intensity DECIMAL(10,4),
    Air_quality INT,
    Station_id INT,
    FOREIGN KEY (Station_id) REFERENCES Station(Station_id)
);
INSERT INTO Measurement (Timestamp, Temperature, Humidity, Air_pressure, Light_intensity, Air_quality, Station_id) 
VALUES 
('2026-01-18 08:00:00', 22.0, 45.0, 1013.0, 350.0, 1, 1),
('2026-01-18 10:00:00', 24.0, 42.0, 1012.0, 850.0, 1, 1),
('2026-01-18 12:00:00', 26.0, 38.0, 1011.0, 1200.0, 1, 1),
('2026-01-18 14:00:00', 25.0, 40.0, 1010.0, 1100.0, 2, 2),
('2026-01-18 16:00:00', 23.0, 48.0, 1009.0, 600.0, 2, 2),
('2026-01-18 18:00:00', 21.0, 55.0, 1009.0, 50.0, 3, 3),
('2026-01-18 20:00:00', 19.0, 60.0, 1010.0, 0.0, 3, 3),
('2026-01-18 22:00:00', 18.0, 65.0, 1011.0, 0.0, 5, 5),
('2026-01-18 04:00:00', 25.0, 40.0, 1010.0, 1100.0, 1, 1),
('2026-01-18 06:00:00', 23.0, 48.0, 1009.0, 600.0, 2, 2),
('2026-01-18 08:00:00', 21.0, 55.0, 1009.0, 50.0, 3, 3),
('2026-01-18 20:00:00', 19.0, 60.0, 1010.0, 0.0, 4, 4);
-- Now update the CollectionContains foreign keys to reference the tables
ALTER TABLE CollectionContains
ADD FOREIGN KEY (Collection_id) REFERENCES Collection(Collection_id),
ADD FOREIGN KEY (Measurement_id) REFERENCES Measurement(Measurement_id);

-- FriendList table
CREATE TABLE FriendList(
    UserA_ID int NOT NULL,
    UserB_ID int NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    requested_by int NOT NULL default 0,
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

CREATE TABLE Message(
    Message_ID INT PRIMARY KEY AUTO_INCREMENT,
    Message_content VARCHAR(255),
    Sender_ID INT,
    Group_id INT DEFAULT NULL,
    Message_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Sender_ID) REFERENCES Users(UserID)
);
insert into Users(Fullname, Email, Username, Password, AccessLevelID) values
('Alice Smith', 'alice@example.com', 'user1', '$2y$10$J.VKHbFZuj24IoW01.FQt./tW/QOlb4jfJL.zl6oPSF6dwvYwyBTm', 3),
('Bob Johnson', 'bob@example.com', 'user2', '$2y$10$ecYj0gAohASVeFmB1eyucuKtMVZsXx1Yp35OF5ubOO738XI1jOtDq', 3),
('Charlie Brown', 'charlie@example.com', 'user3', '$2y$10$zYZdMrbaWSX90h5z7oUY4eU5nUvSevVcKkDhVnTPKDPtGu.REVLgW', 3);

-- MessageRead table: tracks which users have read each message
CREATE TABLE MessageRead (
    message_id INT NOT NULL,
    user_id    INT NOT NULL,
    read_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (message_id, user_id),
    FOREIGN KEY (message_id) REFERENCES Message(Message_ID),
    FOREIGN KEY (user_id)    REFERENCES Users(UserID)
);

CREATE TABLE ChatGroup (
    Group_id INT PRIMARY KEY AUTO_INCREMENT,
    Group_name VARCHAR(50) NOT NULL,
    Creator_id INT NOT NULL,
    Created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Creator_id) REFERENCES Users(UserID)
);
-- GroupMember table (creator is also added as a member)
CREATE TABLE GroupMember (
    Group_id INT NOT NULL,
    User_id INT NOT NULL,
    PRIMARY KEY (Group_id, User_id),
    FOREIGN KEY (Group_id) REFERENCES ChatGroup(Group_id),
    FOREIGN KEY (User_id) REFERENCES Users(UserID)
);

-- NotificationType table
CREATE TABLE NotificationType (
    NotificationType_ID INT PRIMARY KEY AUTO_INCREMENT,
    type_key VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description VARCHAR(255)
);

INSERT INTO NotificationType (type_key, display_name, description) VALUES
('friend_request', 'Friendship Request', 'Sent when a user receives a friend request.'),
('collection_share', 'Collection Share', 'Sent when a collection is shared with a user.'),
('message', 'Message', 'Sent when a user receives a chat or group message.'),
('public_announcement', 'Public Announcement', 'Sent when an admin publishes a public message.');

-- Notifications table
CREATE TABLE Notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    notification_type_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(UserID),
    FOREIGN KEY (notification_type_id) REFERENCES NotificationType(NotificationType_ID)
);

ALTER TABLE Message
ADD FOREIGN KEY (Group_id) REFERENCES ChatGroup(Group_id);

