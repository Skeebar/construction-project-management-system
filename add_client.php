<?php
require 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if (!$name) {
        $error = "Client name is required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO clients (name, email, phone, address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $phone, $address);
        if ($stmt->execute()) {
            header("Location: client_list.php");
            exit;
        } else {
            $error = "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Add New Client</title>
<style>
  body {
    font-family: Arial, sans-serif;
    background: #f0f4f8;
    margin: 0; padding: 0;
    display: flex; justify-content: center; align-items: center; height: 100vh;
  }
  .container {
    background: white;
    padding: 30px 40px;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    width: 400px;
  }
  h2 {
    margin-bottom: 20px;
    text-align: center;
    color: #333;
  }
  label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
  }
  input[type=text], input[type=email], textarea {
    width: 100%;
    padding: 8px 12px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
  }
  textarea {
    resize: vertical;
  }
  button {
    margin-top: 25px;
    width: 100%;
    padding: 10px;
    background-color: #004080;
    color: white;
    border: none;
    font-size: 16px;
    border-radius: 6px;
    cursor: pointer;
  }
  button:hover {
    background-color: #00264d;
  }
  .error {
    color: red;
    margin-top: 15px;
    font-weight: bold;
    text-align: center;
  }
</style>
</head>
<body>

<div class="container">
  <h2>Add New Client</h2>
  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST" action="">
    <label for="name">Client Name *</label>
    <input type="text" id="name" name="name" required />

    <label for="email">Email</label>
    <input type="email" id="email" name="email" />

    <label for="phone">Phone</label>
    <input type="text" id="phone" name="phone" />

    <label for="address">Address</label>
    <textarea id="address" name="address" rows="3"></textarea>

    <button type="submit">Add Client</button>
  </form>
</div>

</body>
</html>
