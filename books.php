<?php
include "D:/xampp/htdocs/library-api/db.php";

header("Content-Type: application/json");

$requestMethod = $_SERVER["REQUEST_METHOD"]; // GET, POST, PUT, DELETE
$book_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$keyword = isset($_GET['search']) ? $_GET['search'] : null;
$filter_genre = isset($_GET['genre']) ? $_GET['genre'] : null;
$filter_year = isset($_GET['year']) ? intval($_GET['year']) : null;

switch ($requestMethod) {
    case 'POST':
        createBook();
        break;
    case 'GET':
        if ($keyword) {
            searchBooks($keyword);
        } elseif ($filter_genre || $filter_year) {
            filterBooks($filter_genre, $filter_year);
        } elseif ($book_id) {
            getBook($book_id);
        } else {
            getBooks();
        }
        break;
    case 'DELETE':
        if ($book_id) {
            deleteBook($book_id);
        } else {
            deleteAllBooks();
        }
        break;
    case 'PUT':
        if ($book_id) {
            updateBook($book_id);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Book ID is required."]);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
}

mysqli_close($conn);

// Create Book
function createBook() {
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);

    $title = $data['title'] ?? null;
    $author = $data['author'] ?? null;
    $genre = $data['genre'] ?? null;
    $published_year = $data['published_year'] ?? null;

    if (!$title || !$author || !$genre || !$published_year) {
        http_response_code(400);
        echo json_encode(["message" => "All fields are required."]);
        return;
    }

    $sql = "INSERT INTO books (title, author, genre, published_year) VALUES ('$title', '$author', '$genre', $published_year)";
    if (mysqli_query($conn, $sql)) {
        http_response_code(201);
        echo json_encode(["message" => "Book created successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error creating book: " . mysqli_error($conn)]);
    }
}

// Get All Books
function getBooks() {
    global $conn;
    $sql = "SELECT * FROM books";
    $result = mysqli_query($conn, $sql);

    $books = mysqli_fetch_all($result, MYSQLI_ASSOC);
    echo json_encode($books);
}

// Get Single Book
function getBook($id) {
    global $conn;
    $sql = "SELECT * FROM books WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Book not found."]);
    }
}

// Search for Books
function searchBooks($keyword) {
    global $conn;
    $keyword = mysqli_real_escape_string($conn, $keyword);
    $sql = "SELECT * FROM books WHERE title LIKE '%$keyword%'";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $books = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($books);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error searching books: " . mysqli_error($conn)]);
    }
}

// Filter Books
function filterBooks($genre, $year) {
    global $conn;
    $conditions = [];

    if ($genre) {
        $genre = mysqli_real_escape_string($conn, $genre);
        $conditions[] = "genre = '$genre'";
    }
    if ($year) {
        $conditions[] = "published_year = $year";
    }

    $whereClause = implode(' AND ', $conditions);
    $sql = "SELECT * FROM books" . ($whereClause ? " WHERE $whereClause" : "");
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $books = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($books);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error filtering books: " . mysqli_error($conn)]);
    }
}

// Update Book
function updateBook($id) {
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);

    $title = $data['title'] ?? null;
    $author = $data['author'] ?? null;
    $genre = $data['genre'] ?? null;
    $published_year = $data['published_year'] ?? null;

    if (!$title || !$author || !$genre || !$published_year) {
        http_response_code(400);
        echo json_encode(["message" => "All fields are required."]);
        return;
    }

    $sql = "UPDATE books SET title = '$title', author = '$author', genre = '$genre', published_year = $published_year WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        if (mysqli_affected_rows($conn) > 0) {
            echo json_encode(["message" => "Book updated successfully."]);
        } else {
            echo json_encode(["message" => "Book not found."]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error updating book: " . mysqli_error($conn)]);
    }
}

// Delete Single Book
function deleteBook($id) {
    global $conn;
    $sql = "DELETE FROM books WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        if (mysqli_affected_rows($conn) > 0) {
            echo json_encode(["message" => "Book deleted successfully."]);
        } else {
            echo json_encode(["message" => "Book not found."]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error deleting book: " . mysqli_error($conn)]);
    }
}

// Delete All Books
function deleteAllBooks() {
    global $conn;
    $sql = "DELETE FROM books";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(["message" => "All books deleted successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error deleting books: " . mysqli_error($conn)]);
    }
}
?>
