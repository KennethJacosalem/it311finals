<?php
// Include database connection
include "D:/xampp/htdocs/it311finals/db.php";

// Set content type to JSON for API responses
header("Content-Type: application/json");

// This is the API key used to give authorization
define("API_KEY", "Idol-sirMhezel");

// Validate the API key
if (!isset($_SERVER['HTTP_X_API_KEY']) || $_SERVER['HTTP_X_API_KEY'] !== API_KEY) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized. Invalid API key."]);
    exit;
}

// Get the request method and parameters
$requestMethod = $_SERVER["REQUEST_METHOD"];
$student_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$searchTerm = isset($_GET['search']) ? $_GET['search'] : null;
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : null;

// Handle API requests based on method
switch ($requestMethod) {
    case 'POST':
        createStudent();
        break;
    case 'GET':
        if ($searchTerm) {
            searchStudents($searchTerm);
        } elseif ($course_id) {
            filterStudentsByCourse($course_id);
        } elseif ($student_id) {
            getStudent($student_id);
        } else {
            getStudents();
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
    case 'DELETE':
        if ($student_id) {
            deleteStudent($student_id);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Student ID is required for deleting."]);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
}

// Close the database connection
mysqli_close($conn);

/**
 * Create a new student record in the database.
 */
function createStudent() {
    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);
    $firstname = $data['firstname'];
    $lastname = $data['lastname'];
    $email = $data['email'];
    $birthdate = $data['birthdate'];
    $course_id = $data['course_id'];

    // Validate required fields
    if (empty($firstname) || empty($lastname) || empty($email) || empty($birthdate) || empty($course_id)) {
        http_response_code(400);
        echo json_encode(["message" => "All fields are required."]);
        return;
    }

    // Check for duplicate email
    $email = mysqli_real_escape_string($conn, $email);
    $sqlCheck = "SELECT * FROM students WHERE email = '$email'";
    $resultCheck = mysqli_query($conn, $sqlCheck);
    if (mysqli_num_rows($resultCheck) > 0) {
        http_response_code(409);
        echo json_encode(["message" => "Duplicate email not allowed."]);
        return;
    }

    // Insert new student record
    $sql = "INSERT INTO students (firstname, lastname, email, birthdate, course_id) 
            VALUES ('$firstname', '$lastname', '$email', '$birthdate', $course_id)";
    if (mysqli_query($conn, $sql)) {
        http_response_code(201);
        echo json_encode(["message" => "Student created successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error: " . mysqli_error($conn)]);
    }
}

/**
 * Retrieve all student records.
 */
function getStudents() {
    global $conn;

    $sql = "SELECT s.id, s.firstname, s.lastname, s.email, s.birthdate, c.course_name 
            FROM students s 
            JOIN courses c ON s.course_id = c.course_id";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $students = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($students);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error fetching students: " . mysqli_error($conn)]);
    }
}

/**
 * Retrieve a specific student record by ID.
 */
function getStudent($id) {
    global $conn;

    $sql = "SELECT s.id, s.firstname, s.lastname, s.email, s.birthdate, c.course_name 
            FROM students s 
            JOIN courses c ON s.course_id = c.course_id 
            WHERE s.id = $id";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Student not found."]);
    }
}

/**
 * Search for students by name or email.
 */
function searchStudents($searchTerm) {
    global $conn;

    $searchTerm = mysqli_real_escape_string($conn, $searchTerm);
    $sql = "SELECT s.id, s.firstname, s.lastname, s.email, s.birthdate, c.course_name 
            FROM students s 
            JOIN courses c ON s.course_id = c.course_id 
            WHERE s.firstname LIKE '$searchTerm%' 
               OR s.email LIKE '$searchTerm%'";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $students = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($students);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error fetching students: " . mysqli_error($conn)]);
    }
}

/**
 * Filter students by course ID.
 */
function filterStudentsByCourse($course_id) {
    global $conn;

    $sql = "SELECT s.id, s.firstname, s.lastname, s.email, s.birthdate, c.course_name 
            FROM students s 
            JOIN courses c ON s.course_id = c.course_id 
            WHERE c.course_id = $course_id";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $students = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($students);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error fetching students: " . mysqli_error($conn)]);
    }
}

/**
 * Update an existing student record.
 */
function updateStudent($id) {
    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);
    $firstname = $data['firstname'];
    $lastname = $data['lastname'];
    $email = $data['email'];
    $birthdate = $data['birthdate'];
    $course_id = $data['course_id'];

    // Validate required fields
    if (empty($firstname) || empty($lastname) || empty($email) || empty($birthdate) || empty($course_id)) {
        http_response_code(400);
        echo json_encode(["message" => "All fields are required."]);
        return;
    }

    // Update student record
    $sql = "UPDATE students 
            SET firstname = '$firstname', lastname = '$lastname', email = '$email', 
                birthdate = '$birthdate', course_id = $course_id 
            WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        if (mysqli_affected_rows($conn) > 0) {
            echo json_encode(["message" => "Student updated successfully."]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Student not found."]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error updating student: " . mysqli_error($conn)]);
    }
}

/**
 * Delete a student record by ID.
 */
function deleteStudent($id) {
    global $conn;

    $sql = "DELETE FROM students WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        if (mysqli_affected_rows($conn) > 0) {
            echo json_encode(["message" => "Student deleted successfully."]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Student not found."]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error deleting student: " . mysqli_error($conn)]);
    }
}
?>
