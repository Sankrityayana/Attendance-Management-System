# 👥 Attendance Management System

A comprehensive employee attendance tracking system with leave management, department organization, and detailed reporting capabilities.

## ✨ Features

### Employee Management
- Add and manage employees with unique IDs
- Department-wise organization
- Employee profiles with contact information
- Active/inactive status tracking
- Designation and joining date records

### Attendance Tracking
- Mark daily attendance with multiple statuses (Present, Absent, Late, Leave, Half Day)
- Check-in and check-out time recording
- Date-wise attendance filtering
- Department-wise attendance reports
- Remarks and notes support
- Prevent duplicate attendance entries for same date

### Leave Management
- Submit leave requests with multiple types (Sick, Casual, Annual, Unpaid)
- Leave duration tracking (start date to end date)
- Approval workflow (Pending → Approved/Rejected)
- Leave reason documentation
- Recent leave requests dashboard

### Reporting & Analytics
- Real-time statistics dashboard
- Total employees count
- Present today count
- Daily attendance summary
- Department-wise filtering
- Search functionality across employees

### User Interface
- Clean, multicolor light theme design
- No gradients - solid color palette
- Responsive layout for all devices
- Intuitive form toggles
- Color-coded status badges
- Department badges
- Leave type indicators

## 📋 Installation

1. **Start XAMPP**
   - Start Apache and MySQL services
   - Ensure MySQL is running on port 3307

2. **Import Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import `database.sql` file
   - Database `attendance_system` will be created

3. **Deploy Files**
   - Place all files in `htdocs/attendance-management-system` directory
   - Ensure proper file permissions

4. **Access Application**
   - Open browser: `http://localhost/attendance-management-system`
   - System is ready to use with sample data

## 💡 Usage Guide

### Adding Employees
1. Click "➕ Add Employee" button
2. Fill in employee details (ID, name, email, department, etc.)
3. Submit to create new employee record

### Marking Attendance
1. Click "✅ Mark Attendance" button
2. Select employee and date
3. Choose status (Present/Absent/Late/Leave/Half Day)
4. Optionally add check-in/check-out times
5. Add remarks if needed
6. Submit to record attendance

### Managing Leave Requests
1. Click "📝 Leave Request" button
2. Select employee and leave type
3. Set start and end dates
4. Provide reason for leave
5. Submit request
6. Approve/reject from pending requests section

### Viewing Reports
- Use date filter to view specific day's attendance
- Filter by department for department-specific reports
- Search employees by ID, name, or email
- View statistics on dashboard

## 🛠️ Technical Specifications

### Technology Stack
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ (Port 3307)
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Server**: Apache (XAMPP)

### Database Structure
- **departments**: Department information and codes
- **employees**: Employee master data with department links
- **attendance**: Daily attendance records with timestamps
- **leave_requests**: Leave applications with approval status

### Security Features
- PDO prepared statements (SQL injection prevention)
- Input sanitization with htmlspecialchars()
- Unique constraints on employee IDs and emails
- Duplicate attendance prevention
- Foreign key constraints for data integrity

### Design Standards
- Light multicolor theme with CSS variables
- No gradient backgrounds (solid colors only)
- Color-coded status indicators:
  - 🔵 Blue: Info/Leave
  - 🟢 Green: Present/Approved
  - 🟠 Orange: Late/Pending
  - 🔴 Red: Absent/Rejected
  - 🟣 Purple: Half Day/Departments
- Responsive grid layouts
- Mobile-friendly interface

## 📊 Sample Data

The system includes sample data:
- 5 departments (HR, IT, Finance, Marketing, Operations)
- 6 employees across different departments
- Recent attendance records
- Sample leave requests

## 🔧 Configuration

Database settings in `config.php`:
```php
$host = 'localhost:3307';
$dbname = 'attendance_system';
$username = 'root';
$password = '';
```

Modify as needed for your environment.

## 📝 System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Web Server
- XAMPP (recommended)
- Modern web browser

## 👤 Author

**Created by Sankrityayana**

---

*Efficient workforce attendance tracking and leave management solution*
