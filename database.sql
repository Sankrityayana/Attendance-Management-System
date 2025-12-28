-- Create Database
CREATE DATABASE IF NOT EXISTS attendance_system;
USE attendance_system;

-- Departments Table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dept_name VARCHAR(100) NOT NULL,
    dept_code VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Employees Table
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emp_id VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20),
    department_id INT,
    designation VARCHAR(100),
    join_date DATE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT,
    INDEX idx_emp_id (emp_id),
    INDEX idx_department (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Attendance Records Table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    check_in TIME NULL,
    check_out TIME NULL,
    status ENUM('present', 'absent', 'late', 'leave', 'half_day') NOT NULL,
    remarks TEXT,
    marked_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (employee_id, attendance_date),
    INDEX idx_date (attendance_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Leave Requests Table
CREATE TABLE IF NOT EXISTS leave_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    leave_type ENUM('sick', 'casual', 'annual', 'unpaid') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX idx_employee (employee_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Departments
INSERT INTO departments (dept_name, dept_code) VALUES
('Human Resources', 'HR'),
('Information Technology', 'IT'),
('Finance', 'FIN'),
('Marketing', 'MKT'),
('Operations', 'OPS');

-- Sample Employees
INSERT INTO employees (emp_id, full_name, email, phone, department_id, designation, join_date, status) VALUES
('EMP001', 'John Smith', 'john.smith@company.com', '555-0101', 2, 'Senior Developer', '2023-01-15', 'active'),
('EMP002', 'Sarah Johnson', 'sarah.j@company.com', '555-0102', 1, 'HR Manager', '2022-06-20', 'active'),
('EMP003', 'Michael Chen', 'michael.chen@company.com', '555-0103', 2, 'DevOps Engineer', '2023-03-10', 'active'),
('EMP004', 'Emily Davis', 'emily.davis@company.com', '555-0104', 3, 'Financial Analyst', '2022-11-05', 'active'),
('EMP005', 'Robert Wilson', 'robert.w@company.com', '555-0105', 4, 'Marketing Specialist', '2023-05-12', 'active'),
('EMP006', 'Lisa Anderson', 'lisa.anderson@company.com', '555-0106', 5, 'Operations Manager', '2022-08-18', 'active');

-- Sample Attendance Records (Last 3 days)
INSERT INTO attendance (employee_id, attendance_date, check_in, check_out, status, marked_by) VALUES
(1, CURDATE(), '09:00:00', '17:30:00', 'present', 'Admin'),
(2, CURDATE(), '09:15:00', '17:00:00', 'present', 'Admin'),
(3, CURDATE(), '09:30:00', NULL, 'late', 'Admin'),
(4, CURDATE(), NULL, NULL, 'absent', 'Admin'),
(5, CURDATE(), '09:00:00', '17:00:00', 'present', 'Admin'),
(6, CURDATE(), '09:00:00', '13:00:00', 'half_day', 'Admin'),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '09:00:00', '17:30:00', 'present', 'Admin'),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '09:00:00', '17:00:00', 'present', 'Admin'),
(3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '09:00:00', '17:30:00', 'present', 'Admin');

-- Sample Leave Requests
INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, reason, status) VALUES
(1, 'annual', '2025-01-10', '2025-01-12', 'Family vacation', 'approved'),
(3, 'sick', '2025-01-05', '2025-01-06', 'Medical appointment', 'pending');
