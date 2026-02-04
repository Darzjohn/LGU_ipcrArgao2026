<table class="table table-bordered table-hover align-middle">
  <thead class="table-dark">
    <tr>
      <th>ID</th>
      <th>TD No</th>
      <th>Lot</th>
      <th>Barangay</th>
      <th>Location</th>
      <th>Owner</th>
      <th>Classification</th>
      <th>Assessed Value</th>
      <th>Effective Year</th>
      <th>Revision Year</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php while($row = $res->fetch_assoc()): ?>
    <tr>
      <td><?=$row['id']?></td>
      <td><?=htmlspecialchars($row['td_no'])?></td>
      <td><?=htmlspecialchars($row['lot_no'])?></td>
      <td><?=htmlspecialchars($row['barangay'])?></td>
      <td><?=htmlspecialchars($row['location'])?></td>
      <td><?=htmlspecialchars($row['owner_name'])?></td>
      <td><?=htmlspecialchars($row['classification'])?></td>
      <td>₱<?=number_format($row['assessed_value'],2)?></td>
      <td><?=$row['effective_year']?></td>
      <td><?=$row['revision_year']?></td>
      <td>
        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editPropertyModal<?=$row['id']?>">Edit</button>
        <a href="?delete_id=<?=$row['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this property?')">Delete</a>
        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addAssessmentModal<?=$row['id']?>">Add Assessment</button>
      </td>
    </tr>

    <!-- Include Edit Property Modal -->
    <?php include 'modals/edit_property_modal.php'; ?>

    <!-- Include Add/Edit Assessment Modals -->
    <?php include 'modals/add_assessment_modal.php'; ?>
    <?php include 'modals/edit_assessment_modals.php'; ?>

    <?php endwhile; ?>
  </tbody>
</table>

<!-- Pagination -->
<nav>
  <ul class="pagination justify-content-center mt-3">
    <li class="page-item <?=($page<=1?'disabled':'')?>"><a class="page-link" href="?page=<?=$page-1?>&search=<?=urlencode($search)?>">Previous</a></li>
    <?php for($i=1;$i<=$total_pages;$i++): ?>
    <li class="page-item <?=($i==$page?'active':'')?>"><a class="page-link" href="?page=<?=$i?>&search=<?=urlencode($search)?>"><?=$i?></a></li>
    <?php endfor; ?>
    <li class="page-item <?=($page>=$total_pages?'disabled':'')?>"><a class="page-link" href="?page=<?=$page+1?>&search=<?=urlencode($search)?>">Next</a></li>
  </ul>
</nav>
