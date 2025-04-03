-- Create the database
CREATE DATABASE IF NOT EXISTS fikraa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fikraa;

-- Create individuals table
CREATE TABLE IF NOT EXISTS individuals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    birth_date DATE,
    gender ENUM('Male', 'Female') NOT NULL,
    residence VARCHAR(100),
    marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed'),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    national_id VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create social_status_info table
CREATE TABLE IF NOT EXISTS social_status_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    individual_id INT NOT NULL,
    occupation VARCHAR(100),
    occupation_type VARCHAR(100),
    occupation_place VARCHAR(100),
    study_year VARCHAR(20),
    speciality VARCHAR(100),
    school VARCHAR(100),
    quran ENUM('Yes', 'No'),
    retirement_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (individual_id) REFERENCES individuals(id) ON DELETE CASCADE
);

-- Create additional_social_status table
CREATE TABLE IF NOT EXISTS additional_social_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    individual_id INT NOT NULL,
    status VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (individual_id) REFERENCES individuals(id) ON DELETE CASCADE
);

-- Create families table
CREATE TABLE IF NOT EXISTS families (
    id INT AUTO_INCREMENT PRIMARY KEY,
    family_name VARCHAR(100) NOT NULL,
    father_id INT,
    mother_id INT,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (father_id) REFERENCES individuals(id) ON DELETE SET NULL,
    FOREIGN KEY (mother_id) REFERENCES individuals(id) ON DELETE SET NULL
);

-- Create associations table
CREATE TABLE IF NOT EXISTS associations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create memberships table
CREATE TABLE IF NOT EXISTS memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    individual_id INT NOT NULL,
    association_id INT NOT NULL,
    membership_type VARCHAR(50),
    start_date DATE,
    end_date DATE,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (individual_id) REFERENCES individuals(id) ON DELETE CASCADE,
    FOREIGN KEY (association_id) REFERENCES associations(id) ON DELETE CASCADE
);

-- Create family_relations table
CREATE TABLE IF NOT EXISTS family_relations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    individual1_id INT NOT NULL,
    individual2_id INT NOT NULL,
    relation_type ENUM('Parent', 'Child', 'Sibling', 'Spouse') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (individual1_id) REFERENCES individuals(id) ON DELETE CASCADE,
    FOREIGN KEY (individual2_id) REFERENCES individuals(id) ON DELETE CASCADE
); 