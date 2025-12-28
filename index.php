<?php
session_start();
require_once 'config.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_employee') {
        $emp_id = trim($_POST['emp_id']);
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $department_id = intval($_POST['department_id']);
        $designation = trim($_POST['designation']);
        $join_date = $_POST['join_date'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO employees (emp_id, full_name, email, phone, department_id, designation, join_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$emp_id, $full_name, $email, $phone, $department_id, $designation, $join_date]);
            $success = "Employee added successfully!";
        } catch (PDOException $e) {
            $error = "Error adding employee. Employee ID or email may already exist.";
        }
    } elseif ($action === 'mark_attendance') {
        $employee_id = intval($_POST['employee_id']);
        $attendance_date = $_POST['attendance_date'];
        $status = $_POST['status'];
        $check_in = $_POST['check_in'] ?? null;
        $check_out = $_POST['check_out'] ?? null;
        $remarks = trim($_POST['remarks']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, attendance_date, check_in, check_out, status, remarks, marked_by) VALUES (?, ?, ?, ?, ?, ?, 'Admin') ON DUPLICATE KEY UPDATE check_in=?, check_out=?, status=?, remarks=?");
            $stmt->execute([$employee_id, $attendance_date, $check_in, $check_out, $status, $remarks, $check_in, $check_out, $status, $remarks]);
            $success = "Attendance marked successfully!";
        } catch (PDOException $e) {
            $error = "Error marking attendance.";
        }
    } elseif ($action === 'submit_leave') {
        $employee_id = intval($_POST['employee_id']);
        $leave_type = $_POST['leave_type'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $reason = trim($_POST['reason']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, reason) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$employee_id, $leave_type, $start_date, $end_date, $reason]);
            $success = "Leave request submitted successfully!";
        } catch (PDOException $e) {
            $error = "Error submitting leave request.";
        }
    } elseif ($action === 'update_leave') {
        $leave_id = intval($_POST['leave_id']);
        $status = $_POST['leave_status'];
        
        $stmt = $pdo->prepare("UPDATE leave_requests SET status = ? WHERE id = ?");
        $stmt->execute([$status, $leave_id]);
        $success = "Leave request updated successfully!";
    }
}

$date_filter = $_GET['date'] ?? date('Y-m-d');
$dept_filter = $_GET['department'] ?? '';
$search = $_GET['search'] ?? '';

$employees_sql = "SELECT e.*, d.dept_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.status = 'active'";
$params = [];

if ($dept_filter) {
    $employees_sql .= " AND e.department_id = ?";
    $params[] = $dept_filter;
}

if ($search) {
    $employees_sql .= " AND (e.emp_id LIKE ? OR e.full_name LIKE ? OR e.email LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

$employees_sql .= " ORDER BY e.emp_id";
$stmt = $pdo->prepare($employees_sql);
$stmt->execute($params);
$employees = $stmt->fetchAll();

$stats = $pdo->query("SELECT 
    COUNT(*) as total_employees,
    COUNT(DISTINCT department_id) as departments,
    (SELECT COUNT(*) FROM attendance WHERE attendance_date = CURDATE()) as today_attendance,
    (SELECT COUNT(*) FROM attendance WHERE attendance_date = CURDATE() AND status = 'present') as present_today
    FROM employees WHERE status = 'active'")->fetch();

$departments = $pdo->query("SELECT * FROM departments ORDER BY dept_name")->fetchAll();

$attendance_sql = "SELECT a.*, e.emp_id, e.full_name, d.dept_name 
    FROM attendance a 
    JOIN employees e ON a.employee_id = e.id 
    LEFT JOIN departments d ON e.department_id = d.id 
    WHERE a.attendance_date = ?";
$attendance_params = [$date_filter];

if ($dept_filter) {
    $attendance_sql .= " AND e.department_id = ?";
    $attendance_params[] = $dept_filter;
}

$attendance_sql .= " ORDER BY e.emp_id";
$stmt = $pdo->prepare($attendance_sql);
$stmt->execute($attendance_params);
$attendance_records = $stmt->fetchAll();

$leave_requests = $pdo->query("SELECT lr.*, e.emp_id, e.full_name, d.dept_name 
    FROM leave_requests lr 
    JOIN employees e ON lr.employee_id = e.id 
    LEFT JOIN departments d ON e.department_id = d.id 
    ORDER BY lr.requested_at DESC 
    LIMIT 10")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👥 Attendance Management System</h1>
            <p class="subtitle">Track employee attendance and manage leave requests</p>
        </div>

        <?php if ($success): ?>
            <div class="message success">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card card-blue">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $stats['total_employees']; ?></div>
                    <div class="stat-label">Total Employees</div>
                </div>
            </div>
            <div class="stat-card card-green">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $stats['present_today']; ?></div>
                    <div class="stat-label">Present Today</div>
                </div>
            </div>
            <div class="stat-card card-orange">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $stats['today_attendance']; ?></div>
                    <div class="stat-label">Marked Today</div>
                </div>
            </div>
            <div class="stat-card card-purple">
                <div class="stat-icon">🏢</div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $stats['departments']; ?></div>
                    <div class="stat-label">Departments</div>
                </div>
            </div>
        </div>

        <div class="actions-bar">
            <button onclick="toggleForm('addEmployeeForm')" class="btn btn-primary">➕ Add Employee</button>
            <button onclick="toggleForm('markAttendanceForm')" class="btn btn-success">✅ Mark Attendance</button>
            <button onclick="toggleForm('leaveRequestForm')" class="btn btn-orange">📝 Leave Request</button>
        </div>

        <div id="addEmployeeForm" class="form-card" style="display: none;">
            <h2>➕ Add New Employee</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_employee">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Employee ID *</label>
                        <input type="text" name="emp_id" required>
                    </div>
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone">
                    </div>
                    <div class="form-group">
                        <label>Department *</label>
                        <select name="department_id" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['dept_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Designation *</label>
                        <input type="text" name="designation" required>
                    </div>
                    <div class="form-group">
                        <label>Join Date *</label>
                        <input type="date" name="join_date" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Add Employee</button>
                    <button type="button" onclick="toggleForm('addEmployeeForm')" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>

        <div id="markAttendanceForm" class="form-card" style="display: none;">
            <h2>✅ Mark Attendance</h2>
            <form method="POST">
                <input type="hidden" name="action" value="mark_attendance">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Employee *</label>
                        <select name="employee_id" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo $emp['id']; ?>"><?php echo htmlspecialchars($emp['emp_id'] . ' - ' . $emp['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date *</label>
                        <input type="date" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                            <option value="leave">Leave</option>
                            <option value="half_day">Half Day</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Check In</label>
                        <input type="time" name="check_in">
                    </div>
                    <div class="form-group">
                        <label>Check Out</label>
                        <input type="time" name="check_out">
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <input type="text" name="remarks">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Mark Attendance</button>
                    <button type="button" onclick="toggleForm('markAttendanceForm')" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>

        <div id="leaveRequestForm" class="form-card" style="display: none;">
            <h2>📝 Submit Leave Request</h2>
            <form method="POST">
                <input type="hidden" name="action" value="submit_leave">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Employee *</label>
                        <select name="employee_id" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo $emp['id']; ?>"><?php echo htmlspecialchars($emp['emp_id'] . ' - ' . $emp['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Leave Type *</label>
                        <select name="leave_type" required>
                            <option value="sick">Sick Leave</option>
                            <option value="casual">Casual Leave</option>
                            <option value="annual">Annual Leave</option>
                            <option value="unpaid">Unpaid Leave</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Start Date *</label>
                        <input type="date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label>End Date *</label>
                        <input type="date" name="end_date" required>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Reason *</label>
                        <textarea name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Submit Request</button>
                    <button type="button" onclick="toggleForm('leaveRequestForm')" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>

        <div class="filter-section">
            <form method="GET" class="filter-form">
                <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
                <select name="department">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php echo $dept_filter == $dept['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['dept_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="🔍 Search employees...">
                <button type="submit" class="btn btn-search">Filter</button>
                <?php if ($search || $dept_filter || $date_filter !== date('Y-m-d')): ?>
                    <a href="index.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="section-title">
            <h2>📊 Attendance Records - <?php echo date('F j, Y', strtotime($date_filter)); ?></h2>
        </div>

        <div class="table-container">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Emp ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance_records as $record): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($record['emp_id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($record['full_name']); ?></td>
                            <td><span class="dept-badge"><?php echo htmlspecialchars($record['dept_name']); ?></span></td>
                            <td><?php echo $record['check_in'] ? date('g:i A', strtotime($record['check_in'])) : '-'; ?></td>
                            <td><?php echo $record['check_out'] ? date('g:i A', strtotime($record['check_out'])) : '-'; ?></td>
                            <td><span class="status-badge status-<?php echo $record['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $record['status'])); ?></span></td>
                            <td><?php echo htmlspecialchars($record['remarks'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($attendance_records)): ?>
                        <tr>
                            <td colspan="7" class="no-data">No attendance records found for selected date.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="section-title">
            <h2>📝 Recent Leave Requests</h2>
        </div>

        <div class="leave-requests-grid">
            <?php foreach ($leave_requests as $leave): ?>
                <div class="leave-card">
                    <div class="leave-header">
                        <div>
                            <h3><?php echo htmlspecialchars($leave['full_name']); ?></h3>
                            <p class="leave-emp-id"><?php echo htmlspecialchars($leave['emp_id']); ?> • <?php echo htmlspecialchars($leave['dept_name']); ?></p>
                        </div>
                        <span class="leave-status leave-<?php echo $leave['status']; ?>"><?php echo ucfirst($leave['status']); ?></span>
                    </div>
                    <div class="leave-details">
                        <div class="leave-info">
                            <span class="leave-type-badge leave-type-<?php echo $leave['leave_type']; ?>"><?php echo ucfirst($leave['leave_type']); ?> Leave</span>
                            <p><strong>📅 Duration:</strong> <?php echo date('M j', strtotime($leave['start_date'])); ?> - <?php echo date('M j, Y', strtotime($leave['end_date'])); ?></p>
                            <p><strong>📝 Reason:</strong> <?php echo htmlspecialchars($leave['reason']); ?></p>
                        </div>
                        <?php if ($leave['status'] === 'pending'): ?>
                            <form method="POST" class="leave-action-form">
                                <input type="hidden" name="action" value="update_leave">
                                <input type="hidden" name="leave_id" value="<?php echo $leave['id']; ?>">
                                <select name="leave_status" required>
                                    <option value="pending" selected>Pending</option>
                                    <option value="approved">Approve</option>
                                    <option value="rejected">Reject</option>
                                </select>
                                <button type="submit" class="btn btn-small">Update</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="footer">
            <p>👥 Attendance Management System - Efficient Workforce Tracking</p>
        </div>
    </div>

    <script>
        function toggleForm(formId) {
            const form = document.getElementById(formId);
            const allForms = document.querySelectorAll('.form-card');
            allForms.forEach(f => {
                if (f.id !== formId) f.style.display = 'none';
            });
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
