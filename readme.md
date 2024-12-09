# Student Data Management API

## **Project Description**
The Student Data Management API allows users to manage student information, including creating, reading, updating, and deleting student records. It supports filtering and searching students by name, email, or course. API key-based authentication ensures secure access to the system.

### **Key Features**
- CRUD operations for managing student data.
- Filter students by course.
- Search students by name or email.
- API key authentication for secure access.
- Validation and error handling for data integrity.

---

## **Setup Instructions**

### **1. Prerequisites**
- Install [XAMPP](https://www.apachefriends.org/index.html) (or any local PHP/MySQL environment).
- Postman or a similar API testing tool.

### **2. Import the `.sql` file**
1. Open phpMyAdmin (typically accessible at `http://localhost/phpmyadmin`).
2. Create a new database named `student_management`.
3. Import the provided `student_management.sql` file into the `student_management` database.

### **3. Configure the Database Connection**
1. Locate the `db.php` file in the project folder.
2. Update the file with your database credentials:
   ```php
   <?php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "student_management";

   $conn = mysqli_connect($servername, $username, $password, $dbname);

   if (!$conn) {
       die("Connection failed: " . mysqli_connect_error());
   }
   ?>.


### **Start the Server**
1. Place the project folder in the htdocs directory of XAMPP.
2. Start Apache and MySQL from the XAMPP control panel.
3. Use Postman.
    Access the API at 'http://localhost/it311finals/.
    API Endpoints
    Authentication
    All API requests must include an X-API-KEY header with the value: Idol-sirMhezel.
    Example:
    X-API-KEY: Idol-sirMhezel

1. **Create a Student**
HTTP Method: POST
Endpoint: /students.php
Request Body (JSON):
json
Copy code
{
    "firstname": "John",
    "lastname": "Doe",
    "email": "john.doe@example.com",
    "birthdate": "2000-01-01",
    "course_id": 1
}
Example Response:
json
Copy code
{
    "message": "Student created successfully."
}
2. **Get All Students**
HTTP Method: GET
Endpoint: /students.php
Example Response:
json
Copy code
[
    {
        "id": 1,
        "firstname": "John",
        "lastname": "Doe",
        "email": "john.doe@example.com",
        "birthdate": "2000-01-01",
        "course_name": "Computer Science"
    }
]
3. **Get a Student by ID**
HTTP Method: GET
Endpoint: /students.php?id=1
Example Response:
json
Copy code
{
    "id": 1,
    "firstname": "John",
    "lastname": "Doe",
    "email": "john.doe@example.com",
    "birthdate": "2000-01-01",
    "course_name": "Computer Science"
}
4. **Search Students**
HTTP Method: GET
Endpoint: /students.php?search=John
Example Response:
json
Copy code
[
    {
        "id": 1,
        "firstname": "John",
        "lastname": "Doe",
        "email": "john.doe@example.com",
        "birthdate": "2000-01-01",
        "course_name": "Computer Science"
    }
]
5. **Filter Students by Course**
HTTP Method: GET
Endpoint: /students.php?course_id=1
Example Response:
json
Copy code
[
    {
        "id": 2,
        "firstname": "Jane",
        "lastname": "Smith",
        "email": "jane.smith@example.com",
        "birthdate": "1999-12-25",
        "course_name": "Business Administration"
    }
]
6. **Update a Student**
HTTP Method: PUT
Endpoint: /students.php?id=1
Request Body (JSON):
json
Copy code
{
    "firstname": "Johnny",
    "lastname": "Doe",
    "email": "johnny.doe@example.com",
    "birthdate": "2000-01-01",
    "course_id": 2
}
Example Response:
json
Copy code
{
    "message": "Student updated successfully."
}
7. **Delete a Student**
HTTP Method: DELETE
Endpoint: /students.php?id=1
Example Response:
json
Copy code
{
    "message": "Student deleted successfully."
}