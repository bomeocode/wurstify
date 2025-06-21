<div class="row g-2" style="min-height: 80vh;">
  <div class="col-md-6">
    <div class="d-flex flex-column h-100">
      <div class="flex-fill pb-1">
        <?php
        $slot = ['name' => 'col-1-top', 'data' => $slots['col-1-top'] ?? null];
        include(APPPATH . 'Views/pages/layouts/partials/slot_content.php');
        ?>
      </div>
      <div class="flex-fill pt-1">
        <?php
        $slot = ['name' => 'col-1-bottom', 'data' => $slots['col-1-bottom'] ?? null];
        include(APPPATH . 'Views/pages/layouts/partials/slot_content.php');
        ?>
      </div>
    </div>
  </div>
  <div class="col-md-6 d-flex">
    <?php
    $slot = ['name' => 'col-2', 'data' => $slots['col-2'] ?? null];
    include(APPPATH . 'Views/pages/layouts/partials/slot_content.php');
    ?>
  </div>
</div>