<?php
require_once 'db.php';

// Fetch Form51 data (example)
$form51_id = $_GET['id'];
$form = $conn->query("SELECT * FROM form51 WHERE id = $form51_id")->fetch_assoc();
$payments = $conn->query("SELECT * FROM form51_payments WHERE form51_id = $form51_id")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Form51</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<h2>Edit Form51</h2>
<form action="update_form51.php" method="post">
    <input type="hidden" name="form51_id" value="<?= $form51_id ?>">

    <table id="payments_table" border="1">
        <thead>
            <tr>
                <th>NGAS Code</th>
                <th>Nature of Collection</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($payments as $p): ?>
            <tr>
                <td><input type="text" name="ngas_code[]" value="<?= htmlspecialchars($p['ngas_code']) ?>" required></td>
                <td><input type="text" name="nature_of_collection[]" value="<?= htmlspecialchars($p['nature_of_collection']) ?>" required></td>
                <td><input type="number" class="amount" name="amount[]" value="<?= $p['amount'] ?>" step="0.01" required></td>
                <td><button type="button" class="remove_row">Remove</button></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <button type="button" id="add_row">Add Payment</button><br><br>

    <!-- Grand Total -->
    <h3>Grand Total: ₱<span id="grand_total">0.00</span></h3>
    <input type="hidden" name="grand_total" id="grand_total_input" value="0.00">

    <button type="submit">Update Form51</button>
</form>

<script>
$(document).ready(function(){

    function updateGrandTotal(){
        let total = 0;
        $('.amount').each(function(){
            let val = parseFloat($(this).val());
            if(!isNaN(val)) total += val;
        });
        $('#grand_total').text(total.toFixed(2));
        $('#grand_total_input').val(total.toFixed(2)); // update hidden input
    }

    updateGrandTotal(); // initial total

    $(document).on('input', '.amount', function(){
        updateGrandTotal();
    });

    $('#add_row').click(function(){
        $('#payments_table tbody').append(`
            <tr>
                <td><input type="text" name="ngas_code[]" required></td>
                <td><input type="text" name="nature_of_collection[]" required></td>
                <td><input type="number" class="amount" name="amount[]" step="0.01" required></td>
                <td><button type="button" class="remove_row">Remove</button></td>
            </tr>
        `);
        updateGrandTotal();
    });

    $(document).on('click', '.remove_row', function(){
        $(this).closest('tr').remove();
        updateGrandTotal();
    });

});
</script>

</body>
</html>
