<div class="row g-2">
  <div class="col-md-6">
    <?php
    $slot = ['name' => 'col-1', 'data' => $slots['col-1'] ?? null];
    include(APPPATH . 'Views/pages/layouts/partials/slot_content.php');
    ?>
  </div>
  <div class="col-md-6">
    <?php
    $slot = ['name' => 'col-2', 'data' => $slots['col-2'] ?? null];
    include(APPPATH . 'Views/pages/layouts/partials/slot_content.php');
    ?>
  </div>
</div>