CREATE DATABASE gp1ma_db;
USE gp1ma_db;

CREATE TABLE users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    profile_pic VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE groups (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_by INT(11) UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE


) ENGINE=InnoDB;

CREATE TABLE group_members (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id INT(11) UNSIGNED NOT NULL,
    user_id INT(11) UNSIGNED NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (group_id, user_id)
) ENGINE=InnoDB;

CREATE TABLE messages (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id INT(11) UNSIGNED NOT NULL,
    user_id INT(11) UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE friends (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id INT(11) UNSIGNED NOT NULL,
    receiver_id INT(11) UNSIGNED NOT NULL,
    status ENUM('pending', 'accepted') DEFAULT 'pending',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE private_chats (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id INT(11) UNSIGNED NOT NULL,
    receiver_id INT(11) UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;



ALTER TABLE groups ADD COLUMN profile_pic VARCHAR(255) DEFAULT NULL;


CREATE TABLE group_messages (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id INT(11) UNSIGNED NOT NULL,
    user_id INT(11) UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (created_at)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS uploaded_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(255) NOT NULL,
    is_folder BOOLEAN DEFAULT 0,       
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP


);

ALTER TABLE uploaded_files ADD COLUMN upload_date DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE uploaded_files MODIFY COLUMN filepath VARCHAR(255) NULL;

ALTER TABLE uploaded_files MODIFY COLUMN filepath VARCHAR(255) DEFAULT '';
ALTER TABLE uploaded_files ADD COLUMN parent_id INT DEFAULT NULL;





CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    profile_picture VARCHAR(255) DEFAULT NULL
);

CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    deadline DATE,
    teacher_id INT,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
    

);


ALTER TABLE assignments ADD COLUMN file_attachment VARCHAR(255) AFTER description;
ALTER TABLE assignments MODIFY COLUMN deadline DATETIME;



CREATE TABLE modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    date DATE,
    file_attachment VARCHAR(255) DEFAULT NULL,
    teacher_id INT,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
);

ALTER TABLE modules ADD COLUMN subject VARCHAR(255) NOT NULL;
ALTER TABLE assignments ADD COLUMN subject VARCHAR(255) NOT NULL;


CREATE TABLE teacher_student_chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    student_id INT UNSIGNED NOT NULL, -- Match users.id as UNSIGNED
    message TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
