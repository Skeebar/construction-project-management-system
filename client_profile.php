<?php
require 'db.php';
include 'nav.php';

$clientId = intval($_GET['id'] ?? 0);

if (!$clientId) {
    die("Client ID missing.");
}

// Fetch client info
$client = $conn->query("SELECT * FROM clients WHERE id = $clientId")->fetch_assoc();
if (!$client) {
    die("Client not found.");
}

// Fetch client projects
$projects = $conn->query("SELECT * FROM projects WHERE client_name = '" . $conn->real_escape_string($client['name']) . "' ORDER BY start_date DESC");

// Fetch client documents
$documents = $conn->query("SELECT * FROM client_documents WHERE client_id = $clientId ORDER BY uploaded_at DESC");

// Fetch client invoices
$invoices = $conn->query("SELECT * FROM invoices WHERE client_id = $clientId ORDER BY invoice_date DESC");

// Fetch client quotations (fixed: using client_name)
$quotations = $conn->query("SELECT * FROM quotations WHERE client_name = '" . $conn->real_escape_string($client['name']) . "' ORDER BY created_at DESC");

// Handle document upload
$uploadError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file = $_FILES['document'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        if (in_array(strtolower($ext), $allowed)) {
            $newName = uniqid() . '.' . $ext;
            $uploadDir = 'uploads/client_docs/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $targetPath = $uploadDir . $newName;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $stmt = $conn->prepare("INSERT INTO client_documents (client_id, file_name, file_path) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $clientId, $file['name'], $targetPath);
                $stmt->execute();
                header("Location: client_profile.php?id=$clientId");
                exit;
            } else {
                $uploadError = "Failed to move uploaded file.";
            }
        } else {
            $uploadError = "Invalid file type. Allowed: pdf, doc, docx, jpg, png.";
        }
    } else {
        $uploadError = "File upload error code: " . $file['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Client Profile - <?= htmlspecialchars($client['name']) ?></title>
<style>
  body { font-family: Space Grotesk, sans-serif; background: #f4f7fb; margin: 20px; }
  .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
  h1 { color: #004080; margin-bottom: 10px; }
  section { margin-bottom: 40px; }
  h2 { border-bottom: 2px solid #004080; padding-bottom: 5px; color: #004080; }
  table { width: 100%; border-collapse: collapse; margin-top: 15px; }
  th, td { border: 1px solid #ccc; padding: 10px; }
  th { background-color: #004080; color: white; text-align: left; }
  a.button { display: inline-block; margin-top: 10px; background: #004080; color: white; padding: 8px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; }
  a.button:hover { background: #00264d; }
  .error { color: red; margin-top: 10px; }
  .doc-list a { text-decoration: none; color: #004080; }
  .doc-list a:hover { text-decoration: underline; }
  form label { font-weight: bold; display: block; margin: 10px 0 5px; }
  form input[type=file] { display: block; }
  form button { margin-top: 12px; background: #004080; color: white; border: none; padding: 8px 18px; border-radius: 6px; cursor: pointer; }
  form button:hover { background: #00264d; }
</style>
</head>
<body>
<div class="container">

  <h1>Client Profile: <?= htmlspecialchars($client['name']) ?></h1>
  <a href="edit_client.php?id=<?= $clientId ?>" class="button">✏️ Edit Client</a>
  <a href="print_client.php?id=<?= $clientId ?>" target="_blank" class="button">🖨 Print / Export PDF</a>

  <section>
    <h2>Contact Information</h2>
    <p><strong>Email:</strong> <?= htmlspecialchars($client['email']) ?: '<em>Not Provided</em>' ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($client['phone']) ?: '<em>Not Provided</em>' ?></p>
    <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($client['address'])) ?: '<em>Not Provided</em>' ?></p>
  </section>

  <section>
    <h2>Past Projects</h2>
    <?php if ($projects->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Project Name</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>View</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($proj = $projects->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($proj['name']) ?></td>
              <td><?= htmlspecialchars($proj['start_date']) ?></td>
              <td><?= htmlspecialchars($proj['end_date']) ?></td>
              <td><?= htmlspecialchars($proj['status']) ?></td>
              <td><a href="view_project.php?id=<?= $proj['id'] ?>">View</a></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No projects found for this client.</p>
    <?php endif; ?>
  </section>

  <section>
    <h2>Uploaded Documents</h2>
    <?php if ($documents->num_rows > 0): ?>
      <ul class="doc-list">
        <?php while ($doc = $documents->fetch_assoc()): ?>
          <li><a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank"><?= htmlspecialchars($doc['file_name']) ?></a> (Uploaded: <?= $doc['uploaded_at'] ?>)</li>
        <?php endwhile; ?>
      </ul>
    <?php else: ?>
      <p>No documents uploaded yet.</p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <label for="document">Upload Document (pdf, doc, jpg, png):</label>
      <input type="file" name="document" id="document" required />
      <button type="submit">Upload</button>
      <?php if ($uploadError): ?>
        <p class="error"><?= htmlspecialchars($uploadError) ?></p>
      <?php endif; ?>
    </form>
  </section>

  <section>
    <h2>Invoices</h2>
    <?php if ($invoices->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Invoice #</th>
            <th>Date</th>
            <th>Amount (R)</th>
            <th>Status</th>
            <th>View</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($inv = $invoices->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($inv['invoice_number'] ?? $inv['id']) ?></td>
              <td><?= htmlspecialchars($inv['invoice_date']) ?></td>
              <td><?= number_format($inv['amount'], 2) ?></td>
              <td><?= htmlspecialchars($inv['status'] ?? 'N/A') ?></td>
              <td><a href="view_invoice.php?id=<?= $inv['id'] ?>">View</a></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No invoices found.</p>
    <?php endif; ?>
    <a href="send_invoice.php?client_id=<?= $clientId ?>" class="button">Send Invoice</a>
  </section>

  <section>
    <h2>Quotations</h2>
    <?php if ($quotations->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Quotation #</th>
            <th>Date</th>
            <th>Amount (R)</th>
            <th>Status</th>
            <th>View</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($quote = $quotations->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($quote['quotation_number'] ?? $quote['id']) ?></td>
              <td><?= htmlspecialchars($quote['quotation_date']) ?></td>
              <td><?= number_format($quote['total_amount'], 2) ?></td>
              <td><?= htmlspecialchars($quote['status'] ?? 'N/A') ?></td>
              <td><a href="view_quotation.php?id=<?= $quote['id'] ?>">View</a></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No quotations found.</p>
    <?php endif; ?>
    <a href="send_quotation.php?client_id=<?= $clientId ?>" class="button">Send Quotation</a>
  </section>

</div>
</body>
</html>
