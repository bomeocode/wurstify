<div class="row g-2" style="min-height: 80vh;">
  <div class="col-md-8 d-flex">
    <?php
    // Der Name 'main' oder 'col-1' ist hier sinnvoll
    $slot = ['name' => 'main', 'data' => $slots['main'] ?? null];
    include(APPPATH . 'Views/pages/layouts/partials/slot_content.php');
    ?>
  </div>
  <div class="col-md-4">
    <div class="d-flex flex-column h-100">
      <div class="flex-fill pb-1">
        <?php
        // Die Namen hier sollten aussagekräftig sein, z.B. 'sidebar-top'
        $slot = ['name' => 'sidebar-top', 'data' => $slots['sidebar-top'] ?? null];
        include(APPPATH . 'Views/pages/layouts/partials/slot_content.php');
        ?>
      </div>
      <div class="flex-fill pt-1">
        <?php
        $slot = ['name' => 'sidebar-bottom', 'data' => $slots['sidebar-bottom'] ?? null];
        include(APPPATH . 'Views/pages/layouts/partials/slot_content.php');
        ?>
      </div>
    </div>
  </div>
</div>