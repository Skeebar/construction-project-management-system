<?php
// Determine current page to highlight active link
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<style>
  /* Navbar styles */
  nav {
    background-color: #1f1fff;
    padding: 12px 24px;
    font-family: 'Space Grotesk', sans-serif;
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: white;
    box-shadow: 0 3px 8px rgba(0,0,255,0.3);
  }

  nav .logo {
    font-weight: 700;
    font-size: 22px;
    letter-spacing: 1.5px;
  }

  nav ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    gap: 20px;
  }

  nav ul li {
    position: relative;
  }

  nav ul li a {
    color: white;
    text-decoration: none;
    font-weight: 600;
    padding: 8px 12px;
    border-radius: 6px;
    transition: background-color 0.3s ease;
    display: block;
  }

  nav ul li a:hover,
  nav ul li a.active {
    background-color: #0000cc;
  }

  /* Dropdown menu */
  nav ul li .dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background: #1f1fff;
    min-width: 180px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 1000;
    padding: 8px 0;
  }

  nav ul li .dropdown-menu a {
    padding: 10px 15px;
    white-space: nowrap;
  }

  nav ul li:hover .dropdown-menu {
    display: block;
  }

  nav ul li .dropdown-menu a:hover {
    background-color: #0000cc;
  }

  /* Responsive hamburger (optional) */
  @media (max-width: 600px) {
    nav {
      flex-wrap: wrap;
    }
    nav ul {
      width: 100%;
      justify-content: center;
      margin-top: 10px;
      gap: 10px;
      flex-direction: column;
    }
    nav ul li .dropdown-menu {
      position: relative;
    }
  }
</style>

<nav>
  <div class="logo">KwaSkeebar</div>
  <ul>
    <li><a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Dashboard</a></li>
    <li class="dropdown">
      <a href="project_list.php">Projects</a>
      <div class="dropdown-menu">
        <a href="add_project.php" class="<?= $currentPage === 'add_project.php' ? 'active' : '' ?>">Add Project</a>
      </div>
    </li>
    <li class="dropdown">
      <a href="employees.php">Employees</a>
      <div class="dropdown-menu">
        <a href="add_employee.php" class="<?= $currentPage === 'add_employee.php' ? 'active' : '' ?>">Add Employee</a>
      </div>
    </li>
 <li class="dropdown">
      <a href="quotations.php">Quotations</a>
      <div class="dropdown-menu">
        <a href="add_quotation.php" class="<?= $currentPage === 'add_quotation.php' ? 'active' : '' ?>">Add Quotation</a>
      </div>    
      <li><a href="expenses.php" class="<?= $currentPage === 'expenses.php' ? 'active' : '' ?>">Expenses</a></li>
      <li class="dropdown">
      <a href="client_list.php">Clients</a>
      <div class="dropdown-menu">
        <a href="add_client.php" class="<?= $currentPage === 'add_client.php' ? 'active' : '' ?>">Add Client</a>
      </div>
    </li>
        <li><a href="purchase_orders.php" class="<?= $currentPage === 'purchase_orders.php' ? 'active' : '' ?>">Purchase Order</a></li>
            <li><a href="transactions.php" class="<?= $currentPage === 'transactions.php' ? 'active' : '' ?>">Transactions</a></li>
    <li><a href="invoice_list.php" class="<?= $currentPage === 'invoice_list.php' ? 'active' : '' ?>">Invoices</a></li>
    <li><a href="logout.php">Logout</a></li>
  </ul>
</nav>
