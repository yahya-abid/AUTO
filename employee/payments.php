<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!$auth->checkRole(ROLE_EMPLOYEE) && !$auth->checkRole(ROLE_ADMIN)) {
    header("Location: ../index.php");
    exit();
}

$location_id = $_SESSION['location_id'];
$employee_id = $_SESSION['user_id'];

$message = '';
$error = '';

// Handle payment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['receive_payment'])) {
        $contrat_id = $_POST['contrat_id'];
        $montant = $_POST['montant'];
        $mode_payement = $_POST['mode_payement'];
        $transaction_id = $functions->sanitize($_POST['transaction_id'] ?? '');
        $notes = $functions->sanitize($_POST['notes'] ?? '');
        
        // Generate payment reference
        $paiement_reference = 'PAY-' . date('Ymd') . '-' . $functions->generateRandomString(6);
        
        $sql = "INSERT INTO paiement (
                    paiement_reference, montant, mode_payement, status,
                    transaction_id, contrat_id, employee_id, notes
                ) VALUES (?, ?, ?, 'completed', ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssiis", 
            $paiement_reference, $montant, $mode_payement,
            $transaction_id, $contrat_id, $employee_id, $notes
        );
        
        if ($stmt->execute()) {
            // Check if contract is fully paid
            $check_sql = "SELECT c.prix_total, SUM(p.montant) as total_paid
                          FROM contrat c
                          LEFT JOIN paiement p ON c.contrat_id = p.contrat_id AND p.status = 'completed'
                          WHERE c.contrat_id = ?
                          GROUP BY c.contrat_id";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $contrat_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result()->fetch_assoc();
            
            if ($result && $result['total_paid'] >= $result['prix_total']) {
                // Update contract status to paid
                $update_sql = "UPDATE contrat SET status_contrat = 'active' WHERE contrat_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $contrat_id);
                $update_stmt->execute();
            }
            
            $message = 'Payment recorded successfully. Reference: ' . $paiement_reference;
        } else {
            $error = 'Failed to record payment';
        }
    } elseif (isset($_POST['issue_refund'])) {
        $paiement_id = $_POST['paiement_id'];
        $refund_amount = $_POST['refund_amount'];
        $refund_reason = $functions->sanitize($_POST['refund_reason']);
        
        // Get payment details
        $payment_sql = "SELECT * FROM paiement WHERE paiement_id = ?";
        $payment_stmt = $conn->prepare($payment_sql);
        $payment_stmt->bind_param("i", $paiement_id);
        $payment_stmt->execute();
        $payment = $payment_stmt->get_result()->fetch_assoc();
        
        if ($refund_amount > $payment['montant']) {
            $error = 'Refund amount cannot exceed original payment';
        } else {
            // Create refund record
            $refund_sql = "INSERT INTO compensation (
                            type_comp, montant, description, contrat_id,
                            processed_by_employee_id, status
                          ) VALUES ('remboursement', ?, ?, ?, ?, 'completed')";
            
            $refund_stmt = $conn->prepare($refund_sql);
            $refund_stmt->bind_param("dsii", $refund_amount, $refund_reason, $payment['contrat_id'], $employee_id);
            
            if ($refund_stmt->execute()) {
                $message = 'Refund issued successfully';
            } else {
                $error = 'Failed to issue refund';
            }
        }
    }
}

// Get payments for this location
$payments_sql = "SELECT p.*, 
                        c.contrat_number, cl.first_name, cl.last_name,
                        car.matriculation, car.marque, car.model
                 FROM paiement p
                 JOIN contrat c ON p.contrat_id = c.contrat_id
                 JOIN client cl ON c.client_id = cl.client_id
                 JOIN car car ON c.car_id = car.car_id
                 WHERE c.location_id = ?
                 ORDER BY p.date_pay DESC";
$stmt = $conn->prepare($payments_sql);
$stmt->bind_param("i", $location_id);
$stmt->execute();
$payments = $stmt->get_result();

// Get contracts needing payment
$unpaid_contracts_sql = "SELECT c.*, 
                                cl.first_name, cl.last_name,
                                car.matriculation, car.marque, car.model,
                                (SELECT SUM(montant) FROM paiement WHERE contrat_id = c.contrat_id AND status = 'completed') as paid_amount
                         FROM contrat c
                         JOIN client cl ON c.client_id = cl.client_id
                         JOIN car car ON c.car_id = car.car_id
                         WHERE c.location_id = ? 
                         AND c.status_contrat = 'confirmed'
                         HAVING paid_amount < c.prix_total OR paid_amount IS NULL";
$unpaid_stmt = $conn->prepare($unpaid_contracts_sql);
$unpaid_stmt->bind_param("i", $location_id);
$unpaid_stmt->execute();
$unpaid_contracts = $unpaid_stmt->get_result();

// Get today's payments
$today_sql = "SELECT SUM(montant) as total, COUNT(*) as count 
              FROM paiement p
              JOIN contrat c ON p.contrat_id = c.contrat_id
              WHERE c.location_id = ? 
              AND DATE(p.date_pay) = CURDATE() 
              AND p.status = 'completed'";
$today_stmt = $conn->prepare($today_sql);
$today_stmt->bind_param("i", $location_id);
$today_stmt->execute();
$today_stats = $today_stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - Employee Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="dashboard.php" class="logo">Employee Panel</a>
            <nav class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="reservations.php">Reservations</a>
                <a href="contracts.php">Contracts</a>
                <a href="payments.php" class="active">Payments</a>
                <a href="cars.php">Cars</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div style="margin: 2rem 0;">
            <h1>Manage Payments</h1>
            <p>Receive payments and manage transactions</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="dashboard-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <h3>Today's Revenue</h3>
                <div class="number"><?php echo CURRENCY . number_format($today_stats['total'] ?? 0, 2); ?></div>
                <p><?php echo $today_stats['count'] ?? 0; ?> transactions</p>
            </div>
            <div class="stat-card">
                <h3>Unpaid Contracts</h3>
                <div class="number">
                    <?php echo $unpaid_contracts->num_rows; ?>
                </div>
                <a href="#unpaidSection" class="btn btn-primary" style="margin-top: 1rem;">View Unpaid</a>
            </div>
            <div class="stat-card">
                <h3>Payment Methods</h3>
                <div style="font-size: 2rem; margin: 1rem 0;">
                    <i class="fas fa-credit-card"></i>
                    <i class="fas fa-money-bill-wave"></i>
                    <i class="fas fa-university"></i>
                </div>
            </div>
            <div class="stat-card">
                <h3>Quick Action</h3>
                <button class="btn btn-success" onclick="toggleReceivePayment()" style="margin-top: 1rem;">
                    <i class="fas fa-money-check-alt"></i> Receive Payment
                </button>
            </div>
        </div>

        <!-- Receive Payment Form -->
        <div class="card" id="receivePaymentForm" style="display: none; margin-bottom: 2rem;">
            <div class="card-header">
                <h2>Receive Payment</h2>
            </div>
            <div style="padding: 1.5rem;">
                <form method="POST" action="" id="paymentForm">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label for="contrat_id">Contract *</label>
                            <select id="contrat_id" name="contrat_id" class="form-control" required>
                                <option value="">Select Contract</option>
                                <?php 
                                $unpaid_contracts->data_seek(0);
                                while($contract = $unpaid_contracts->fetch_assoc()): 
                                    $remaining = $contract['prix_total'] - ($contract['paid_amount'] ?? 0);
                                ?>
                                <option value="<?php echo $contract['contrat_id']; ?>" 
                                        data-remaining="<?php echo $remaining; ?>"
                                        data-total="<?php echo $contract['prix_total']; ?>">
                                    <?php echo htmlspecialchars($contract['contrat_number'] . ' - ' . $contract['first_name'] . ' ' . $contract['last_name'] . ' (' . CURRENCY . number_format($remaining, 2) . ' due)'); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="montant">Amount *</label>
                            <input type="number" step="0.01" id="montant" name="montant" class="form-control" required min="0">
                        </div>
                        <div class="form-group">
                            <label for="mode_payement">Payment Method *</label>
                            <select id="mode_payement" name="mode_payement" class="form-control" required>
                                <option value="cash">Cash</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="check">Check</option>
                                <option value="mobile_payment">Mobile Payment</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="transaction_id">Transaction ID</label>
                            <input type="text" id="transaction_id" name="transaction_id" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin: 1.5rem 0;">
                        <h4>Payment Summary</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <div>
                                <strong>Contract Total:</strong>
                                <span id="contractTotal"><?php echo CURRENCY; ?>0.00</span>
                            </div>
                            <div>
                                <strong>Already Paid:</strong>
                                <span id="alreadyPaid"><?php echo CURRENCY; ?>0.00</span>
                            </div>
                            <div>
                                <strong>Remaining Due:</strong>
                                <span id="remainingDue" style="color: #e74c3c;"><?php echo CURRENCY; ?>0.00</span>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="receive_payment" class="btn btn-primary">
                        <i class="fas fa-check-circle"></i> Record Payment
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="toggleReceivePayment()">
                        Cancel
                    </button>
                </form>
            </div>
        </div>

        <!-- Unpaid Contracts -->
        <div class="card" id="unpaidSection">
            <div class="card-header">
                <h2>Unpaid Contracts</h2>
            </div>
            <?php if ($unpaid_contracts->num_rows > 0): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Contract #</th>
                            <th>Client</th>
                            <th>Car</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Due Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $unpaid_contracts->data_seek(0);
                        while($contract = $unpaid_contracts->fetch_assoc()): 
                            $paid = $contract['paid_amount'] ?? 0;
                            $due = $contract['prix_total'] - $paid;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($contract['contrat_number']); ?></td>
                            <td><?php echo htmlspecialchars($contract['first_name'] . ' ' . $contract['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($contract['marque'] . ' ' . $contract['model']); ?></td>
                            <td><?php echo CURRENCY . number_format($contract['prix_total'], 2); ?></td>
                            <td>
                                <span style="color: #2ecc71;">
                                    <?php echo CURRENCY . number_format($paid, 2); ?>
                                </span>
                            </td>
                            <td>
                                <span style="color: #e74c3c; font-weight: bold;">
                                    <?php echo CURRENCY . number_format($due, 2); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-primary" 
                                        onclick="fillPaymentForm(<?php echo $contract['contrat_id']; ?>, <?php echo $due; ?>)">
                                    <i class="fas fa-money-check-alt"></i> Receive Payment
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div style="padding: 2rem; text-align: center; color: #666;">
                <p>All contracts are fully paid.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Recent Payments -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2>Recent Payments</h2>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Payment #</th>
                            <th>Contract #</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($payment = $payments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($payment['date_pay'])); ?></td>
                            <td><?php echo htmlspecialchars($payment['paiement_reference']); ?></td>
                            <td><?php echo htmlspecialchars($payment['contrat_number']); ?></td>
                            <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                            <td><?php echo CURRENCY . number_format($payment['montant'], 2); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $payment['mode_payement'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo $payment['status'] == 'completed' ? 'status-completed' : 'status-pending'; ?>">
                                    <?php echo ucfirst($payment['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="payment-details.php?id=<?php echo $payment['paiement_id']; ?>" 
                                   class="btn btn-primary">View</a>
                                <?php if ($payment['status'] == 'completed' && $payment['mode_payement'] != 'cash'): ?>
                                    <button class="btn btn-warning" onclick="openRefundModal(<?php echo $payment['paiement_id']; ?>, <?php echo $payment['montant']; ?>)">
                                        Refund
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Refund Modal -->
    <div class="modal" id="refundModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Issue Refund</h3>
                <button class="modal-close" onclick="closeRefundModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="refundForm">
                    <input type="hidden" name="paiement_id" id="refundPaymentId">
                    
                    <div class="form-group">
                        <label for="refund_amount">Refund Amount *</label>
                        <input type="number" step="0.01" id="refund_amount" name="refund_amount" class="form-control" required min="0">
                        <small>Maximum refundable: <span id="maxRefund"><?php echo CURRENCY; ?>0.00</span></small>
                    </div>
                    
                    <div class="form-group">
                        <label for="refund_reason">Reason for Refund *</label>
                        <textarea id="refund_reason" name="refund_reason" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="submit" name="issue_refund" class="btn btn-primary">
                            Issue Refund
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeRefundModal()">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleReceivePayment() {
            const form = document.getElementById('receivePaymentForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            if (form.style.display === 'block') {
                form.scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        function fillPaymentForm(contractId, dueAmount) {
            toggleReceivePayment();
            document.getElementById('contrat_id').value = contractId;
            document.getElementById('montant').value = dueAmount.toFixed(2);
            
            // Update payment summary
            const selectedOption = document.querySelector(`#contrat_id option[value="${contractId}"]`);
            if (selectedOption) {
                const total = parseFloat(selectedOption.getAttribute('data-total'));
                const remaining = parseFloat(selectedOption.getAttribute('data-remaining'));
                const paid = total - remaining;
                
                document.getElementById('contractTotal').textContent = CURRENCY + total.toFixed(2);
                document.getElementById('alreadyPaid').textContent = CURRENCY + paid.toFixed(2);
                document.getElementById('remainingDue').textContent = CURRENCY + remaining.toFixed(2);
            }
        }
        
        function openRefundModal(paymentId, maxAmount) {
            document.getElementById('refundPaymentId').value = paymentId;
            document.getElementById('refund_amount').max = maxAmount;
            document.getElementById('refund_amount').value = maxAmount;
            document.getElementById('maxRefund').textContent = CURRENCY + maxAmount.toFixed(2);
            
            document.getElementById('refundModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeRefundModal() {
            document.getElementById('refundModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Update payment summary when contract is selected
        document.getElementById('contrat_id').addEventListener('change', function() {
            const selectedOption = this.selectedOptions[0];
            if (selectedOption && selectedOption.value) {
                const total = parseFloat(selectedOption.getAttribute('data-total'));
                const remaining = parseFloat(selectedOption.getAttribute('data-remaining'));
                const paid = total - remaining;
                
                document.getElementById('contractTotal').textContent = CURRENCY + total.toFixed(2);
                document.getElementById('alreadyPaid').textContent = CURRENCY + paid.toFixed(2);
                document.getElementById('remainingDue').textContent = CURRENCY + remaining.toFixed(2);
                document.getElementById('montant').value = remaining.toFixed(2);
                document.getElementById('montant').max = remaining;
            }
        });
        
        // Validate payment amount
        document.getElementById('montant').addEventListener('input', function() {
            const remaining = parseFloat(document.getElementById('remainingDue').textContent.replace(CURRENCY, ''));
            const entered = parseFloat(this.value);
            
            if (entered > remaining) {
                this.style.borderColor = '#e74c3c';
                this.setCustomValidity('Amount cannot exceed remaining due');
            } else {
                this.style.borderColor = '';
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>