<?php
require_once __DIR__ . '/config/config.php';

try {
    $db = Database::getConnection();
    
    // Hash password once to save time
    $password_hash = password_hash('password123', PASSWORD_BCRYPT);
    
    // Create Departments
    $depts_data = [
        ['dept_code' => 'MCA', 'dept_name' => 'Master of Computer Applications'],
        ['dept_code' => 'MBA', 'dept_name' => 'Master of Business Administration'],
        ['dept_code' => 'IT', 'dept_name' => 'Information Technology'],
        ['dept_code' => 'CSE', 'dept_name' => 'Computer Science & Engineering'],
        ['dept_code' => 'AI&ML', 'dept_name' => 'Artificial Intelligence & Machine Learning'],
        ['dept_code' => 'ECE', 'dept_name' => 'Electronics & Communication Engineering'],
        ['dept_code' => 'EEE', 'dept_name' => 'Electrical & Electronics Engineering'],
    ];

    $dept_ids = [];
    foreach ($depts_data as $d) {
        $stmt = $db->prepare("SELECT id FROM departments WHERE dept_code = ?");
        $stmt->execute([$d['dept_code']]);
        $id = $stmt->fetchColumn();
        if (!$id) {
            $db->prepare("INSERT INTO departments (dept_code, dept_name) VALUES (?, ?)")->execute([$d['dept_code'], $d['dept_name']]);
            $id = $db->lastInsertId();
        }
        $dept_ids[$d['dept_code']] = $id;
    }

    // List of names from user
    $raw_names = "manimozhi shaktidev anbarasan rajan divya tamizh sekar kannan keerthi jhonsi bhaskar maasi basha theantami meera raju karthik kode krishnave magesh mani mohamed mohan mohasir padmaja parthiban parveen prasanth premalika premji priya raja rajesh rakesh rasheddha rogan rohit sabapathy safiya sandhiya sanjay sathya selvi sharmilan siraj siva dhanush dharani elizharas eyal gayathiri grish hari harish hema jafren jagan james jayapriya joseph jp aagash aanand aasik abdul ajith anbu apsal arun arunthath ashwin aysha balaji began charu deepik";
    $names_list = array_values(array_filter(explode(" ", $raw_names)));

    // Fill with extra names if needed to reach 182+
    $extra_names = ["Vikram", "Surya", "Vijay", "Ajith", "Kamal", "Rajini", "Dhanush", "Simbu", "Sivakarthikeyan", "Karthi", "Jayam", "Vishal", "Arya", "Jiiva", "Madhavan", "Prashanth", "Arvind", "Sarath", "Sathyaraj", "Prabhu", "Karthik", "Murali", "Vijayakanth", "Ramki", "Arjun", "Prakash", "Nasser", "Vivek", "Vadivelu", "Santhanam", "Soori", "Yogi", "Satish", "Karunas", "Goundamani", "Senthil", "Nagesh", "Chandrababu", "Thangavelu", "Bala", "Shankar", "Mani", "Gautham", "Murugadoss", "Atlee", "Lokesh", "Karthik", "Ranjith", "Vetrimaaran", "Ram", "Selvaraghavan", "Mysskin", "Sundar", "Hari", "KS", "Raj", "Suseenthiran", "Pandiraj", "Mohan", "Raja", "Bharathiraja", "Balachander", "Mahendran", "Balu", "Sp", "Ilayaraja", "Rahman", "Harris", "Yuvan", "Anirudh", "Imman", "Deva", "Vidyasagar", "Karthik", "Vijay", "GV", "Santhosh", "Gibran", "Sam", "Thaman", "Nivas", "Justin", "Darbuka", "Sean", "Leon", "Simon", "Vishal", "Arrol", "K", "Tenma", "Govind", "Bindu", "Madhu", "Sneha", "Trisha", "Nayanthara", "Samantha", "Kajal", "Tamannaah", "Shruti", "Hansika", "Anushka", "Asin", "Simran", "Jyothika", "Meena", "Roja", "Kushboo", "Nadhiya", "Radha", "Ambika", "Revathi", "Suhasini", "Bhanupriya", "Ramya", "Shriya", "Amala", "Tabu", "Soundarya", "Devayani", "Ramba", "Sanghavi"];
    $all_names = array_merge($names_list, $extra_names);
    shuffle($all_names);

    $name_index = 0;
    function getNextName() {
        global $all_names, $name_index;
        if ($name_index >= count($all_names)) $name_index = 0;
        return ucfirst(strtolower(preg_replace('/[^a-zA-Z]/', '', $all_names[$name_index++])));
    }

    $db->beginTransaction();

    $std_counter = 2000;
    $staff_counter = 100;

    foreach ($dept_ids as $dept_code => $dept_id) {
        
        // 1. Create 1 HOD
        $hod_name = getNextName() . " HOD";
        $hod_email = strtolower(str_replace(' ', '', $hod_name)) . "_" . strtolower($dept_code) . "@campusguardian.edu";
        
        // Check if user exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$hod_email]);
        if (!$stmt->fetch()) {
            $db->prepare("INSERT INTO users (email, password, role, is_active) VALUES (?, ?, 'hod', 1)")->execute([$hod_email, $password_hash]);
            $u_id = $db->lastInsertId();
            $db->prepare("INSERT INTO hod (user_id, department_id, name, phone, email) VALUES (?, ?, ?, ?, ?)")->execute([$u_id, $dept_id, $hod_name, '9999999999', $hod_email]);
        }

        // 2. Create 5 Staff
        for ($i=1; $i<=5; $i++) {
            $staff_name = getNextName() . " Staff";
            $staff_email = strtolower(str_replace(' ', '', $staff_name)) . "_" . strtolower($dept_code) . "@campusguardian.edu";
            
            $stmt->execute([$staff_email]);
            if (!$stmt->fetch()) {
                $db->prepare("INSERT INTO users (email, password, role, is_active) VALUES (?, ?, 'staff', 1)")->execute([$staff_email, $password_hash]);
                $u_id = $db->lastInsertId();
                
                $staff_code = "FAC-" . $dept_code . "-" . $staff_counter++;
                $db->prepare("INSERT INTO staff (user_id, department_id, staff_code, name, designation, phone, email) VALUES (?, ?, ?, ?, ?, ?, ?)")->execute([$u_id, $dept_id, $staff_code, $staff_name, 'Assistant Professor', '8888888888', $staff_email]);
            }
        }

        // 3. Create 20 Students
        for ($j=1; $j<=20; $j++) {
            $std_name = getNextName();
            $std_email = strtolower($std_name) . $std_counter . "@student.cg.edu";
            
            $stmt->execute([$std_email]);
            if (!$stmt->fetch()) {
                // Student User
                $db->prepare("INSERT INTO users (email, password, role, is_active) VALUES (?, ?, 'student', 1)")->execute([$std_email, $password_hash]);
                $std_u_id = $db->lastInsertId();

                // Parent User
                $parent_email = "parent_" . strtolower($std_name) . $std_counter . "@example.com";
                $db->prepare("INSERT INTO users (email, password, role, is_active) VALUES (?, ?, 'parent', 1)")->execute([$parent_email, $password_hash]);
                $parent_u_id = $db->lastInsertId();

                $reg_no = "REG" . $dept_code . $std_counter;
                $std_code = "STD-" . $std_counter;
                $course_type = in_array($dept_code, ['MCA', 'MBA']) ? 'PG' : 'UG';
                $year = ['I', 'II', 'III', 'IV'][array_rand(['I', 'II', 'III', 'IV'])];
                if ($course_type == 'PG' && in_array($year, ['III', 'IV'])) $year = 'II';
                $sec = ['A', 'B', 'C'][array_rand(['A', 'B', 'C'])];
                $parent_name = getNextName() . " Parent";

                $db->prepare("INSERT INTO students (user_id, student_id_code, register_number, course_type, name, student_email, department_id, year, section, phone, parent_name, parent_phone, parent_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute([
                    $std_u_id, $std_code, $reg_no, $course_type, $std_name, $std_email, $dept_id, $year, $sec, '7777777777', $parent_name, '6666666666', $parent_email
                ]);
                $student_table_id = $db->lastInsertId();

                // Dummy Leave Request (50% chance)
                if (rand(1, 100) > 50) {
                    $l_status = ['pending', 'approved', 'rejected'][array_rand(['pending', 'approved', 'rejected'])];
                    $db->prepare("INSERT INTO leave_requests (student_id, leave_type, start_date, end_date, total_days, reason, status, staff_approval, hod_approval) VALUES (?, 'Sick Leave', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 DAY), 2, 'Fever', ?, ?, ?)")->execute([
                        $student_table_id, $l_status, $l_status, $l_status
                    ]);
                    $lr_id = $db->lastInsertId();
                    
                    // Parent Notification
                    $p_status = ($l_status == 'approved') ? 'received' : 'sent';
                    $db->prepare("INSERT INTO parent_notifications (student_id, leave_request_id, parent_email, title, message, status) VALUES (?, ?, ?, ?, ?, ?)")->execute([
                        $student_table_id, $lr_id, $parent_email, 'Leave Update', 'Leave status: ' . $l_status, $p_status
                    ]);
                }

                $std_counter++;
            }
        }
    }

    $db->commit();
    echo "Successfully generated massive seed data with HODs, Staff, and Students!";

} catch (Exception $e) {
    if (isset($db)) $db->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
