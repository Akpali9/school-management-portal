<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school_portal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create tables if they don't exist
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    full_name VARCHAR(100) NOT NULL
)");

$conn->query("CREATE TABLE IF NOT EXISTS school_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    principal VARCHAR(100) NOT NULL
)");

$conn->query("CREATE TABLE IF NOT EXISTS teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    subject VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    qualification VARCHAR(100) NOT NULL
)");

$conn->query("CREATE TABLE IF NOT EXISTS staff (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    department VARCHAR(50) NOT NULL
)");

$conn->query("CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    grade VARCHAR(20) NOT NULL,
    section VARCHAR(10) NOT NULL,
    dob DATE NOT NULL,
    parent_name VARCHAR(100) NOT NULL,
    parent_contact VARCHAR(20) NOT NULL
)");

// Initialize variables
$notification = "";
$isLoggedIn = isset($_SESSION['user_id']);
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    
    $result = $conn->query("SELECT * FROM users WHERE username = '$username'");
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $isLoggedIn = true;
            $currentPage = 'dashboard';
            $notification = "Login successful!";
        } else {
            $notification = "Invalid password!";
        }
    } else {
        $notification = "User not found!";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    $isLoggedIn = false;
    $notification = "You have been logged out.";
    header("Location: ?");
    exit;
}

// Handle form submissions
if ($isLoggedIn && $_SERVER["REQUEST_METHOD"] == "POST") {
    // School Info Update
    if (isset($_POST['update_school_info'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $address = $conn->real_escape_string($_POST['address']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $email = $conn->real_escape_string($_POST['email']);
        $principal = $conn->real_escape_string($_POST['principal']);
        
        // Check if school info exists
        $check = $conn->query("SELECT * FROM school_info WHERE id=1");
        if ($check->num_rows > 0) {
            $sql = "UPDATE school_info SET name='$name', address='$address', phone='$phone', email='$email', principal='$principal' WHERE id=1";
        } else {
            $sql = "INSERT INTO school_info (name, address, phone, email, principal) VALUES ('$name', '$address', '$phone', '$email', '$principal')";
        }
        
        if ($conn->query($sql) === TRUE) {
            $notification = "School information updated successfully!";
        } else {
            $notification = "Error updating school information: " . $conn->error;
        }
    }
    
    // Add Teacher
    if (isset($_POST['add_teacher'])) {
        $name = $conn->real_escape_string($_POST['teacher_name']);
        $subject = $conn->real_escape_string($_POST['subject']);
        $email = $conn->real_escape_string($_POST['teacher_email']);
        $phone = $conn->real_escape_string($_POST['teacher_phone']);
        $qualification = $conn->real_escape_string($_POST['qualification']);
        
        $sql = "INSERT INTO teachers (name, subject, email, phone, qualification) 
                VALUES ('$name', '$subject', '$email', '$phone', '$qualification')";
        
        if ($conn->query($sql)) {
            $notification = "Teacher added successfully!";
        } else {
            $notification = "Error adding teacher: " . $conn->error;
        }
    }
    
    // Add Staff
    if (isset($_POST['add_staff'])) {
        $name = $conn->real_escape_string($_POST['staff_name']);
        $position = $conn->real_escape_string($_POST['position']);
        $email = $conn->real_escape_string($_POST['staff_email']);
        $phone = $conn->real_escape_string($_POST['staff_phone']);
        $department = $conn->real_escape_string($_POST['department']);
        
        $sql = "INSERT INTO staff (name, position, email, phone, department) 
                VALUES ('$name', '$position', '$email', '$phone', '$department')";
        
        if ($conn->query($sql)) {
            $notification = "Staff member added successfully!";
        } else {
            $notification = "Error adding staff member: " . $conn->error;
        }
    }
    
    // Add Student
    if (isset($_POST['add_student'])) {
        $name = $conn->real_escape_string($_POST['student_name']);
        $grade = $conn->real_escape_string($_POST['grade']);
        $section = $conn->real_escape_string($_POST['section']);
        $dob = $conn->real_escape_string($_POST['dob']);
        $parent = $conn->real_escape_string($_POST['parent_name']);
        $contact = $conn->real_escape_string($_POST['parent_contact']);
        
        $sql = "INSERT INTO students (name, grade, section, dob, parent_name, parent_contact) 
                VALUES ('$name', '$grade', '$section', '$dob', '$parent', '$contact')";
        
        if ($conn->query($sql)) {
            $notification = "Student added successfully!";
        } else {
            $notification = "Error adding student: " . $conn->error;
        }
    }
}

// Fetch data for display
if ($isLoggedIn) {
    $school_info = $conn->query("SELECT * FROM school_info WHERE id=1");
    $school_info = ($school_info->num_rows > 0) ? $school_info->fetch_assoc() : [
        'name' => 'Greenwood High School',
        'address' => '123 Education St, Academic City',
        'phone' => '(555) 123-4567',
        'email' => 'info@greenwood.edu',
        'principal' => 'Dr. Sarah Johnson'
    ];
    
    $teachers = $conn->query("SELECT * FROM teachers ORDER BY name ASC");
    $staff = $conn->query("SELECT * FROM staff ORDER BY name ASC");
    $students = $conn->query("SELECT * FROM students ORDER BY grade, name ASC");
}

// Add initial data if tables are empty
if ($isLoggedIn && $teachers->num_rows == 0) {
    $conn->query("INSERT INTO teachers (name, subject, email, phone, qualification) VALUES 
        ('John Smith', 'Mathematics', 'john.smith@school.edu', '555-1234', 'MSc Mathematics'),
        ('Emily Johnson', 'Science', 'emily.j@school.edu', '555-5678', 'PhD Physics')");
    
    $conn->query("INSERT INTO staff (name, position, email, phone, department) VALUES 
        ('Robert Davis', 'Administrator', 'robert.d@school.edu', '555-9012', 'Administration'),
        ('Lisa Thompson', 'Librarian', 'lisa.t@school.edu', '555-3456', 'Library')");
    
    $conn->query("INSERT INTO students (name, grade, section, dob, parent_name, parent_contact) VALUES 
        ('Michael Brown', 'Grade 10', 'A', '2010-05-15', 'David Brown', '555-7890'),
        ('Sophia Wilson', 'Grade 9', 'B', '2011-08-22', 'James Wilson', '555-2345')");
    
    // Refresh data
    $teachers = $conn->query("SELECT * FROM teachers ORDER BY name ASC");
    $staff = $conn->query("SELECT * FROM staff ORDER BY name ASC");
    $students = $conn->query("SELECT * FROM students ORDER BY grade, name ASC");
}

// Create admin user if not exists
$result = $conn->query("SELECT * FROM users WHERE username='admin'");
if ($result->num_rows == 0) {
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, password, role, full_name) VALUES 
        ('admin', '$hashed_password', 'admin', 'Administrator')");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #f8f9fa;
            --dark: #343a40;
            --sidebar-width: 250px;
            --header-height: 70px;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Login Page Styles */
        .login-container {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--secondary), #4a6491);
        }
        
        .login-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            color: white;
            text-align: center;
            background: rgba(0, 0, 0, 0.3);
        }
        
        .login-left h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 30px;
            text-align: left;
            max-width: 600px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .login-form-container {
            width: 450px;
            background: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo h2 {
            font-size: 2rem;
            color: var(--secondary);
            font-weight: 700;
        }
        
        /* Dashboard Styles */
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background: var(--secondary);
            color: white;
            position: fixed;
            height: 100vh;
            padding-top: var(--header-height);
            transition: all 0.3s ease;
            z-index: 100;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #eee;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid var(--primary);
            color: white;
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
        }
        
        .header {
            height: var(--header-height);
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 99;
            transition: all 0.3s ease;
        }
        
        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--secondary);
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-name {
            font-weight: 600;
            color: var(--secondary);
        }
        
        .user-role {
            font-size: 0.85rem;
            color: #777;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            cursor: pointer;
        }
        
        .content {
            padding: 2rem;
            margin-top: var(--header-height);
        }
        
        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .notification.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .notification.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .section-title {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
            color: var(--secondary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
            height: 100%;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary);
        }
        
        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        .card .number {
            font-size: 2rem;
            font-weight: 700;
            margin: 15px 0;
            color: var(--primary);
        }
        
        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .table-header {
            background-color: var(--primary);
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-content {
            padding: 15px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .table th {
            background-color: #f1f5f9;
            font-weight: 600;
        }
        
        .table tr:hover {
            background-color: #f8fafc;
        }
        
        .form-section {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .header {
                left: 0;
            }
        }
        
        @media (max-width: 768px) {
            .features {
                grid-template-columns: 1fr;
            }
            
            .login-form-container {
                width: 100%;
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php if (!$isLoggedIn): ?>
        <!-- Login Page -->
        <div class="login-container">
            <div class="login-left">
                <h1>School Management Portal</h1>
                <p>Comprehensive solution for administrators to manage school information, teachers, staff, and students</p>
                
                <div class="features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <div>Student Information Management</div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <div>Teacher & Staff Directory</div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <div>Attendance Tracking</div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <div>Grade Management</div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <div>Timetable Scheduling</div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <div>Reporting & Analytics</div>
                    </div>
                </div>
            </div>
            
            <div class="login-form-container">
                <div class="logo">
                    <h2>School<span style="color: var(--primary);">Portal</span></h2>
                    <p>Administrative Dashboard</p>
                </div>
                
                <?php if ($notification): ?>
                    <div class="alert alert-<?php echo strpos($notification, 'success') !== false ? 'success' : 'danger'; ?>">
                        <?php echo $notification; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    
                    <div class="mb-3">
                        <button type="submit" name="login" class="btn btn-primary w-100">Sign In</button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <p>Default admin credentials: admin / admin123</p>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- Dashboard -->
        <div class="dashboard">
            <!-- Sidebar -->
            <div class="sidebar">
                <ul class="sidebar-menu">
                    <li><a href="?page=dashboard" class="<?php echo $currentPage == 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="?page=school" class="<?php echo $currentPage == 'school' ? 'active' : ''; ?>"><i class="fas fa-school"></i> School Info</a></li>
                    <li><a href="?page=teachers" class="<?php echo $currentPage == 'teachers' ? 'active' : ''; ?>"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                    <li><a href="?page=staff" class="<?php echo $currentPage == 'staff' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Staff</a></li>
                    <li><a href="?page=students" class="<?php echo $currentPage == 'students' ? 'active' : ''; ?>"><i class="fas fa-user-graduate"></i> Students</a></li>
                    <li><a href="?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
            
            <!-- Main Content -->
            <div class="main-content">
                <!-- Header -->
                <div class="header">
                    <div class="header-left">
                        <div class="header-title">
                            <?php 
                                $pageTitles = [
                                    'dashboard' => 'Dashboard',
                                    'school' => 'School Information',
                                    'teachers' => 'Teacher Management',
                                    'staff' => 'Staff Management',
                                    'students' => 'Student Management'
                                ];
                                echo $pageTitles[$currentPage] ?? 'Dashboard';
                            ?>
                        </div>
                    </div>
                    
                    <div class="user-menu">
                        <div class="user-info">
                            <div class="user-name"><?php echo $_SESSION['full_name']; ?></div>
                            <div class="user-role">Administrator</div>
                        </div>
                        <div class="user-avatar"><?php echo substr($_SESSION['full_name'], 0, 1); ?></div>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="content">
                    <?php if ($notification): ?>
                        <div class="notification <?php echo strpos($notification, 'success') !== false ? 'success' : 'error'; ?>">
                            <?php echo $notification; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($currentPage == 'dashboard'): ?>
                        <!-- Dashboard Content -->
                        <h2 class="section-title">School Overview</h2>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card text-center p-4">
                                    <div class="card-icon"><i class="fas fa-user-graduate"></i></div>
                                    <h3>Students</h3>
                                    <div class="number"><?php echo $students->num_rows; ?></div>
                                    <p>Total enrolled students</p>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="card text-center p-4">
                                    <div class="card-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                    <h3>Teachers</h3>
                                    <div class="number"><?php echo $teachers->num_rows; ?></div>
                                    <p>Teaching staff members</p>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="card text-center p-4">
                                    <div class="card-icon"><i class="fas fa-users"></i></div>
                                    <h3>Staff</h3>
                                    <div class="number"><?php echo $staff->num_rows; ?></div>
                                    <p>Administrative staff</p>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="card text-center p-4">
                                    <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
                                    <h3>Attendance</h3>
                                    <div class="number">96%</div>
                                    <p>Today's attendance rate</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="table-container">
                                    <div class="table-header">
                                        <h3 class="m-0">Recent Teachers</h3>
                                        <span><i class="fas fa-chalkboard-teacher"></i></span>
                                    </div>
                                    <div class="table-content">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Subject</th>
                                                    <th>Contact</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $teachers->data_seek(0);
                                                $counter = 0;
                                                while ($teacher = $teachers->fetch_assoc() && $counter < 5): 
                                                    $counter++;
                                                ?>
                                                    <tr>
                                                        <td><?php echo $teacher['name']; ?></td>
                                                        <td><?php echo $teacher['subject']; ?></td>
                                                        <td><?php echo $teacher['email']; ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="table-container">
                                    <div class="table-header">
                                        <h3 class="m-0">Recent Students</h3>
                                        <span><i class="fas fa-user-graduate"></i></span>
                                    </div>
                                    <div class="table-content">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Grade</th>
                                                    <th>Section</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $students->data_seek(0);
                                                $counter = 0;
                                                while ($student = $students->fetch_assoc() && $counter < 5): 
                                                    $counter++;
                                                ?>
                                                    <tr>
                                                        <td><?php echo $student['name']; ?></td>
                                                        <td><?php echo $student['grade']; ?></td>
                                                        <td><?php echo $student['section']; ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card p-4">
                                    <h3 class="mb-4">School Announcements</h3>
                                    <div class="alert alert-info">
                                        <strong>Parent-Teacher Meeting:</strong> Scheduled for next Friday, 3:00 PM in the school auditorium.
                                    </div>
                                    <div class="alert alert-warning">
                                        <strong>Holiday Notice:</strong> School will remain closed on 15th August for Independence Day.
                                    </div>
                                    <div class="alert alert-success">
                                        <strong>Annual Sports Day:</strong> Preparations are underway for the annual sports event on 25th September.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($currentPage == 'school'): ?>
                        <!-- School Information -->
                        <div class="form-section">
                            <h2 class="section-title">School Information</h2>
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">School Name</label>
                                        <input type="text" class="form-control" name="name" value="<?php echo $school_info['name']; ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Principal Name</label>
                                        <input type="text" class="form-control" name="principal" value="<?php echo $school_info['principal']; ?>" required>
                                    </div>
                                    
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Address</label>
                                        <input type="text" class="form-control" name="address" value="<?php echo $school_info['address']; ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" class="form-control" name="phone" value="<?php echo $school_info['phone']; ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="<?php echo $school_info['email']; ?>" required>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <button type="submit" name="update_school_info" class="btn btn-primary">Update School Info</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <div class="card p-4">
                            <h3 class="mb-4">School Statistics</h3>
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <div class="number"><?php echo $students->num_rows; ?></div>
                                    <p>Total Students</p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="number"><?php echo $teachers->num_rows; ?></div>
                                    <p>Total Teachers</p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="number"><?php echo $staff->num_rows; ?></div>
                                    <p>Total Staff</p>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($currentPage == 'teachers'): ?>
                        <!-- Teacher Management -->
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-section">
                                    <h2 class="section-title">Add New Teacher</h2>
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" class="form-control" name="teacher_name" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Subject</label>
                                            <input type="text" class="form-control" name="subject" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="teacher_email" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="tel" class="form-control" name="teacher_phone" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Qualification</label>
                                            <input type="text" class="form-control" name="qualification" required>
                                        </div>
                                        
                                        <button type="submit" name="add_teacher" class="btn btn-success">Add Teacher</button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="col-md-7">
                                <div class="table-container">
                                    <div class="table-header">
                                        <h3 class="m-0">Teachers Directory</h3>
                                        <span><?php echo $teachers->num_rows; ?> Teachers</span>
                                    </div>
                                    <div class="table-content">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Subject</th>
                                                    <th>Contact</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($teacher = $teachers->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo $teacher['name']; ?></td>
                                                        <td><?php echo $teacher['subject']; ?></td>
                                                        <td><?php echo $teacher['email']; ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($currentPage == 'staff'): ?>
                        <!-- Staff Management -->
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-section">
                                    <h2 class="section-title">Add New Staff Member</h2>
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" class="form-control" name="staff_name" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Position</label>
                                            <input type="text" class="form-control" name="position" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="staff_email" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="tel" class="form-control" name="staff_phone" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Department</label>
                                            <input type="text" class="form-control" name="department" required>
                                        </div>
                                        
                                        <button type="submit" name="add_staff" class="btn btn-success">Add Staff Member</button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="col-md-7">
                                <div class="table-container">
                                    <div class="table-header">
                                        <h3 class="m-0">Staff Directory</h3>
                                        <span><?php echo $staff->num_rows; ?> Members</span>
                                    </div>
                                    <div class="table-content">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Position</th>
                                                    <th>Department</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($staff_member = $staff->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo $staff_member['name']; ?></td>
                                                        <td><?php echo $staff_member['position']; ?></td>
                                                        <td><?php echo $staff_member['department']; ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($currentPage == 'students'): ?>
                        <!-- Student Management -->
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-section">
                                    <h2 class="section-title">Add New Student</h2>
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" class="form-control" name="student_name" required>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Grade</label>
                                                <select class="form-select" name="grade" required>
                                                    <option value="">Select Grade</option>
                                                    <?php 
                                                    $grades = ['Kindergarten', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 
                                                               'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'];
                                                    foreach ($grades as $grade): ?>
                                                        <option value="<?php echo $grade; ?>"><?php echo $grade; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Section</label>
                                                <input type="text" class="form-control" name="section" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Date of Birth</label>
                                            <input type="date" class="form-control" name="dob" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Parent/Guardian Name</label>
                                            <input type="text" class="form-control" name="parent_name" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Parent Contact</label>
                                            <input type="tel" class="form-control" name="parent_contact" required>
                                        </div>
                                        
                                        <button type="submit" name="add_student" class="btn btn-success">Add Student</button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="col-md-7">
                                <div class="table-container">
                                    <div class="table-header">
                                        <h3 class="m-0">Student Records</h3>
                                        <span><?php echo $students->num_rows; ?> Students</span>
                                    </div>
                                    <div class="table-content">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Grade</th>
                                                    <th>Section</th>
                                                    <th>Parent</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($student = $students->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo $student['name']; ?></td>
                                                        <td><?php echo $student['grade']; ?></td>
                                                        <td><?php echo $student['section']; ?></td>
                                                        <td><?php echo $student['parent_name']; ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple notification fade effect
        document.addEventListener('DOMContentLoaded', function() {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => {
                setTimeout(() => {
                    notification.style.transition = 'opacity 1s';
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.style.display = 'none';
                    }, 1000);
                }, 5000);
            });
        });
    </script>
</body>
</html>
