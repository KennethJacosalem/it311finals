<?php
include "D:/xampp/htdocs/student-management/db.php";

header("Content-Type: application/json");

$requestMethod = $_SERVER["REQUEST_METHOD"];
$student_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$filter_course = isset($_GET['course_id']) ? intval($_GET['course_id']) : null;
$keyword = isset($_GET['search']) ? $_GET['search'] : null;

// API Key for Authentication
$apiKey = "YOUR_SECURE_API_KEY";
$providedApiKey = isset($_SERVER['HTTP_X_API_KEY']) ? $_SERVER['HTTP_X_API_KEY'] : null;

if ($providedApiKey !== $apiKey) {
    http_response_code(403);
    echo json_encode(["message" => "Unauthorized access."]);
    exit;
}

switch ($requestMethod) {
    case 'POST':
        createStudent();
        break;
    case 'GET':
        if ($keyword) {
            searchStudents($keyword);
        } elseif ($filter_course) {
            filterStudentsByCourse($filter_course);
        } elseif ($student_id) {
            getStudent($student_id);
        } else {
            getStudents();
        }
        break;
    case 'DELETE':
        if ($student_id) {
            deleteStudent($student_id);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Student ID is required for deletion."]);
        }
        break;
    case 'PUT':
        if ($student_id) {
            updateStudent($student_id);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Student ID is required for updating."]);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
}

mysqli_close($conn);

// Create Student
function createStudent() {
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);

    $firstname = $data['firstname'] ?? null;
    $lastname = $data['lastname'] ?? null;
    $email = $data['email'] ?? null;
    $birthdate = $data['birthdate'] ?? null;
    $course_id = $data['course_id'] ?? null;

    if (!$firstname || !$lastname || !$email || !$birthdate || !$course_id) {
        http_response_code(400);
        echo json_encode(["message" => "All fields are required."]);
        return;
    }

    $checkDuplicate = "SELECT * FROM students WHERE email = '$email'";
    $duplicateResult = mysqli_query($conn, $checkDuplicate);

    if (mysqli_num_rows($duplicateResult) > 0) {
        http_response_code(409);
        echo json_encode(["message" => "Email already exists."]);
        return;
    }

    $sql = "INSERT INTO students (firstname, lastname, email, birthdate, course_id) 
            VALUES ('$firstname', '$lastname', '$email', '$birthdate', $course_id)";
    if (mysqli_query($conn, $sql)) {
        http_response_code(201);
        echo json_encode(["message" => "Student created successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error creating student: " . mysqli_error($conn)]);
    }
}

// Get All Students
function getStudents() {
    global $conn;
    $sql = "SELECT s.id, s.firstname, s.lastname, s.email, s.birthdate, c.course_name 
            FROM students s LEFT JOIN courses c ON s.course_id = c.course_id";
    $result = mysqli_query($conn, $sql);

    $students = mysqli_fetch_all($result, MYSQLI_ASSOC);
    echo json_encode($students);
}

// Get Single Student
function getStudent($id) {
    global $conn;
    $sql = "SELECT s.id, s.firstname, s.lastname, s.email, s.birthdate, c.course_name 
            FROM students s LEFT JOIN courses c ON s.course_id = c.course_id WHERE s.id = $id";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Student not found."]);
    }
}

// Filter Students by Course
function filterStudentsByCourse($course_id) {
    global $conn;
    $sql = "SELECT s.id, s.firstname, s.lastname, s.email, s.birthdate, c.course_name 
            FROM students s LEFT JOIN courses c ON s.course_id = c.course_id WHERE s.course_id = $course_id";
    $result = mysqli_query($conn, $sql);

    $students = mysqli_fetch_all($result, MYSQLI_ASSOC);
    echo json_encode($students);
}

// Search Students by Name or Email
function searchStudents($keyword) {
    global $conn;
    $keyword = mysqli_real_escape_string($conn, $keyword);
    $sql = "SELECT s.id, s.firstname, s.lastname, s.email, s.birthdate, c.course_name 
            FROM students s LEFT JOIN courses c ON s.course_id = c.course_id 
            WHERE s.firstname LIKE '%$keyword%' OR s.lastname LIKE '%$keyword%' OR s.email LIKE '%$keyword%'";
    $result = mysqli_query($conn, $sql);

    $students = mysqli_fetch_all($result, MYSQLI_ASSOC);
    echo json_encode($students);
}

// Update Student
function updateStudent($id) {
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);

    $firstname = $data['firstname'] ?? null;
    $lastname = $data['lastname'] ?? null;
    $email = $data['email'] ?? null;
    $birthdate = $data['birthdate'] ?? null;
    $course_id = $data['course_id'] ?? null;

    if (!$firstname || !$lastname || !$email || !$birthdate || !$course_id) {
        http_response_code(400);
        echo json_encode(["message" => "All fields are required."]);
        return;
    }

    $sql = "UPDATE students SET firstname = '$firstname', lastname = '$lastname', email = '$email', 
            birthdate = '$birthdate', course_id = $course_id WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        if (mysqli_affected_rows($conn) > 0) {
            echo json_encode(["message" => "Student updated successfully."]);
        } else {
            echo json_encode(["message" => "Student not found."]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error updating student: " . mysqli_error($conn)]);
    }
}

// Delete Student
function deleteStudent($id) {
    global $conn;
    $sql = "DELETE FROM students WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        if (mysqli_affected_rows($conn) > 0) {
            echo json_encode(["message" => "Student deleted successfully."]);
        } else {
            echo json_encode(["message" => "Student not found."]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error deleting student: " . mysqli_error($conn)]);
    }
}
?>
