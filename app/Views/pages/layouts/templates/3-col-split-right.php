<?php // Vorlage: 3 Spalten, rechts geteilt 
?>
<div class="row g-2">
  <div class="col-4">
    <?php
    $slot = ['name' => 'col-1', 'data' => $slots['col-1'] ?? null];
    include(APPPATH . 'Views/pages/layouts/partials/slot_content.php');
    ?>
  </div>
  <div class="col-4">
    <?php
    $slot = ['name' => 'col-2', 'data' => $slots['col-2'] ?? null];
    include(APPPATH . 'Views/pages/layouts/partials/slot_content.php');
    ?>
  </div>
  <div class="col-4">
    <div class="row g-2">
      <div class="col-12" style="height: 50vh;">
        <?php
        $slot = ['name' => 'col-3-top', 'data' => $slots['col-3-top'] ?? null];
        include(APPPATH . 'Views/pages/layouts/partials/slot_content.php');
        ?>
      </div>
      <div class="col-12" style="height: 50vh;">
        <?php
        $slot = ['name' => 'col-3-bottom', 'data' => $slots['col-3-bottom'] ?? null];
        include(APPPATH . 'Views/pages/layouts/partials/slot_content.php');
        ?>
      </div>
    </div>
  </div>
</div>