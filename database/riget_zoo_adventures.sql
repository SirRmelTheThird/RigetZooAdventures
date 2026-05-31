-- Riget Zoo Adventures Database
-- MySQL / MariaDB setup file

CREATE DATABASE IF NOT EXISTS riget_zoo_adventures;
USE riget_zoo_adventures;

-- Customers / users table
CREATE TABLE IF NOT EXISTS customers (
    CustomerID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    FirstName VARCHAR(50),
    LastName VARCHAR(50),
    Phone VARCHAR(20),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reward points table
CREATE TABLE IF NOT EXISTS reward_points (
    PointID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT NOT NULL,
    Points INT NOT NULL DEFAULT 0,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CustomerID) REFERENCES customers(CustomerID) ON DELETE CASCADE
);

-- Ticket types table
CREATE TABLE IF NOT EXISTS tickets (
    TicketID INT AUTO_INCREMENT PRIMARY KEY,
    TicketName VARCHAR(100) NOT NULL,
    TicketType ENUM('Standard', 'Premium') NOT NULL,
    AgeGroup ENUM('Adult', 'Child', 'Infant') NOT NULL,
    Price DECIMAL(10,2) NOT NULL,
    Description TEXT
);

-- Accommodation table
CREATE TABLE IF NOT EXISTS accommodations (
    AccommodationID INT AUTO_INCREMENT PRIMARY KEY,
    AccommodationName VARCHAR(100) NOT NULL,
    Description TEXT,
    PricePerNight DECIMAL(10,2) NOT NULL,
    Capacity INT NOT NULL,
    ImagePath VARCHAR(255)
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    OrderID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT NULL,
    TotalAmount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    OrderStatus ENUM('Pending', 'Paid', 'Cancelled') DEFAULT 'Pending',
    OrderDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CustomerID) REFERENCES customers(CustomerID) ON DELETE SET NULL
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    OrderItemID INT AUTO_INCREMENT PRIMARY KEY,
    OrderID INT NOT NULL,
    ItemType ENUM('Ticket', 'Accommodation') NOT NULL,
    TicketID INT NULL,
    AccommodationID INT NULL,
    Quantity INT NOT NULL DEFAULT 1,
    StartDate DATE NULL,
    EndDate DATE NULL,
    Price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (OrderID) REFERENCES orders(OrderID) ON DELETE CASCADE,
    FOREIGN KEY (TicketID) REFERENCES tickets(TicketID) ON DELETE SET NULL,
    FOREIGN KEY (AccommodationID) REFERENCES accommodations(AccommodationID) ON DELETE SET NULL
);

-- Sample ticket data
INSERT INTO tickets (TicketName, TicketType, AgeGroup, Price, Description) VALUES
('Standard Adult Ticket', 'Standard', 'Adult', 25.00, 'Standard zoo entry for adults.'),
('Standard Child Ticket', 'Standard', 'Child', 20.00, 'Standard zoo entry for children.'),
('Standard Infant Ticket', 'Standard', 'Infant', 0.00, 'Free entry for infants.'),
('Premium Adult Ticket', 'Premium', 'Adult', 50.00, 'Premium zoo entry for adults.'),
('Premium Child Ticket', 'Premium', 'Child', 40.00, 'Premium zoo entry for children.'),
('Premium Infant Ticket', 'Premium', 'Infant', 0.00, 'Free premium entry for infants.');

-- Sample accommodation data
INSERT INTO accommodations (AccommodationName, Description, PricePerNight, Capacity, ImagePath) VALUES
('Safari Lodge Retreat', 'A relaxing safari-themed lodge for families.', 120.00, 4, 'images/safari_lodge.jpg'),
('Jungle Bungalow', 'A comfortable bungalow close to the zoo attractions.', 100.00, 4, 'images/jungle_bungalow.jpg'),
('Tropical Treehouse Stay', 'A unique treehouse-style accommodation experience.', 150.00, 5, 'images/treehouse.jpg'),
('Savannah Safari Camp', 'A camp-style overnight stay for adventurous visitors.', 90.00, 3, 'images/savannah_camp.jpg');
